<?php
include "db_conn.php"; 
// Check if the form data is being received
var_dump($_POST);

// Process the form data if method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate that all necessary form data is received
    if (!isset($_POST['id'], $_POST['name'], $_POST['username'], $_POST['password'], $_POST['confirm_password'])) {
        echo "Missing form data.";
        exit();
    }

    $id = $_POST['id'];  // Get the user id from the form
    $name = $_POST['name'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Debugging - check the submitted form data
    var_dump($username); // This will output the username from the form
    var_dump($id); // This will output the user ID

    // Check if the passwords match
    if ($password !== $confirm_password) {
        echo "Passwords do not match.";
        exit();
    }

    // Prepare the update query
    $sql = "UPDATE users SET name = ?, username = ?, password = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $name, $username, $password, $id); // Use plain password

    // Execute the query and redirect
    if ($stmt->execute()) {
        header("Location: users.php");  // Redirect to users.php after successful update
        exit();
    } else {
        echo "Error updating user.";
    }

    $stmt->close();
}
?>
