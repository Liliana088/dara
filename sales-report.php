<?php
session_start();
if (!isset($_SESSION['user_name'])) {
    header("Location: login.php");
    exit();
}
include "db_conn.php";

// Get initial data for full chart (optional fallback)
$sql = "SELECT DATE(date) AS sale_date, SUM(total_cost) AS total_sales, SUM(markup) AS total_profit
        FROM sales GROUP BY DATE(date) ORDER BY sale_date ASC";
$result = mysqli_query($conn, $sql);
$dates = [];
$sales = [];
$profits = [];

while ($row = mysqli_fetch_assoc($result)) {
    $dates[] = $row['sale_date'];
    $sales[] = $row['total_sales'];
    $profits[] = $row['total_profit'];
}

$sellers = [];
$sellerResult = mysqli_query($conn, "SELECT DISTINCT seller FROM sales");
while ($row = mysqli_fetch_assoc($sellerResult)) {
    $sellers[] = $row['seller'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>POS System Dashboard</title>
  <link href="bootstrap-offline/css/bootstrap.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="icon" type="image/x-icon" href="img/daraa.ico">
  <link href="/dara/css/dashboard.css" rel="stylesheet">
</head>
<body>

  <!-- Navbar -->
  <nav class="navbar navbar-dark">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
             <!-- Dara Logo -->
             <img src="img/daralogo.svg" alt="Dara Logo" style="height: 40px;" />
            <!-- Hamburger Icon -->
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
      <li class="nav-item">
        <a class="nav-link" href="/dara/dashboard.php">
          <i class="bi bi-house"></i>
          <span>Home</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="/dara/categories.php">
          <i class="bi bi-grid"></i>
          <span>Categories</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="/dara/products.php">
          <i class="bi bi-box-seam"></i>
          <span>Products</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="/dara/sales.php">
          <i class="bi bi-currency-dollar"></i>
          <span>Sales</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="/dara/sales-report.php">
          <i class="bi bi-bar-chart-line"></i>
          <span>Sales Report</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="/dara/users.php">
          <i class="bi bi-people"></i>
          <span>User Management</span>
        </a>
      </li>
    </ul>
  </div>
     <!-- Main Content -->
     <div class="main-content">
  <div class="d-flex justify-content-between align-items-center">
    <h3>Sales Report</h3>
  </div>

  <div class="sales-graph mt-4">
    <div class="d-flex gap-3 mb-3">
      <select id="filterDate" class="form-select w-auto" onchange="applyFilter()">
        <option value="month">This Month</option>
        <option value="week">This Week</option>
        <option value="custom">Custom Date Range</option>
      </select>

      <select id="filterSeller" class="form-select w-auto" onchange="applyFilter()">
        <option value="">All Sellers</option>
        <?php foreach ($sellers as $seller): ?>
          <option value="<?= $seller ?>"><?= $seller ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <h4 class="text-center">SALES GRAPH</h4>
    <canvas id="salesChart" height="100"></canvas>

    <h4 class="text-center mt-5">PROFIT GRAPH</h4>
    <canvas id="profitChart" height="100"></canvas>
  </div>
</div>

  <!-- Scripts -->
  <script src="bootstrap-offline/js/bootstrap.bundle.min.js"></script>
  <script>
    function toggleSidebar() {
      const sidebar = document.getElementById("sidebarMenu");
      sidebar.classList.toggle("expand");
    }

    const dates = <?= json_encode($dates) ?>;
  const sales = <?= json_encode($sales) ?>;
  const profits = <?= json_encode($profits) ?>;

  const ctxSales = document.getElementById('salesChart').getContext('2d');
  const ctxProfit = document.getElementById('profitChart').getContext('2d');

  const salesChart = new Chart(ctxSales, {
    type: 'line',
    data: {
      labels: dates,
      datasets: [{
        label: 'Total Sales (₱)',
        data: sales,
        backgroundColor: 'rgba(54, 162, 235, 0.2)',
        borderColor: 'rgb(54, 162, 235)',
        borderWidth: 2,
        fill: true,
        tension: 0.4
      }]
    },
    options: { responsive: true, scales: { y: { beginAtZero: true } } }
  });

  const profitChart = new Chart(ctxProfit, {
    type: 'line',
    data: {
      labels: dates,
      datasets: [{
        label: 'Profit (₱)',
        data: profits,
        backgroundColor: 'rgba(75, 192, 192, 0.2)',
        borderColor: 'rgb(75, 192, 192)',
        borderWidth: 2,
        fill: true,
        tension: 0.4
      }]
    },
    options: { responsive: true, scales: { y: { beginAtZero: true } } }
  });

  function applyFilter() {
    const dateFilter = document.getElementById('filterDate').value;
    const sellerFilter = document.getElementById('filterSeller').value;

    fetch('sales-report-ajax.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ date: dateFilter, seller: sellerFilter })
    })
    .then(response => response.json())
    .then(data => {
      salesChart.data.labels = data.dates;
      salesChart.data.datasets[0].data = data.sales;
      salesChart.update();

      profitChart.data.labels = data.dates;
      profitChart.data.datasets[0].data = data.profits;
      profitChart.update();
    });
  }
 </script>
</body>
</html>