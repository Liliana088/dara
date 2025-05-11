<?php
include 'db_conn.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Delete related sales_items first (if needed)
    $conn->query("DELETE FROM sales_items WHERE sale_id = $id");

    // Delete from sales table
    $result = $conn->query("DELETE FROM sales WHERE id = $id");

    if ($result) {
        // Check if table is now empty
        $check = $conn->query("SELECT COUNT(*) AS count FROM sales");
        $row = $check->fetch_assoc();

        if ($row['count'] == 0) {
            // Reset AUTO_INCREMENT if table is empty
            $conn->query("ALTER TABLE sales AUTO_INCREMENT = 1");
        }

        header("Location: sales.php?msg=Sale has been <b>DELETED</b> successfully");
    } else {
        echo "Failed: " . $conn->error;
    }

    $conn->close();
}
?>
