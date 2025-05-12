<?php
require_once '../config.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    errorResponse('Method not allowed', 405);
}

$panel_id = isset($_POST['panel_id']) ? (int)$_POST['panel_id'] : 0;
$username = sanitizeInput($_POST['username']);
$password = $_POST['password'];
$expire_time = isset($_POST['expire_time']) ? (int)$_POST['expire_time'] : 0;

if (empty($username) || empty($password)) {
    errorResponse('نام کاربری و رمز عبور الزامی است');
}

// Check if user has access to this panel
$stmt = $pdo->prepare("
    SELECT p.* FROM panels p
    INNER JOIN user_panels up ON p.id = up.panel_id
    WHERE up.user_id = ? AND p.id = ?
");
$stmt->execute([$_SESSION['user_id'], $panel_id]);
$panel = $stmt->fetch();

if (!$panel) {
    errorResponse('دسترسی غیرمجاز');
}

// Check if username already exists
$stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND panel_id = ?");
$stmt->execute([$username, $panel_id]);
if ($stmt->fetch()) {
    errorResponse('این نام کاربری قبلاً استفاده شده است');
}

// Calculate expire timestamp
$expire_timestamp = $expire_time > 0 ? time() + ($expire_time * 86400) : 0;

try {
    // Start transaction
    $pdo->beginTransaction();

    // Add user to database
    $stmt = $pdo->prepare("
        INSERT INTO users (username, password, panel_id, expire_time, created_at)
        VALUES (?, ?, ?, ?, NOW())
    ");
    $stmt->execute([
        $username,
        password_hash($password, PASSWORD_BCRYPT, ['cost' => HASH_COST]),
        $panel_id,
        $expire_timestamp
    ]);

    // Commit transaction
    $pdo->commit();

    successResponse(null, 'کاربر با موفقیت اضافه شد');
} catch (Exception $e) {
    // Rollback transaction on error
    $pdo->rollBack();
    errorResponse('خطا در افزودن کاربر');
} 