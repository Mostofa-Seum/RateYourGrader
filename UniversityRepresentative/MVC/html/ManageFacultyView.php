<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Faculty</title>
    <link rel="stylesheet" href="../css/ManageFaculty.css">
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
        <button class="mobile-menu-btn">
            <img src="../images/menu.png" alt="Menu" class="mobile-menu-icon">
        </button>
    </div>
</nav>

<main class="page-container">
    <div class="container">
        <div class="header-flex">
            <div>
                <a href="UniversityRepDashboard.php" class="back-link">← Back to Dashboard</a>
                <h2 class="page-title">Manage Faculty</h2>
            </div>
            <a href="AddProfessor.php" class="btn btn-primary">Add New Faculty</a>
        </div>

        <div class="faculty-grid">
            <?php
            $avatars = [
                '../images/aiden.png', '../images/emma.png', '../images/lucas.png', '../images/sophia.png',
                '../images/mia.png', '../images/james.png', '../images/olivia.png', '../images/ethan.png'
            ];
            $defaultAvatar = '../images/aiden.png';

            if ($faculty_result && $faculty_result->num_rows > 0) {
                while($row = $faculty_result->fetch_assoc()) {
                    $index = $row['P_id'] % count($avatars);
                    $selectedAvatar = $avatars[$index];
                    
                    $courseDisplay = $row['CourseNames'] ? htmlspecialchars($row['CourseNames']) : 'No Assigned Courses';
                    if (strlen($courseDisplay) > 40) $courseDisplay = substr($courseDisplay, 0, 40) . '...';

                    echo '
                    <div class="faculty-card" onclick="openEditModal('.$row['P_id'].')">
                        <img src="'.$selectedAvatar.'" onerror="this.src=\''.$defaultAvatar.'\'" alt="Prof" class="card-avatar">
                        <h3 class="card-name">'.htmlspecialchars($row['Name']).'</h3>
                        <div class="card-detail">
                            <span class="icon">🏛</span> '.htmlspecialchars($row['University']).'
                        </div>
                        <div class="card-detail">
                            <span class="icon">🎓</span> '.htmlspecialchars($row['Department']).'
                        </div>
                        <div class="card-detail courses-preview">
                            <span class="icon">📚</span> '.$courseDisplay.'
                        </div>
                        <div class="card-footer">
                            <span class="edit-text">Edit / Manage Courses</span>
                        </div>
                    </div>';
                }
            } else {
                echo '<p class="empty-msg">No faculty members found.</p>';
            }
            ?>
        </div>
    </div>
</main>

<div class="modal" id="editModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Edit Faculty & Courses</h2>
            <button class="close-modal" onclick="closeEditModal()">×</button>
        </div>
        
        <div class="scrollable-body">
            <form id="editForm" onsubmit="return false;">
                <input type="hidden" id="edit_p_id">
                <div class="form-group"><label>Name</label><input type="text" id="edit_name"></div>
                <div class="form-group"><label>Department</label><input type="text" id="edit_dept"></div>
                <div class="form-group"><label>University</label><input type="text" id="edit_uni"></div>
                
                <div class="modal-actions">
                    <button class="btn btn-danger" onclick="deleteProfessor()">Delete Professor</button>
                    <button class="btn btn-primary" onclick="updateProfessor()">Update Details</button>
                </div>
            </form>

            <hr class="divider">

            <div class="course-management-section">
                <h3>Assigned Courses</h3>
                <div id="course-list-container" class="course-list"></div>
                <h4 style="margin-top:1.5rem; color:#444;">Assign New Course</h4>
                <div class="add-course-row">
                    <input type="text" id="new_course_id" placeholder="Course ID (e.g. CSE101)" class="sm-input" oninput="fetchCourseName()">
                    <input type="text" id="new_course_name" placeholder="Waiting for ID..." class="lg-input" disabled>
                    <button class="btn btn-secondary" onclick="addCourse()">Add</button>
                </div>
            </div>
        </div>
    </div>
</div>

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
        <div class="footer-bottom"><p>&copy; 2026 Rate Your Grader. All rights reserved.</p></div>
    </div>
</footer>

<script src="../js/ManageFaculty.js"></script>
</body>
</html>