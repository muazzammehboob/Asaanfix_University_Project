<?php
/**
 * AsaanFix Pakistan - User Model
 */
require_once __DIR__ . '/Model.php';

class UserModel extends Model {
    protected string $table = 'users';

    public function findByEmail(string $email): ?array {
        return $this->findBy('email', $email);
    }

    public function getActiveUsers(string $role = 'user'): array {
        return $this->where("role = ? AND is_active = 1", [$role], 'created_at DESC');
    }

    public function verifyEmail(int $userId): void {
        $this->update($userId, ['email_verified_at' => date('Y-m-d H:i:s'), 'verification_token' => null]);
    }

    public function updateLastLogin(int $userId): void {
        $this->update($userId, ['last_login' => date('Y-m-d H:i:s')]);
    }

    public function search(string $query): array {
        return $this->where(
            "name LIKE ? OR email LIKE ? OR phone LIKE ?",
            ["%$query%", "%$query%", "%$query%"]
        );
    }
}
