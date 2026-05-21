<?php
/**
 * AsaanFix Pakistan - Authentication Middleware
 */

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/csrf.php';

/**
 * Require user to be logged in
 */
function requireLogin(): void {
    if (!isLoggedIn()) {
        setFlash('warning', 'Please login to continue.');
        redirect(APP_URL . '/login');
    }
}

/**
 * Require specific role
 */
function requireRole(string $role): void {
    requireLogin();
    if (getCurrentUserRole() !== $role) {
        setFlash('danger', 'You do not have permission to access this page.');
        redirect(APP_URL . '/');
    }
}

/**
 * Require admin role
 */
function requireAdmin(): void {
    requireRole('admin');
}

/**
 * Require technician role
 */
function requireTechnician(): void {
    requireLogin();
    if (!in_array(getCurrentUserRole(), ['technician', 'admin'])) {
        setFlash('danger', 'You do not have permission to access this page.');
        redirect(APP_URL . '/');
    }
}

/**
 * Require user role (customer)
 */
function requireUser(): void {
    requireLogin();
    if (!in_array(getCurrentUserRole(), ['user', 'admin'])) {
        setFlash('danger', 'You do not have permission to access this page.');
        redirect(APP_URL . '/');
    }
}

/**
 * Redirect if already logged in
 */
function redirectIfLoggedIn(): void {
    if (isLoggedIn()) {
        $role = getCurrentUserRole();
        switch ($role) {
            case 'admin':
                redirect(APP_URL . '/admin/dashboard');
                break;
            case 'technician':
                redirect(APP_URL . '/technician/dashboard');
                break;
            default:
                redirect(APP_URL . '/user/dashboard');
        }
    }
}

/**
 * Get the dashboard URL for current user
 */
function getDashboardUrl(): string {
    $role = getCurrentUserRole();
    switch ($role) {
        case 'admin': return APP_URL . '/admin/dashboard';
        case 'technician': return APP_URL . '/technician/dashboard';
        default: return APP_URL . '/user/dashboard';
    }
}

/**
 * Get current user data from database
 */
function getCurrentUser(): ?array {
    if (!isLoggedIn()) return null;
    return dbQueryOne("SELECT * FROM users WHERE id = ? AND is_active = 1", [getCurrentUserId()]);
}

/**
 * Get technician profile for current user
 */
function getCurrentTechnicianProfile(): ?array {
    if (!isLoggedIn()) return null;
    return dbQueryOne(
        "SELECT t.*, u.name, u.email, u.phone, u.avatar, u.city 
         FROM technicians t JOIN users u ON t.user_id = u.id 
         WHERE t.user_id = ?",
        [getCurrentUserId()]
    );
}
