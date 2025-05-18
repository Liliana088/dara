<?php
session_start();
include 'db_conn.php';

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: users.php?msg=error&reason=not_logged_in");
    exit;
}

$old_password = $_POST['old_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$confirm_new_password = $_POST['confirm_new_password'] ?? '';

if ($new_password !== $confirm_new_password) {
    header("Location: users.php?msg=error&reason=password_mismatch");
    exit;
}

// Fetch current password from DB (plain text)
$sql = "SELECT password FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    header("Location: users.php?msg=error&reason=user_not_found");
    exit;
}

// Check if old password matches exactly
if ($old_password !== $user['password']) {
    header("Location: users.php?msg=error&reason=old_wrong");
    exit;
}

// Update new password in DB (plain text)
$update_sql = "UPDATE users SET password = ? WHERE id = ?";
$update_stmt = $conn->prepare($update_sql);
$update_stmt->bind_param("si", $new_password, $user_id);

if ($update_stmt->execute()) {
    header("Location: users.php?msg=success");
    exit;
} else {
    header("Location: users.php?msg=error&reason=update_failed");
    exit;
}
