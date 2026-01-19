<?php
session_start(); 


include "../db/Config.php";

// Initialize variables
$name = "";
$email = "";
$signup_error = "";
$signup_success = "";
$login_error = "";        
$login_success_name = ""; 
$show_signup_form = false; 

if (isset($_GET['signup']) && $_GET['signup'] === 'success') {
    $signup_success = "Account created successfully! Please login.";
    $show_signup_form = false; // Show login form
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // SIGNUP LOGIC

    if (isset($_POST['action']) && $_POST['action'] == 'signup') {
        
        // Sanitize and Escape inputs for Database Safety
        $name = $conn->real_escape_string(htmlspecialchars(trim($_POST['name'])));
        $email = $conn->real_escape_string(htmlspecialchars(trim($_POST['email'])));
        $password = $_POST['password'];
        $confirmPassword = $_POST['confirmPassword'];

        // Validation
        if (empty($name) || empty($email) || empty($password) || empty($confirmPassword)) {
            $signup_error = "All fields are required!";
            $show_signup_form = true; 
        } elseif ($password !== $confirmPassword) {
            $signup_error = "Passwords do not match!";
            $show_signup_form = true; 
        } elseif (strlen($password) < 6) {
            $signup_error = "Password must be at least 6 characters long!";
            $show_signup_form = true;
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $signup_error = "Invalid email format!";
            $show_signup_form = true;
        } else {
            // Check for duplicates
            $check_sql = "SELECT * FROM users WHERE Username = '$name' OR Email = '$email'";
            $check_result = $conn->query($check_sql);

            if ($check_result->num_rows > 0) {
                $signup_error = "Username or Email is already taken!";
                $show_signup_form = true;
            } else {
                // Determine default role
                $default_role = 'Student';

                // Insert new user
                $sql = "INSERT INTO users (Username, Password, Email, role) 
                        VALUES ('$name', '$password', '$email', '$default_role')";

                if ($conn->query($sql) === TRUE) {
                    header("Location: " . $_SERVER['PHP_SELF'] . "?signup=success");
                    exit();
                } else {
                    $signup_error = "Error: " . $conn->error;
                    $show_signup_form = true;
                }
            }
        }
    }


    // LOGIN LOGIC

    if (isset($_POST['action']) && $_POST['action'] == 'login') {
        
        $login_email = $conn->real_escape_string($_POST['login_email']);
        $login_pass  = $_POST['login_password'];


        // ADMIN CHECK

        if ($login_email === 'admin' && $login_pass === 'admin') {
            $_SESSION['user_name'] = "System Admin";
            $_SESSION['role'] = "Admin"; 
            $_SESSION['s_id'] = 0; 
            
            // Redirect to Admin Dashboard
            header("Location: ../../../Admin/MVC/php/AdminDashboard.php");
            exit();
        } else {

            // 2. DATABASE USER CHECK

            $sql = "SELECT * FROM users WHERE Email = '$login_email'";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                
                // Compare passwords
                if ($login_pass === $row['Password']) {
                    
                    // Save Session Data
                    $_SESSION['user_name'] = $row['Username']; 
                    $_SESSION['s_id'] = $row['s_id']; 
                    $_SESSION['role'] = $row['role']; 

                    //  ROLE BASED REDIRECT 
                    $user_role = $row['role']; // Ensure database role is capitalized like 'Admin'

                    if ($user_role === 'Student') {
                        header("Location: HomePage.php");
                        exit();
                    } 
                    elseif ($user_role === 'Reviewer') {
                        header("Location: ../../../Reviewer/MVC/php/ReviewerDashboard.php");
                        exit();
                    } 
                    // FIXED ADMIN REDIRECT LOGIC
                    elseif ($user_role === 'Admin') {
                        header("Location: ../../../Admin/MVC/php/AdminDashboard.php");
                        exit();
                    } 
                    else {
                        // Fallback
                        header("Location: HomePage.php?login=success"); 
                        exit();
                    }

                } else {
                    $login_error = "Incorrect Password";
                }
            } else {
                $login_error = "User not found";
            }
        }
    }
}

include '../html/Login.php';
?>