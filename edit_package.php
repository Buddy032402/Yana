<?php
session_start();
if (!isset($_SESSION["admin"])) {
    header("Location: login.php");
    exit;
}

include "../db.php";

$package_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch package details
$stmt = $conn->prepare("SELECT * FROM packages WHERE id = ?");
$stmt->bind_param("i", $package_id);
$stmt->execute();
$package = $stmt->get_result()->fetch_assoc();

// Remove or modify these lines (around line 37)
if (!$package) {
    header("Location: manage_packages.php");
    exit;
}

// Remove the category-related code
$package['gallery_images'] = $package['gallery_images'] ?? '[]';

// Fetch active destinations for the dropdown
$destinations = $conn->query("SELECT id, name FROM destinations WHERE status = 1 ORDER BY name");

// Update the POST handling section (around line 74)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Preserve all existing values from the package
    $name = $_POST['name'] ?? $package['name'];
    $destination_id = $_POST['destination_id'] ?? $package['destination_id'];
    $description = $_POST['description'] ?? $package['description'];
    $duration = $_POST['duration'] ?? $package['duration'];
    $price = $_POST['price'] ?? $package['price'];
    $max_persons = isset($_POST['max_persons']) ? (int)$_POST['max_persons'] : $package['max_persons'];
    $available_slots = isset($_POST['available_slots']) ? (int)$_POST['available_slots'] : $package['available_slots'];
    $group_pricing = $_POST['group_pricing'] ?? $package['group_pricing'];
    $status = isset($_POST['status']) ? 1 : 0;
    $includes = $_POST['includes'] ?? $package['includes'];
    $excludes = $_POST['excludes'] ?? $package['excludes'];
    $itinerary = $_POST['itinerary'] ?? $package['itinerary'];
    $activities = $_POST['activities'] ?? $package['activities'];
    $booking_requirements = $_POST['booking_requirements'] ?? $package['booking_requirements'];
    $payment_options = $_POST['payment_options'] ?? $package['payment_options'];
   
    // Create gallery directory if it doesn't exist
    $target_dir = "../uploads/packages/";
    $gallery_dir = $target_dir . "gallery/";
    if (!file_exists($gallery_dir)) {
        mkdir($gallery_dir, 0777, true);
    }
    
    // Handle gallery images
    $gallery_images = [];
    if (!empty($package['gallery_images'])) {
        $decoded = json_decode($package['gallery_images'], true);
        if (is_array($decoded)) {
            $gallery_images = $decoded;
        }
    }
    
    if (isset($_FILES['gallery_images']) && !empty($_FILES['gallery_images']['name'][0])) {
        foreach ($_FILES['gallery_images']['tmp_name'] as $key => $tmp_name) {
            if (!empty($tmp_name)) {
                $file_extension = strtolower(pathinfo($_FILES['gallery_images']['name'][$key], PATHINFO_EXTENSION));
                $new_gallery_filename = uniqid() . '.' . $file_extension;
                $target_gallery_file = $gallery_dir . $new_gallery_filename;
                
                if (move_uploaded_file($tmp_name, $target_gallery_file)) {
                    $gallery_images[] = $new_gallery_filename;
                }
            }
        }
    }
    
    $gallery_images_json = json_encode($gallery_images);
    
    if ($_FILES['image']['name']) {
        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $new_filename = uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        // Handle new image upload
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            // Delete old image and update with new image
            if ($package['image'] && file_exists($target_dir . $package['image'])) {
                unlink($target_dir . $package['image']);
            }
            
            $stmt = $conn->prepare("UPDATE packages SET 
                name = ?, destination_id = ?, description = ?, duration = ?, 
                price = ?, image = ?, gallery_images = ?, max_persons = ?, 
                available_slots = ?, group_pricing = ?, includes = ?, 
                excludes = ?, itinerary = ?, activities = ?, booking_requirements = ?, 
                payment_options = ?, status = ? 
                WHERE id = ?");
            
            $stmt->bind_param("sisdssssissssssssi", 
                $name, $destination_id, $description, $duration, 
                $price, $new_filename, $gallery_images_json, $max_persons, 
                $available_slots, $group_pricing, $includes, 
                $excludes, $itinerary, $activities, $booking_requirements, 
                $payment_options, $status, $package_id);
        }
    }
    else {
        // Update without changing the image
        $stmt = $conn->prepare("UPDATE packages SET 
            name = ?, destination_id = ?, description = ?, duration = ?, 
            price = ?, gallery_images = ?, max_persons = ?, available_slots = ?, 
            group_pricing = ?, includes = ?, excludes = ?, 
            itinerary = ?, activities = ?, booking_requirements = ?, 
            payment_options = ?, status = ? 
            WHERE id = ?");
        
        $stmt->bind_param("sisdsssissssssssi", 
            $name, $destination_id, $description, $duration, 
            $price, $gallery_images_json, $max_persons, $available_slots, 
            $group_pricing, $includes, $excludes, 
            $itinerary, $activities, $booking_requirements, 
            $payment_options, $status, $package_id);
    }

    if ($stmt->execute()) {
        $_SESSION['message'] = "✅ Package updated successfully";
        header("Location: manage_packages.php");
        exit;
    } else {
        $message = "❌ Error updating package: " . $conn->error;
    }
}
?>

<!DOCTYPE html> 
<html lang="en" data-theme="<?php echo $_SESSION['theme'] ?? 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Package - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>

<div class="admin-container">
    <?php include "includes/sidebar.php"; ?>

    <main class="main-content">
        <header class="dashboard-header">
            <div class="header-content">
                <h1>Edit Package</h1>
                <a href="manage_packages.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Packages
                </a>
            </div>
        </header>

        <?php if (isset($message)): ?>
            <div class="alert <?php echo strpos($message, '✅') !== false ? 'alert-success' : 'alert-danger'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label class="form-label">Package Name</label>
                                <input type="text" name="name" class="form-control" 
                                       value="<?php echo htmlspecialchars($package['name']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Destination</label>
                                <select name="destination_id" class="form-select" required>
                                    <option value="">Select Destination</option>
                                    <?php while($destination = $destinations->fetch_assoc()): ?>
                                        <option value="<?php echo $destination['id']; ?>" 
                                                <?php echo $package['destination_id'] == $destination['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($destination['name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <div id="description-editor" style="height: 200px;"><?php echo $package['description']; ?></div>
                                <input type="hidden" name="description" id="description-input">
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Duration (days)</label>
                                        <input type="number" name="duration" class="form-control" 
                                               value="<?php echo $package['duration']; ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Price</label>
                                        <input type="number" name="price" class="form-control" step="0.01" 
                                               value="<?php echo $package['price']; ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Available Slots</label>
                                        <input type="number" name="available_slots" class="form-control" 
                                               value="<?php echo $package['available_slots']; ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Activities</label>
                                <div id="activities-editor" style="height: 200px;"><?php echo $package['activities']; ?></div>
                                <input type="hidden" name="activities" id="activities-input">
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Includes</label>
                                        <div id="includes-editor" style="height: 200px;"><?php echo $package['includes']; ?></div>
                                        <input type="hidden" name="includes" id="includes-input">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Excludes</label>
                                        <div id="excludes-editor" style="height: 200px;"><?php echo $package['excludes']; ?></div>
                                        <input type="hidden" name="excludes" id="excludes-input">
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Itinerary</label>
                                <div id="itinerary-editor" style="height: 200px;"><?php echo $package['itinerary']; ?></div>
                                <input type="hidden" name="itinerary" id="itinerary-input">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Main Image</label>
                                <input type="file" name="image" class="form-control" accept="image/*">
                                <?php if($package['image']): ?>
                                    <div class="mt-2 position-relative">
                                        <img src="../uploads/packages/<?php echo $package['image']; ?>" 
                                             class="img-thumbnail" style="max-width: 200px;">
                                        <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0" 
                                                onclick="deleteMainImage(<?php echo $package_id; ?>)">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Gallery Images</label>
                                <input type="file" name="gallery_images[]" class="form-control" accept="image/*" multiple>
                                <?php if(!empty($package['gallery_images'])): ?>
                                    <div class="row mt-2">
                                        <?php foreach(json_decode($package['gallery_images']) as $index => $image): ?>
                                            <div class="col-6 mb-2 position-relative">
                                                <img src="../uploads/packages/gallery/<?php echo $image; ?>" 
                                                     class="img-thumbnail" style="width: 100%;">
                                                <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0" 
                                                        onclick="deleteGalleryImage(<?php echo $package_id; ?>, '<?php echo $image; ?>', <?php echo $index; ?>)">
                                                            <i class="fas fa-times"></i>
                                                    </button>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Group Pricing</label>
                                <textarea name="group_pricing" class="form-control" rows="3"><?php echo htmlspecialchars($package['group_pricing']); ?></textarea>
                            </div>


                            <div class="mb-3">
                                <label class="form-label">Booking Requirements</label>
                                <textarea name="booking_requirements" class="form-control" rows="3"><?php echo htmlspecialchars($package['booking_requirements']); ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Payment Options</label>
                                <textarea name="payment_options" class="form-control" rows="3"><?php echo htmlspecialchars($package['payment_options']); ?></textarea>
                            </div>

                      
                            <div class="mb-3">
                                <div class="form-check">
                                    <input type="checkbox" name="status" class="form-check-input" 
                                           <?php echo $package['status'] ? 'checked' : ''; ?> value="1">
                                    <label class="form-check-label">Active Package</label>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-save"></i> Update Package
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
<script>
// Initialize Quill editors
const editors = {
    description: new Quill('#description-editor', {
        theme: 'snow',
        placeholder: 'Enter package description...'
    }),
    activities: new Quill('#activities-editor', {
        theme: 'snow',
        placeholder: 'Enter activities details...'
    }),
    includes: new Quill('#includes-editor', {
        theme: 'snow',
        placeholder: 'Enter what\'s included...'
    }),
    excludes: new Quill('#excludes-editor', {
        theme: 'snow',
        placeholder: 'Enter what\'s not included...'
    }),
    itinerary: new Quill('#itinerary-editor', {
        theme: 'snow',
        placeholder: 'Enter detailed itinerary...'
    })
};

// Update hidden inputs before form submission
document.querySelector('form').onsubmit = function() {
    document.getElementById('description-input').value = editors.description.root.innerHTML;
    document.getElementById('activities-input').value = editors.activities.root.innerHTML;
    document.getElementById('includes-input').value = editors.includes.root.innerHTML;
    document.getElementById('excludes-input').value = editors.excludes.root.innerHTML;
    document.getElementById('itinerary-input').value = editors.itinerary.root.innerHTML;
    return true;
};
</script>
</body>

// Add this before the closing </body> tag
<script>
function deleteMainImage(packageId) {
    if(confirm('Are you sure you want to delete the main image?')) {
        fetch('delete_package_image.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'package_id=' + packageId + '&type=main'
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                location.reload();
            } else {
                alert('Error deleting image: ' + data.message);
            }
        });
    }
}

function deleteGalleryImage(packageId, imageName, index) {
    if(confirm('Are you sure you want to delete this gallery image?')) {
        fetch('delete_package_image.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'package_id=' + packageId + '&type=gallery&image=' + imageName + '&index=' + index
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                location.reload();
            } else {
                alert('Error deleting image: ' + data.message);
            }
        });
    }
}
</script>
</body>
</html>