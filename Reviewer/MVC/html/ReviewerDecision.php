<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reviewer Dashboard</title>
    <link rel="stylesheet" href="../css/ReviewerDecision.css">
</head>
<body>
    <?php if (isset($_GET['login']) && $_GET['login'] == 'success'): ?>
    <script>
        window.history.replaceState(null, null, window.location.pathname);
    </script>
<?php endif; ?>
    <nav class="navbar">
        <div class="container nav-container">
            <div class="logo-wrapper">
                <div class="logo-icon">
                    <img src="../images/scolar_cap.png" alt="Logo" class="logo-img">
                </div>
                <span class="logo-text">Rate Your Grader</span>
            </div>
            
<div class="nav-links">
    <?php if (isset($_SESSION['user_name'])): ?>
        <a href="../../../Common/MVC/php/Logout.php" class="btn btn-primary" style="background-color: #dc3545; color: white;">Logout</a>
    <?php else: ?>
        <a href="../../../Common/MVC/php/Login.php" class="btn btn-primary" style="color: white;">Sign Up Free</a>
    <?php endif; ?>
</div>
        </div>
    </nav>

    <div style="margin-top: 100px;"></div>

    <main class="container">
        <h1 class="page-title">Pending Reviews</h1>
        
        <?php if ($message != ""): ?>
            <div class="alert <?= $messageType ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <?php if ($result && $result->num_rows > 0): ?>
            <div class="review-grid">
                <?php while($row = $result->fetch_assoc()): ?>
                    <div class="admin-card">
                        <div class="card-header">
                            <span class="review-id">ID: #<?= $row['r_id'] ?></span>
                            <span class="date-badge">Prof: <?= htmlspecialchars($row['ProfName']) ?></span>
                        </div>
                        
                        <div class="card-body">
                            <div class="meta-tags">
                                <span class="tag">Course: <?= htmlspecialchars($row['Course Name']) ?></span>
                            </div>
                            <p class="review-text">"<?= nl2br(htmlspecialchars($row['Review'])) ?>"</p>
                        </div>

                        <div class="card-actions">
                            <form method="POST" action="">
                                <input type="hidden" name="action" value="approve">
                                <input type="hidden" name="review_id" value="<?= $row['r_id'] ?>">
                                <button type="submit" class="btn-action btn-approve">
                                    Approve
                                </button>
                            </form>

                            <button class="btn-action btn-reject" onclick="openRejectModal(<?= $row['r_id'] ?>)">
                                Reject
                            </button>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <h3>All Caught Up!</h3>
                <p>There are no pending reviews to moderate.</p>
            </div>
        <?php endif; ?>
    </main>

    <div id="rejectModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Reject Review</h2>
                <span class="close-btn" onclick="closeRejectModal()">&times;</span>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="action" value="reject">
                <input type="hidden" id="modal_review_id" name="review_id" value="">
                
                <div class="form-group">
                    <label for="rejection_reason">Reason for Rejection:</label>
                    <textarea name="rejection_reason" id="rejection_reason" rows="4" placeholder="e.g., Inappropriate language, Spam, Irrelevant content..." required></textarea>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="closeRejectModal()">Cancel</button>
                    <button type="submit" class="btn-confirm-reject">Confirm Rejection</button>
                </div>
            </form>
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
                        // Logic 1: Reviewer Option
                        if ($role === 'Reviewer') {
                            echo '<span class="footer-nav-link" style="cursor: default; color: #6c757d;">You are a Reviewer</span>';
                        } else {
                            // Direct link because user is logged in on dashboard
                            echo '<a href="../../../Common/MVC/php/ApplyRole.php" class="footer-nav-link">Apply for Reviewer</a>';
                        }

                        // Logic 2: University Rep Option
                        if ($role === 'UniRep') {
                            echo '<span class="footer-nav-link" style="cursor: default; color: #6c757d;">You are a University Representative</span>';
                        } else {
                            // Direct link because user is logged in on dashboard
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
                <p>&copy; 2026 Rate Your Grader. All rights reserved. Made with ❤️ for students everywhere.</p>
            </div>
        </div>
    </footer>

    <script src="../js/ReviewerDecision.js"></script>
</body>
</html>