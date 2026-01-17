<?php
session_start();
include '../db/Config.php';

// --- HANDLE FORM SUBMISSION ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_application') {
    header('Content-Type: application/json');

    // 1. Check Login
    if (!isset($_SESSION['s_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'You must be logged in to apply.']);
        exit;
    }

    $s_id = $_SESSION['s_id'];
    $role = $_POST['role']; 
    // Trim removes whitespace from the beginning and end
    $reason = isset($_POST['reason']) ? trim($_POST['reason']) : ''; 

    // --- VALIDATION: Check if Reason is Empty ---
    if (empty($reason)) {
        echo json_encode(['status' => 'error', 'message' => 'Please explain why you want this role. The reason field cannot be empty.']);
        exit;
    }

    // Validate Input Role
    $valid_roles = ['Reviewer', 'UniRep'];
    if (!in_array($role, $valid_roles)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid role selected.']);
        exit;
    }

    // =========================================================
    // 2. NEW CHECK: Is the user ALREADY a Reviewer or UniRep?
    // =========================================================
    
    $user_check = $conn->prepare("SELECT role FROM users WHERE s_id = ?");
    $user_check->bind_param("i", $s_id);
    $user_check->execute();
    $user_result = $user_check->get_result();

    if ($user_result->num_rows > 0) {
        $user_row = $user_result->fetch_assoc();
        $current_user_role = $user_row['role']; 

        // If the user already holds a restricted role, stop them.
        if ($current_user_role === 'Reviewer' || $current_user_role === 'UniRep') {
            echo json_encode([
                'status' => 'error', 
                'message' => "You are already a $current_user_role.\nYou cannot submit a new application."
            ]);
            exit;
        }
    }
    $user_check->close();

    // =========================================================
    // 3. CHECK PENDING: Did they already apply?
    // =========================================================
    $check_stmt = $conn->prepare("SELECT `Applied Role` FROM `applications` WHERE S_id = ?");
    $check_stmt->bind_param("i", $s_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $existing_role = $row['Applied Role'];
        
        $msg = "You Already Applied For \"" . $existing_role . "\" \nWait for the Response";

        echo json_encode(['status' => 'error', 'message' => $msg]);
        exit;
    }
    $check_stmt->close();

    // =========================================================
    // 4. INSERT APPLICATION
    // =========================================================
    $stmt = $conn->prepare("INSERT INTO `applications` (`S_id`, `Applied Role`, `Reason`) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $s_id, $role, $reason);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Application submitted successfully!']);
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
            <a href="Logout.php" class="btn btn-primary" style="background-color: #dc3545; color: white;">Logout</a>
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
                    <select id="role" name="role" required>
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
                    <a href="#"><img src="../images/facebook.png" alt="Facebook" class="social-icon"></a>
                    <a href="#"><img src="../images/instagram.png" alt="Instagram" class="social-icon"></a>
                    <a href="#"><img src="../images/twitter.png" alt="Twitter" class="social-icon"></a>
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