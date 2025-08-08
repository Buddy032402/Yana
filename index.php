<?php
session_start();
include "db.php";


// Add this query to fetch top picks
$top_picks = handleQuery("
    SELECT tp.*, d.name as destination_name, d.image as destination_image,
           d.country, d.description as destination_description
    FROM top_picks tp
    JOIN destinations d ON tp.destination_id = d.id
    ORDER BY tp.featured_order
    LIMIT 6
");

// Check database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Add error handling to queries
function handleQuery($query, $errorMessage = "Database error") {
    global $conn;
    $result = $conn->query($query);
    if (!$result) {
        error_log("Query error: " . $conn->error);
        return false;
    }
    return $result;
}

// Update your queries to use the new function
// Update the query to fetch only active destinations
$popular_destinations = handleQuery("
    SELECT d.*, 
           (SELECT COUNT(*) FROM packages WHERE destination_id = d.id) as package_count 
    FROM destinations d 
    WHERE d.status = 1 
    ORDER BY d.featured DESC, package_count DESC
"); // Removed the LIMIT 6

// Fetch featured packages (packages marked as featured by admin)
$featured_packages = $conn->query("
    SELECT p.*, d.name as destination_name,
           AVG(r.rating) as avg_rating,
           COUNT(r.id) as review_count
    FROM packages p 
    LEFT JOIN destinations d ON p.destination_id = d.id
    LEFT JOIN package_reviews r ON p.id = r.package_id
    WHERE p.featured = 1 AND p.status = 1 
    GROUP BY p.id
    ORDER BY avg_rating DESC
    LIMIT 6
");

// Fetch top rated packages (based on actual user reviews, not admin selection)
$top_rated = $conn->query("
    SELECT p.*, d.name as destination_name, 
           AVG(r.rating) as avg_rating,
           COUNT(r.id) as review_count
    FROM packages p 
    LEFT JOIN destinations d ON p.destination_id = d.id
    LEFT JOIN package_reviews r ON p.id = r.package_id
    WHERE p.status = 1 
    GROUP BY p.id
    HAVING review_count > 0
    ORDER BY avg_rating DESC, review_count DESC
    LIMIT 6
");

// Update the top picks query to include client information
// First, update the top picks query to include the rating information
$top_rated = handleQuery("
    SELECT p.*, d.name as destination_name, 
           tp.client_name, tp.client_image, tp.client_rating, tp.description as client_review,
           d.id as destination_id,
           COALESCE(AVG(pr.rating), 0) as avg_rating,
           COUNT(pr.id) as review_count
    FROM top_picks tp
    JOIN destinations d ON tp.destination_id = d.id
    JOIN packages p ON d.id = p.destination_id
    LEFT JOIN package_reviews pr ON p.id = pr.package_id
    WHERE p.status = 1 
    GROUP BY p.id, tp.id
    ORDER BY tp.featured_order ASC
"); // Removed the LIMIT 6 to get all records

// Replace the existing testimonials query with this combined query
// Update the testimonials query to include all necessary fields
$testimonials = $conn->query("
    (SELECT 
        t.rating,
        t.content as review,
        t.created_at,
        t.customer_name as user_name,
        t.customer_email as user_email,
        t.image,
        p.name as package_name,
        'testimonial' as source
    FROM testimonials t
    LEFT JOIN packages p ON t.package_id = p.id
    WHERE t.status = 'approved')
    UNION ALL
    (SELECT 
        r.rating,
        r.review,
        r.created_at,
        u.name as user_name,
        u.email as user_email,
        u.profile_image as image,
        p.name as package_name,
        'review' as source
    FROM package_reviews r
    LEFT JOIN users u ON r.user_id = u.id
    LEFT JOIN packages p ON r.package_id = p.id
    WHERE r.status = 1 AND r.rating >= 4)
    ORDER BY created_at DESC
    LIMIT 6
");
?>

<!DOCTYPE html>
<html lang="en">
<!-- Add this right after your existing CSS links in the head section -->
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yana Biyahe Na Travel and Tours - Your Journey Begins Here</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/location.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/animations.css">
    <link rel="stylesheet" href="css/about.css">
    <link rel="stylesheet" href="css/booking.css">
    <link rel="stylesheet" href="css/contact.css">
    <link rel="stylesheet" href="css/destinations.css">
    <link rel="stylesheet" href="css/packages.css">
    <link rel="stylesheet" href="css/testimonials.css">
    <link rel="stylesheet" href="css/faq.css">
    <link rel="stylesheet" href="css/footer.css">
    <link rel="stylesheet" href="css/responsive.css">
    <link rel="stylesheet" href="css/home.css">
    <link rel="stylesheet" href="css/top_picks.css">
    <link rel="stylesheet" href="css/mission_vision.css">
    <link rel="stylesheet" href="css/About_Dream_Travels.css">
    
</head>
<!-- Add this rght before the closing </body> tag -->
<body>
    <?php include "includes/navbar.php"; ?>
<!-- Hero Section -->
<section class="hero">
    <video class="hero-video" autoplay muted loop playsinline>
        <source src="videos/hero-bg.mp4" type="video/mp4">
    </video>
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <h1 class="animate-fade-up">Discover Your Next Adventure</h1>
        <p class="animate-fade-up-delay">Experience the world's most breathtaking destinations</p>
        <!-- ... rest of the hero content ... -->
            <div class="hero-search animate-fade-up-delay">
                <div class="hero-search">
                    <form action="search.php" method="GET" class="search-container">
                        <input type="text" 
                               name="keyword" 
                               placeholder="Search destinations, packages, or activities..." 
                               class="form-control"
                               required>
                        <button type="submit" class="search-btn">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </form>
                </div>
            </div>
 <div class="hero-stats animate-fade-up-delay">
                <div class="stat-item">
                    <span class="number">500+</span>
                    <span class="label">Destinations</span>
                </div>
                <div class="stat-item">
                    <span class="number">10k+</span>
                    <span class="label">Happy Travelers</span>
                </div>
                <div class="stat-item">
                    <span class="number">10+</span>
                    <span class="label">Years Experience</span>
                </div>
            </div>
            
            <!-- Messenger Button -->
            <div class="messenger-button-container animate-fade-up-delay">
                <a href="https://www.facebook.com/messages/t/976049275771938" target="_blank" class="messenger-button">
                    <i class="fab fa-facebook-messenger"></i>
                    <span>Chat with Us</span>
                </a>
            </div>
        </div>
    </section>
    

    <!-- Destinations Section -->
    <section id="destinations" class="section">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
            <span class="section-subtitle">"Escape to the Most Beautiful Places on Earth"</span>
                <h2 class="section-title">Popular Destinations</h2>
                <p class="section-description">Discover the world's most amazing places</p>
            </div>
            <div class="destination-grid">
                <?php 
                $count = 0;
                $total_shown = 8; // Initial limit to show
                while($destination = $popular_destinations->fetch_assoc()): 
                ?>
                    <div class="destination-card <?php echo $count >= $total_shown ? 'hidden-destination' : ''; ?>" 
                         data-aos="zoom-in" 
                         data-aos-delay="<?php echo $count * 100; ?>">
                        <div class="destination-image">
                            <?php 
                            $image_path = "uploads/destinations/" . $destination['image'];
                            if (!empty($destination['image']) && file_exists($image_path)): 
                            ?>
                                <img src="<?php echo $image_path; ?>" 
                                     alt="<?php echo htmlspecialchars($destination['name']); ?>"
                                     style="width: 100%; 
                                            height: 300px; 
                                            object-fit: cover;
                                            border-radius: 12px;
                                            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
                                            transition: transform 0.5s ease, filter 0.3s ease;">
                            <?php else: ?>
                                <img src="images/placeholder.jpg" 
                                     alt="No image available"
                                     style="width: 100%; 
                                            height: 300px; 
                                            object-fit: cover;
                                            border-radius: 12px;
                                            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
                                            transition: transform 0.5s ease, filter 0.3s ease;">
                            <?php endif; ?>
                            <style>
                                .destination-image img:hover {
                                    transform: scale(1.05);
                                    filter: brightness(1.1);
                                }
                            </style>
                            <div class="destination-overlay">
                                <div class="destination-content">
                                    <div class="destination-info-container">
                                        <h3 class="destination-zoom"><?php echo htmlspecialchars($destination['name']); ?></h3>
                                        <p class="destination-location destination-zoom">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <?php echo htmlspecialchars($destination['country']); ?>
                                        </p>
                                    </div>
                                    <div class="destination-meta">
                                        <span class="package-count">
                                            <i class="fas fa-box"></i>
                                            <?php echo $destination['package_count']; ?> packages
                                        </span>
                                    </div>
                                    <a href="destination.php?id=<?php echo $destination['id']; ?>" 
                                       class="btn btn-explore">Explore More</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php 
                $count++;
                endwhile; 
                
// Check if there are more than 8 destinations
                $total_destinations = $conn->query("SELECT COUNT(*) as total FROM destinations WHERE status = 1")->fetch_assoc()['total'];
                if($total_destinations > 8):
                ?>
                    <div class="text-center mt-4 w-100" data-aos="fade-up" id="view-all-container">
                        <button class="btn-view-all" id="view-all-destinations">
                            View All Destinations <i class="fas fa-arrow-right"></i>
                        </button>
                        <button class="btn-view-less" id="view-less-destinations" style="display: none;">
                            <i class="fas fa-arrow-left"></i> View Less
                        </button>
                    </div>
                <?php endif; ?>
                
          <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const viewAllBtn = document.getElementById('view-all-destinations');
                    const viewLessBtn = document.getElementById('view-less-destinations');
                    
                    if (viewAllBtn) {
                        viewAllBtn.addEventListener('click', function() {
                            // Show all hidden destinations
                            document.querySelectorAll('.hidden-destination').forEach(function(destination) {
                                destination.style.display = 'block';
                                setTimeout(() => {
                                    destination.style.opacity = '1';
                                    destination.style.transform = 'translateY(0)';
                                }, 10);
                            });
                            
                            // Hide "View All" and show "View Less" button
                            this.style.display = 'none';
                            if (viewLessBtn) {
                                viewLessBtn.style.display = 'inline-flex';
                            }
                        });
                    }

                    if (viewLessBtn) {
                        viewLessBtn.addEventListener('click', function() {
                            // Hide extra destinations
                            document.querySelectorAll('.hidden-destination').forEach(function(destination) {
                                destination.style.opacity = '0';
                                destination.style.transform = 'translateY(20px)';
                                setTimeout(() => {
                                    destination.style.display = 'none';
                                }, 400);
                            });
                            
                            // Show "View All" and hide "View Less" button
                            if (viewAllBtn) {
                                viewAllBtn.style.display = 'inline-flex';
                            }
                            this.style.display = 'none';
                            
                            // Scroll back to destinations section
                            document.getElementById('destinations').scrollIntoView({ behavior: 'smooth' });
                        });
                    }
                });
            </script>
            </div>
        </div>
    </section>

  <!-- Enhanced Featured Packages -->
  <section id="packages" class="section bg-light">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <span class="section-subtitle">"Plan Less, Explore More with These Packages"</span>
            <h2 class="section-title">Featured Travel Packages</h2>
            <p class="section-description">Handpicked packages for unforgettable experiences</p>
        </div>
        <div class="package-grid">
            <?php 
            $count = 0;
            $total_shown = 6; // Initial limit to show
            $featured_packages_array = array();
            
            // Store all packages in an array
            while($package = $featured_packages->fetch_assoc()) {
                $featured_packages_array[] = $package;
            }
            
            // Display initial packages
            for($i = 0; $i < min(count($featured_packages_array), $total_shown); $i++):
                $package = $featured_packages_array[$i];
                
                // Calculate discount price if applicable
                $discounted_price = $package['price'];
                $has_discount = false;
                if(isset($package['discount']) && $package['discount'] > 0) {
                    $has_discount = true;
                    $discounted_price = $package['price'] - ($package['price'] * $package['discount'] / 100);
                }
            ?>
            <div class="package-card" data-aos="zoom-in" data-aos-delay="<?php echo $count * 100; ?>">
                <!-- Package card content remains the same -->
                <div class="package-image">
                    <?php 
                    $image_path = "uploads/packages/" . $package['image'];
                    if (!empty($package['image']) && file_exists($image_path)): 
                    ?>
                        <img src="<?php echo $image_path; ?>" 
                             alt="<?php echo htmlspecialchars($package['name']); ?>">
                    <?php else: ?>
                        <img src="images/placeholder.jpg" alt="No image available">
                    <?php endif; ?>
                    <?php if($has_discount): ?>
                        <div class="package-badge">
                            <?php echo $package['discount']; ?>% OFF
                        </div>
                    <?php endif; ?>
                    <?php if(isset($package['avg_rating']) && $package['avg_rating'] > 0): ?>
                        <div class="package-rating-badge">
                            <i class="fas fa-star"></i>
                            <?php echo number_format($package['avg_rating'], 1); ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="package-content-wrapper">
                    <!-- Rest of the package card content remains the same -->
                    <div class="package-info">
                        <h3 class="package-title"><?php echo htmlspecialchars($package['name']); ?></h3>
                        <p class="package-location">
                            <i class="fas fa-map-marker-alt"></i>
                            <?php echo htmlspecialchars($package['destination_name']); ?>
                        </p>
                        
                                <div class="package-details">
                                    <div class="package-detail-item">
                                        <i class="fas fa-clock"></i>
                                        <span><?php echo $package['duration']; ?> days</span>
                                    </div>
                                    <?php if(isset($package['difficulty']) && !empty($package['difficulty'])): ?>
                                    <div class="package-detail-item">
                                        <i class="fas fa-hiking"></i>
                                        <span><?php echo htmlspecialchars($package['difficulty']); ?></span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                        
                                   </div>
                                    <div class="package-price-row">
                    <div class="package-price-container">
                        <span class="package-price-label">Price per person</span>
                        <div>   
                            <?php if($has_discount): ?>
                                <span class="package-old-price">₱<?php echo number_format($package['price'], 2); ?></span>
                            <?php endif; ?>
                            <span class="package-price">₱<?php echo number_format($discounted_price, 2); ?></span>
                        </div>
                    </div>
                    <a href="package_detail.php?id=<?php echo $package['id']; ?>" class="btn-view-details">
                        <i class="fas fa-eye"></i> View Details
                    </a>
                </div>
                </div>
            </div>
            <?php 
                $count++;
            endfor; 
            
            // Check if there are more packages
            $total_packages = $conn->query("SELECT COUNT(*) as total FROM packages WHERE status = 1")->fetch_assoc()['total'];
            if($total_packages > $total_shown):
            ?>
            <div class="text-center mt-4 w-100" data-aos="fade-up" id="view-all-container">
                <a href="view_all_packages.php" class="btn-view-all">
                    View All Packages <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<!-- Top Rated Packages -->
<section id="top-picks" class="section">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <span class="section-subtitle">"Our Most Loved Travel Experiences"</span>
            <h2 class="section-title">Top Client Picks</h2>
            <p class="section-description">Most loved destinations by our travelers</p>
        </div>
        <div class="package-grid">
            <?php 
            // Fetch top picks directly from the top_picks table
            $top_picks_query = $conn->query("
                SELECT tp.*, d.name as destination_name, d.country, d.image as destination_image
                FROM top_picks tp
                JOIN destinations d ON tp.destination_id = d.id
                ORDER BY tp.featured_order
            ");
            
            // Store all top picks in an array
            $top_picks_array = array();
            while($pick = $top_picks_query->fetch_assoc()) {
                $top_picks_array[] = $pick;
            }
            
            // Get the total count of top picks
            $total_top_picks = count($top_picks_array);
            
            // Display initial 6 top picks
            for($i = 0; $i < min($total_top_picks, 6); $i++):
                $pick = $top_picks_array[$i];
            ?>
                <div class="package-card" data-aos="zoom-in" data-aos-delay="<?php echo $i * 100; ?>">
                    <div class="package-image">
                        <?php 
                        $image_path = "uploads/destinations/" . $pick['destination_image'];
                        if (!empty($pick['destination_image']) && file_exists($image_path)): 
                        ?>
                            <img src="<?php echo $image_path; ?>" 
                                 alt="<?php echo htmlspecialchars($pick['destination_name']); ?>">
                        <?php else: ?>
                            <img src="images/placeholder.jpg" alt="No image available">
                        <?php endif; ?>
                        <div class="package-rating-badge">
                            <i class="fas fa-star"></i>
                            <?php echo number_format($pick['client_rating'], 1); ?>
                        </div>
                    </div>
                    <div class="package-content-wrapper">
                        <h3 class="package-title"><?php echo htmlspecialchars($pick['destination_name']); ?></h3>
                        <p class="package-location">
                            <i class="fas fa-map-marker-alt"></i>
                            <?php echo htmlspecialchars($pick['country']); ?>
                        </p>
                        
                        <!-- Add client review -->
                        <div class="client-review">
                            <p class="review-text">"<?php echo htmlspecialchars($pick['description']); ?>"</p>
                            <div class="client-info">
                                <img src="uploads/clients/<?php echo $pick['client_image']; ?>" 
                                     alt="<?php echo htmlspecialchars($pick['client_name']); ?>"
                                     class="client-avatar">
                                <span class="client-name"><?php echo htmlspecialchars($pick['client_name']); ?></span>
                            </div>
                        </div>
                        
                        <div class="package-divider"></div>
                        
                        <div class="package-price-row">
                            <a href="destination.php?id=<?php echo $pick['destination_id']; ?>" class="btn-view-details">
                                <i class="fas fa-eye"></i> View Destination
                            </a>
                             <a href="view_top_picks.php" class="btn btn-primary">
                            <i class="fas fa-info-circle"></i> View Details
                             </a>
                        </div>
                      
                    </div>
                </div>
            <?php 
            endfor;
            
            // Display remaining top picks (initially hidden)
            for($i = 6; $i < $total_top_picks; $i++):
                $pick = $top_picks_array[$i];
            ?>
                <div class="package-card hidden-top-pick" data-aos="zoom-in" data-aos-delay="<?php echo ($i - 6) * 100; ?>">
                    <div class="package-image">
                        <?php 
                        $image_path = "uploads/destinations/" . $pick['destination_image'];
                        if (!empty($pick['destination_image']) && file_exists($image_path)): 
                        ?>
                            <img src="<?php echo $image_path; ?>" 
                                 alt="<?php echo htmlspecialchars($pick['destination_name']); ?>">
                        <?php else: ?>
                            <img src="images/placeholder.jpg" alt="No image available">
                        <?php endif; ?>
                        <div class="package-rating-badge">
                            <i class="fas fa-star"></i>
                            <?php echo number_format($pick['client_rating'], 1); ?>
                        </div>
                    </div>
                    <div class="package-content-wrapper">
                        <h3 class="package-title"><?php echo htmlspecialchars($pick['destination_name']); ?></h3>
                        <p class="package-location">
                            <i class="fas fa-map-marker-alt"></i>
                            <?php echo htmlspecialchars($pick['country']); ?>
                        </p>
                        
                        <div class="client-review">
                            <p class="review-text">"<?php echo htmlspecialchars($pick['description']); ?>"</p>
                            <div class="client-info">
                                <img src="uploads/clients/<?php echo $pick['client_image']; ?>" 
                                     alt="<?php echo htmlspecialchars($pick['client_name']); ?>"
                                     class="client-avatar">
                                <span class="client-name"><?php echo htmlspecialchars($pick['client_name']); ?></span>
                            </div>
                        </div>
                        
                        <div class="package-divider"></div>
                        
                        <div class="package-price-row">
                            <a href="destination.php?id=<?php echo $pick['destination_id']; ?>" class="btn-view-details">
                                <i class="fas fa-eye"></i> View Destination
                            </a>
                            <a href="destination_details.php?id=<?php echo $pick['destination_id']; ?>" class="btn-view-details">
                                <i class="fas fa-info-circle"></i> View Details
                            </a>
                        </div>
                    </div>
                </div>
            <?php 
            endfor;
            
            // Show View All button if there are more than 6 top picks
            if($total_top_picks > 6):
            ?>
            <div class="text-center mt-4 w-100" data-aos="fade-up" id="top-picks-view-all-container">
                <button class="btn-view-all" id="view-all-top-picks">
                    View All Top Picks <i class="fas fa-arrow-right"></i>
                </button>
                <button class="btn-view-less" id="view-less-top-picks" style="display: none;">
                    View Less <i class="fas fa-arrow-left"></i>
                </button>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Add this script right after the top-picks section -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Top Picks View All/Less functionality
        const viewAllTopPicks = document.getElementById('view-all-top-picks');
        const viewLessTopPicks = document.getElementById('view-less-top-picks');
        
        if (viewAllTopPicks) {
            viewAllTopPicks.addEventListener('click', function() {
                // Show all hidden top picks
                document.querySelectorAll('.hidden-top-pick').forEach(function(pick) {
                    pick.style.display = 'block';
                    setTimeout(() => {
                        pick.style.opacity = '1';
                        pick.style.transform = 'translateY(0)';
                    }, 10);
                });
                
                // Hide "View All" and show "View Less" button
                this.style.display = 'none';
                viewLessTopPicks.style.display = 'inline-flex';
            });
        }
        
        if (viewLessTopPicks) {
            viewLessTopPicks.addEventListener('click', function() {
                // Hide extra top picks
                document.querySelectorAll('.hidden-top-pick').forEach(function(pick) {
                    pick.style.opacity = '0';
                    pick.style.transform = 'translateY(20px)';
                    setTimeout(() => {
                        pick.style.display = 'none';
                    }, 400);
                });
                
                // Show "View All" and hide "View Less" button
                viewAllTopPicks.style.display = 'inline-flex';
                this.style.display = 'none';
                
                // Scroll back to top picks section
                document.getElementById('top-picks').scrollIntoView({ behavior: 'smooth' });
            });
        }
    });
</script>

<!-- Testimonials Section -->
<section id="testimonials" class="section bg-light">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <span class="section-subtitle">"Real Stories from Our Happy Travelers"</span>
            <h2 class="section-title">What Our Customers Say</h2>
            <p class="section-description">Real experiences from our valued clients</p>
        </div>
        <div class="testimonial-grid">
            <?php 
            $count = 0;
            while($testimonial = $testimonials->fetch_assoc()):
                if($count < 6):
            ?>

            
                <div class="testimonial-card" data-aos="fade-up">
                    <div class="testimonial-header">
                        <div class="testimonial-author">
                            <?php if($testimonial['image']): ?>
                                <img src="uploads/testimonials/<?php echo $testimonial['image']; ?>" 
                                     alt="<?php echo htmlspecialchars($testimonial['user_name']); ?>"
                                     class="author-image">
                            <?php else: ?>
                                <div class="author-initial">
                                    <?php echo strtoupper(substr($testimonial['user_name'], 0, 1)); ?>
                                </div>
                            <?php endif; ?>
                            <div class="author-info">
                                <h4 class="author-name"><?php echo htmlspecialchars($testimonial['user_name']); ?></h4>
                                <?php if($testimonial['user_email']): ?>
                                    <p class="author-email"><?php echo htmlspecialchars($testimonial['user_email']); ?></p>
                                <?php endif; ?>
                                <?php if($testimonial['package_name']): ?>
                                    <p class="package-name">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <?php echo htmlspecialchars($testimonial['package_name']); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="testimonial-rating">
                            <?php for($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star <?php echo $i <= $testimonial['rating'] ? 'active' : ''; ?>"></i>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <div class="testimonial-content">
                        <p class="testimonial-text"><?php echo htmlspecialchars($testimonial['review']); ?></p>
                        <p class="testimonial-date">
                            <i class="far fa-calendar-alt"></i>
                            <?php echo date('M d, Y', strtotime($testimonial['created_at'])); ?>
                        </p>
                    </div>
                </div>
            <?php 
            endif;
            $count++;
            endwhile; 
            
            // Check if there are more testimonials
            $total_testimonials = $conn->query("
                SELECT COUNT(*) as total FROM (
                    SELECT id FROM testimonials WHERE status = 'approved'
                    UNION ALL
                    SELECT id FROM package_reviews WHERE status = 1 AND rating >= 4
                ) as combined_reviews
            ")->fetch_assoc()['total'];
            
            if($total_testimonials > 6):
            ?>
                <div class="text-center mt-4 w-100" data-aos="fade-up">
                    <a href="all-testimonials.php" class="btn-view-all">
                        View All Testimonials <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>


<!-- About Us Section -->

<section class="about-section" id="about">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <span class="section-subtitle">Who We Are</span>
            <h2 class="section-title">About Yana Biyahe Na Travel and Tours</h2>
            <p class="section-description">Your trusted partner in creating memorable journeys</p>
        </div>
        <div class="row align-items-center">
            <div class="col-lg-6" data-aos="fade-right">
                <div class="about-content">
                    <h3>Creating Unforgettable Travel Experiences Since 2015</h3>
                    <p class="lead mb-4">We are passionate about transforming your travel dreams into reality, offering personalized experiences that combine adventure, comfort, and cultural immersion.</p>
                    <div class="about-features">
                        <div class="feature-item">
                            <i class="fas fa-check-circle"></i>
                            <span>Expert Travel Planning</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-check-circle"></i>
                            <span>Handpicked Destinations</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-check-circle"></i>
                            <span>24/7 Customer Support</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <div class="about-image-wrapper">
                    <img src="images/about_new.jpg" alt="Dream Travels Experience" class="img-fluid rounded-lg shadow">     
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Mission and Vision Section -->
    <section class="mission-vision-section py-5">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                <span class="section-subtitle">Our Purpose</span>
                <h2 class="section-title">Mission & Vision</h2>
                <p class="section-description">Guiding principles that drive our passion for travel</p>
            </div>
            <div class="row">
                <div class="col-lg-6 mb-4 mb-lg-0" data-aos="fade-right" data-aos-delay="100">
                    <div class="mission-card">
                        <div class="card-icon">
                            <i class="fas fa-bullseye"></i>
                        </div>
                        <h3>Our Mission</h3>
                        <div class="mission-content">
                            <p class="mission-point"><i class="fas fa-check-circle"></i> Provide seamless and personalized travel services that cater to the diverse needs of our clients.</p>
                            <p class="mission-point"><i class="fas fa-check-circle"></i> Promote local and international destinations while supporting eco-friendly and community-based tourism.</p>
                            <p class="mission-point"><i class="fas fa-check-circle"></i> Ensure customer satisfaction through professionalism, integrity, and excellent customer service.</p>
                            <p class="mission-point"><i class="fas fa-check-circle"></i> Offer affordable yet high-quality travel packages that make dream vacations accessible to all.</p>
                            <p class="mission-point"><i class="fas fa-check-circle"></i> Embrace innovation and technology to enhance travel experiences and provide hassle-free booking solutions.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6" data-aos="fade-left" data-aos-delay="200">
                    <div class="vision-card">
                        <div class="card-icon">
                            <i class="fas fa-eye"></i>
                        </div>
                        <h3>Our Vision</h3>
                        <div class="vision-content">
                            <p class="vision-text">"To be a trusted and innovative travel agency, creating unforgettable journeys and enriching experiences while promoting sustainable and responsible tourism worldwide."</p>
                            <div class="vision-values">
                                <div class="value-item">
                                    <i class="fas fa-globe"></i>
                                    <span>Global Perspective</span>
                                </div>
                                <div class="value-item">
                                    <i class="fas fa-handshake"></i>
                                    <span>Customer Trust</span>
                                </div>
                                <div class="value-item">
                                    <i class="fas fa-leaf"></i>
                                    <span>Sustainability</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
 <!-- Booking Inquiry Section -->
 <section id="booking-inquiry" class="section">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                <span class="section-subtitle">Start Your Journey</span>
                <h2 class="section-title">Book Your Travel</h2>
                <p class="section-description">Fill out the form below to start planning your next adventure</p>
            </div>
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="booking-form-container" data-aos="fade-up">
                        <form id="bookingInquiryForm" action="process_booking_inquiry.php" method="POST">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <input type="text" name="name" class="form-control" placeholder="Your Name" required>
                                </div>
                                <div class="col-md-6">
                                    <input type="email" name="email" class="form-control" placeholder="Your Email" required>
                                </div>
                                <div class="col-md-6">
                                    <input type="tel" name="phone" class="form-control" placeholder="Phone Number" required>
                                </div>
                                <div class="col-md-6">
                                    <select name="destination" id="destination-select" class="form-select" required>
                                        <option value="">Select Destination</option>
                                        <?php
                                        $destinations = handleQuery("SELECT id, name FROM destinations WHERE status = 1 ORDER BY name");
                                        while($dest = $destinations->fetch_assoc()):
                                        ?>
                                        <option value="<?php echo $dest['id']; ?>"><?php echo htmlspecialchars($dest['name']); ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <select name="package" id="package-select" class="form-select" required>
                                        <option value="">Select Package</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <input type="number" name="travelers" class="form-control" placeholder="Number of Travelers" min="1" required>
                                </div>
                                <div class="col-md-6">
                                    <input type="date" name="preferred_date" class="form-control" min="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                                <div class="col-md-6">  
                                    <select name="budget_range" class="form-select" required>
                                        <option value="">Select Budget Range</option>
                                        <option value="economy">Economy (₱10,000 - ₱30,000)</option>
                                        <option value="standard">Standard (₱30,000 - ₱50,000)</option>
                                        <option value="luxury">Luxury (₱50,000+)</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <textarea name="special_requests" class="form-control" rows="4" placeholder="Special Requests or Questions"></textarea>
                                </div>
                                <div class="col-12 text-center">
                                    <button type="submit" class="btn btn-primary btn-lg">Submit Inquiry</button>
                                </div>
                            </div>
                        </form>
                        <div id="booking-success" class="alert alert-success mt-3" style="display: none;">
                            Your booking inquiry has been submitted successfully! We'll contact you shortly.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Add this script at the bottom of your index.php file, before the closing body tag -->
    <script>
        // Dynamic package loading based on destination selection
        document.getElementById('destination-select').addEventListener('change', function() {
            const destinationId = this.value;
            const packageSelect = document.getElementById('package-select');
            
            // Clear current options
            packageSelect.innerHTML = '<option value="">Select Package</option>';
            
            if (destinationId) {
                // Add loading state
                packageSelect.disabled = true;
                
                // Fetch packages for the selected destination
                fetch('get_packages.php?destination_id=' + destinationId)
                    .then(response => response.json())
                    .then(data => {
                        // Create a Set to track unique package IDs and prevent duplicates
                        const addedPackages = new Set();
                        
                        data.forEach(package => {
                            // Only add the package if it hasn't been added yet
                            if (!addedPackages.has(package.id)) {
                                const option = document.createElement('option');
                                option.value = package.id;
                                option.textContent = package.name;
                                packageSelect.appendChild(option);
                            
                            }
                        });
                        packageSelect.disabled = false;
                    })
                    .catch(error => {
                        console.error('Error loading packages:', error);
                        packageSelect.disabled = false;
                    });
            }
        });

        // Form submission handling with prevention of double submission
        document.getElementById('bookingInquiryForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitButton = this.querySelector('button[type="submit"]');
            submitButton.disabled = true;
            
            const formData = new FormData(this);
            const customerName = formData.get('name');
            
            fetch('process_booking_inquiry.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const successMessage = `Thank you, ${customerName}! Your booking inquiry has been submitted successfully! We'll contact you shortly.`;
                    document.getElementById('booking-success').innerHTML = successMessage;
                    document.getElementById('booking-success').style.display = 'block';
                    this.reset();
                    document.getElementById('booking-success').scrollIntoView({behavior: 'smooth'});
                    
                    setTimeout(() => {
                        document.getElementById('booking-success').style.display = 'none';
                    }, 5000);
                } else {
                    alert('Error: Please try again.');
                }
            })
            .catch(error => {
                console.error('Error submitting form:', error);
                alert('An error occurred. Please try again.');
            })
            .finally(() => {
                // Re-enable the submit button
                submitButton.disabled = false;
            });
        });
    </script>

    
 <!-- Contact Us Section -->
 <section id="contact" class="section bg-light">
            <div class="container">
                <div class="section-header" data-aos="fade-up">
                    <span class="section-subtitle">Get in Touch</span>
                    <h2 class="section-title">Contact us</h2>
                    <p class="section-description">We're here to help and answer any questions you might have</p>
                </div>
                <div class="row g-4"> 
                    <!-- Contact Information -->
                    <div class="col-lg-4" data-aos="fade-right">
                        <div class="contact-info-card">
                            <div class="contact-info-item">
                                <div class="icon-wrapper">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div class="contact-details">
                                    <h4>Location</h4>
                                    <p>Space 41-5 Generoso St. Corner Cervantes Bo. Obrero, Davao City, Davao City, Philippines, 8000</p>
                                </div>
                            </div>
                            <div class="contact-info-item">
                                <div class="icon-wrapper">
                                    <i class="fas fa-phone"></i>
                                </div>
                                <div class="contact-details">
                                    <h4>Contact</h4>
                                    <p>Phone 0917 311 1569 / WhatsApp +63 917 311 1569</p>
                                </div>
                            </div>
                            <div class="contact-info-item">
                                <div class="icon-wrapper">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div class="contact-details">
                                    <h4>Email</h4>
                                    <p>yanabiyahena@gmail.com</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Contact Form -->
                    <div class="col-lg-8" data-aos="fade-left">
                        <div class="contact-form-container">
                            <div id="messageStatus" class="alert" style="display: none;"></div>
                            <form action="process_contact.php" method="POST" id="contactForm" class="contact-form">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <input type="text" name="name" class="form-control" placeholder="Your Name" required>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="email" name="email" class="form-control" placeholder="Your Email" required>
                                    </div>
                                      <div class="col-md-6">
                                        <input type="tel" name="phone" class="form-control" placeholder="Your Phone">
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" name="subject" class="form-control" placeholder="Subject">
                                    </div>
                                    <div class="col-12">
                                        <textarea name="message" class="form-control" rows="5" placeholder="Your Message" required></textarea>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary">Send Message</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    
    
    <script>
        // ... existing code ...
        document.getElementById('contactForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Disable the submit button to prevent double submission
        const submitButton = this.querySelector('button[type="submit"]');
        if (submitButton.disabled) {
            return; // If button is already disabled, don't submit
        }
        submitButton.disabled = true;
        
        const formData = new FormData(this);
        const customerName = formData.get('name');
        
        fetch('process_contact.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            const messageStatus = document.getElementById('messageStatus');
            if (data.success) {
                messageStatus.className = 'alert alert-success';
                messageStatus.innerHTML = data.message;
                messageStatus.style.display = 'block';
                this.reset();
                messageStatus.scrollIntoView({behavior: 'smooth'});
                
                setTimeout(() => {
                    messageStatus.style.display = 'none';
                }, 5000);
            } else {
                messageStatus.className = 'alert alert-danger';
                messageStatus.innerHTML = data.message;
                messageStatus.style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            const messageStatus = document.getElementById('messageStatus');
            messageStatus.className = 'alert alert-danger';
            messageStatus.innerHTML = 'An error occurred. Please try again later.';
            messageStatus.style.display = 'block';
        })
        .finally(() => {
            // Re-enable the submit button after 2 seconds
            setTimeout(() => {
                submitButton.disabled = false;
            }, 2000);
        });
    });
    // ... existing code ...
    </script>

<section id="location-features" class="section">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                <span class="section-subtitle">OUR LOCATION</span>
                <h2 class="section-title">Visit Our Office</h2>
                <p class="section-description">Conveniently located in the heart of the city</p>
            </div>
            <div class="row g-4">
                <div class="col-lg-6" data-aos="fade-right" data-aos-delay="100">
                    <div class="location-info">
                        <div class="location-feature-card">
                            <div class="feature-icon-wrapper">
                                <i class="fas fa-parking"></i>
                            </div>
                            <div class="feature-content">
                                <h4>Free Parking</h4>
                                <p>Convenient parking space available for our clients</p>
                            </div>
                        </div>
                        <div class="location-feature-card">
                            <div class="feature-icon-wrapper">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="feature-content">
                                <h4>Business Hours</h4>
                                <p>Monday - Saturday: 9:00 AM - 6:00 PM</p>
                                <p>Sunday: Closed</p>
                            </div>
                        </div>
                        <div class="location-feature-card">
                            <div class="feature-icon-wrapper">
                                <i class="fas fa-directions"></i>
                            </div>
                            <div class="feature-content">
                                <h4>Easy to Find</h4>
                                <p>Located at Space 41-5 Generoso St. Corner Cervantes Bo. Obrero, Davao City</p>
                            </div>
                        </div>
                        <div class="location-feature-card">
                            <div class="feature-icon-wrapper">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6" data-aos="fade-left" data-aos-delay="200">
                    <div class="map-container">
                        <iframe 
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d989.1645916713051!2d125.6125917!3d7.0870043!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x32f96daf330010bd%3A0x504ddd46f4384bf0!2sYANA%20BIYAHE%20NA%20TRAVEL%20AND%20TOURS!5e0!3m2!1sen!2sph!4v1710939291721!5m2!1sen!2sph" 
                            width="100%" 
                            height="450" 
                            style="border:0;" 
                            allowfullscreen="" 
                            loading="lazy" 
                            referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include "includes/footer.php"; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script src="js/main.js"></script>
</body>
</html>
