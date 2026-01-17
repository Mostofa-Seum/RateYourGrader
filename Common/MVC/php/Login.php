<?php
session_start(); // Start session to use $_SESSION variables

// Initialize variables
$name = "";
$email = "";
$signup_error = "";
$signup_success = "";
$login_error = "";        
$login_success_name = ""; 
$show_signup_form = false; 

// Path to DB Config: Step out of 'php', into 'db'
include "../db/Config.php";

if (isset($_GET['signup']) && $_GET['signup'] === 'success') {
    $signup_success = "Account created successfully! Please login.";
    $show_signup_form = false; // Show login form
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // ==========================================
    // SIGNUP LOGIC
    // ==========================================
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

    // ==========================================
    // LOGIN LOGIC
    // ==========================================
    if (isset($_POST['action']) && $_POST['action'] == 'login') {
        
        $login_email = $conn->real_escape_string($_POST['login_email']);
        $login_pass  = $_POST['login_password'];

        // ---------------------------------------------------------
        // 1. HARDCODED ADMIN CHECK (FIXED)
        // ---------------------------------------------------------
        if ($login_email === 'admin' && $login_pass === 'admin') {
            $_SESSION['user_name'] = "System Admin";
            $_SESSION['role'] = "Admin"; 
            
            // CRITICAL FIX: The dashboard checks if 's_id' is set. 
            // We must give the hardcoded admin a dummy ID (e.g., 0).
            $_SESSION['s_id'] = 0; 
            
            // Redirect to Admin Dashboard
            header("Location: ../../../Admin/MVC/php/AdminDashboard.php");
            exit();
        } else {
            // -----------------------------------------------------
            // 2. DATABASE USER CHECK
            // -----------------------------------------------------
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

                    // --- ROLE BASED REDIRECT ---
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/Login.css">
    <title>Signin & Signup</title>
    <style>
        .error-msg {
            color: #721c24; background-color: #f8d7da; border: 1px solid #f5c6cb;
            padding: 10px; border-radius: 5px; margin-bottom: 15px; font-size: 0.9rem;
            text-align: center; width: 100%;
        }
        .success-msg {
            color: #155724; background-color: #d4edda; border: 1px solid #c3e6cb;
            padding: 10px; border-radius: 5px; margin-bottom: 15px; font-size: 0.9rem;
            text-align: center; width: 100%;
        }
        .hello-msg {
            font-size: 2rem;
            color: #333;
            text-align: center;
            margin-top: 50px;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="auth-container <?php echo $show_signup_form ? 'signup-mode' : ''; ?>" id="authContainer">
            
            <div class="slider <?php echo $show_signup_form ? 'active' : ''; ?>" id="slider">
                <div class="slider-content">
                    <h2 id="sliderTitle">
                        <?php echo $show_signup_form ? 'Hello!' : 'Welcome Back!'; ?>
                    </h2>
                    <p id="sliderText">
                        <?php echo $show_signup_form 
                            ? 'Enter your personal details and start your journey with us' 
                            : 'To keep connected with us please login with your personal info'; ?>
                    </p>
                    <button class="slider-btn" id="sliderBtn">
                        <?php echo $show_signup_form ? 'Sign In' : 'Sign Up'; ?>
                    </button>
                </div>
            </div>
 
            <div class="form-container login-form">
                
                <?php if (!empty($login_success_name)): ?>
                    <div class="hello-msg">
                        Hello <?php echo htmlspecialchars($login_success_name); ?>
                    </div>
                <?php else: ?>

                    <form class="form" action="" method="POST" novalidate>
                        <h2>Sign In</h2>
                        <input type="hidden" name="action" value="login">

                        <?php if (!empty($signup_success)): ?>
                            <div class="success-msg"><?php echo $signup_success; ?></div>
                        <?php endif; ?>

                        <?php if (!empty($login_error)): ?>
                            <div class="error-msg"><?php echo $login_error; ?></div>
                        <?php endif; ?>
                        
                        <div class="input-group">
                            <input type="text" id="email" name="login_email" required>
                            <label>Email</label>
                        </div>
                        
                        <div class="input-group">
                            <input type="password" id="password" name="login_password" required>
                            <label>Password</label>
                        </div>

                        <div class="forgot-pass-container">
                            <a href="ForgotPassword.php" class="forgot-pass-link">Forgot Password?</a>
                        </div>
                        
                        <button type="submit" class="submit-btn">Sign In</button>
                    </form>

                <?php endif; ?>
            </div>
 
            <div class="form-container signup-form">
                <form class="form" action="" method="POST" novalidate>
                    <h2>Create Account</h2>

                    <input type="hidden" name="action" value="signup">

                    <?php if (!empty($signup_error)): ?>
                        <div class="error-msg"><?php echo $signup_error; ?></div>
                    <?php endif; ?>
                    
                    <div class="input-group">
                        <input type="text" id="nameInput" name="name" value="<?php echo $name; ?>" required>
                        <label>Name</label>
                    </div>
                    
                    <div class="input-group">
                        <input type="text" id="emailInput" name="email" value="<?php echo $email; ?>" required>
                        <label>Email</label>
                    </div>
                    
                    <div class="input-group">
                        <input type="password" id="passwordInput" name="password" required>
                        <label>Password</label>
                    </div>

                    <div class="input-group">
                        <input type="password" id="confirmPasswordInput" name="confirmPassword" required>
                        <label>Confirm Password</label>
                    </div>

                    <button type="submit" class="submit-btn">Sign Up</button>
                </form>
            </div>
        </div>
    </div>
    <script src="../js/Login.js"></script>
</body>
</html>