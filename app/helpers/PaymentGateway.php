<?php
/**
 * AsaanFix Pakistan - Payment Gateway Integration
 * Mock JazzCash & EasyPaisa integration for Pakistan
 */

class PaymentGateway {

    /**
     * Initialize a JazzCash payment
     */
    public static function initJazzCash(array $booking): array {
        $merchantId = env('JAZZCASH_MERCHANT_ID', '');
        $password = env('JAZZCASH_PASSWORD', '');
        $salt = env('JAZZCASH_INTEGRITY_SALT', '');
        $isSandbox = env('JAZZCASH_SANDBOX', true);

        $txnRef = 'FH-JC-' . $booking['booking_number'] . '-' . time();
        $amount = number_format($booking['total_amount'] * 100, 0, '', ''); // In paisa
        $dateTime = date('YmdHis');
        $expiryDT = date('YmdHis', strtotime('+1 hour'));

        $data = [
            'pp_Version'           => '1.1',
            'pp_TxnType'           => 'MWALLET',
            'pp_Language'          => 'EN',
            'pp_MerchantID'        => $merchantId,
            'pp_SubMerchantID'     => '',
            'pp_Password'          => $password,
            'pp_BankID'            => 'TBANK',
            'pp_ProductID'         => 'RETL',
            'pp_TxnRefNo'          => $txnRef,
            'pp_Amount'            => $amount,
            'pp_TxnCurrency'       => 'PKR',
            'pp_TxnDateTime'       => $dateTime,
            'pp_BillReference'     => $booking['booking_number'],
            'pp_Description'       => 'AsaanFix Service Booking #' . $booking['booking_number'],
            'pp_TxnExpiryDateTime' => $expiryDT,
            'pp_ReturnURL'         => APP_URL . '/api/payment/jazzcash-callback',
            'pp_SecureHash'        => '',
        ];

        // Generate secure hash
        $hashString = $salt . '&' . implode('&', array_values($data));
        $data['pp_SecureHash'] = hash_hmac('sha256', $hashString, $salt);

        // In sandbox/demo mode, return mock response
        if ($isSandbox || empty($merchantId)) {
            return self::mockPaymentResponse('jazzcash', $booking, $txnRef);
        }

        return [
            'success'     => true,
            'gateway'     => 'jazzcash',
            'txn_ref'     => $txnRef,
            'redirect_url'=> env('JAZZCASH_ENDPOINT'),
            'form_data'   => $data,
        ];
    }

    /**
     * Initialize an EasyPaisa payment
     */
    public static function initEasyPaisa(array $booking): array {
        $storeId = env('EASYPAISA_STORE_ID', '');
        $token = env('EASYPAISA_TOKEN', '');
        $isSandbox = env('EASYPAISA_SANDBOX', true);

        $orderId = 'FH-EP-' . $booking['booking_number'] . '-' . time();
        $amount = number_format($booking['total_amount'], 2, '.', '');

        $data = [
            'storeId'       => $storeId,
            'amount'        => $amount,
            'postBackURL'   => APP_URL . '/api/payment/easypaisa-callback',
            'orderRefNum'   => $orderId,
            'expiryDate'    => date('Ymd His', strtotime('+1 hour')),
            'merchantHashedReq' => '',
        ];

        if ($isSandbox || empty($storeId)) {
            return self::mockPaymentResponse('easypaisa', $booking, $orderId);
        }

        return [
            'success'     => true,
            'gateway'     => 'easypaisa',
            'txn_ref'     => $orderId,
            'redirect_url'=> env('EASYPAISA_ENDPOINT'),
            'form_data'   => $data,
        ];
    }

    /**
     * Process payment callback
     */
    public static function processCallback(string $gateway, array $data): array {
        // Record payment in database
        $txnRef = $data['txn_ref'] ?? $data['pp_TxnRefNo'] ?? $data['orderRefNum'] ?? '';
        $status = ($data['pp_ResponseCode'] ?? $data['responseCode'] ?? '') === '000' ? 'completed' : 'failed';

        return [
            'success'        => $status === 'completed',
            'gateway'        => $gateway,
            'transaction_id' => $txnRef,
            'status'         => $status,
            'message'        => $status === 'completed' ? 'Payment successful' : 'Payment failed',
        ];
    }

    /**
     * Record payment in database
     */
    public static function recordPayment(int $bookingId, float $amount, string $method, string $txnId, string $status = 'completed'): int {
        dbExecute(
            "INSERT INTO payments (booking_id, amount, method, transaction_id, status, paid_at) VALUES (?, ?, ?, ?, ?, NOW())",
            [$bookingId, $amount, $method, $txnId, $status]
        );
        return dbLastId();
    }

    /**
     * Process refund
     */
    public static function processRefund(int $paymentId, string $reason = ''): array {
        $payment = dbQueryOne("SELECT * FROM payments WHERE id = ?", [$paymentId]);
        if (!$payment) return ['success' => false, 'message' => 'Payment not found'];

        dbExecute("UPDATE payments SET status = 'refunded', updated_at = NOW() WHERE id = ?", [$paymentId]);
        dbExecute("UPDATE bookings SET status = 'refunded' WHERE id = ?", [$payment['booking_id']]);

        return ['success' => true, 'message' => 'Refund processed for ' . formatPrice($payment['amount'])];
    }

    /**
     * Mock payment response for demo/sandbox
     */
    private static function mockPaymentResponse(string $gateway, array $booking, string $txnRef): array {
        // Simulate successful payment in sandbox
        return [
            'success'        => true,
            'gateway'        => $gateway,
            'txn_ref'        => $txnRef,
            'status'         => 'completed',
            'amount'         => $booking['total_amount'],
            'mock'           => true,
            'message'        => "Demo: $gateway payment of " . formatPrice($booking['total_amount']) . ' successful',
            'redirect_url'   => APP_URL . '/user/bookings',
        ];
    }

    /**
     * Get all available payment methods
     */
    public static function getAvailableMethods(): array {
        return PAYMENT_METHODS;
    }
}
