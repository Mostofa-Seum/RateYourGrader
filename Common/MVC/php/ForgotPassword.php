<?php
session_start();
// Path to DB Config
include "../db/Config.php"; 

$error_msg = "";
$success_msg = "";
$step = 1; // Default step: Verification

// Handle Form Submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // STEP 1: VERIFY USER
    if (isset($_POST['action']) && $_POST['action'] == 'verify') {
        $username = $conn->real_escape_string($_POST['username']);
        $email = $conn->real_escape_string($_POST['email']);

        // Check if user exists
        $sql = "SELECT * FROM users WHERE Username = '$username' AND Email = '$email'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            // User found! Move to Step 2
            $step = 2;
            $_SESSION['reset_email'] = $email; 
        } else {
            $error_msg = "No account found with that Name and Email combination.";
        }
    }

    // STEP 2: RESET PASSWORD
    if (isset($_POST['action']) && $_POST['action'] == 'reset') {
        $pass = $_POST['password'];
        $confirm_pass = $_POST['confirm_password'];
        $email = $_SESSION['reset_email'] ?? '';

        if (empty($email)) {
            $error_msg = "Session expired. Please try again.";
            $step = 1;
        } elseif (strlen($pass) < 6) {
            $error_msg = "Password must be at least 6 characters long!";
            $step = 2; 
        } elseif ($pass !== $confirm_pass) {
            $error_msg = "Passwords do not match!";
            $step = 2; 
        } else {
            // Update Password
            $update_sql = "UPDATE users SET Password = '$pass' WHERE Email = '$email'";
            
            if ($conn->query($update_sql) === TRUE) {
                $success_msg = "Password updated successfully! Redirecting to login...";
                $success_msg .= "<script>setTimeout(function(){ window.location.href = 'Login.php'; }, 3000);</script>";
                session_unset();
                session_destroy();
            } else {
                $error_msg = "Error updating record: " . $conn->error;
            }
        }
    }
}

// Load the View (HTML)
include '../html/ForgotPassword.php';
?>