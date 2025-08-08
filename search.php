<?php
session_start();
include "db.php";

// Get the search keyword
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';

if (empty($keyword)) {
    header("Location: index.php");
    exit;
}

// Sanitize the search keyword
$keyword = $conn->real_escape_string($keyword);
$keyword_like = "%{$keyword}%";

// Prepare the queries for different sections
// Search in destinations
// Modify the keyword preparation to handle multiple words
$keywords = explode(' ', $keyword);
$search_conditions = [];
foreach ($keywords as $word) {
    $word = trim($word);
    if (strlen($word) > 2) { // Only search for words longer than 2 characters
        $search_conditions[] = "%{$word}%";
    }
}

// If no valid search terms, use the original keyword
if (empty($search_conditions)) {
    $search_conditions[] = $keyword_like;
}

// Modify destinations query for better matching
$destinations_query = "
    SELECT 
        d.*, 
        'destination' as type,
        (SELECT COUNT(*) FROM packages WHERE destination_id = d.id) as package_count
    FROM destinations d 
    WHERE d.status = 1 
    AND (
        CONCAT(d.name, ' ', d.country, ' ', d.description, ' ', 
               COALESCE(d.highlights, '')) LIKE ?
        OR SOUNDEX(d.name) = SOUNDEX(?)
        OR SOUNDEX(d.country) = SOUNDEX(?)
    )
";

// Modify packages query
$packages_query = "
    SELECT 
        p.*, 
        'package' as type,
        d.name as destination_name,
        AVG(r.rating) as avg_rating,
        COUNT(r.id) as review_count
    FROM packages p
    LEFT JOIN destinations d ON p.destination_id = d.id
    LEFT JOIN package_reviews r ON p.id = r.package_id
    WHERE p.status = 1 
    AND (
        CONCAT(p.name, ' ', p.description, ' ', d.name, ' ', 
               d.country) LIKE ?
        OR SOUNDEX(p.name) = SOUNDEX(?)
        OR SOUNDEX(d.name) = SOUNDEX(?)
    )
    GROUP BY p.id
";

// Modify top picks query
$top_picks_query = "
    SELECT 
        tp.*,
        'top_pick' as type,
        d.name as destination_name,
        d.image as destination_image,
        d.country
    FROM top_picks tp
    JOIN destinations d ON tp.destination_id = d.id
    WHERE 
        CONCAT(d.name, ' ', d.country, ' ', tp.description, ' ', 
               tp.client_name, ' ', COALESCE(d.highlights, '')) LIKE ?
        OR SOUNDEX(d.name) = SOUNDEX(?)
        OR SOUNDEX(tp.client_name) = SOUNDEX(?)
";

// Modify the bind_param calls
$stmt_destinations = $conn->prepare($destinations_query);
$stmt_destinations->bind_param("sss", $keyword_like, $keyword, $keyword);

$stmt_packages = $conn->prepare($packages_query);
$stmt_packages->bind_param("sss", $keyword_like, $keyword, $keyword);

$stmt_top_picks = $conn->prepare($top_picks_query);
$stmt_top_picks->bind_param("sss", $keyword_like, $keyword, $keyword);

$stmt_destinations->execute();
$destinations_results = $stmt_destinations->get_result();

$stmt_packages->execute();
$packages_results = $stmt_packages->get_result();

$stmt_top_picks->execute();
$top_picks_results = $stmt_top_picks->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - <?php echo htmlspecialchars($keyword); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .search-header {
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('images/search-bg.jpg');
            background-size: cover;
            background-position: center;
            padding: 100px 0 50px;
            color: white;
            margin-bottom: 40px;
        }

        .search-form {
            max-width: 600px;
            margin: 20px auto;
        }

        .search-input-group {
            display: flex;
            gap: 10px;
        }

        .result-section {
            margin-bottom: 40px;
            padding: 20px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        }

        .result-card {
            border: 1px solid #eee;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            transition: transform 0.3s ease;
        }

        .result-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .result-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .result-type {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8em;
            margin-bottom: 10px;
        }

        .type-destination {
            background: #e3f2fd;
            color: #1976d2;
        }

        .type-package {
            background: #f3e5f5;
            color: #7b1fa2;
        }

        .type-top-pick {
            background: #fff3e0;
            color: #ef6c00;
        }

        .back-button {
            position: fixed;
            top: 30px;
            left: 30px;
            z-index: 1000;
            background: #4169E1;
            color: white;
            padding: 12px 24px;
            border-radius: 50px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 500;
            box-shadow: 0 4px 15px rgba(65, 105, 225, 0.3);
            transition: all 0.3s ease;
        }

        .back-button:hover {
            background: #1e40af;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(65, 105, 225, 0.4);
        }

        .back-button i {
            font-size: 1.1em;
        }
    </style>
</head>
<body>
    <!-- Remove the navbar include -->
    
    <a href="index.php" class="back-button">
        <i class="fas fa-arrow-left"></i>
        <span>Back to Home</span>
    </a>

    <div class="search-header">
        <div class="container">
            <h1 class="text-center mb-4">Search Results</h1>
            <form action="search.php" method="GET" class="search-form">
                <div class="search-input-group">
                    <input type="text" 
                           name="keyword" 
                           value="<?php echo htmlspecialchars($keyword); ?>" 
                           class="form-control form-control-lg" 
                           placeholder="Search destinations, packages, or activities...">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-search"></i> Search
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="container">
        <?php
        $total_results = $destinations_results->num_rows + 
                        $packages_results->num_rows + 
                        $top_picks_results->num_rows;
        
        if ($total_results == 0): ?>
            <div class="no-results">
                <i class="fas fa-search fa-3x mb-3"></i>
                <h3>No results found</h3>
                <p>Try different keywords or check your spelling</p>
            </div>
        <?php else: ?>
            <p class="lead mb-4">Found <?php echo $total_results; ?> results for "<?php echo htmlspecialchars($keyword); ?>"</p>

            <?php if ($destinations_results->num_rows > 0): ?>
            <div class="result-section">
                <h2 class="mb-4">Destinations</h2>
                <div class="row">
                    <?php while($destination = $destinations_results->fetch_assoc()): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="result-card">
                            <span class="result-type type-destination">
                                <i class="fas fa-map-marker-alt"></i> Destination
                            </span>
                            <img src="uploads/destinations/<?php echo $destination['image']; ?>" 
                                 alt="<?php echo htmlspecialchars($destination['name']); ?>"
                                 class="result-image">
                            <h3><?php echo htmlspecialchars($destination['name']); ?></h3>
                            <p><i class="fas fa-globe"></i> <?php echo htmlspecialchars($destination['country']); ?></p>
                            <p><?php echo substr(htmlspecialchars($destination['description']), 0, 100); ?>...</p>
                            <a href="destination.php?id=<?php echo $destination['id']; ?>" 
                               class="btn btn-primary">
                                View Details
                            </a>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($packages_results->num_rows > 0): ?>
            <div class="result-section">
                <h2 class="mb-4">Travel Packages</h2>
                <div class="row">
                    <?php while($package = $packages_results->fetch_assoc()): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="result-card">
                            <span class="result-type type-package">
                                <i class="fas fa-box"></i> Package
                            </span>
                            <img src="uploads/packages/<?php echo $package['image']; ?>" 
                                 alt="<?php echo htmlspecialchars($package['name']); ?>"
                                 class="result-image">
                            <h3><?php echo htmlspecialchars($package['name']); ?></h3>
                            <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($package['destination_name']); ?></p>
                            <p><i class="fas fa-clock"></i> <?php echo $package['duration']; ?> days</p>
                            <p><i class="fas fa-tag"></i> ₱<?php echo number_format($package['price'], 2); ?></p>
                            <?php if($package['avg_rating']): ?>
                            <p>
                                <i class="fas fa-star text-warning"></i>
                                <?php echo number_format($package['avg_rating'], 1); ?>
                                (<?php echo $package['review_count']; ?> reviews)
                            </p>
                            <?php endif; ?>
                            <a href="package_detail.php?id=<?php echo $package['id']; ?>" 
                               class="btn btn-primary">
                                View Details
                            </a>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($top_picks_results->num_rows > 0): ?>
            <div class="result-section">
                <h2 class="mb-4">Top Picks</h2>
                <div class="row">
                    <?php while($pick = $top_picks_results->fetch_assoc()): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="result-card">
                            <span class="result-type type-top-pick">
                                <i class="fas fa-star"></i> Top Pick
                            </span>
                            <img src="uploads/destinations/<?php echo $pick['destination_image']; ?>" 
                                 alt="<?php echo htmlspecialchars($pick['destination_name']); ?>"
                                 class="result-image">
                            <h3><?php echo htmlspecialchars($pick['destination_name']); ?></h3>
                            <p><i class="fas fa-globe"></i> <?php echo htmlspecialchars($pick['country']); ?></p>
                            <div class="client-info mt-3">
                                <img src="uploads/clients/<?php echo $pick['client_image'] ?: 'default-user.png'; ?>" 
                                     alt="<?php echo htmlspecialchars($pick['client_name']); ?>"
                                     style="width: 40px; height: 40px; border-radius: 50%; margin-right: 10px;">
                                <span><?php echo htmlspecialchars($pick['client_name']); ?></span>
                            </div>
                            <p class="mt-2">"<?php echo substr(htmlspecialchars($pick['description']), 0, 100); ?>..."</p>
                            <a href="view_details_picks.php?id=<?php echo $pick['id']; ?>" 
                               class="btn btn-primary">
                                View Details
                            </a>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

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