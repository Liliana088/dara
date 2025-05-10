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

$lowStockThreshold = 5;
// Items per page (default to 7, override if set in query)
$itemsPerPage = isset($_GET['entries']) ? max(1, (int)$_GET['entries']) : 7;

// Get the current page, default to page 1
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Calculate the starting point (offset)
$offset = ($page - 1) * $itemsPerPage;

// Modify the SQL query to include LIMIT and OFFSET for pagination
if (isset($_GET['lowstock']) && $_GET['lowstock'] == 1) {
  $sql = "SELECT products.*, categories.category AS category_name 
          FROM products 
          LEFT JOIN categories ON products.Category = categories.id 
          WHERE Stock <= $lowStockThreshold
          LIMIT $itemsPerPage OFFSET $offset";
} else {
  $sql = "SELECT products.*, categories.category AS category_name 
          FROM products 
          LEFT JOIN categories ON products.Category = categories.id
          LIMIT $itemsPerPage OFFSET $offset";
}


$result = mysqli_query($conn, $sql);

// Count the total number of rows in the table
$countSql = "SELECT COUNT(*) as total FROM products";
$countResult = mysqli_query($conn, $countSql);
$totalRows = mysqli_fetch_assoc($countResult)['total'];
$totalPages = ceil($totalRows / $itemsPerPage);  // Calculate the total pages


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
  <link rel="icon" type="image/x-icon" href="img/daraa.ico">
  <link href="/dara/css/products.css" rel="stylesheet" />
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
      <div class="d-flex justify-content-between align-items-center flex-wrap">
  <!-- Left side (Add Product) -->
  <button class="btn ms-2 mb-2 mt-2" data-bs-toggle="modal" data-bs-target="#addProductModal" style="background-color:#dc7a91;color:#fff;width:153px;height:37px;">
    <i class="bi bi-plus-lg"></i> Add Product    
  </button>

  <!-- Right sid  e (View Low Stock Only & View All) -->
  <div class="mb-1 mt-2 me-2">
    <a href="products.php?lowstock=1" class="btn custom-lowstock-btn me-1">View Low Stock Only</a>
    <a href="products.php" class="btn custom-viewall-btn">View All</a>
  </div>
</div>

      <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <div>
          Show <input type="number" id="showEntries" value="10" class="form-control d-inline-block w-auto" /> Entries
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
              <th scope="col">Cost</th>
              <th scope="col">Markup %</th>
              <th scope="col">Date Added</th>
              <th scope="col">Action</th>
            </tr>
          </thead>
          <tbody id="productTableBody">
            <?php
              $count = $offset + 1;
              while ($row = mysqli_fetch_assoc($result)) {

                $stock = (int)$row['Stock'];
                $lowStock = $stock <= 5;
            ?>
              <tr class="<?php echo $lowStock ? 'table-warning' : ''; ?>">
                <td><?php echo $count++; ?></td>
                <td><?php echo htmlspecialchars($row['Code']); ?></td>
                <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                <td><?php echo htmlspecialchars($row['Description']); ?></td>
                <td>
                  <?php echo $stock; ?>
                  <?php if ($lowStock): ?>
                    <span class="badge bg-danger">Low</span>
                  <?php endif; ?>
                </td>
                <td><?php echo htmlspecialchars($row['cost']); ?></td>
                <td><?php echo htmlspecialchars($row['markup']). '%'; ?></td>
                <td><?php echo htmlspecialchars($row['Date Added']); ?></td>
                <td>
                  <a href="#" class="icon-box edit-icon" data-bs-toggle="modal" data-bs-target="#editProductModal" onclick="populateEditModal('<?php echo $row['id']; ?>', '<?php echo htmlspecialchars($row['Code']); ?>', '<?php echo htmlspecialchars($row['Description']); ?>', '<?php echo htmlspecialchars($row['Stock']); ?>', '<?php echo htmlspecialchars($row['cost']); ?>', '<?php echo htmlspecialchars($row['markup']); ?>', '<?php echo $row['Category']; ?>')"
                  >
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

        <!-- Pagination Controls -->
        <div class="d-flex justify-content-center mt-4">
            <ul class="pagination pagination-s my-custom-pagination">
                <!-- Previous Button -->
                <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page - 1; ?>&entries=<?php echo $itemsPerPage; ?><?php echo isset($_GET['lowstock']) ? '&lowstock=1' : ''; ?>">Previous</a>
                </li>
                
                <!-- Page Numbers -->
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>&entries=<?php echo $itemsPerPage; ?><?php echo isset($_GET['lowstock']) ? '&lowstock=1' : ''; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>

                <!-- Next Button -->
                <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page + 1; ?>&entries=<?php echo $itemsPerPage; ?>">Next</a>
                </li>
            </ul>
        </div>
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
            <label class="form-label">Cost</label>
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

<!-- Edit Product Modal -->
<div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="editProductForm">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editProductModalLabel">Edit Product</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id" id="editId">
          <div class="mb-3">
            <label class="form-label">Product Code</label>
            <input type="text" class="form-control" name="code" id="editCode" readonly required>
          </div>
          <div class="mb-3">
            <label class="form-label">Description</label>
            <input type="text" class="form-control" name="description" id="editDescription" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Stock</label>
            <input type="number" class="form-control" name="stock" id="editStock" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Cost</label>
            <input type="number" step="0.01" class="form-control" name="buying_price" id="editBuyingPrice" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Markup %</label>
            <input type="number" step="0.01" class="form-control" name="markup" id="editMarkup" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Selling Price</label>
            <input type="number" step="0.01" class="form-control" name="selling_price" id="editSellingPrice" readonly>
          </div>
          <div class="mb-3">
            <label class="form-label">Category</label>
            <select class="form-select" name="category" id="editCategorySelect" required></select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary"  id="saveEditBtn">Save Changes</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // Filter and limit table rows
    function updateTable() {
    const searchTerm = document.getElementById("searchInput").value.toLowerCase();
    let showCount = parseInt(document.getElementById("showEntries").value, 10) || 1;

    if (showCount < 1) showCount = 1;

    const tableBody = document.getElementById("productTableBody"); // Correct ID
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

    // Add event listeners for input elements
    document.getElementById("searchInput").addEventListener("input", updateTable);
    document.getElementById("showEntries").value = <?php echo $itemsPerPage; ?>;
    document.getElementById("showEntries").addEventListener("change", function () {
    const entries = this.value;
    const urlParams = new URLSearchParams(window.location.search);
    urlParams.set("entries", entries);
    urlParams.set("page", 1); // Reset to first page on entries change
    window.location.search = urlParams.toString();
});

    // Initial load
    window.addEventListener("DOMContentLoaded", updateTable);

    // Toggle Sidebar
    function toggleSidebar() {
    const sidebar = document.getElementById("sidebarMenu");
    sidebar.classList.toggle("expand");
    }


    // Fetch categories from the database and populate the dropdown in the "Add Product" modal
    $(document).ready(function() {

      let cachedCategories = [];

$.ajax({
    url: 'getCategories.php',
    type: 'GET',
    dataType: 'json',
    success: function(response) {
        if (response && Array.isArray(response.categories)) {
            cachedCategories = response.categories;

            const addSelect = $('#categorySelect');
            const editSelect = $('#editCategorySelect');

            addSelect.empty().append('<option value="">Select Category</option>');
            editSelect.empty().append('<option value="">Select Category</option>');

            response.categories.forEach(function(category) {
                const option = `<option value="${category.id}">${category.category}</option>`;
                addSelect.append(option);
                editSelect.append(option);
            });
        }
    },
    error: function(xhr, status, error) {
        console.log('Error fetching categories: ' + error);
    }
});


        // Handle product saving
        document.getElementById("saveProductBtn").addEventListener("click", function () {
            const category = document.getElementById("categorySelect").value; // Get selected category ID from the dropdown
            const code = document.getElementById("codeInput").value.trim();
            const description = document.getElementById("descriptionInput").value.trim();
            const stock = document.getElementById("stockInput").value.trim();
            const buyingPrice = document.getElementById("buyingPriceInput").value.trim();
            const markup = document.getElementById("markupInput").value.trim();

            // Validate the inputs
            if (!category || !code || !description || !stock || !buyingPrice || !markup) {
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
                markup: markup
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
    
    // Handle edit submit
    $('#editProductForm').on('submit', function(e) {
    e.preventDefault();

    $.ajax({
        url: 'edit_product.php',
        type: 'POST',
        data: $(this).serialize(),
        success: function(response) {
        alert(response); // Alert the response from the server
        }
    });
    });
</script>
<script>
function populateEditModal(id, code, description, stock, cost, markup, categoryId) {
  // Set field values
  document.getElementById('editId').value = id;
  document.getElementById('editCode').value = code;
  document.getElementById('editDescription').value = description;
  document.getElementById('editStock').value = stock;
  document.getElementById('editBuyingPrice').value = cost;
  document.getElementById('editMarkup').value = markup;

  var sellingPrice = parseFloat(cost) + (parseFloat(cost) * parseFloat(markup) / 100);
  document.getElementById('editSellingPrice').value = sellingPrice.toFixed(2);

  // Fetch categories, then set the selected value
  $.ajax({
    url: 'getCategories.php',
    type: 'GET',
    dataType: 'json',
    success: function(response) {
      if (response && response.categories && Array.isArray(response.categories)) {
        const categoryDropdown = $('#editCategorySelect');
        categoryDropdown.empty();
        categoryDropdown.append('<option value="">Select Category</option>');

        response.categories.forEach(function(cat) {
          const selected = cat.id == categoryId ? 'selected' : '';
          categoryDropdown.append(`<option value="${cat.id}" ${selected}>${cat.category}</option>`);
        });
      } else {
        console.error('Invalid response format from getCategories.php');
      }
    },
    error: function(xhr, status, error) {
      console.log('Error fetching categories: ' + error);
    }
  });

  const editModal = new bootstrap.Modal(document.getElementById('editProductModal'));
  editModal.show();
}

 
  // Handle Selling Price Calculation for Add Product
  document.getElementById('buyingPriceInput').addEventListener('input', calculateSellingPrice);
  document.getElementById('markupInput').addEventListener('input', calculateSellingPrice);

  function calculateSellingPrice() {
    var buyingPrice = parseFloat(document.getElementById('buyingPriceInput').value) || 0;
    var markup = parseFloat(document.getElementById('markupInput').value) || 0;

    var sellingPrice = buyingPrice + (buyingPrice * markup / 100);
    document.getElementById('sellingPriceInput').value = sellingPrice.toFixed(2);
  }

  // Handle Selling Price Calculation for Edit Product
  document.getElementById('editBuyingPrice').addEventListener('input', calculateEditSellingPrice);
  document.getElementById('editMarkup').addEventListener('input', calculateEditSellingPrice);

  function calculateEditSellingPrice() {
    var buyingPrice = parseFloat(document.getElementById('editBuyingPrice').value) || 0;
    var markup = parseFloat(document.getElementById('editMarkup').value) || 0;

    var sellingPrice = buyingPrice + (buyingPrice * markup / 100);
    document.getElementById('editSellingPrice').value = sellingPrice.toFixed(2);
  }

  document.getElementById("saveEditBtn").addEventListener("click", function () {
    const id = document.getElementById("editId").value.trim();
    const category = document.getElementById("editCategorySelect").value;
    const code = document.getElementById("editCode").value.trim();
    const description = document.getElementById("editDescription").value.trim();
    const stock = document.getElementById("editStock").value.trim();
    const buyingPrice = document.getElementById("editBuyingPrice").value.trim();
    const markup = document.getElementById("editMarkup").value.trim();

    // Validate the inputs
    if (!category || !code || !description || !stock || !buyingPrice || !markup) {
        alert("Please fill in all fields.");
        return;
    }

    const xhr = new XMLHttpRequest();
    xhr.open("POST", "edit_product.php", true); // Ensure this is the correct PHP file for editing
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    const params = new URLSearchParams({
        id: id,
        category: category,
        code: code,
        description: description,
        stock: stock,
        buying_price: buyingPrice,
        markup: markup
    });

    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            const response = xhr.responseText;
            console.log(response); // Debugging server response
            if (response === "success") {
                location.reload(); // Refresh the page after successful update to reflect the changes
            } else {
                alert(response); // Show error message from the server
            }
        }
    };

    xhr.send(params.toString());
});

  
</script>
</body>
</html>
