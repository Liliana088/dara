<?php
include "db_conn.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $categoryId = $_POST["category"]; // This is still the ID from the dropdown
  $code = $_POST["code"];
  $description = $_POST["description"];
  $stock = $_POST["stock"];
  $buyingPrice = $_POST["buying_price"];
  $sellingPrice = $_POST["selling_price"];
  $dateAdded = date("Y-m-d");

  // Step 1: Get category name from categories table
  $categoryName = "";
  $catQuery = "SELECT category FROM categories WHERE id = ?";
  $catStmt = mysqli_prepare($conn, $catQuery);
  mysqli_stmt_bind_param($catStmt, "i", $categoryId);
  mysqli_stmt_execute($catStmt);
  mysqli_stmt_bind_result($catStmt, $categoryName);
  mysqli_stmt_fetch($catStmt);
  mysqli_stmt_close($catStmt);

  if (!$categoryName) {
    echo "Invalid category selected.";
    exit;
  }

  // Step 2: Insert into products using category name
  $sql = "INSERT INTO products (Category, Code, Description, Stock, `Buying Price`, `Selling Price`, `Date Added`)
          VALUES (?, ?, ?, ?, ?, ?, ?)";
  $stmt = mysqli_prepare($conn, $sql);
  mysqli_stmt_bind_param($stmt, "sssidds", $categoryName, $code, $description, $stock, $buyingPrice, $sellingPrice, $dateAdded);

  if (mysqli_stmt_execute($stmt)) {
    echo "Product added successfully!";
  } else {
    echo "Error: " . mysqli_error($conn);
  }

  mysqli_stmt_close($stmt);
  mysqli_close($conn);
}
?>
