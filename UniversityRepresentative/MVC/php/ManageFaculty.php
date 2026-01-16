<?php
// Turn off output buffering to prevent whitespace issues breaking JSON
ob_start();
session_start();
include '../db/Config.php';

// --- HANDLE AJAX REQUESTS ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    ob_clean(); // Clean buffer before sending JSON
    header('Content-Type: application/json');

    if (!isset($_SESSION['s_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        exit;
    }

    $action = $_POST['action'] ?? '';

    // 1. GET DETAILS
    // Fetches professor info AND a concatenated list of their courses (ID::Name)
    if ($action === 'get_details') {
        $p_id = intval($_POST['p_id']);
        
        $sql = "SELECT p.*, 
                       GROUP_CONCAT(CONCAT(c.c_id, '::', c.`Course Name`) SEPARATOR '||') as CourseData 
                FROM professors p 
                LEFT JOIN courses c ON p.P_id = c.P_id 
                WHERE p.P_id = ?
                GROUP BY p.P_id";
                
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $p_id);
        
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                echo json_encode(['status' => 'success', 'data' => $row]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Professor not found']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'DB Error: ' . $conn->error]);
        }
        exit;
    }

    // 2. UPDATE PROFESSOR (Personal Details Only)
    if ($action === 'update_professor') {
        $p_id = intval($_POST['p_id']);
        $name = $_POST['name'];
        $dept = $_POST['department'];
        $uni = $_POST['university'];

        $conn->begin_transaction();
        try {
            $stmt1 = $conn->prepare("UPDATE professors SET Name=?, Department=?, University=? WHERE P_id=?");
            $stmt1->bind_param("sssi", $name, $dept, $uni, $p_id);
            $stmt1->execute();

            $stmt2 = $conn->prepare("UPDATE university SET uni_name=? WHERE p_id=?");
            $stmt2->bind_param("si", $uni, $p_id);
            $stmt2->execute();

            $conn->commit();
            echo json_encode(['status' => 'success', 'message' => 'Professor details updated']);
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(['status' => 'error', 'message' => 'Update failed: ' . $e->getMessage()]);
        }
        exit;
    }

    // 3. DELETE PROFESSOR (Cascading delete)
    if ($action === 'delete_professor') {
        $p_id = intval($_POST['p_id']);
        $conn->begin_transaction();
        try {
            $conn->query("DELETE FROM university WHERE p_id = $p_id");
            $conn->query("DELETE FROM courses WHERE P_id = $p_id");
            $conn->query("DELETE FROM professors WHERE P_id = $p_id");
            
            $conn->commit();
            echo json_encode(['status' => 'success', 'message' => 'Professor entry deleted']);
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(['status' => 'error', 'message' => 'Delete failed']);
        }
        exit;
    }

    // 4. ADD COURSE (Inserts into courses table)
    if ($action === 'add_course') {
        $p_id = intval($_POST['p_id']); 
        $course_name = trim($_POST['course_name']);
        $course_id = trim($_POST['course_id']);

        if(empty($course_name) || empty($course_id)) {
            echo json_encode(['status' => 'error', 'message' => 'Course Name and ID required']);
            exit;
        }

        // Prepare Insert
        $stmt = $conn->prepare("INSERT INTO courses (c_id, `Course Name`, P_id) VALUES (?, ?, ?)");
        // 'isi' -> integer (c_id), string (Name), integer (P_id)
        // If your c_id is actually a string (e.g. "CSE101"), change to 'ssi'
        $stmt->bind_param("isi", $course_id, $course_name, $p_id); 

        if($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'New course assigned']);
        } else {
            // Error 1062 is for Duplicate Entry
            if ($conn->errno == 1062) {
                echo json_encode(['status' => 'error', 'message' => 'Course ID already exists!']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Error adding course: ' . $stmt->error]);
            }
        }
        $stmt->close();
        exit;
    }

    // 5. REMOVE COURSE
    if ($action === 'remove_course') {
        $c_id = trim($_POST['c_id']);

        $stmt = $conn->prepare("DELETE FROM courses WHERE c_id = ?");
        $stmt->bind_param("i", $c_id); // Change to 's' if c_id is string

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Course removed']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Could not remove course']);
        }
        $stmt->close();
        exit;
    }
    exit;
}
?>

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

            // Fetch distinct professors and a summary of their courses
            $sql = "SELECT p.*, GROUP_CONCAT(c.`Course Name` SEPARATOR ', ') as CourseNames 
                    FROM professors p 
                    LEFT JOIN courses c ON p.P_id = c.P_id 
                    GROUP BY p.P_id 
                    ORDER BY p.Name ASC";
            
            $result = $conn->query($sql);

            if ($result && $result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
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
                
                <div class="form-group">
                    <label>Name</label>
                    <input type="text" id="edit_name">
                </div>
                <div class="form-group">
                    <label>Department</label>
                    <input type="text" id="edit_dept">
                </div>
                <div class="form-group">
                    <label>University</label>
                    <input type="text" id="edit_uni">
                </div>
                
                <div class="modal-actions">
                    <button class="btn btn-danger" onclick="deleteProfessor()">Delete Professor</button>
                    <button class="btn btn-primary" onclick="updateProfessor()">Update Details</button>
                </div>
            </form>

            <hr class="divider">

            <div class="course-management-section">
                <h3>Assigned Courses</h3>
                
                <div id="course-list-container" class="course-list">
                    </div>

                <h4 style="margin-top:1.5rem; color:#444;">Add New Course</h4>
                <div class="add-course-row">
                    <input type="number" id="new_course_id" placeholder="ID (e.g. 101)" class="sm-input">
                    <input type="text" id="new_course_name" placeholder="Name (e.g. CSE101)" class="lg-input">
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
                    <a href="#" class="footer-nav-link">Apply for Reviewer</a>
                    <a href="#" class="footer-nav-link">Apply for University Representative</a>
                </div>
            </div>

            <div class="footer-socials">
                <h5>Our Socials</h5>
                <div class="social-icons">
                    <a href="#" aria-label="Facebook">
                        <img src="../images/facebook.png" alt="Facebook" class="social-icon">
                    </a>
                    <a href="#" aria-label="Instagram">
                        <img src="../images/instagram.png" alt="Instagram" class="social-icon">
                    </a>
                    <a href="#" aria-label="Twitter">
                        <img src="../images/twitter.png" alt="Twitter" class="social-icon">
                    </a>
                </div>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; 2026 Rate Your Grader. All rights reserved.</p>
        </div>
    </div>
</footer>

<script src="../js/ManageFaculty.js"></script>
</body>
</html>