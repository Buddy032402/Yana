<?php
session_start();
include "db.php";

// Check database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to handle queries with error checking
function handleQuery($query, $errorMessage = "Database error") {
    global $conn;
    $result = $conn->query($query);
    if (!$result) {
        error_log("Query error: " . $conn->error);
        return false;
    }
    return $result;
}

// Get all testimonials and high-rated reviews
$testimonials = handleQuery("
    (SELECT 
        t.id,
        t.rating,
        t.content as review,
        t.created_at,
        t.customer_name as user_name,
        t.customer_email as user_email,
        t.image,
        p.name as package_name,
        p.id as package_id,
        'testimonial' as source
    FROM testimonials t
    LEFT JOIN packages p ON t.package_id = p.id
    WHERE t.status = 'approved')
    UNION ALL
    (SELECT 
        r.id,
        r.rating,
        r.review,
        r.created_at,
        u.name as user_name,
        u.email as user_email,
        u.profile_image as image,
        p.name as package_name,
        p.id as package_id,
        'review' as source
    FROM package_reviews r
    LEFT JOIN users u ON r.user_id = u.id
    LEFT JOIN packages p ON r.package_id = p.id
    WHERE r.status = 1 AND r.rating >= 4)
    ORDER BY created_at DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Testimonials - Yana Biyahe Na Travel and Tours</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/testimonials.css">
    <link rel="stylesheet" href="css/responsive.css">
    <style>
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

        .page-header {
            margin-top: 20px;
            padding-top: 40px;
            text-align: center;
            background: #f8f9fa;
            padding: 3rem 0;
        }

        .page-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 0;
            position: relative;
            display: inline-block;
        }

        .page-header h1:after {
            content: '';
            display: block;
            width: 60px;
            height: 3px;
            background: #4169E1;
            margin: 15px auto 0;
        }
    </style>
</head>
<body>
    <!-- Back Button -->
    <a href="index.php" class="back-button">
        <i class="fas fa-arrow-left fa-lg"></i>
    </a>

    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12">
                    <h1>All Testimonials</h1>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section id="testimonials" class="section">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
            </div>
            
            <div class="testimonial-grid">
                <?php 
                if($testimonials && $testimonials->num_rows > 0):
                    while($testimonial = $testimonials->fetch_assoc()):
                ?>
                    <div class="testimonial-card" data-aos="fade-up">
                        <div class="testimonial-header">
                            <div class="testimonial-author">
                                <?php if(!empty($testimonial['image']) && file_exists("uploads/testimonials/".$testimonial['image'])): ?>
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
                                            <?php if($testimonial['package_id']): ?>
                                                <a href="package.php?id=<?php echo $testimonial['package_id']; ?>">
                                                    <?php echo htmlspecialchars($testimonial['package_name']); ?>
                                                </a>
                                            <?php else: ?>
                                                <?php echo htmlspecialchars($testimonial['package_name']); ?>
                                            <?php endif; ?>
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
                    endwhile;
                else:
                ?>
                    <div class="col-12 text-center">
                        <div class="alert alert-info">
                            <h4>No testimonials found</h4>
                            <p>Be the first to share your experience with us!</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="text-center mt-5">
                <a href="index.php#testimonials" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left"></i> Back to Home
                </a>
            </div>
        </div>
    </section>

    <?php include "includes/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 800,
            once: true
        });
    </script>
</body>
</html>