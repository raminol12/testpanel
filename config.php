<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'marzban_panel');
define('DB_USER', 'root');
define('DB_PASS', '');

// Application configuration
define('APP_NAME', 'Marzban Panel');
define('APP_URL', 'http://localhost');
define('APP_VERSION', '1.0.0');

// Session configuration
define('SESSION_LIFETIME', 7200); // 2 hours
define('SESSION_NAME', 'marzban_session');

// Security configuration
define('HASH_COST', 12); // For password hashing
define('TOKEN_EXPIRY', 3600); // 1 hour

// API configuration
define('API_TIMEOUT', 30); // seconds
define('API_RETRY_ATTEMPTS', 3);

// Cache configuration
define('CACHE_DURATION', 300); // 5 minutes

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/error.log');

// Time zone
date_default_timezone_set('UTC');

// Session start
session_start();

// Database connection
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Helper functions
function redirect($url) {
    header("Location: $url");
    exit;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}

function requireLogin() {
    if (!isLoggedIn()) {
        redirect('/login.php');
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        redirect('/dashboard.php');
    }
}

function formatTraffic($bytes) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, 2) . ' ' . $units[$pow];
}

function formatExpireTime($timestamp) {
    if (!$timestamp) {
        return 'بدون انقضا 🕒';
    }
    $now = time();
    $diff = $timestamp - $now;
    $days = floor($diff / 86400);
    return $days >= 0 ? "$days روز 📅" : "منقضی شده ⛔";
}

function validatePanelUrl($url) {
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return false;
    }
    $parts = parse_url($url);
    return !empty($parts['scheme']) && !empty($parts['host']);
}

function generateRandomString($length = 8) {
    return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
}

function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

function jsonResponse($data, $status = 200) {
    header('Content-Type: application/json');
    http_response_code($status);
    echo json_encode($data);
    exit;
}

function errorResponse($message, $status = 400) {
    jsonResponse(['error' => $message], $status);
}

function successResponse($data = null, $message = 'Success') {
    jsonResponse(['success' => true, 'message' => $message, 'data' => $data]);
} 