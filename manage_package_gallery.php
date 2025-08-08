<?php
session_start();
if (!isset($_SESSION["admin"])) {
    header("Location: login.php");
    exit;
}

include "../db.php";

$package_id = isset($_GET['package_id']) ? (int)$_GET['package_id'] : 0;

// Fetch package details
$stmt = $conn->prepare("SELECT name FROM packages WHERE id = ?");
$stmt->bind_param("i", $package_id);
$stmt->execute();
$package = $stmt->get_result()->fetch_assoc();

if (!$package) {
    header("Location: manage_packages.php");
    exit;
}

// Handle image upload
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['upload'])) {
    $target_dir = "../uploads/packages/gallery/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $uploaded_files = $_FILES['images'];
    $success_count = 0;
    $error_count = 0;
    
    for ($i = 0; $i < count($uploaded_files['name']); $i++) {
        if ($uploaded_files['error'][$i] == 0) {
            $file_extension = strtolower(pathinfo($uploaded_files['name'][$i], PATHINFO_EXTENSION));
            $new_filename = uniqid() . '.' . $file_extension;
            $target_file = $target_dir . $new_filename;
            
            if (move_uploaded_file($uploaded_files['tmp_name'][$i], $target_file)) {
                $caption = $_POST['captions'][$i] ?? '';
                $stmt = $conn->prepare("INSERT INTO package_gallery (package_id, image, caption) VALUES (?, ?, ?)");
                $stmt->bind_param("iss", $package_id, $new_filename, $caption);
                
                if ($stmt->execute()) {
                    $success_count++;
                } else {
                    $error_count++;
                    unlink($target_file); // Delete the uploaded file if DB insert fails
                }
            } else {
                $error_count++;
            }
        }
    }
    
    if ($success_count > 0) {
        $_SESSION['message'] = "✅ Successfully uploaded $success_count images";
    }
    if ($error_count > 0) {
        $_SESSION['error'] = "❌ Failed to upload $error_count images";
    }
    
    header("Location: manage_package_gallery.php?package_id=" . $package_id);
    exit;
}

// Handle image deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete'])) {
    $image_id = $_POST['image_id'];
    
    // Get image filename before deletion
    $stmt = $conn->prepare("SELECT image FROM package_gallery WHERE id = ? AND package_id = ?");
    $stmt->bind_param("ii", $image_id, $package_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($image = $result->fetch_assoc()) {
        $image_path = "../uploads/packages/gallery/" . $image['image'];
        
        $stmt = $conn->prepare("DELETE FROM package_gallery WHERE id = ? AND package_id = ?");
        $stmt->bind_param("ii", $image_id, $package_id);
        
        if ($stmt->execute()) {
            if (file_exists($image_path)) {
                unlink($image_path);
            }
            $_SESSION['message'] = "✅ Image deleted successfully";
        } else {
            $_SESSION['error'] = "❌ Error deleting image";
        }
    }
    
    header("Location: manage_package_gallery.php?package_id=" . $package_id);
    exit;
}

// Update caption
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_caption'])) {
    $image_id = $_POST['image_id'];
    $caption = $_POST['caption'];
    
    $stmt = $conn->prepare("UPDATE package_gallery SET caption = ? WHERE id = ? AND package_id = ?");
    $stmt->bind_param("sii", $caption, $image_id, $package_id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "✅ Caption updated successfully";
    } else {
        $_SESSION['error'] = "❌ Error updating caption";
    }
    
    header("Location: manage_package_gallery.php?package_id=" . $package_id);
    exit;
}

// Fetch gallery images
$stmt = $conn->prepare("SELECT * FROM package_gallery WHERE package_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $package_id);
$stmt->execute();
$gallery = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en" data-theme="<?php echo $_SESSION['theme'] ?? 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Package Gallery - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
    <style>
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.5rem;
            padding: 1.5rem;
        }
        .gallery-item {
            position: relative;
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .gallery-item img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .gallery-actions {
            position: absolute;
            top: 10px;
            right: 10px;
            display: flex;
            gap: 5px;
        }
        .upload-preview {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }
        .preview-item {
            position: relative;
        }
        .preview-item img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 4px;
        }
    </style>
</head>
<body>

<div class="admin-container">
    <?php include "includes/sidebar.php"; ?>

    <main class="main-content">
        <header class="dashboard-header">
            <div class="header-content">
                <h1>Gallery: <?php echo htmlspecialchars($package['name']); ?></h1>
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

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Upload New Images</h5>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data" id="uploadForm">
                    <div class="mb-3">
                        <label class="form-label">Select Images</label>
                        <input type="file" name="images[]" class="form-control" accept="image/*" multiple required 
                               onchange="previewImages(this)">
                    </div>
                    <div class="upload-preview" id="imagePreview"></div>
                    <button type="submit" name="upload" class="btn btn-primary mt-3">
                        <i class="fas fa-upload"></i> Upload Images
                    </button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Gallery Images</h5>
            </div>
            <div class="gallery-grid">
                <?php while($image = $gallery->fetch_assoc()): ?>
                    <div class="gallery-item">
                        <img src="../uploads/packages/gallery/<?php echo $image['image']; ?>" 
                             alt="<?php echo htmlspecialchars($image['caption']); ?>">
                        <div class="gallery-actions">
                            <button type="button" class="btn btn-sm btn-primary" 
                                    onclick="editCaption(<?php echo $image['id']; ?>, '<?php echo htmlspecialchars($image['caption']); ?>')">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form method="POST" class="d-inline" onsubmit="return confirm('Delete this image?');">
                                <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
                                <button type="submit" name="delete" class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                        <div class="p-2">
                            <small class="text-muted"><?php echo htmlspecialchars($image['caption']); ?></small>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </main>
</div>

<!-- Caption Edit Modal -->
<div class="modal fade" id="captionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Caption</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="image_id" id="editImageId">
                    <div class="mb-3">
                        <label class="form-label">Caption</label>
                        <input type="text" name="caption" id="editCaption" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="update_caption" class="btn btn-primary">Update Caption</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function previewImages(input) {
    const preview = document.getElementById('imagePreview');
    preview.innerHTML = '';
    
    if (input.files) {
        [...input.files].forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.className = 'preview-item';
                div.innerHTML = `
                    <img src="${e.target.result}">
                    <input type="text" name="captions[]" class="form-control mt-2" placeholder="Caption">
                `;
                preview.appendChild(div);
            }
            reader.readAsDataURL(file);
        });
    }
}

function editCaption(imageId, caption) {
    document.getElementById('editImageId').value = imageId;
    document.getElementById('editCaption').value = caption;
    new bootstrap.Modal(document.getElementById('captionModal')).show();
}
</script>
</body>
</html>