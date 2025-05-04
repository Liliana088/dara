<?php
include "db_conn.php";

if (isset($_POST['id'])) {
    $id = intval($_POST['id']);

    // Prepare SQL query to delete the user from the database
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo "success"; // Successfully deleted user
    } else {
        echo "error"; // Failed to delete user
    }

    $stmt->close();
} else {
    echo "invalid"; // Missing user ID
}
?>
