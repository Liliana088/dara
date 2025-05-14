<?php
include "db_conn.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'], $_POST['username'], $_POST['password'])) {
    $name = trim($_POST['name']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $status = 'Active'; // Automatically set to Active
    $last_login = date('Y-m-d H:i:s'); // Current system date and time

    if ($name !== '' && $username !== '' && $password !== '') {

        // Directly use the plain password without hashing
        $stmt = $conn->prepare("INSERT INTO users (name, username, password, Status, last_login) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $name, $username, $password, $status, $last_login);

        if ($stmt->execute()) {
            echo "success";
            header("Location: users.php");
            exit(); // Make sure to stop further execution
        } else {
            echo "error";
        }

        $stmt->close();
    } else {
        echo "empty";
    }
} else {
    echo "Invalid request";
}
?>
