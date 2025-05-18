<?php
include "db_conn.php";

if (isset($_GET['id'])) {
    $sale_id = intval($_GET['id']);

    // Check if already voided
    $check = $conn->prepare("SELECT voided FROM sales WHERE id = ?");
    $check->bind_param("i", $sale_id);
    $check->execute();
    $check_result = $check->get_result()->fetch_assoc();
    $check->close();

    if ($check_result && $check_result['voided']) {
        // Already voided
        header("Location: sales.php");
        exit();
    }

    // 1. Mark sale as voided and reset payment-related fields
    $stmt = $conn->prepare("UPDATE sales SET voided = TRUE WHERE id = ?");
    $stmt->bind_param("i", $sale_id);
    $stmt->execute();
    $stmt->close();

    // 2. Restore stock for each product in the sale
    $items = $conn->prepare("SELECT product_id, quantity FROM sales_items WHERE sale_id = ?");
    $items->bind_param("i", $sale_id);
    $items->execute();
    $result = $items->get_result();

    while ($row = $result->fetch_assoc()) {
        $product_id = intval($row['product_id']);
        $qty = intval($row['quantity']);

        // Use prepared statement to update product stock
        $update_stock = $conn->prepare("UPDATE products SET Stock = Stock + ? WHERE id = ?");
        $update_stock->bind_param("ii", $qty, $product_id);
        $update_stock->execute();
        $update_stock->close();
    }
    $items->close();

    header("Location: sales.php");
    exit();
}
?>
