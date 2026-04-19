<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/UserDashboard.css">
    <title>User Dashboard</title>
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
            <a href="../../../Common/MVC/php/Logout.php" class="btn btn-primary-nav" style="background-color: #dc3545; color: white;">Logout</a>
        </div>
    </div>
</nav>

<main class="dashboard-page">
    <div class="container">
        <div class="dashboard-grid">
            
            <div class="card profile-card">
                <div class="profile-avatar"><img src="../images/aiden.png" alt="Profile Avatar"></div>
                <div class="profile-name"><?php echo htmlspecialchars($db_username); ?></div>
                <div class="profile-email"><?php echo htmlspecialchars($db_email); ?></div>
                
                <button class="edit-profile-btn" onclick="toggleEditProfileSection()">Edit Profile</button>

                <?php if ($user_role === 'UniRep'): ?>
                    <button class="btn btn-secondary" style="width: 100%; margin-top: 10px;" onclick="window.location.href='../../../UniversityRepresentative/MVC/php/UniversityRepDashboard.php'">
                       <b> Switch to University Representative View </b>
                    </button>
                <?php endif; ?>
            </div>

            <div class="stats-section">
                <h2>Your Reviews</h2>
                <div class="stats-grid">
                    <div class="stat-card accepted" onclick="openReviewsModal('accepted')">
                        <div class="stat-number" id="accepted-count">
                            <?php echo $accepted_count; ?>
                        </div>
                        <div class="stat-label">Accepted</div>
                    </div>

                    <div class="stat-card pending" onclick="openReviewsModal('pending')">
                        <div class="stat-number" id="pending-count">
                            <?php echo $pending_count; ?>
                        </div>
                        <div class="stat-label">Pending</div>
                    </div>

                    <div class="stat-card rejected" onclick="openReviewsModal('rejected')">
                        <div class="stat-number" id="rejected-count">
                            <?php echo $rejected_count; ?>
                        </div>
                        <div class="stat-label">Rejected</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card" id="edit-profile-section" style="display: none;">
            <div class="form-section">
                <h3>Account Settings</h3>
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" placeholder="Enter your username" value="<?php echo htmlspecialchars($db_username); ?>">
                </div>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" placeholder="Enter your email" value="<?php echo htmlspecialchars($db_email); ?>" disabled>
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
                <h3>Recent Reviews</h3>
            </div>
            <div class="recent-reviews-list" id="recent-reviews-list"></div>
        </div>
    </div>

    <div class="modal" id="reviewsModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="reviews-modal-title">Accepted Reviews</h2>
                <button class="close-modal" onclick="closeReviewsModal()">×</button>
            </div>
            <div class="reviews-list" id="reviews-list">
                </div>
        </div>
    </div>

    <div class="modal" id="deleteConfirmationModal" style="z-index: 2000;">
        <div class="modal-content confirmation-box">
            <div class="confirmation-icon">🗑️</div>
            <h3>Delete Review?</h3>
            <p>Are you sure you want to delete this review? This action cannot be undone.</p>
            <div class="confirmation-actions">
                <button class="btn btn-secondary" onclick="closeDeleteModal()">No, Keep it</button>
                <button class="btn btn-danger" onclick="confirmDelete()">Yes, Delete it</button>
            </div>
        </div>
    </div>

    <div class="success-message" id="successMessage"></div>

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
                        <a href="../../../Common/MVC/php/ApplyRole.php" class="footer-nav-link">Apply for Reviewer</a>
                        <a href="../../../Common/MVC/php/ApplyRole.php" class="footer-nav-link">Apply for University Representative</a>
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
                <p>&copy; 2026 Rate Your Grader. All rights reserved.</p>
            </div>
        </div>
    </footer>
</main>

<script>
    window.reviewsData = <?php echo json_encode($reviewsData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
</script>

<script src="../js/UserDashboard.js"></script>
</body>
</html>