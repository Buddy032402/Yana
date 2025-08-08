<?php
session_start();
include "db.php";

// Fetch all available packages
// Get filter values
$destination = isset($_GET['destination']) ? (int)$_GET['destination'] : '';
$duration = isset($_GET['duration']) ? $_GET['duration'] : '';
$price_range = isset($_GET['price']) ? $_GET['price'] : '';

// Build the WHERE clause
$where_conditions = ["p.status = 1"];
if (!empty($destination)) {
    $where_conditions[] = "p.destination_id = " . $destination;
}

if (!empty($duration)) {
    list($min_days, $max_days) = explode('-', $duration);
    if ($max_days === '+') {
        $where_conditions[] = "p.duration >= " . (int)$min_days;
    } else {
        $where_conditions[] = "p.duration BETWEEN " . (int)$min_days . " AND " . (int)$max_days;
    }
}

if (!empty($price_range)) {
    list($min_price, $max_price) = explode('-', $price_range);
    if ($max_price === '+') {
        $where_conditions[] = "p.price >= " . (int)$min_price;
    } else {
        $where_conditions[] = "p.price BETWEEN " . (int)$min_price . " AND " . (int)$max_price;
    }
}

// Combine conditions
$where_clause = implode(" AND ", $where_conditions);

// Update the main query
$packages = $conn->query("
    SELECT p.*, d.name as destination_name, 
           AVG(r.rating) as avg_rating,
           COUNT(r.id) as review_count
    FROM packages p
    LEFT JOIN destinations d ON p.destination_id = d.id
    LEFT JOIN package_reviews r ON p.id = r.package_id AND r.status = 1
    WHERE {$where_clause}
    GROUP BY p.id
    ORDER BY p.featured DESC, avg_rating DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Travel Packages - Yana Biyahi Na Travel and Tours</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/packages.css">
    <style>
        /* Add back button styles */
        .back-button {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1000;
            background: rgba(255,255,255,0.9);
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .back-button:hover {
            transform: scale(1.1);
            background: #4169E1;
            color: white;
        }

        .packages-hero {
            /* Remove margin-top since navbar is removed */
            margin-top: 0;
            position: relative;
            height: 50vh;
            background-image: url('images/packages-hero.jpg');
            background-size: cover;
            background-position: center;
            color: white;
            overflow: hidden;
        }

        .packages-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        .packages-content {
            max-width: 800px;
            padding: 2rem;
            animation: fadeInUp 1s ease;
        }

        .filter-section {
            background: #f8f9fa;
            padding: 2rem 0;
            border-bottom: 1px solid #eee;
            margin-top: 80px; /* Add margin to prevent navbar overlap */
        }

        .filter-container {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            align-items: flex-end; /* Changed from center to flex-end */
            justify-content: center;
        }

        .filter-item {
            flex: 1;
            min-width: 200px;
            max-width: 250px;
            display: flex;
            flex-direction: column;
        }

        .filter-btn {
            background: #4169E1;
            color: white;
            border: none;
            padding: 0.5rem 1.5rem;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
            height: 38px; /* Match the height of select inputs */
            margin-top: auto; /* Push button to bottom */
        }

        .filter-btn:hover {
            background: #3158d3;
        }

        .packages-section {
            padding: 4rem 0;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <!-- Back Button -->
    <a href="index.php" class="back-button">
        <i class="fas fa-arrow-left fa-lg"></i>
    </a>

    <!-- Filter Section -->
    <section class="filter-section">
        <div class="container">
            <form action="" method="GET" id="filter-form">
                <div class="filter-container">
                    <div class="filter-item">
                        <label for="destination" class="form-label">Destination</label>
                        <select class="form-select" id="destination" name="destination">
                            <option value="">All Destinations</option>
                            <?php
                            $destinations = $conn->query("SELECT id, name FROM destinations WHERE status = 1 ORDER BY name");
                            while($dest = $destinations->fetch_assoc()):
                            ?>
                            <option value="<?php echo $dest['id']; ?>" <?php echo ($destination == $dest['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($dest['name']); ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="filter-item">
                        <label for="duration" class="form-label">Duration</label>
                        <select class="form-select" id="duration" name="duration">
                            <option value="">Any Duration</option>
                            <option value="1-3" <?php echo ($duration == '1-3') ? 'selected' : ''; ?>>1-3 days</option>
                            <option value="4-7" <?php echo ($duration == '4-7') ? 'selected' : ''; ?>>4-7 days</option>
                            <option value="8-14" <?php echo ($duration == '8-14') ? 'selected' : ''; ?>>8-14 days</option>
                            <option value="15+" <?php echo ($duration == '15+') ? 'selected' : ''; ?>>15+ days</option>
                        </select>
                    </div>
                    <div class="filter-item">
                        <label for="price" class="form-label">Price Range</label>
                        <select class="form-select" id="price" name="price">
                            <option value="">Any Price</option>
                            <option value="0-5000" <?php echo ($price_range == '0-5000') ? 'selected' : ''; ?>>Under ₱5,000</option>
                            <option value="5000-10000" <?php echo ($price_range == '5000-10000') ? 'selected' : ''; ?>>₱5,000 - ₱10,000</option>
                            <option value="10000-20000" <?php echo ($price_range == '10000-20000') ? 'selected' : ''; ?>>₱10,000 - ₱20,000</option>
                            <option value="20000+" <?php echo ($price_range == '20000+') ? 'selected' : ''; ?>>Above ₱20,000</option>
                        </select>
                    </div>
    
                    <div class="filter-item d-flex align-items-end">
                        <button type="submit" class="filter-btn w-100">Apply Filters</button>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <!-- Available Packages -->
    <section class="packages-section">
        <div class="container">
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
                                <div class="package-detail-item">
                                    <i class="fas fa-users"></i>
                                    <span>Max: <?php echo isset($package['max_people']) ? $package['max_people'] : '10'; ?> people</span>
                                </div>
                                <?php if(isset($package['difficulty']) && !empty($package['difficulty'])): ?>
                                <div class="package-detail-item">
                                    <i class="fas fa-hiking"></i>
                                    <span><?php echo htmlspecialchars($package['difficulty']); ?></span>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="package-features">
                                <?php if(isset($package['includes_hotel']) && $package['includes_hotel']): ?>
                                    <span><i class="fas fa-hotel"></i> Hotel</span>
                                <?php endif; ?>
                                <?php if(isset($package['includes_meals']) && $package['includes_meals']): ?>
                                    <span><i class="fas fa-utensils"></i> Meals</span>
                                <?php endif; ?>
                                <?php if(isset($package['includes_transport']) && $package['includes_transport']): ?>
                                    <span><i class="fas fa-plane"></i> Transport</span>
                                <?php endif; ?>
                                <?php if(isset($package['includes_guide']) && $package['includes_guide']): ?>
                                    <span><i class="fas fa-user-tie"></i> Guide</span>
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
                $delay += 100;
                endwhile; 
                
                // If no packages found
                if ($packages->num_rows == 0):
                ?>
                <div class="text-center w-100 py-5">
                    <i class="fas fa-search fa-3x mb-3 text-muted"></i>
                    <h3>No packages found</h3>
                    <p>Try adjusting your filters or check back later for new packages.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <?php include "includes/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 800,
            offset: 100,
            once: true
        });

        // Remove the existing filter form handling and replace with this:
        document.getElementById('filter-form').addEventListener('submit', function(e) {
            const form = this;
            const formData = new FormData(form);
            const params = new URLSearchParams();
        
            for (const [key, value] of formData.entries()) {
                if (value) {
                    params.append(key, value);
                }
            }
        
            window.location.href = 'view_all_packages.php' + (params.toString() ? '?' + params.toString() : '');
            e.preventDefault();
        });
    </script>
</body>
</html>