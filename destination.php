<?php
session_start();
include "db.php";

// Get destination ID and fetch details
$destination_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch destination details with error handling
$destination = $conn->query("
    SELECT d.*, 
           d.best_time_to_visit,  /* Explicitly select this field */
           COUNT(DISTINCT p.id) as package_count,
           COUNT(DISTINCT pr.id) as review_count,
           AVG(pr.rating) as avg_rating
    FROM destinations d
    LEFT JOIN packages p ON d.id = p.destination_id AND p.status = 1
    LEFT JOIN package_reviews pr ON p.id = pr.package_id AND pr.status = 1
    WHERE d.id = $destination_id AND d.status = 1
    GROUP BY d.id
")->fetch_assoc();

if (!$destination) {
    header("Location: index.php");
    exit();
}

// Fetch available packages
$packages = $conn->query("
    SELECT p.*, 
           AVG(r.rating) as avg_rating,
           COUNT(r.id) as review_count
    FROM packages p
    LEFT JOIN package_reviews r ON p.id = r.package_id AND r.status = 1
    WHERE p.destination_id = $destination_id AND p.status = 1
    GROUP BY p.id
    ORDER BY p.featured DESC, avg_rating DESC
");

// Parse gallery images JSON
$gallery_images = [];
if (!empty($destination['gallery_images'])) {
    $gallery_images = json_decode($destination['gallery_images'], true) ?: [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($destination['name']); ?> - Yana Biyahi Na Travel and Tours</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/packages.css">
    <style>
        /* Base styles */
        :root {
            --primary-color: #2563eb;
            --secondary-color: #0d6efd;
            --accent-color: #f59e0b;
            --light-bg: #f8fafc;
            --dark-bg: #1e293b;
            --text-light: #f8fafc;
            --text-dark: #1e293b;
            --shadow-sm: 0 2px 4px rgba(0,0,0,0.05);
            --shadow-md: 0 5px 15px rgba(0,0,0,0.05);
            --shadow-lg: 0 15px 30px rgba(0,0,0,0.1);
            --border-radius: 10px;
            --transition: all 0.3s ease;
        }

        /* Hero section enhancements */
        .destination-hero {
            position: relative;
            height: 80vh;
            background-size: cover;
            background-position: center;
            color: white;
            overflow: hidden;
        }

        .destination-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to bottom, rgba(0,0,0,0.2), rgba(0,0,0,0.7));
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        .destination-content {
            max-width: 800px;
            padding: 2rem;
            animation: fadeInUp 1s ease;
            backdrop-filter: blur(5px);
            background-color: rgba(0,0,0,0.3);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-lg);
        }

        .destination-stats {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-top: 2rem;
            flex-wrap: wrap;
        }

        .stat-item {
            text-align: center;
            animation: fadeInUp 1s ease;
            animation-delay: 0.2s;
            background-color: rgba(255,255,255,0.1);
            padding: 1rem;
            border-radius: var(--border-radius);
            backdrop-filter: blur(10px);
            min-width: 120px;
            transition: var(--transition);
        }

        .stat-item:hover {
            transform: translateY(-5px);
            background-color: rgba(255,255,255,0.2);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
            color: var(--accent-color);
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }

        .stat-label {
            font-size: 1rem;
            opacity: 0.9;
            font-weight: 500;
        }

        /* Description section enhancements */
        .destination-description {
            background: white;
            padding: 5rem 0;
            position: relative;
        }

        .description-content {
            max-width: 800px;
            margin: 0 auto;
            animation: fadeIn 1s ease;
        }

        .description-content h2 {
            position: relative;
            display: inline-block;
            margin-bottom: 2rem;
            font-weight: 700;
            color: var(--dark-bg);
        }

        .description-content h2:after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: var(--primary-color);
            border-radius: 2px;
        }

        .description-content .lead {
            font-size: 1.1rem;
            line-height: 1.8;
            color: #4b5563;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            border-radius: 30px;
            transition: var(--transition);
            box-shadow: var(--shadow-sm);
        }

        .btn-primary:hover {
            background-color: #1d4ed8;
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
        }

        /* Info section enhancements */
        .destination-info-section {
            padding: 5rem 0;
            background: var(--light-bg);
            position: relative;
            overflow: hidden;
        }

        .destination-info-section:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%232563eb" fill-opacity="0.05" d="M0,192L48,197.3C96,203,192,213,288,229.3C384,245,480,267,576,250.7C672,235,768,181,864,181.3C960,181,1056,235,1152,234.7C1248,235,1344,181,1392,154.7L1440,128L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>');
            background-size: cover;
            background-position: center;
            opacity: 0.5;
            z-index: 0;
        }

        .destination-info-section .container {
            position: relative;
            z-index: 1;
        }

        .destination-info-section h2 {
            position: relative;
            display: inline-block;
            margin-bottom: 3rem;
            font-weight: 700;
            color: var(--dark-bg);
        }

        .destination-info-section h2:after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: var(--primary-color);
            border-radius: 2px;
        }

        .info-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-md);
            padding: 2.5rem;
            height: 100%;
            transition: var(--transition);
            border-top: 5px solid var(--primary-color);
            position: relative;
            overflow: hidden;
        }

        .info-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-lg);
        }

        .info-card:after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100px;
            background: var(--primary-color);
            opacity: 0.1;
            border-radius: 50%;
            transform: translate(50%, -50%);
            z-index: 0;
        }

        .info-icon {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            position: relative;
            z-index: 1;
            transition: var(--transition);
        }

        .info-card:hover .info-icon {
            transform: scale(1.2);
        }

        .info-card h3 {
            font-weight: 600;
            margin-bottom: 1.5rem;
            position: relative;
            z-index: 1;
        }

        .info-card p {
            color: #4b5563;
            line-height: 1.7;
            position: relative;
            z-index: 1;
        }

        /* Gallery section enhancements */
        /* Enhanced Gallery section */
                .gallery-section {
                    padding: 5rem 0;
                    background: white;
                    position: relative;
                }
        
                .gallery-section h2 {
                    position: relative;
                    display: inline-block;
                    margin-bottom: 3rem;
                    font-weight: 700;
                    color: var(--dark-bg);
                }
        
                .gallery-section h2:after {
                    content: '';
                    position: absolute;
                    bottom: -10px;
                    left: 50%;
                    transform: translateX(-50%);
                    width: 80px;
                    height: 4px;
                    background: var(--primary-color);
                    border-radius: 2px;
                }
        
                .gallery-container {
                    display: grid;
                    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
                    gap: 1.5rem;
                    margin-top: 2rem;
                }
        
                .gallery-item {
                    border-radius: var(--border-radius);
                    overflow: hidden;
                    box-shadow: var(--shadow-md);
                    cursor: pointer;
                    transition: var(--transition);
                    position: relative;
                    height: 250px;
                }
        
                .gallery-item:hover {
                    transform: scale(1.03);
                    box-shadow: var(--shadow-lg);
                }
        
                .gallery-item img {
                    width: 100%;
                    height: 100%;
                    object-fit: cover;
                    transition: transform 0.5s ease;
                }
        
                .gallery-item:hover img {
                    transform: scale(1.1);
                }
        
                .gallery-overlay {
                    position: absolute;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0,0,0,0.4);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    opacity: 0;
                    transition: var(--transition);
                }
        
                .gallery-item:hover .gallery-overlay {
                    opacity: 1;
                }
        
                .gallery-overlay i {
                    color: white;
                    font-size: 2rem;
                    transform: scale(0.8);
                    transition: transform 0.3s ease;
                }
        
                .gallery-item:hover .gallery-overlay i {
                    transform: scale(1);
                }
        
                .gallery-item:after {
                    content: '';
                    position: absolute;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: linear-gradient(to top, rgba(0,0,0,0.5), transparent);
                    opacity: 0;
                    transition: var(--transition);
                }

                .gallery-item:hover:after {
                    opacity: 1;
                }

        /* Packages section enhancements */
        .packages-section {
            padding: 5rem 0;
            background: var(--light-bg);
            position: relative;
        }

        .packages-section h2 {
            position: relative;
            display: inline-block;
            margin-bottom: 3rem;
            font-weight: 700;
            color: var(--dark-bg);
        }

        .packages-section h2:after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: var(--primary-color);
            border-radius: 2px;
        }

        .package-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 2rem;
        }

        .package-card {
            background: white;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow-md);
            transition: var(--transition);
        }

        .package-card:hover {
            transform: translateY(-15px);
            box-shadow: var(--shadow-lg);
        }

        .package-image {
            position: relative;
            height: 200px;
            overflow: hidden;
        }

        .package-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .package-card:hover .package-image img {
            transform: scale(1.1);
        }

        .package-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: var(--accent-color);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 30px;
            font-weight: bold;
            box-shadow: var(--shadow-sm);
        }

        .package-content {
            padding: 1.5rem;
        }

        .package-content h3 {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--dark-bg);
        }

        .package-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            color: #6b7280;
        }

        .package-meta span {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .package-meta i {
            color: var(--primary-color);
        }

        .package-price {
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .old-price {
            text-decoration: line-through;
            color: #9ca3af;
            font-size: 0.9rem;
        }

        .current-price {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
        }

        .btn-book {
            display: inline-block;
            background: var(--primary-color);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
            text-align: center;
            width: 100%;
        }

        .btn-book:hover {
            background: #1d4ed8;
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
            color: white;
        }

        /* Modal enhancements */
        .modal-content {
            border-radius: var(--border-radius);
            overflow: hidden;
            border: none;
        }

        .modal-img {
            width: 100%;
            height: auto;
            max-height: 80vh;
            object-fit: contain;
        }

        .modal-prev, .modal-next {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(0,0,0,0.5);
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
            z-index: 10;
        }

        .modal-prev:hover, .modal-next:hover {
            background: rgba(0,0,0,0.8);
            transform: translateY(-50%) scale(1.1);
        }

        .modal-prev {
            left: 20px;
        }

        .modal-next {
            right: 20px;
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .destination-hero {
                height: 60vh;
            }
            
            .stat-item {
                min-width: 100px;
            }
            
            .stat-number {
                font-size: 2rem;
            }
            
            .gallery-container {
                grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            }
            
            .gallery-item {
                height: 200px;
            }
            
            .package-grid {
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            }
        }

        @media (max-width: 576px) {
            .destination-content {
                padding: 1.5rem;
            }
            
            .destination-stats {
                gap: 1rem;
            }
            
            .stat-item {
                min-width: 90px;
                padding: 0.75rem;
            }
            
            .stat-number {
                font-size: 1.75rem;
            }
            
            .gallery-container {
                grid-template-columns: 1fr 1fr;
            }
            
            .gallery-item {
                height: 180px;
            }
        }
    </style>
</head>
<body>
    <style>
            /* Add this to your existing styles */
            .back-button {
                position: fixed;
                top: 20px;
                left: 20px;
                z-index: 1000;
                background: rgba(255, 255, 255, 0.9);
                border: none;
                padding: 12px 20px;
                border-radius: 30px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                transition: all 0.3s ease;
                display: flex;
                align-items: center;
                gap: 8px;
                font-weight: 500;
                color: var(--dark-bg);
                text-decoration: none;
            }
    
            .back-button:hover {
                transform: translateX(-5px);
                box-shadow: 0 4px 15px rgba(0,0,0,0.15);
                background: white;
                color: var(--primary-color);
            }
    
            .back-button i {
                font-size: 1.2rem;
            }
        </style>
    </head>
    <body>
        <!-- Replace navbar include with back button -->
        <a href="index.php" class="back-button">
            <i class="fas fa-arrow-left"></i>
            <span>Back to Home</span>
        </a>
    
        <!-- Destination Hero Section -->
        <section class="destination-hero" style="background-image: url('uploads/destinations/<?php echo $destination['image']; ?>');">
            <div class="destination-overlay">
                <div class="destination-content">
                    <h1 class="display-4 fw-bold mb-4"><?php echo htmlspecialchars($destination['name']); ?></h1>
                    <p class="lead mb-4"><?php echo htmlspecialchars($destination['country']); ?></p>
                    <div class="destination-stats">
                        <div class="stat-item">
                            <div class="stat-number"><?php echo $destination['package_count']; ?>+</div>
                            <div class="stat-label">Available Packages</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number"><?php echo number_format($destination['avg_rating'], 1); ?></div>
                            <div class="stat-label">Average Rating</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number"><?php echo $destination['review_count']; ?>+</div>
                            <div class="stat-label">Reviews</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Destination Description -->
        <section class="destination-description">
            <div class="container">
                <div class="description-content">
                    <h2 class="text-center mb-4">About <?php echo htmlspecialchars($destination['name']); ?></h2>
                    <p class="lead text-center mb-5"><?php echo nl2br(htmlspecialchars($destination['description'])); ?></p>

                    <!-- Display gallery images if available -->
                    <?php if (!empty($gallery_images)): ?>
                    <div class="text-center mb-4">
                        <a href="#gallery" class="btn btn-primary">View Gallery</a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- Destination Information Section -->
        <section class="destination-info-section">
            <div class="container">
                <h2 class="text-center mb-5">Travel Information</h2>
                <div class="row g-4">
                    <!-- Inside the destination-info-section -->
                    <?php if (!empty($destination['best_time_to_visit'])): ?>
                    <div class="col-md-4" data-aos="fade-up">
                        <div class="info-card">
                            <div class="text-center">
                                <i class="fas fa-calendar-alt info-icon"></i>
                                <h3 class="mb-3">Best Time to Visit</h3>
                            </div>
                            <p><?php echo nl2br(htmlspecialchars($destination['best_time_to_visit'])); ?></p>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($destination['travel_requirements'])): ?>
                    <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                        <div class="info-card">
                            <div class="text-center">
                                <i class="fas fa-passport info-icon"></i>
                                <h3 class="mb-3">Travel Requirements</h3>
                            </div>
                            <p><?php echo nl2br(htmlspecialchars($destination['travel_requirements'])); ?></p>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($destination['transportation_details'])): ?>
                    <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                        <div class="info-card">
                            <div class="text-center">
                                <i class="fas fa-bus info-icon"></i>
                                <h3 class="mb-3">Transportation</h3>
                            </div>
                            <p><?php echo nl2br(htmlspecialchars($destination['transportation_details'])); ?></p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- Gallery Section -->
        <?php if (!empty($gallery_images)): ?>
        <section id="gallery" class="gallery-section">
            <div class="container">
                <h2 class="text-center mb-4">Destination Gallery</h2>
                <div class="gallery-container">
                    <?php foreach ($gallery_images as $index => $image): ?>
                    <div class="gallery-item" data-aos="fade-up" data-aos-delay="<?php echo $index * 50; ?>" onclick="openModal(<?php echo $index; ?>)">
                        <img src="uploads/destinations/gallery/<?php echo htmlspecialchars($image); ?>" alt="<?php echo htmlspecialchars($destination['name']); ?> Gallery Image">
                        <div class="gallery-overlay">
                            <i class="fas fa-search-plus"></i>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <!-- Available Packages -->
        <section class="packages-section">
            <div class="container">
                <h2 class="text-center mb-5">Available Travel Packages</h2>
                <div class="package-grid">
                    <?php 
                    $delay = 0;
                    while($package = $packages->fetch_assoc()):
                        $discounted_price = $package['price'];
                        $has_discount = false;
                        if(isset($package['discount']) && $package['discount'] > 0) {
                            $has_discount = true;
                            $discounted_price = $package['price'] - ($package['price'] * $package['discount'] / 100);
                        }
                    ?>
                        <div class="package-card" data-aos="fade-up" data-aos-delay="<?php echo $delay; ?>">
                            <div class="package-image">
                                <img src="uploads/packages/<?php echo $package['image']; ?>" 
                                     alt="<?php echo htmlspecialchars($package['name']); ?>">
                                <?php if($has_discount): ?>
                                    <div class="package-badge">
                                        <?php echo $package['discount']; ?>% OFF
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="package-content">
                                <h3><?php echo htmlspecialchars($package['name']); ?></h3>
                                <div class="package-meta">
                                    <span><i class="fas fa-clock"></i> <?php echo $package['duration']; ?> days</span>
                                    <span><i class="fas fa-users"></i> Max: <?php echo $package['max_persons']; ?></span>
                                    <span><i class="fas fa-star"></i> <?php echo number_format($package['avg_rating'], 1); ?></span>
                                </div>
                                <div class="package-price">
                                    <?php if($has_discount): ?>
                                        <span class="old-price">₱<?php echo number_format($package['price'], 2); ?></span>
                                    <?php endif; ?>
                                    <span class="current-price">₱<?php echo number_format($discounted_price, 2); ?></span>
                                </div>
                                <a href="package_detail.php?id=<?php echo $package['id']; ?>" class="btn-book">View Details</a>
                            </div>
                        </div>
                    <?php 
                    $delay += 100;
                    endwhile; 
                    ?>
                </div>
            </div>
        </section>

        <!-- Gallery Modal -->
        <?php if (!empty($gallery_images)): ?>
        <div class="modal fade" id="galleryModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body position-relative p-0">
                        <button type="button" class="btn-close position-absolute top-0 end-0 m-3 bg-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        <img id="modalImage" src="" alt="Gallery Image" class="modal-img">
                        <div class="modal-prev" onclick="changeImage(-1)"><i class="fas fa-chevron-left"></i></div>
                        <div class="modal-next" onclick="changeImage(1)"><i class="fas fa-chevron-right"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php include "includes/footer.php"; ?>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
        <script>
            AOS.init({
                duration: 800,
                offset: 100,
                once: true
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

            <?php if (!empty($gallery_images)): ?>
            // Gallery modal functionality
            const galleryImages = <?php echo json_encode($gallery_images); ?>;
            let currentImageIndex = 0;
            const galleryModal = new bootstrap.Modal(document.getElementById('galleryModal'));
            
            function openModal(index) {
                currentImageIndex = index;
                document.getElementById('modalImage').src = 'uploads/destinations/gallery/' + galleryImages[index];
                galleryModal.show();
            }
            
            function changeImage(step) {
                currentImageIndex = (currentImageIndex + step + galleryImages.length) % galleryImages.length;
                document.getElementById('modalImage').src = 'uploads/destinations/gallery/' + galleryImages[currentImageIndex];
            }
            
            // Keyboard navigation for gallery
            document.addEventListener('keydown', function(e) {
                if (document.getElementById('galleryModal').classList.contains('show')) {
                    if (e.key === 'ArrowLeft') {
                        changeImage(-1);
                    } else if (e.key === 'ArrowRight') {
                        changeImage(1);
                    } else if (e.key === 'Escape') {
                        galleryModal.hide();
                    }
                }
            });
            <?php endif; ?>
        </script>
</body>
</html> 