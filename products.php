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

if (isset($_GET['lowstock']) && $_GET['lowstock'] == 1) {
  $sql = "SELECT products.*, categories.category AS category_name 
  FROM products 
  LEFT JOIN categories ON products.Category = categories.id 
  WHERE Stock <= $lowStockThreshold";

} else {
    $sql = "SELECT products.*, categories.category AS category_name 
    FROM products 
    LEFT JOIN categories ON products.Category = categories.id";

}

$result = mysqli_query($conn, $sql);

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
  <link href="/dara/products.css" rel="stylesheet" />
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

  <!-- Right side (View Low Stock Only & View All) -->
  <div class="mb-1 mt-2 me-2">
    <a href="products.php?lowstock=1" class="btn btn-warning me-1">View Low Stock Only</a>
    <a href="products.php" class="btn btn-secondary">View All</a>
  </div>
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
              $count = 1;
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
                <td><?php echo htmlspecialchars($row['Buying Price']); ?></td>
                <td><?php echo htmlspecialchars($row['Selling Price']); ?></td>
                <td><?php echo htmlspecialchars($row['Date Added']); ?></td>
                <td>
                  <a href="#" class="icon-box edit-icon" data-bs-toggle="modal" data-bs-target="#editProductModal" onclick="populateEditModal('<?php echo $row['id']; ?>', '<?php echo htmlspecialchars($row['Code']); ?>', '<?php echo htmlspecialchars($row['Description']); ?>', '<?php echo htmlspecialchars($row['Stock']); ?>', '<?php echo htmlspecialchars($row['Buying Price']); ?>', '<?php echo htmlspecialchars($row['Selling Price']); ?>', '<?php echo $row['category_name']; ?>')"
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

<!-- Edit Product Modal -->
<div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="editProductForm">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editProductModalLabel">Edit Product</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id" id="editProductIdInput">
          <div class="mb-3">
            <label class="form-label">Product Code</label>
            <input type="text" class="form-control" name="code" id="editCodeInput" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Description</label>
            <input type="text" class="form-control" name="description" id="editDescriptionInput" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Stock</label>
            <input type="number" class="form-control" name="stock" id="editStockInput" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Buying Price</label>
            <input type="number" step="0.01" class="form-control" name="buying_price" id="editBuyingPriceInput" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Markup %</label>
            <input type="number" step="0.01" class="form-control" name="markup" id="editMarkupInput" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Selling Price</label>
            <input type="number" step="0.01" class="form-control" name="selling_price" id="editSellingPriceInput" readonly>
          </div>
          <div class="mb-3">
            <label class="form-label">Category</label>
            <select class="form-select" name="category" id="editCategorySelect" required></select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary" id="saveProductBtn">Save Changes</button>
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





    function populateEditModal(id, code, description, stock, buyingPrice, sellingPrice, categoryId) {
    // Set the input fields with the provided product details
    document.getElementById('editProductIdInput').value = id;
    document.getElementById('editCodeInput').value = code;
    document.getElementById('editDescriptionInput').value = description;
    document.getElementById('editStockInput').value = stock;
    document.getElementById('editBuyingPriceInput').value = buyingPrice;
    document.getElementById('editSellingPriceInput').value = sellingPrice;

    // Calculate markup dynamically (Markup = (Selling Price - Buying Price) / Buying Price * 100)
    const markup = ((sellingPrice - buyingPrice) / buyingPrice) * 100;
    document.getElementById('editMarkupInput').value = markup.toFixed(); // Display markup

    // Fetch categories and populate the category dropdown
    fetch('getCategories.php')
        .then(response => response.json())
        .then(data => {
            if (data && data.categories && Array.isArray(data.categories)) {
                const categoryDropdown = document.getElementById('editCategorySelect');
                categoryDropdown.innerHTML = ''; // Clear existing options
                categoryDropdown.innerHTML = '<option value="">Select Category</option>'; // Default option

                data.categories.forEach(function (category) {
                    const isSelected = category.id == categoryId ? 'selected' : '';
                    categoryDropdown.innerHTML += `<option value="${category.id}" ${isSelected}>${category.category}</option>`;
                });
            } else {
                console.error('Invalid response format from getCategories.php');
            }
        })
        .catch(error => {
            console.error('Error fetching categories:', error);
        });

    // Show the modal
    const editModal = new bootstrap.Modal(document.getElementById('editProductModal'));
    editModal.show();
}

//sellling price adjustment
function calculateSellingPrice() {
    const buyingPrice = parseFloat(document.getElementById("editBuyingPriceInput").value) || 0;
    const markup = parseFloat(document.getElementById("editMarkupInput").value) || 0;
    const sellingPrice = buyingPrice + (buyingPrice * (markup / 100));
    document.getElementById("editSellingPriceInput").value = sellingPrice.toFixed(2);
  }

  document.getElementById("editBuyingPriceInput").addEventListener("input", calculateSellingPrice);
  document.getElementById("editMarkupInput").addEventListener("input", calculateSellingPrice);


// edit submit
$('#editProductForm').on('submit', function(e) {
    e.preventDefault();

    $.ajax({
        url: 'edit_product.php',
        type: 'POST',
        data: $(this).serialize(),
        success: function(response) {
            alert(response); // This is where "Invalid" is being alerted
        }
    });
});




   </script>
 </body>                                                       
</html>