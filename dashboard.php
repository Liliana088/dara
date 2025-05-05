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
  <title>POS System Dashboard</title>
  <link href="bootstrap-offline/css/bootstrap.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
      margin-left: auto;  /* Align the toggler to the right */
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

    .sales-graph {
      background-color: #f8c8c8;
      padding: 1rem;
      margin-top: 1rem;
      border-radius: 0.5rem;
    }

    .section-header {
      font-weight: bold;
      color: #5c47a1;
      margin-bottom: 1rem;
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
  </style>
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
    <h2 class="section-header">Dashboard</h2>
    <div class="row g-4">
  <!-- Sales Card -->
  <div class="col-md-4">
    <div class="card text-white position-relative" style="background-color: #7673C0;">
      <div class="card-body">
        <div style="font-size: 1.5rem; font-weight: bold;">₱11,832.00</div>
        <div>Sales</div>
      </div>
      <div class="card-footer bg-transparent border-0">
        <a href="sales.php" class="text-white text-decoration-none">More info <i class="bi bi-arrow-right"></i></a>
      </div>
      <i class="bi bi-currency-dollar position-absolute" style="font-size: 6rem; opacity: 0.1; bottom: 10px; right: 10px;"></i>
    </div>
  </div>

  <!-- Categories Card -->
  <div class="col-md-4">
    <div class="card text-white position-relative" style="background-color: #3F417D;">
      <div class="card-body">
        <div style="font-size: 1.5rem; font-weight: bold;">7</div>
        <div>Categories</div>
      </div>
      <div class="card-footer bg-transparent border-0">
        <a href="categories.php" class="text-white text-decoration-none">More info <i class="bi bi-arrow-right"></i></a>
      </div>
      <i class="bi bi-clipboard position-absolute" style="font-size: 6rem; opacity: 0.1; bottom: 10px; right: 10px;"></i>
    </div>
  </div>

  <!-- Products Card -->
  <div class="col-md-4">
    <div class="card text-white position-relative" style="background-color: #824C83;">
      <div class="card-body">
        <div style="font-size: 1.5rem; font-weight: bold;">12</div>
        <div>Products</div>
      </div>
      <div class="card-footer bg-transparent border-0">
        <a href="products.php" class="text-white text-decoration-none">More info <i class="bi bi-arrow-right"></i></a>
      </div>
      <i class="bi bi-box-seam position-absolute" style="font-size: 6rem; opacity: 0.1; bottom: 10px; right: 10px;"></i>
    </div>
  </div>
</div>


    <div class="sales-graph mt-4">
      <h4 class="text-center">SALES GRAPH</h4>
      <canvas id="salesChart" height="100"></canvas>
    </div>
  </div>

  <!-- Scripts -->
  <script src="bootstrap-offline/js/bootstrap.bundle.min.js"></script>
  <script>
    function toggleSidebar() {
      const sidebar = document.getElementById("sidebarMenu");
      sidebar.classList.toggle("expand");
    }

    const ctx = document.getElementById('salesChart').getContext('2d');
    new Chart(ctx, {
      type: 'line',
      data: {
        labels: ['2019', '2020', '2021', '2022', '2023', '2024', '2025'],
        datasets: [{
          label: 'Sales',
          data: [3700, 3100, 1900, 500, 2700, 1800, 300],
          backgroundColor: 'rgba(92, 71, 161, 0.2)',
          borderColor: '#5c47a1',
          borderWidth: 2,
          fill: true,
          tension: 0.3
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { display: false }
        },
        scales: {
          y: { beginAtZero: true }
        }
      }
    });
  </script>
</body>
</html>