<?php
/**
 * AsaanFix Pakistan - API Authentication Endpoint
 * POST /api/auth/login    → Get JWT tokens
 * POST /api/auth/refresh  → Refresh access token
 * GET  /api/auth/me       → Get current user info
 */

require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../app/middleware/JWTAuth.php';
require_once __DIR__ . '/../../app/middleware/Security.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Authorization, Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'login':
        handleLogin();
        break;
    case 'refresh':
        handleRefresh();
        break;
    case 'me':
        handleMe();
        break;
    default:
        jsonResponse(['success' => false, 'message' => 'Invalid action. Use: login, refresh, me'], 400);
}

function handleLogin(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(['success' => false, 'message' => 'POST method required'], 405);
    }

    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $email = $input['email'] ?? '';
    $password = $input['password'] ?? '';

    if (empty($email) || empty($password)) {
        jsonResponse(['success' => false, 'message' => 'Email and password required'], 400);
    }

    // Rate limit
    if (!Security::rateLimit('api_login_' . $email, 10, 300)) {
        jsonResponse(['success' => false, 'message' => 'Too many attempts. Try later.'], 429);
    }

    // Check login attempts
    if (!Security::checkLoginAttempts($email)) {
        $remaining = Security::getLockoutRemaining($email);
        jsonResponse(['success' => false, 'message' => "Account locked. Try again in {$remaining}s."], 423);
    }

    $user = dbQueryOne("SELECT * FROM users WHERE email = ? AND is_active = 1", [$email]);

    if (!$user || !password_verify($password, $user['password'])) {
        Security::recordFailedLogin($email);
        jsonResponse(['success' => false, 'message' => 'Invalid credentials'], 401);
    }

    Security::clearLoginAttempts($email);
    dbExecute("UPDATE users SET last_login = NOW() WHERE id = ?", [$user['id']]);

    $tokens = JWTAuth::generateTokenPair($user);

    jsonResponse([
        'success' => true,
        'message' => 'Login successful',
        'data'    => [
            'user'   => [
                'id'    => $user['id'],
                'name'  => $user['name'],
                'email' => $user['email'],
                'role'  => $user['role'],
            ],
            'tokens' => $tokens,
        ]
    ]);
}

function handleRefresh(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(['success' => false, 'message' => 'POST method required'], 405);
    }

    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $refreshToken = $input['refresh_token'] ?? '';

    if (empty($refreshToken)) {
        jsonResponse(['success' => false, 'message' => 'Refresh token required'], 400);
    }

    $tokens = JWTAuth::refreshAccessToken($refreshToken);
    if (!$tokens) {
        jsonResponse(['success' => false, 'message' => 'Invalid refresh token'], 401);
    }

    jsonResponse(['success' => true, 'data' => ['tokens' => $tokens]]);
}

function handleMe(): void {
    $user = JWTAuth::authenticate();
    if (!$user) return;

    jsonResponse([
        'success' => true,
        'data'    => ['user' => $user]
    ]);
}
