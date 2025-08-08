<?php
session_start();
if (!isset($_SESSION["admin"])) {
    header("Location: login.php");
    exit;
}

include "../db.php";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $destination_id = $_POST['destination_id'];
    $featured_order = $_POST['featured_order'];
    $description = $_POST['description'];
    $client_name = $_POST['client_name'];
    $client_rating = $_POST['client_rating'];
    
    // Handle client image upload
    $client_image = "default-user.png"; // Default image
    
    if (isset($_FILES['client_image']) && $_FILES['client_image']['error'] == 0) {
        $upload_dir = "../uploads/clients/";
        
        // Create directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES['client_image']['name'], PATHINFO_EXTENSION);
        $new_filename = uniqid('client_') . '.' . $file_extension;
        $upload_path = $upload_dir . $new_filename;
        
        if (move_uploaded_file($_FILES['client_image']['tmp_name'], $upload_path)) {
            $client_image = $new_filename;
        } else {
            $_SESSION['error'] = "❌ Error uploading client image";
        }
    }
    
    // Insert with package_id
    $stmt = $conn->prepare("INSERT INTO top_picks (destination_id, package_id, featured_order, description, client_name, client_image, client_rating) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iiisssi", $destination_id, $_POST['package_id'], $featured_order, $description, $client_name, $client_image, $client_rating);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "✅ Top pick added successfully";
        header("Location: manage_top_picks.php");
        exit;
    } else {
        $_SESSION['error'] = "❌ Error adding top pick: " . $conn->error;
    }
}

// Fetch available destinations
$destinations = $conn->query("
    SELECT d.id, d.name, d.image 
    FROM destinations d
    WHERE d.status = 1
    ORDER BY d.name
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Top Pick - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
    <style>
        .destination-card, .package-card {
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        .destination-card:hover, .package-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .destination-card.selected, .package-card.selected {
            border-color: #3949ab;
            background-color: rgba(57, 73, 171, 0.05);
        }
        .destination-img, .package-img {
            height: 150px;
            object-fit: cover;
        }
        .step-container {
            border-left: 3px solid #3949ab;
            padding-left: 20px;
            margin-bottom: 30px;
        }
        .step-title {
            background-color: #f8f9fa;
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-weight: 600;
            color: #3949ab;
        }
        .rating-container {
            margin-top: 10px;
        }
        .rating-stars {
            color: #ffc107;
            font-size: 1.2rem;
        }
        .client-info {
            margin-top: 15px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .client-image {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #3949ab;
        }
        .preview-container {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
        }
        .preview-title {
            font-weight: 600;
            color: #3949ab;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include "includes/sidebar.php"; ?>

        <main class="main-content">
            <div class="container py-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1>Add New Top Pick</h1>
                    <a href="manage_top_picks.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Top Picks
                    </a>
                </div>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php 
                            echo $_SESSION['error'];
                            unset($_SESSION['error']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <form action="" method="POST" id="addTopPickForm" enctype="multipart/form-data">
                            <input type="hidden" name="destination_id" id="selected_destination_id">
                            <input type="hidden" name="package_id" id="selected_package_id">
                            
                            <div class="step-container">
                                <h5 class="step-title"><i class="fas fa-map-marker-alt me-2"></i> Step 1: Select a Destination</h5>
                                
                                <?php if ($destinations->num_rows === 0): ?>
                                    <div class="alert alert-info">
                                        All available destinations are already in top picks.
                                    </div>
                                <?php else: ?>
                                    <div class="row row-cols-1 row-cols-md-3 g-4 mb-3">
                                        <?php while($dest = $destinations->fetch_assoc()): ?>
                                            <div class="col">
                                                <div class="card h-100 destination-card" data-id="<?php echo $dest['id']; ?>" data-name="<?php echo htmlspecialchars($dest['name']); ?>">
                                                    <img src="../uploads/destinations/<?php echo $dest['image']; ?>" 
                                                         class="card-img-top destination-img" alt="<?php echo $dest['name']; ?>">
                                                    <div class="card-body">
                                                        <h5 class="card-title"><?php echo htmlspecialchars($dest['name']); ?></h5>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endwhile; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div id="step2" class="step-container" style="display: none;">
                                <h5 class="step-title"><i class="fas fa-suitcase me-2"></i> Step 2: Select a Package</h5>
                                <div id="packages-container" class="row row-cols-1 row-cols-md-3 g-4 mb-3">
                                    <!-- Packages will be loaded here via AJAX -->
                                </div>
                            </div>
                            
                            <div id="step3" class="step-container" style="display: none;">
                                <h5 class="step-title"><i class="fas fa-cog me-2"></i> Step 3: Client Information and Display Settings</h5>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="client_name" class="form-label">Client Name</label>
                                            <input type="text" class="form-control" id="client_name" name="client_name" required>
                                            <div class="form-text">Name of the client who recommends this destination</div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="client_image" class="form-label">Client Image</label>
                                            <input type="file" class="form-control" id="client_image" name="client_image" accept="image/*">
                                            <div class="form-text">Profile picture of the client (optional)</div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Client Rating</label>
                                            <div class="rating-container">
                                                <div class="rating-stars">
                                                    <i class="far fa-star" data-rating="1"></i>
                                                    <i class="far fa-star" data-rating="2"></i>
                                                    <i class="far fa-star" data-rating="3"></i>
                                                    <i class="far fa-star" data-rating="4"></i>
                                                    <i class="far fa-star" data-rating="5"></i>
                                                </div>
                                                <input type="hidden" name="client_rating" id="client_rating" value="5">
                                                <div class="form-text">How much did the client enjoy this destination?</div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="featured_order" class="form-label">Display Order</label>
                                            <input type="number" class="form-control" id="featured_order" name="featured_order" 
                                                   value="1" required>
                                            <div class="form-text">Lower numbers will appear first</div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="description" class="form-label">Client Testimonial</label>
                                            <textarea class="form-control" id="description" name="description" 
                                                      rows="4" required></textarea>
                                            <div class="form-text">What did the client say about this destination?</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="preview-container">
                                    <h6 class="preview-title"><i class="fas fa-eye me-2"></i> Preview</h6>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <img id="preview_destination_image" class="img-fluid rounded mb-3" alt="Destination Image">
                                        </div>
                                        <div class="col-md-8">
                                            <h5 id="preview_destination_name" class="mb-2"></h5>
                                            <div class="client-info d-flex align-items-center mb-3">
                                                <div class="me-3">
                                                    <img id="preview_client_image" src="../uploads/clients/default-user.png" class="client-image" alt="Client">
                                                </div>
                                                <div>
                                                    <div id="preview_client_name" class="fw-bold">Client Name</div>
                                                    <div id="preview_client_rating" class="rating-stars">
                                                        <i class="fas fa-star"></i>
                                                        <i class="fas fa-star"></i>
                                                        <i class="fas fa-star"></i>
                                                        <i class="fas fa-star"></i>
                                                        <i class="fas fa-star"></i>
                                                    </div>
                                                </div>
                                            </div>
                                            <p id="preview_description" class="fst-italic">"Your testimonial will appear here..."</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-end mt-4">
                                    <a href="manage_top_picks.php" class="btn btn-outline-secondary me-2">Cancel</a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> Add Top Pick
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const destinationCards = document.querySelectorAll('.destination-card');
            const step2 = document.getElementById('step2');
            const step3 = document.getElementById('step3');
            const selectedDestinationId = document.getElementById('selected_destination_id');
            const selectedPackageId = document.getElementById('selected_package_id');
            const packagesContainer = document.getElementById('packages-container');
            
            // Client information preview elements
            const previewDestImage = document.getElementById('preview_destination_image');
            const previewDestName = document.getElementById('preview_destination_name');
            const previewClientImage = document.getElementById('preview_client_image');
            const previewClientName = document.getElementById('preview_client_name');
            const previewClientRating = document.getElementById('preview_client_rating');
            const previewDescription = document.getElementById('preview_description');
            
            // Rating stars functionality
            const ratingStars = document.querySelectorAll('.rating-stars .fa-star');
            const ratingInput = document.getElementById('client_rating');
            
            ratingStars.forEach(star => {
                star.addEventListener('click', function() {
                    const rating = this.dataset.rating;
                    ratingInput.value = rating;
                    
                    // Update stars display
                    ratingStars.forEach(s => {
                        const starRating = s.dataset.rating;
                        if (starRating <= rating) {
                            s.classList.remove('far');
                            s.classList.add('fas');
                        } else {
                            s.classList.remove('fas');
                            s.classList.add('far');
                        }
                    });
                    
                    // Update preview
                    updateRatingPreview(rating);
                });
                
                // Hover effect
                star.addEventListener('mouseenter', function() {
                    const rating = this.dataset.rating;
                    
                    ratingStars.forEach(s => {
                        const starRating = s.dataset.rating;
                        if (starRating <= rating) {
                            s.classList.remove('far');
                            s.classList.add('fas');
                        } else {
                            s.classList.remove('fas');
                            s.classList.add('far');
                        }
                    });
                });
                
                star.addEventListener('mouseleave', function() {
                    const currentRating = ratingInput.value;
                    
                    ratingStars.forEach(s => {
                        const starRating = s.dataset.rating;
                        if (starRating <= currentRating) {
                            s.classList.remove('far');
                            s.classList.add('fas');
                        } else {
                            s.classList.remove('fas');
                            s.classList.add('far');
                        }
                    });
                });
            });
            
            // Client image preview
            const clientImageInput = document.getElementById('client_image');
            clientImageInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        previewClientImage.src = e.target.result;
                    }
                    
                    reader.readAsDataURL(this.files[0]);
                }
            });
            
            // Client name preview
            const clientNameInput = document.getElementById('client_name');
            clientNameInput.addEventListener('input', function() {
                previewClientName.textContent = this.value || 'Client Name';
            });
            
            // Description preview
            const descriptionInput = document.getElementById('description');
            descriptionInput.addEventListener('input', function() {
                previewDescription.textContent = '"' + (this.value || 'Your testimonial will appear here...') + '"';
            });
            
            function updateRatingPreview(rating) {
                // Clear all stars
                previewClientRating.innerHTML = '';
                
                // Add filled stars based on rating
                for (let i = 1; i <= 5; i++) {
                    const starIcon = document.createElement('i');
                    if (i <= rating) {
                        starIcon.className = 'fas fa-star';
                    } else {
                        starIcon.className = 'far fa-star';
                    }
                    previewClientRating.appendChild(starIcon);
                }
            }
            
            destinationCards.forEach(card => {
                card.addEventListener('click', function() {
                    // Remove selected class from all cards
                    destinationCards.forEach(c => c.classList.remove('selected'));
                    
                    // Add selected class to clicked card
                    this.classList.add('selected');
                    
                    // Set the selected destination ID
                    const destId = this.dataset.id;
                    const destName = this.dataset.name;
                    selectedDestinationId.value = destId;
                    
                    // Update preview
                    previewDestName.textContent = destName;
                    previewDestImage.src = this.querySelector('img').src;
                    
                    // Hide step 3 if it was visible
                    step3.style.display = 'none';
                    
                    // Load packages for this destination
                    loadPackages(destId, destName);
                    
                    // Show step 2
                    step2.style.display = 'block';
                    
                    // Scroll to step 2
                    step2.scrollIntoView({ behavior: 'smooth' });
                });
            });
            
            // Define loadPackages function inside the DOMContentLoaded scope
            function loadPackages(destinationId, destinationName) {
                // Clear previous packages
                packagesContainer.innerHTML = '<div class="col-12"><div class="d-flex justify-content-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div></div>';
                
                // Hide step 3 when loading new packages
                step3.style.display = 'none';
                
                // Fetch packages for the selected destination
                fetch(`get_packages.php?destination_id=${destinationId}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        packagesContainer.innerHTML = '';
                        
                        if (data.length === 0) {
                            packagesContainer.innerHTML = `
                                <div class="col-12">
                                    <div class="alert alert-warning">
                                        Please select a package to continue. This destination requires a package selection.
                                    </div>
                                </div>
                            `;
                        } else {
                            data.forEach(pkg => {
                                if (!pkg.in_top_picks) {
                                    const packageCard = document.createElement('div');
                                    packageCard.className = 'col';
                                    packageCard.innerHTML = `
                                        <div class="card h-100 package-card" data-id="${pkg.id}">
                                            <img src="../uploads/packages/${pkg.image}" 
                                                 class="card-img-top package-img" alt="${pkg.name}">
                                            <div class="card-body">
                                                <h5 class="card-title">${pkg.name}</h5>
                                                <p class="card-text text-muted">
                                                    <i class="fas fa-tag"></i> ${pkg.price_formatted}
                                                </p>
                                            </div>
                                        </div>
                                    `;
                                    packagesContainer.appendChild(packageCard);
                                }
                            });

                            // Add event listeners to package cards
                            document.querySelectorAll('.package-card').forEach(card => {
                                card.addEventListener('click', function() {
                                    document.querySelectorAll('.package-card').forEach(c => c.classList.remove('selected'));
                                    this.classList.add('selected');
                                    selectedPackageId.value = this.dataset.id;
                                    showStep3();
                                });
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error loading packages:', error);
                        packagesContainer.innerHTML = `
                            <div class="col-12">
                                <div class="alert alert-danger">
                                    Error loading packages. Please try again.
                                </div>
                                <button type="button" class="btn btn-primary" id="skipPackageBtn">
                                    Continue without selecting a package
                                </button>
                            </div>
                        `;
                        
                        document.getElementById('skipPackageBtn').addEventListener('click', function() {
                            selectedPackageId.value = '';
                            showStep3();
                        });
                    });
            }
            
            // Define showStep3 function inside the DOMContentLoaded scope
            function showStep3() {
                step3.style.display = 'block';
                step3.scrollIntoView({ behavior: 'smooth' });
            }
            
            // Form validation
            document.getElementById('addTopPickForm').addEventListener('submit', function(e) {
                if (!selectedDestinationId.value) {
                    e.preventDefault();
                    alert('Please select a destination first');
                    return;
                }
                
                if (!selectedPackageId.value) {
                    e.preventDefault();
                    alert('Please select a package first');
                    return;
                }
            });
        }); // Closing bracket for DOMContentLoaded
    </script>
</body>
</html>