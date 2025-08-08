<?php
session_start();
include "../../db.php";

$response = [
    'labels' => [],
    'inquiries' => [],
    'bookings' => [],
    'userLabels' => [],
    'userActivity' => []
];

// Last 7 days data
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $response['labels'][] = date('M d', strtotime($date));
    
    $inquiries = $conn->query("SELECT COUNT(*) as count FROM inquiries WHERE DATE(created_at) = '$date'")->fetch_assoc()['count'];
    $response['inquiries'][] = $inquiries;
    
    $bookings = $conn->query("SELECT COUNT(*) as count FROM bookings WHERE DATE(created_at) = '$date'")->fetch_assoc()['count'];
    $response['bookings'][] = $bookings;
}

// User activity
$users = $conn->query("SELECT DATE(login_time) as date, COUNT(*) as count FROM login_history GROUP BY DATE(login_time) ORDER BY date DESC LIMIT 7");
while ($row = $users->fetch_assoc()) {
    $response['userLabels'][] = date('M d', strtotime($row['date']));
    $response['userActivity'][] = $row['count'];
}

echo json_encode($response);