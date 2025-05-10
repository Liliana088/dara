<?php
include 'db_conn.php'; // Ensure your database connection is correct

// Check if the 'id' is passed via the URL
if (isset($_GET['id'])) {
  $id = $_GET['id'];

  // Prepare the SQL statement to delete the product by id
  $sql = "DELETE FROM products WHERE id = ?";
  $stmt = $conn->prepare($sql);

  // Bind the 'id' parameter to the statement
  $stmt->bind_param("i", $id);

  // Execute the statement
  if ($stmt->execute()) {
    // Reset the AUTO_INCREMENT value for the table
    $sql_reset = "ALTER TABLE products AUTO_INCREMENT = 1";
    if (mysqli_query($conn, $sql_reset)) {
      // Redirect to the products page after successful deletion and reset
      header("Location: products.php?msg=Data has been <b>DELETED</b> successfully");
      exit;
    } else {
      echo "Error resetting AUTO_INCREMENT: " . mysqli_error($conn);
    }
  } else {
    // Display error if the deletion fails
    echo "Error deleting product.";
  }

  // Close the prepared statement
  $stmt->close();
}

// Close the database connection
$conn->close();
?>
