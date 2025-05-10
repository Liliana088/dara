<?php
include 'db_conn.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Delete category from the database
    $sql = "DELETE FROM categories WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        // Reset AUTO_INCREMENT for the table after deletion
        $sql_reset = "ALTER TABLE categories AUTO_INCREMENT = 1";
        if (mysqli_query($conn, $sql_reset)) {
            // Redirect with success message
            header("Location: categories.php?msg=Data has been <b>DELETED</b> successfully");
            exit;
        } else {
            echo "Failed to reset AUTO_INCREMENT: " . mysqli_error($conn);
        }
    } else {
        echo "Error deleting category: " . mysqli_error($conn);
    }

    $stmt->close();
}

$conn->close();
?>
