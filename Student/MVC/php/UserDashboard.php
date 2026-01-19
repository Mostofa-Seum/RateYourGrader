<?php
session_start();
include '../db/Config.php';

// HANDLE AJAX REQUESTS 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json'); 
    
    // Security: Check if user is logged in
    if (!isset($_SESSION['s_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'You are not logged in.']);
        exit;
    }

    $user_id = $_SESSION['s_id'];
    $action = $_POST['action'] ?? '';

    //  DELETE REVIEW LOGIC 
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
            // Verify user owns the review
            $check_sql = "SELECT r_id FROM review WHERE r_id = ? AND s_id = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("ii", $r_id, $user_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();

            if ($check_result->num_rows === 0) {
                throw new Exception("Review not found or permission denied.");
            }
            $check_stmt->close();

            // Delete from specific tables based on type
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

            //  Delete from main review table (for all types including pending)
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

    //  UPDATE USERNAME LOGIC 
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
    
    //  UPDATE PASSWORD LOGIC 
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
    
    exit; 
}

//   REGULAR PAGE LOAD (SESSION CHECK) 
$tableName = "users"; 

if (!isset($_SESSION['s_id'])) {
    // Path relative to Student/MVC/php/
    header("Location: ../../../Common/MVC/php/Login.php"); 
    exit(); 
} 

$current_user_id = $_SESSION['s_id'];

//   GET USER INFO 
$sql = "SELECT Username, Email, role FROM $tableName WHERE s_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $current_user_id);
$stmt->execute();
$result = $stmt->get_result();

$user_role = ''; // Initialize variable

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $db_username = $row['Username'];
    $db_email = $row['Email'];
    $user_role = $row['role']; 
} else {
    $db_username = "Unknown";
    $db_email = "Unknown";
}
$stmt->close();

//   GET STATS COUNTS 

// Accepted Count
$accepted_sql = "SELECT COUNT(*) as count FROM a_review 
                 JOIN review ON a_review.r_id = review.r_id 
                 WHERE review.s_id = ?";
$stmt = $conn->prepare($accepted_sql);
$stmt->bind_param("i", $current_user_id);
$stmt->execute();
$acc_result = $stmt->get_result();
$accepted_count = $acc_result->fetch_assoc()['count'];
$stmt->close();

// Pending Count
$pending_sql = "SELECT COUNT(*) as count FROM review 
                WHERE s_id = ? AND Reviewed = 0";
$stmt = $conn->prepare($pending_sql);
$stmt->bind_param("i", $current_user_id);
$stmt->execute();
$pend_result = $stmt->get_result();
$pending_count = $pend_result->fetch_assoc()['count'];
$stmt->close();

// Rejected Count
$rejected_sql = "SELECT COUNT(*) as count FROM r_review 
                 JOIN review ON r_review.r_id = review.r_id 
                 WHERE review.s_id = ?";
$stmt = $conn->prepare($rejected_sql);
$stmt->bind_param("i", $current_user_id);
$stmt->execute();
$rej_result = $stmt->get_result();
$rejected_count = $rej_result->fetch_assoc()['count'];
$stmt->close();

//   PREPARE REVIEWS DATA FOR VIEW (Moved from bottom) 
$reviewsData = [
    'accepted' => [],
    'pending' => [],
    'rejected' => []
];

// Fetch Accepted
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

// Fetch Pending
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

// Fetch Rejected
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
include '../html/UserDashboard.php';
?>