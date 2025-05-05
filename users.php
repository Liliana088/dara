<?php
session_start();

if (!isset($_SESSION['user_name'])) {
    header("Location: login.php");
    exit();
}

// Block guest access
if ($_SESSION['user_name'] === 'Guest') {
  header("Location: dashboard.php");
  exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>User Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <style>
        body {
        background-color: #f4f4f4;
        margin: 0;
        padding: 0;
      }

      .navbar {
        background-color: #393E75;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 1100;
        height: 56px;
      }

      .navbar .navbar-brand {
        display: flex;
        align-items: center;
      }

      .navbar-brand img.logo {
        height: 40px;
        margin-left: 10px;
      }

      .navbar-toggler {
        font-size: 1.3rem;
        margin-left: auto;
      }

      .sidebar {
        position: fixed;
        top: 56px;
        left: 0;
        height: calc(100vh - 56px);
        width: 60px;
        background-color: #5c47a1;
        transition: width 0.3s ease;
        overflow-x: hidden;
        z-index: 1000;
      }

      .sidebar.expand {
        width: 200px;
      }

      .sidebar .nav-link {
        color: #fff;
        display: flex;
        align-items: center;
        padding: 12px 15px;
      }

      .sidebar .nav-link i {
        font-size: 1.2rem;
        width: 24px;
        text-align: center;
      }

      .sidebar .nav-link span {
        display: none;
        margin-left: 10px;
        white-space: nowrap;
      }

      .sidebar.expand .nav-link span {
        display: inline;
      }

      .main-content {
        margin-left: 60px;
        padding: 80px 20px 20px 20px;
        transition: margin-left 0.3s ease;
      }

      .sidebar.expand ~ .main-content {
        margin-left: 200px;
      }

      .card {
        margin-top: 20px;
      }

      .category-table th,
      .category-table td {
        vertical-align: middle;
      }

      @media (max-width: 991px) {
        .sidebar {
          width: 0;
        }

        .sidebar.expand {
          width: 200px;
        }

        .main-content {
          margin-left: 0;
        }

        .sidebar.expand ~ .main-content {
          margin-left: 200px;
        }
      }

      /* icon buttons */
      .icon-box {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        font-size: 16px;
        color: white;
        text-decoration: none;
        border-radius: 4px;
      }

      .edit-icon {
        background-color: #A9CCE9;
      }

      .delete-icon {
        background-color: #AF0F0F;
      }

      /* modal styling from design */
      .box {
        width: 570px;
        height: 318px;
      }

      .box .group {
        position: relative;
        width: 100%;
        height: 100%;
      }

      .box .overlap {
        position: relative;
        width: 100%;
        height: 100%;
      }

      .rectangle {
        position: absolute;
        width: 100%;
        height: 100%;
        background-color: #ffbbb5;
      }

      .div {
        position: absolute;
        width: 100%;
        height: 61px;
        background-color: #dc7a91;
      }

      .text-wrapper {
        position: absolute;
        top: 16px;
        left: 18px;
        font-family: "Inter", sans-serif;
        color: #ffffff;
        font-size: 24px;
      }

      .rectangle-2 {
        position: absolute;
        width: 100%;
        height: 77px;
        bottom: 0;
        background-color: #ffbbb5;
        border-top: 1px solid #b1b1b1;
      }

      .close-button .overlap-group {
        background-color: #FE978E;
        border: 1px solid #ccc;
        border-radius: 5px;
        text-align: center;
        line-height: 45px;
        font-size: 18px;
        color:rgb(240, 233, 233);
      }

      .close-button {
        position: absolute;
        left: 18px;
        bottom: 13px;
        width: 84px;
        height: 51px;
      }

      .fa-remove {
        position: absolute;
        top: 18px;
        right: 18px;
        font-size: 20px;
        color: #ffffff;
      }

      .modal-dialog {
        margin-top: 80px; /* adjustment of modal */
      }

      /* Custom modal header color */
        .custom-header {
        background-color: #DC7A91; /* Modal header color */
        color: white; /* White text color */
        }

        /* Custom modal body color */
        .custom-body {
        background-color: #FFBBB5; /* Modal body color */
        }

        /* Custom button color */
        .btn-custom {
        background-color: #8E588A; /* Save user button color */
        color: white; /* White text on button */
        }

        .btn-custom:hover {
        background-color: #7A4874; /* Darker shade on hover */
        }

    </style>
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
            <div class="dropdown">
                <a class="dropdown-toggle text-white text-decoration-none" href="#" role="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <?php echo $_SESSION['user_name']; ?>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                    <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Sidebar -->
    <div id="sidebarMenu" class="sidebar">
        <ul class="nav flex-column">
          <li class="nav-item"><a class="nav-link" href="/dara/dashboard.php"><i class="bi bi-house"></i><span>Home</span></a></li>
          <li class="nav-item"><a class="nav-link" href="/dara/categories.php"><i class="bi bi-grid"></i><span>Categories</span></a></li>
          <li class="nav-item"><a class="nav-link" href="/dara/products.php"><i class="bi bi-box-seam"></i><span>Products</span></a></li>
          <li class="nav-item"><a class="nav-link" href="/dara/sales.php"><i class="bi bi-currency-dollar"></i><span>Sales</span></a></li>
          <li class="nav-item"><a class="nav-link" href="/dara/sales-report.php"><i class="bi bi-bar-chart-line"></i><span>Sales Report</span></a></li>
          <li class="nav-item"><a class="nav-link" href="/dara/users.php"><i class="bi bi-people"></i><span>User Management</span></a></li>
        </ul>
    </div>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <h3>User Management</h3>
        <div class="card">
            <!-- Add User Button -->
            <div>
                <button class="btn ms-2 mb-2 mt-2" data-bs-toggle="modal" data-bs-target="#addUserModal" style="background-color:#dc7a91;color:#fff;width:153px;height:37px;">
                    <i class="bi bi-plus-lg"></i> Add User
                </button>
            </div>

            <div class="card-body p-0">
                <table class="table category-table mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Name</th>
                            <th scope="col">Username</th>
                            <th scope="col">Status</th>
                            <th scope="col">Last Login</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="userTableBody">
                        <?php
                        include "db_conn.php";
                        $sql = "SELECT * FROM users";
                        $result = mysqli_query($conn, $sql);
                        $count = 1;
                        while ($row = mysqli_fetch_assoc($result)) {
                        ?>
                        <tr>
                            <th scope="row"><?php echo $count++; ?></th>
                            <td><?php echo $row['name']; ?></td>
                            <td><?php echo $row['username']; ?></td>
                            <td><?php echo $row['Status']; ?></td>
                            <td><?php echo $row['last_login']; ?></td>
                            <td>
                                <!-- Edit Button -->
                                <button class="icon-box edit-icon" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $row['id']; ?>" onclick="openEditUserModal('<?php echo $row['id']; ?>', '<?php echo $row['name']; ?>', '<?php echo $row['username']; ?>', '<?php echo $row['password']; ?>')">
                                    <i class="bi bi-pencil-square"></i>
                                </button>

                                <!-- Delete Button -->
                                <button class="icon-box delete-icon" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $row['id']; ?>">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>

                        <!-- Edit User Modal -->
                        <div class="modal fade" id="editModal<?php echo $row['id']; ?>" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header custom-header">
                                        <h5 class="modal-title" id="editModalLabel">Edit User</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body custom-body">
                                        <form action="edit_user.php" method="POST">
                                            <div class="mb-3">
                                                <input type="text" name="name" class="form-control" id="editName<?php echo $row['id']; ?>" placeholder="Enter a new name" value="<?php echo $row['name']; ?>" required />
                                            </div>
                                            <div class="mb-3">
                                                <input type="text" name="username" class="form-control" id="editUsername<?php echo $row['id']; ?>" placeholder="Enter a new username" value="<?php echo $row['username']; ?>" required />
                                            </div>
                                            <div class="mb-3">
                                                <input type="password" name="password" class="form-control" id="editPassword<?php echo $row['id']; ?>" placeholder="Enter a new password" required />
                                            </div>
                                            <div class="text-center">
                                                <button type="submit" class="btn btn-custom">Save Changes</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Delete Modal -->
                        <div class="modal fade" id="deleteModal<?php echo $row['id']; ?>" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p>Are you sure you want to delete this user?</p>
                                    </div>
                                    <div class="modal-footer">
                                        <form action="delete_user.php" method="POST">
                                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>" />
                                            <button type="submit" class="btn btn-danger">Delete</button>
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header custom-header">
                    <h5 class="modal-title" id="addUserModalLabel">Add New User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body custom-body">
                    <form action="add_user.php" method="POST">
                        <div class="mb-3">
                            <input type="text" name="name" class="form-control" placeholder="Name" required />
                        </div>
                        <div class="mb-3">
                            <input type="text" name="username" class="form-control" placeholder="Username" required />
                        </div>
                        <div class="mb-3">
                            <input type="password" name="password" class="form-control" placeholder="Password" required />
                        </div>
                        <div class="text-center">
                            <button type="submit" class="btn btn-custom">Add User</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle sidebar function
        function toggleSidebar() {
            document.getElementById('sidebarMenu').classList.toggle('expand');
        }

        // Function to open the Edit User Modal and fill in the data
        function openEditUserModal(id, name, username, password) {
            document.getElementById('editName' + id).value = name;
            document.getElementById('editUsername' + id).value = username;
            document.getElementById('editPassword' + id).value = password;
        }
    </script>
</body>
</html>
