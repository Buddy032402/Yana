<?php
// Fetch top client picks with destination details
$top_picks_query = $conn->query("
    SELECT tcp.*, d.name as destination_name, d.image as destination_image, 
           d.description as destination_description, d.country
    FROM top_client_picks tcp
    JOIN destinations d ON tcp.destination_id = d.id
    WHERE tcp.status = 1
    ORDER BY tcp.featured_order
    LIMIT 6
");
?>

<section class="top-picks section-padding bg-light">
    <div class="container">
        <h2 class="section-title text-center">Top Client Picks</h2>
        <div class="row">
            <?php while($pick = $top_picks_query->fetch_assoc()): ?>
                <div class="col-md-4 mb-4">
                    <div class="destination-card">
                        <img src="uploads/destinations/<?php echo htmlspecialchars($pick['destination_image']); ?>" 
                             class="card-img-top" alt="<?php echo htmlspecialchars($pick['destination_name']); ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($pick['destination_name']); ?></h5>
                            <p class="card-subtitle mb-2 text-muted"><?php echo htmlspecialchars($pick['country']); ?></p>
                            <p class="card-text"><?php echo htmlspecialchars($pick['description']); ?></p>
                            <a href="destination.php?id=<?php echo $pick['destination_id']; ?>" class="btn btn-primary">
                                Explore More
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>