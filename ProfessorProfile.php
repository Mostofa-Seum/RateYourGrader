<?php
include 'Config.php';

$p_id = isset($_GET['P_id']) ? ($_GET['P_id']) : 0;

if ($p_id == 0) {
    echo "Invalid Professor ID.";
    exit;
}


$sql_prof = "SELECT * FROM professors WHERE P_id = $p_id";
$result_prof = $conn->query($sql_prof);

if ($result_prof->num_rows > 0) {
    $prof = $result_prof->fetch_assoc();
} else {
    echo "Professor not found.";
    exit;
}


$sql_stats = "SELECT 
    COUNT(*) as total_reviews,
    AVG(`Overall Rating`) as avg_overall,
    AVG(`Grading Fairness`) as avg_fairness,
    AVG(`Behavior and Communication`) as avg_behavior,
    SUM(CASE WHEN `Would You Take This Course Again?` = 'Yes' THEN 1 ELSE 0 END) as take_again_count
    FROM review 
    WHERE P_id = $p_id";

$result_stats = $conn->query($sql_stats);
$stats = $result_stats->fetch_assoc();

// Handle edge case (No reviews yet)
$total_reviews = $stats['total_reviews'];
$avg_overall = $total_reviews > 0 ? number_format($stats['avg_overall'], 1) : "N/A";
$avg_fairness = $total_reviews > 0 ? number_format($stats['avg_fairness'], 1) : 0;
$avg_behavior = $total_reviews > 0 ? number_format($stats['avg_behavior'], 1) : 0;

// Calculate Take Again Percentage
$take_again_percent = 0;
if ($total_reviews > 0) {
    $take_again_percent = round(($stats['take_again_count'] / $total_reviews) * 100);
}

// 4. FETCH INDIVIDUAL REVIEWS (Joined with Courses table)
$sql_reviews = "SELECT r.*, c.`Course Name` 
                FROM review r 
                LEFT JOIN courses c ON r.C_id = c.c_id 
                WHERE r.P_id = $p_id 
                ORDER BY r.r_id DESC"; // Newest first
$result_reviews = $conn->query($sql_reviews);

// --- HELPER FUNCTION FOR STARS ---
function renderStars($rating) {
    $output = '';
    $fullStars = floor($rating);
    $hasHalf = ($rating - $fullStars) >= 0.5;
    $emptyStars = 5 - $fullStars - ($hasHalf ? 1 : 0);

    for ($i = 0; $i < $fullStars; $i++) {
        $output .= '<img src="Images/starFull.png" class="star-icon">';
    }
    if ($hasHalf) {
        $output .= '<img src="Images/starHalf.jpg" class="star-icon">';
    }
    for ($i = 0; $i < $emptyStars; $i++) {
        $output .= '<img src="Images/starEmpty.png" class="star-icon">';
    }
    return $output;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($prof['Name']) ?> - Rate My Grader</title>
    <link rel="stylesheet" href="ProfessorProfile.css">
</head>
<body>

<nav class="navbar">
    <div class="container nav-container">
        <div class="logo-wrapper">
            <div class="logo-icon">
                <img src="Figures/scolar_cap.png" alt="Logo" class="logo-img">
            </div>
            <span class="logo-text">Rate Your Grader</span>
        </div>
        
        <div class="nav-links">
            <a href="#how-it-works">How it works</a>
            <a href="#features">Features</a>
            <a href="#search">Search Graders</a>
            <button class="btn btn-primary">Sign Up Free</button>
        </div>
    </div>
</nav>
    <div style="margin-top: 80px;"></div>

    <header class="prof-header">
        <div class="container">
            <a href="SearchOutput.php" class="back-link">
                <img src="Images/iconArrow.png" style="transform: rotate(180deg); width: 1em;"> 
                Back to Search
            </a>

            <div class="prof-summary-card">
                <div class="prof-bio">
                    <div class="prof-avatar-large">
                        <img src="Images/iconUser.png" alt="Professor">
                    </div>
                    <div class="prof-details">
                        <h1><?= htmlspecialchars($prof['Name']) ?></h1>
                        <p class="dept-text">
                            <?= htmlspecialchars($prof['Department']) ?> at 
                            <strong><?= htmlspecialchars($prof['University']) ?></strong>
                        </p>
                        
                        <div class="action-buttons">
                            <a href="ProfessorReview.php?P_id=<?= $p_id ?>&name=<?= urlencode($prof['Name']) ?>&dept=<?= urlencode($prof['Department']) ?>&uni=<?= urlencode($prof['University']) ?>" class="btn btn-primary">
                                Rate This Professor
                            </a>
                        </div>
                    </div>
                </div>

                <div class="prof-stats-box">
                    <div class="overall-score-box">
                        <span class="score-label">Overall Quality</span>
                        <div class="big-score"><?= $avg_overall ?></div>
                        <div class="stars-display">
                            <?= renderStars($stats['avg_overall']) ?>
                        </div>
                        <span class="total-reviews"><?= $total_reviews ?> reviews</span>
                    </div>
                    
                    <div class="sub-ratings">
                        <div class="stat-row">
                            <span class="stat-label">Would Take Again</span>
                            <span class="stat-value highlight"><?= $take_again_percent ?>%</span>
                        </div>
                        <div class="stat-row">
                            <span class="stat-label">Fairness</span>
                            <span class="stat-value"><?= $avg_fairness ?> / 5</span>
                        </div>
                        <div class="stat-row">
                            <span class="stat-label">Communication</span>
                            <span class="stat-value"><?= $avg_behavior ?> / 5</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main class="reviews-section">
        <div class="container">
            <h3 class="section-title">Student Reviews</h3>

            <?php if ($result_reviews->num_rows > 0): ?>
                <div class="reviews-grid">
                    <?php while($row = $result_reviews->fetch_assoc()): ?>
                        <div class="review-card">
                            <div class="review-header">
                                <div class="course-info">
                                    <span class="course-badge"><?= htmlspecialchars($row['Course Name']) ?></span>
                                    <span class="date-badge">Difficulty: <?= htmlspecialchars($row['Difficulty Level']) ?></span>
                                </div>
                                <div class="review-rating-display">
                                    <span class="rating-num"><?= $row['Overall Rating'] ?>.0</span>
                                    <?= renderStars($row['Overall Rating']) ?>
                                </div>
                            </div>

                            <div class="review-tags">
                                <span class="tag <?= $row['Would You Take This Course Again?'] == 'Yes' ? 'tag-green' : ($row['Would You Take This Course Again?'] == 'Maybe' ? 'tag-yellow' : 'tag-red') ?>">
                                    Take again: <?= htmlspecialchars($row['Would You Take This Course Again?']) ?>
                                </span>
                                <span class="tag tag-gray">Fairness: <?= $row['Grading Fairness'] ?>/5</span>
                                <span class="tag tag-gray">Comm: <?= $row['Behavior and Communication'] ?>/5</span>
                            </div>

                            <div class="review-body">
                                <p><?= nl2br(htmlspecialchars($row['Review'])) ?></p>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="no-reviews">
                    <p>No reviews yet. Be the first to rate <?= htmlspecialchars($prof['Name']) ?>!</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <div class="logo-wrapper mb-2">
                        <div class="logo-icon small">
                            <img src="Figures/scolar_cap.png" alt="Logo" class="logo-img">
                        </div>
                        <span class="footer-logo-text">Rate Your Grader</span>
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
                            <img src="Figures/facebook.png" alt="Facebook" class="social-icon">
                        </a>
                        <a href="#" aria-label="Instagram">
                            <img src="Figures/instagram.png" alt="Instagram" class="social-icon">
                        </a>
                        <a href="#" aria-label="Twitter">
                            <img src="Figures/twitter.png" alt="Twitter" class="social-icon">
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2024 Rate My Grader. All rights reserved. Made with ❤️ for students everywhere.</p>
            </div>
        </div>
    </footer>
</body>
</html>