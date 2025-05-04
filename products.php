<?php
include "db_conn.php";

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
  <title>Product Management</title>
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
      background-color: #fff;
      border: 1px solid #ccc;
      border-radius: 5px;
      text-align: center;
      line-height: 51px;
      font-size: 18px;
      color: #0e0d0d;
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

    .modal-dialog {
      max-width: 570px;
    }
    .modal-content {
      height: 810px;
      background-color: #fff;
      border-radius: 0;
      position: relative;
    }

    .modal-header {
      background-color: #dc7a91;
      border-bottom: none;
    }

    .modal-title {
      color: white;
      font-size: 24px;
    }

    .btn-close-white {
      background: none;
      border: none;
      color: white;
      font-size: 1.3rem;
    }

    .form-control, .form-select {
      border-color: #b1b1b1;
    }

    .bottom-bar {
      position: absolute;
      bottom: 0;
      left: 0;
      width: 100%;
      background-color: #ffbbb5;
      border-top: 1px solid #b1b1b1;
      padding: 10px 20px;
      display: flex;
      justify-content: space-between;
    }

    .btn-save {
      background-color: #dc7a91;
      color: #fff;
      border: none;
    }

    .btn-close-modal {
      background-color: #fff;
      border: 1px solid #000;
      color: #000;
    }

    .input-group-icon {
      position: relative;
    }

    .input-group-icon i {
      position: absolute;
      right: 15px;
      top: 50%;
      transform: translateY(-50%);
      color: #999;
    }

    .form-section {
      padding: 20px;
      padding-bottom: 120px;
      overflow-y: auto;
      height: 100%;
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
      <h3>Product Management</h3>
    </div>

    <div class="card">
      <!-- Add Product Button -->
      <div>
        <button class="btn btn-danger ms-2 mb-2 mt-2" data-bs-toggle="modal" data-bs-target="#addProductModal">
          <i class="bi bi-plus-lg"></i> Add Product    
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
        <table class="table product-table mb-0">
          <thead class="table-light">
            <tr>
              <th scope="col">#</th>
              <th scope="col">Code</th>
              <th scope="col">Category</th>
              <th scope="col">Description</th>
              <th scope="col">Stock</th>
              <th scope="col">Buying Price</th>
              <th scope="col">Selling Price</th>
              <th scope="col">Date Added</th>
              <th scope="col">Action</th>
            </tr>
          </thead>
          <tbody id="productTableBody">
            <?php
              $sql = "SELECT * FROM products";
              $result = mysqli_query($conn, $sql);
              $count = 1;
              while ($row = mysqli_fetch_assoc($result)) {
            ?>
              <tr>
                <td><?php echo $count++; ?></td>
                <td><?php echo htmlspecialchars($row['Code']); ?></td>
                <td><?php echo htmlspecialchars($row['Category']); ?></td>
                <td><?php echo htmlspecialchars($row['Description']); ?></td>
                <td><?php echo htmlspecialchars($row['Stock']); ?></td>
                <td><?php echo htmlspecialchars($row['Buying Price']); ?></td>
                <td><?php echo htmlspecialchars($row['Selling Price']); ?></td>
                <td><?php echo htmlspecialchars($row['Date Added']); ?></td>
                <td>
                  <a href="#" class="icon-box edit-icon" data-bs-toggle="modal" data-bs-target="#editProductModal" onclick="populateEditModal('<?php echo $row['id']; ?>', '<?php echo htmlspecialchars($row['Code']); ?>', '<?php echo htmlspecialchars($row['Description']); ?>', '<?php echo htmlspecialchars($row['Stock']); ?>', '<?php echo htmlspecialchars($row['Buying Price']); ?>', '<?php echo htmlspecialchars($row['Selling Price']); ?>')">
                    <i class="bi bi-pencil-fill"></i>
                  </a>
                  <a href="delete_product.php?id=<?php echo $row['id']; ?>" class="icon-box delete-icon" onclick="return confirm('Are you sure you want to delete this product?');">
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

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="addProductForm">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Add Product</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Product Code</label>
            <input type="text" class="form-control" name="code" id="codeInput" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Description</label>
            <input type="text" class="form-control" name="description" id="descriptionInput" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Stock</label>
            <input type="number" class="form-control" name="stock" id="stockInput" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Buying Price</label>
            <input type="number" step="0.01" class="form-control" name="buying_price" id="buyingPriceInput" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Markup %</label>
            <input type="number" step="0.01" class="form-control" name="markup" id="markupInput" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Selling Price</label>
            <input type="number" step="0.01" class="form-control" name="selling_price" id="sellingPriceInput" readonly>
          </div>
          <div class="mb-3">
            <label class="form-label">Category</label>
            <select class="form-select" name="category" id="categorySelect" required></select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary" id="saveProductBtn">Save Product</button>
        </div>
      </div>
    </form>
  </div>
</div>


<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
// Filter and limit table rows
function updateTable() {
  const searchTerm = document.getElementById("searchInput").value.toLowerCase();
  let showCount = parseInt(document.getElementById("showEntries").value, 10) || 1;

  if (showCount < 1) showCount = 1;

  const tableBody = document.getElementById("productTableBody"); // ← Correct ID
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


    document.getElementById("searchInput").addEventListener("input", updateTable);
    document.getElementById("showEntries").addEventListener("input", updateTable);

    // Initial load
    window.addEventListener("DOMContentLoaded", updateTable);
   
    // Toggle Sidebar
    function toggleSidebar() {
        const sidebar = document.getElementById("sidebarMenu");
        sidebar.classList.toggle("expand");
    }

    // Adjust selling price based on markup
    const markupInput = document.getElementById('markupInput');
    const buyingPriceInput = document.getElementById('buyingPriceInput');
    const sellingPriceInput = document.getElementById('sellingPriceInput');

    markupInput.addEventListener('input', () => {
        const markupPercentage = parseFloat(markupInput.value) || 0;
        const buyingPrice = parseFloat(buyingPriceInput.value) || 0;

        if (buyingPrice > 0) {
            const sellingPrice = buyingPrice + (buyingPrice * (markupPercentage / 100));
            sellingPriceInput.value = sellingPrice.toFixed(2);
        } else {
            sellingPriceInput.value = '';
        }
    });

    buyingPriceInput.addEventListener('input', () => {
        const markupPercentage = parseFloat(markupInput.value) || 0;
        const buyingPrice = parseFloat(buyingPriceInput.value) || 0;

        if (buyingPrice > 0) {
            const sellingPrice = buyingPrice + (buyingPrice * (markupPercentage / 100));
            sellingPriceInput.value = sellingPrice.toFixed(2);
        } else {
            sellingPriceInput.value = '';
        }
    });

    // Fetch categories from the database and populate the dropdown
    $(document).ready(function() {

        // Using AJAX for category population
        $.ajax({
    url: 'getCategories.php',
    type: 'GET',
    dataType: 'json',
    success: function(response) {
        console.log(response);  // Log the response to check the data
        if (response && response.categories && Array.isArray(response.categories)) {
            const categoryDropdown = $('#categorySelect');
            categoryDropdown.empty(); // Clear existing options
            categoryDropdown.append('<option value="">Select Category</option>'); // Default option

            response.categories.forEach(function(category) {
                categoryDropdown.append(`<option value="${category.id}">${category.category}</option>`);
            });
        } else {
            console.error('Invalid response format from getCategories.php');
        }
    },
    error: function(xhr, status, error) {
        console.log('Error fetching categories: ' + error);
    }
});


        // Handle product saving
        document.getElementById("saveProductBtn").addEventListener("click", function () {
            const category = document.getElementById("categorySelect").value;
            const code = document.getElementById("codeInput").value.trim();
            const description = document.getElementById("descriptionInput").value.trim();
            const stock = document.getElementById("stockInput").value.trim();
            const buyingPrice = document.getElementById("buyingPriceInput").value.trim();
            const sellingPrice = document.getElementById("sellingPriceInput").value.trim();

            if (!category || !code || !description || !stock || !buyingPrice || !sellingPrice) {
                alert("Please fill in all fields.");
                return;
            }

            const xhr = new XMLHttpRequest();
            xhr.open("POST", "insert_product.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

            const params = new URLSearchParams({
                category: category,
                code: code,
                description: description,
                stock: stock,
                buying_price: buyingPrice,
                selling_price: sellingPrice
            });

            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    alert(xhr.responseText);
                    location.reload(); // Refresh the page to show the new product
                }
            };

            xhr.send(params.toString());
        });
    });
   </script>
 </body>                                                       
</html>