<?php
// AddProfessor.php (Controller)
ob_start();
session_start();
include '../db/Config.php';

//HANDLE AJAX REQUESTS
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    ob_clean();
    header('Content-Type: application/json');

    // Security Check
    if (!isset($_SESSION['s_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'You are not logged in.']);
        exit;
    }

    $action = $_POST['action'];

    //FETCH COURSE NAME
    if ($action === 'get_course_name') {
        $c_id = trim($_POST['c_id']);
        
        if (empty($c_id)) {
            echo json_encode(['status' => 'error']);
            exit;
        }

        $stmt = $conn->prepare("SELECT `Course Name` FROM courses WHERE c_id = ? LIMIT 1");
        $stmt->bind_param("s", $c_id);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($row = $res->fetch_assoc()) {
            echo json_encode(['status' => 'success', 'course_name' => $row['Course Name']]);
        } else {
            echo json_encode(['status' => 'not_found']);
        }
        $stmt->close();
        exit;
    }

    //ADD NEW PROFESSOR
    if ($action === 'add_new_professor') {
        $name = trim($_POST['name']);
        $dept = trim($_POST['department']);
        $uni  = trim($_POST['university']);
        $c_id = trim($_POST['c_id']); 
        $course_name = trim($_POST['course_name']);

        if (empty($name) || empty($dept) || empty($uni) || empty($c_id) || empty($course_name)) {
            echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
            exit;
        }

        if ($course_name === "No course found") {
            echo json_encode(['status' => 'error', 'message' => 'Invalid Course ID. Please enter a valid ID.']);
            exit;
        }

        $conn->begin_transaction();

        try {
            //Check for Duplicate Professor
            $check_stmt = $conn->prepare("SELECT P_id FROM professors WHERE Name = ? AND Department = ? AND University = ?");
            $check_stmt->bind_param("sss", $name, $dept, $uni);
            $check_stmt->execute();
            if ($check_stmt->get_result()->num_rows > 0) {
                throw new Exception("This professor is already registered.");
            }
            $check_stmt->close();

            //Insert Professor
            $stmt1 = $conn->prepare("INSERT INTO professors (Name, Department, University) VALUES (?, ?, ?)");
            $stmt1->bind_param("sss", $name, $dept, $uni);
            if (!$stmt1->execute()) throw new Exception("DB Error (Prof): " . $stmt1->error);
            $new_p_id = $conn->insert_id;
            $stmt1->close();

            //Insert University Data
            $stmt2 = $conn->prepare("INSERT INTO university (p_id, uni_name) VALUES (?, ?)");
            $stmt2->bind_param("is", $new_p_id, $uni);
            if (!$stmt2->execute()) throw new Exception("DB Error (Uni): " . $stmt2->error);
            $stmt2->close();

            //Assign Course
            $stmt3 = $conn->prepare("INSERT INTO courses (c_id, `Course Name`, P_id) VALUES (?, ?, ?)");
            $stmt3->bind_param("ssi", $c_id, $course_name, $new_p_id); 
            if (!$stmt3->execute()) {
                throw new Exception("DB Error (Course): " . $stmt3->error);
            }
            $stmt3->close();

            $conn->commit();
            echo json_encode(['status' => 'success', 'message' => 'Professor added and course assigned!']);

        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }
}

//PAGE LOAD USER CHECK
if (!isset($_SESSION['s_id'])) {
    header("Location: Login.php");
    exit();
}

//Fetch User Role for View
$user_id = $_SESSION['s_id'];
$stmt = $conn->prepare("SELECT role FROM users WHERE s_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
$user_data = $res->fetch_assoc();
$user_role = $user_data['role']; 
$stmt->close();

//CONNECT TO VIEW
include '../html/AddProfessorView.php';
?>