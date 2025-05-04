<?php
include "db_conn.php";

if (isset($_POST['id'], $_POST['name'], $_POST['username'], $_POST['status'], $_POST['password'])) {
    $id = intval($_POST['id']);
    $name = trim($_POST['name']);
    $username = trim($_POST['username']);
    $status = trim($_POST['status']);
    $password = trim($_POST['password']);

    if ($name !== "" && $username !== "" && $status !== "") {
        // If a new password is provided, hash it. Otherwise, use the old password.
        $hashed_password = !empty($password) ? password_hash($password, PASSWORD_DEFAULT) : null;

        // Prepare SQL query to update the user details in the database
        if ($hashed_password) {
            // If a new password is provided, update the password as well
            $stmt = $conn->prepare("UPDATE users SET name = ?, username = ?, password = ?, Status = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $name, $username, $hashed_password, $status, $id);
        } else {
            // If no new password is provided, don't update the password
            $stmt = $conn->prepare("UPDATE users SET name = ?, username = ?, Status = ? WHERE id = ?");
            $stmt->bind_param("sssi", $name, $username, $status, $id);
        }

        if ($stmt->execute()) {
            echo "success"; // Successfully updated user
        } else {
            echo "error"; // Failed to update user
        }

        $stmt->close();
    } else {
        echo "empty"; // Some fields are empty
    }
} else {
    echo "invalid"; // Missing required data
}
?>
