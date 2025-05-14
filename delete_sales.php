<?php
include 'db_conn.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Use prepared statement to prevent SQL injection
    if ($stmt = $conn->prepare("DELETE FROM sales_items WHERE sale_id = ?")) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }

    // Now delete from sales table using prepared statement
    if ($stmt = $conn->prepare("DELETE FROM sales WHERE id = ?")) {
        $stmt->bind_param("i", $id);
        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            // Reset AUTO_INCREMENT for sales table if no rows are left
            $check = $conn->query("SELECT COUNT(*) AS count FROM sales");
            $row = $check->fetch_assoc();

            if ($row['count'] == 0) {
                $conn->query("ALTER TABLE sales AUTO_INCREMENT = 1");
            }

            // Reset AUTO_INCREMENT for sales_items table (optional, if needed)
            $check_items = $conn->query("SELECT COUNT(*) AS count FROM sales_items");
            $row_items = $check_items->fetch_assoc();

            if ($row_items['count'] == 0) {
                $conn->query("ALTER TABLE sales_items AUTO_INCREMENT = 1");
            }

            // Redirect to sales page with a success message
            header("Location: sales.php?msg=Sale has been <b>DELETED</b> successfully");
            exit;
        } else {
            echo "Failed: " . $conn->error;
        }
    }

    $conn->close();
} else {
    echo "No sale ID provided.";
}
?>
