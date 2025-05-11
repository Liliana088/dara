<?php
include "db_conn.php";

$requestData = json_decode(file_get_contents('php://input'), true);
$filterDate = $requestData['date'] ?? 'month';  // Default to 'month'
$filterSeller = $requestData['seller'] ?? '';

// Start building the SQL query
$sql = "SELECT DATE(date) AS sale_date, SUM(total_cost) AS total_sales, SUM(markup) AS total_profit
        FROM sales";

// Apply date filters (Month, Week, Custom)
if ($filterDate === 'month') {
    $sql .= " WHERE MONTH(date) = MONTH(CURRENT_DATE()) AND YEAR(date) = YEAR(CURRENT_DATE())";
} elseif ($filterDate === 'week') {
    $sql .= " WHERE YEAR(date) = YEAR(CURRENT_DATE()) AND WEEK(date) = WEEK(CURRENT_DATE())";
} elseif ($filterDate === 'custom' && isset($requestData['startDate']) && isset($requestData['endDate'])) {
    $startDate = $requestData['startDate'];
    $endDate = $requestData['endDate'];
    $sql .= " WHERE date BETWEEN '$startDate' AND '$endDate'";
}

// Apply seller filter
if ($filterSeller) {
    $sql .= " AND seller = '$filterSeller'";
}

$sql .= " GROUP BY DATE(date) ORDER BY sale_date ASC";

// Execute the query
$result = mysqli_query($conn, $sql);

$dates = [];
$sales = [];
$profits = [];

while ($row = mysqli_fetch_assoc($result)) {
    $dates[] = $row['sale_date'];
    $sales[] = $row['total_sales'];
    $profits[] = $row['total_profit'];
}

// Return data as JSON
echo json_encode([
    'dates' => $dates,
    'sales' => $sales,
    'profits' => $profits
]);
?>
