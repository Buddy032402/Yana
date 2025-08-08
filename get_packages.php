<?php
include "db.php";

header('Content-Type: application/json');

if(isset($_GET['destination_id'])) {
    $destination_id = intval($_GET['destination_id']);
    
    // Modified query to ensure we only get distinct packages
    $stmt = $conn->prepare("SELECT DISTINCT id, name FROM packages WHERE destination_id = ? AND status = 1");
    $stmt->bind_param("i", $destination_id);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $packages = [];
    
    while($row = $result->fetch_assoc()) {
        $packages[] = $row;
    }
    
    echo json_encode($packages);
    
    $stmt->close();
} else {
    echo json_encode([]);
}

$conn->close();
?>