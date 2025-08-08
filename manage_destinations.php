<?php
session_start();
if (!isset($_SESSION["admin"])) {
    header("Location: login.php");
    exit;
}

include "../db.php";

// Add this temporarily and remove after execution
$sql = "ALTER TABLE destinations 
        ADD COLUMN IF NOT EXISTS country VARCHAR(100) DEFAULT NULL,
        ADD COLUMN IF NOT EXISTS featured TINYINT(1) DEFAULT 0,
        ADD COLUMN IF NOT EXISTS status TINYINT(1) DEFAULT 1";
$conn->query($sql);
// Remove the above code after running once

// Add this after your session_start() and before handling any requests
$gallery_dir = "../uploads/destinations/gallery/";
if (!file_exists($gallery_dir)) {
    mkdir($gallery_dir, 0777, true);
}

// Handle destination status toggle
if (isset($_POST['toggle_status'])) {
    $destination_id = $_POST['destination_id'];
    $status = $_POST['status'] ? 0 : 1;
    
    $stmt = $conn->prepare("UPDATE destinations SET status = ? WHERE id = ?");
    $stmt->bind_param("ii", $status, $destination_id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "✅ Destination status updated successfully";
    } else {
        $_SESSION['error'] = "❌ Error updating destination status";
    }
    header("Location: manage_destinations.php");
    exit;
}

// Handle destination deletion
if (isset($_POST['delete_destination'])) {
    $destination_id = $_POST['destination_id'];
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // First get all packages for this destination
        $stmt = $conn->prepare("SELECT id FROM packages WHERE destination_id = ?");
        $stmt->bind_param("i", $destination_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($package = $result->fetch_assoc()) {
            // Delete booking inquiries first
            $stmt = $conn->prepare("DELETE FROM booking_inquiries WHERE package_id = ?");
            $stmt->bind_param("i", $package['id']);
            $stmt->execute();
            
            // Delete package reviews
            $stmt = $conn->prepare("DELETE FROM package_reviews WHERE package_id = ?");
            $stmt->bind_param("i", $package['id']);
            $stmt->execute();
            
            // Delete bookings
            $stmt = $conn->prepare("DELETE FROM bookings WHERE package_id = ?");
            $stmt->bind_param("i", $package['id']);
            $stmt->execute();
        }
        
        // Delete packages
        $stmt = $conn->prepare("DELETE FROM packages WHERE destination_id = ?");
        $stmt->bind_param("i", $destination_id);
        $stmt->execute();
        
        // Delete top picks entries if they exist
        $stmt = $conn->prepare("DELETE FROM top_picks WHERE destination_id = ?");
        $stmt->bind_param("i", $destination_id);
        $stmt->execute();
        
        // Finally delete the destination
        $stmt = $conn->prepare("DELETE FROM destinations WHERE id = ?");
        $stmt->bind_param("i", $destination_id);
        $stmt->execute();
        
        // Commit transaction
        $conn->commit();
        $_SESSION['message'] = "✅ Destination and related data deleted successfully";
        
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        $_SESSION['error'] = "❌ Error deleting destination: " . $e->getMessage();
    }
    
    header("Location: manage_destinations.php");
    exit;
} // Remove everything between here and pagination setup

// Pagination setup
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Build query conditions
$where_clauses = [];
$params = [];
$param_types = "";

if (isset($_GET['search']) && $_GET['search'] !== '') {
    $where_clauses[] = "(name LIKE ? OR country LIKE ? OR description LIKE ?)";
    $search = "%" . $_GET['search'] . "%";
    $params[] = $search;
    $params[] = $search;
    $params[] = $search;
    $param_types .= "sss";
}

if (isset($_GET['status']) && $_GET['status'] !== '') {
    $where_clauses[] = "status = ?";
    $params[] = $_GET['status'];
    $param_types .= "i";
}

$where_sql = !empty($where_clauses) ? "WHERE " . implode(" AND ", $where_clauses) : "";

// Get total destinations count
$count_sql = "SELECT COUNT(*) as count FROM destinations $where_sql";
$stmt = $conn->prepare($count_sql);
if (!empty($params)) {
    $stmt->bind_param($param_types, ...$params);
}
$stmt->execute();
$total_destinations = $stmt->get_result()->fetch_assoc()['count'];
$total_pages = ceil($total_destinations / $limit);

// Get destinations for current page
$sql = "SELECT * FROM destinations $where_sql ORDER BY name ASC LIMIT ? OFFSET ?";
$param_types .= "ii";
$params[] = $limit;
$params[] = $offset;
$stmt = $conn->prepare($sql);
$stmt->bind_param($param_types, ...$params);
$stmt->execute();
$destinations = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en" data-theme="<?php echo $_SESSION['theme'] ?? 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Destinations - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/admin.css">
    <style>
        .sidebar {
            background: linear-gradient(135deg, #1a237e, #283593, #303f9f, #3949ab, #3f51b5);
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
        }
        
        .sidebar-menu li.active a {
            background: linear-gradient(to right, rgba(26, 35, 126, 0.9), rgba(63, 81, 181, 0.7));
            border-left: 3px solid #fff;
        }
        
        /* Action button styling */
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            border-radius: 0.2rem;
            margin: 0 2px;
        }
        
        .table td .btn-sm {
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body>

<div class="admin-container">
    <?php include "includes/sidebar.php"; ?>
    
    <main class="main-content">
        <header class="dashboard-header">
            <div class="header-content">
                <h1>Manage Destinations</h1>
                <a href="add_destination.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Destination
                </a>
            </div>
        </header>
        <!-- Rest of your content -->

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
                            errorMessage.style.display = 'none';
                        }, 500);
                    }, 3000);
                }
            });
        </script>

        <div class="card">
            <div class="card-header">
                <form class="row g-3" method="GET" action="">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" 
                               placeholder="Search destinations..." 
                               value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                    </div>
                    
                    <div class="col-md-2">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="1" <?php echo isset($_GET['status']) && $_GET['status'] === '1' ? 'selected' : ''; ?>>Active</option>
                            <option value="0" <?php echo isset($_GET['status']) && $_GET['status'] === '0' ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                    <div class="col-md-2">
                        <a href="manage_destinations.php" class="btn btn-secondary w-100">
                            <i class="fas fa-redo"></i> Reset
                        </a>
                    </div>
                    <div class="col-md-2">
                        <div class="alert alert-info mb-0 text-center">
                            <strong>Total: <?php echo $total_destinations; ?></strong>
                        </div>
                    </div>
                </form>
            </div>
            
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">       
                        <thead>
                            <tr class="text-center">
                                <th>Image</th>
                                <th>Name</th>
                                <th>Country</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Last Updated</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($destination = $destinations->fetch_assoc()): ?>
                            <tr data-id="<?php echo $destination['id']; ?>">
                                <td>
                                    <?php 
                                        $image_path = "../uploads/destinations/" . $destination['image'];
                                        if (!empty($destination['image']) && file_exists($image_path)): 
                                    ?>
                                        <img src="<?php echo $image_path; ?>"
                                             alt="<?php echo htmlspecialchars($destination['name']); ?>"
                                             class="img-thumbnail mx-auto d-block"
                                             style="width: 100px; height: 100px; object-fit: cover;">
                                    <?php else: ?>
                                        <img src="../images/placeholder.jpg"
                                             alt="No image available"
                                             class="img-thumbnail mx-auto d-block"
                                             style="width: 100px; height: 100px; object-fit: cover;">
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($destination['name']); ?></td>
                                <td><?php echo htmlspecialchars($destination['country'] ?? 'N/A'); ?></td>
                                <td style="max-width: 200px;">
                                    <div class="text-truncate">
                                        <?php echo htmlspecialchars($destination['description']); ?>
                                    </div>
                                </td>
                                <td>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="destination_id" value="<?php echo $destination['id']; ?>">
                                        <input type="hidden" name="status" value="<?php echo $destination['status']; ?>">
                                        <button type="submit" name="toggle_status" class="btn btn-sm btn-<?php echo $destination['status'] == 1 ? 'success' : 'danger'; ?>">
                                            <?php echo $destination['status'] == 1 ? 'Active' : 'Inactive'; ?>
                                        </button>
                                    </form>
                                </td>
                                <td><?php echo date('Y-m-d H:i', strtotime($destination['updated_at'])); ?></td>
                                <td>
                                    <div class="d-flex justify-content-center">
                                        <button type="button" class="btn btn-sm btn-info me-1" 
                                                onclick='viewDestination(<?php echo json_encode($destination); ?>)'>
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <a href="edit_destinations.php?id=<?php echo $destination['id']; ?>" 
                                           class="btn btn-sm btn-warning me-1">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="POST" class="d-inline" 
                                              onsubmit="return confirm('Are you sure you want to delete this destination?');">
                                            <input type="hidden" name="destination_id" value="<?php echo $destination['id']; ?>">
                                            <button type="submit" name="delete_destination" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($total_pages > 1): ?>
                <nav aria-label="Destination pagination" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page-1; ?>&search=<?php echo $_GET['search'] ?? ''; ?>&status=<?php echo $_GET['status'] ?? ''; ?>">Previous</a>
                        </li>
                        
                        <?php for($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo $_GET['search'] ?? ''; ?>&status=<?php echo $_GET['status'] ?? ''; ?>"><?php echo $i; ?></a>
                        </li>
                        <?php endfor; ?>
                        
                        <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page+1; ?>&search=<?php echo $_GET['search'] ?? ''; ?>&status=<?php echo $_GET['status'] ?? ''; ?>">Next</a>
                        </li>
                    </ul>
                </nav>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<!-- Add Destination Modal -->
<div class="modal fade" id="addDestinationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Destination</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data" action="add_destination.php">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Destination Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Country</label>
                        <input type="text" name="country" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="4" required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Image</label>
                        <input type="file" name="image" class="form-control" accept="image/*" required>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="featured" class="form-check-input" value="1">
                            <label class="form-check-label">Featured Destination</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Add Destination</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Destination Modal -->
<div class="modal fade" id="editDestinationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Destination</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data" action="edit_destination.php">
                <input type="hidden" name="destination_id" id="edit_destination_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Destination Name</label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Country</label>
                        <input type="text" name="country" id="edit_country" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="edit_description" class="form-control" rows="4" required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Image</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                        <small class="text-muted">Leave empty to keep current image</small>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="featured" id="edit_featured" class="form-check-input" value="1">
                            <label class="form-check-label">Featured Destination</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update Destination</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Update the View Destination Modal structure -->
<div class="modal fade" id="viewDestinationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-info-circle me-2"></i>Destination Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="destination-image-container">
                            <img id="view_image" src="" alt="Destination Image" class="img-fluid rounded shadow">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="destination-info">
                            <h3 id="view_name" class="border-bottom pb-2 mb-3"></h3>
                            <div class="info-grid">
                                <div class="info-item">
                                    <i class="fas fa-map-marker-alt text-primary"></i>
                                    <strong>Location:</strong> <span id="view_country"></span>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-check-circle text-success"></i>
                                    <strong>Status:</strong> <span id="view_status"></span>
                                </div>
                                <!-- Featured item removed from here -->
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="info-sections">
                    <div class="info-section">
                        <h5><i class="fas fa-align-left text-primary me-2"></i>Description</h5>
                        <div class="content-box" id="view_description"></div>
                    </div>

                    <div class="info-section">
                        <h5><i class="fas fa-calendar-alt text-success me-2"></i>Best Time to Visit</h5>
                        <div class="content-box" id="view_best_time"></div>
                    </div>

                    <div class="info-section">
                        <h5><i class="fas fa-passport text-info me-2"></i>Travel Requirements</h5>
                        <div class="content-box" id="view_travel_requirements"></div>
                    </div>

                    <div class="info-section">
                        <h5><i class="fas fa-bus text-warning me-2"></i>Transportation Details</h5>
                        <div class="content-box" id="view_transportation"></div>
                    </div>

                    <div id="gallery_images_container" class="info-section">
                        <h5><i class="fas fa-images text-danger me-2"></i>Gallery Images</h5>
                        <div id="view_gallery" class="row g-3"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Add these styles -->
<style>
.destination-image-container {
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

.destination-info {
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    height: 100%;
}

.info-grid {
    display: grid;
    gap: 15px;
}

.info-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px;
    background: white;
    border-radius: 6px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.info-sections {
    display: grid;
    gap: 20px;
    padding: 20px 0;
}

.info-section {
    background: white;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.info-section h5 {
    color: #333;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 2px solid #e9ecef;
}

.content-box {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 6px;
    line-height: 1.6;
}

#view_gallery .col-md-4 {
    margin-bottom: 20px;
}

.gallery-image-container {
    position: relative;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
}

.gallery-image-container:hover {
    transform: scale(1.02);
}

.gallery-image-label {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: rgba(0,0,0,0.7);
    color: white;
    padding: 8px;
    font-size: 14px;
    text-align: center;
}

#viewDestinationModal .modal-dialog {
    max-width: 900px;
}
</style>
<script>
function viewDestination(destination) {
    try {
        const modal = new bootstrap.Modal(document.getElementById('viewDestinationModal'));
        
        // Set basic destination info
        document.getElementById('view_name').textContent = destination.name || 'Unnamed Destination';
        document.getElementById('view_country').textContent = destination.country || 'N/A';
        document.getElementById('view_status').textContent = destination.status == 1 ? 'Active' : 'Inactive';
        
        // Set description with fallback
        const description = document.getElementById('view_description');
        description.textContent = destination.description || 'No description available';
        
        // Set additional fields with fallbacks
        document.getElementById('view_best_time').textContent = 
            destination.best_time_to_visit || 'Not specified';
        document.getElementById('view_travel_requirements').textContent = 
            destination.travel_requirements || 'Not specified';
        document.getElementById('view_transportation').textContent = 
            destination.transportation_details || 'Not specified';

        // Handle main image
        const imagePath = destination.image ? 
            '../uploads/destinations/' + destination.image : 
            '../images/placeholder.jpg';
        document.getElementById('view_image').src = imagePath;
        
        // Handle gallery images
        const galleryContainer = document.getElementById('view_gallery');
        galleryContainer.innerHTML = '';
        
        if (destination.gallery_images && destination.gallery_images !== '[]') {
            try {
                const galleryImages = typeof destination.gallery_images === 'string' ? 
                    JSON.parse(destination.gallery_images) : 
                    destination.gallery_images;
                
                if (Array.isArray(galleryImages) && galleryImages.length > 0) {
                    galleryImages.forEach(image => {
                        if (!image) return;
                        
                        const col = document.createElement('div');
                        col.className = 'col-md-4 mb-3';
                        
                        const imgContainer = document.createElement('div');
                        imgContainer.className = 'gallery-image-container';
                        
                        const img = document.createElement('img');
                        img.src = '../uploads/destinations/gallery/' + image;
                        img.className = 'img-fluid rounded';
                        img.style.width = '100%';
                        img.style.height = '200px';
                        img.style.objectFit = 'cover';
                        img.onerror = function() {
                            this.src = '../images/placeholder.jpg';
                        };
                        
                        const label = document.createElement('div');
                        label.className = 'gallery-image-label';
                        label.textContent = 'Gallery Image';
                        
                        imgContainer.appendChild(img);
                        imgContainer.appendChild(label);
                        col.appendChild(imgContainer);
                        galleryContainer.appendChild(col);
                    });
                } else {
                    galleryContainer.innerHTML = '<div class="col-12"><p class="text-muted">No gallery images available</p></div>';
                }
            } catch (e) {
                console.error('Error parsing gallery images:', e);
                galleryContainer.innerHTML = '<div class="col-12"><p class="text-muted">Error loading gallery images</p></div>';
            }
        } else {
            galleryContainer.innerHTML = '<div class="col-12"><p class="text-muted">No gallery images available</p></div>';
        }
        
        modal.show();
    } catch (error) {
        console.error('Error in viewDestination:', error);
        alert('Error loading destination details. Please check console for details.');
    }
}
</script>

<!-- Add this style section before the closing </body> tag -->
<style>
    /* Add these new styles for gallery image labels */
    .gallery-image-container {
        position: relative;
        margin-bottom: 10px;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .gallery-image-label {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background-color: rgba(0,0,0,0.6);
        color: white;
        padding: 5px 10px;
        text-align: center;
        font-size: 14px;
    }
    
    #view_gallery img {
        width: 100%;
        height: 200px;
        object-fit: cover;
        transition: transform 0.3s ease;
    }
    
    #view_gallery img:hover {
        transform: scale(1.05);
    }
    
    #view_gallery .col-md-4 {
        margin-bottom: 20px;
    }
</style>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function editDestination(destination) {
    const modal = new bootstrap.Modal(document.getElementById('editDestinationModal'));
    
    // Set form values
    document.getElementById('edit_destination_id').value = destination.id;
    document.getElementById('edit_name').value = destination.name;
    document.getElementById('edit_country').value = destination.country;
    document.getElementById('edit_description').value = destination.description;
    document.getElementById('edit_featured').checked = destination.featured == 1;
    
    modal.show();
}
</script>
<link rel="stylesheet" href="css/admin.css">
<script src="js/admin.js">

</script>
</body>
</html>

<style>
.btn-group {
    display: inline-flex;
    gap: 3px;
    align-items: center;
}

.btn-group .btn {
    padding: 0.375rem 0.75rem;
    line-height: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.btn-group form {
    margin: 0;
    display: inline-flex;
}

.table td {
    vertical-align: middle;
    padding: 0.75rem;
    white-space: nowrap;
}

.table td div.text-truncate {
    max-width: 200px;
    overflow: hidden;
    text-overflow: ellipsis;
}

.btn i {
    font-size: 0.875rem;
}

.badge i {
    margin-right: 3px;
}

pt.table thead th,
.table tbody td {
    text-align: center;
    vertical-align: middle;
}

.table img {
    margin: 0 auto;
    display: block;
}

.table td {
    vertical-align: middle;
}

.btn-group {
    display: flex;
    gap: 5px;
}
</style>