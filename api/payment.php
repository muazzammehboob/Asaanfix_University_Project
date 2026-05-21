<?php
/**
 * AsaanFix Pakistan - Payment API Endpoint
 * POST /api/payment/initiate   → Start payment
 * POST /api/payment/callback   → Payment callback
 */

require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../app/helpers/PaymentGateway.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'initiate':
        handleInitiate();
        break;
    case 'jazzcash-callback':
    case 'easypaisa-callback':
        handleCallback($action);
        break;
    case 'methods':
        jsonResponse(['success' => true, 'data' => PaymentGateway::getAvailableMethods()]);
        break;
    default:
        jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
}

function handleInitiate(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(['success' => false, 'message' => 'POST required'], 405);
    }

    requireCSRF();

    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $bookingId = (int) ($input['booking_id'] ?? 0);
    $method = $input['method'] ?? 'cash';

    if (!$bookingId) {
        jsonResponse(['success' => false, 'message' => 'Booking ID required'], 400);
    }

    $booking = dbQueryOne("SELECT * FROM bookings WHERE id = ?", [$bookingId]);
    if (!$booking) {
        jsonResponse(['success' => false, 'message' => 'Booking not found'], 404);
    }

    switch ($method) {
        case 'jazzcash':
            $result = PaymentGateway::initJazzCash($booking);
            break;
        case 'easypaisa':
            $result = PaymentGateway::initEasyPaisa($booking);
            break;
        case 'cash':
            // Record as pending cash payment
            PaymentGateway::recordPayment($bookingId, $booking['total_amount'], 'cash', 'CASH-' . time(), 'pending');
            $result = ['success' => true, 'message' => 'Cash payment recorded. Pay on service completion.', 'gateway' => 'cash'];
            break;
        default:
            jsonResponse(['success' => false, 'message' => 'Unsupported payment method'], 400);
            return;
    }

    jsonResponse($result);
}

function handleCallback(string $gateway): void {
    $data = $_POST ?: $_GET;
    $gateway = str_replace('-callback', '', $gateway);
    $result = PaymentGateway::processCallback($gateway, $data);

    if ($result['success']) {
        // Find and update booking
        $txnId = $result['transaction_id'];
        $payment = dbQueryOne("SELECT * FROM payments WHERE transaction_id = ?", [$txnId]);
        if ($payment) {
            dbExecute("UPDATE payments SET status = 'completed', paid_at = NOW() WHERE id = ?", [$payment['id']]);
        }
    }

    // Redirect back to user bookings
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        jsonResponse($result);
    } else {
        setFlash($result['success'] ? 'success' : 'danger', $result['message']);
        redirect(APP_URL . '/user/bookings');
    }
}
