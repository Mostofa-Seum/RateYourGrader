<?php
session_start();
// Path relative to Reviewer/MVC/php/
include '../../../Student/MVC/db/Config.php';

$message = "";
$messageType = "";
$role = ""; // Initialize role variable

// --- 1. FETCH CURRENT REVIEWER ID (RV_id) & ROLE ---
$reviewer_id = 0;
if (isset($_SESSION['user_name'])) {
    $safe_username = $conn->real_escape_string($_SESSION['user_name']);
    
    // Step A: Get s_id AND role from users table
    $user_sql = "SELECT s_id, role FROM users WHERE Username = '$safe_username'";
    $user_result = $conn->query($user_sql);
    
    if ($user_result && $user_result->num_rows > 0) {
        $u_row = $user_result->fetch_assoc();
        
        $s_id = $u_row['s_id']; 
        $role = $u_row['role']; 

        // Step B: Get RV_id from reviwer table using s_id
        $rv_sql = "SELECT RV_id FROM reviwer WHERE s_id = '$s_id'";
        $rv_result = $conn->query($rv_sql);
        
        if ($rv_result && $rv_result->num_rows > 0) {
            $rv_row = $rv_result->fetch_assoc();
            $reviewer_id = $rv_row['RV_id'];
        }
    }
}

// --- HANDLE FORM SUBMISSIONS ---

// 2. APPROVE LOGIC
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'approve') {
    $target_id = intval($_POST['review_id']);
    
    // Fetch review text
    $fetch_sql = "SELECT Review FROM review WHERE r_id = $target_id";
    $fetch_result = $conn->query($fetch_sql);
    
    if ($fetch_result && $fetch_result->num_rows > 0) {
        $row = $fetch_result->fetch_assoc();
        $review_text = $conn->real_escape_string($row['Review']);
        
        // Insert into A_Review
        $sql_insert = "INSERT INTO A_Review (R_id, Review, Report) VALUES ('$target_id', '$review_text', 0)";
        
        if ($conn->query($sql_insert) === TRUE) {
            
            // Update 'Reviewed' status AND assign 'Rv_id'
            $sql_update = "UPDATE review SET Reviewed = 1, Rv_id = '$reviewer_id' WHERE r_id = $target_id";
            $conn->query($sql_update);

            $message = "Review #$target_id Approved Successfully!";
            $messageType = "success";
        } else {
            $message = "Error: " . $conn->error;
            $messageType = "error";
        }
    }
}

// 3. REJECT LOGIC
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'reject') {
    $target_id = intval($_POST['review_id']);
    $cause = $conn->real_escape_string($_POST['rejection_reason']);
    
    // Insert into R_Review
    $sql_insert = "INSERT INTO R_Review (R_id, Cause) VALUES ('$target_id', '$cause')";
    
    if ($conn->query($sql_insert) === TRUE) {

        // Update 'Reviewed' status AND assign 'Rv_id'
        $sql_update = "UPDATE review SET Reviewed = 1, Rv_id = '$reviewer_id' WHERE r_id = $target_id";
        $conn->query($sql_update);

        $message = "Review #$target_id Rejected.";
        $messageType = "success"; 
    } else {
        $message = "Error: " . $conn->error;
        $messageType = "error";
    }
}

// --- FETCH PENDING REVIEWS ---
$sql_pending = "SELECT r.*, p.Name as ProfName, c.`Course Name`
                FROM review r
                LEFT JOIN professors p ON r.P_id = p.P_id
                LEFT JOIN courses c ON r.C_id = c.c_id
                WHERE r.Reviewed = 0
                GROUP BY r.r_id
                ORDER BY r.r_id ASC";

$result = $conn->query($sql_pending);

// Load the View (HTML)
// Path relative to Reviewer/MVC/php/
include '../html/ReviewerDecision.php';
?>