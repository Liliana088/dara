<?php
// getCategories.php
include "db_conn.php";

$sql = "SELECT id, category FROM categories"; // Assuming the category table has 'id' and 'category'
$result = mysqli_query($conn, $sql);

$categories = [];

if ($result) {
  while ($row = mysqli_fetch_assoc($result)) {
    $categories[] = ['id' => $row['id'], 'category' => $row['category']];
  }
}

echo json_encode(['categories' => $categories]);

?>

