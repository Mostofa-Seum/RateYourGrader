<?php
session_start();
include '../../../Student/MVC/db/Config.php';

// HANDLE AJAX REQUESTS (Profile Updates)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json'); 
    
    if (!isset($_SESSION['s_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'You are not logged in.']);
        exit;
    }

    $user_id = $_SESSION['s_id'];
    $action = $_POST['action'] ?? '';

    // UPDATE USERNAME LOGIC
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
    
    // UPDATE PASSWORD LOGIC
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

//  2. REGULAR PAGE LOAD (SESSION CHECK) 
if (!isset($_SESSION['s_id'])) {
    // Path relative to Reviewer/MVC/php/
    header("Location: ../../../Common/MVC/php/Login.php");
    exit();
}

$current_user_id = $_SESSION['s_id'];

//  3. GET USER INFO 
$sql_user = "SELECT Username, Email, role FROM users WHERE s_id = ?";
$stmt = $conn->prepare($sql_user);
$stmt->bind_param("i", $current_user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user_data = $user_result->fetch_assoc();
$user_role = $user_data['role']; 
$stmt->close();

//  4. GET COUNTS (Reviewer Stats) 

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


//  5. FETCH DATA FOR MODALS 

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
include '../html/ReviewerDashboard.php';
?>