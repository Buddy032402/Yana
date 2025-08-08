<?php
session_start();
if (!isset($_SESSION["admin"])) {
    header("Location: login.php");
    exit;
}

include "../db.php";

// Handle testimonial deletion
if (isset($_POST['delete_testimonial'])) {
    $testimonial_id = $_POST['testimonial_id'];
    $stmt = $conn->prepare("DELETE FROM testimonials WHERE id = ?");
    $stmt->bind_param("i", $testimonial_id);
    if ($stmt->execute()) {
        $_SESSION['message'] = "✅ Testimonial deleted successfully";
    } else {
        $_SESSION['error'] = "❌ Error deleting testimonial";
    }
    header("Location: manage_testimonials.php");
    exit;
}

// Handle status toggle
if (isset($_POST['toggle_status'])) {
    $testimonial_id = $_POST['testimonial_id'];
    $status = $_POST['status'];
    $new_status = $status == 'approved' ? 'pending' : 'approved';
    
    $stmt = $conn->prepare("UPDATE testimonials SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $testimonial_id);
    $stmt->execute();
    header("Location: manage_testimonials.php");
    exit;
}

// Fetch testimonials with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$total_testimonials = $conn->query("SELECT COUNT(*) as count FROM testimonials")->fetch_assoc()['count'];
$total_pages = ceil($total_testimonials / $limit);

// Update the query to use package_id instead of tour_id
$testimonials = $conn->query("
    SELECT t.*, p.name as package_name, 
           COALESCE(t.image, 'default-avatar.jpg') as display_image
    FROM testimonials t 
    LEFT JOIN packages p ON t.package_id = p.id 
    ORDER BY t.created_at DESC 
    LIMIT $limit OFFSET $offset
");
?>

<!DOCTYPE html>
<html lang="en" data-theme="<?php echo $_SESSION['theme'] ?? 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Testimonials - Admin Dashboard</title>
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
                <h1>Manage Testimonials</h1>
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
                            errorMessage.style.display = 'none';
                        }, 500);
                    }, 3000);
                }
            });
        </script>

        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class="mb-0">Customer Testimonials</h3>
                    <div class="d-flex gap-3 align-items-center">
                        <a href="add_testimonial.php" class="btn btn-primary btn-add">
                            <i class="fas fa-plus-circle me-2"></i> Add Testimonial
                        </a>
                        <div class="search-wrapper">
                            <div class="input-group">
                                <span class="input-group-text border-end-0">
                                    <i class="fas fa-search"></i>
                                </span>
                                <input type="text" class="form-control border-start-0" 
                                       id="searchTestimonials" 
                                       placeholder="Search testimonials...">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="px-4 py-3">User</th>
                                <th class="px-4 py-3">Rating</th>
                                <th class="px-4 py-3">Comment</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Date</th>
                                <th class="px-4 py-3 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="testimonialsTableBody">
                            <?php while($testimonial = $testimonials->fetch_assoc()): ?>
                            <tr class="testimonial-row">
                                <td class="px-4">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle bg-primary text-white me-2">
                                            <?php echo strtoupper(substr($testimonial['customer_name'] ?? 'A', 0, 1)); ?>
                                        </div>
                                        <div>
                                            <div class="fw-semibold"><?php echo htmlspecialchars($testimonial['customer_name'] ?? 'Anonymous'); ?></div>
                                            <div class="text-muted small"><?php echo htmlspecialchars($testimonial['customer_email'] ?? ''); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4">
                                    <div class="rating">
                                        <?php for($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star <?php echo $i <= $testimonial['rating'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                </td>
                                <td class="px-4">
                                    <div class="testimonial-content text-truncate" style="max-width: 300px;">
                                        <?php echo htmlspecialchars($testimonial['content']); ?>
                                    </div>
                                </td>
                                <td class="px-4">
                                    <div class="dropdown">
                                        <button class="btn btn-sm status-dropdown dropdown-toggle <?php 
                                            echo $testimonial['status'] === 'approved' ? 'btn-success' : 
                                                ($testimonial['status'] === 'pending' ? 'btn-warning' : 'btn-danger'); 
                                        ?>" data-bs-toggle="dropdown">
                                            <?php echo ucfirst($testimonial['status']); ?>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <form method="POST" class="status-form">
                                                    <input type="hidden" name="testimonial_id" value="<?php echo $testimonial['id']; ?>">
                                                    <input type="hidden" name="toggle_status" value="1">
                                                    <input type="hidden" name="status" value="<?php echo $testimonial['status']; ?>">
                                                    <button type="submit" class="dropdown-item">
                                                        <i class="fas fa-exchange-alt me-2"></i>
                                                        Toggle Status
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                                <td class="px-4">
                                    <?php echo date('M d, Y', strtotime($testimonial['created_at'])); ?>
                                </td>
                                <td class="actions">
                                    <div class="btn-group">
                                        <a href="view_testimonial.php?id=<?php echo $testimonial['id']; ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="edit_testimonial.php?id=<?php echo $testimonial['id']; ?>" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger" onclick="deleteTestimonial(<?php echo $testimonial['id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- View Modal -->
                            <div class="modal fade" id="viewModal<?php echo $testimonial['id']; ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Testimonial Details</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="fw-bold">User</label>
                                                <p><?php echo htmlspecialchars($testimonial['name'] ?? 'Anonymous'); ?></p>
                                            </div>
                                            <div class="mb-3">
                                                <label class="fw-bold">Rating</label>
                                                <div class="rating">
                                                    <?php for($i = 1; $i <= 5; $i++): ?>
                                                        <i class="fas fa-star <?php echo $i <= $testimonial['rating'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                                    <?php endfor; ?>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="fw-bold">Comment</label>
                                                <p><?php echo nl2br(htmlspecialchars($testimonial['content'])); ?></p>
                                            </div>
                                            <div class="mb-3">
                                                <label class="fw-bold">Date</label>
                                                <p><?php echo date('F d, Y h:i A', strtotime($testimonial['created_at'])); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Add this CSS to your admin.css file -->
        <style>
        .avatar-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        
        .status-dropdown {
            min-width: 100px;
        }
        
        .testimonial-content {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .btn-group .btn {
            padding: 0.25rem 0.5rem;
        }
        
        .table td, .table th {
            vertical-align: middle;
        }
        </style>

        <script>
        function deleteTestimonial(id) {
            if (confirm('Are you sure you want to delete this testimonial?')) {
                window.location.href = `delete_testimonial.php?id=${id}`;
            }
        }
        </script>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <nav aria-label="Testimonial pagination" class="mt-4">
            <ul class="pagination justify-content-center">
                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page-1; ?>">Previous</a>
                </li>
                
                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                </li>
                <?php endfor; ?>
                
                <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page+1; ?>">Next</a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/admin.js"></script>

<!-- Add search functionality -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchTestimonials');
    const testimonialRows = document.querySelectorAll('.testimonial-row');
    
    searchInput.addEventListener('keyup', function() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        
        testimonialRows.forEach(row => {
            // Get the text content from relevant cells
            const customerName = row.querySelector('.fw-semibold').textContent.toLowerCase();
            const customerEmail = row.querySelector('.text-muted.small').textContent.toLowerCase();
            const comment = row.querySelector('.testimonial-content').textContent.toLowerCase();
            const status = row.querySelector('.status-dropdown').textContent.trim().toLowerCase();
            
            // Check if any of the fields contain the search term
            const matchFound = 
                customerName.includes(searchTerm) || 
                customerEmail.includes(searchTerm) || 
                comment.includes(searchTerm) || 
                status.includes(searchTerm);
            
            // Show or hide the row based on the search result
            row.style.display = matchFound ? '' : 'none';
        });
        
        // Show a message if no results found
        const visibleRows = document.querySelectorAll('.testimonial-row[style="display: none;"]');
        const noResultsMessage = document.getElementById('noResultsMessage');
        
        if (visibleRows.length === testimonialRows.length && searchTerm !== '') {
            if (!noResultsMessage) {
                const tbody = document.getElementById('testimonialsTableBody');
                const messageRow = document.createElement('tr');
                messageRow.id = 'noResultsMessage';
                messageRow.innerHTML = `<td colspan="6" class="text-center py-4">No testimonials found matching "${searchTerm}"</td>`;
                tbody.appendChild(messageRow);
            }
        } else if (noResultsMessage) {
            noResultsMessage.remove();
        }
    });
});
</script>
</body>
</html>

<!-- Edit Modal -->
<div class="modal fade" id="editModal<?php echo $testimonial['id']; ?>" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Testimonial</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="update_testimonial.php">
                <div class="modal-body">
                    <input type="hidden" name="testimonial_id" value="<?php echo $testimonial['id']; ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">Rating</label>
                        <select name="rating" class="form-select" required>
                            <?php for($i = 1; $i <= 5; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php echo $testimonial['rating'] == $i ? 'selected' : ''; ?>>
                                    <?php echo str_repeat('⭐', $i); ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Comment</label>
                        <textarea name="content" class="form-control" rows="4" required><?php echo htmlspecialchars($testimonial['content']); ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select" required>
                            <option value="pending" <?php echo $testimonial['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="approved" <?php echo $testimonial['status'] == 'approved' ? 'selected' : ''; ?>>Approved</option>
                            <option value="rejected" <?php echo $testimonial['status'] == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>