<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reviewer Dashboard</title>
    <link rel="stylesheet" href="../css/ReviewerDashboard.css"> 
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
            <a href="ReviewerDecision.php">Review Panel</a>
            <a href="../../../Common/MVC/php/Logout.php" class="btn btn-primary" style="color: white;">Logout</a>
        </div>
    </div>
</nav>

<main class="dashboard-page">
    <div class="container">
        <div class="dashboard-grid">
            
            <div class="card profile-card">
                <div class="profile-avatar">
                    <img src="../images/aiden.png" alt="Profile Avatar"> 
                </div>
                <div class="profile-name" style="color: var(--primary-navy);"><?php echo htmlspecialchars($user_data['Username']); ?></div>
                <div class="profile-email"><?php echo htmlspecialchars($user_data['Email']); ?></div>
                <button class="edit-profile-btn" onclick="toggleEditProfileSection()">Edit Profile</button>
            </div>

            <div class="stats-section">
                <h2>Platform Overview</h2>
                <div class="stats-grid">
                    
                    <div class="stat-card pending" onclick="handleCardClick('pending')">
                        <div class="stat-number"><?php echo $pending_count; ?></div>
                        <div class="stat-label">Pending Reviews</div>
                    </div>

                    <div class="stat-card accepted" onclick="handleCardClick('accepted')">
                        <div class="stat-number"><?php echo $accepted_count; ?></div>
                        <div class="stat-label">Approved History</div>
                    </div>

                    <div class="stat-card rejected" onclick="handleCardClick('rejected')">
                        <div class="stat-number"><?php echo $rejected_count; ?></div>
                        <div class="stat-label">Rejection History</div>
                    </div>

                </div>
            </div>
        </div>

        <div class="card" id="edit-profile-section" style="display: none;">
            <div class="form-section">
                <h3>Account Settings</h3>
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" placeholder="Enter your username" value="<?php echo htmlspecialchars($user_data['Username']); ?>">
                </div>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" placeholder="Enter your email" value="<?php echo htmlspecialchars($user_data['Email']); ?>" disabled>
                </div>
                
                <div id="username-message" class="message-box"></div> 

                <div class="button-group">
                    <button class="btn btn-primary" onclick="updateUsername()">Update Username</button>
                    <button class="btn btn-secondary" onclick="toggleEditProfileSection()">Cancel</button>
                </div>
            </div>

            <div class="form-section">
                <h3>Change Password</h3>
                <div class="form-group">
                    <label for="current-password">Current Password</label>
                    <input type="password" id="current-password" placeholder="Enter current password">
                </div>
                <div class="form-group">
                    <label for="new-password">New Password</label>
                    <input type="password" id="new-password" placeholder="Enter new password">
                </div>
                <div class="form-group">
                    <label for="confirm-password">Confirm New Password</label>
                    <input type="password" id="confirm-password" placeholder="Confirm new password">
                </div>

                <div id="password-message" class="message-box"></div>

                <div class="button-group">
                    <button class="btn btn-primary" onclick="updatePassword()">Update Password</button>
                    <button class="btn btn-secondary" onclick="resetPasswordForm()">Cancel</button>
                </div>
            </div>
        </div>

        <div class="card recent-reviews-card">
            <div class="section-header">
                <h3>Recently Approved</h3>
            </div>
            <div class="recent-reviews-list" id="recent-reviews-list">
            </div>
        </div>
    </div>

    <div class="modal" id="historyModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modal-title">Review History</h2>
                <button class="close-modal" onclick="closeHistoryModal()">×</button>
            </div>
            <div class="reviews-list" id="modal-list">
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
                        <span class="footer-logo-text">Rate Your Grader</span>
                    </div>
                    <p>Empowering students with transparent grading information since 2024.</p>
                </div>

                <div class="footer-actions">
                    <h5>Apply</h5>
                    <div class="footer-buttons">
                        <?php 
                        // Reviewer Option
                        if ($user_role === 'Reviewer') {
                            echo '<span class="footer-nav-link" style="cursor: default; color: #6c757d;">You are a Reviewer</span>';
                        } else {
                            echo '<a href="../../../Common/MVC/php/ApplyRole.php" class="footer-nav-link">Apply for Reviewer</a>';
                        }

                        // University Rep Option
                        if ($user_role === 'UniRep') {
                            echo '<span class="footer-nav-link" style="cursor: default; color: #6c757d;">You are a University Representative</span>';
                        } else {
                            echo '<a href="../../../Common/MVC/php/ApplyRole.php" class="footer-nav-link">Apply for University Representative</a>';
                        }
                        ?>
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
</main>

<script>
    window.reviewerData = <?php echo json_encode($reviewsData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
</script>
<script src="../js/ReviewerDashboard.js"></script>

</body>
</html>