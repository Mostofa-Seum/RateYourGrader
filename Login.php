<?php
// Initialize variables
$name = "";
$email = "";
$signup_error = "";
$signup_success = "";
$login_error = "";        
$login_success_name = ""; 
$show_signup_form = false; 

include "loginconfig.php";


if (isset($_GET['signup']) && $_GET['signup'] === 'success') {
    $signup_success = "Account created successfully! Please login.";
    $show_signup_form = false; // Show login form
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (isset($_POST['action']) && $_POST['action'] == 'signup') {
        
        $name = htmlspecialchars(trim($_POST['name']));
        $email = htmlspecialchars(trim($_POST['email']));
        $password = $_POST['password'];
        $confirmPassword = $_POST['confirmPassword'];

        
        if (empty($name) || empty($email) || empty($password) || empty($confirmPassword)) {
            $signup_error = "All fields are required!";
            $show_signup_form = true; 
        } elseif ($password !== $confirmPassword) {
            $signup_error = "Passwords do not match!";
            $show_signup_form = true; 
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $signup_error = "Invalid email format!";
            $show_signup_form = true;
        } elseif (strlen($password) < 6) {
            $signup_error = "Password must be at least 6 characters long!";
            $show_signup_form = true;
        }

         else {
            
            $sql = "INSERT INTO users (username, password, email)
                    VALUES ('$name', '$password', '$email')";

            if ($conn->query($sql) === TRUE) {
                header("Location: " . $_SERVER['PHP_SELF'] . "?signup=success");
                exit();
            } else {
                $signup_error = "Error: " . $conn->error;
                $show_signup_form = true;
            }
        }
    }

if (isset($_POST['action']) && $_POST['action'] == 'login') {
        
        $login_email = $conn->real_escape_string($_POST['login_email']);
        $login_pass  = $_POST['login_password'];


        if ($login_email === 'admin' && $login_pass === 'admin') {
            $login_success_name = "admin";
        }

        else {
            $sql = "SELECT * FROM users WHERE email = '$login_email'";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                
                if ($login_pass === $row['password']) {
                    $login_success_name = $row['username'];
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
    <link rel="stylesheet" href="login.css">
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
        /* Style for the Hello message */
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
    <script src="login.js"></script>
</body>
</html>