<?php
session_start();
include '../db/Config.php';

// --- HANDLE AJAX REQUEST (Form Submission) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_new_professor') {
    header('Content-Type: application/json');

    // Security Check
    if (!isset($_SESSION['s_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'You are not logged in.']);
        exit;
    }

    $name = trim($_POST['name']);
    $dept = trim($_POST['department']);
    $uni  = trim($_POST['university']);
    $course_name = trim($_POST['course_name']);
    $c_id = trim($_POST['c_id']); 

    if (empty($name) || empty($dept) || empty($uni) || empty($course_name) || empty($c_id)) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
        exit;
    }

    // --- START TRANSACTION ---
    $conn->begin_transaction();

    try {
        // 1. CHECK IF PROFESSOR ALREADY EXISTS
        // We check Name, Department, and University combination
        $check_stmt = $conn->prepare("SELECT P_id FROM professors WHERE Name = ? AND Department = ? AND University = ?");
        $check_stmt->bind_param("sss", $name, $dept, $uni);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $check_stmt->close();
            throw new Exception("This professor already exists in the database.");
        }
        $check_stmt->close();

        // 2. Insert Name, Dept, and Uni into 'professors' table
        $stmt1 = $conn->prepare("INSERT INTO professors (Name, Department, University) VALUES (?, ?, ?)");
        $stmt1->bind_param("sss", $name, $dept, $uni);
        if (!$stmt1->execute()) throw new Exception("Error inserting professor: " . $stmt1->error);
        
        $new_p_id = $conn->insert_id;
        $stmt1->close();

        // 3. Insert P_id and Uni Name into 'university' table
        $stmt2 = $conn->prepare("INSERT INTO university (p_id, uni_name) VALUES (?, ?)");
        $stmt2->bind_param("is", $new_p_id, $uni);
        if (!$stmt2->execute()) throw new Exception("Error inserting university data: " . $stmt2->error);
        $stmt2->close();

        // 4. Insert c_id, Course Name, and P_id into 'courses' table
        // We do NOT update the professors table with C_id, as the link is stored here in the courses table.
        $stmt3 = $conn->prepare("INSERT INTO courses (c_id, `Course Name`, P_id) VALUES (?, ?, ?)");
        // 'isi' means integer (c_id), string (Name), integer (P_id)
        $stmt3->bind_param("isi", $c_id, $course_name, $new_p_id); 
        if (!$stmt3->execute()) {
            if ($conn->errno == 1062) {
                throw new Exception("Course ID ($c_id) already exists. Please use a unique ID.");
            }
            throw new Exception("Error inserting course: " . $stmt3->error);
        }
        $stmt3->close();

        // Commit changes
        $conn->commit();
        echo json_encode(['status' => 'success', 'message' => 'Professor and Course added successfully!']);

    } catch (Exception $e) {
        // Rollback if any error occurs
        $conn->rollback();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}
?>

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
                <p>Register a new professor and assign their course.</p>
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
                        <input type="number" id="c_id" name="c_id" placeholder="e.g. 101" required>
                    </div>

                    <div class="form-group full-width">
                        <label for="course_name">Course Name</label>
                        <input type="text" id="course_name" name="course_name" placeholder="e.g. CSE101" required>
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

<script src="AddProfessor.js"></script>

</body>
</html>