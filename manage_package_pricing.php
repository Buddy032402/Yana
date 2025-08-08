<?php
session_start();
if (!isset($_SESSION["admin"])) {
    header("Location: login.php");
    exit;
}

include "../db.php";

$package_id = isset($_GET['package_id']) ? (int)$_GET['package_id'] : 0;

// Fetch package details
$stmt = $conn->prepare("SELECT name, price as base_price FROM packages WHERE id = ?");
$stmt->bind_param("i", $package_id);
$stmt->execute();
$package = $stmt->get_result()->fetch_assoc();

if (!$package) {
    header("Location: manage_packages.php");
    exit;
}

// Handle pricing tier creation/update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_tier'])) {
    $tier_id = $_POST['tier_id'] ?? null;
    $name = $_POST['name'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $max_persons = $_POST['max_persons'];
    
    if ($tier_id) {
        // Update existing tier
        $stmt = $conn->prepare("UPDATE package_pricing_tiers SET name = ?, price = ?, description = ?, max_persons = ? WHERE id = ? AND package_id = ?");
        $stmt->bind_param("sdssii", $name, $price, $description, $max_persons, $tier_id, $package_id);
    } else {
        // Create new tier
        $stmt = $conn->prepare("INSERT INTO package_pricing_tiers (package_id, name, price, description, max_persons) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("isdss", $package_id, $name, $price, $description, $max_persons);
    }
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "✅ Pricing tier " . ($tier_id ? "updated" : "added") . " successfully";
    } else {
        $_SESSION['error'] = "❌ Error " . ($tier_id ? "updating" : "adding") . " pricing tier";
    }
    
    header("Location: manage_package_pricing.php?package_id=" . $package_id);
    exit;
}

// Handle tier deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_tier'])) {
    $tier_id = $_POST['tier_id'];
    
    $stmt = $conn->prepare("DELETE FROM package_pricing_tiers WHERE id = ? AND package_id = ?");
    $stmt->bind_param("ii", $tier_id, $package_id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "✅ Pricing tier deleted successfully";
    } else {
        $_SESSION['error'] = "❌ Error deleting pricing tier";
    }
    
    header("Location: manage_package_pricing.php?package_id=" . $package_id);
    exit;
}

// Fetch pricing tiers
$stmt = $conn->prepare("SELECT * FROM package_pricing_tiers WHERE package_id = ? ORDER BY price ASC");
$stmt->bind_param("i", $package_id);
$stmt->execute();
$pricing_tiers = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en" data-theme="<?php echo $_SESSION['theme'] ?? 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Package Pricing - Admin Dashboard</title>
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
                <h1>Pricing Tiers: <?php echo htmlspecialchars($package['name']); ?></h1>
                <a href="manage_packages.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Packages
                </a>
            </div>
        </header>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success">
                <?php 
                    echo $_SESSION['message'];
                    unset($_SESSION['message']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php 
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Add New Pricing Tier</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" id="tierForm">
                            <input type="hidden" name="tier_id" id="tier_id">
                            
                            <div class="mb-3">
                                <label class="form-label">Tier Name</label>
                                <input type="text" name="name" id="tier_name" class="form-control" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Price</label>
                                <input type="number" name="price" id="tier_price" class="form-control" 
                                       step="0.01" min="0" required>
                                <small class="text-muted">Base price: $<?php echo number_format($package['base_price'], 2); ?></small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Max Persons</label>
                                <input type="number" name="max_persons" id="tier_max_persons" class="form-control" 
                                       min="1" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" id="tier_description" class="form-control" 
                                          rows="4" required></textarea>
                            </div>
                            
                            <button type="submit" name="save_tier" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Tier
                            </button>
                            
                            <button type="button" class="btn btn-secondary" onclick="resetForm()">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Pricing Tiers</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Price</th>
                                        <th>Max Persons</th>
                                        <th>Description</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($tier = $pricing_tiers->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($tier['name']); ?></td>
                                            <td>$<?php echo number_format($tier['price'], 2); ?></td>
                                            <td><?php echo $tier['max_persons']; ?></td>
                                            <td><?php echo htmlspecialchars($tier['description']); ?></td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-primary" 
                                                        onclick='editTier(<?php echo json_encode($tier); ?>)'>
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form method="POST" class="d-inline" 
                                                      onsubmit="return confirm('Delete this pricing tier?');">
                                                    <input type="hidden" name="tier_id" value="<?php echo $tier['id']; ?>">
                                                    <button type="submit" name="delete_tier" class="btn btn-sm btn-danger">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function editTier(tier) {
    document.getElementById('tier_id').value = tier.id;
    document.getElementById('tier_name').value = tier.name;
    document.getElementById('tier_price').value = tier.price;
    document.getElementById('tier_max_persons').value = tier.max_persons;
    document.getElementById('tier_description').value = tier.description;
}

function resetForm() {
    document.getElementById('tierForm').reset();
    document.getElementById('tier_id').value = '';
}
</script>
</body>
</html>