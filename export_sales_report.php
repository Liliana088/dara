<?php
session_start();
if (!isset($_SESSION['user_name'])) {
    header("Location: login.php");
    exit();
}
include "db_conn.php";

// Get filters from the URL parameters
$dateFilter = isset($_GET['date']) ? $_GET['date'] : 'week';
$sellerFilter = isset($_GET['seller']) ? $_GET['seller'] : '';
$startDate = isset($_GET['start']) ? $_GET['start'] : '';
$endDate = isset($_GET['end']) ? $_GET['end'] : '';

// Get date range based on the filter
if ($dateFilter === 'week') {
    $startOfWeek = date('Y-m-d', strtotime('monday this week'));
    $endOfWeek = date('Y-m-d', strtotime('sunday this week'));
} elseif ($dateFilter === 'month') {
    $startOfWeek = date('Y-m-01');
    $endOfWeek = date('Y-m-t');
} elseif ($dateFilter === 'custom' && $startDate && $endDate) {
    $startOfWeek = $startDate;
    $endOfWeek = $endDate;
}

// Fetch sales data within the date range
$sql = "SELECT DATE(date) AS sale_date, SUM(total_cost) AS total_sales, SUM(markup) AS total_profit
        FROM sales
        WHERE DATE(date) BETWEEN '$startOfWeek' AND '$endOfWeek'";

if ($sellerFilter) {
    $sql .= " AND seller = '$sellerFilter'";
}

$sql .= " GROUP BY DATE(date) ORDER BY sale_date ASC";

$result = mysqli_query($conn, $sql);

// Prepare sales data
$salesData = [];
while ($row = mysqli_fetch_assoc($result)) {
    $salesData[] = $row;
}

// Fetch best-selling products
$topProductsQuery = "
    SELECT p.Description AS product_name, SUM(si.quantity) AS total_quantity
    FROM sales_items si
    JOIN products p ON si.product_id = p.id
    GROUP BY si.product_id
    ORDER BY total_quantity DESC
    LIMIT 10
";
$topResult = mysqli_query($conn, $topProductsQuery);

$topProducts = [];
while ($row = mysqli_fetch_assoc($topResult)) {
    $topProducts[] = $row;
}

// Set headers for CSV export
header('Content-Type: text/csv');
header('Content-Disposition: attachment;filename="sales_report.csv"');

// Open PHP output stream for CSV
$output = fopen('php://output', 'w');

// Write the header row for sales data
fputcsv($output, ['Sale Date', 'Total Sales (₱)', 'Total Profit (₱)']);

// Assuming $salesData is an array of sales data with keys 'sale_date', 'total_sales', and 'total_profit'
foreach ($salesData as $sale) {
    // Round total_sales and total_profit to 2 decimal places
    fputcsv($output, [
        $sale['sale_date'], 
        round($sale['total_sales'], 2),  // Rounding off to 2 decimal places
        round($sale['total_profit'], 2)  // Rounding off to 2 decimal places
    ]);
}


// Add an empty line between sections
fputcsv($output, []);

// Write the header row for best-selling products
fputcsv($output, ['Product Name', 'Quantity Sold']);

// Write best-selling products data
foreach ($topProducts as $product) {
    fputcsv($output, [$product['product_name'], $product['total_quantity']]);
}

fclose($output);
exit;
?>
