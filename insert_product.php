<?php
include "db_conn.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $categoryId = $_POST["category"];
  $code = $_POST["code"];
  $description = $_POST["description"];
  $stock = $_POST["stock"];
  $buyingPrice = $_POST["buying_price"];
  $markup = $_POST["markup"]; // percentage, e.g., 20 for 20%
  $dateAdded = date("Y-m-d");

  // Calculate the selling price from markup
  $sellingPrice = $buyingPrice * (1 + ($markup / 100));

  // Insert into database including markup instead of directly using a manually-entered selling price
  $sql = "INSERT INTO products (Category, Code, Description, Stock, `Cost`, Markup, `Date Added`)
          VALUES (?, ?, ?, ?, ?, ?, ?)";
  $stmt = mysqli_prepare($conn, $sql);
  mysqli_stmt_bind_param($stmt, "issiids", $categoryId, $code, $description, $stock, $buyingPrice, $markup, $dateAdded);

  if (mysqli_stmt_execute($stmt)) {
    echo "Product added successfully!";
  } else {
    echo "Error: " . mysqli_error($conn);
  }

  mysqli_stmt_close($stmt);
  mysqli_close($conn);
}
?>
