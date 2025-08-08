<?php
session_start();
if (!isset($_SESSION["admin"])) {
    header("Location: login.php");
    exit;
}

include "../db.php";

$message = "";
$tour_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch existing tour data
$stmt = $conn->prepare("SELECT * FROM tours WHERE id = ?");
$stmt->bind_param("i", $tour_id);
$stmt->execute();
$tour = $stmt->get_result()->fetch_assoc();

if (!$tour) {
    header("Location: manage_tours.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST["title"];
    $description = $_POST["description"];
    $price = $_POST["price"];
    $duration = $_POST["duration"];
    $location = $_POST["location"];
    $featured = isset($_POST["featured"]) ? 1 : 0;
    $popular = isset($_POST["popular"]) ? 1 : 0;
    $highlights = isset($_POST["highlights"]) ? json_encode($_POST["highlights"]) : '[]';
    $inclusions = isset($_POST["inclusions"]) ? json_encode($_POST["inclusions"]) : '[]';
    $itinerary = isset($_POST["itinerary"]) ? json_encode($_POST["itinerary"]) : '[]';

    $mainImagePath = $tour['image_main'];
    $galleryImages = json_decode($tour['image_gallery'], true) ?: [];
    $uploadOk = 1;

    // Handle main image update
    if (isset($_FILES["main_image"]) && $_FILES["main_image"]["error"] == 0) {
        $target_dir = "../uploads/tours/";
        $mainImage = $_FILES["main_image"];
        $newMainImagePath = $target_dir . uniqid() . '_' . basename($mainImage["name"]);
        
        if (validateImage($mainImage)) {
            if (move_uploaded_file($mainImage["tmp_name"], $newMainImagePath)) {
                // Delete old main image
                if (file_exists($mainImagePath)) {
                    unlink($mainImagePath);
                }
                $mainImagePath = $newMainImagePath;
            }
        } else {
            $message = "❌ Invalid main image format or size";
            $uploadOk = 0;
        }
    }

    // Handle gallery images update
    if ($uploadOk && isset($_FILES["gallery_images"])) {
        $target_dir = "../uploads/tours/";
        foreach($_FILES["gallery_images"]["tmp_name"] as $key => $tmp_name) {
            if ($_FILES["gallery_images"]["error"][$key] == 0) {
                $file = [
                    "name" => $_FILES["gallery_images"]["name"][$key],
                    "type" => $_FILES["gallery_images"]["type"][$key],
                    "tmp_name" => $tmp_name,
                    "error" => $_FILES["gallery_images"]["error"][$key],
                    "size" => $_FILES["gallery_images"]["size"][$key]
                ];
                
                $imagePath = $target_dir . uniqid() . '_' . basename($file["name"]);
                if (validateImage($file) && move_uploaded_file($file["tmp_name"], $imagePath)) {
                    $galleryImages[] = $imagePath;
                }
            }
        }
    }

    // Remove gallery images if requested
    if (isset($_POST['remove_gallery']) && is_array($_POST['remove_gallery'])) {
        foreach ($_POST['remove_gallery'] as $index) {
            if (isset($galleryImages[$index])) {
                if (file_exists($galleryImages[$index])) {
                    unlink($galleryImages[$index]);
                }
                unset($galleryImages[$index]);
            }
        }
        $galleryImages = array_values($galleryImages); // Reindex array
    }

    if ($uploadOk) {
        $gallery_json = json_encode(array_values($galleryImages));
        
        $stmt = $conn->prepare("UPDATE tours SET title=?, description=?, price=?, duration=?, location=?, featured=?, popular=?, image_main=?, image_gallery=?, highlights=?, inclusions=?, itinerary=? WHERE id=?");
        $stmt->bind_param("ssdssiisssssi", $title, $description, $price, $duration, $location, $featured, $popular, $mainImagePath, $gallery_json, $highlights, $inclusions, $itinerary, $tour_id);

        if ($stmt->execute()) {
            $_SESSION['message'] = "✅ Tour package updated successfully!";
            header("Location: manage_tours.php");
            exit;
        } else {
            $message = "❌ Error: " . $stmt->error;
        }
        $stmt->close();
    }
}

function validateImage($file) {
    $allowed = ["jpg", "jpeg", "png", "gif"];
    $ext = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    
    if (!in_array($ext, $allowed)) return false;
    if ($file["size"] > 5000000) return false;
    if (!getimagesize($file["tmp_name"])) return false;
    
    return true;
}
?>

<!DOCTYPE html>
<html lang="en" data-theme="<?php echo $_SESSION['theme'] ?? 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Tour - Admin Dashboard</title>
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
                <h1>Edit Tour Package</h1>
            </div>
        </header>

        <?php if ($message): ?>
            <div class="alert <?php echo strpos($message, '✅') !== false ? 'alert-success' : 'alert-danger'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Package Title</label>
                            <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($tour['title']); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Location</label>
                            <input type="text" name="location" class="form-control" value="<?php echo htmlspecialchars($tour['location']); ?>" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Price</label>
                            <input type="number" name="price" class="form-control" step="0.01" value="<?php echo $tour['price']; ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Duration</label>
                            <input type="text" name="duration" class="form-control" value="<?php echo htmlspecialchars($tour['duration']); ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="5" required><?php echo htmlspecialchars($tour['description']); ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Highlights</label>
                        <div id="highlightsContainer">
                            <?php foreach(json_decode($tour['highlights'], true) ?: [] as $highlight): ?>
                            <div class="input-group mb-2">
                                <input type="text" name="highlights[]" class="form-control" value="<?php echo htmlspecialchars($highlight); ?>">
                                <button type="button" class="btn btn-danger remove-field"><i class="fas fa-minus"></i></button>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="btn btn-secondary" onclick="addField('highlights')">
                            <i class="fas fa-plus"></i> Add Highlight
                        </button>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Inclusions</label>
                        <div id="inclusionsContainer">
                            <?php foreach(json_decode($tour['inclusions'], true) ?: [] as $inclusion): ?>
                            <div class="input-group mb-2">
                                <input type="text" name="inclusions[]" class="form-control" value="<?php echo htmlspecialchars($inclusion); ?>">
                                <button type="button" class="btn btn-danger remove-field"><i class="fas fa-minus"></i></button>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="btn btn-secondary" onclick="addField('inclusions')">
                            <i class="fas fa-plus"></i> Add Inclusion
                        </button>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Itinerary</label>
                        <div id="itineraryContainer">
                            <?php foreach(json_decode($tour['itinerary'], true) ?: [] as $item): ?>
                            <div class="input-group mb-2">
                                <input type="text" name="itinerary[]" class="form-control" value="<?php echo htmlspecialchars($item); ?>">
                                <button type="button" class="btn btn-danger remove-field"><i class="fas fa-minus"></i></button>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="btn btn-secondary" onclick="addField('itinerary')">
                            <i class="fas fa-plus"></i> Add Itinerary Item
                        </button>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Current Main Image</label>
                        <div class="mb-2">
                            <img src="<?php echo htmlspecialchars($tour['image_main']); ?>" alt="Main Image" style="max-width: 200px;">
                        </div>
                        <input type="file" name="main_image" class="form-control" accept="image/*">
                        <small class="text-muted">Leave empty to keep current image</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Gallery Images</label>
                        <div class="row mb-3">
                            <?php foreach(json_decode($tour['image_gallery'], true) ?: [] as $index => $image): ?>
                            <div class="col-md-3 mb-2">
                                <div class="gallery-image-container">
                                    <img src="<?php echo htmlspecialchars($image); ?>" alt="Gallery Image" class="img-fluid">
                                    <div class="form-check">
                                        <input type="checkbox" name="remove_gallery[]" value="<?php echo $index; ?>" class="form-check-input">
                                        <label class="form-check-label">Remove</label>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <input type="file" name="gallery_images[]" class="form-control" accept="image/*" multiple>
                        <small class="text-muted">Select multiple files to add to gallery</small>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-check">
                                <input type="checkbox" name="featured" class="form-check-input" id="featured" <?php echo $tour['featured'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="featured">Featured Package</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input type="checkbox" name="popular" class="form-check-input" id="popular" <?php echo $tour['popular'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="popular">Popular Package</label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="manage_tours.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Package</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/admin.js"></script>
<script>
function addField(type) {
    const container = document.getElementById(type + 'Container');
    const newField = document.createElement('div');
    newField.className = 'input-group mb-2';
    newField.innerHTML = `
        <input type="text" name="${type}[]" class="form-control" placeholder="Add ${type.slice(0, -1)}">
        <button type="button" class="btn btn-danger remove-field"><i class="fas fa-minus"></i></button>
    `;
    container.appendChild(newField);
}

document.addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-field') || e.target.parentElement.classList.contains('remove-field')) {
        const button = e.target.classList.contains('remove-field') ? e.target : e.target.parentElement;
        button.closest('.input-group').remove();
    }
});
</script>
</body>
</html>