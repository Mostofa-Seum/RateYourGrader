<?php
session_start();
// Path to DB Config
include "../db/Config.php"; 

$error_msg = "";
$success_msg = "";
$step = 1; // Default step: Verification

// Handle Form Submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // STEP 1: VERIFY USER
    if (isset($_POST['action']) && $_POST['action'] == 'verify') {
        $username = $conn->real_escape_string($_POST['username']);
        $email = $conn->real_escape_string($_POST['email']);

        // Check if user exists
        $sql = "SELECT * FROM users WHERE Username = '$username' AND Email = '$email'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            // User found! Move to Step 2
            $step = 2;
            $_SESSION['reset_email'] = $email; 
        } else {
            $error_msg = "No account found with that Name and Email combination.";
        }
    }

    // STEP 2: RESET PASSWORD
    if (isset($_POST['action']) && $_POST['action'] == 'reset') {
        $pass = $_POST['password'];
        $confirm_pass = $_POST['confirm_password'];
        $email = $_SESSION['reset_email'] ?? '';

        if (empty($email)) {
            $error_msg = "Session expired. Please try again.";
            $step = 1;
        } elseif (strlen($pass) < 6) {
            $error_msg = "Password must be at least 6 characters long!";
            $step = 2; 
        } elseif ($pass !== $confirm_pass) {
            $error_msg = "Passwords do not match!";
            $step = 2; 
        } else {
            // Update Password
            $update_sql = "UPDATE users SET Password = '$pass' WHERE Email = '$email'";
            
            if ($conn->query($update_sql) === TRUE) {
                $success_msg = "Password updated successfully! Redirecting to login...";
                $success_msg .= "<script>setTimeout(function(){ window.location.href = 'Login.php'; }, 3000);</script>";
                session_unset();
                session_destroy();
            } else {
                $error_msg = "Error updating record: " . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="../css/ForgotPassword.css">
    <style>
        /* Small override to ensure navbar style consistency if not fully present in ForgotPassword.css */
        .navbar { position: fixed; top: 0; width: 100%; z-index: 1000; background-color: rgba(255, 255, 255, 0.8); backdrop-filter: blur(12px); border-bottom: 1px solid rgba(229, 231, 235, 0.5); }
        .nav-container { display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.5rem; max-width: 1280px; margin: 0 auto; }
        .logo-wrapper { display: flex; align-items: center; gap: 0.5rem; }
        .logo-icon { width: 40px; height: 40px; background: linear-gradient(to right, #1e40af, #1e3a8a); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; }
        .logo-text { font-size: 1.5rem; font-weight: 700; background: linear-gradient(to right, #1e3a8a, #1e1b4b); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .nav-links { display: flex; align-items: center; gap: 2rem; }
        .nav-links a { text-decoration: none; color: #4b5563; font-weight: 500; transition: color 0.2s; }
        .nav-links a:hover { color: #1e40af; }
        @media (max-width: 768px) { .nav-links { display: none; } }
    </style>
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
                <p>&copy; 2026 Rate Your Grader. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="../js/ForgotPassword.js"></script>
</body>
</html>