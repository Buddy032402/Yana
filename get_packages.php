<?php
session_start();
if (!isset($_SESSION["admin"])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

include "../db.php";

if (!isset($_GET['destination_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Destination ID is required']);
    exit;
}

$destination_id = intval($_GET['destination_id']);

$query = "
    SELECT 
        p.*,
        CONCAT('₱', FORMAT(p.price, 2)) as price_formatted
    FROM packages p
    WHERE p.destination_id = ? AND p.status = 1
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $destination_id);
$stmt->execute();
$result = $stmt->get_result();

$packages = [];
while($row = $result->fetch_assoc()) {
    $packages[] = $row;
}

header('Content-Type: application/json');
echo json_encode($packages);