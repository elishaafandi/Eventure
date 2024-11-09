<?php
session_start();
require("config.php");

// Ensure the user is logged in
if (!isset($_SESSION['id'])) {
    echo "You must be logged in to access this page.";
    exit;
}

// Get the user ID from the session
$user_id = $_SESSION['id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve and sanitize form inputs
    $club_name = mysqli_real_escape_string($conn, $_POST['club_name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $founded_date = mysqli_real_escape_string($conn, $_POST['founded_date']);
    $club_type = mysqli_real_escape_string($conn, $_POST['club_type']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    
    // Handle file upload for "proof"
    $proof = $_FILES['proof']['name'];
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($proof);

    // Ensure the upload directory exists
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    if (move_uploaded_file($_FILES["proof"]["tmp_name"], $target_file)) {
        // Prepare and execute the SQL statement to insert data into the Club table
        $sql = "INSERT INTO clubs (club_name, description, founded_date, club_type, president_id) 
        VALUES ('$club_name', '$description', '$founded_date', '$club_type', {$_SESSION['id']})";


        if (mysqli_query($conn, $sql)) {
            echo "<script>alert('Club details added successfully!'); window.location.href='organizerhome.php';</script>";
        } else {
            echo "<script>alert('Error: Could not add club details. Please try again.');</script>";
        }
    } else {
        echo "<script>alert('Failed to upload proof file. Please check the file and try again.');</script>";
    }

    // Close the database connection
    mysqli_close($conn);
}
?>
