<?php
session_start();
if (!isset($_SESSION["admin"])) {
    header("Location: login.php");
    exit;
}

// Add CSRF protection
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error'] = "❌ Invalid request";
        header("Location: manage_packages.php");
        exit;
    }
}
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

include "../db.php";

// Handle package deletion
if (isset($_POST['delete_package'])) {
    $package_id = $_POST['package_id'];
    
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        // First delete related records in booking_inquiries
        $stmt = $conn->prepare("DELETE FROM booking_inquiries WHERE package_id = ?");
        $stmt->bind_param("i", $package_id);
        $stmt->execute();
        
        // Then delete related records in bookings if they exist
        $stmt = $conn->prepare("DELETE FROM bookings WHERE package_id = ?");
        $stmt->bind_param("i", $package_id);
        $stmt->execute();
        
        // Finally delete the package
        $stmt = $conn->prepare("DELETE FROM packages WHERE id = ?");
        $stmt->bind_param("i", $package_id);
        
        if ($stmt->execute()) {
            $conn->commit();
            $_SESSION['message'] = "✅ Package deleted successfully";
        } else {
            throw new Exception("Error deleting package");
        }
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "❌ Error deleting package: " . $e->getMessage();
    }
    
    header("Location: manage_packages.php");
    exit;
}

// Pagination setup
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Build query conditions
$where_clauses = [];
$params = [];
$param_types = "";

if (isset($_GET['search']) && $_GET['search'] !== '') {
    $where_clauses[] = "(p.name LIKE ? OR p.description LIKE ?)";
    $search = "%" . $_GET['search'] . "%";
    $params[] = $search;
    $params[] = $search;
    $param_types .= "ss";
}

if (isset($_GET['destination']) && $_GET['destination'] !== '') {
    $where_clauses[] = "p.destination_id = ?";
    $params[] = $_GET['destination'];
    $param_types .= "i";
}

if (isset($_GET['status']) && $_GET['status'] !== '') {
    $where_clauses[] = "p.status = ?";
    $params[] = $_GET['status'];
    $param_types .= "i";
}

$where_sql = !empty($where_clauses) ? "WHERE " . implode(" AND ", $where_clauses) : "";

// Get total packages count
$count_sql = "SELECT COUNT(*) as count FROM packages p $where_sql";
$stmt = $conn->prepare($count_sql);
if (!empty($params)) {
    $stmt->bind_param($param_types, ...$params);
}
$stmt->execute();
$total_packages = $stmt->get_result()->fetch_assoc()['count'];
$total_pages = ceil($total_packages / $limit);


$sql = "SELECT p.*, d.name as destination_name,
        p.description, p.activities, p.includes, p.excludes, 
        p.itinerary, p.group_pricing,
        p.booking_requirements, p.payment_options, p.gallery_images,
        p.max_persons, p.status
    FROM packages p 
    LEFT JOIN destinations d ON p.destination_id = d.id 
    $where_sql 
    ORDER BY p.id DESC 
    LIMIT ? OFFSET ?";

$param_types .= "ii";
$params[] = $limit;
$params[] = $offset;

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($param_types, ...$params);
}
$stmt->execute();
$packages = $stmt->get_result();

// Get all destinations for filter
$destinations = $conn->query("SELECT id, name FROM destinations WHERE status = 1 ORDER BY name");
?>

<!DOCTYPE html>
<html lang="en" data-theme="<?php echo $_SESSION['theme'] ?? 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Packages - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/package-details.css">
    <link rel="stylesheet" href="css/manage-packages.css">
</head>
<body>

<div class="admin-container">
    <?php include "includes/sidebar.php"; ?>

    <main class="main-content">
        <header class="dashboard-header">
            <div class="header-content">
                <h1>Manage Packages</h1>
                <a href="add_package.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Package
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

        <!-- Add auto-dismiss script for success and error messages -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Auto-dismiss success messages
                const successMessage = document.querySelector('.alert-success');
                if (successMessage) {
                    setTimeout(function() {
                        successMessage.style.transition = 'opacity 0.5s ease';
                        successMessage.style.opacity = '0';
                        setTimeout(function() {
                            successMessage.style.display = 'none';
                        }, 500);
                    }, 3000);
                }
                
                // Auto-dismiss error messages
                const errorMessage = document.querySelector('.alert-danger');
                if (errorMessage) {
                    setTimeout(function() {
                        errorMessage.style.transition = 'opacity 0.5s ease';
                        errorMessage.style.opacity = '0';
                        setTimeout(function() {
                            successMessage.style.display = 'none';
                        }, 500);
                    }, 3000);
                }
            });
        </script>

        <div class="card">
            <div class="card-header">
                <form class="row g-3">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" 
                               placeholder="Search packages..." 
                               value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                    </div>
                    
                    <div class="col-md-3">
                        <select name="destination" class="form-select">
                            <option value="">All Destinations</option>
                            <?php while($destination = $destinations->fetch_assoc()): ?>
                                <option value="<?php echo $destination['id']; ?>" 
                                        <?php echo isset($_GET['destination']) && $_GET['destination'] == $destination['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($destination['name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="1" <?php echo isset($_GET['status']) && $_GET['status'] === '1' ? 'selected' : ''; ?>>Active</option>
                            <option value="0" <?php echo isset($_GET['status']) && $_GET['status'] === '0' ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                    
                    <div class="col-md-1">
                        <div class="bg-info text-white p-1 rounded text-center" style="background-color: #e6f7ff !important; color: #000 !important; padding: 6px !important;">
                            Total: <?php echo $total_packages; ?>
                        </div>
                    </div>
                </form>
            </div>
            
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr class="text-center">
                                <th>Image</th>
                                <th>Name</th>
                                <th>Destination</th>
                                <th>Duration</th>
                                <th>Price</th>
                                <th>Available Slots</th>             
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($package = $packages->fetch_assoc()): ?>
                            <tr class="text-center align-middle">
                                <td>
                                    <?php if (!empty($package['image']) && file_exists("../uploads/packages/" . $package['image'])): ?>
                                        <img src="../uploads/packages/<?php echo htmlspecialchars($package['image']); 
                                             htmlspecialchars($package['image']);?>"
                                             alt="<?php echo htmlspecialchars($package['name']);?>"
                                             class="img-thumbnail package-thumb">
                                    <?php else: ?>
                                        <img src="../images/placeholder.jpg" alt="No image" class="img-thumbnail package-thumb">
                                    <?php endif; ?>
                                </td>
                                
                                <td><?php echo htmlspecialchars($package['name']); ?></td>
                                <td><?php echo htmlspecialchars($package['destination_name']); ?></td>
                                <td><?php echo $package['duration']; ?> days</td>
                                <td>₱<?php echo number_format($package['price'], 2); ?></td>
                                <td><?php echo $package['available_slots']; ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $package['status'] ? 'success' : 'secondary'; ?>">
                                        <?php echo $package['status'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" 
                                                class="btn btn-info btn-sm viewPackageBtn" 
                                                onclick='viewPackage(<?php echo json_encode($package, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE); ?>)'
                                                title="View">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        <a href="edit_package.php?id=<?php echo $package['id']; ?>" 
                                           class="btn btn-primary btn-sm" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-danger btn-sm" 
                                                onclick="deletePackage(<?php echo $package['id']; ?>)"
                                                title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($total_pages > 1): ?>
                <nav aria-label="Package pagination" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page-1; ?>&search=<?php echo $_GET['search'] ?? ''; ?>&destination=<?php echo $_GET['destination'] ?? ''; ?>&status=<?php echo $_GET['status'] ?? ''; ?>">Previous</a>
                        </li>
                        
                        <?php for($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo $_GET['search'] ?? ''; ?>&destination=<?php echo $_GET['destination'] ?? ''; ?>&status=<?php echo $_GET['status'] ?? ''; ?>"><?php echo $i; ?></a>
                        </li>
                        <?php endfor; ?>
                        
                        <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page+1; ?>&search=<?php echo $_GET['search'] ?? ''; ?>&destination=<?php echo $_GET['destination'] ?? ''; ?>&status=<?php echo $_GET['status'] ?? ''; ?>">Next</a>
                        </li>
                    </ul>
                </nav>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<!-- Add Bootstrap and other necessary scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Delete Package Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this package?
            </div>
            <div class="modal-footer">
                <form method="POST">
                    <input type="hidden" name="package_id" id="deletePackageId">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="delete_package" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Package View Modal -->
<div class="modal fade" id="packageViewModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="packageViewModalLabel">
                    <i class="fas fa-suitcase me-2"></i>Package Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="packageDetails">
                <!-- Content will be loaded dynamically -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
// Delete package function
function deletePackage(packageId) {
    document.getElementById('deletePackageId').value = packageId;
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    deleteModal.show();
}


function viewPackage(package) {
    try {
        const modalElement = document.getElementById('packageViewModal');
        const modal = new bootstrap.Modal(modalElement);
        const detailsContainer = document.getElementById('packageDetails');
        
        // Parse gallery images
        const galleryImages = package.gallery_images ? JSON.parse(package.gallery_images) : [];
        const galleryHtml = galleryImages.length > 0 ? `
            <div class="gallery-section mt-4">
                <h5>Gallery Images</h5>
                <div class="row g-2">
                    ${galleryImages.map(img => `
                        <div class="col-md-2 col-4">
                            <img src="../uploads/packages/gallery/${img}" 
                                 alt="Gallery image" 
                                 class="gallery-thumb"
                                 onclick="showFullImage('../uploads/packages/gallery/${img}')">
                        </div>
                    `).join('')}
                </div>
            </div>` : '';
        
        const content = `
            <div class="package-details">
                <div class="row">
                    <!-- Media Section -->
                    <div class="col-md-4">
                        <div class="media-section">
                            <div class="main-image-container">
                                <img src="../uploads/packages/${package.image}" 
                                     alt="${package.name}" 
                                     class="img-fluid rounded main-image"
                                     onclick="showFullImage('../uploads/packages/${package.image}')">
                            </div>
                            ${galleryHtml}
                        </div>
                    </div>
                    
                    <!-- Package Information -->
                    <div class="col-md-8">
                        <div class="package-header d-flex justify-content-between align-items-start mb-4">
                            <div>
                                <h3 class="package-title">${package.name}</h3>
                                <p class="text-muted mb-2">
                                    <i class="fas fa-map-marker-alt me-2"></i>${package.destination_name}
                                </p>
                            </div>
                            <div class="package-status">
                                <span class="badge bg-${package.status ? 'success' : 'secondary'}">
                                    ${package.status ? 'Active' : 'Inactive'}
                                </span>
                            </div>
                        </div>
                    
                    <!-- Key Information Cards -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-3 col-6">
                            <div class="info-card">
                                <div class="info-icon"><i class="fas fa-clock"></i></div>
                                <div class="info-details">
                                    <h6>Duration</h6>
                                    <p>${package.duration} days</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="info-card">
                                <div class="info-icon"><i class="fas fa-peso-sign"></i></div>
                                <div class="info-details">
                                    <h6>Starting Price</h6>
                                    <p>₱${parseFloat(package.price).toLocaleString()}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="info-card">
                                <div class="info-icon"><i class="fas fa-users"></i></div>
                                <div class="info-details">
                                    <h6>Capacity</h6>
                                    <p>${package.available_slots}/${package.max_persons}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="info-card">
                                <div class="info-icon"><i class="fas fa-tag"></i></div>
                                <div class="info-details">
                                    <h6>Category</h6>
                                    <p>${package.category || 'General'}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tabs Section -->
                    <div class="package-tabs mt-4">
                        <ul class="nav nav-tabs nav-fill" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" href="#overview">
                                    <i class="fas fa-info-circle me-2"></i>Overview
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#inclusions">
                                    <i class="fas fa-list-check me-2"></i>Inclusions
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#itinerary">
                                    <i class="fas fa-route me-2"></i>Itinerary
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#booking">
                                    <i class="fas fa-book me-2"></i>Booking Info
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content p-4 border border-top-0 rounded-bottom">
                            <!-- Overview Tab -->
                            <div class="tab-pane fade show active" id="overview">
                                <h5 class="section-title">Description</h5>
                                <div class="content-section">
                                    ${package.description || 'No description available'}
                                </div>
                                
                                <h5 class="section-title mt-4">Activities</h5>
                                <div class="content-section">
                                    ${package.activities || 'No activities listed'}
                                </div>
                            </div>

                            <!-- Inclusions Tab -->
                            <div class="tab-pane fade" id="inclusions">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="inclusion-card">
                                            <h5 class="text-success"><i class="fas fa-check-circle me-2"></i>Includes</h5>
                                            ${package.includes || 'Not specified'}
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="inclusion-card">
                                            <h5 class="text-danger"><i class="fas fa-times-circle me-2"></i>Excludes</h5>
                                            ${package.excludes || 'Not specified'}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Itinerary Tab -->
                            <div class="tab-pane fade" id="itinerary">
                                <div class="itinerary-section">
                                    ${package.itinerary || 'No itinerary available'}
                                </div>
                            </div>

                            <!-- Booking Info Tab -->
                            <div class="tab-pane fade" id="booking">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="booking-card">
                                            <h5><i class="fas fa-clipboard-list me-2"></i>Requirements</h5>
                                            ${package.booking_requirements || 'No requirements specified'}
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="booking-card">
                                            <h5><i class="fas fa-credit-card me-2"></i>Payment Options</h5>
                                            ${package.payment_options || 'No payment options specified'}
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="pricing-section mt-4">
                                    <h5><i class="fas fa-tags me-2"></i>Group Pricing</h5>
                                    <div class="pricing-details">
                                        ${package.group_pricing || 'No group pricing available'}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        
        detailsContainer.innerHTML = content;
        modal.show();
    } catch (error) {
        console.error('Error displaying package details:', error);
        alert('Error displaying package details. Please try again.');
    }
}

// Image viewer function
function showFullImage(src) {
    try {
        const img = new Image();
        img.src = src;
        img.onload = function() {
            const maxWidth = window.innerWidth * 0.8;
            const maxHeight = window.innerHeight * 0.8;
            const ratio = Math.min(maxWidth / this.width, maxHeight / this.height);
            
            const width = this.width * ratio;
            const height = this.height * ratio;
            
            window.open(src, '_blank', `width=${width},height=${height}`);
        };
    } catch (error) {
        console.error('Error opening image:', error);
    }
}

// Initialize all tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>

<style>
/* Base Styles */
.package-details {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

/* Image Styles */
.package-thumb {
    width: 60px !important;  /* Force small size */
    height: 45px !important; /* Force small size */
    object-fit: cover;
    border-radius: 4px;
}

.gallery-thumb {
    width: 60px !important;    /* Force small size */
    height: 45px !important;   /* Force small size */
    object-fit: cover;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.3s ease;
}

/* Remove conflicting gallery-thumb styles */
.gallery-section .gallery-thumb {
    width: 60px !important;
    height: 45px !important;
}

/* Force small size even on larger screens */
@media (min-width: 768px) {
    .package-thumb,
    .gallery-thumb {
        width: 60px !important;
        height: 45px !important;
    }
}

.gallery-thumb:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

/* Enhanced Info Card Styles */
.pricing-card,
.timing-card {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    background: #ffffff;
    padding: 1.25rem;
    border-radius: 10px;
    border: 1px solid rgba(0,0,0,0.1);
    height: 100%;
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.pricing-card:hover,
.timing-card:hover {
    transform: scale(1.02);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.card-icon {
    background: rgba(13, 110, 253, 0.1);
    padding: 1rem;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 48px;
}

.card-icon i {
    font-size: 1.5rem;
    color: #0d6efd;
}

.card-content {
    flex: 1;
}

.card-content h6 {
    color: #344767;
    font-weight: 600;
    margin-bottom: 0.75rem;
    font-size: 1rem;
}

.pricing-details,
.timing-details {
    color: #67748e;
    font-size: 0.95rem;
    line-height: 1.6;
    white-space: pre-line;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .pricing-card,
    .timing-card {
        padding: 1rem;
    }
    
    .card-icon {
        padding: 0.5rem;
    }
    
    .card-icon i {
        font-size: 1rem;
    }
}

.info-card {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1rem;
}

.info-card h6 {
    color: #6c757d;
    margin-bottom: 0.25rem;
    font-weight: 500;
}

.info-card p {
    margin-bottom: 0;
    font-weight: 500;
}

/* Tab Styles */
.nav-tabs .nav-link {
    color: #495057;
    padding: 0.75rem 1.25rem;
    border-radius: 0.25rem 0.25rem 0 0;
}

.nav-tabs .nav-link.active {
    font-weight: 500;
    background-color: #fff;
    border-color: #dee2e6 #dee2e6 #fff;
}

.tab-content {
    background: #fff;
    min-height: 200px;
    padding: 1.5rem;
    border: 1px solid #dee2e6;
    border-top: none;
}

/* Button Styles */
.btn-group .btn {
    padding: 0.375rem 0.75rem;
    margin: 0 2px;
    border-radius: 4px;
}

.btn-group .btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

.btn-group .btn i {
    margin-right: 0;
}

/* Modal Styles */
.modal-xl {
    max-width: 90%;
}

/* Responsive Styles */
@media (max-width: 768px) {
    .modal-xl {
        max-width: 95%;
    }
    
    .info-card {
        margin-bottom: 1rem;
    }
    
    .btn-group .btn {
        padding: 0.25rem 0.5rem;
    }
}
</style>
</body>
</html>