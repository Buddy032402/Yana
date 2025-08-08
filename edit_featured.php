<?php
session_start();
if (!isset($_SESSION["admin"])) {
    header("Location: login.php");
    exit;
}

include "../db.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch destination details
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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $featured_order = $_POST['featured_order'];
    $highlight_text = $_POST['highlight_text'];
    
    $conn->begin_transaction();
    
    try {
        // Update featured_destinations table
        $stmt = $conn->prepare("
            UPDATE featured_destinations 
            SET featured_order = ?, highlight_text = ? 
            WHERE destination_id = ?
        ");
        $stmt->bind_param("isi", $featured_order, $highlight_text, $id);
        $stmt->execute();

        $conn->commit();
        $_SESSION['message'] = "✅ Featured destination updated successfully";
        header("Location: manage_featured.php");
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "❌ Error updating featured destination: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en" data-theme="<?php echo $_SESSION['theme'] ?? 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Featured Destination - Admin Dashboard</title>
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
                    <h1>Edit Featured Destination</h1>
                    <a href="manage_featured.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>
            </header>

            <div class="card">
                <div class="card-body">
                    <form method="POST" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label class="form-label">Destination Name</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($destination['name']); ?>" readonly>
                        </div>

                        <div class="mb-3">
                            <label for="featured_order" class="form-label">Display Order</label>
                            <input type="number" class="form-control" id="featured_order" name="featured_order" 
                                   min="1" value="<?php echo $destination['featured_order'] ?? 1; ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="highlight_text" class="form-label">Highlight Text</label>
                            <textarea class="form-control" id="highlight_text" name="highlight_text" 
                                      rows="4" required><?php echo htmlspecialchars($destination['highlight_text'] ?? ''); ?></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Featured Destination
                        </button>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/admin.js"></script>
</body>
</html>