<?php
session_start(); // Start session first

include "db_conn.php";

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  // Normal login
  $username = trim($_POST['username'] ?? '');
  $password = trim($_POST['password'] ?? '');

  if (empty($username) || empty($password)) {
      $error = "Please fill in both Username and Password.";
  } else {
      $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND password = ?");
      $stmt->bind_param("ss", $username, $password);
      $stmt->execute();
      $result = $stmt->get_result();

      if ($result && $result->num_rows > 0) {
          $user = $result->fetch_assoc();
          $_SESSION['user_id'] = $user['id'];
          $_SESSION['user_name'] = $user['name'];

          // Update the last_login timestamp
          $update = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
          $update->bind_param("i", $user['id']);
          $update->execute();

          header("Location: dashboard.php");
          exit();
      } else {
          $error = "Incorrect username or password.";
      }

      $stmt->close();
  }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <title>Login Form</title>
    <link href="bootstrap-offline/css/bootstrap.css" rel="stylesheet">
    <link rel="stylesheet" href="/dara/fontawesome/css/all.min.css" />
    <link rel="icon" type="image/x-icon" href="img/daraa.ico">
    <link rel="stylesheet" href="/dara/css/login.css">
  </head>

  <body>
    <div class="login-container">
      <img src="img/dara.png" alt="Logo" class="top-image">
        <form method="post" action="" class="needs-validation" novalidate>
        <input type="hidden" name="username" value="admin">
          <div class="mb-3 text-start">      
            <div class="input-group has-validation">
              <span class="input-group-text"><i class="fa fa-lock"></i></span>
              <input type="password" class="form-control" id="password" name="password" placeholder="Enter password">
              <div class="invalid-feedback">
                Please enter your password.
              </div>
            </div>
          </div>
          <button type="submit" class="btn btn-outline-light w-100 mb-1">Login</button>
        </form>

        <!-- PHP message output -->
        <?php if (!empty($error)): ?>
          <div class="alert alert-warning mt-3"><?php echo $error; ?></div>
        <?php elseif (!empty($success)): ?>
          <div class="alert alert-success mt-3"><?php echo $success; ?></div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap 5 JavaScript for validation -->
    <script src="bootstrap-offline/js/bootstrap.bundle.min.js"></script>
    <script>
    (() => {
      'use strict';
      const forms = document.querySelectorAll('.needs-validation');
      Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
          if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
          }
          form.classList.add('was-validated');
        }, false);
      });
    })();
    </script>
  </body>
</html>
