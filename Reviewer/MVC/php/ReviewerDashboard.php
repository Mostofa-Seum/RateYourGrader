<?php
session_start();
include '../../../Student/MVC/db/Config.php';

// --- 1. HANDLE AJAX REQUESTS (Profile Updates) ---
// Copied from UserDashboard.php logic
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json'); 
    
    if (!isset($_SESSION['s_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'You are not logged in.']);
        exit;
    }

    $user_id = $_SESSION['s_id'];
    $action = $_POST['action'] ?? '';

    // --- UPDATE USERNAME LOGIC ---
    if ($action === 'update_username') {
        $new_username = trim($_POST['username']);
        if (empty($new_username)) {
            echo json_encode(['status' => 'error', 'message' => 'Username cannot be empty.']);
            exit;
        }
        
        $stmt = $conn->prepare("UPDATE users SET Username = ? WHERE s_id = ?");
        $stmt->bind_param("si", $new_username, $user_id);
        
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Username updated!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Database error.']);
        }
        $stmt->close();
        exit;
    }
    
    // --- UPDATE PASSWORD LOGIC ---
    if ($action === 'update_password') {
        $current_pass = $_POST['current_password'];
        $new_pass = $_POST['new_password'];
        $confirm_pass = $_POST['confirm_password'];

        if ($new_pass !== $confirm_pass) {
            echo json_encode(['status' => 'error', 'message' => 'New passwords do not match.']);
            exit;
        }

        $check_sql = "SELECT Password FROM users WHERE s_id = ?";
        $stmt = $conn->prepare($check_sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && $user['Password'] === $current_pass) {
            $update_stmt = $conn->prepare("UPDATE users SET Password = ? WHERE s_id = ?");
            $update_stmt->bind_param("si", $new_pass, $user_id);
            if ($update_stmt->execute()) {
                echo json_encode(['status' => 'success', 'message' => 'Password changed!']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Update failed.']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Incorrect current password.']);
        }
        exit;
    }
    
    exit; // End POST request
}

// --- 2. REGULAR PAGE LOAD (SESSION CHECK) ---
if (!isset($_SESSION['s_id'])) {
    header("Location: Login.php");
    exit();
}

$current_user_id = $_SESSION['s_id'];

// --- 3. GET USER INFO ---
$sql_user = "SELECT Username, Email FROM users WHERE s_id = ?";
$stmt = $conn->prepare($sql_user);
$stmt->bind_param("i", $current_user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user_data = $user_result->fetch_assoc();
$stmt->close();

// --- 4. GET COUNTS (Reviewer Stats) ---

// Count Pending
$sql_pending_count = "SELECT COUNT(*) as count FROM review WHERE Reviewed = 0";
$res_pending = $conn->query($sql_pending_count);
$pending_count = $res_pending->fetch_assoc()['count'];

// Count Accepted
$sql_accepted_count = "SELECT COUNT(*) as count FROM a_review";
$res_accepted = $conn->query($sql_accepted_count);
$accepted_count = $res_accepted->fetch_assoc()['count'];

// Count Rejected
$sql_rejected_count = "SELECT COUNT(*) as count FROM r_review";
$res_rejected = $conn->query($sql_rejected_count);
$rejected_count = $res_rejected->fetch_assoc()['count'];


// --- 5. FETCH DATA FOR MODALS ---

$reviewsData = [
    'accepted' => [],
    'rejected' => []
];

// Fetch Accepted (Fixed `Overall Rating` column)
$sql_acc = "SELECT ar.AR_id, ar.Review as FinalReview, r.r_id, r.C_id, r.`Overall Rating` 
            FROM a_review ar 
            JOIN review r ON ar.r_id = r.r_id 
            ORDER BY ar.AR_id DESC";
$res_acc = $conn->query($sql_acc);

if ($res_acc) {
    while ($row = $res_acc->fetch_assoc()) {
        $reviewsData['accepted'][] = [
            'id' => $row['r_id'],
            'title' => 'Course ID: ' . $row['C_id'],
            'date' => 'Review ID: #' . $row['AR_id'],
            'rating' => $row['Overall Rating'], 
            'content' => $row['FinalReview']
        ];
    }
}

// Fetch Rejected (Fixed `Overall Rating` column)
$sql_rej = "SELECT rr.RR_id, rr.Cause, r.r_id, r.C_id, r.`Overall Rating`, r.Review as OriginalReview
            FROM r_review rr 
            JOIN review r ON rr.r_id = r.r_id 
            ORDER BY rr.RR_id DESC";
$res_rej = $conn->query($sql_rej);

if ($res_rej) {
    while ($row = $res_rej->fetch_assoc()) {
        $reviewsData['rejected'][] = [
            'id' => $row['r_id'],
            'title' => 'Course ID: ' . $row['C_id'],
            'date' => 'Review ID: #' . $row['RR_id'],
            'rating' => $row['Overall Rating'], 
            'content' => $row['OriginalReview'],
            'rejectionReason' => $row['Cause']
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reviewer Dashboard</title>
    <link rel="stylesheet" href="../css/ReviewerDashboard.css"> 
    <style>
        .stat-card.pending {
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }
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