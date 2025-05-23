<?php
session_start();

if (!isset($_SESSION['user_name'])) {
    header("Location: login.php");
    exit();
}

// Include the database connection
include 'db_conn.php';

// Items per page (default to 7, override if set in query)
$itemsPerPage = isset($_GET['entries']) ? max(1, (int)$_GET['entries']) : 7;

// Get the current page, default to page 1
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Calculate the starting point (offset)
$offset = ($page - 1) * $itemsPerPage;

// Modify the SQL query to include LIMIT and OFFSET for pagination
$sql = "SELECT * FROM categories LIMIT $itemsPerPage OFFSET $offset";

// Execute the query to get the paginated categories
$result = mysqli_query($conn, $sql);

// Count the total number of rows in the categories table
$countSql = "SELECT COUNT(*) as total FROM categories";
$countResult = mysqli_query($conn, $countSql);
$totalRows = mysqli_fetch_assoc($countResult)['total'];
$totalPages = ceil($totalRows / $itemsPerPage);  // Calculate the total pages
?>


<!DOCTYPE html>
<html lang="en">
    <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Category Management</title>
    <link href="bootstrap-offline/css/bootstrap.css" rel="stylesheet">
    <link rel="stylesheet" href="bootstrap-icons/font/bootstrap-icons.css">
    <link rel="icon" type="image/x-icon" href="img/daraa.ico">
    <link href="css/categories.css" rel="stylesheet" />
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
          <li class="nav-item"><a class="nav-link" href="/dara/users.php"><i class="bi bi-person-circle"></i><span>User Information</span></a></li>
        </ul>
      </div>

      <!-- Main Content -->
      <div class="main-content">
        <div class="d-flex justify-content-between align-items-center">
          <h3>Category Management</h3>
        </div>

        <div class="card">
          <!-- Add Category Button -->
          <div>
            <button class="btn btn-add ms-2 mb-2 mt-2" data-bs-toggle="modal" data-bs-target="#addCategoryModal" style="background-color:#dc7a91;color:#fff;width:153px;height:37px;">
              <i class="bi bi-plus-lg"></i> Add Category    
            </button> 
          </div>

          <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <div>
              Show <input type="number" id="showEntries" value="7" class="form-control d-inline-block w-auto" /> Entries
            </div>
            <div>
              Search: <input type="text" id="searchInput" class="form-control d-inline-block w-auto" placeholder="Search" />
            </div>
          </div>

          <!-- Table -->
          <div class="card-body p-0">
            <table class="table category-table mb-0">
              <thead class="table-light">
                <tr>
                  <th scope="col">#</th>
                  <th scope="col">Category Name</th>
                  <th scope="col">Actions</th>
                </tr>
              </thead>
              <tbody id="categoryTableBody">
                <?php
                  $count = $offset + 1;
                  while ($row = mysqli_fetch_assoc($result)) {
                ?>
                  <tr>
                    <td><?php echo $count++; ?></td>
                    <td><?php echo htmlspecialchars($row['category']); ?></td>
                    <td>
                      <a href="#" class="icon-box edit-icon" data-bs-toggle="modal" data-bs-target="#editCategoryModal" onclick="populateEditModal('<?php echo $row['id']; ?>', '<?php echo htmlspecialchars($row['category']); ?>')">
                        <i class="bi bi-pencil-fill"></i>
                      </a>
                      <a href="delete_category.php?id=<?php echo $row['id']; ?>" class="icon-box delete-icon" onclick="return confirm('Are you sure you want to delete this category?');">
                        <i class="bi bi-trash-fill"></i>
                      </a>
                    </td>
                  </tr>
                <?php } ?>
              </tbody>
            </table>

            <!-- Pagination Controls -->
            <div class="d-flex justify-content-center mt-4">
              <ul class="pagination pagination-s my-custom-pagination">
                <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                  <a class="page-link" href="?page=<?php echo $page - 1; ?>&entries=<?php echo $itemsPerPage; ?>">Previous</a>
                </li>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                  <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>&entries=<?php echo $itemsPerPage; ?>"><?php echo $i; ?></a>
                  </li>
                <?php endfor; ?>
                <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                  <a class="page-link" href="?page=<?php echo $page + 1; ?>&entries=<?php echo $itemsPerPage; ?>">Next</a>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <!-- Add Category Modal -->
      <div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryLabel" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content box">
            <div class="group">
              <div class="overlap">
                <div class="rectangle"></div>
                <div class="div"></div>
                <div class="text-wrapper">Add Categories</div>
                <div class="form-group" style="position:absolute;top:100px;left:18px;width:530px;">
                  <input type="text" id="categoryInput" class="form-control" placeholder="Enter category name" />
                </div>
                <div class="rectangle-2"></div>
                <div class="close-button" data-bs-dismiss="modal" style="cursor:pointer;">
                  <div class="overlap-group">Close</div>
                </div>
                <button class="btn" id="saveCategoryBtn" style="position:absolute;top:252px;left:395px;background-color:#dc7a91;color:#fff;width:153px;height:51px;">
                  Save Category
                </button>
                <i class="bi bi-x-lg" data-bs-dismiss="modal" style="cursor:pointer;"></i>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Edit Category Modal -->
      <div class="modal fade" id="editCategoryModal" tabindex="-1" aria-labelledby="editCategoryLabel" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content box">
            <div class="group">
              <div class="overlap">
                <div class="rectangle"></div>
                <div class="div"></div>
                <div class="text-wrapper">Edit Category</div>
                <div class="form-group" style="position:absolute;top:100px;left:18px;width:530px;">
                  <input type="text" id="editCategoryInput" class="form-control" placeholder="Enter category name" />
                </div>
                <div class="rectangle-2"></div>
                <div class="close-button" data-bs-dismiss="modal" style="cursor:pointer;">
                  <div class="overlap-group">Close</div>
                </div>
                <button class="btn" id="saveEditCategoryBtn" style="position:absolute;top:252px;left:395px;background-color:#dc7a91;color:#fff;width:153px;height:51px;">
                  Save Changes
                </button>
                <i class="bi bi-x-lg" data-bs-dismiss="modal" style="cursor:pointer;"></i>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Scripts -->
      <script src="bootstrap-offline/js/bootstrap.bundle.min.js"></script>
      <script>
        function toggleSidebar() {
          const sidebar = document.getElementById("sidebarMenu");
          sidebar.classList.toggle("expand");
        }

        document.getElementById("saveCategoryBtn").addEventListener("click", function () {
          const category = document.getElementById("categoryInput").value.trim();
          if (!category) {
            alert("Please enter a category name.");
            return;
          }

          fetch("add_category.php", {
            method: "POST",
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: "category=" + encodeURIComponent(category)
          })
          .then(response => response.text())
          .then(data => {
            if (data.trim() === "success") {
              location.reload();
            } else {
              alert("Failed to add category.");
            }
          });
        });

        function populateEditModal(id, category) {
          document.getElementById('editCategoryInput').value = category;
          document.getElementById('saveEditCategoryBtn').setAttribute('data-category-id', id);
        }

        document.getElementById("saveEditCategoryBtn").addEventListener("click", function () {
          const categoryId = this.getAttribute('data-category-id');
          const category = document.getElementById("editCategoryInput").value.trim();
          if (!category) {
            alert("Please enter a category name.");
            return;
          }

          fetch("edit_category.php", {
            method: "POST",
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: "id=" + encodeURIComponent(categoryId) + "&category=" + encodeURIComponent(category)
          })
          .then(response => response.text())
          .then(data => {
            if (data.trim() === "success") {
              location.reload();
            } else {
              alert("Failed to update category.");
            }
          });
        });
      </script>
      <script>
        function updateTable() {
          const searchTerm = document.getElementById("searchInput").value.toLowerCase();
          let showCount = parseInt(document.getElementById("showEntries").value, 10) || 1;

          if (showCount < 1) showCount = 1;

          const tableBody = document.getElementById("categoryTableBody");
          const rows = tableBody.querySelectorAll("tr");

          let visibleCount = 0;

          rows.forEach(row => {
            const cells = row.querySelectorAll("td");
            const rowMatches = Array.from(cells).some(cell =>
              cell.textContent.toLowerCase().includes(searchTerm)
            );

            if (rowMatches && visibleCount < showCount) {
              row.style.display = "";
              visibleCount++;
            } else {
              row.style.display = "none";
            }
          });
        }

        document.getElementById("searchInput").addEventListener("input", updateTable);
        document.getElementById("showEntries").value = <?php echo $itemsPerPage; ?>;
        document.getElementById("showEntries").addEventListener("change", function () {
          const entries = this.value;
          const urlParams = new URLSearchParams(window.location.search);
          urlParams.set("entries", entries);
          urlParams.set("page", 1);
          window.location.search = urlParams.toString();
        });

        window.addEventListener("DOMContentLoaded", updateTable);

        function loadCategories() {
          fetch("getCategories.php")
            .then(response => response.json())
            .then(data => {
              const categories = data.categories;
              const dropdown = document.getElementById("categorySelect");
              dropdown.innerHTML = "";

              const defaultOption = document.createElement("option");
              defaultOption.value = "";
              defaultOption.textContent = "Select Category";
              dropdown.appendChild(defaultOption);

              categories.forEach(category => {
                const option = document.createElement("option");
                option.value = category.id;
                option.textContent = category.category;
                dropdown.appendChild(option);
              });
            })
            .catch(error => {
              console.error("Error loading categories:", error);
            });
        }

        window.onload = function () {
          loadCategories();
          calculateSellingPrice();
        };
      </script>
    </body>
</html>
