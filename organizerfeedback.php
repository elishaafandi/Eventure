<?php 
session_start(); // Start the session
require("config.php"); // Including the config file

// Check if user is logged in by verifying the session ID
if (!isset($_SESSION["id"])) {
    // Redirect to homepage if ID is missing
    header("Location: organizerhome.php");
    exit; 
}

// Ensure the club ID is stored in the session
if (isset($_GET['club_id'])) {
    $_SESSION['SELECTEDID'] = $_GET['club_id'];  // Store selected club ID in session
} 

// Initialize club ID from session
$selected_club_id = isset($_SESSION["SELECTEDID"]) ? $_SESSION["SELECTEDID"] : '';

// Get the selected club ID from the session for event creation
$club_id = $_SESSION['SELECTEDID'] ?? 0; // Use the club_id from session

// Corrected query to select events based on the new schema (using the correct 'events' table)
$sql = "SELECT event_id, event_name, event_type, event_status, start_date, end_date 
        FROM events WHERE club_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $club_id);
$stmt->execute();
$result = $stmt->get_result();

$events = []; // Initialize $events array
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }
}
$stmt->close();

// Function to fetch feedback data
function fetchFeedback() {
    global $conn;
    $sql = "SELECT feedback_text, rating, from_id, created_at 
            FROM feedbackcrew
            ORDER BY created_at DESC";

    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<div class='feedback-item'>";
            echo "<p><strong>" . htmlspecialchars($row['from_id']) . "</strong> - Rating: " . $row['rating'] . "/5</p>";
            echo "<p>" . htmlspecialchars($row['feedback_text']) . "</p>";
            echo "<p><em>Submitted on: " . htmlspecialchars($row['created_at']) . "</em></p>";
            echo "</div>";
        }
    } else {
        echo "<p>No feedback available at the moment.</p>";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eventure Organizer Site</title>
    <link rel="stylesheet" href="organizerhome.css">
    <link rel="stylesheet" href="organizerfeedback.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <header>
        <div class="header-left">
            <div class="nav-right">
                <a href="participanthome.php" class="participant-site">PARTICIPANT SITE</a>
                <a href="organizerhome.php" class="organizer-site">ORGANIZER SITE</a> 
                <span class="notification-bell">🔔</span>
                <a href="profilepage.html" class="profile-icon"><i class="fas fa-user-circle"></i></a>
            </div>
        </div>
    </header>

    <main>
        <aside class="sidebar">
            <div class="logo-container">
                <a href="organizerhome.php" class="logo">EVENTURE</a>
            </div>
            <ul>
                <li><a href="organizerhome.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'organizerhome.php' ? 'active' : ''; ?>"><i class="fas fa-home-alt"></i> Dashboard</a></li>
                <li><a href="organizerevent.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'organizerevent.php' ? 'active' : ''; ?>"><i class="fas fa-calendar-alt"></i>Event Hosted</a></li>
                <li><a href="organizerparticipant.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'organizerparticipant.php' ? 'active' : ''; ?>"><i class="fas fa-user-friends"></i>Participant Listing</a></li>
                <li><a href="organizercrew.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'organizercrew.php' ? 'active' : ''; ?>"><i class="fas fa-users"></i>Crew Listing</a></li>
                <li><a href="organizerreport.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'organizerreport.php' ? 'active' : ''; ?>"><i class="fas fa-chart-line"></i>Reports</a></li>
            </ul>
            <ul style="margin-top: 60px;">
                <li><a href="organizersettings.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'organizersettings.php' ? 'active' : ''; ?>"><i class="fas fa-cog"></i>Settings</a></li>
                <li><a href="organizerlogout.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'organizerlogout.php' ? 'active' : ''; ?>"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
            <ul style="margin-top: 60px;">
                <li><a href="organizerfeedback.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'organizerfeedback.php' ? 'active' : ''; ?>"><i class="fas fa-star"></i>Feedback</a></li>
            </ul>
        </aside>

        <section class="main-content">
            <h2>Feedback Collection Page</h2>
            <p>View feedback from participants to understand the strengths and weaknesses of your events.</p>

            <!-- Feedback List -->
            <div class="feedback-list">
                <?php fetchFeedback(); ?>
            </div>

            <!-- Crew/Participant Feedback Section -->
            <div class="crew-feedback-section">
                <h3>Provide Feedback for Crew/Participants</h3>
                <form class="feedback-form" method="post" action="organizerfeedback.php">
                    <label for="event">Select Event:</label>
                    <select id="event" name="event_id" required>
                        <option value="">Select an event</option>
                        <?php foreach ($events as $event): ?>
                            <option value="<?php echo $event['event_id']; ?>"><?php echo htmlspecialchars($event['event_name']); ?> (<?php echo htmlspecialchars($event['event_type']); ?>)</option>
                        <?php endforeach; ?>
                    </select>

                    <label for="feedback-to">Name of Crew/Participant:</label>
                    <input type="text" id="feedback-to" name="feedback_to" placeholder="Enter the name of the participant or crew member" required>

                    <label for="feedback">Your Feedback:</label>
                    <textarea id="feedback" name="feedback_text" rows="4" placeholder="Share your experience..." required></textarea>

                    <button type="submit" class="btn-submit-feedback">Submit Feedback</button>
                </form>
            </div>
        </section>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
