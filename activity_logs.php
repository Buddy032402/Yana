<?php
session_start();
if (!isset($_SESSION["admin"])) {
    header("Location: login.php");
    exit;
}

include "../db.php";

// Pagination setup
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Build query conditions
$where_clauses = [];
$params = [];
$param_types = "";

if (isset($_GET['module']) && $_GET['module'] !== '') {
    $where_clauses[] = "l.module = ?";
    $params[] = $_GET['module'];
    $param_types .= "s";
}

if (isset($_GET['user']) && $_GET['user'] !== '') {
    $where_clauses[] = "u.username LIKE ?";
    $params[] = "%" . $_GET['user'] . "%";
    $param_types .= "s";
}

if (isset($_GET['date_from']) && $_GET['date_from'] !== '') {
    $where_clauses[] = "DATE(l.created_at) >= ?";
    $params[] = $_GET['date_from'];
    $param_types .= "s";
}

if (isset($_GET['date_to']) && $_GET['date_to'] !== '') {
    $where_clauses[] = "DATE(l.created_at) <= ?";
    $params[] = $_GET['date_to'];
    $param_types .= "s";
}

$where_sql = !empty($where_clauses) ? "WHERE " . implode(" AND ", $where_clauses) : "";

// Get total logs count
$count_sql = "SELECT COUNT(*) as count FROM activity_logs l 
              LEFT JOIN users u ON l.user_id = u.id 
              $where_sql";
$stmt = $conn->prepare($count_sql);
if (!empty($params)) {
    $stmt->bind_param($param_types, ...$params);
}
$stmt->execute();
$total_logs = $stmt->get_result()->fetch_assoc()['count'];
$total_pages = ceil($total_logs / $limit);

// Get logs for current page
$sql = "SELECT l.*, u.username 
        FROM activity_logs l 
        LEFT JOIN users u ON l.user_id = u.id 
        $where_sql 
        ORDER BY l.created_at DESC 
        LIMIT ? OFFSET ?";
$param_types .= "ii";
$params[] = $limit;
$params[] = $offset;
$stmt = $conn->prepare($sql);
$stmt->bind_param($param_types, ...$params);
$stmt->execute();
$logs = $stmt->get_result();

// Get unique modules for filter
$modules = $conn->query("SELECT DISTINCT module FROM activity_logs ORDER BY module")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en" data-theme="<?php echo $_SESSION['theme'] ?? 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Logs - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>

<div class="admin-container">
    <?php include "includes/sidebar.php"; ?>

    <main class="main-content">
        <header class="dashboard-header">
            <div class="header-content">
                <h1>Activity Logs</h1>
            </div>
        </header>

        <div class="card">
            <div class="card-header">
                <form class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label">Module</label>
                        <select name="module" class="form-select">
                            <option value="">All Modules</option>
                            <?php foreach ($modules as $module): ?>
                                <option value="<?php echo htmlspecialchars($module['module']); ?>" 
                                        <?php echo isset($_GET['module']) && $_GET['module'] === $module['module'] ? 'selected' : ''; ?>>
                                    <?php echo ucfirst($module['module']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label">User</label>
                        <input type="text" name="user" class="form-control" 
                               value="<?php echo htmlspecialchars($_GET['user'] ?? ''); ?>" 
                               placeholder="Username">
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label">Date From</label>
                        <input type="date" name="date_from" class="form-control" 
                               value="<?php echo $_GET['date_from'] ?? ''; ?>">
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label">Date To</label>
                        <input type="date" name="date_to" class="form-control" 
                               value="<?php echo $_GET['date_to'] ?? ''; ?>">
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary d-block">
                            <i class="fas fa-search"></i> Filter
                        </button>
                    </div>
                </form>
            </div>
            
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date & Time</th>
                                <th>User</th>
                                <th>Module</th>
                                <th>Action</th>
                                <th>Description</th>
                                <th>IP Address</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($log = $logs->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo date('Y-m-d H:i:s', strtotime($log['created_at'])); ?></td>
                                <td><?php echo htmlspecialchars($log['username'] ?? 'System'); ?></td>
                                <td><?php echo ucfirst($log['module']); ?></td>
                                <td><?php echo ucfirst($log['action']); ?></td>
                                <td><?php echo htmlspecialchars($log['description']); ?></td>
                                <td><?php echo $log['ip_address']; ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($total_pages > 1): ?>
                <nav aria-label="Activity log pagination" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page-1; ?>&module=<?php echo $_GET['module'] ?? ''; ?>&user=<?php echo $_GET['user'] ?? ''; ?>&date_from=<?php echo $_GET['date_from'] ?? ''; ?>&date_to=<?php echo $_GET['date_to'] ?? ''; ?>">Previous</a>
                        </li>
                        
                        <?php for($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&module=<?php echo $_GET['module'] ?? ''; ?>&user=<?php echo $_GET['user'] ?? ''; ?>&date_from=<?php echo $_GET['date_from'] ?? ''; ?>&date_to=<?php echo $_GET['date_to'] ?? ''; ?>"><?php echo $i; ?></a>
                        </li>
                        <?php endfor; ?>
                        
                        <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page+1; ?>&module=<?php echo $_GET['module'] ?? ''; ?>&user=<?php echo $_GET['user'] ?? ''; ?>&date_from=<?php echo $_GET['date_from'] ?? ''; ?>&date_to=<?php echo $_GET['date_to'] ?? ''; ?>">Next</a>
                        </li>
                    </ul>
                </nav>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/admin.js"></script>
</body>
</html>