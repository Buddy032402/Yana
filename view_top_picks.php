<?php
session_start();
include "db.php";

// Fetch all top picks with destination info
$top_picks = $conn->query("
    SELECT tp.*, d.name as destination_name, d.image as destination_image,
           d.country, d.description as destination_description,
           p.name as package_name, p.price, p.duration,
           COALESCE(AVG(pr.rating), 0) as avg_rating,
           COUNT(pr.id) as review_count
    FROM top_picks tp
    JOIN destinations d ON tp.destination_id = d.id
    LEFT JOIN packages p ON d.id = p.destination_id
    LEFT JOIN package_reviews pr ON p.id = pr.package_id
    GROUP BY tp.id
    ORDER BY tp.featured_order ASC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Top Client Picks - Yana Biyahi Na Travel and Tours</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/top_picks.css">
    <style>
        .top-pick-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            margin-bottom: 30px;
        }

        .top-pick-card:hover {
            transform: translateY(-5px);
        }

        .destination-image {
            height: 300px;
            position: relative;
            overflow: hidden;
        }

        .destination-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .top-pick-card:hover .destination-image img {
            transform: scale(1.05);
        }

        .client-section {
            padding: 20px;
            background: #f8f9fa;
            border-bottom: 1px solid #eee;
        }

        .client-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .client-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #fff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .client-details h4 {
            margin: 0;
            color: #333;
            font-size: 1.1rem;
        }

        .rating {
            color: #ffc107;
            margin-top: 5px;
        }

        .destination-details {
            padding: 20px;
        }

        .destination-name {
            font-size: 1.5rem;
            color: #333;
            margin-bottom: 10px;
        }

        .destination-location {
            color: #666;
            margin-bottom: 15px;
        }

        .destination-description {
            color: #777;
            margin-bottom: 20px;
            line-height: 1.6;
        }

        .stats {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }

        .stat-item {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #666;
        }

        .client-review {
            font-style: italic;
            color: #555;
            background: #fff;
            padding: 15px;
            border-radius: 10px;
            margin-top: 15px;
            position: relative;
            max-height: none; /* Remove any height limitation */
            overflow: visible; /* Ensure content isn't cut off */
            white-space: normal; /* Allow text to wrap */
            word-wrap: break-word; /* Break long words if needed */
            line-height: 1.6; /* Improve readability */
        }

        .client-review::before {
            content: '"';
            font-size: 3rem;
            color: #e1e1e1;
            position: absolute;
            top: -10px;
            left: 5px;
        }

        .view-package-btn {
            display: inline-block;
            padding: 10px 25px;
            background: #4169E1;
            color: white;
            text-decoration: none;
            border-radius: 25px;
            transition: all 0.3s ease;
        }

        .view-package-btn:hover {
            background: #1e40af;
            color: white;
            transform: translateY(-2px);
        }

        .section-header {
            text-align: center;
            margin-bottom: 50px;
        }

        .back-to-top {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #4169E1;
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: all 0.3s ease;
            opacity: 0;
            visibility: hidden;
        }

        .back-to-top.visible {
            opacity: 1;
            visibility: visible;
        }

        .back-to-top:hover {
            background: #1e40af;
            color: white;
            transform: translateY(-3px);
        }
    </style>
</head>
<body>
    <style>
        /* Add this to your existing styles */
        .back-button {
            position: fixed;
            top: 25px;
            left: 25px;
            z-index: 1000;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            width: 55px;
            height: 55px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 6px 25px rgba(0, 0, 0, 0.1);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            color: #4169E1;
            text-decoration: none;
            border: 1px solid rgba(65, 105, 225, 0.1);
        }

        .back-button:hover {
            background: #ffffff;
            transform: translateY(-2px) scale(1.05);
            box-shadow: 0 8px 30px rgba(65, 105, 225, 0.2);
        }

        .back-button:active {
            transform: translateY(0) scale(0.98);
        }

        .back-button i {
            transition: transform 0.2s ease;
        }

        .back-button:hover i {
            transform: translateX(-3px);
        }
    </style>
</head>
<body>

    <!-- Replace back button with direct link to index.php -->
    <a href="index.php" class="back-button" aria-label="Go back to home page">
        <i class="fas fa-arrow-left fa-lg"></i>
    </a>

    <div class="container py-5">
        <div class="section-header" data-aos="fade-up">
            <span class="section-subtitle">Traveler's Choice</span>
            <h2 class="section-title">Our Top Client Picks</h2>
            <p class="section-description">Discover the destinations our clients love the most</p>
        </div>

        <div class="row">
            <?php while($pick = $top_picks->fetch_assoc()): ?>
                <div class="col-lg-6" data-aos="fade-up">
                    <div class="top-pick-card">
                        <div class="destination-image">
                            <img src="uploads/destinations/<?php echo $pick['destination_image']; ?>" 
                                 alt="<?php echo htmlspecialchars($pick['destination_name']); ?>">
                        </div>
                        
                        <div class="client-section">
                            <div class="client-info">
                                <img src="uploads/clients/<?php echo $pick['client_image'] ?: 'default-user.png'; ?>" 
                                     alt="<?php echo htmlspecialchars($pick['client_name']); ?>" 
                                     class="client-avatar">
                                <div class="client-details">
                                    <h4><?php echo htmlspecialchars($pick['client_name']); ?></h4>
                                    <div class="rating">
                                        <?php for($i = 1; $i <= 5; $i++): ?>
                                            <i class="<?php echo ($i <= $pick['client_rating']) ? 'fas' : 'far'; ?> fa-star"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                            </div>
                            <!-- I'll modify the client-review section to better display full comments -->
                                            <div class="client-review">
                                                "<?php echo htmlspecialchars($pick['description']); ?>"
                                            </div>
                        </div>

                        <div class="destination-details">
                            <h3 class="destination-name"><?php echo htmlspecialchars($pick['destination_name']); ?></h3>
                            <p class="destination-location">
                                <i class="fas fa-map-marker-alt"></i> 
                                <?php echo htmlspecialchars($pick['country']); ?>
                            </p>
                            
                            <?php if ($pick['package_name']): ?>
                                <div class="package-info mb-3">
                                    <h4 class="package-title">
                                        <i class="fas fa-box"></i> 
                                        <?php echo htmlspecialchars($pick['package_name']); ?>
                                    </h4>
                                    <p class="destination-description">
                                        <?php echo htmlspecialchars($pick['destination_description']); ?>
                                    </p>
                                    <div class="stats">
                                        <div class="stat-item">
                                            <i class="fas fa-clock"></i>
                                            <span><?php echo $pick['duration']; ?> days</span>
                                        </div>
                                        <div class="stat-item">
                                            <i class="fas fa-star"></i>
                                            <span><?php echo number_format($pick['avg_rating'], 1); ?> (<?php echo $pick['review_count']; ?> reviews)</span>
                                        </div>
                                        <div class="stat-item">
                                            <i class="fas fa-tag"></i>
                                            <span>₱<?php echo number_format($pick['price'], 2); ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <p class="destination-description">
                                    <?php echo htmlspecialchars($pick['destination_description']); ?>
                                </p>
                                <div class="stats">
                                    <div class="stat-item">
                                        <i class="fas fa-info-circle"></i>
                                        <span>No package available</span>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if ($pick['package_name']): ?>
                                <a href="package_detail.php?id=<?php echo $pick['package_id']; ?>" class="view-package-btn">
                                    <i class="fas fa-eye"></i> View Package Details
                                </a>
                            <?php else: ?>
                                <a href="destinationsPrefinal.php?id=<?php echo $pick['destination_id']; ?>" class="view-package-btn">
                                    <i class="fas fa-eye"></i> View Destination Details
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <a href="#" class="back-to-top">
        <i class="fas fa-arrow-up"></i>
    </a>

    <?php include "includes/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 800,
            offset: 100
        });

        // Back to top button
        const backToTop = document.querySelector('.back-to-top');
        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 100) {
                backToTop.classList.add('visible');
            } else {
                backToTop.classList.remove('visible');
            }
        });

        backToTop.addEventListener('click', (e) => {
            e.preventDefault();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });

        // Back button functionality
        // Enhanced back navigation with session storage
        document.addEventListener('DOMContentLoaded', function() {
            sessionStorage.setItem('previousPage', document.referrer);
        });

        function goBack() {
            const previousPage = sessionStorage.getItem('previousPage') || document.referrer;
            const fallbackPages = [
                'destinations.php',
                'view_all_packages.php',
                'index.php'
            ];

            if (previousPage && new URL(previousPage).origin === location.origin) {
                window.location.href = previousPage;
            } else {
                for (const page of fallbackPages) {
                    if (pageExists(page)) {
                        window.location.href = page;
                        return;
                    }
                }
                window.location.href = 'index.php';
            }
        }

        function pageExists(url) {
            const xhr = new XMLHttpRequest();
            xhr.open('HEAD', url, false);
            try {
                xhr.send();
                return xhr.status !== 404;
            } catch(e) {
                return false;
            }
        }
    </script>
</body>
</html>

 <style> section
<style>
    .package-info {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
        border-left: 4px solid #4169E1;
    }

    .package-title {
        font-size: 1.2rem;
        color: #4169E1;
        margin-bottom: 15px;
    }

    .package-title i {
        margin-right: 8px;
    }
</style>