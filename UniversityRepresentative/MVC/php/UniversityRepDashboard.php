<?php
session_start();
include '../db/Config.php';

// --- 1. HANDLE AJAX REQUESTS ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    if (!isset($_SESSION['s_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'You are not logged in.']);
        exit;
    }
    $user_id = $_SESSION['s_id'];
    $action = $_POST['action'] ?? '';

    // --- PROFILE ACTIONS ---
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

    // --- COURSE MANAGEMENT ACTIONS ---
    
    // 1. Get All Courses
    if ($action === 'get_courses') {
        $sql = "SELECT serial, c_id, `Course Name` as course_name FROM courses ORDER BY c_id ASC";
        $result = $conn->query($sql);
        $courses = [];
        while ($row = $result->fetch_assoc()) {
            $courses[] = $row;
        }
        echo json_encode(['status' => 'success', 'data' => $courses]);
        exit;
    }

    // 2. Add New Course
    if ($action === 'add_course') {
        $c_id = trim($_POST['c_id']);
        $c_name = trim($_POST['c_name']);

        if(empty($c_id) || empty($c_name)) {
            echo json_encode(['status' => 'error', 'message' => 'ID and Name required.']); exit;
        }

        // Check Duplicates
        $chk = $conn->prepare("SELECT serial FROM courses WHERE c_id = ? OR `Course Name` = ?");
        $chk->bind_param("ss", $c_id, $c_name);
        $chk->execute();
        if($chk->get_result()->num_rows > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Course ID or Name already exists!']); exit;
        }

        $stmt = $conn->prepare("INSERT INTO courses (c_id, `Course Name`) VALUES (?, ?)");
        $stmt->bind_param("ss", $c_id, $c_name);
        if($stmt->execute()) echo json_encode(['status' => 'success', 'message' => 'Course added successfully.']);
        else echo json_encode(['status' => 'error', 'message' => 'DB Error.']);
        exit;
    }

    // 3. Edit Existing Course
    if ($action === 'update_course') {
        $serial = intval($_POST['serial']);
        $c_id = trim($_POST['c_id']);
        $c_name = trim($_POST['c_name']);

        // Check Duplicates (Excluding current row)
        $chk = $conn->prepare("SELECT serial FROM courses WHERE (c_id = ? OR `Course Name` = ?) AND serial != ?");
        $chk->bind_param("ssi", $c_id, $c_name, $serial);
        $chk->execute();
        if($chk->get_result()->num_rows > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Course ID or Name already exists!']); exit;
        }

        $stmt = $conn->prepare("UPDATE courses SET c_id = ?, `Course Name` = ? WHERE serial = ?");
        $stmt->bind_param("ssi", $c_id, $c_name, $serial);
        if($stmt->execute()) echo json_encode(['status' => 'success', 'message' => 'Course updated successfully.']);
        else echo json_encode(['status' => 'error', 'message' => 'DB Error.']);
        exit;
    }

    // 4. Delete Course
    if ($action === 'delete_course') {
        $serial = intval($_POST['serial']);
        $stmt = $conn->prepare("DELETE FROM courses WHERE serial = ?");
        $stmt->bind_param("i", $serial);
        if($stmt->execute()) echo json_encode(['status' => 'success', 'message' => 'Course deleted.']);
        else echo json_encode(['status' => 'error', 'message' => 'DB Error.']);
        exit;
    }

    exit;
}

// --- 2. PAGE LOAD & ANALYTICS ---
if (!isset($_SESSION['s_id'])) { header("Location: Login.php"); exit(); }
$current_user_id = $_SESSION['s_id'];

// Get User Info
$stmt = $conn->prepare("SELECT Username, Email FROM users WHERE s_id = ?");
$stmt->bind_param("i", $current_user_id);
$stmt->execute();
$user_data = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Counts
$sql_count = "SELECT COUNT(DISTINCT Name, Department, University) as count FROM professors";
$res_count = $conn->query($sql_count);
$total_profs = ($res_count && $row = $res_count->fetch_assoc()) ? $row['count'] : "0";

$sql_course_count = "SELECT COUNT(*) as count FROM courses";
$res_c_count = $conn->query($sql_course_count);
$total_courses = ($res_c_count && $row = $res_c_count->fetch_assoc()) ? $row['count'] : "0";

?>

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
                
                <button class="btn btn-secondary" style="width: 100%; margin-top: 10px;" onclick="window.location.href='../../../User/MVC/php/UserDashboard.php'">
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
                            <tr>
                                <th>ID</th>
                                <th>Course Name</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="courseTableBody">
                            </tbody>
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
                        <a href="#" class="footer-nav-link">Apply for Reviewer</a>
                        <a href="#" class="footer-nav-link">Apply for University Representative</a>
                    </div>
                </div>
                <div class="footer-socials">
                    <h5>Our Socials</h5>
                    <div class="social-icons">
                        <a href="#"><img src="../images/facebook.png" class="social-icon"></a>
                        <a href="#"><img src="../images/instagram.png" class="social-icon"></a>
                        <a href="#"><img src="../images/twitter.png" class="social-icon"></a>
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