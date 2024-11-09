<?php
session_start();

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize inputs
    $username = htmlspecialchars($_POST["username"]);
    $room = htmlspecialchars($_POST["room"]);
    $start_time = $_POST["start_time"]; // You might need further validation
    $duration = intval($_POST["duration"]); // Ensure duration is an integer
    $user_id = $_SESSION["ID"]; // Get user ID from the session

    // Example validation: Ensure start time is in the future (if required)
    if (strtotime($start_time) < time()) {
        // Handle invalid start time
        echo "Invalid start time. Please select a future time.";
        exit;
    }

    // Calculate end time based on duration
    $end_time = date('H:i:s', strtotime($start_time) + $duration * 3600);

    // Insert booking into the database
    require('config.php');
    $stmt = $conn->prepare("INSERT INTO book (user_id, room_code, booking_date, start_time, end_time) VALUES (?, ?, ?, ?, ?)");
    if ($stmt) {
        $booking_date = date('Y-m-d', strtotime($start_time));
        $start_time_formatted = date('H:i:s', strtotime($start_time));

        $stmt->bind_param("sssss", $user_id, $room, $booking_date, $start_time_formatted, $end_time);

        if ($stmt->execute()) {
            // Redirect to a confirmation page upon successful booking
            header("Location: booking_confirmation.php");
            exit;
        } else {
            echo "Error: " . $stmt->error;
        }

        // Close the statement
        $stmt->close();
    } else {
        echo "Failed to prepare the SQL statement.";
    }

    // Close the connection
    $conn->close();
} else {
    // Redirect to booking page if accessed directly without POST data
    header("Location: lectbook.php");
    exit;
}
?>
