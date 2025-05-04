<?php
include "db_conn.php";

if (isset($_POST['id'], $_POST['category'])) {
    $id = intval($_POST['id']);
    $category = trim($_POST['category']);

    if ($category !== "") {
        $stmt = $conn->prepare("UPDATE categories SET category = ? WHERE id = ?");
        $stmt->bind_param("si", $category, $id);

        if ($stmt->execute()) {
            echo "success";
        } else {
            echo "error";
        }

        $stmt->close();
    } else {
        echo "empty";
    }
} else {
    echo "invalid";
}
?>
