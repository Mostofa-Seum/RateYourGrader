<?php
include '../db/Config.php';

$p_id = isset($_GET['P_id']) ? intval($_GET['P_id']) : 0;

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

$sql_stats = "SELECT COUNT(r.Rv_id) as total_reviews, AVG(r.`Overall Rating`) as avg_overall, AVG(r.`Grading Fairness`) as avg_fairness, AVG(r.`Behavior and Communication`) as avg_behavior, SUM(CASE WHEN r.`Would You Take This Course Again?` = 'Yes' THEN 1 ELSE 0 END) as take_again_count FROM review r INNER JOIN A_Review ar ON r.r_id = ar.R_id WHERE r.P_id = $p_id";
$result_stats = $conn->query($sql_stats);
$stats = $result_stats->fetch_assoc();

$total_reviews = $stats['total_reviews'];
$avg_overall = $total_reviews > 0 ? number_format($stats['avg_overall'], 1) : "N/A";
$avg_fairness = $total_reviews > 0 ? number_format($stats['avg_fairness'], 1) : 0;
$avg_behavior = $total_reviews > 0 ? number_format($stats['avg_behavior'], 1) : 0;
$take_again_percent = ($total_reviews > 0) ? round(($stats['take_again_count'] / $total_reviews) * 100) : 0;

$sql_reviews = "SELECT r.*, c.`Course Name` FROM review r INNER JOIN A_Review ar ON r.r_id = ar.R_id LEFT JOIN courses c ON r.C_id = c.c_id WHERE r.P_id = $p_id ORDER BY r.r_id DESC";
$result_reviews = $conn->query($sql_reviews);

function renderStars($rating) {
    $output = '';
    $fullStars = floor($rating);
    $hasHalf = ($rating - $fullStars) >= 0.5;
    $emptyStars = 5 - $fullStars - ($hasHalf ? 1 : 0);
    $imgDir = '../../Common/MVC/images/';

    for ($i = 0; $i < $fullStars; $i++) { $output .= '<img src="'.$imgDir.'starFull.png" class="star-icon">'; }
    if ($hasHalf) { $output .= '<img src="'.$imgDir.'starHalf.jpg" class="star-icon">'; }
    for ($i = 0; $i < $emptyStars; $i++) { $output .= '<img src="'.$imgDir.'starEmpty.png" class="star-icon">'; }
    return $output;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($prof['Name']) ?> - Rate Your Grader</title>
    <link rel="stylesheet" href="../css/ProfessorProfile.css">
</head>
<body>

<nav class="navbar">
    <div class="container nav-container">
        <div class="logo-wrapper">
            <div class="logo-icon"><img src="../images/scolar_cap.png" alt="Logo" class="logo-img"></div>
            <span class="logo-text">Rate Your Grader</span>
        </div>
        <div class="nav-links">
            <a href="../../../Common/MVC/php/HomePage.php">Home</a> <a href="SearchOutput.php">Search Graders</a>
            <?php if (isset($_SESSION['user_name'])): ?>
                <a href="UserDashboard.php" style="text-decoration: none;">
                    <span style="margin-right: 15px; font-weight: bold; color: inherit;">Hello, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                </a>
                <a href="../../Common/MVC/php/Logout.php" class="btn btn-primary" style="background-color: #dc3545; color: white;">Logout</a>
            <?php else: ?>
                <a href="../../Common/MVC/php/Login.php" class="btn btn-primary" style="color: white;">Sign Up Free</a>
            <?php endif; ?>
        </div>
    </div>
</nav>
<div style="margin-top: 80px;"></div>

<header class="prof-header">
    <div class="container">
        <a href="SearchOutput.php" class="back-link">
            <img src="../../Common/MVC/images/iconArrow.png" style="transform: rotate(180deg); width: 1em;"> Back to Search
        </a>
        <div class="prof-summary-card">
            <div class="prof-bio">
                <div class="prof-avatar-large"><img src="../../Common/MVC/images/iconUser.png" alt="Professor"></div>
                <div class="prof-details">
                    <h1><?= htmlspecialchars($prof['Name']) ?></h1>
                    <p class="dept-text"><?= htmlspecialchars($prof['Department']) ?> at <strong><?= htmlspecialchars($prof['University']) ?></strong></p>
                    <div class="action-buttons">
                        <a href="ProfessorReview.php?P_id=<?= $p_id ?>&name=<?= urlencode($prof['Name']) ?>&dept=<?= urlencode($prof['Department']) ?>&uni=<?= urlencode($prof['University']) ?>" class="btn btn-primary">Rate This Professor</a>
                    </div>
                </div>
            </div>
            <div class="prof-stats-box">
                <div class="overall-score-box">
                    <span class="score-label">Overall Quality</span>
                    <div class="big-score"><?= $avg_overall ?></div>
                    <div class="stars-display"><?= renderStars($stats['avg_overall']) ?></div>
                    <span class="total-reviews"><?= $total_reviews ?> reviews</span>
                </div>
                <div class="sub-ratings">
                    <div class="stat-row"><span class="stat-label">Would Take Again</span><span class="stat-value highlight"><?= $take_again_percent ?>%</span></div>
                    <div class="stat-row"><span class="stat-label">Fairness</span><span class="stat-value"><?= $avg_fairness ?> / 5</span></div>
                    <div class="stat-row"><span class="stat-label">Communication</span><span class="stat-value"><?= $avg_behavior ?> / 5</span></div>
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
                            <span class="tag <?= $row['Would You Take This Course Again?'] == 'Yes' ? 'tag-green' : ($row['Would You Take This Course Again?'] == 'Maybe' ? 'tag-yellow' : 'tag-red') ?>">Take again: <?= htmlspecialchars($row['Would You Take This Course Again?']) ?></span>
                            <span class="tag tag-gray">Fairness: <?= $row['Grading Fairness'] ?>/5</span>
                            <span class="tag tag-gray">Comm: <?= $row['Behavior and Communication'] ?>/5</span>
                        </div>
                        <div class="review-body"><p><?= nl2br(htmlspecialchars($row['Review'])) ?></p></div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="no-reviews" style="text-align: center; padding: 3rem; color: #666; background: white; border-radius: 8px; border: 1px dashed #ccc;">
                <p>No approved reviews yet. Be the first to rate <?= htmlspecialchars($prof['Name']) ?>!</p>
            </div>
        <?php endif; ?>
    </div>
</main>

<footer>
    <div class="container">
        <div class="footer-grid">
            <div class="footer-brand">
                <div class="logo-wrapper mb-2">
                    <div class="logo-icon small"><img src="../../Common/MVC/images/scolar_cap.png" alt="Logo" class="logo-img"></div>
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
                    <a href="#"><img src="../../Common/MVC/images/facebook.png" alt="Facebook" class="social-icon"></a>
                    <a href="#"><img src="../../Common/MVC/images/instagram.png" alt="Instagram" class="social-icon"></a>
                    <a href="#"><img src="../../Common/MVC/images/twitter.png" alt="Twitter" class="social-icon"></a>
                </div>
            </div>
        </div>
        <div class="footer-bottom"><p>&copy; 2024 Rate My Grader. All rights reserved.</p></div>
    </div>
</footer>
</body>
</html>