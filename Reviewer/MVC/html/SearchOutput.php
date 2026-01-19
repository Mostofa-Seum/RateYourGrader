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
                placeholder="Enter grader's name, dept, or university" class="search-input">
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
                <h3 style="font-size: 1.5rem; font-weight: 700;">
                    <?php echo empty($search_term) ? "Suggested Professors" : "Search Results"; ?>
                </h3>
                
                <?php if (!empty($search_term)): ?>
                    <p>Found <span id="resultCount"><?php echo $count; ?></span> graders matching your search</p>
                <?php endif; ?>
            </div>

            <div id="graderResults" class="results-grid">
                <?php
                if (!empty($professors_data)) {
                    foreach($professors_data as $prof) {
                        $row = $prof['info'];
                        $stats = $prof['stats'];
                        $review_text_display = $prof['latest_review'];
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
                                <span class="rating-score"><?php echo $stats['overall_rating']; ?></span>
                            </div>
                            <div class="stars-row">
                                <?= $starFull . $starFull . $starFull . $starFull . $starHalf ?>
                                <span class="review-count">(<?php echo $stats['total_reviews']; ?> reviews)</span>
                            </div>
                            
                            <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                                <div class="rating-row">
                                    <span class="rating-label">Fairness</span>
                                    <div class="rating-bar-bg"><div class="rating-bar-fill" style="width: <?php echo $stats['fairness_width']; ?>%"></div></div>
                                    <span class="rating-val"><?php echo $stats['fairness_score']; ?></span>
                                </div>
                                <div class="rating-row">
                                    <span class="rating-label">Clarity</span>
                                    <div class="rating-bar-bg"><div class="rating-bar-fill" style="width: <?php echo $stats['clarity_width']; ?>%"></div></div>
                                    <span class="rating-val"><?php echo $stats['clarity_score']; ?></span>
                                </div>
                            </div>
                        </div>

                        <div style="background-color: #f9fafb; padding: 0.75rem; border-radius: 8px; margin-bottom: 1rem; font-style: italic; color: #4b5563; font-size: 0.9rem;">
                            <strong>Latest Review:</strong><br>
                            <?php echo $review_text_display; ?>
                        </div>
                        
                        <div class="card-footer">
                            <a href="ProfessorProfile.php?P_id=<?= $row['P_id'] ?>" class="view-profile-link" style="text-decoration: none;">
                                View Profile &nbsp; <?= $iconArrow ?>
                            </a>
                            
                            <?php if (isset($_SESSION['user_name'])): ?>
                                <?php if ($is_reviewer): ?>
                                    <a href="javascript:void(0);" onclick="showReviewerMessage()" class="rate-profile-btn" style="text-decoration: none; opacity: 0.7;">
                                        Rate Now &nbsp; <?= $iconArrow ?>
                                    </a>
                                <?php else: ?>
                                    <a href="ProfessorReview.php?P_id=<?= $row['P_id'] ?>&name=<?= urlencode($row['Name']) ?>&dept=<?= urlencode($row['Department']) ?>&uni=<?= urlencode($row['University']) ?>" 
                                       class="rate-profile-btn" style="text-decoration: none;">
                                        Rate Now &nbsp; <?= $iconArrow ?>
                                    </a>
                                <?php endif; ?>
                            <?php else: ?>
                                <a href="javascript:void(0);" onclick="showLoginMessage()" class="rate-profile-btn" style="text-decoration: none;">
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
                        <?php 
                        // Reviewer Option
                        if ($is_reviewer) {
                            echo '<span style="color: #9ca3af; font-weight: 500;">You are a Reviewer</span>';
                        } else {
                            echo '<a href="../../../Common/MVC/php/ApplyRole.php?role=Reviewer" class="footer-nav-link">Apply for Reviewer</a>';
                        }

                        // University Rep Option
                        if ($is_rep) {
                            echo '<span style="color: #9ca3af; font-weight: 500;">You are a University Representative</span>';
                        } else {
                            echo '<a href="../../../Common/MVC/php/ApplyRole.php?role=University Representative" class="footer-nav-link">Apply for University Representative</a>';
                        }
                        ?>
                    </div>
                </div>

                <div class="footer-socials">
                    <h5>Our Socials</h5>
                    <div class="social-icons">
                        <a href="https://www.facebook.com">
                            <img src="../images/facebook.png" alt="Facebook" class="social-icon">
                        </a>
                        <a href="https://www.instagram.com">
                            <img src="../images/instagram.png" alt="Instagram" class="social-icon">
                        </a>
                        <a href="https://www.twitter.com">
                            <img src="../images/twitter.png" alt="Twitter" class="social-icon">
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2026 Rate Your Grader. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="../js/SearchOutput.js"></script>
</body>
</html>