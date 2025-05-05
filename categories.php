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
    <title>Category Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="icon" type="image/x-icon" href="img/daraa.ico">

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

    <!-- Main Content -->
    <div class="main-content">
      <div class="d-flex justify-content-between align-items-center">
        <h3>Category Management</h3>
      </div>

      <div class="card">
        <!-- Add Category Button -->
        <div>
          <button class="btn ms-2 mb-2 mt-2" data-bs-toggle="modal" data-bs-target="#addCategoryModal" style="background-color:#dc7a91;color:#fff;width:153px;height:37px;">
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
                <th scope="col">Action</th>
              </tr>
            </thead>
            <tbody id="categoryTableBody">
              <?php
                include "db_conn.php";
                $sql = "SELECT * FROM categories";
                $result = mysqli_query($conn, $sql);
                $count = 1;
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
                      <i class="fa-solid fa-trash"></i>
                    </a>
                  </td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
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
              <i class="bi bi-x-lg fa-remove" data-bs-dismiss="modal" style="cursor:pointer;"></i>
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
              <i class="bi bi-x-lg fa-remove" data-bs-dismiss="modal" style="cursor:pointer;"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
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

  const tableBody = document.getElementById("categoryTableBody"); // ← Correct ID
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
      document.getElementById("showEntries").addEventListener("input", updateTable);

      // Initial load
      window.addEventListener("DOMContentLoaded", updateTable);


    //will make the categories load on products
    function loadCategories() {
    fetch("getCategories.php")
      .then(response => response.json())
      .then(data => { // Access the response object
        const categories = data.categories; // Get categories array
        const dropdown = document.getElementById("categorySelect");
        dropdown.innerHTML = ""; // Clear existing options

        // Add default option
        const defaultOption = document.createElement("option");
        defaultOption.value = "";
        defaultOption.textContent = "Select Category";
        dropdown.appendChild(defaultOption);

        // Loop through the categories and create options
        categories.forEach(category => {
          const option = document.createElement("option");
          option.value = category.id; // Use the 'id' for the option value
          option.textContent = category.category; // Use 'category' for the option text
          dropdown.appendChild(option);
        });
      })
      .catch(error => {
        console.error("Error loading categories:", error);
      });
  }

  window.onload = function () {
    loadCategories();
    calculateSellingPrice(); // Recalculate selling price if needed
  };
  </script>
  </body>
</html>
