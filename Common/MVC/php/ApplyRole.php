<?php
session_start();
include '../db/Config.php';

// DEFINE DASHBOARD LINK LOGIC (From Homepage)
$dashboardLink = "HomePage.php"; // Default fallback
if (isset($_SESSION['user_name'])) {
    $username = $_SESSION['user_name'];
    $safe_username = $conn->prepare("SELECT role FROM users WHERE Username = ?");
    $safe_username->bind_param("s", $username);
    $safe_username->execute();
    $result = $safe_username->get_result();

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $role = $row['role']; 

        if ($role == 'Reviewer') {
            $dashboardLink = "../../../Reviewer/MVC/php/ReviewerDashboard.php"; 
        } elseif ($role == 'Student') {
            $dashboardLink = "../../../Student/MVC/php/UserDashboard.php";
        } elseif ($role == 'UniRep') {
            $dashboardLink = "../../../UniversityRepresentative/MVC/php/UniversityRepDashboard.php"; 
        }
    }
    $safe_username->close();
}

//  HANDLE FORM SUBMISSION 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_application') {
    header('Content-Type: application/json');

    // 1. Check Login
    if (!isset($_SESSION['s_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'You must be logged in to apply.']);
        exit;
    }

    $s_id = $_SESSION['s_id'];
    $role = $_POST['role']; 
    $reason = isset($_POST['reason']) ? trim($_POST['reason']) : ''; 

    //  VALIDATION 
    if (empty($reason)) {
        echo json_encode(['status' => 'error', 'message' => 'Please explain why you want this role. The reason field cannot be empty.']);
        exit;
    }

    $valid_roles = ['Reviewer', 'UniRep'];
    if (!in_array($role, $valid_roles)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid role selected.']);
        exit;
    }

    // 2. CHECK EXISTING ROLE
    $user_check = $conn->prepare("SELECT role FROM users WHERE s_id = ?");
    $user_check->bind_param("i", $s_id);
    $user_check->execute();
    $user_result = $user_check->get_result();

    if ($user_result->num_rows > 0) {
        $user_row = $user_result->fetch_assoc();
        $current_user_role = $user_row['role']; 

        if ($current_user_role === 'Reviewer' || $current_user_role === 'UniRep') {
            echo json_encode([
                'status' => 'error', 
                'message' => "You are already a $current_user_role.\nYou cannot submit a new application."
            ]);
            exit;
        }
    }
    $user_check->close();

    // 3. CHECK PENDING APPLICATIONS
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

    // 4. INSERT APPLICATION
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

include '../html/ApplyRole.php';
?>