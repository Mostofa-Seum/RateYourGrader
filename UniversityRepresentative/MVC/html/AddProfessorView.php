<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Professor - Rate Your Grader</title>
    <link rel="stylesheet" href="../css/AddProfessor.css">
</head>
<body>

<nav class="navbar">
    <div class="container nav-container">
        <div class="logo-wrapper">
            <div class="logo-icon">
                <img src="../images/scolar_cap.png" alt="Logo" class="logo-img">
            </div>
            <span class="logo-text">Rate Your Grader</span>
        </div>
        
        <div class="nav-links">
            <a href="../../../Common/MVC/php/HomePage.php">Home</a>
            <a href="UniversityRepDashboard.php">Dashboard</a> 
            <a href="../../../Common/MVC/php/Logout.php" class="btn btn-primary" style="background-color: #dc3545; color: white;">Logout</a>
        </div>
    </div>
</nav>

<main class="page-container">
    <div class="container">
        <a href="UniversityRepDashboard.php" class="back-link">← Back to Dashboard</a>

        <div class="form-card">
            <div class="form-header">
                <h2>Add New Faculty</h2>
                <p>Register a new professor and assign an existing course.</p>
            </div>

            <form id="addProfForm" onsubmit="return false;">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="name">Professor Name</label>
                        <input type="text" id="name" name="name" placeholder="e.g. Dr. John Doe" required>
                    </div>

                    <div class="form-group">
                        <label for="department">Department</label>
                        <input type="text" id="department" name="department" placeholder="e.g. CSE" required>
                    </div>

                    <div class="form-group">
                        <label for="university">University Name</label>
                        <input type="text" id="university" name="university" placeholder="e.g. University of Dhaka" required>
                    </div>

                    <div class="form-group">
                        <label for="c_id">Course ID</label>
                        <input type="text" id="c_id" name="c_id" placeholder="e.g. CSE101" oninput="fetchCourseName()" required>
                    </div>

                    <div class="form-group full-width">
                        <label for="course_name">Course Name</label>
                        <input type="text" id="course_name" name="course_name" placeholder="Waiting for valid Course ID..." disabled required>
                    </div>
                </div>

                <div id="response-message" class="message-box"></div>

                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="window.location.href='UniversityRepDashboard.php'">Cancel</button>
                    <button type="submit" class="btn btn-primary" onclick="submitProfessor()">Add Professor</button>
                </div>
            </form>
        </div>
    </div>
</main>

<div id="toast-box" class="toast-box"></div>

<footer>
    <div class="container">
        <div class="footer-grid">
            <div class="footer-brand">
                <div class="logo-wrapper mb-2">
                    <div class="logo-icon small">
                        <img src="../images/scolar_cap.png" alt="Logo" class="logo-img">
                    </div>
                    <span class="footer-logo-text">Rate Your Grader</span>
                </div>
                <p>Empowering students with transparent grading information since 2024.</p>
            </div>

            <div class="footer-actions">
                <h5>Apply</h5>
                <div class="footer-buttons">
                    <?php 
                    if ($user_role === 'Reviewer') {
                        echo '<span class="footer-nav-link" style="cursor: default; color: #6c757d;">You are a Reviewer</span>';
                    } else {
                        echo '<a href="../../../Common/MVC/php/ApplyRole.php" class="footer-nav-link">Apply for Reviewer</a>';
                    }

                    if ($user_role === 'UniRep') {
                        echo '<span class="footer-nav-link" style="cursor: default; color: #6c757d;">You are a University Representative</span>';
                    } else {
                        echo '<a href="../../../Common/MVC/php/ApplyRole.php" class="footer-nav-link">Apply for University Representative</a>';
                    }
                    ?>
                </div>
            </div>

            <div class="footer-socials">
                <h5>Our Socials</h5>
                <div class="social-icons">
                        <a href="https://www.facebook.com"><img src="../images/facebook.png" alt="Facebook" class="social-icon"></a>
                        <a href="https://www.instagram.com"><img src="../images/instagram.png" alt="Instagram" class="social-icon"></a>
                        <a href="https://www.twitter.com"><img src="../images/twitter.png" alt="Twitter" class="social-icon"></a>
                    </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2026 Rate Your Grader. All rights reserved.</p>
        </div>
    </div>
</footer>

<script src="../js/AddProfessor.js"></script>
</body>
</html>