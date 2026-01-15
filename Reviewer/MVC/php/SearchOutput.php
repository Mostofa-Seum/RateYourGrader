<?php
session_start(); 
include '../../../Common/MVC/db/Config.php';

// --- CONFIGURATION: DEFINE ROLE ---
// Check if the user is a reviewer based on session data
$user_role = isset($_SESSION['role']) ? strtolower($_SESSION['role']) : ''; 
$is_reviewer = ($user_role === 'reviewer'); 
// ----------------------------------

$iconUser  = '<img src="../images/iconUser.png" alt="User" style="width: 4rem; height: 4rem; object-fit: contain;">';
$starFull  = '<img src="../images/starFull.png" alt="Star" style="width: 1.2em; height: 1.2em; vertical-align: middle;">';
$starHalf  = '<img src="../images/starHalf.jpg" alt="Half Star" style="width: 1.2em; height: 1.2em; vertical-align: middle;">';
$starEmpty = '<img src="../images/zeroStar.png" alt="Empty Star" style="width: 1.2em; height: 1.2em; vertical-align: middle;">';
$iconArrow = '<img src="../images/iconArrow.png" alt="Arrow" style="width: 1em; height: 1em; vertical-align: middle;">';

// SEARCH LOGIC
$search_term = "";
$search_performed = false;
$result = null;
$count = 0;

if (isset($_GET['q'])) {
    $search_performed = true;
    $search_term = $_GET['q'];
    $safe_search = $conn->real_escape_string($search_term);
    $sql = "SELECT * FROM professors WHERE Name LIKE '%$safe_search%' OR Department LIKE '%$safe_search%' OR University LIKE '%$safe_search%'";
    $result = $conn->query($sql);
    if (!$result) { die("Query Failed: " . $conn->error); }
    $count = $result->num_rows;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/SearchOutput.css">
    <title>Find Your Grader</title>
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
                <a href="SearchOutput.php">Search Graders</a>

                <?php if (isset($_SESSION['user_name'])): ?>
                    <a href="ReviewerDashboard.php" style="text-decoration: none;">
                        <span style="margin-right: 15px; font-weight: bold; color: inherit;">Hello, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                    </a>
                    <a href="../../../Common/MVC/php/Logout.php" class="btn btn-primary" style="background-color: #dc3545; color: white;">Logout</a>
                <?php else: ?>
                    <a href="../../../Common/MVC/php/Login.php" class="btn btn-primary" style="color: white;">Sign Up Free</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    <div style="margin-top: 80px;"></div>

    <section class="search-section">
        <div class="search-container">
            <div class="text-center" style="margin-bottom: 2rem;">
                <h2 class="search-title">Find Your Grader</h2>
                <p>Search for graders and read reviews from fellow students</p>
            </div>
            
            <form action="" method="GET" class="search-wrapper" onsubmit="return validateSearch()">
                <input type="text" id="searchBox" name="q" value="<?php echo htmlspecialchars($search_term); ?>" 
                placeholder="Enter grader's name, dept, or university" class="search-input" required>
                <button type="submit" class="search-btn">
                    <img src="../images/SearchIcon.png" alt="Search Icon">
                </button>
            </form>
        </div>
    </section>

    <?php if ($search_performed): ?>
    <main class="results-section">
        <div class="container">
            
            <div id="login-warning-msg">
                ⚠️ You must <a href="../../../Common/MVC/php/Login.php">Login / Sign Up</a> to rate a professor.
            </div>

            <div id="reviewer-warning-msg">
                🚫 Sorry! Reviewers cannot give reviews.
            </div>

            <div style="margin-bottom: 1.5rem;">
                <h3 style="font-size: 1.5rem; font-weight: 700;">Search Results</h3>
                <p>Found <span id="resultCount"><?php echo $count; ?></span> graders matching your search</p>
            </div>

            <div id="graderResults" class="results-grid">
                <?php
                if ($count > 0) {
                    while($row = $result->fetch_assoc()) {
                        $current_p_id = $row['P_id'];
                        $stat_sql = "SELECT COUNT(r.r_id) as total_reviews, AVG(r.`Overall Rating`) as avg_overall,
                         AVG(r.`Grading Fairness`) as avg_fairness, AVG(r.`Behavior and Communication`) as avg_behavior 
                         FROM review r INNER JOIN A_Review ar ON r.r_id = ar.R_id WHERE r.P_id = '$current_p_id'";
                        $stat_result = $conn->query($stat_sql);
                        $stats = $stat_result->fetch_assoc();

                        $total_reviews = $stats['total_reviews'];
                        $overall_rating = $total_reviews > 0 ? number_format($stats['avg_overall'], 1) : "0.0";
                        $fairness_score = $total_reviews > 0 ? number_format($stats['avg_fairness'], 1) : "0.0";
                        $clarity_score  = $total_reviews > 0 ? number_format($stats['avg_behavior'], 1) : "0.0";
                        $fairness_width = ($fairness_score / 5) * 100;
                        $clarity_width  = ($clarity_score / 5) * 100;
                ?>
                
                <div class="card">
                    <div class="card-padding">
                        <div class="flex items-center" style="margin-bottom: 1rem;">
                            <div class="grader-avatar"><?= $iconUser ?></div>
                            <div class="grader-info">
                                <h4><?php echo htmlspecialchars($row['Name']); ?></h4>
                                <p style="font-size: 0.9rem; color: #666;">
                                    <?php echo htmlspecialchars($row['Department']); ?> <br>
                                    <span style="font-size: 0.8rem; color: #888;">at <?php echo htmlspecialchars($row['University']); ?></span>
                                </p>
                            </div>
                        </div>
                        
                        <div style="margin-bottom: 1rem;">
                            <div class="rating-header">
                                <span style="font-weight: 600;">Overall Rating</span>
                                <span class="rating-score"><?php echo $overall_rating; ?></span>
                            </div>
                            <div class="stars-row">
                                <?= $starFull . $starFull . $starFull . $starFull . $starHalf ?>
                                <span class="review-count">(<?php echo $total_reviews; ?> reviews)</span>
                            </div>
                            
                            <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                                <div class="rating-row">
                                    <span class="rating-label">Fairness</span>
                                    <div class="rating-bar-bg"><div class="rating-bar-fill" style="width: <?php echo $fairness_width; ?>%"></div></div>
                                    <span class="rating-val"><?php echo $fairness_score; ?></span>
                                </div>
                                <div class="rating-row">
                                    <span class="rating-label">Clarity</span>
                                    <div class="rating-bar-bg"><div class="rating-bar-fill" style="width: <?php echo $clarity_width; ?>%"></div></div>
                                    <span class="rating-val"><?php echo $clarity_score; ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card-footer">
                            <a href="ProfessorProfile.php?P_id=<?= $row['P_id'] ?>" class="view-profile-link" style="text-decoration: none;">
                                View Profile &nbsp; <?= $iconArrow ?>
                            </a>
                            
                            <?php if (isset($_SESSION['user_name'])): ?>
                                <?php if ($is_reviewer): ?>
                                    <a href="javascript:void(0);" 
                                       onclick="showReviewerMessage()"
                                       class="rate-profile-btn" style="text-decoration: none; opacity: 0.7;">
                                        Rate Now &nbsp; <?= $iconArrow ?>
                                    </a>
                                <?php else: ?>
                                    <a href="ProfessorReview.php?P_id=<?= $row['P_id'] ?>&name=<?= urlencode($row['Name']) ?>&dept=<?= urlencode($row['Department']) ?>&uni=<?= urlencode($row['University']) ?>" 
                                       class="rate-profile-btn" style="text-decoration: none;">
                                        Rate Now &nbsp; <?= $iconArrow ?>
                                    </a>
                                <?php endif; ?>
                            <?php else: ?>
                                <a href="javascript:void(0);" 
                                   onclick="showLoginMessage()"
                                   class="rate-profile-btn" style="text-decoration: none;">
                                    Rate Now &nbsp; <?= $iconArrow ?>
                                </a>
                            <?php endif; ?>
                        </div>

                    </div>
                </div>
                <?php 
                    } 
                } else {
                    echo "<p>No results found for '" . htmlspecialchars($search_term) . "'</p>";
                }
                ?>
            </div>
        </div>
    </main>
    <?php endif; ?>

     <footer>
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <div class="logo-wrapper mb-2">
                        <div class="logo-icon small">
                            <img src="../images/scolar_cap.png" alt="Logo" class="logo-img">
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
                        <a href="https://www.facebook.com" aria-label="Facebook">
                            <img src="../images/facebook.png" alt="Facebook" class="social-icon">
                        </a>
                        <a href="https://www.instagram.com" aria-label="Instagram">
                            <img src="../images/instagram.png" alt="Instagram" class="social-icon">
                        </a>
                        <a href="https://www.twitter.com" aria-label="Twitter">
                            <img src="../images/twitter.png" alt="Twitter" class="social-icon">
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2026 Rate My Grader. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="../js/SearchOutput.js"></script>
</body>
</html>