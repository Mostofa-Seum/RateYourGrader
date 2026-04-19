<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for Role - Rate Your Grader</title>
    <link rel="stylesheet" href="../css/ApplyRole.css">
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
                <a href="HomePage.php">Home</a>

                <?php if (isset($_SESSION['user_name'])): ?>
                    <a href="<?php echo $dashboardLink; ?>" style="text-decoration:none;">
                        <span style="margin-right: 15px; font-weight: bold; color: inherit;">Hello, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                    </a>
                    <a href="Logout.php" class="btn btn-primary" style="background-color: #dc3545; color: white;">Logout</a>
                <?php else: ?>
                    <a href="Login.php" class="btn btn-primary" style="color: white;">Sign Up Free</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

<main class="page-container">
    <div class="container">
        <div class="form-card">
            <div class="form-header">
                <h2>Join Our Team</h2>
                <p>Apply to become a Reviewer or University Representative.</p>
            </div>

            <form id="applyForm" onsubmit="return false;">
                <div class="form-group">
                    <label for="role">Select Position</label>
                    <select id="role" name="role">
                        <option value="" disabled selected>Choose a role...</option>
                        <option value="Reviewer">Reviewer (Moderate Reviews)</option>
                        <option value="UniRep">University Representative (Manage Faculty)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="reason">Why do you want this role?</label>
                    <textarea id="reason" name="reason" rows="4" placeholder="Briefly explain why you are a good fit"></textarea>
                </div>

                <div id="response-message" class="message-box"></div>

                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="window.location.href='HomePage.php'">Cancel</button>
                    <button type="submit" class="btn btn-primary" onclick="submitApplication()">Submit Application</button>
                </div>
            </form>
        </div>
        
        <div class="info-section">
            <div class="info-card">
                <h3>⚖️ Reviewer</h3>
                <p>Reviewers help maintain the quality of our platform by moderating student reviews to ensure they follow community guidelines.</p>
            </div>
            <div class="info-card">
                <h3>🎓 University Representative</h3>
                <p>Uni Reps manage faculty data for their specific university, adding new professors and updating course information.</p>
            </div>
        </div>
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

<script src="../js/ApplyRole.js"></script>

</body>
</html>