<?php
session_start();
include 'db_conn.php';

$user_id = $_SESSION['user_id'] ?? 1;
$sql = "SELECT * FROM users WHERE id = $user_id";
$result = $conn->query($sql);
$user = $result->fetch_assoc();

if (isset($_GET['msg'])) {
    $msg = $_GET['msg'];
    $reason = $_GET['reason'] ?? '';

    if ($msg === 'success') {
        echo '<div class="alert alert-success alert-dismissible fade show m-3" role="alert">
                Password changed successfully.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>';
    } elseif ($msg === 'error') {
        $errorMessage = "An error occurred.";

        switch ($reason) {
            case 'not_logged_in':
                $errorMessage = "You must be logged in.";
                break;
            case 'password_mismatch':
                $errorMessage = "New passwords do not match.";
                break;
            case 'user_not_found':
                $errorMessage = "User not found.";
                break;
            case 'old_wrong':
                $errorMessage = "Old password is incorrect.";
                break;
            case 'update_failed':
                $errorMessage = "Failed to update password.";
                break;
        }

        echo '<div class="alert alert-danger alert-dismissible fade show m-3" role="alert">'
            . htmlspecialchars($errorMessage) .
            '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>User Information</title>
    <link href="bootstrap-offline/css/bootstrap.css" rel="stylesheet">
    <link rel="stylesheet" href="bootstrap-icons/font/bootstrap-icons.css">
    <link href="css/users.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="img/daraa.ico">
  </head>
  <body>
    <!-- Navbar -->
    <nav class="navbar navbar-dark">
      <div class="container-fluid d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
          <img src="img/daralogo.svg" alt="Dara Logo" style="height: 40px;" />
          <button class="navbar-toggler text-white border-0 bg-transparent" type="button" onclick="toggleSidebar()">
            <i class="bi bi-list"></i>
          </button>
        </div>
        <!-- User -->
        <div class="dropdown">
          <a class="dropdown-toggle text-white text-decoration-none" href="#" role="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
            <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Guest'); ?>
          </a>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
            <li><a class="dropdown-item" href="logout.php">Logout</a></li>
          </ul>
        </div>
      </div>
    </nav>

    <!-- Wrapper (sidebar + content) -->
    <div class="wrapper" id="wrapper">
      <!-- Sidebar -->
      <div id="sidebarMenu" class="sidebar">
        <ul class="nav flex-column">
          <li class="nav-item"><a class="nav-link" href="/dara/dashboard.php"><i class="bi bi-house"></i><span>Home</span></a></li>
          <li class="nav-item"><a class="nav-link" href="/dara/categories.php"><i class="bi bi-grid"></i><span>Categories</span></a></li>
          <li class="nav-item"><a class="nav-link" href="/dara/products.php"><i class="bi bi-box-seam"></i><span>Products</span></a></li>
          <li class="nav-item"><a class="nav-link" href="/dara/sales.php"><i class="bi bi-currency-dollar"></i><span>Sales</span></a></li>
          <li class="nav-item"><a class="nav-link" href="/dara/sales-report.php"><i class="bi bi-bar-chart-line"></i><span>Sales Report</span></a></li>
          <li class="nav-item"><a class="nav-link" href="/dara/users.php"><i class="bi bi-person-circle"></i><span>User Information</span></a></li>
        </ul>
      </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Title -->
        <h4 class="mb-6 user-info-wrapper">User Information</h4>
      <div class="card p-5">


    <!-- Responsive avatar and button layout -->
    <div class="d-flex justify-content-between align-items-start flex-wrap">
      <div class="d-flex align-items-center">
        <div class="avatar me-4">
          <img src="<?php echo !empty($user['profile_image']) ? 'uploads/' . htmlspecialchars($user['profile_image']) : 'img/default-user.jpg'; ?>" alt="Profile" style="width: 140px; height: 140px; object-fit: cover; border-radius: 50%; border: 3px solid #e49da4;" />
        </div>
        <div class="user-info">
          <p><i class="bi bi-person-fill"></i> <strong>Name:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
          <p><i class="bi bi-person-vcard"></i> <strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
          <p><i class="bi bi-clock-history"></i> <strong>Last Login:</strong> <?php echo htmlspecialchars($user['last_login']); ?></p>
        </div>
      </div>

      <div>
        <a href="#" class="btn btn-sm btn-custom mt-3 mt-md-0" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
          <i class="bi bi-pencil-square"></i> Edit Password
        </a>
      </div>
    </div>

    <!-- Change Password Modal -->
    <div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <form id="changePasswordForm" class="modal-content" method="POST" action="change_password.php">
          <div class="modal-header">
            <h5 class="modal-title" id="changePasswordLabel">Change Password</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label for="oldPassword" class="form-label">Old Password</label>
              <input type="password" class="form-control" id="oldPassword" name="old_password" required />
            </div>
            <div class="mb-3">
              <label for="newPassword" class="form-label">New Password</label>
              <input type="password" class="form-control" id="newPassword" name="new_password" required />
            </div>
            <div class="mb-3">
              <label for="confirmNewPassword" class="form-label">Confirm New Password</label>
              <input type="password" class="form-control" id="confirmNewPassword" name="confirm_new_password" required />
            </div>
            <div id="passwordError" class="text-danger"></div>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-custom">Save</button>
          </div>
        </form>
      </div>
    </div>

    <script src="bootstrap-offline/js/bootstrap.bundle.min.js"></script>
    <script>
      function toggleSidebar() {
        const sidebar = document.getElementById("sidebarMenu");
        const wrapper = document.getElementById("wrapper");

        sidebar.classList.toggle("expand");
        wrapper.classList.toggle("sidebar-expanded");
      }
    </script>
    <script>
    // Validate new password confirmation before submit
    document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
      const newPassword = document.getElementById('newPassword').value;
      const confirmNewPassword = document.getElementById('confirmNewPassword').value;
      const errorDiv = document.getElementById('passwordError');

      if (newPassword !== confirmNewPassword) {
        e.preventDefault();
        errorDiv.textContent = "New passwords do not match.";
      } else {
        errorDiv.textContent = "";
      }
    });
  </script>
  </body>
</html>
