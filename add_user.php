<?php
include "db_conn.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'], $_POST['username'], $_POST['password'], $_POST['status'])) {
    $name = trim($_POST['name']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $status = trim($_POST['status']);
    $last_login = date('Y-m-d H:i:s'); // Set current time for last_login

    if ($name !== '' && $username !== '' && $password !== '' && $status !== '') {
        // Hash the password for security
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Prepare the SQL statement to insert the user into the database
        $stmt = $conn->prepare("INSERT INTO users (name, username, password, Status, last_login) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $name, $username, $hashed_password, $status, $last_login);

        if ($stmt->execute()) {
            echo "success"; // Successfully added user
        } else {
            echo "error"; // Failed to add user
        }

        $stmt->close();
    } else {
        echo "empty"; // Some fields are empty
    }
}
?>
