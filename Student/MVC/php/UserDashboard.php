<?php
session_start();
include '../db/Config.php';

// --- 1. HANDLE AJAX REQUESTS (Profile Updates & Deletion) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json'); 
    
    // Security: Check if user is logged in
    if (!isset($_SESSION['s_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'You are not logged in.']);
        exit;
    }

    $user_id = $_SESSION['s_id'];
    $action = $_POST['action'] ?? '';

    // --- NEW: DELETE REVIEW LOGIC ---
    if ($action === 'delete_review') {
        $r_id = $_POST['r_id'];
        $type = $_POST['type']; // accepted, rejected, or pending

        if (!$r_id) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid Review ID.']);
            exit;
        }

        // Start Transaction to ensure data integrity
        $conn->begin_transaction();

        try {
            // 1. Verify user owns the review
            $check_sql = "SELECT r_id FROM review WHERE r_id = ? AND s_id = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("ii", $r_id, $user_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();

            if ($check_result->num_rows === 0) {
                throw new Exception("Review not found or permission denied.");
            }
            $check_stmt->close();

            // 2. Delete from specific tables based on type
            // Note: We delete child records (a_review/r_review) first to avoid Foreign Key errors
            if ($type === 'accepted') {
                $del_a = $conn->prepare("DELETE FROM a_review WHERE r_id = ?");
                $del_a->bind_param("i", $r_id);
                $del_a->execute();
                $del_a->close();
            } 
            elseif ($type === 'rejected') {
                $del_r = $conn->prepare("DELETE FROM r_review WHERE r_id = ?");
                $del_r->bind_param("i", $r_id);
                $del_r->execute();
                $del_r->close();
            }

            // 3. Delete from main review table (for all types including pending)
            $del_main = $conn->prepare("DELETE FROM review WHERE r_id = ? AND s_id = ?");
            $del_main->bind_param("ii", $r_id, $user_id);
            
            if ($del_main->execute()) {
                $conn->commit(); 
                echo json_encode(['status' => 'success', 'message' => 'Review deleted successfully.']);
            } else {
                throw new Exception("Failed to delete review.");
            }
            $del_main->close();

        } catch (Exception $e) {
            $conn->rollback(); 
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

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
$tableName = "users"; 

if (!isset($_SESSION['s_id'])) {
    header("Location: Login.php"); 
    exit(); 
} 

$current_user_id = $_SESSION['s_id'];

// --- 3. GET USER INFO ---
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
    $db_username = "Unknown";
    $db_email = "Unknown";
}
$stmt->close();

// --- 4. GET ACCEPTED REVIEWS COUNT ---
$accepted_sql = "SELECT COUNT(*) as count FROM a_review 
                 JOIN review ON a_review.r_id = review.r_id 
                 WHERE review.s_id = ?";
$stmt = $conn->prepare($accepted_sql);
$stmt->bind_param("i", $current_user_id);
$stmt->execute();
$acc_result = $stmt->get_result();
$accepted_count = $acc_result->fetch_assoc()['count'];
$stmt->close();

// --- 5. GET PENDING REVIEWS COUNT ---
$pending_sql = "SELECT COUNT(*) as count FROM review 
                WHERE s_id = ? AND Reviewed = 0";
$stmt = $conn->prepare($pending_sql);
$stmt->bind_param("i", $current_user_id);
$stmt->execute();
$pend_result = $stmt->get_result();
$pending_count = $pend_result->fetch_assoc()['count'];
$stmt->close();

// --- 6. GET REJECTED REVIEWS COUNT ---
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
        
        <button class="mobile-menu-btn">
            <img src="Figures/menu.png" alt="Menu" class="mobile-menu-icon">
        </button>
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

    <button class="btn btn-secondary" style="width: 100%; margin-top: 10px;" onclick="window.location.href='../../../UniversityRepresentative/MVC/php/UniversityRepDashboard.php'">
       <b> Switch to University Representative View </b>
    </button>
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
</main>

<?php
// Initialize arrays explicitly to avoid NULL errors in JS
$reviewsData = [
    'accepted' => [],
    'pending' => [],
    'rejected' => []
];

// 1. FETCH ACCEPTED REVIEWS
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
        'title' => 'Course ID: ' . $row['C_id'],
        'date' => '#'.$row['r_id'],
        'rating' => $row['Overall Rating'],
        'content' => $row['Review']
    ];
}
$stmt->close();

// 2. FETCH PENDING REVIEWS
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
    window.reviewsData = <?php echo json_encode($reviewsData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
</script>

<script src="../js/UserDashboard.js"></script>
</body>
</html>