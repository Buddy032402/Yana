<?php
session_start();
if (!isset($_SESSION["admin"])) {
    header("Location: login.php");
    exit;
}

include "../db.php";

// Add new columns to the destinations table
$sql = "ALTER TABLE destinations 
        ADD COLUMN IF NOT EXISTS best_time_to_visit TEXT DEFAULT NULL,
        ADD COLUMN IF NOT EXISTS travel_requirements TEXT DEFAULT NULL,
        ADD COLUMN IF NOT EXISTS transportation_details TEXT DEFAULT NULL,
        ADD COLUMN IF NOT EXISTS gallery_images TEXT DEFAULT NULL";
$conn->query($sql);

$message = "";
// Store form data to prevent loss on validation errors
$formData = [
    'name' => '',
    'country' => '',
    'description' => '',
    'best_time' => '',
    'travel_requirements' => '',
    'transportation' => '',
    'status' => 1
];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Save form data
    $formData = [
        'name' => trim($_POST['name']),
        'country' => trim($_POST['country']),
        'description' => trim($_POST['description']),
        'best_time' => trim($_POST['best_time'] ?? ''),
        'travel_requirements' => trim($_POST['travel_requirements'] ?? ''),
        'transportation' => trim($_POST['transportation'] ?? ''),
        'status' => $_POST['status'] ?? 1
    ];
    
    $name = $formData['name'];
    $country = $formData['country'];
    $description = $formData['description'];
    $best_time = $formData['best_time'];
    $travel_requirements = $formData['travel_requirements'];
    $transportation = $formData['transportation'];
    $featured = isset($_POST['featured']) ? 1 : 0;
    $status = $formData['status'];
    $latitude = $_POST['latitude'] ?? null;
    $longitude = $_POST['longitude'] ?? null;

    // Create required directories if they don't exist
    $upload_dir = "../uploads";
    $destinations_dir = "../uploads/destinations";
    $gallery_dir = "../uploads/destinations/gallery";

    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    if (!file_exists($destinations_dir)) {
        mkdir($destinations_dir, 0777, true);
    }
    if (!file_exists($gallery_dir)) {
        mkdir($gallery_dir, 0777, true);
    }

    // Handle main image upload
    $uploadOk = 1;
    $new_filename = '';
    $imageError = '';
    
    if(isset($_FILES["image"]) && $_FILES["image"]["tmp_name"] != "") {
        $target_dir = "../uploads/destinations/";
        $imageFileType = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
        $new_filename = uniqid() . '.' . $imageFileType;
        $target_file = $target_dir . $new_filename;

        // Check if image file is valid
        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if($check === false) {
            $imageError = "❌ File is not an image.";
            $uploadOk = 0;
        }
        
        // Allow certain file formats
        if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
            $imageError = "❌ Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            $uploadOk = 0;
        }
    } else {
        $imageError = "❌ Please select an image.";
        $uploadOk = 0;
    }

    // Handle gallery images
    $gallery_images = array();
    $galleryError = '';
    
    if(isset($_FILES['gallery_images']) && !empty($_FILES['gallery_images']['name'][0])) {
        foreach($_FILES['gallery_images']['tmp_name'] as $key => $tmp_name) {
            if($_FILES['gallery_images']['error'][$key] === 0 && !empty($tmp_name)) {
                $gallery_ext = strtolower(pathinfo($_FILES['gallery_images']['name'][$key], PATHINFO_EXTENSION));
                
                // Validate gallery image format
                if($gallery_ext != "jpg" && $gallery_ext != "png" && $gallery_ext != "jpeg" && $gallery_ext != "gif") {
                    $galleryError = "❌ Gallery image #" . ($key+1) . " is not in an allowed format (JPG, JPEG, PNG, GIF).";
                    $uploadOk = 0;
                    break;
                }
                
                // Generate a simple filename without the original filename to avoid issues
                $gallery_filename = uniqid() . '.' . $gallery_ext;
                $gallery_images[] = $gallery_filename;
            }
        }
    }
    $gallery_images_json = json_encode($gallery_images);

    // Set appropriate error message
    if (!empty($imageError)) {
        $message = $imageError;
    } elseif (!empty($galleryError)) {
        $message = $galleryError;
    }

    if ($uploadOk == 1) {
        // Upload main image
        if(move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            // Upload gallery images
            // Upload gallery images - FIX HERE
            if(!empty($gallery_images)) {
                foreach($_FILES['gallery_images']['tmp_name'] as $key => $tmp_name) {
                    if($_FILES['gallery_images']['error'][$key] === 0 && !empty($tmp_name)) {
                        $gallery_ext = strtolower(pathinfo($_FILES['gallery_images']['name'][$key], PATHINFO_EXTENSION));
                        // Use the same filename generation as above to ensure consistency
                        $gallery_filename = uniqid() . '.' . $gallery_ext;
                        
                        // Make sure we're using the correct index in the gallery_images array
                        if (isset($gallery_images[$key])) {
                            $gallery_target_file = $gallery_dir . "/" . $gallery_images[$key];
                            if (!move_uploaded_file($tmp_name, $gallery_target_file)) {
                                // Log error if upload fails
                                error_log("Failed to upload gallery image: " . $gallery_target_file);
                            }
                        }
                    }
                }
            }
            
            // Insert into database
            $stmt = $conn->prepare("INSERT INTO destinations (name, country, description, image, gallery_images, 
                best_time_to_visit, travel_requirements, transportation_details, featured, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $stmt->bind_param("ssssssssii", $name, $country, $description, $new_filename, 
                $gallery_images_json, $best_time, $travel_requirements, $transportation, $featured, $status);
            
            if ($stmt->execute()) {
                $_SESSION['message'] = "✅ Destination added successfully";
                header("Location: manage_destinations.php");
                exit();
            } else {
                $message = "❌ Error: " . $stmt->error;
            }
        } else {
            $message = "❌ Error uploading main image.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en" data-theme="<?php echo $_SESSION['theme'] ?? 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Destination - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
    <style>
        .toolbar {
            background: #f8f9fa;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .toolbar-btn {
            padding: 5px 10px;
            margin-right: 5px;
            border: 1px solid #ddd;
            background: white;
            border-radius: 3px;
        }
        .toolbar-btn:hover {
            background: #e9ecef;
        }
        .form-section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .section-title {
            border-bottom: 2px solid #0d6efd;
            padding-bottom: 10px;
            margin-bottom: 20px;
            color: #0d6efd;
        }
        .preview-image {
            max-width: 200px;
            max-height: 200px;
            margin-top: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            display: none;
        }
        .gallery-preview {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }
        .gallery-preview img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border: 1px solid #ddd;
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
                    <h1>Add New Destination</h1>
                    <a href="manage_destinations.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Destinations
                    </a>
                </div>
            </header>

            <div class="card">
                <div class="card-header">
                    <h3>Add New Destination</h3>
                </div>
                <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo strpos($message, '✅') !== false ? 'success' : 'danger'; ?> m-3">
                    <?php echo $message; ?>
                </div>
                <?php endif; ?>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Destination Name</label>
                                    <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($formData['name']); ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Country</label>
                                    <input type="text" name="country" class="form-control" value="<?php echo htmlspecialchars($formData['country']); ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" class="form-control" rows="4" required><?php echo htmlspecialchars($formData['description']); ?></textarea>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Best Time to Visit</label>
                                    <textarea name="best_time" class="form-control" rows="3"><?php echo htmlspecialchars($formData['best_time']); ?></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Travel Requirements</label>
                                    <textarea name="travel_requirements" class="form-control" rows="3"><?php echo htmlspecialchars($formData['travel_requirements']); ?></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Transportation Details</label>
                                    <textarea name="transportation" class="form-control" rows="3"><?php echo htmlspecialchars($formData['transportation']); ?></textarea>
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label">Main Image (JPG, JPEG, PNG, GIF only)</label>
                                    <input type="file" name="image" id="mainImage" class="form-control" accept="image/jpeg,image/png,image/gif" required>
                                    <img id="mainImagePreview" class="preview-image" alt="Image preview">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Gallery Images (Multiple, JPG, JPEG, PNG, GIF only)</label>
                                    <input type="file" name="gallery_images[]" id="galleryImages" class="form-control" accept="image/jpeg,image/png,image/gif" multiple>
                                    <div id="galleryPreview" class="gallery-preview"></div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-select">
                                        <option value="1" <?php echo $formData['status'] == 1 ? 'selected' : ''; ?>>Active</option>
                                        <option value="0" <?php echo $formData['status'] == 0 ? 'selected' : ''; ?>>Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">Add Destination</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/admin.js"></script>
    <script>
    // Add this to your existing script section
    // Main image preview
    document.getElementById('mainImage').addEventListener('change', function() {
        const file = this.files[0];
        const preview = document.getElementById('mainImagePreview');
        
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
            reader.readAsDataURL(file);
        } else {
            preview.style.display = 'none';
        }
    });
    
    // Gallery images preview
    document.getElementById('galleryImages').addEventListener('change', function() {
        const files = this.files;
        const preview = document.getElementById('galleryPreview');
        preview.innerHTML = '';
        
        if (files.length > 0) {
            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.alt = 'Gallery preview';
                    preview.appendChild(img);
                }
                
                reader.readAsDataURL(file);
            }
        }
    });
    </script>
</body>
<style>
.gallery-img-container {
    position: relative;
    display: inline-block;
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