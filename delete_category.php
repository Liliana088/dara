<?php
include 'db_conn.php';

if (isset($_GET['id'])) {
  $id = $_GET['id'];
  $sql = "DELETE FROM categories WHERE id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $id);

  if ($stmt->execute()) {
    header("Location: categories.php");
    exit;
  } else {
    echo "Error deleting category.";
  }

  $stmt->close();
}

$conn->close();
?>
