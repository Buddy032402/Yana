<?php
session_start();
if (!isset($_SESSION["admin"])) {
    header("Location: login.php");
    exit;
}

include "../db.php";

// Fetch active destinations for the dropdown
$destinations = $conn->query("SELECT id, name FROM destinations WHERE status = 1 ORDER BY name");

$message = "";
// Update the formData array
$formData = [
    'name' => '',
    'destination_id' => '',
    'category' => '',  // Add this line
    'description' => '',
    'duration' => '',
    'price' => '',
    'max_persons' => 10,
    'available_slots' => 10,
    'group_pricing' => '',
    'includes' => '',
    'excludes' => '',
    'itinerary' => '',
    'activities' => '',
    'booking_requirements' => '',
    'payment_options' => '',
    'featured' => 0,
    'status' => 1
];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Save form data
    $formData = [
        'name' => $_POST['name'] ?? '',
        'destination_id' => $_POST['destination_id'] ?? '',
        'category' => ($_POST['category'] === 'others' && !empty($_POST['custom_category'])) 
            ? $_POST['custom_category'] 
            : ($_POST['category'] ?? ''),
        'description' => $_POST['description'] ?? '',
        'duration' => $_POST['duration'] ?? '',
        'price' => $_POST['price'] ?? '',
        'max_persons' => isset($_POST['max_persons']) ? (int)$_POST['max_persons'] : 10,
        'available_slots' => isset($_POST['available_slots']) ? (int)$_POST['available_slots'] : 10,
        'group_pricing' => $_POST['group_pricing'] ?? '',
        'includes' => $_POST['includes'] ?? '',
        'excludes' => $_POST['excludes'] ?? '',
        'itinerary' => $_POST['itinerary'] ?? '',
        'activities' => $_POST['activities'] ?? '',
        'booking_requirements' => $_POST['booking_requirements'] ?? '',
        'payment_options' => $_POST['payment_options'] ?? '',
        'featured' => isset($_POST['featured']) ? 1 : 0
    ];
    
    // Create required directories if they don't exist
    $target_dir = "../uploads/packages/";
    $gallery_dir = $target_dir . "gallery/";
    
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    if (!file_exists($gallery_dir)) {
        mkdir($gallery_dir, 0777, true);
    }
    
    $uploadOk = 1;
    $new_filename = '';
    
    // Handle main image upload
    if(isset($_FILES["image"]) && $_FILES["image"]["tmp_name"] != "") {
        $imageFileType = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
        $new_filename = uniqid() . '.' . $imageFileType;
        $target_file = $target_dir . $new_filename;
        
        // Check if image file is valid
        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if($check === false) {
            $message = "❌ File is not an image.";
            $uploadOk = 0;
        }
        
        // Allow certain file formats
        if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
            $message = "❌ Sorry, only JPG, JPEG, PNG & GIF files are allowed for the main image.";
            $uploadOk = 0;
        }
    } else {
        $message = "❌ Please select a main image.";
        $uploadOk = 0;
    }
    
    // Handle gallery images
    $gallery_images = array();
    if(isset($_FILES['gallery_images']) && !empty($_FILES['gallery_images']['name'][0])) {
        foreach($_FILES['gallery_images']['tmp_name'] as $key => $tmp_name) {
            if($_FILES['gallery_images']['error'][$key] === 0 && !empty($tmp_name)) {
                $gallery_ext = strtolower(pathinfo($_FILES['gallery_images']['name'][$key], PATHINFO_EXTENSION));
                
                // Validate gallery image format
                if($gallery_ext != "jpg" && $gallery_ext != "png" && $gallery_ext != "jpeg" && $gallery_ext != "gif") {
                    $message = "❌ Gallery image #" . ($key+1) . " is not in an allowed format (JPG, JPEG, PNG, GIF).";
                    $uploadOk = 0;
                    break;
                }
                
                $gallery_filename = uniqid() . '.' . $gallery_ext;
                $gallery_images[] = $gallery_filename;
                
                // Move the file immediately after validation
                if(!move_uploaded_file($tmp_name, $gallery_dir . $gallery_filename)) {
                    $message = "❌ Error uploading gallery image #" . ($key+1);
                    $uploadOk = 0;
                    break;
                }
            }
        }
    }
    
    $gallery_images_json = json_encode($gallery_images);
    
    if ($uploadOk == 1) {
        // Upload main image
        if(move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            // Remove the duplicate gallery upload code since we're handling it above
            
            // Insert into database
            $stmt = $conn->prepare("INSERT INTO packages (
                name, destination_id, category, description, 
                duration, price, image, gallery_images, 
                max_persons, available_slots, group_pricing, 
                featured, includes, excludes, itinerary,
                activities, booking_requirements, payment_options) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $stmt->bind_param("sssssdssiissssssss", 
                $formData['name'], 
                $formData['destination_id'],
                $formData['category'],
                $formData['description'], 
                $formData['duration'], 
                $formData['price'], 
                $new_filename, 
                $gallery_images_json, 
                $formData['max_persons'], 
                $formData['available_slots'], 
                $formData['group_pricing'], 
                $formData['featured'], 
                $formData['includes'], 
                $formData['excludes'], 
                $formData['itinerary'],
                $formData['activities'], 
                $formData['booking_requirements'], 
                $formData['payment_options']
            );
            
            if ($stmt->execute()) {
                $_SESSION['message'] = "✅ Package added successfully";
                header("Location: manage_packages.php");
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
    <title>Add New Package - Admin Dashboard</title>
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
                <h1>Add New Package</h1>
                <a href="manage_packages.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Packages
                </a>
            </div>
        </header>

        <?php if (!empty($message)): ?>
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
                                <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($formData['name']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Destination</label>
                                <select name="destination_id" class="form-select" required>
                                    <option value="">Select Destination</option>
                                    <?php while($destination = $destinations->fetch_assoc()): ?>
                                        <option value="<?php echo $destination['id']; ?>" <?php echo $formData['destination_id'] == $destination['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($destination['name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Category</label>
                                <input type="text" name="category" class="form-control" value="<?php echo htmlspecialchars($formData['category']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <div id="description-editor" style="height: 200px;"><?php echo $formData['description']; ?></div>
                                <input type="hidden" name="description" id="description-input">
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Duration (days)</label>
                                        <input type="number" name="duration" class="form-control" value="<?php echo $formData['duration']; ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Price</label>
                                        <input type="number" name="price" class="form-control" step="0.01" value="<?php echo $formData['price']; ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Available Slots</label>
                                        <input type="number" name="available_slots" class="form-control" value="<?php echo $formData['available_slots']; ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Activities</label>
                                <div id="activities-editor" style="height: 200px;"><?php echo $formData['activities']; ?></div>
                                <input type="hidden" name="activities" id="activities-input">
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Includes</label>
                                        <div id="includes-editor" style="height: 200px;"><?php echo $formData['includes']; ?></div>
                                        <input type="hidden" name="includes" id="includes-input">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Excludes</label>
                                        <div id="excludes-editor" style="height: 200px;"><?php echo $formData['excludes']; ?></div>
                                        <input type="hidden" name="excludes" id="excludes-input">
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Itinerary</label>
                                <div id="itinerary-editor" style="height: 200px;"><?php echo $formData['itinerary']; ?></div>
                                <input type="hidden" name="itinerary" id="itinerary-input">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Main Image (JPG, JPEG, PNG, GIF only)</label>
                                <input type="file" name="image" class="form-control" accept="image/jpeg,image/png,image/gif" required id="mainImage">
                                <div class="mt-2">
                                    <img id="mainImagePreview" src="#" alt="Main image preview" style="max-width: 100%; display: none;">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Gallery Images (Multiple, JPG, JPEG, PNG, GIF only)</label>
                                <input type="file" name="gallery_images[]" class="form-control" accept="image/jpeg,image/png,image/gif" multiple id="galleryImages">
                                <div id="galleryPreview" class="mt-2 d-flex flex-wrap gap-2"></div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Group Pricing</label>
                                <textarea name="group_pricing" class="form-control" rows="3"><?php echo htmlspecialchars($formData['group_pricing']); ?></textarea>
                            </div>


                            <div class="mb-3">
                                <label class="form-label">Booking Requirements</label>
                                <textarea name="booking_requirements" class="form-control" rows="3"><?php echo htmlspecialchars($formData['booking_requirements']); ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Payment Options</label>
                                <textarea name="payment_options" class="form-control" rows="3"><?php echo htmlspecialchars($formData['payment_options']); ?></textarea>
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input type="checkbox" name="featured" class="form-check-input" <?php echo $formData['featured'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label">Featured Package</label>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-save"></i> Add Package
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
        placeholder: 'Enter package description...',
        modules: {
            toolbar: [
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                ['link', 'image'],
                ['clean']
            ]
        }
    }),
    activities: new Quill('#activities-editor', {
        theme: 'snow',
        placeholder: 'Enter activities and itinerary details...',
        modules: {
            toolbar: [
                ['bold', 'italic', 'underline'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                ['link'],
                ['clean']
            ]
        }
    }),
    includes: new Quill('#includes-editor', {
        theme: 'snow',
        placeholder: 'Enter what\'s included...',
        modules: {
            toolbar: [
                ['bold', 'italic'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                ['clean']
            ]
        }
    }),
    excludes: new Quill('#excludes-editor', {
        theme: 'snow',
        placeholder: 'Enter what\'s not included...',
        modules: {
            toolbar: [
                ['bold', 'italic'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                ['clean']
            ]
        }
    }),
    itinerary: new Quill('#itinerary-editor', {
        theme: 'snow',
        placeholder: 'Enter detailed itinerary...',
        modules: {
            toolbar: [
                ['bold', 'italic', 'underline'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                [{ 'header': [1, 2, 3, false] }],
                ['link'],
                ['clean']
            ]
        }
    })
};

// Update hidden inputs before form submission
document.querySelector('form').onsubmit = function() {
    // Update hidden inputs with editor content
    document.getElementById('description-input').value = editors.description.root.innerHTML;
    document.getElementById('activities-input').value = editors.activities.root.innerHTML;
    document.getElementById('includes-input').value = editors.includes.root.innerHTML;
    document.getElementById('excludes-input').value = editors.excludes.root.innerHTML;
    document.getElementById('itinerary-input').value = editors.itinerary.root.innerHTML;
    
    // Basic validation
    const requiredFields = ['name', 'destination_id', 'category', 'duration', 'price', 'available_slots'];
    let isValid = true;
    
    requiredFields.forEach(field => {
        const input = this.elements[field];
        if (!input.value.trim()) {
            input.classList.add('is-invalid');
            isValid = false;
        } else {
            input.classList.remove('is-invalid');
        }
    });
    
    // Check if description is empty
    if (editors.description.getText().trim().length === 0) {
        document.querySelector('#description-editor .ql-editor').classList.add('border-danger');
        isValid = false;
    }
    
    // If image validation failed but form data is valid, save form data
    if (!isValid) {
        return false;
    }
    
    return true;
};

// Add custom CSS for validation
const style = document.createElement('style');
style.textContent = `
    .ql-editor.border-danger {
        border: 1px solid #dc3545 !important;
    }
    .is-invalid {
        border-color: #dc3545 !important;
        padding-right: calc(1.5em + 0.75rem) !important;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e") !important;
        background-repeat: no-repeat !important;
        background-position: right calc(0.375em + 0.1875rem) center !important;
        background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem) !important;
    }
`;
document.head.appendChild(style);

// Image preview functionality
document.addEventListener('DOMContentLoaded', function() {
    // Main image preview
    document.getElementById('mainImage').addEventListener('change', function(e) {
        const preview = document.getElementById('mainImagePreview');
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
            reader.readAsDataURL(this.files[0]);
        } else {
            preview.style.display = 'none';
        }
    });

    // Gallery images preview
    document.getElementById('galleryImages').addEventListener('change', function(e) {
        const preview = document.getElementById('galleryPreview');
        preview.innerHTML = ''; // Clear existing previews
        
        if (this.files) {
            Array.from(this.files).forEach(file => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.style.maxWidth = '150px';
                    img.style.height = 'auto';
                    img.className = 'img-thumbnail';
                    preview.appendChild(img);
                }
                reader.readAsDataURL(file);
            });
        }
    });
});
</script>
</body>
</html>