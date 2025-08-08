<?php
session_start();
if (!isset($_SESSION["admin"])) {
    header("Location: login.php");
    exit;
}

include "../db.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $conn->prepare("
    SELECT d.*, fd.featured_order, fd.highlight_text 
    FROM destinations d 
    LEFT JOIN featured_destinations fd ON d.id = fd.destination_id 
    WHERE d.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$destination = $stmt->get_result()->fetch_assoc();

if (!$destination) {
    $_SESSION['error'] = "Destination not found";
    header("Location: manage_featured.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en" data-theme="<?php echo $_SESSION['theme'] ?? 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Featured Destination - Admin Dashboard</title>
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
                    <h1>View Featured Destination</h1>
                    <a href="manage_featured.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>
            </header>

            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <img src="../uploads/destinations/<?php echo $destination['image']; ?>" 
                                 alt="<?php echo htmlspecialchars($destination['name']); ?>"
                                 class="img-fluid rounded">
                        </div>
                        <div class="col-md-8">
                            <h2><?php echo htmlspecialchars($destination['name']); ?></h2>
                            <p class="text-muted"><?php echo htmlspecialchars($destination['country']); ?></p>
                            
                            <div class="mb-3">
                                <h5>Display Order</h5>
                                <p><?php echo $destination['featured_order'] ?? 'N/A'; ?></p>
                            </div>

                            <div class="mb-3">
                                <h5>Highlight Text</h5>
                                <p><?php echo nl2br(htmlspecialchars($destination['highlight_text'] ?? '')); ?></p>
                            </div>

                            <div class="mb-3">
                                <h5>Description</h5>
                                <p><?php echo nl2br(htmlspecialchars($destination['description'])); ?></p>
                            </div>

                            <div class="mt-4">
                                <a href="edit_featured.php?id=<?php echo $destination['id']; ?>" class="btn btn-primary">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <button type="button" class="btn btn-danger" 
                                        onclick="confirmDelete(<?php echo $destination['id']; ?>)">
                                    <i class="fas fa-trash"></i> Remove from Featured
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/admin.js"></script>
    <script>
    function confirmDelete(id) {
        if (confirm('Are you sure you want to remove this destination from featured?')) {
            window.location.href = `remove_featured.php?id=${id}`;
        }
    }
    </script>
</body>
</html>