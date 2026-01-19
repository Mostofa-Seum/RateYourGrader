<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($prof['Name']) ?> - Profile</title>
    <link rel="stylesheet" href="../css/ProfessorProfile.css">
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
                    <a href="UserDashboard.php" style="color: var(--dark-navy); font-weight: 600;">
                        Hello, <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                    </a>
                    <a href="../../../Common/MVC/php/Logout.php" class="btn btn-danger">Logout</a>
                <?php else: ?>
                    <a href="../../../Common/MVC/php/Login.php" class="btn btn-primary">Sign Up Free</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <header class="prof-header">
        <div class="container">
            <a href="SearchOutput.php" class="back-link">
                <img src="../images/iconArrow.png" style="transform: rotate(180deg); width: 1em;"> Back to Search
            </a>
            <div class="prof-summary-card">
                <div class="prof-bio">
                    <div class="prof-avatar-large"><img src="../images/iconUser.png" alt="Professor"></div>
                    <div class="prof-details">
                        <h1><?= htmlspecialchars($prof['Name']) ?></h1>
                        <p class="dept-text"><?= htmlspecialchars($prof['Department']) ?> at <strong><?= htmlspecialchars($prof['University']) ?></strong></p>
                        
                        <div id="loginWarning" class="login-warning">
                            You Must <a href="../../../Common/MVC/php/Login.php" style="color: inherit; text-decoration: underline; font-weight: bold;">Login / Sign Up</a> to rate a professor.
                        </div>

                        <div class="action-buttons">
                            <a href="<?= $rateLink ?>" <?= $btnID ?> class="btn btn-primary">Rate This Professor</a>
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
                        <a href="javascript:void(0)" onclick="checkLoginAndApply()" class="footer-nav-link">Apply for Reviewer</a>
                        <a href="javascript:void(0)" onclick="checkLoginAndApply()" class="footer-nav-link">Apply for University Representative</a>
                    </div>
                </div>

                <div class="footer-socials">
                    <h5>Our Socials</h5>
                    <div class="social-icons">
                        <a href="https://www.facebook.com"><img src="../images/facebook.png" alt="Facebook" class="social-icon"></a>
                        <a href="https://www.instagram.com"><img src="../images/instagram.png" alt="Instagram" class="social-icon"></a>
                        <a href="https://www.twitter.com"><img src="../images/twitter.png" alt="Twitter" class="social-icon"></a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2026 Rate Your Grader. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <div id="toast-container"></div>

    <script>
        const isUserLoggedIn = <?php echo isset($_SESSION['user_name']) ? 'true' : 'false'; ?>;
    </script>
    <script src="../js/ProfessorProfile.js"></script>

</body>
</html>