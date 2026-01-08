<?php
include 'Config.php';
$message = "";
$messageType = "";

// --- HANDLE FORM SUBMISSIONS ---

// 1. APPROVE LOGIC
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'approve') {
    $r_id = intval($_POST['review_id']);
    
    // First, fetch the review text to store in A_Review (based on your table schema)
    $fetch_sql = "SELECT Review FROM review WHERE Rv_id = $r_id";
    $fetch_result = $conn->query($fetch_sql);
    
    if ($fetch_result && $fetch_result->num_rows > 0) {
        $row = $fetch_result->fetch_assoc();
        $review_text = $conn->real_escape_string($row['Review']);
        
        // Insert into A_Review
        // Assuming Report defaults to 0 (False)
        $sql = "INSERT INTO A_Review (R_id, Review, Report) VALUES ('$r_id', '$review_text', 0)";
        
        if ($conn->query($sql) === TRUE) {
            $message = "Review #$r_id Approved Successfully!";
            $messageType = "success";
        } else {
            $message = "Error: " . $conn->error;
            $messageType = "error";
        }
    }
}

// 2. REJECT LOGIC
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'reject') {
    $r_id = intval($_POST['review_id']);
    $cause = $conn->real_escape_string($_POST['rejection_reason']);
    
    // Insert into R_Review
    $sql = "INSERT INTO R_Review (R_id, Cause) VALUES ('$r_id', '$cause')";
    
    if ($conn->query($sql) === TRUE) {
        $message = "Review #$r_id Rejected.";
        $messageType = "success"; // Using success style for confirmation
    } else {
        $message = "Error: " . $conn->error;
        $messageType = "error";
    }
}

// --- FETCH PENDING REVIEWS ---
// We only want reviews that are NOT in A_Review AND NOT in R_Review
$sql_pending = "SELECT r.*, p.Name as ProfName, c.`Course Name`
                FROM review r
                LEFT JOIN professors p ON r.P_id = p.P_id
                LEFT JOIN courses c ON r.C_id = c.c_id
                WHERE r.Rv_id NOT IN (SELECT R_id FROM A_Review)
                AND r.Rv_id NOT IN (SELECT R_id FROM R_Review)
                ORDER BY r.Rv_id ASC";

$result = $conn->query($sql_pending);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reviewer Dashboard</title>
    <link rel="stylesheet" href="ReviewerDecision.css">
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
                <a href="SearchOutput.php">Home</a>
                <a href="#">Dashboard</a>
                <button class="btn btn-primary">Logout</button>
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

        <?php if ($result->num_rows > 0): ?>
            <div class="review-grid">
                <?php while($row = $result->fetch_assoc()): ?>
                    <div class="admin-card">
                        <div class="card-header">
                            <span class="review-id">ID: #<?= $row['Rv_id'] ?></span>
                            <span class="date-badge">Prof: <?= htmlspecialchars($row['ProfName']) ?></span>
                        </div>
                        
                        <div class="card-body">
                            <div class="meta-tags">
                                <span class="tag">Course: <?= htmlspecialchars($row['Course Name']) ?></span>
                                <span class="tag">Rating: <?= $row['Overall Rating'] ?>/5</span>
                            </div>
                            <p class="review-text">"<?= nl2br(htmlspecialchars($row['Review'])) ?>"</p>
                        </div>

                        <div class="card-actions">
                            <form method="POST" action="">
                                <input type="hidden" name="action" value="approve">
                                <input type="hidden" name="review_id" value="<?= $row['Rv_id'] ?>">
                                <button type="submit" class="btn-action btn-approve">
                                    Approve
                                </button>
                            </form>

                            <button class="btn-action btn-reject" onclick="openRejectModal(<?= $row['Rv_id'] ?>)">
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
                            <img src="Figures/scolar_cap.png" alt="Logo" class="logo-img">
                        </div>
                        <span class="footer-logo-text">Rate Your Grader</span>
                    </div>
                    <p>Empowering students with transparent grading information since 2024.</p>
                </div>
                <div class="footer-bottom">
                    <p>&copy; 2024 Rate My Grader. Admin Dashboard.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="ReviewerDecision.js"></script>
</body>
</html>