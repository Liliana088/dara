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

include "db_conn.php";
//insert ung products sa dropdown sa add sales
$productQuery = "SELECT id, Description,Selling_Price FROM products";
$productResult = $conn->query($productQuery);

$products = [];
if ($productResult->num_rows > 0) {
    while ($row = $productResult->fetch_assoc()) {
        $products[] = $row;
    }
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $seller = $_POST['seller'];
    $payment_method = $_POST['payment_method'];
    $subtotal = $_POST['subtotal'];
    $markup = $_POST['markup'];
    $total_cost = $_POST['total_cost'];
    $date = $_POST['date'];

    $stmt = $conn->prepare("INSERT INTO sales (seller, payment_method, subtotal, markup, total_cost, date) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssddds", $seller, $payment_method, $subtotal, $markup, $total_cost, $date);
    $stmt->execute();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <title>Sales Management</title>
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
        <h3>Sales Management</h3>
      </div>

      <div class="card">
        <!-- Add Sale Button -->
        <div>
            <button class="btn ms-2 mb-2 mt-2" data-bs-toggle="offcanvas" data-bs-target="#addSaleSidebar" style="background-color:#dc7a91;color:#fff;width:153px;height:37px;">
                <i class="bi bi-plus-lg"></i> Add Sale
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
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Seller</th>
                    <th>Payment Method</th>
                    <th>Subtotal</th>
                    <th>Markup</th>
                    <th>Total Cost</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT * FROM sales ORDER BY date DESC";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>{$row['id']}</td>
                                <td>{$row['seller']}</td>
                                <td>{$row['payment_method']}</td>
                                <td>{$row['subtotal']}</td>
                                <td>{$row['markup']}</td>
                                <td>{$row['total_cost']}</td>
                                <td>{$row['date']}</td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='7'>No sales records found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- Add Sale Sidebar (Offcanvas) -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="addSaleSidebar" aria-labelledby="addSaleSidebarLabel">
    <div class="offcanvas-header" style="background-color: #dc7a91;">
        <h5 class="offcanvas-title text-white" id="addSaleSidebarLabel">Add Sale</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body" style="background-color: #ffbbb5;">
        <form method="POST" action="">
        <div class="mb-3">
            <label class="form-label">Seller</label>
            <input type="text" name="seller" class="form-control" value="<?php echo $_SESSION['user_name']; ?>" readonly />
        </div>

        <!-- Products Container -->
        <div id="productContainer">
        <div class="mb-3 d-flex align-items-center gap-2 product-row">
            <select class="form-select product-name" style="max-width: 200px;">
            <?php foreach($products as $product): ?>
                <option value="<?= $product['Selling_Price']; ?>"><?= htmlspecialchars($product['Description']); ?></option>
            <?php endforeach; ?>

            <!-- Add other options with values as prices -->
            </select>
            <input type="number" class="form-control quantity" value="1" min="1" style="width: 70px;">
            <span>₱</span>
            <input type="text" class="form-control product-total" value="12.00" style="width: 100px;" readonly>
        </div>
        </div>

        <!-- Add Product Button -->
        <div class="mb-3">
        <button type="button" class="btn btn-light" id="addProductBtn" style="background-color: #dc7a91; color: white;">+ Add Product</button>
        </div>


        <!-- Total -->
        <div class="mb-3">
        <label class="form-label">Total</label>
        <input type="text" id="totalCost" name="total_cost" class="form-control" readonly />
        </div>

        <!-- Cash Section -->
        <div class="row mb-3">
        <div class="col">
            <label class="form-label">Cash Received</label>
            <input type="number" id="cashReceived" name="received" class="form-control">
        </div>
        <div class="col">
            <label class="form-label">Change</label>
            <input type="text" id="changeDue" name="change" class="form-control" readonly>
        </div>
        </div>


        <input type="hidden" name="subtotal" value="0">
        <input type="hidden" name="markup" value="0">
        <input type="hidden" name="payment_method" value="Cash">
        <input type="hidden" name="date" value="<?php echo date('Y-m-d'); ?>">

        <button type="submit" class="btn" style="background-color: #dc7a91; color: white; width: 100%;">Save Sale</button>
        </form>
    </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
      function toggleSidebar() {
        const sidebar = document.getElementById("sidebarMenu");
        sidebar.classList.toggle("expand");
      }

// Filter and limit table rows
function updateTable() {
  const searchTerm = document.getElementById("searchInput").value.toLowerCase();
  let showCount = parseInt(document.getElementById("showEntries").value, 10) || 1;

  if (showCount < 1) showCount = 1;

  const tableBody = document.querySelector("table tbody"); // Correct selection of table body
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

   
    </script>
    <script>
function updateTotals() {
  let total = 0;

  document.querySelectorAll(".product-row").forEach(row => {
    const price = parseFloat(row.querySelector(".product-name").value) || 0;
    const quantity = parseInt(row.querySelector(".quantity").value) || 1;
    const productTotal = price * quantity;
    row.querySelector(".product-total").value = productTotal.toFixed(2);
    total += productTotal;
  });

  document.getElementById("totalCost").value = total.toFixed(2);
  updateChange();
}

function updateChange() {
  const total = parseFloat(document.getElementById("totalCost").value) || 0;
  const received = parseFloat(document.getElementById("cashReceived").value) || 0;
  const change = received - total;
  document.getElementById("changeDue").value = change >= 0 ? change.toFixed(2) : '0.00';
}

// Event listeners for dynamic updates
document.addEventListener("input", (e) => {
  if (e.target.classList.contains("product-name") || e.target.classList.contains("quantity")) {
    updateTotals();
  }
  if (e.target.id === "cashReceived") {
    updateChange();
  }
});

// Add new product row
document.getElementById("addProductBtn").addEventListener("click", () => {
  const container = document.getElementById("productContainer");
  const newRow = document.createElement("div");
  newRow.classList.add("mb-3", "d-flex", "align-items-center", "gap-2", "product-row");

  newRow.innerHTML = `
    <select class="form-select product-name" style="max-width: 200px;">
      <option value="12">Datu Puti Soy Sauce 100ml</option>
    </select>
    <input type="number" class="form-control quantity" value="1" min="1" style="width: 70px;">
    <span>₱</span>
    <input type="text" class="form-control product-total" value="12.00" style="width: 100px;" readonly>
  `;
  container.appendChild(newRow);
  updateTotals();
});
</script>
<script>
document.addEventListener("DOMContentLoaded", function () {
  const productTemplate = `
    <div class="d-flex align-items-center gap-2 product-row mb-2">
      <select class="form-select product-name" style="max-width: 200px;">
        <?php foreach($products as $product): ?>
          <option value="<?= $product['price']; ?>"><?= htmlspecialchars($product['description']); ?></option>
        <?php endforeach; ?>
      </select>
      <input type="number" class="form-control quantity" value="1" min="1" style="width: 70px;">
      <span>₱</span>
      <input type="text" class="form-control product-total" value="0.00" style="width: 100px;" readonly>
    </div>`;

  document.getElementById("addProductBtn").addEventListener("click", () => {
    const container = document.getElementById("productContainer");
    container.insertAdjacentHTML("beforeend", productTemplate);
    updateTotals(); // immediately recalculate
  });

  // Delegate quantity and select changes
  document.getElementById("productContainer").addEventListener("input", function (e) {
    if (e.target.classList.contains("quantity") || e.target.classList.contains("product-name")) {
      updateTotals();
    }
  });

  function updateTotals() {
    let total = 0;
    document.querySelectorAll(".product-row").forEach(row => {
      const price = parseFloat(row.querySelector(".product-name").value) || 0;
      const qty = parseInt(row.querySelector(".quantity").value) || 1;
      const rowTotal = price * qty;
      row.querySelector(".product-total").value = rowTotal.toFixed(2);
      total += rowTotal;
    });
    document.getElementById("totalDisplay").textContent = "₱" + total.toFixed(2);
  }
});
</script>


</body>
</html>
