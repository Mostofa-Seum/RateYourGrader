<?php
session_start();
include '../db/Config.php';

// --- HANDLE AJAX REQUESTS ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    if (!isset($_SESSION['s_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        exit;
    }

    $action = $_POST['action'] ?? '';

    // FETCH ANALYTICS FOR A SPECIFIC UNIVERSITY
    if ($action === 'get_uni_stats') {
        $uni_name = $_POST['uni_name'];

        // 1. Count Faculty (From professors table based on Uni Name)
        $sql_count = "SELECT COUNT(*) as count FROM professors WHERE University = ?";
        $stmt = $conn->prepare($sql_count);
        $stmt->bind_param("s", $uni_name);
        $stmt->execute();
        $count_res = $stmt->get_result()->fetch_assoc();
        $faculty_count = $count_res['count'];
        $stmt->close();

        // 2. Calculate Average Rating
        // Logic: Join Review (Ratings) + A_Review (Accepted) + Professors (Link to Uni)
        // We ensure data comes from 'accepted review table' by joining a_review
        $sql_rating = "SELECT AVG(r.`Overall Rating`) as avg_rating 
                       FROM review r
                       JOIN a_review ar ON r.r_id = ar.r_id
                       JOIN professors p ON r.P_id = p.P_id
                       WHERE p.University = ?";
        
        $stmt = $conn->prepare($sql_rating);
        $stmt->bind_param("s", $uni_name);
        $stmt->execute();
        $rating_res = $stmt->get_result()->fetch_assoc();
        
        // Handle case where there are no reviews yet
        $avg_rating = $rating_res['avg_rating'] !== null ? round($rating_res['avg_rating'], 1) : "N/A";
        
        echo json_encode([
            'status' => 'success', 
            'data' => [
                'name' => $uni_name,
                'faculty_count' => $faculty_count,
                'rating' => $avg_rating
            ]
        ]);
        exit;
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>University Analytics</title>
    <link rel="stylesheet" href="../css/UniversityAnalytics.css">
</head>
<body>

<nav class="navbar">
    <div class="container nav-container">
        <div class="logo-wrapper">
            <div class="logo-icon">
                <img src="../images/scolar_cap.png" alt="Logo" class="logo-img">
            </div>
            <span class="logo-text">Rate Your Grader</span>
        </div>
        
        <div class="nav-links">
            <a href="../../../Common/MVC/php/HomePage.php">Home</a>
            <a href="UniversityRepDashboard.php">Dashboard</a>
            <a href="../../../Common/MVC/php/Logout.php" class="btn btn-primary" style="background-color: #dc3545; color: white;">Logout</a>
        </div>
        
        <button class="mobile-menu-btn">
            <img src="../images/menu.png" alt="Menu" class="mobile-menu-icon">
        </button>
    </div>
</nav>

<main class="page-container">
    <div class="container">
        <div class="header-flex">
            <div>
                <a href="UniversityRepDashboard.php" class="back-link">← Back to Dashboard</a>
                <h2 class="page-title">University Analytics</h2>
                <p style="color: #666; margin-top: 5px;">View performance metrics for registered universities.</p>
            </div>
        </div>

        <div class="uni-grid">
            <?php
            // Fetch Distinct Universities from the 'university' table as requested
            $sql = "SELECT DISTINCT uni_name FROM university ORDER BY uni_name ASC";
            $result = $conn->query($sql);

            if ($result && $result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $uName = htmlspecialchars($row['uni_name']);
                    // Generate a random gradient for visual appeal based on name length
                    $len = strlen($uName);
                    $hue = ($len * 25) % 360; 
                    
                    echo '
                    <div class="uni-card" onclick="openAnalyticsModal(\''.$uName.'\')">
                        <div class="card-icon" style="background: hsl('.$hue.', 60%, 90%); color: hsl('.$hue.', 60%, 40%);">
                            🏛️
                        </div>
                        <h3 class="card-name">'.$uName.'</h3>
                        <div class="card-footer">
                            <span>View Analytics ➔</span>
                        </div>
                    </div>';
                }
            } else {
                echo '<p class="empty-msg">No universities found in the database.</p>';
            }
            ?>
        </div>
    </div>
</main>

<div class="modal" id="analyticsModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modal-uni-name">University Name</h2>
            <button class="close-modal" onclick="closeAnalyticsModal()">×</button>
        </div>
        
        <div id="loading-spinner" style="text-align: center; padding: 2rem;">
            Loading data...
        </div>

        <div id="analytics-data" style="display: none;">
            <div class="stats-row">
                <div class="stat-box">
                    <div class="stat-icon-large">👨‍🏫</div>
                    <div class="stat-value" id="modal-faculty-count">0</div>
                    <div class="stat-label">Total Faculty</div>
                </div>

                <div class="stat-box">
                    <div class="stat-icon-large">⭐</div>
                    <div class="stat-value" id="modal-rating">0.0</div>
                    <div class="stat-label">Avg. Rating</div>
                </div>
            </div>
            
            <div class="rating-bar-container">
                <p style="margin-bottom: 5px; font-weight: 600; color: #555;">Performance Score</p>
                <div class="progress-bar">
                    <div class="progress-fill" id="rating-progress" style="width: 0%"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<footer>
    <div class="container">
        <div class="footer-grid">
            <div class="footer-brand">
                <div class="logo-wrapper mb-2">
                    <div class="logo-icon small">
                        <img src="../images/scolar_cap.png" alt="Logo" class="logo-img">
                    </div>
                    <span class="footer-logo-text">Rate My Grader</span>
                </div>
                <p>Empowering students with transparent grading information since 2024.</p>
            </div>

            <div class="footer-actions">
                <h5>Apply</h5>
                <div class="footer-buttons">
                    <a href="#" class="footer-nav-link">Apply for Reviewer</a>
                    <a href="#" class="footer-nav-link">Apply for University Representative</a>
                </div>
            </div>

            <div class="footer-socials">
                <h5>Our Socials</h5>
                <div class="social-icons">
                    <a href="#" aria-label="Facebook">
                        <img src="../images/facebook.png" alt="Facebook" class="social-icon">
                    </a>
                    <a href="#" aria-label="Instagram">
                        <img src="../images/instagram.png" alt="Instagram" class="social-icon">
                    </a>
                    <a href="#" aria-label="Twitter">
                        <img src="../images/twitter.png" alt="Twitter" class="social-icon">
                    </a>
                </div>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; 2024 Rate My Grader. All rights reserved.</p>
        </div>
    </div>
</footer>

<script src="../js/UniversityAnalytics.js"></script>
</body>
</html>