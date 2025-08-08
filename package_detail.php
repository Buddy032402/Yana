
<?php
session_start();
include "db.php";

// Get package ID from URL
$package_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch package details
$stmt = $conn->prepare("SELECT p.*, d.name as destination_name, d.destination_type,
    p.description, p.activities, p.includes, p.excludes, 
    p.itinerary, p.group_pricing,
    p.booking_requirements, p.payment_options, p.gallery_images,
    p.max_persons, p.status
FROM packages p 
LEFT JOIN destinations d ON p.destination_id = d.id 
WHERE p.id = ? AND p.status = 1");
$stmt->bind_param("i", $package_id);
$stmt->execute();
$package = $stmt->get_result()->fetch_assoc();

if (!$package) {
    header("Location: index.php");
    exit;
}

// Fetch pricing tiers - Check if table exists first
$pricing_tiers = [];
$table_check = $conn->query("SHOW TABLES LIKE 'package_pricing_tiers'");
if ($table_check->num_rows > 0) {
    $stmt = $conn->prepare("SELECT * FROM package_pricing_tiers WHERE package_id = ? ORDER BY price ASC");
    $stmt->bind_param("i", $package_id);
    $stmt->execute();
    $pricing_tiers = $stmt->get_result();
}

// Fetch gallery images
$stmt = $conn->prepare("SELECT * FROM package_gallery WHERE package_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $package_id);
$stmt->execute();
$gallery = $stmt->get_result();


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($package['name']); ?> - Yana Biyahe Na Travel and Tours</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/css/lightbox.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/responsive.css">
    <style>
        :root {
            --primary-color: #1e88e5;
            --primary-dark: #1565c0;
            --primary-light: #bbdefb;
            --secondary-color: #ff6f00;
            --text-dark: #212121;
            --text-light: #757575;
            --light-bg: #f5f7fa;
            --white: #ffffff;
            --gray-bg: #f9f9f9;
            --success: #4caf50;
            --warning: #ff9800;
            --danger: #f44336;
            --border-radius: 8px;
            --box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s ease;
        }

        body {
            font-family: 'Poppins', sans-serif;
            color: var(--text-dark);
            background-color: var(--light-bg);
            scroll-behavior: smooth;
        }

        .section-title {
            position: relative;
            color: var(--primary-dark);
            display: inline-block;
            margin-bottom: 25px;
            font-weight: 600;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 0;
            width: 70px;
            height: 3px;
            background: var(--secondary-color);
            border-radius: 3px;
        }
        
        /* Header Styles */
        .package-header {
            background-size: cover;
            background-position: center;
            color: var(--white);
            padding: 180px 0 100px;
            position: relative;
            overflow: hidden;
        }
        
        .package-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to bottom, rgba(0, 0, 0, 0.6) 0%, rgba(0, 0, 0, 0.4) 100%);
            z-index: 1;
        }
        
        .package-header .container {
            position: relative;
            z-index: 2;
        }

        .package-title {
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
            margin-bottom: 15px;
            transform: translateY(20px);
            opacity: 0;
            animation: fadeInUp 0.8s forwards 0.2s;
        }

        .package-subtitle {
            font-weight: 500;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
            margin-bottom: 30px;
            transform: translateY(20px);
            opacity: 0;
            animation: fadeInUp 0.8s forwards 0.4s;
        }

        @keyframes fadeInUp {
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        .badge-info {
            font-size: 0.9rem;
            background-color: rgba(255, 255, 255, 0.85);
            color: var(--text-dark);
            padding: 8px 16px;
            border-radius: 50px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            transition: var(--transition);
            transform: translateY(20px);
            opacity: 0;
            animation: fadeInUp 0.8s forwards 0.6s;
        }

        .badge-info:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        .badge-info i {
            color: var(--primary-color);
            margin-right: 6px;
        }
        
        /* Back Button */
        .back-button {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1000;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: var(--box-shadow);
            transition: var(--transition);
            color: var(--primary-dark);
        }
        
        .back-button:hover {
            background: var(--white);
            transform: scale(1.1);
            color: var(--primary-color);
        }

        /* Main Content Styles */
        .content-wrapper {
            background-color: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            margin-top: -50px;
            position: relative;
            z-index: 3;
            padding: 40px;
            margin-bottom: 40px;
        }
        
        .content-section {
            margin-bottom: 40px;
        }
        
        .detail-item {
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }
        
        .detail-item i {
            color: var(--primary-color);
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        /* Gallery Styles */
        .package-gallery {
            margin: 30px 0;
        }
        
        .gallery-container {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            grid-gap: 20px;
        }
        
        .gallery-item {
            position: relative;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            height: 220px;
            transform: translateY(20px);
            opacity: 0;
            animation: fadeInUp 0.6s forwards;
            transition: var(--transition);
        }
        
        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: var(--transition);
            display: block;
        }
        
        .gallery-item:hover img {
            transform: scale(1.05);
        }

        .gallery-item:hover::after {
            opacity: 1;
        }

        .gallery-item::after {
            content: "\f002"; /* Font Awesome search icon */
            font-family: "Font Awesome 5 Free";
            font-weight: 900;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-size: 24px;
            z-index: 2;
            opacity: 0;
            transition: var(--transition);
        }

        .gallery-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.3);
            opacity: 0;
            transition: var(--transition);
            z-index: 1;
        }

        .gallery-item:hover::before {
            opacity: 1;
        }
        
        .gallery-caption {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(0, 0, 0, 0.7), transparent);
            color: white;
            padding: 10px;
            font-size: 0.85rem;
            opacity: 0;
            transition: var(--transition);
            transform: translateY(20px);
        }
        
        .gallery-item:hover .gallery-caption {
            opacity: 1;
            transform: translateY(0);
        }
        
        /* Itinerary Styles */
        .itinerary-day {
            margin-bottom: 30px;
            padding-left: 30px;
            position: relative;
            border-left: 2px solid var(--primary-light);
            padding-bottom: 20px;
        }
        
        .itinerary-day:last-child {
            border-left: none;
            padding-bottom: 0;
        }
        
        .itinerary-day::before {
            content: '';
            position: absolute;
            left: -10px;
            top: 0;
            width: 20px;
            height: 20px;
            background: var(--primary-color);
            border-radius: 50%;
            box-shadow: 0 0 0 4px var(--primary-light);
        }
        
        .itinerary-day h4 {
            background: var(--primary-color);
            color: white;
            padding: 8px 15px;
            border-radius: var(--border-radius);
            display: inline-block;
            margin-bottom: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        /* Pricing Styles */
        .pricing-tier {
            background: var(--gray-bg);
            border-radius: var(--border-radius);
            padding: 25px;
            margin-bottom: 20px;
            transition: var(--transition);
            border: 2px solid transparent;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
        }
        
        .pricing-tier:hover {
            border-color: var(--primary-color);
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .pricing-tier h4 {
            color: var(--primary-dark);
            margin-bottom: 10px;
        }

        .pricing-tier .price {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--secondary-color);
            margin-bottom: 10px;
        }
        
        
        
        /* Booking Sidebar */
        .sidebar-booking {
            top: 20px;
            transition: var(--transition);
        }
        
        .card-header {
            background-color: var(--primary-color);
            color: var(--white);
            padding: 15px 20px;
            border-top-left-radius: var(--border-radius);
            border-top-right-radius: var(--border-radius);
        }
        
        .book-now-btn {
            background: linear-gradient(45deg, var(--secondary-color), #ff9800);
            border: none;
            box-shadow: 0 4px 15px rgba(255, 111, 0, 0.3);
            padding: 12px 25px;
            border-radius: 50px;
            font-weight: 600;
            transition: var(--transition);
        }
        
        .book-now-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(255, 111, 0, 0.4);
            background: linear-gradient(45deg, #ff9800, var(--secondary-color));
        }
        
        .book-now-btn i {
            margin-right: 8px;
        }
        
        /* Tabs for mobile */
        .package-tabs {
            margin-bottom: 30px;
            display: none;
        }
        
        .tab-button {
            padding: 10px 20px;
            background: var(--gray-bg);
            border: none;
            border-radius: var(--border-radius);
            margin-right: 10px;
            margin-bottom: 10px;
            color: var(--text-dark);
            transition: var(--transition);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }
        
        .tab-button.active {
            background: var(--primary-color);
            color: var(--white);
            box-shadow: 0 3px 10px rgba(30, 136, 229, 0.3);
        }
        
        /* Responsive adjustments */
        @media (max-width: 992px) {
            .content-wrapper {
                padding: 25px;
            }
            
            .gallery-container {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .sidebar-booking {
                margin-top: 40px;
                position: static !important;
            }
        }
        
        @media (max-width: 768px) {
            .package-tabs {
                display: flex;
                flex-wrap: wrap;
            }
            
            .tab-content > div:not(.active-tab) {
                display: none;
            }
            
            .gallery-container {
                grid-template-columns: 1fr;
            }
            
            .package-header {
                padding: 150px 0 80px;
            }
            
            .content-wrapper {
                margin-top: -30px;
                padding: 20px;
            }
            
            .review-user img {
                width: 50px;
                height: 50px;
            }
        }
        
        /* Animation styles */
        .fade-in {
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.8s forwards;
        }
        
        .delay-1 { animation-delay: 0.1s; }
        .delay-2 { animation-delay: 0.2s; }
        .delay-3 { animation-delay: 0.3s; }
        .delay-4 { animation-delay: 0.4s; }
        .delay-5 { animation-delay: 0.5s; }
        
        /* Lightbox customization */
        .lb-prev, .lb-next, .lb-close {
            filter: drop-shadow(0 0 5px rgba(0, 0, 0, 0.5));
        }
        
        .lb-dataContainer {
            padding-top: 15px;
        }
    </style>
</head>
<body>
    <!-- Back Button -->
    <a href="javascript:void(0)" onclick="goBack()" class="back-button" aria-label="Go back to previous page">
        <i class="fas fa-arrow-left fa-lg"></i>
    </a>

    <!-- Package Header -->
    <section class="package-header" style="background-image: url('uploads/packages/<?php echo $package['image']; ?>');">
        <div class="container">
            <div class="row">
                <div class="col-lg-10 mx-auto text-center">
                    <h1 class="package-title"><?php echo htmlspecialchars($package['name']); ?></h1>
                    <p class="package-subtitle lead"><?php echo htmlspecialchars($package['destination_name']); ?></p>
                    <div class="d-flex justify-content-center gap-3 mt-4 flex-wrap">
                        <span class="badge-info">
                            <i class="fas fa-clock"></i> <?php echo $package['duration']; ?> days
                        </span>
                        <span class="badge-info">
                            <i class="fas fa-tag"></i> From ₱<?php echo number_format($package['price'], 2); ?>
                        </span>
                        <span class="badge-info">
                            <i class="fas fa-users"></i> <?php echo $package['available_slots']; ?> slots available
                        </span>
                        
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="container">
        <div class="content-wrapper">
            <!-- Mobile tabs -->
            <div class="package-tabs">
                <button class="tab-button active" data-tab="details">Details</button>
                <button class="tab-button" data-tab="itinerary">Itinerary</button>
                <button class="tab-button" data-tab="gallery">Gallery</button>
                
            </div>
            
            <div class="row">
                <div class="col-lg-8">
                    <div class="tab-content">
                        <!-- Package Description and Details -->
                        <div id="details" class="active-tab">
                            <!-- Package Description -->
                            <div class="content-section fade-in delay-1">
                                <h3 class="section-title">About This Package</h3>
                                <div class="mb-4">
                                    <?php echo $package['description']; ?>
                                </div>
                                
                                <!-- Package Details -->
                                <h3 class="section-title">Package Details</h3>
                                <div class="mb-4">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <?php if (!empty($package['category'])): ?>
                                            <div class="detail-item">
                                                <i class="fas fa-tag"></i>
                                                <span>Category: <?php echo htmlspecialchars($package['category']); ?></span>
                                            </div>
                                            <?php endif; ?>
                                            <?php if (!empty($package['available_slots'])): ?>
                                            <div class="detail-item">
                                                <i class="fas fa-ticket-alt"></i>
                                                <span>Available Slots: <?php echo $package['available_slots']; ?></span>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-6">
                                            <?php if (!empty($package['group_pricing'])): ?>
                                            <div class="detail-item">
                                                <i class="fas fa-users-cog"></i>
                                                <span>Group Pricing: <?php echo htmlspecialchars($package['group_pricing']); ?></span>
                                            </div>
                                            <?php endif; ?>
                                            <?php if (!empty($package['duration'])): ?>
                                            <div class="detail-item">
                                                <i class="fas fa-calendar-day"></i>
                                                <span>Duration: <?php echo $package['duration']; ?> days</span>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Activities -->
                                <?php if (!empty($package['activities'])): ?>
                                <div class="content-section fade-in delay-2">
                                    <h3 class="section-title">Activities</h3>
                                    <div class="mb-4">
                                        <?php echo $package['activities']; ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <!-- Inclusions -->
                                <?php if (!empty($package['includes'])): ?>
                                <div class="content-section fade-in delay-3">
                                    <h3 class="section-title">What's Included</h3>
                                    <div class="mb-4">
                                        <?php echo $package['includes']; ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <!-- Exclusions -->
                                <?php if (!empty($package['excludes'])): ?>
                                <div class="content-section fade-in delay-4">
                                    <h3 class="section-title">What's Not Included</h3>
                                    <div class="mb-4">
                                        <?php echo $package['excludes']; ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <!-- Booking Requirements -->
                                <?php if (!empty($package['booking_requirements'])): ?>
                                <div class="content-section fade-in delay-5">
                                    <h3 class="section-title">Booking Requirements</h3>
                                    <div class="mb-4">
                                        <?php echo nl2br(htmlspecialchars($package['booking_requirements'])); ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <!-- Payment Options -->
                                <?php if (!empty($package['payment_options'])): ?>
                                <div class="content-section fade-in delay-5">
                                    <h3 class="section-title">Payment Options</h3>
                                    <div class="mb-4">
                                        <?php echo nl2br(htmlspecialchars($package['payment_options'])); ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Itinerary -->
                        <?php if (!empty($package['itinerary'])): ?>
                        <div id="itinerary" class="content-section fade-in">
                            <h3 class="section-title">Itinerary</h3>
                            <div class="mb-4">
                                <?php 
                                $itinerary = json_decode($package['itinerary'], true);
                                if (is_array($itinerary)) {
                                    foreach ($itinerary as $day => $details) {
                                        echo '<div class="itinerary-day fade-in">';
                                        echo '<h4>Day ' . ($day + 1) . '</h4>';
                                        echo '<p>' . $details . '</p>';
                                        echo '</div>';
                                    }
                                } else {
                                    echo $package['itinerary'];
                                }
                                ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                       <?php if ($gallery->num_rows > 0 || !empty($package['gallery_images'])): ?>
                        <div id="gallery" class="content-section fade-in delay-3">
                            <h3 class="section-title">Gallery</h3>
                            <div class="gallery-container">
                                <?php
                                if (!empty($package['gallery_images'])) {
                                    $gallery_images = json_decode($package['gallery_images'], true);
                                    if (is_array($gallery_images)) {
                                        foreach ($gallery_images as $image) {
                                            $image_path = "uploads/packages/gallery/" . $image;
                                            if (file_exists($image_path)) {
                                                echo "<div class='gallery-item'>";
                                                echo "<a href='$image_path' data-lightbox='package-gallery' data-title='Gallery image'>";
                                                echo "<img src='$image_path' alt='Gallery image'>";
                                                echo "<div class='gallery-caption'>Gallery image</div>";
                                                echo "</a>";
                                                echo "</div>";
                                            }
                                        }
                                    }
                                }
                                ?>
                            </div>
                        </div>
                        <?php endif; 
                        
                        ?>
                    </div>
                </div>               
                    <div class="col-lg-4">
                    <!-- Booking Sidebar -->
                    <div class="card sidebar-booking sticky-top">
                        <div class="card-header">
                            <h4 class="mb-0"><i class="fas fa-clipboard-check me-2"></i>Book This Package</h4>
                        </div>
                        <div class="card-body">
                            <div class="detail-item">
                                <i class="fas fa-users"></i>
                                <?php
                                $isInternational = isset($package['destination_type']) && $package['destination_type'] === 'international';
                                $minPersons = 10;
                                $maxPersons = $isInternational ? 35 : 40;
                                ?>
                                <span>
                                    <strong>Group Size:</strong><br>
                                    Minimum: <?php echo $minPersons; ?> persons<br>
                                    Maximum: <?php echo $maxPersons; ?> persons<br>
                                    <?php if ($package['max_persons'] == 1): ?>
                                        <small class="text-warning">*Single supplement surcharge applies for solo travelers</small>
                                    <?php endif; ?>
                                </span>
                            </div>
                            <div class="detail-item">
                                <i class="fas fa-tag"></i>
                                <span>From ₱<?php echo number_format($package['price'], 2); ?></span>
                            </div>
                            
                            <hr>
                            
                            <!-- Pricing Tiers -->
                            <?php if (isset($pricing_tiers) && !empty($pricing_tiers) && $pricing_tiers->num_rows > 0): ?>
                            <h5 class="mb-3">Pricing Options</h5>
                            <?php while($tier = $pricing_tiers->fetch_assoc()): ?>
                            <div class="pricing-tier">
                                <h4><?php echo htmlspecialchars($tier['name']); ?></h4>
                                <div class="price">₱<?php echo number_format($tier['price'], 2); ?></div>
                                <p><i class="fas fa-users me-2"></i>Max <?php echo $tier['max_persons']; ?> persons</p>
                                <p class="small text-muted"><?php echo htmlspecialchars($tier['description']); ?></p>
                            </div>
                            <?php endwhile; ?>
                            <?php endif; ?>
                            
                            <a href="booking_form.php?package_id=<?php echo $package_id; ?>&destination_id=<?php echo $package['destination_id']; ?>" class="btn book-now-btn w-100 mt-3">
                                <i class="fas fa-calendar-check"></i> Book Now
                            </a>
                            
                            <!-- Additional booking info -->
                            <div class="mt-4 text-center">
                                <p class="mb-2"><i class="fas fa-shield-alt text-success me-2"></i>Secure booking</p>
                                <p class="small text-muted">Free cancellation up to 48 hours before departure</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include "includes/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/js/lightbox.min.js"></script>
    <script>
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true
        });
        
        // Image error handling
        document.addEventListener('DOMContentLoaded', function() {
            const galleryImages = document.querySelectorAll('.gallery-item img');
            galleryImages.forEach(img => {
                img.onerror = function() {
                    console.log('Failed to load image:', this.src);
                    this.src = 'images/placeholder.jpg'; // Fallback image
                    this.alt = 'Image not available';
                };
            });
            
            // Smooth scroll for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    
                    document.querySelector(this.getAttribute('href')).scrollIntoView({
                        behavior: 'smooth'
                    });
                });
            });
            
            // Mobile tabs functionality
            const tabButtons = document.querySelectorAll('.tab-button');
            const tabContents = document.querySelectorAll('.tab-content > div');
            
            tabButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Remove active class from all buttons
                    tabButtons.forEach(btn => btn.classList.remove('active'));
                    
                    // Add active class to clicked button
                    this.classList.add('active');
                    
                    // Show the corresponding tab content
                    const tabId = this.getAttribute('data-tab');
                    
                    // Remove active-tab class from all content sections
                    tabContents.forEach(content => content.classList.remove('active-tab'));
                    
                    // Add active-tab class to the selected content section
                    document.getElementById(tabId).classList.add('active-tab');
                    
                    // Scroll to top of the content
                    document.querySelector('.tab-content').scrollIntoView({
                        behavior: 'smooth'
                    });
                });
            });
            
            // Customize lightbox
            lightbox.option({
                'resizeDuration': 300,
                'wrapAround': true,
                'albumLabel': 'Image %1 of %2',
                'fadeDuration': 300,
                'showImageNumberLabel': true
            });
            
            // Apply fade-in animations to elements as they scroll into view
            const fadeElements = document.querySelectorAll('.content-section:not(.fade-in)');
            
            const fadeObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('fade-in');
                        fadeObserver.unobserve(entry.target);
                    }
                });
            }, {
                threshold: 0.1
            });
            
            fadeElements.forEach(element => {
                fadeObserver.observe(element);
            });
            
            // Add active class to currently visible section for mobile nav
            const sections = document.querySelectorAll('.content-section');
            const navObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting && entry.intersectionRatio >= 0.5) {
                        const id = entry.target.getAttribute('id');
                        if (id) {
                            document.querySelectorAll('.tab-button').forEach(btn => {
                                btn.classList.remove('active');
                                if (btn.getAttribute('data-tab') === id) {
                                    btn.classList.add('active');
                                }
                            });
                        }
                    }
                });
            }, {
                threshold: [0.5]
            });
            
            sections.forEach(section => {
                if (section.id) navObserver.observe(section);
            });
        });
        function goBack() {
        if (document.referrer) {
            window.location.href = document.referrer;
        } else {
            window.location.href = 'index.php';
        }
    }
    </script>
</html>