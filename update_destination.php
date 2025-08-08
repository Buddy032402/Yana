<?php
session_start();
if (!isset($_SESSION["admin"])) {
    header("Location: login.php");
    exit;
}

include "../db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $destination_id = $_POST['destination_id'];
    $name = trim($_POST['name']);
    $country = trim($_POST['country']);
    $description = trim($_POST['description']);
    $featured = isset($_POST['featured']) ? 1 : 0;
    $status = isset($_POST['status']) ? $_POST['status'] : 1;
    $best_time = trim($_POST['best_time'] ?? '');
    $travel_requirements = trim($_POST['travel_requirements'] ?? '');
    $transportation = trim($_POST['transportation'] ?? '');

    // Get existing gallery images
    $stmt = $conn->prepare("SELECT gallery_images FROM destinations WHERE id = ?");
    $stmt->bind_param("i", $destination_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $existing_gallery = $result->fetch_assoc()['gallery_images'];
    $gallery_images = !empty($existing_gallery) ? json_decode($existing_gallery, true) : [];
    
    // Handle gallery images upload
    if (!empty($_FILES['gallery_images']['name'][0])) {
        $gallery_dir = "../uploads/destinations/gallery/";
        
        // Create directory if it doesn't exist
        if (!file_exists($gallery_dir)) {
            mkdir($gallery_dir, 0777, true);
        }
        
        foreach ($_FILES['gallery_images']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['gallery_images']['error'][$key] === 0) {
                $gallery_ext = strtolower(pathinfo($_FILES['gallery_images']['name'][$key], PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                
                if (in_array($gallery_ext, $allowed)) {
                    $gallery_filename = uniqid() . '_' . pathinfo($_FILES['gallery_images']['name'][$key], PATHINFO_FILENAME) . '.' . $gallery_ext;
                    if (move_uploaded_file($tmp_name, $gallery_dir . $gallery_filename)) {
                        $gallery_images[] = $gallery_filename;
                    }
                }
            }
        }
    }
    
    $gallery_images_json = json_encode($gallery_images);

    // Handle image upload if new image is provided
    if (!empty($_FILES['image']['name'])) {
        $target_dir = "../uploads/destinations/";
        
        // Create directory if it doesn't exist
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $imageFileType = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
        $new_filename = uniqid() . '.' . $imageFileType;
        $target_file = $target_dir . $new_filename;

        // Check if image file is valid
        $uploadOk = 1;
        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if ($check === false) {
            $_SESSION['error'] = "❌ File is not an image.";
            $uploadOk = 0;
        }

        // Allow certain file formats
        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
            $_SESSION['error'] = "❌ Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            $uploadOk = 0;
        }

        if ($uploadOk && move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            // Delete old image
            $stmt = $conn->prepare("SELECT image FROM destinations WHERE id = ?");
            $stmt->bind_param("i", $destination_id);
            $stmt->execute();
            $old_image = $stmt->get_result()->fetch_assoc()['image'];
            if ($old_image && file_exists($target_dir . $old_image)) {
                unlink($target_dir . $old_image);
            }

            // Update with new image
            // Add this to your POST handling
            $destination_type = $_POST['destination_type'];
            
            // Update your SQL query to include destination_type
            $stmt = $conn->prepare("UPDATE destinations SET 
                name = ?, 
                country = ?, 
                description = ?, 
                destination_type = ?,
                featured = ?, 
                status = ?, 
                best_time_to_visit = ?,
                travel_requirements = ?,
                transportation_details = ?,
                updated_at = CURRENT_TIMESTAMP 
                WHERE id = ?");
            $stmt->bind_param("sssssssssi", 
                $name, 
                $country, 
                $description, 
                $destination_type,
                $featured, 
                $status, 
                $best_time,
                $travel_requirements,
                $transportation,
                $destination_id
            );
        } else {
            if (!isset($_SESSION['error'])) {
                $_SESSION['error'] = "❌ Error uploading image.";
            }
            header("Location: edit_destinations.php?id=" . $destination_id);
            exit;
        }
    } else {
        // Update without changing image
        $stmt = $conn->prepare("UPDATE destinations SET 
            name = ?, 
            country = ?, 
            description = ?, 
            gallery_images = ?,
            featured = ?,
            status = ?,
            best_time_to_visit = ?,
            travel_requirements = ?,
            transportation_details = ?
            WHERE id = ?");
        $stmt->bind_param("ssssiiissi", 
            $name, 
            $country, 
            $description,
            $gallery_images_json,
            $featured,
            $status,
            $best_time,
            $travel_requirements,
            $transportation,
            $destination_id
        );
    }

    if ($stmt->execute()) {
        $_SESSION['message'] = "✅ Destination updated successfully";
        $_SESSION['auto_dismiss'] = true; // Add this flag to indicate auto-dismiss
        header("Location: manage_destinations.php?highlight=" . $destination_id);
        exit;
    } else {
        $_SESSION['error'] = "❌ Error updating destination: " . $stmt->error;
        header("Location: edit_destinations.php?id=" . $destination_id);
        exit;
    }
} else {
    header("Location: manage_destinations.php");
    exit;
}
?>