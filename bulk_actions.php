<?php
session_start();
if (!isset($_SESSION["admin"])) {
    header("Location: login.php");
    exit;
}

include "../db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ids = $_POST['ids'] ?? [];
    $action = $_POST['action'] ?? '';
    $type = $_POST['type'] ?? '';

    if (!empty($ids) && !empty($action) && !empty($type)) {
        $ids = array_map('intval', $ids);
        $ids_string = implode(',', $ids);

        switch($action) {
            case 'delete':
                $conn->query("DELETE FROM $type WHERE id IN ($ids_string)");
                break;
            case 'archive':
                $conn->query("UPDATE $type SET is_archived = 1 WHERE id IN ($ids_string)");
                break;
            case 'export':
                header('Content-Type: text/csv');
                header("Content-Disposition: attachment; filename={$type}_export.csv");
                $fp = fopen('php://output', 'w');
                $query = $conn->query("SELECT * FROM $type WHERE id IN ($ids_string)");
                $headers = array_keys($query->fetch_assoc());
                fputcsv($fp, $headers);
                while ($row = $query->fetch_assoc()) {
                    fputcsv($fp, $row);
                }
                fclose($fp);
                exit;
        }
        echo json_encode(['success' => true]);
        exit;
    }
}

echo json_encode(['success' => false]);