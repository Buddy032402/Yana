<?php
session_start();
include "db.php";

if (!isset($_GET['id'])) {
    header("Location: view_top_picks.php");
    exit;
}

$pick_id = $_GET['id'];

// Fetch detailed information about the specific top pick
// Update the SQL query to remove non-existent columns
$stmt = $conn->prepare("
    SELECT tp.*, 
           d.name as destination_name, 
           d.image as destination_image,
           d.country, 
           d.description as destination_description,
           d.highlights,
           p.name as package_name, 
           p.price, 
           p.duration,
           p.id as package_id,
           COALESCE(AVG(pr.rating), 0) as avg_rating,
           COUNT(pr.id) as review_count
    FROM top_picks tp
    JOIN destinations d ON tp.destination_id = d.id
    LEFT JOIN packages p ON d.id = p.destination_id
    LEFT JOIN package_reviews pr ON p.id = pr.package_id
    WHERE tp.id = ?
    GROUP BY tp.id
");

$stmt->bind_param("i", $pick_id);
$stmt->execute();
$pick = $stmt->get_result()->fetch_assoc();

if (!$pick) {
    header("Location: view_top_picks.php");
    exit;
}

// Fetch related packages for this destination
$related_packages = $conn->query("
    SELECT p.*, 
           AVG(pr.rating) as avg_rating,
           COUNT(pr.id) as review_count
    FROM packages p
    LEFT JOIN package_reviews pr ON p.id = pr.package_id
    WHERE p.destination_id = {$pick['destination_id']}
    AND p.status = 1
    GROUP BY p.id
    LIMIT 3
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pick['destination_name']); ?> - Top Pick Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .hero-section {
            height: 60vh;
            position: relative;
            overflow: hidden;
        }

        .hero-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .hero-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-align: center;
        }

        .client-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-top: -50px;
            position: relative;
            z-index: 2;
        }

        .client-image {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 4px solid white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin: -70px auto 20px;
            display: block;
        }

        .rating {
            color: #ffc107;
            font-size: 1.2rem;
            margin: 10px 0;
        }

        .testimonial {
            font-style: italic;
            color: #666;
            margin: 20px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
            position: relative;
        }

        .testimonial::before {
            content: '"';
            font-size: 4rem;
            color: #e1e1e1;
            position: absolute;
            top: -20px;
            left: 10px;
        }

        .destination-info {
            margin-top: 30px;
        }

        .info-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .highlight-item {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }

        .related-package-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .related-package-card:hover {
            transform: translateY(-5px);
        }

        .package-image {
            height: 200px;
            overflow: hidden;
        }

        .package-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .related-package-card:hover .package-image img {
            transform: scale(1.05);
        }

        .package-details {
            padding: 20px;
        }

        .back-button {
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
            z-index: 1000;
        }

        .back-button:hover {
            background: #1e40af;
            color: white;
            transform: translateY(-3px);
        }
    </style>
</head>
<body>
    <?php include "includes/navbar.php"; ?>

    <div class="hero-section">
        <img src="uploads/destinations/<?php echo $pick['destination_image']; ?>" 
             alt="<?php echo htmlspecialchars($pick['destination_name']); ?>" 
             class="hero-image">
        <div class="hero-overlay">
            <div class="container">
                <h1 class="display-4"><?php echo htmlspecialchars($pick['destination_name']); ?></h1>
                <p class="lead">
                    <i class="fas fa-map-marker-alt"></i> 
                    <?php echo htmlspecialchars($pick['country']); ?>
                </p>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="client-card text-center">
                    <img src="uploads/clients/<?php echo $pick['client_image'] ?: 'default-user.png'; ?>" 
                         alt="<?php echo htmlspecialchars($pick['client_name']); ?>" 
                         class="client-image">
                    <h3><?php echo htmlspecialchars($pick['client_name']); ?></h3>
                    <div class="rating">
                        <?php for($i = 1; $i <= 5; $i++): ?>
                            <i class="<?php echo ($i <= $pick['client_rating']) ? 'fas' : 'far'; ?> fa-star"></i>
                        <?php endfor; ?>
                    </div>
                    <div class="testimonial">
                        "<?php echo htmlspecialchars($pick['description']); ?>"
                    </div>
                </div>
            </div>
        </div>

        <div class="destination-info">
            <div class="row">
                <div class="col-lg-8">
                    <div class="info-card">
                        <h3>About the Destination</h3>
                        <p><?php echo nl2br(htmlspecialchars($pick['destination_description'])); ?></p>
                    </div>

                    <?php if($pick['highlights']): ?>
                    <div class="info-card">
                        <h3>Highlights</h3>
                        <?php 
                        $highlights = explode("\n", $pick['highlights']);
                        foreach($highlights as $highlight): 
                            if(trim($highlight)):
                        ?>
                            <div class="highlight-item">
                                <i class="fas fa-check-circle text-success"></i>
                                <span><?php echo htmlspecialchars(trim($highlight)); ?></span>
                            </div>
                        <?php 
                            endif;
                        endforeach; 
                        ?>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="col-lg-4">
                    <div class="info-card">
                        <h3>Related Packages</h3>
                        <?php while($package = $related_packages->fetch_assoc()): ?>
                            <div class="related-package-card mb-3">
                                <div class="package-image">
                                    <img src="uploads/packages/<?php echo $package['image']; ?>" 
                                         alt="<?php echo htmlspecialchars($package['name']); ?>">
                                </div>
                                <div class="package-details">
                                    <h4><?php echo htmlspecialchars($package['name']); ?></h4>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-primary">₱<?php echo number_format($package['price'], 2); ?></span>
                                        <span><?php echo $package['duration']; ?> days</span>
                                    </div>
                                    <div class="mt-2">
                                        <i class="fas fa-star text-warning"></i>
                                        <?php echo number_format($package['avg_rating'], 1); ?>
                                        (<?php echo $package['review_count']; ?> reviews)
                                    </div>
                                    <a href="view_package.php?id=<?php echo $package['id']; ?>" 
                                       class="btn btn-primary btn-sm mt-2">
                                        View Details
                                    </a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <a href="view_top_picks.php" class="back-button">
        <i class="fas fa-arrow-left"></i>
    </a>

    <?php include "includes/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 800,
            offset: 100
        });
    </script>
</body>
</html>