<?php
// UniversityRepDashboard.php (Controller)
session_start();
include '../db/Config.php';

//HANDLE AJAX REQUESTS
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    if (!isset($_SESSION['s_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'You are not logged in.']);
        exit;
    }
    $user_id = $_SESSION['s_id'];
    $action = $_POST['action'] ?? '';

    //PROFILE ACTIONS
    if ($action === 'update_username') {
        $new_username = trim($_POST['username']);
        if (empty($new_username)) { echo json_encode(['status' => 'error', 'message' => 'Username empty.']); exit; }
        $stmt = $conn->prepare("UPDATE users SET Username = ? WHERE s_id = ?");
        $stmt->bind_param("si", $new_username, $user_id);
        if ($stmt->execute()) echo json_encode(['status' => 'success', 'message' => 'Username updated!']);
        else echo json_encode(['status' => 'error', 'message' => 'Database error.']);
        $stmt->close();
        exit;
    }
    if ($action === 'update_password') {
        $current = $_POST['current_password'];
        $new = $_POST['new_password'];
        $confirm = $_POST['confirm_password'];
        if ($new !== $confirm) { echo json_encode(['status' => 'error', 'message' => 'Passwords do not match.']); exit; }
        $stmt = $conn->prepare("SELECT Password FROM users WHERE s_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $u = $res->fetch_assoc();
        if ($u && $u['Password'] === $current) {
            $up = $conn->prepare("UPDATE users SET Password = ? WHERE s_id = ?");
            $up->bind_param("si", $new, $user_id);
            if ($up->execute()) echo json_encode(['status' => 'success', 'message' => 'Password changed!']);
            else echo json_encode(['status' => 'error', 'message' => 'Update failed.']);
        } else { echo json_encode(['status' => 'error', 'message' => 'Incorrect password.']); }
        exit;
    }

    // COURSE MANAGEMENT ACTIONS
    if ($action === 'get_courses') {
        $sql = "SELECT serial, c_id, `Course Name` as course_name FROM courses ORDER BY c_id ASC";
        $result = $conn->query($sql);
        $courses = [];
        while ($row = $result->fetch_assoc()) $courses[] = $row;
        echo json_encode(['status' => 'success', 'data' => $courses]);
        exit;
    }
    if ($action === 'add_course') {
        $c_id = trim($_POST['c_id']);
        $c_name = trim($_POST['c_name']);
        if(empty($c_id) || empty($c_name)) { echo json_encode(['status' => 'error', 'message' => 'ID and Name required.']); exit; }
        $chk = $conn->prepare("SELECT serial FROM courses WHERE c_id = ? OR `Course Name` = ?");
        $chk->bind_param("ss", $c_id, $c_name);
        $chk->execute();
        if($chk->get_result()->num_rows > 0) { echo json_encode(['status' => 'error', 'message' => 'Duplicate found!']); exit; }
        $stmt = $conn->prepare("INSERT INTO courses (c_id, `Course Name`) VALUES (?, ?)");
        $stmt->bind_param("ss", $c_id, $c_name);
        if($stmt->execute()) echo json_encode(['status' => 'success', 'message' => 'Course added.']);
        else echo json_encode(['status' => 'error', 'message' => 'DB Error.']);
        exit;
    }
    if ($action === 'update_course') {
        $serial = intval($_POST['serial']);
        $c_id = trim($_POST['c_id']);
        $c_name = trim($_POST['c_name']);
        $chk = $conn->prepare("SELECT serial FROM courses WHERE (c_id = ? OR `Course Name` = ?) AND serial != ?");
        $chk->bind_param("ssi", $c_id, $c_name, $serial);
        $chk->execute();
        if($chk->get_result()->num_rows > 0) { echo json_encode(['status' => 'error', 'message' => 'Duplicate found!']); exit; }
        $stmt = $conn->prepare("UPDATE courses SET c_id = ?, `Course Name` = ? WHERE serial = ?");
        $stmt->bind_param("ssi", $c_id, $c_name, $serial);
        if($stmt->execute()) echo json_encode(['status' => 'success', 'message' => 'Updated.']);
        else echo json_encode(['status' => 'error', 'message' => 'DB Error.']);
        exit;
    }
    if ($action === 'delete_course') {
        $serial = intval($_POST['serial']);
        $stmt = $conn->prepare("DELETE FROM courses WHERE serial = ?");
        $stmt->bind_param("i", $serial);
        if($stmt->execute()) echo json_encode(['status' => 'success', 'message' => 'Deleted.']);
        else echo json_encode(['status' => 'error', 'message' => 'DB Error.']);
        exit;
    }
    exit;
}

//PAGE LOAD
if (!isset($_SESSION['s_id'])) { header("Location: Login.php"); exit(); }
$current_user_id = $_SESSION['s_id'];

//Get User Info
$stmt = $conn->prepare("SELECT Username, Email, role FROM users WHERE s_id = ?");
$stmt->bind_param("i", $current_user_id);
$stmt->execute();
$user_data = $stmt->get_result()->fetch_assoc();
$user_role = $user_data['role']; 
$stmt->close();

//Counts
$sql_count = "SELECT COUNT(DISTINCT Name, Department, University) as count FROM professors";
$res_count = $conn->query($sql_count);
$total_profs = ($res_count && $row = $res_count->fetch_assoc()) ? $row['count'] : "0";

$sql_course_count = "SELECT COUNT(*) as count FROM courses";
$res_c_count = $conn->query($sql_course_count);
$total_courses = ($res_c_count && $row = $res_c_count->fetch_assoc()) ? $row['count'] : "0";

//CONNECT TO VIEW
include '../html/UniversityRepDashboardView.php';
?>