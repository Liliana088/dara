<?php
session_start();

if (!isset($_SESSION['user_name'])) {
    header("Location: login.php");
    exit();
}

include "db_conn.php";
//insert products on dropdown, add sales
$productQuery = "SELECT * FROM products";
$productResult = $conn->query($productQuery);

$products = [];
if ($productResult->num_rows > 0) {
    while ($row = $productResult->fetch_assoc()) {
        $products[] = $row;
    }
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['product_id']) && isset($_POST['quantity'])) {
  $seller = $_SESSION['user_name'];
  $payment_method = $_POST['payment_method'];
  $date = $_POST['date'];
  $product_ids = $_POST['product_id'];
  $quantities = $_POST['quantity'];
  $cash_received = floatval($_POST['received'] ?? 0);
  $change_given = isset($_POST['change']) ? floatval($_POST['change']) : 0;

  $subtotal = 0;
  $total_markup = 0;
  $price_at_sale_list = [];

  // Fetch cost and markup for each product to compute prices
  foreach ($product_ids as $index => $product_id) {
      $quantity = intval($quantities[$index]);

      // Get the product's cost and markup
      $stmt = $conn->prepare("SELECT cost, markup FROM products WHERE id = ?");
      $stmt->bind_param("i", $product_id);
      $stmt->execute();
      $stmt->bind_result($cost, $markup);
      $stmt->fetch();
      $stmt->close();

      $subtotal += round($cost * $quantity);
      $markup_amount = round ($cost * ($markup / 100));
      $total_markup += round(($markup / 100) * $cost * $quantity, 2);
      $price_at_sale_list[] = $cost + ($markup / 100 * $cost);
      
  }

  $total_cost = round($subtotal + $total_markup);
  $change = $cash_received - $total_cost;
  

// If stock is sufficient, proceed with recording the sale
$is_stock_sufficient = true;

// First, check if there is enough stock for all products
foreach ($product_ids as $index => $product_id) {
    $quantity = intval($quantities[$index]);

    $check = $conn->prepare("SELECT Description, stock FROM products WHERE id = ?");
    $check->bind_param("i", $product_id);
    $check->execute();
    $check->bind_result($product_name, $available_stocks);
    $check->fetch();
    $check->close();

    if ($available_stocks < $quantity) {
        $_SESSION['error'] = "Cannot proceed with sale for \"{$product_name}\". Only {$available_stocks} in stock.";
        header("Location: sales.php");
        exit;
    }
}

// Check if cash_received is less than total cost
$total_cost = $subtotal + $total_markup;

if ($cash_received < $total_cost) {
    $_SESSION['error'] = "Cash received was less than total cost. Sale was not processed.";
    header("Location: sales.php");
    exit;
}

// If all validations pass, proceed with the sale
if ($is_stock_sufficient) {
    // Insert the main sale record
    $stmt = $conn->prepare("INSERT INTO sales (seller, payment_method, subtotal, markup, total_cost, date, cash_received, change_given) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdddsdd", $seller, $payment_method, $subtotal, $total_markup, $total_cost, $date, $cash_received, $change_given);
    $stmt->execute();
    $sale_id = $stmt->insert_id;
    $stmt->close();

    // Insert each item and update stock
    foreach ($product_ids as $index => $product_id) {
        $quantity = intval($quantities[$index]);
        $price_at_sale = $price_at_sale_list[$index];

        $stmt = $conn->prepare("INSERT INTO sales_items (sale_id, product_id, quantity, price_at_sale) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiid", $sale_id, $product_id, $quantity, $price_at_sale);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
        $stmt->bind_param("ii", $quantity, $product_id);
        $stmt->execute();
        $stmt->close();

    // After successful insert:
    $_SESSION['success'] = "Sale added successfully!";
    header("Location: sales.php");
    exit();
    }
}

}
// Items per page (default to 7, override if set in query)
$itemsPerPage = isset($_GET['entries']) ? max(1, (int)$_GET['entries']) : 7;

// Get the current page, default to page 1
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Calculate the starting point (offset)
$offset = ($page - 1) * $itemsPerPage;

// Modify the SQL query to include LIMIT and OFFSET for pagination
$sql = "SELECT sales.id, users.name AS seller, sales.payment_method, sales.subtotal, sales.markup, sales.total_cost, sales.date,
        GROUP_CONCAT(DISTINCT products.description ORDER BY products.description ASC) AS product_names
        FROM sales
        LEFT JOIN sales_items ON sales.id = sales_items.sale_id
        LEFT JOIN products ON sales_items.product_id = products.id
        LEFT JOIN users ON sales.seller = users.id
        GROUP BY sales.id
        LIMIT $itemsPerPage OFFSET $offset";

// Execute the query to get the paginated sales data
$result = mysqli_query($conn, $sql);

// Count the total number of rows in the sales table
$countSql = "SELECT COUNT(DISTINCT sales.id) as total FROM sales";
$countResult = mysqli_query($conn, $countSql);
$totalRows = mysqli_fetch_assoc($countResult)['total'];
$totalPages = ceil($totalRows / $itemsPerPage);  // Calculate the total pages

?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <title>Sales Management</title>
    <link href="bootstrap-offline/css/bootstrap.css" rel="stylesheet">
    <link rel="stylesheet" href="bootstrap-icons/font/bootstrap-icons.css">
    <link rel="icon" type="image/x-icon" href="img/daraa.ico">
    <link href="css/sales.css" rel="stylesheet" />

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
          <li class="nav-item"><a class="nav-link" href="/dara/users.php"><i class="bi bi-person-circle"></i><span>User Information</span></a></li>
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

          <div class="table-responsive">
            <table class="table">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Seller</th>
                  <th>Payment Method</th>
                  <th>Subtotal</th>
                  <th>Markup</th>
                  <th>Total Cost</th>
                  <th>Date</th>
                  <th>Items</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody name="salesTablebody" id="salesTableBody">
              <?php
              // Fetch all sales records first
              $sql = "SELECT * FROM sales ORDER BY date DESC";
              $result = $conn->query($sql);

              if ($result->num_rows > 0) {
                  // Get the total number of sales records
                  $total_sales = $result->num_rows;
                  
                  // Loop through each sale
                  while($row = $result->fetch_assoc()) {
                      $sale_id = $row['id'];

                      // Fetch items for this sale
                      $itemsStmt = $conn->prepare("
                          SELECT p.Description AS product_name, si.quantity
                          FROM sales_items si
                          JOIN products p ON p.id = si.product_id
                          WHERE si.sale_id = ?
                      ");
                      $itemsStmt->bind_param("i", $sale_id);
                      $itemsStmt->execute();
                      $itemsResult = $itemsStmt->get_result();

                      // Store all item names and quantities
                      $items = [];
                      while ($itemRow = $itemsResult->fetch_assoc()) {
                          $items[] = $itemRow['product_name'] . " (" . $itemRow['quantity'] . ")";
                      }

                      // Combine the items into a string
                      $row['items'] = implode("<br>", $items);

                      // Format subtotal, markup, and total cost to two decimal places
                      $formatted_subtotal = number_format(round($row['subtotal']), 2);
                      $formatted_markup = number_format(round($row['markup']), 2);
                      $formatted_total_cost = number_format(round($row['total_cost']), 2);

                      //show voided
                      $badge = $row['voided'] ? "<span class='badge bg-danger'>Voided</span>" : "";
                      // Display the sale information in the table row with the counter decreasing
                      if ($row['voided']): ?>
                        <tr class="text-danger">
                            <td><?= $total_sales ?></td>
                            <td><?= $row['seller'] ?></td>
                            <td><?= $row['payment_method'] ?></td>
                            <td><s>₱<?= $formatted_subtotal ?></s></td>
                            <td><s>₱<?= $formatted_markup ?></s></td>
                            <td><s>₱<?= $formatted_total_cost ?></s></td>
                            <td><?= $row['date'] ?></td>
                            <td><?= $row['items'] ?></td>
                            <td><span class="badge bg-danger">Voided</span></td>
                        </tr>
                    <?php else: ?>
                        <tr>
                            <td><?= $total_sales ?></td>
                            <td><?= $row['seller'] ?></td>
                            <td><?= $row['payment_method'] ?></td>
                            <td>₱<?= $formatted_subtotal ?></td>
                            <td>₱<?= $formatted_markup ?></td>
                            <td>₱<?= $formatted_total_cost ?></td>
                            <td><?= $row['date'] ?></td>
                            <td><?= $row['items'] ?></td>
                            <td>
                                <a href='receipt.php?id=<?= $row['id'] ?>&display=<?= $total_sales ?>' target='_blank' class='icon-box print-icon' onclick='printReceipt(<?= $row['id'] ?>, <?= $total_sales ?>)'>
                                    <i class='bi bi-printer-fill'></i>
                                </a>
                                <a href='void_sales.php?id=<?= $row['id'] ?>' class='icon-box delete-icon' onclick='return confirm("Are you sure you want to void this sale?");'>
                                    <i class='bi bi-file-earmark-x-fill'></i>
                                </a>
                            </td>
                        </tr>
                    <?php endif;
                      // Decrement the counter for the next row
                      $total_sales--;
                  }
              } else {
                  // If no sales records exist, show this message
                  echo "<tr><td colspan='9'>No sales records found.</td></tr>";
              }
              ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Add Sale Sidebar (Offcanvas) -->
        <div class="offcanvas offcanvas-end" tabindex="-1" id="addSaleSidebar" aria-labelledby="addSaleSidebarLabel" style="width: 600px">
          <div class="offcanvas-header mt-5" style="background-color: #dc7a91;">
            <h5 class="offcanvas-title text-white" id="addSaleSidebarLabel">Add Sale</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
          </div>

          <div class="offcanvas-body" style="background-color: #ffbbb5;">
            <form method="POST" action="">
              <div class="mb-2">
                <label class="form-label">Seller</label>
                <input type="text" name="seller" class="form-control" value="<?php echo $_SESSION['user_name']; ?>" readonly />
              </div>

              <!-- Products Container -->
              <div id="productContainer">
                <div class="mb-2 d-flex align-items-center product-row">
                  <div class="input-group me-2" style="width: 1300px;">
                    <button type="button" class="btn btn-outline-danger delete-row-btn">
                      <i class="bi bi-trash-fill"></i>
                    </button>
                    <select class="form-select product-select" name="product_id[]" onchange="updatePrice(this)">
                      <?php foreach($products as $product): ?>
                        <option value="<?= $product['id']; ?>" 
                                data-cost="<?= $product['cost']; ?>" 
                                data-markup="<?= $product['markup']; ?>">
                          <?= htmlspecialchars($product['Description']); ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <input type="number" class="form-control quantity" name="quantity[]" value="1" min="1" style="width: 60px;">
                  <span>₱</span>
                  <input type="text" name="product_price[]" class="form-control price-field" readonly>
                </div>
              </div>

              <!-- Add Product Button -->
              <div class="mb-2">
                <button type="button" class="btn btn-light" id="addProductBtn" style="background-color: #dc7a91; color: white;">+ Add Product</button>
              </div>

              <!-- Subtotal -->
              <div class="mb-2">
                <label class="form-label">Subtotal</label>
                <input type="text" id="subtotalDisplay" class="form-control" readonly>
              </div>

              <!-- Total (Subtotal + Markup) -->
              <div class="mb-2">
                <label class="form-label fw-bold">Total Cost</label>
                <input type="text" id="totalCost" name="total_cost" class="form-control" readonly />
              </div>

              <!-- Cash Section -->
              <div class="row mb-3">
                <div class="col">
                  <label class="form-label">Cash Received</label>
                  <input type="number" id="cashReceived" name="received" class="form-control" required>
                  <div class="invalid-feedback">
                    Cash is less than total cost. Please enter enough.
                  </div>
                  <div class="valid-feedback">
                    Enough cash received.
                  </div>
                </div>

                <div class="col">
                  <label class="form-label fw-bold">Change</label>
                  <input type="text" id="changeDue" name="change" class="form-control" readonly>
                </div>
              </div>

              <!-- Warning Message -->
              <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                  <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
              <?php endif; ?>

              <!-- Hidden values -->
              <input type="hidden" name="subtotal" id="subtotalInput">
              <input type="hidden" name="markup" id="markupInput">
              <input type="hidden" name="payment_method" value="Cash">
              <input type="hidden" name="date" value="<?php echo date('Y-m-d H:i:s'); ?>">

              <!-- Submit button -->
              <button type="submit" id="saveSaleButton" class="btn" style="background-color: #dc7a91; color: white; width: 100%;">Save Sale</button>
            </form>
          </div>
        </div>

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

      <script src="bootstrap-offline/js/bootstrap.bundle.min.js"></script>
      <!-- Inject product options as JSON -->
      <script id="product-data" type="application/json">
        <?= json_encode($products); ?>
      </script>
      <!-- Link to external JS -->
      <script src="/dara/js/sales.js" defer></script>
      <script>
        document.getElementById("showEntries").value = <?php echo $itemsPerPage; ?>;
      </script>
  </body>
</html>