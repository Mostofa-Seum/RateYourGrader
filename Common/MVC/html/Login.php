<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/Login.css">
    <title>Signin & Signup</title>
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