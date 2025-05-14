<?php
session_start();
include "db_conn.php";

if (!isset($_SESSION['user_name'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = mysqli_real_escape_string($conn, $_POST['id']);

    // Delete the user
    $deleteSql = "DELETE FROM users WHERE id='$id'";
    if (mysqli_query($conn, $deleteSql)) {

        // Reset IDs to be sequential (1, 2, 3, ...)
        $tempId = 1;
        $result = mysqli_query($conn, "SELECT * FROM users ORDER BY id ASC");

        while ($row = mysqli_fetch_assoc($result)) {
            $currentId = $row['id'];
            mysqli_query($conn, "UPDATE users SET id = $tempId WHERE id = $currentId");
            $tempId++;
        }

        // Reset AUTO_INCREMENT to next number
        mysqli_query($conn, "ALTER TABLE users AUTO_INCREMENT = $tempId");

        header("Location: users.php?msg=User deleted and IDs reset successfully");
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }
} else {
    header("Location: users.php");
    exit();
}
?>
