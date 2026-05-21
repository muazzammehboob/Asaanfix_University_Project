<?php
/**
 * AsaanFix Pakistan - JWT Authentication for API
 * Stateless token-based auth for mobile/REST API support
 */

class JWTAuth {

    public static function generateToken(array $payload, int $expiresIn = 86400): string {
        $header = self::b64e(json_encode(['typ'=>'JWT','alg'=>'HS256']));
        $payload['iat'] = time();
        $payload['exp'] = time() + $expiresIn;
        $payload['iss'] = defined('APP_NAME') ? APP_NAME : 'AsaanFix';
        $payloadEnc = self::b64e(json_encode($payload));
        $sig = self::b64e(hash_hmac('sha256', "$header.$payloadEnc", JWT_SECRET, true));
        return "$header.$payloadEnc.$sig";
    }

    public static function validateToken(string $token): ?array {
        $parts = explode('.', $token);
        if (count($parts) !== 3) return null;
        [$header, $payload, $sig] = $parts;
        $expected = self::b64e(hash_hmac('sha256', "$header.$payload", JWT_SECRET, true));
        if (!hash_equals($expected, $sig)) return null;
        $data = json_decode(self::b64d($payload), true);
        if (!$data || (isset($data['exp']) && $data['exp'] < time())) return null;
        return $data;
    }

    public static function extractToken(): ?string {
        $h = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
        if (preg_match('/Bearer\s+(.+)$/i', $h, $m)) return $m[1];
        return $_GET['api_token'] ?? null;
    }

    public static function authenticate(): ?array {
        $token = self::extractToken();
        if (!$token) { self::send401('No token provided.'); return null; }
        $payload = self::validateToken($token);
        if (!$payload) { self::send401('Invalid or expired token.'); return null; }
        $user = dbQueryOne("SELECT id,name,email,role,is_active FROM users WHERE id=? AND is_active=1", [$payload['user_id']]);
        if (!$user) { self::send401('User not found.'); return null; }
        return $user;
    }

    public static function generateTokenPair(array $user): array {
        $access = self::generateToken(['user_id'=>$user['id'],'email'=>$user['email'],'role'=>$user['role'],'type'=>'access'], 3600);
        $refresh = self::generateToken(['user_id'=>$user['id'],'type'=>'refresh'], 604800);
        return ['access_token'=>$access,'refresh_token'=>$refresh,'token_type'=>'Bearer','expires_in'=>3600];
    }

    public static function refreshAccessToken(string $refreshToken): ?array {
        $p = self::validateToken($refreshToken);
        if (!$p || ($p['type']??'') !== 'refresh') return null;
        $user = dbQueryOne("SELECT id,email,role FROM users WHERE id=? AND is_active=1", [$p['user_id']]);
        return $user ? self::generateTokenPair($user) : null;
    }

    private static function send401(string $msg): void {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['success'=>false,'message'=>$msg,'error'=>'unauthorized']);
        exit;
    }

    private static function b64e(string $d): string { return rtrim(strtr(base64_encode($d),'+/','-_'),'='); }
    private static function b64d(string $d): string { return base64_decode(strtr($d,'-_','+/')); }
}
