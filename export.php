<?php
session_start();
if (!isset($_SESSION["admin"])) {
    header("Location: login.php");
    exit;
}

include "../db.php";

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="export_' . $_GET['type'] . '_' . date('Y-m-d') . '.csv"');

$fp = fopen('php://output', 'w');

switch($_GET['type']) {
    case 'inquiries':
        fputcsv($fp, ['ID', 'Name', 'Email', 'Subject', 'Message', 'Status', 'Created At']);
        $query = $conn->query("SELECT * FROM inquiries ORDER BY created_at DESC");
        while ($row = $query->fetch_assoc()) {
            fputcsv($fp, $row);
        }
        break;
        
    case 'users':
        fputcsv($fp, ['ID', 'Name', 'Email', 'Created At', 'Last Login']);
        $query = $conn->query("SELECT id, name, email, created_at, last_login FROM users ORDER BY created_at DESC");
        while ($row = $query->fetch_assoc()) {
            fputcsv($fp, $row);
        }
        break;
}

fclose($fp);