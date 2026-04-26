<?php
require_once 'DB_Ops.php';
require_once 'auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['movie_title'] ?? '';
    $userId = getCurrentUserId();

    if (!$userId) {
        die("User not logged in.");
    }

    // Upload settings
    $targetDir = "uploads/";
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $fileName = basename($_FILES["movie_image"]["name"]);
    $targetFile = $targetDir . time() . "_" . $fileName; // Add timestamp to prevent duplicate names

    if (move_uploaded_file($_FILES["movie_image"]["tmp_name"], $targetFile)) {
        // Upload to database
        $conn = getConnection();
        $stmt = $conn->prepare("INSERT INTO watchlist (title, image, id_user) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $title, $targetFile, $userId);
        
        if ($stmt->execute()) {
            // Success: Go to the home page immediately
            header("Location: index.php");
            exit; 
        } else {
            echo "Database error: " . $stmt->error;
        }
    } else {
        echo "Failed to upload image file.";
    }
}