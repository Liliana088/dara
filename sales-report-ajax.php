<?php
include "db_conn.php";

// Receive JSON input
$data = json_decode(file_get_contents("php://input"), true);
$dateFilter = $data['date'] ?? 'month';
$sellerFilter = $data['seller'] ?? '';
$startDate = $data['start'] ?? '';
$endDate = $data['end'] ?? '';

$where = [];
$rangeStart = '';
$rangeEnd = '';

// Determine date range
if ($dateFilter === 'month') {
    $rangeStart = date('Y-m-01');
    $rangeEnd = date('Y-m-t');
} elseif ($dateFilter === 'week') {
    $rangeStart = date('Y-m-d', strtotime('monday this week'));
    $rangeEnd = date('Y-m-d', strtotime('sunday this week'));
} elseif ($dateFilter === 'custom' && $startDate && $endDate) {
    $rangeStart = $startDate;
    $rangeEnd = $endDate;
}

// Build WHERE clause
if ($rangeStart && $rangeEnd) {
    $where[] = "DATE(date) BETWEEN '$rangeStart' AND '$rangeEnd'";
}
if (!empty($sellerFilter)) {
    $where[] = "seller = '" . mysqli_real_escape_string($conn, $sellerFilter) . "'";
}
$whereSql = $where ? "WHERE " . implode(' AND ', $where) : '';

// Fetch actual sales data
$salesData = [];
$sql = "SELECT DATE(date) AS sale_date, SUM(total_cost) AS total_sales, SUM(markup) AS total_profit
        FROM sales
        $whereSql
        GROUP BY DATE(date)
        ORDER BY sale_date ASC";
$result = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_assoc($result)) {
    $salesData[$row['sale_date']] = [
        'sales' => (float)$row['total_sales'],
        'profits' => (float)$row['total_profit']
    ];
}

// Build full date range with zeros
$response = ['dates' => [], 'sales' => [], 'profits' => []];

if ($rangeStart && $rangeEnd) {
    $period = new DatePeriod(
        new DateTime($rangeStart),
        new DateInterval('P1D'),
        (new DateTime($rangeEnd))->modify('+1 day')
    );

    foreach ($period as $date) {
        $d = $date->format('Y-m-d');
        $response['dates'][] = $d;
        $response['sales'][] = $salesData[$d]['sales'] ?? 0;
        $response['profits'][] = $salesData[$d]['profits'] ?? 0;
    }
}

header('Content-Type: application/json');
echo json_encode($response);
