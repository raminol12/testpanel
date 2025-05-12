<?php
require_once '../config.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    errorResponse('Method not allowed', 405);
}

$user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;

if (!$user_id) {
    errorResponse('شناسه کاربر نامعتبر است');
}

// Check if user has access to this panel
$stmt = $pdo->prepare("
    SELECT p.* FROM panels p
    INNER JOIN user_panels up ON p.id = up.panel_id
    INNER JOIN users u ON u.panel_id = p.id
    WHERE up.user_id = ? AND u.id = ?
");
$stmt->execute([$_SESSION['user_id'], $user_id]);
$panel = $stmt->fetch();

if (!$panel) {
    errorResponse('دسترسی غیرمجاز');
}

try {
    // Start transaction
    $pdo->beginTransaction();

    // Delete user
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$user_id]);

    // Commit transaction
    $pdo->commit();

    successResponse(null, 'کاربر با موفقیت حذف شد');
} catch (Exception $e) {
    // Rollback transaction on error
    $pdo->rollBack();
    errorResponse('خطا در حذف کاربر');
} 