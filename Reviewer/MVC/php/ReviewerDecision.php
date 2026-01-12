<?php
session_start();
include '../../../Student/MVC/db/Config.php';

$message = "";
$messageType = "";

// --- HANDLE FORM SUBMISSIONS ---

// 1. APPROVE LOGIC
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'approve') {
    // FIX: Use the unique ID (r_id)
    $target_id = intval($_POST['review_id']);
    
    // Fetch review text
    // FIX: WHERE r_id = ...
    $fetch_sql = "SELECT Review FROM review WHERE r_id = $target_id";
    $fetch_result = $conn->query($fetch_sql);
    
    if ($fetch_result && $fetch_result->num_rows > 0) {
        $row = $fetch_result->fetch_assoc();
        $review_text = $conn->real_escape_string($row['Review']);
        
        // Insert into A_Review
        // Note: We store the unique review ID ($target_id) in R_id column of A_Review
        $sql_insert = "INSERT INTO A_Review (R_id, Review, Report) VALUES ('$target_id', '$review_text', 0)";
        
        if ($conn->query($sql_insert) === TRUE) {
            
            // FIX: UPDATE WHERE r_id (Unique ID)
            $sql_update = "UPDATE review SET Reviewed = 1 WHERE r_id = $target_id";
            $conn->query($sql_update);

            $message = "Review #$target_id Approved Successfully!";
            $messageType = "success";
        } else {
            $message = "Error: " . $conn->error;
            $messageType = "error";
        }
    }
}

// 2. REJECT LOGIC
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'reject') {
    $target_id = intval($_POST['review_id']);
    $cause = $conn->real_escape_string($_POST['rejection_reason']);
    
    // Insert into R_Review
    $sql_insert = "INSERT INTO R_Review (R_id, Cause) VALUES ('$target_id', '$cause')";
    
    if ($conn->query($sql_insert) === TRUE) {

        // FIX: UPDATE WHERE r_id (Unique ID)
        $sql_update = "UPDATE review SET Reviewed = 1 WHERE r_id = $target_id";
        $conn->query($sql_update);

        $message = "Review #$target_id Rejected.";
        $messageType = "success"; 
    } else {
        $message = "Error: " . $conn->error;
        $messageType = "error";
    }
}

// --- FETCH PENDING REVIEWS ---
// FIX: Ensure we select r_id (Unique) 
$sql_pending = "SELECT r.*, p.Name as ProfName, c.`Course Name`
                FROM review r
                LEFT JOIN professors p ON r.P_id = p.P_id
                LEFT JOIN courses c ON r.C_id = c.c_id
                WHERE r.Reviewed = 0
                ORDER BY r.r_id ASC";

$result = $conn->query($sql_pending);
?>

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
        // This line cleans the URL so the alert doesn't appear if they refresh
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
        <a href="../../../Common/MVC/phpLogout.php" class="btn btn-primary" style="background-color: #dc3545; color: white;">Logout</a>
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
                        <a href="#" class="footer-nav-link">Apply for Reviewer</a>
                        <a href="#" class="footer-nav-link">Apply for University Representative</a>
                    </div>
                </div>

                <div class="footer-socials">
                    <h5>Our Socials</h5>
                    <div class="social-icons">
                        <a href="#" aria-label="Facebook">
                            <img src="../images/facebook.png" alt="Facebook" class="social-icon">
                        </a>
                        <a href="#" aria-label="Instagram">
                            <img src="../images/instagram.png" alt="Instagram" class="social-icon">
                        </a>
                        <a href="#" aria-label="Twitter">
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