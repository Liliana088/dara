<?php
include 'db_conn.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Delete from sales_items first to respect foreign key
    if ($stmt = $conn->prepare("DELETE FROM sales_items WHERE sale_id = ?")) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }

    // Delete from sales
    if ($stmt = $conn->prepare("DELETE FROM sales WHERE id = ?")) {
        $stmt->bind_param("i", $id);
        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            // --- Reset AUTO_INCREMENT for sales table ---
            $result = $conn->query("SELECT MAX(id) AS max_id FROM sales");
            $row = $result->fetch_assoc();
            $new_auto_increment = isset($row['max_id']) && $row['max_id'] !== null ? $row['max_id'] + 1 : 1;
            $conn->query("ALTER TABLE sales AUTO_INCREMENT = $new_auto_increment");

            // --- Reset AUTO_INCREMENT for sales_items table ---
            $result_items = $conn->query("SELECT MAX(id) AS max_id FROM sales_items");
            $row_items = $result_items->fetch_assoc();
            $new_auto_increment_items = isset($row_items['max_id']) && $row_items['max_id'] !== null ? $row_items['max_id'] + 1 : 1;
            $conn->query("ALTER TABLE sales_items AUTO_INCREMENT = $new_auto_increment_items");

            // Redirect to sales page
            header("Location: sales.php?msg=Sale has been <b>DELETED</b> successfully");
            exit;
        } else {
            echo "Failed to delete sale: " . $conn->error;
        }
    }

    $conn->close();
} else {
    echo "No sale ID provided.";
}
?>
