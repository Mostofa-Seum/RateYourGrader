<?php
session_start();
// Path: Step out of 'php', into 'db'
include '../db/Config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['s_id'])) {
    // For testing/fallback
    $user_id = 1001; 
} else {
    $user_id = $_SESSION['s_id'];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'];

    // --- CASE 1: UPDATE USERNAME ---
    if ($action == 'update_username') {
        $new_username = $_POST['username'];

        if (empty($new_username)) {
            echo json_encode(['status' => 'error', 'message' => 'Username cannot be empty']);
            exit();
        }

        $stmt = $conn->prepare("UPDATE users SET Username = ? WHERE s_id = ?");
        $stmt->bind_param("si", $new_username, $user_id);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Username updated successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Database error']);
        }
        $stmt->close();
    }

    // --- CASE 2: UPDATE PASSWORD ---
    elseif ($action == 'update_password') {
        $current_pass = $_POST['current_password'];
        $new_pass = $_POST['new_password'];
        $confirm_pass = $_POST['confirm_password'];

        if (empty($current_pass) || empty($new_pass) || empty($confirm_pass)) {
            echo json_encode(['status' => 'error', 'message' => 'All password fields are required']);
            exit();
        }

        if ($new_pass !== $confirm_pass) {
            echo json_encode(['status' => 'error', 'message' => 'New passwords do not match']);
            exit();
        }

        $stmt = $conn->prepare("SELECT Password FROM users WHERE s_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $db_password = $row['Password'];

            if ($current_pass !== $db_password) {
                echo json_encode(['status' => 'error', 'message' => 'Current password is incorrect']);
                exit();
            }

            $update_stmt = $conn->prepare("UPDATE users SET Password = ? WHERE s_id = ?");
            $update_stmt->bind_param("si", $new_pass, $user_id);
            
            if ($update_stmt->execute()) {
                echo json_encode(['status' => 'success', 'message' => 'Password changed successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to update password']);
            }
            $update_stmt->close();
        } else {
            echo json_encode(['status' => 'error', 'message' => 'User not found']);
        }
        $stmt->close();
    }
}
$conn->close();
?>