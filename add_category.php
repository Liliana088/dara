<?php
include "db_conn.php";

if (!isset($_SESSION['user_name'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['category'])) {
    $category = trim($_POST['category']);
    if ($category !== '') {
        $stmt = $conn->prepare("INSERT INTO categories (category) VALUES (?)");
        $stmt->bind_param("s", $category);
        if ($stmt->execute()) {
            echo "success";
        } else {
            echo "error";
        }
        $stmt->close();
    } else {
        echo "empty";
    }
}
?>
