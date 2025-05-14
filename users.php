<?php
session_start();

if (!isset($_SESSION['user_name'])) {
    header("Location: login.php");
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
    <link href="/dara/css/users.css" rel="stylesheet">
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
                                    <i class="bi bi-pencil-fill"></i>
                                </button>

                                <!-- Delete Button -->
                                <button class="icon-box delete-icon" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $row['id']; ?>">
                                    <i class="fa-solid fa-trash"></i>
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
                                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>" />
                                            <div class="mb-3">
                                                <input type="text" name="name" class="form-control" id="editName<?php echo $row['id']; ?>" placeholder="Enter a new name" value="<?php echo $row['name']; ?>" required />
                                            </div>
                                            <div class="mb-3">
                                                <input type="text" name="username" class="form-control" id="editUsername<?php echo $row['id']; ?>" placeholder="Enter a new username" value="<?php echo $row['username']; ?>" required />
                                            </div>
                                            <div class="mb-3">
                                                <input type="password" name="password" class="form-control" id="editPassword<?php echo $row['id']; ?>" placeholder="Enter a new password" required />
                                            </div>
                                            <div class="mb-3">
                                                <input type="password" name="confirm_password" class="form-control" id="editConfirmPassword<?php echo $row['id']; ?>" placeholder="Confirm password" required />
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

        // Function to open the Edit User Modal and fill in the data
        function openEditUserModal(id, name, username, password) {
            document.getElementById('editName' + id).value = name;
            document.getElementById('editUsername' + id).value = username;
            document.getElementById('editPassword' + id).value = password;
        }

        // Password confirmation validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const password = document.getElementById('editPassword<?php echo $row['id']; ?>').value;
            const confirmPassword = document.getElementById('editConfirmPassword<?php echo $row['id']; ?>').value;
            
            if (password !== confirmPassword) {
                e.preventDefault(); // Prevent form submission
                alert('Passwords do not match. Please confirm the password correctly.');
            }
        });
    </script>
</body>
</html>
