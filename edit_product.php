<?php
include "db_conn.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $code = $_POST['code'];
    $description = $_POST['description'];
    $stock = $_POST['stock'];
    $buyingPrice = $_POST['buying_price'];
    $markup = $_POST['markup'];
    $sellingPrice = $_POST['selling_price'];
    $category = $_POST['category'];

    $sql = "UPDATE products SET Code='$code', Description='$description', Stock='$stock', 
            `Buying Price`='$buyingPrice', Markup='$markup', `Selling Price`='$sellingPrice', 
            Category='$category' WHERE id='$id'";

    if (mysqli_query($conn, $sql)) {
        echo 'success';
    } else {
        echo 'error';
    }
}
?>
