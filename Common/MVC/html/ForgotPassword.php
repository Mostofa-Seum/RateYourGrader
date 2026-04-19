<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="../css/ForgotPassword.css">
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
                </div>
            
            <button class="mobile-menu-btn">
                <img src="../images/menu.png" alt="Menu" class="mobile-menu-icon">
            </button>
        </div>
    </nav>

    <div style="margin-top: 100px;"></div>

    <div class="main-wrapper">
        <div class="auth-card">
            
            <div class="auth-header">
                <h2><?php echo ($step == 1) ? "Find Your Account" : "Reset Password"; ?></h2>
                <p><?php echo ($step == 1) ? "Enter your details to search for your account." : "Create a new strong password."; ?></p>
            </div>

            <?php if (!empty($error_msg)): ?>
                <div class="alert error"><?php echo $error_msg; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($success_msg)): ?>
                <div class="alert success"><?php echo $success_msg; ?></div>
            <?php endif; ?>

            <?php if ($step == 1 && empty($success_msg)): ?>
            <form method="POST" action="" class="auth-form">
                <input type="hidden" name="action" value="verify">
                
                <div class="form-group">
                    <label for="username">Name</label>
                    <input type="text" id="username" name="username" placeholder="Enter your registered name" required>
                </div>

                <div class="form-group">
                    <label for="email">Email </label>
                    <input type="email" id="email" name="email" placeholder="Enter your registered email" required>
                </div>

                <button type="submit" class="btn-submit">Reset Password</button>
            </form>
            <?php endif; ?>

            <?php if ($step == 2 && empty($success_msg)): ?>
            <form method="POST" action="" class="auth-form" id="resetForm">
                <input type="hidden" name="action" value="reset">
                
                <div class="form-group">
                    <label for="password">New Password</label>
                    <input type="password" id="password" name="password" placeholder="Min 6 characters" required>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter password" required>
                </div>

                <button type="submit" class="btn-submit">Update Password</button>
            </form>
            <?php endif; ?>

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

    <script src="../js/ForgotPassword.js"></script>
</body>
</html>