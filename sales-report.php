<?php
session_start();
if (!isset($_SESSION['user_name'])) {
    header("Location: login.php");
    exit();
}
include "db_conn.php";

// Get initial data for full chart (optional fallback)
$startOfWeek = date('Y-m-d', strtotime('monday this week'));
$endOfWeek = date('Y-m-d', strtotime('sunday this week'));

$period = new DatePeriod(
    new DateTime($startOfWeek),
    new DateInterval('P1D'),
    (new DateTime($endOfWeek))->modify('+1 day')
);

$dailyData = [];
foreach ($period as $date) {
    $dailyData[$date->format("Y-m-d")] = ['sales' => 0, 'profit' => 0];
}

$query = "SELECT DATE(date) AS sale_date, SUM(total_cost) AS total_sales, SUM(markup) AS total_profit 
          FROM sales 
          WHERE voided = 0 AND DATE(date) BETWEEN '$startOfWeek' AND '$endOfWeek' 
          GROUP BY DATE(date)";
$result = mysqli_query($conn, $query);
while ($row = mysqli_fetch_assoc($result)) {
    $date = $row['sale_date'];
    $dailyData[$date] = ['sales' => $row['total_sales'], 'profit' => $row['total_profit']];
}

$dates = array_keys($dailyData);
$sales = array_column($dailyData, 'sales');
$profits = array_column($dailyData, 'profit');

$start = date('Y-m-d', strtotime('monday this week'));
$end = date('Y-m-d', strtotime('sunday this week'));

$sql = "SELECT DATE(date) AS sale_date, SUM(total_cost) AS total_sales, SUM(markup) AS total_profit
        FROM sales
        WHERE voided = 0 AND DATE(date) BETWEEN '$start' AND '$end'
        GROUP BY DATE(date)
        ORDER BY sale_date ASC";

$result = mysqli_query($conn, $sql);
$dates = [];
$sales = [];
$profits = [];

while ($row = mysqli_fetch_assoc($result)) {
    $dates[] = $row['sale_date'];
    $sales[] = round($row['total_sales'], 2); // Round to 2 decimal places
    $profits[] = round($row['total_profit'], 2); // Round to 2 decimal places
}

$sellers = [];
$sellerResult = mysqli_query($conn, "SELECT DISTINCT seller FROM sales");
while ($row = mysqli_fetch_assoc($sellerResult)) {
    $sellers[] = $row['seller'];
}

//bestselling products

$topProductsQuery = "
    SELECT p.Description AS product_name, SUM(si.quantity) AS total_quantity
    FROM sales_items si
    JOIN products p ON si.product_id = p.id
    GROUP BY si.product_id
    ORDER BY total_quantity DESC
    LIMIT 10
";

$topResult = mysqli_query($conn, $topProductsQuery);

$topProductNames = [];
$topQuantities = [];

while ($row = mysqli_fetch_assoc($topResult)) {
    $topProductNames[] = $row['product_name'];
    $topQuantities[] = $row['total_quantity'];
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>POS System Dashboard</title>
    <link href="bootstrap-offline/css/bootstrap.css" rel="stylesheet">
    <link rel="stylesheet" href="bootstrap-icons/font/bootstrap-icons.css">
    <link rel="icon" type="image/x-icon" href="img/daraa.ico">
    <link href="css/sales-report.css" rel="stylesheet">
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
      <button class="btn button-export" onclick="exportReport()">Download Report</button>
    </div>
    <div class="sales-graph mt-4">

    <!-- Filters -->
    <div style="max-width: 900px; margin: auto;">
      <div class="d-flex gap-3 my-3">
        <select id="filterDate" class="form-select w-auto" onchange="toggleCustomDate()">
          <option value="month">This Month</option>
          <option value="week">This Week</option>
          <option value="custom">Custom Range</option>
        </select>

        <div id="customDateInputs" style="display:none;" class="d-flex gap-2 align-items-center">
          <input type="date" id="startDate" class="form-control" />
          <input type="date" id="endDate" class="form-control" />
        </div>

        <select id="filterSeller" class="form-select w-auto">
          <option value="">All Sellers</option>
          <?php foreach ($sellers as $seller): ?>
            <option value="<?= $seller ?>"><?= $seller ?></option>
          <?php endforeach; ?>
        </select>

        <button class="btn btn-apply" onclick="applyFilter()">Apply</button>
      </div>
      <h4 class="text-center">SALES GRAPH</h4>
        <canvas id="salesChart" height="100"></canvas>

        <h4 class="text-center mt-5">PROFIT GRAPH</h4>
        <canvas id="profitChart" height="100"></canvas>
    </div>
    </div>

    <div class="d-flex justify-content-center">
      <div style="width: 900px;">
        <h4 class="text-center mt-5 ms-4">Top 10 Best Selling Products</h4>
        <canvas id="topProductsChart" height="120"></canvas>
      </div>
    </div>

    <!-- Scripts -->
    <script src="bootstrap-offline/js/bootstrap.bundle.min.js"></script>
    <script src="js/chart.js"></script>
    <script>
      function toggleSidebar() {
        const sidebar = document.getElementById("sidebarMenu");
        sidebar.classList.toggle("expand");
      }
      </script>
      <script>
      const salesChart = new Chart(document.getElementById('salesChart').getContext('2d'), {
      type: 'line',
      data: {
          labels: <?php echo json_encode($dates); ?>,
          datasets: [{
          label: 'Total Sales (₱)',
          data: <?php echo json_encode($sales); ?>,
          backgroundColor: 'rgba(116, 40, 109, 0.2)',
          borderColor: 'rgb(70, 20, 90)',
          borderWidth: 2,
          fill: true,
          tension: 0.4
          }]
      },
      options: { scales: { y: { beginAtZero: true } } }
      });

      const profitChart = new Chart(document.getElementById('profitChart').getContext('2d'), {
      type: 'line',
      data: {
          labels: <?php echo json_encode($dates); ?>,
          datasets: [{
          label: 'Profit (₱)',
          data: <?php echo json_encode($profits); ?>,
          backgroundColor: 'rgba(92, 71, 161, 0.2)',
          borderColor: '#5c47a1',
          borderWidth: 2,
          fill: true,
          tension: 0.4
          }]
      },
      options: { scales: { y: { beginAtZero: true } } }
      });

      function toggleSidebar() {
      document.getElementById("sidebarMenu").classList.toggle("expand");
      }

      function toggleCustomDate() {
      const filter = document.getElementById("filterDate").value;
      const inputs = document.getElementById("customDateInputs");
      inputs.style.display = (filter === "custom") ? "flex" : "none";
      }

      function applyFilter() {
      const date = document.getElementById('filterDate').value;
      const seller = document.getElementById('filterSeller').value;
      const start = document.getElementById('startDate').value;
      const end = document.getElementById('endDate').value;

      fetch('sales-report-ajax.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ date, seller, start, end })
      })
          .then(res => res.json())
          .then(data => {
          salesChart.data.labels = data.dates;
          salesChart.data.datasets[0].data = data.sales;
          salesChart.update();

          profitChart.data.labels = data.dates;
          profitChart.data.datasets[0].data = data.profits;
          profitChart.update();
          });
      }

      window.addEventListener("DOMContentLoaded", () => {
          applyFilter(); // auto-load this week's sales on page load
          });


          //bestselling products GRAPH

          const topProductsChart = new Chart(document.getElementById('topProductsChart').getContext('2d'), {
      type: 'bar',
      data: {
          labels: <?php echo json_encode($topProductNames); ?>,
          datasets: [{
              label: 'Quantity Sold',
              data: <?php echo json_encode($topQuantities); ?>,
              backgroundColor: 'rgba(250, 143, 197, 0.5)',
              borderColor: 'rgb(235, 54, 175)',
              borderWidth: 1
          }]
      },
      options: {
          indexAxis: 'y',
          scales: {
              x: {
                  beginAtZero: true,
                  title: { display: true, text: 'Units Sold' }
                  }
              }
          }
      });

      function exportReport() {
        const date = document.getElementById('filterDate').value;
        const seller = document.getElementById('filterSeller').value;
        const start = document.getElementById('startDate').value;
        const end = document.getElementById('endDate').value;

        // Build query parameters
        const params = new URLSearchParams({
            date, seller, start, end
        });

        // Redirect to the PHP script with filters
        window.location.href = 'export_sales_report.php?' + params.toString();
    }

    </script>
  </body>
</html>