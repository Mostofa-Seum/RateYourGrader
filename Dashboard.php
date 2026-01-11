<?php
session_start();
include 'config.php';

$tableName = "users"; 

// Check login (Using ID 1 fallback for testing if session is missing)
if (!isset($_SESSION['s_id'])) {
    $current_user_id = 1001; 
} else {
    $current_user_id = $_SESSION['s_id'];
}

// --- 1. GET USER INFO ---
$sql = "SELECT Username, Email FROM $tableName WHERE s_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $current_user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $db_username = $row['Username'];
    $db_email = $row['Email'];
} else {
    $db_username = "Unknown User";
    $db_email = "No Email Found";
}
$stmt->close();

// --- 2. GET ACCEPTED REVIEWS COUNT ---
// Logic: Count rows in 'a_review' that belong to this user
$accepted_sql = "SELECT COUNT(*) as count FROM a_review 
                 JOIN review ON a_review.r_id = review.r_id 
                 WHERE review.s_id = ?";
$stmt = $conn->prepare($accepted_sql);
$stmt->bind_param("i", $current_user_id);
$stmt->execute();
$acc_result = $stmt->get_result();
$accepted_count = $acc_result->fetch_assoc()['count'];
$stmt->close();

// --- 3. GET PENDING REVIEWS COUNT ---
// Logic: Count rows in 'review' table where Reviewed is 0 for this user
$pending_sql = "SELECT COUNT(*) as count FROM review 
                WHERE s_id = ? AND Reviewed = 0";
$stmt = $conn->prepare($pending_sql);
$stmt->bind_param("i", $current_user_id);
$stmt->execute();
$pend_result = $stmt->get_result();
$pending_count = $pend_result->fetch_assoc()['count'];
$stmt->close();

// --- 4. GET REJECTED REVIEWS COUNT ---
// Logic: Count rows in 'r_review' that belong to this user
// Note: Ensure your database column is named 'r_id' inside r_review table. 
// If it is named 'r-id' (with a dash), change query to: `r-id` = review.r_id
$rejected_sql = "SELECT COUNT(*) as count FROM r_review 
                 JOIN review ON r_review.r_id = review.r_id 
                 WHERE review.s_id = ?";
$stmt = $conn->prepare($rejected_sql);
$stmt->bind_param("i", $current_user_id);
$stmt->execute();
$rej_result = $stmt->get_result();
$rejected_count = $rej_result->fetch_assoc()['count'];
$stmt->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rate Your Grader - Dashboard</title>
    <link rel="stylesheet" href="HomePage.css">
    <link rel="stylesheet" href="dashboard.css">
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
                <a href="#how-it-works">How it works</a>
                <a href="#features">Features</a>
                <a href="#search">Search Graders</a>
                <button class="btn btn-primary">Sign Up Free</button>
            </div>
        </div>
    </nav>

    <main class="dashboard-page">
    <div class="container">
        <div class="dashboard-grid">
            <div class="card profile-card">
    <div class="profile-avatar"><img src="Figures/aiden.png" alt="Profile Avatar"></div>
    <div class="profile-name"><?php echo htmlspecialchars($db_username); ?></div>
    <div class="profile-email"><?php echo htmlspecialchars($db_email); ?></div>
    <button class="edit-profile-btn" onclick="toggleEditProfileSection()">Edit Profile</button>
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

    <div class="success-message" id="successMessage"></div>

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
                        <a href="#" class="footer-nav-link">Apply for Reviewer</a>
                        <a href="#" class="footer-nav-link">Apply for University Representative</a>
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
                <p>&copy; 2024 Rate My Grader. All rights reserved. Made with ƒ?‹,? for students everywhere.</p>
            </div>
        </div>
    </footer>

    </main>

   <?php
// Initialize arrays to hold review data
$reviewsData = [
    'accepted' => [],
    'pending' => [],
    'rejected' => []
];

// 1. FETCH ACCEPTED REVIEWS
// Join review and a_review tables
$sql_acc = "SELECT r.* FROM review r 
            JOIN a_review ar ON r.r_id = ar.r_id 
            WHERE r.s_id = ? ORDER BY r.r_id DESC";
$stmt = $conn->prepare($sql_acc);
$stmt->bind_param("i", $current_user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $reviewsData['accepted'][] = [
        'id' => $row['r_id'],
        'title' => 'Course ID: ' . $row['C_id'], // Using Course ID as title since we don't have Name joined
        'date' => '#'.$row['r_id'], // Using ID as a proxy for date/version
        'rating' => $row['Overall Rating'], // Ensure this matches your DB column name exactly
        'content' => $row['Review']
    ];
}
$stmt->close();

// 2. FETCH PENDING REVIEWS
// Reviews where Reviewed = 0
$sql_pen = "SELECT * FROM review WHERE s_id = ? AND Reviewed = 0 ORDER BY r_id DESC";
$stmt = $conn->prepare($sql_pen);
$stmt->bind_param("i", $current_user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $reviewsData['pending'][] = [
        'id' => $row['r_id'],
        'title' => 'Course ID: ' . $row['C_id'],
        'date' => '#' . $row['r_id'],
        'rating' => $row['Overall Rating'],
        'content' => $row['Review']
    ];
}
$stmt->close();

// 3. FETCH REJECTED REVIEWS
// Join review and r_review tables to get the Cause
// REMINDER: using backticks for `r-id` because of the hyphen
$sql_rej = "SELECT r.*, rr.Cause FROM review r 
            JOIN r_review rr ON r.r_id = rr.r_id 
            WHERE r.s_id = ? ORDER BY r.r_id DESC";
$stmt = $conn->prepare($sql_rej);
$stmt->bind_param("i", $current_user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $reviewsData['rejected'][] = [
        'id' => $row['r_id'],
        'title' => 'Course ID: ' . $row['C_id'],
        'date' => 'ID: #' . $row['r_id'],
        'rating' => $row['Overall Rating'],
        'content' => $row['Review'],
        'rejectionReason' => $row['Cause']
    ];
}
$stmt->close();
?>

<script>
    // This creates the reviewsData object using the real data fetched above
    const reviewsData = <?php echo json_encode($reviewsData); ?>;
</script>

<script src="dashboard.js"></script>
</body>
</html>
