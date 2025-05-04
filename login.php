<?php
session_start(); // Start session first

// Database connection
$servername = "localhost";
$db_username = "root";
$db_password = ""; // change if needed
$dbname = "dara";

$conn = new mysqli($servername, $db_username, $db_password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Guest login
  if (isset($_POST['guest_login'])) {
      $_SESSION['user_id'] = 0;
      $_SESSION['user_name'] = 'Guest';
      header("Location: dashboard.php");
      exit();
  }

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

  <!-- Bootstrap 5 CSS -->
  <link href="bootstrap-offline/css/bootstrap.css" rel="stylesheet">

  <!-- FontAwesome Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <style>
    body {
      height: 100vh;
      background: linear-gradient(to bottom right, #644b80, #ed8990);
      display: flex;
      justify-content: center;
      align-items: center;
      font-family: Arial, sans-serif;
    }

    .login-container {
      background: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(5px);
      padding: 40px 30px;
      width: 100%;
      max-width: 500px;
      text-align: center;
      border-radius: 10px;
      color: white;
    }

    .top-image {
      width: 150px;
      height: auto;
      margin-bottom: 30px;
    }

    .form-control::placeholder {
      color: #bbb;
    }
  </style>
</head>

<body>

<div class="login-container">
    <img src="img/dara.png" alt="Logo" class="top-image">

    <form method="post" action="" class="needs-validation" novalidate>
      <div class="mb-1 text-start">   
        <div class="input-group has-validation">
          <span class="input-group-text "><i class="fa fa-user"></i></span>
          <input type="text" class="form-control" id="username" name="username" placeholder="Enter username">
          <div class="invalid-feedback">
            Please enter your username.
          </div>
        </div>
      </div>

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
      <button type="submit" name="guest_login" class="btn btn-outline-secondary w-100">Login as Guest</button>
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
// Bootstrap 5 custom validation with guest login bypass
(() => {
  'use strict';
  const forms = document.querySelectorAll('.needs-validation');
  Array.from(forms).forEach(form => {
    form.addEventListener('submit', event => {
      const isGuestLogin = event.submitter && event.submitter.name === 'guest_login';
      if (!isGuestLogin && !form.checkValidity()) {
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
