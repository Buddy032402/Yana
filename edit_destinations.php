<?php
session_start();
if (!isset($_SESSION["admin"])) {
    header("Location: login.php");
    exit;
}

include "../db.php";

$message = "";
$destination_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch destination details
$stmt = $conn->prepare("SELECT id, name, country, description, image, gallery_images,
    best_time_to_visit, travel_requirements, transportation_details,
    featured, status, created_at, updated_at,
    COALESCE(best_time_to_visit, '') as best_time,
    COALESCE(travel_requirements, '') as travel_requirements,
    COALESCE(transportation_details, '') as transportation_details
    FROM destinations WHERE id = ?");
$stmt->bind_param("i", $destination_id);
$stmt->execute();
$destination = $stmt->get_result()->fetch_assoc();

if (!$destination) {
    $_SESSION['error'] = "Destination not found";
    header("Location: manage_destinations.php");
    exit;
}

// Handle form submission
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $name = trim($_POST['name']);
        $country = trim($_POST['country']);
        $description = trim($_POST['description']);
        $best_time = trim($_POST['best_time']);
        $travel_requirements = trim($_POST['travel_requirements']);
        $transportation = trim($_POST['transportation']);
        // This line is commented out but still needed for the status variable below
        // $featured = isset($_POST['featured']) ? 1 : 0;
        $status = isset($_POST['status']) ? $_POST['status'] : 1;
        
        // Process main image
        $image = $destination['image']; // Keep existing image by default
        if (isset($_FILES['image']) && $_FILES['image']['size'] > 0) {
            $target_dir = "../uploads/destinations/";
            $file_extension = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
            $new_filename = uniqid() . '.' . $file_extension;
            $target_file = $target_dir . $new_filename;
            
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $image = $new_filename;
            }
        }
        
        // Process gallery images
        $gallery_images = !empty($destination['gallery_images']) ? json_decode($destination['gallery_images'], true) : [];
        if (!is_array($gallery_images)) $gallery_images = [];
        
        if (isset($_FILES['gallery_images']) && !empty($_FILES['gallery_images']['name'][0])) {
            $gallery_dir = "../uploads/destinations/gallery/";
            
            foreach ($_FILES['gallery_images']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['gallery_images']['size'][$key] > 0) {
                    $file_extension = strtolower(pathinfo($_FILES['gallery_images']['name'][$key], PATHINFO_EXTENSION));
                    $new_filename = uniqid() . '.' . $file_extension;
                    $target_file = $gallery_dir . $new_filename;
                    
                    if (move_uploaded_file($tmp_name, $target_file)) {
                        $gallery_images[] = $new_filename;
                    }
                }
            }
        }
        
        $gallery_images_json = json_encode($gallery_images);
        
        // Update the SQL query and bind_param to match the number of parameters
        $stmt = $conn->prepare("UPDATE destinations SET 
            name = ?, 
            country = ?, 
            description = ?, 
            best_time_to_visit = ?,
            travel_requirements = ?,
            transportation_details = ?,
            image = ?,
            gallery_images = ?,
            status = ?
            WHERE id = ?");
        
        // Update the bind_param to include the destination_id parameter (10 parameters total)
        $stmt->bind_param("ssssssssii", 
            $name, 
            $country, 
            $description, 
            $best_time,
            $travel_requirements,
            $transportation,
            $image,
            $gallery_images_json,
            $status,
            $destination_id
        );

        if ($stmt->execute()) {
            $_SESSION['message'] = "✅ Destination updated successfully";
            header("Location: manage_destinations.php");
            exit;
        } else {
            $message = "❌ Error updating destination: " . $stmt->error;
        }
    }
?>

<!DOCTYPE html>
<html lang="en" data-theme="<?php echo $_SESSION['theme'] ?? 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Destination - Admin Dashboard</title>
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
                    <h1>Edit Destination</h1>
                    <a href="manage_destinations.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Destinations
                    </a>
                </div>
            </header>

            <?php if ($message): ?>
                <div class="alert <?php echo strpos($message, '✅') !== false ? 'alert-success' : 'alert-danger'; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h3>Edit Destination</h3>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="destination_id" value="<?php echo $destination_id; ?>">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Destination Name</label>
                                    <input type="text" name="name" class="form-control" 
                                        value="<?php echo htmlspecialchars(isset($_POST['name']) ? $_POST['name'] : $destination['name']); ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Country</label>
                                    <input type="text" name="country" class="form-control" 
                                        value="<?php echo htmlspecialchars(isset($_POST['country']) ? $_POST['country'] : $destination['country']); ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" class="form-control" rows="4" required><?php echo htmlspecialchars(isset($_POST['description']) ? $_POST['description'] : $destination['description']); ?></textarea>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Best Time to Visit</label>
                                    <textarea name="best_time" class="form-control" rows="3"><?php echo htmlspecialchars(isset($_POST['best_time']) ? $_POST['best_time'] : $destination['best_time']); ?></textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Travel Requirements</label>
                                    <textarea name="travel_requirements" class="form-control" rows="3"><?php echo htmlspecialchars(isset($_POST['travel_requirements']) ? $_POST['travel_requirements'] : $destination['travel_requirements']); ?></textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Transportation Details</label>
                                    <textarea name="transportation" class="form-control" rows="3"><?php echo htmlspecialchars(isset($_POST['transportation']) ? $_POST['transportation'] : $destination['transportation_details']); ?></textarea>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label">Current Main Image</label>
                                    <div class="mb-2">
                                        <?php if (!empty($destination['image']) && file_exists("../uploads/destinations/" . $destination['image'])): ?>
                                            <img src="../uploads/destinations/<?php echo $destination['image']; ?>" alt="Current Image" class="img-thumbnail" style="max-width: 200px;">
                                        <?php else: ?>
                                            <div class="alert alert-warning">No image available or image file missing</div>
                                        <?php endif; ?>
                                    </div>
                                    <input type="file" name="image" class="form-control" accept="image/*">
                                    <small class="text-muted">Leave empty to keep current image</small>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Gallery Images</label>
                                    <div class="current-gallery mb-2 row">
                                        <?php
                                        if (!empty($destination['gallery_images'])) {
                                            $gallery = json_decode($destination['gallery_images'], true);
                                            if (is_array($gallery) && count($gallery) > 0) {
                                                foreach ($gallery as $index => $img) {
                                                    $gallery_path = "../uploads/destinations/gallery/{$img}";
                                                    if (file_exists($gallery_path)) {
                                                        echo "<div class='gallery-img-container col-md-2 mb-2'>";
                                                        echo "<img src='{$gallery_path}' class='img-thumbnail' style='width: 100%; height: 150px; object-fit: cover;'>";
                                                        echo "<button type='button' class='btn btn-danger btn-sm delete-gallery-img' data-image='" . htmlspecialchars($img) . "'>";
                                                        echo "<i class='fas fa-trash'></i></button>";
                                                        echo "</div>";
                                                    }
                                                }
                                            } else {
                                                echo "<div class='col-12'><p class='text-muted'>No gallery images available</p></div>";
                                            }
                                        } else {
                                            echo "<div class='col-12'><p class='text-muted'>No gallery images available</p></div>";
                                        }
                                        ?>
                                    </div>
                                    <input type="file" name="gallery_images[]" class="form-control" accept="image/*" multiple>
                                    <small class="text-muted">Select multiple files to add to gallery. Existing images will be preserved.</small>
                                </div>

  

                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-select">
                                        <option value="1" <?php echo $destination['status'] == 1 ? 'selected' : ''; ?>>Active</option>
                                        <option value="0" <?php echo $destination['status'] == 0 ? 'selected' : ''; ?>>Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">Update Destination</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/admin.js"></script>
    
    <!-- Add this new script section -->
    <script>
    document.querySelectorAll('.delete-gallery-img').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            if(confirm('Are you sure you want to delete this image?')) {
                const imageFile = this.getAttribute('data-image');
                const container = this.closest('.gallery-img-container');
                
                fetch('delete_gallery_image.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `destination_id=<?php echo $destination_id; ?>&image=${imageFile}`
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        container.remove();
                        if(data.remaining_images === 0) {
                            document.querySelector('.current-gallery').innerHTML = 
                                '<div class="col-12"><p class="text-muted">No gallery images available</p></div>';
                        }
                    } else {
                        alert('Error deleting image: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error deleting image');
                });
            }
        });
    });
    </script>
    <!-- Add this inside your form -->
    <div class="mb-3">
        <label class="form-label">Destination Type</label>
        <select name="destination_type" class="form-select" required>
            <option value="local" <?php echo $destination['destination_type'] === 'local' ? 'selected' : ''; ?>>Local (Philippines)</option>
            <option value="international" <?php echo $destination['destination_type'] === 'international' ? 'selected' : ''; ?>>International</option>
        </select>
    </div>
</body>
<style>
.gallery-img-container {
    position: relative;
    display: inline-block;
}

.delete-gallery-img {
    position: absolute;
    top: 5px;
    right: 20px;
    padding: 4px 8px;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.gallery-img-container:hover .delete-gallery-img {
    opacity: 1;
}

.gallery-img-container img {
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
}

.gallery-img-container img:hover {
    transform: scale(1.05);
}

.img-thumbnail {
    border: 2px solid #dee2e6;
    padding: 0.25rem;
    background-color: #fff;
    border-radius: 0.25rem;
    max-width: 100%;
    height: auto;
}
</style>
</html>