<?php
session_start();
include 'Config.php';

// --- HANDLE FORM SUBMISSION ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_application') {
    header('Content-Type: application/json');

    // 1. Check Login
    if (!isset($_SESSION['s_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'You must be logged in to apply.']);
        exit;
    }

    $s_id = $_SESSION['s_id'];
    $role = $_POST['role']; // 'Reviewer' or 'UniRep'
    $reason = trim($_POST['reason']); // Optional reason text

    // Validate Role Input
    $valid_roles = ['Reviewer', 'UniRep'];
    if (!in_array($role, $valid_roles)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid role selected.']);
        exit;
    }

    // 2. Check for Pending Requests (Prevent Duplicates)
    $check_stmt = $conn->prepare("SELECT id FROM role_requests WHERE s_id = ? AND status = 'Pending'");
    $check_stmt->bind_param("i", $s_id);
    $check_stmt->execute();
    if ($check_stmt->get_result()->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'You already have a pending application. Please wait for Admin approval.']);
        exit;
    }
    $check_stmt->close();

    // 3. Insert Request
    // Note: Assuming table 'role_requests' has columns: id, s_id, requested_role, status, reason (optional)
    // If 'reason' column doesn't exist in your DB, remove it from the query below.
    
    // Check if table exists first (Safety check)
    $tbl_check = $conn->query("SHOW TABLES LIKE 'role_requests'");
    if($tbl_check->num_rows == 0) {
        // Create table if it doesn't exist (Optional, but helpful for setup)
        $conn->query("CREATE TABLE role_requests (
            id INT AUTO_INCREMENT PRIMARY KEY,
            s_id INT,
            requested_role VARCHAR(50),
            status VARCHAR(20) DEFAULT 'Pending',
            FOREIGN KEY (s_id) REFERENCES users(s_id)
        )");
    }

    $stmt = $conn->prepare("INSERT INTO role_requests (s_id, requested_role, status) VALUES (?, ?, 'Pending')");
    $stmt->bind_param("is", $s_id, $role);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Application submitted successfully! The Admin will review it shortly.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $stmt->error]);
    }
    $stmt->close();
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for Role - Rate Your Grader</title>
    <link rel="stylesheet" href="ApplyRole.css">
</head>
<body>

<nav class="navbar">
    <div class="container nav-container">
        <div class="logo-wrapper">
            <div class="logo-icon">
                <img src="Figures/scolar_cap.png" alt="Logo" class="logo-img">
            </div>
            <span class="logo-text">Rate Your Grader</span>
        </div>
        
        <div class="nav-links">
            <a href="HomePage.php">Home</a>
            <a href="UserDashboard.php">Dashboard</a> <a href="Logout.php" class="btn btn-primary" style="background-color: #dc3545; color: white;">Logout</a>
        </div>
        
        <button class="mobile-menu-btn">
            <img src="Figures/menu.png" alt="Menu" class="mobile-menu-icon">
        </button>
    </div>
</nav>

<main class="page-container">
    <div class="container">
        
        <a href="UserDashboard.php" class="back-link">← Back to Dashboard</a>

        <div class="form-card">
            <div class="form-header">
                <h2>Join Our Team</h2>
                <p>Apply to become a Reviewer or University Representative.</p>
            </div>

            <form id="applyForm" onsubmit="return false;">
                
                <div class="form-group">
                    <label for="role">Select Position</label>
                    <select id="role" name="role" required>
                        <option value="" disabled selected>Choose a role...</option>
                        <option value="Reviewer">Reviewer (Moderate Reviews)</option>
                        <option value="UniRep">University Representative (Manage Faculty)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="reason">Why do you want this role?</label>
                    <textarea id="reason" name="reason" rows="4" placeholder="Briefly explain why you are a good fit... (Optional)"></textarea>
                </div>

                <div id="response-message" class="message-box"></div>

                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="window.location.href='UserDashboard.php'">Cancel</button>
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
                        <img src="Figures/scolar_cap.png" alt="Logo" class="logo-img">
                    </div>
                    <span class="footer-logo-text">Rate My Grader</span>
                </div>
                <p>Empowering students with transparent grading information since 2024.</p>
            </div>

            <div class="footer-actions">
                <h5>Apply</h5>
                <div class="footer-buttons">
                    <a href="ApplyRole.php" class="footer-nav-link">Apply for Reviewer</a>
                    <a href="ApplyRole.php" class="footer-nav-link">Apply for University Representative</a>
                </div>
            </div>

            <div class="footer-socials">
                <h5>Our Socials</h5>
                <div class="social-icons">
                    <a href="#" aria-label="Facebook">
                        <img src="Figures/facebook.png" alt="Facebook" class="social-icon">
                    </a>
                    <a href="#" aria-label="Instagram">
                        <img src="Figures/instagram.png" alt="Instagram" class="social-icon">
                    </a>
                    <a href="#" aria-label="Twitter">
                        <img src="Figures/twitter.png" alt="Twitter" class="social-icon">
                    </a>
                </div>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; 2024 Rate My Grader. All rights reserved.</p>
        </div>
    </div>
</footer>

<script src="ApplyRole.js"></script>

</body>
</html>