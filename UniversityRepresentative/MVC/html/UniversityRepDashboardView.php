<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>University Representative Dashboard</title>
    <link rel="stylesheet" href="../css/UniversityRepDashboard.css">
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
            <a href="SearchOutput.php">Search Graders</a>
            <a href="../../../Common/MVC/php/Logout.php" class="btn btn-primary" style="background-color: #dc3545; color: white;">Logout</a>
        </div>
    </div>
</nav>

<main class="dashboard-page">
    <div class="container">
        <div class="dashboard-grid">
            <div class="card profile-card">
                <div class="profile-avatar"><img src="../images/aiden.png" alt="Profile Avatar"></div>
                <div class="profile-name"><?php echo htmlspecialchars($user_data['Username']); ?></div>
                <div class="profile-email"><?php echo htmlspecialchars($user_data['Email']); ?></div>
                
                <button class="edit-profile-btn" onclick="toggleEditProfileSection()">Edit Profile</button>
                
                <button class="btn btn-secondary" style="width: 100%; margin-top: 10px;" onclick="window.location.href='../../../Student/MVC/php/UserDashboard.php'">
                    Switch to Student View
                </button>
            </div>

            <div class="stats-section">
                <h2>University Management</h2>
                <div class="stats-grid">
                    <div class="stat-card action-card add-card" onclick="window.location.href='AddProfessor.php'">
                        <div class="stat-icon">➕</div>
                        <div class="stat-label">Add Professor</div>
                    </div>
                    <div class="stat-card action-card manage-card" onclick="window.location.href='ManageFaculty.php'">
                        <div class="stat-icon">📋</div>
                        <div class="stat-label">Manage Faculty</div>
                        <div class="small-text"><?php echo $total_profs; ?> Listed</div>
                    </div>
                    <div class="stat-card action-card course-card" onclick="openCourseModal()">
                        <div class="stat-icon">📚</div>
                        <div class="stat-label">Manage Courses</div>
                        <div class="small-text"><?php echo $total_courses; ?> Available</div>
                    </div>
                    <div class="stat-card action-card analytics-card" onclick="window.location.href='UniversityAnalytics.php'">
                        <div class="stat-icon">📊</div>
                        <div class="stat-label">University Analytics</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card" id="edit-profile-section" style="display: none;">
            <div class="form-section">
                <h3>Account Settings</h3>
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" value="<?php echo htmlspecialchars($user_data['Username']); ?>">
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" value="<?php echo htmlspecialchars($user_data['Email']); ?>" disabled>
                </div>
                <div id="username-message" class="message-box"></div> 
                <div class="button-group">
                    <button class="btn btn-primary" onclick="updateUsername()">Update Username</button>
                    <button class="btn btn-secondary" onclick="toggleEditProfileSection()">Cancel</button>
                </div>
            </div>
            <div class="form-section">
                <h3>Change Password</h3>
                <div class="form-group"><label>Current Password</label><input type="password" id="current-password"></div>
                <div class="form-group"><label>New Password</label><input type="password" id="new-password"></div>
                <div class="form-group"><label>Confirm New Password</label><input type="password" id="confirm-password"></div>
                <div id="password-message" class="message-box"></div>
                <div class="button-group">
                    <button class="btn btn-primary" onclick="updatePassword()">Update Password</button>
                    <button class="btn btn-secondary" onclick="resetPasswordForm()">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <div id="courseModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Manage Courses</h2>
                <button class="close-modal" onclick="closeCourseModal()">×</button>
            </div>
            <div class="modal-body">
                <div class="course-form-row">
                    <input type="hidden" id="course_serial"> 
                    <input type="text" id="course_id_input" placeholder="Course ID (e.g. CSE101)" class="sm-input">
                    <input type="text" id="course_name_input" placeholder="Course Name (e.g. Intro to CS)" class="lg-input">
                    <button class="btn btn-primary" id="saveCourseBtn" onclick="saveCourse()">Add</button>
                    <button class="btn btn-secondary" id="cancelEditBtn" onclick="resetCourseForm()" style="display:none;">Cancel</button>
                </div>
                <div id="course-message" class="message-box"></div>
                <hr class="divider">
                <div class="table-responsive">
                    <table class="course-table">
                        <thead>
                            <tr><th>ID</th><th>Course Name</th><th>Actions</th></tr>
                        </thead>
                        <tbody id="courseTableBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

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
            <div class="footer-bottom"><p>&copy; 2026 Rate My Grader. All rights reserved.</p></div>
        </div>
    </footer>
</main>
<script src="../js/UniversityRepDashboard.js"></script>
</body>
</html>