<?php
include "db.php";

// Fetch all active destinations with coordinates
$destinations = $conn->query("
    SELECT d.*, COUNT(p.id) as package_count,
           AVG(r.rating) as avg_rating
    FROM destinations d
    LEFT JOIN packages p ON d.id = p.destination_id
    LEFT JOIN package_reviews r ON p.id = r.package_id
    WHERE d.status = 1
    GROUP BY d.id
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Destination Map - Yana Biyahi Na Travel and Tours</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        #map {
            height: 600px;
            width: 100%;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .destination-info {
            max-width: 300px;
        }
        .destination-info img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 5px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <?php include "includes/navbar.php"; ?>

    <div class="container my-5">
        <h2 class="mb-4">Explore Our Destinations</h2>
        <div class="row">
            <div class="col-md-12">
                <div id="map"></div>
            </div>
        </div>
    </div>

    <script>
        let map;
        let markers = [];
        let infoWindows = [];

        function initMap() {
            map = new google.maps.Map(document.getElementById('map'), {
                zoom: 2,
                center: {lat: 20, lng: 0},
                styles: [
                    {
                        featureType: "water",
                        elementType: "geometry",
                        stylers: [{color: "#e9e9e9"}, {lightness: 17}]
                    }
                ]
            });

            // Add destinations to map
            <?php while($dest = $destinations->fetch_assoc()): ?>
            addDestinationMarker({
                id: <?php echo $dest['id']; ?>,
                name: "<?php echo addslashes($dest['name']); ?>",
                lat: <?php echo $dest['latitude']; ?>,
                lng: <?php echo $dest['longitude']; ?>,
                image: "<?php echo $dest['image']; ?>",
                rating: <?php echo number_format($dest['avg_rating'], 1); ?>,
                packages: <?php echo $dest['package_count']; ?>
            });
            <?php endwhile; ?>

            // Cluster markers that are close together
            new MarkerClusterer(map, markers, {
                imagePath: 'https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m'
            });
        }

        function addDestinationMarker(destination) {
            const marker = new google.maps.Marker({
                position: {lat: destination.lat, lng: destination.lng},
                map: map,
                title: destination.name,
                animation: google.maps.Animation.DROP
            });

            const infoContent = `
                <div class="destination-info">
                    <img src="uploads/destinations/${destination.image}" alt="${destination.name}">
                    <h5>${destination.name}</h5>
                    <p>
                        <span class="text-warning">
                            ${'★'.repeat(Math.round(destination.rating))}${'☆'.repeat(5-Math.round(destination.rating))}
                        </span>
                        ${destination.rating}/5
                    </p>
                    <p>${destination.packages} packages available</p>
                    <a href="destination.php?id=${destination.id}" class="btn btn-primary btn-sm">View Details</a>
                </div>
            `;

            const infoWindow = new google.maps.InfoWindow({
                content: infoContent
            });

            marker.addListener('click', () => {
                infoWindows.forEach(iw => iw.close());
                infoWindow.open(map, marker);
            });

            markers.push(marker);
            infoWindows.push(infoWindow);
        }
    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_GOOGLE_MAPS_API_KEY&callback=initMap" async defer></script>
    <?php include "includes/footer.php"; ?>
</body>
</html>