<?php
include "db_conn.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $categoryId = $_POST["category"]; // This is the category ID, keep it
  $code = $_POST["code"];
  $description = $_POST["description"];
  $stock = $_POST["stock"];
  $buyingPrice = $_POST["buying_price"];
  $sellingPrice = $_POST["selling_price"];
  $dateAdded = date("Y-m-d");

  // ✅ Insert using the category ID directly
  $sql = "INSERT INTO products (Category, Code, Description, Stock, `Buying Price`, `Selling_Price`, `Date Added`)
          VALUES (?, ?, ?, ?, ?, ?, ?)";
  $stmt = mysqli_prepare($conn, $sql);
  mysqli_stmt_bind_param($stmt, "issidds", $categoryId, $code, $description, $stock, $buyingPrice, $sellingPrice, $dateAdded);

  if (mysqli_stmt_execute($stmt)) {
    echo "Product added successfully!";
  } else {
    echo "Error: " . mysqli_error($conn);
  }

  mysqli_stmt_close($stmt);
  mysqli_close($conn);
}
?>
