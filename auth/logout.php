<?php
/**
 * AsaanFix Pakistan - Logout
 */
require_once __DIR__ . '/../includes/functions.php';
destroySession();
header("Location: " . APP_URL . "/home");
exit;
