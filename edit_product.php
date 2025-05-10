<?php
include 'db_conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if (
        isset($_POST['id'], $_POST['code'], $_POST['category'], $_POST['description'],
              $_POST['stock'], $_POST['buying_price'], $_POST['markup'])
    ) {
        $id = (int) $_POST['id'];
        $code = trim($_POST['code']);
        $category = trim($_POST['category']);
        $description = trim($_POST['description']);
        $stock = (int) $_POST['stock'];
        $buyingPrice = (float) $_POST['buying_price'];
        $markup = (float) $_POST['markup'];

        // Check if product exists
        $checkQuery = "SELECT COUNT(*) FROM products WHERE id=?";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bind_param("i", $id);
        $checkStmt->execute();
        $checkStmt->bind_result($count);
        $checkStmt->fetch();
        $checkStmt->close();

        if ($count == 0) {
            echo "no_product";
            exit;
        }

        // Update product
        $query = "UPDATE products SET Code=?, Category=?, Description=?, Stock=?, `cost`=?, `markup`=? WHERE id=?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssiidi", $code, $category, $description, $stock, $buyingPrice, $markup, $id);

        if ($stmt->execute()) {
            echo "success";
        } else {
            echo "error";
        }

        $stmt->close();
    } else {
        echo "missing_fields";
    }
} else {
    echo "invalid_request";
}
?>
