<?php
//ManageFaculty.php (Controller)
ob_start();
session_start();
include '../db/Config.php';

//HANDLE AJAX REQUESTS
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    ob_clean(); 
    header('Content-Type: application/json');

    if (!isset($_SESSION['s_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        exit;
    }

    $action = $_POST['action'] ?? '';

    //GET DETAILS
    if ($action === 'get_details') {
        $p_id = intval($_POST['p_id']);
        $sql = "SELECT p.*, GROUP_CONCAT(CONCAT(c.c_id, '::', c.`Course Name`) SEPARATOR '||') as CourseData 
                FROM professors p LEFT JOIN courses c ON p.P_id = c.P_id 
                WHERE p.P_id = ? GROUP BY p.P_id";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $p_id);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) echo json_encode(['status' => 'success', 'data' => $row]);
            else echo json_encode(['status' => 'error', 'message' => 'Professor not found']);
        } else echo json_encode(['status' => 'error', 'message' => 'DB Error']);
        exit;
    }

    //SEARCH COURSE NAME
    if ($action === 'get_course_name') {
        $c_id = trim($_POST['c_id']);
        if (empty($c_id)) { echo json_encode(['status' => 'error']); exit; }
        $stmt = $conn->prepare("SELECT `Course Name` FROM courses WHERE c_id = ? LIMIT 1");
        $stmt->bind_param("s", $c_id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) echo json_encode(['status' => 'success', 'course_name' => $row['Course Name']]);
        else echo json_encode(['status' => 'not_found']);
        $stmt->close();
        exit;
    }

    //ADD COURSE
    if ($action === 'add_course') {
        $p_id = intval($_POST['p_id']); 
        $course_name = trim($_POST['course_name']);
        $course_id = trim($_POST['course_id']);

        if ($course_name === "No course found") {
            echo json_encode(['status' => 'error', 'message' => 'Invalid Course ID.']); exit;
        }

        $check_stmt = $conn->prepare("SELECT serial FROM courses WHERE c_id = ? AND P_id = ?");
        $check_stmt->bind_param("si", $course_id, $p_id);
        $check_stmt->execute();
        if ($check_stmt->get_result()->num_rows > 0) {
            echo json_encode(['status' => 'error', 'message' => 'This professor is already assigned to this course.']); exit;
        }
        $check_stmt->close();

        $stmt = $conn->prepare("INSERT INTO courses (c_id, `Course Name`, P_id) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $course_id, $course_name, $p_id); 
        if($stmt->execute()) echo json_encode(['status' => 'success', 'message' => 'New course assigned']);
        else echo json_encode(['status' => 'error', 'message' => 'Error adding course']);
        $stmt->close();
        exit;
    }

    //UPDATE PROFESSOR
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
            echo json_encode(['status' => 'error', 'message' => 'Update failed']);
        }
        exit;
    }

    //DELETE PROFESSOR
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

    //REMOVE COURSE
    if ($action === 'remove_course') {
        $c_id = trim($_POST['c_id']);
        $p_id = intval($_POST['p_id']); 
        $stmt = $conn->prepare("DELETE FROM courses WHERE c_id = ? AND P_id = ?");
        $stmt->bind_param("si", $c_id, $p_id); 
        if ($stmt->execute()) echo json_encode(['status' => 'success', 'message' => 'Course removed']);
        else echo json_encode(['status' => 'error', 'message' => 'Could not remove course']);
        $stmt->close();
        exit;
    }
    exit;
}

//PAGE LOAD
if (!isset($_SESSION['s_id'])) { header("Location: Login.php"); exit(); }

$user_id = $_SESSION['s_id'];
$stmt = $conn->prepare("SELECT role FROM users WHERE s_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
$user_data = $res->fetch_assoc();
$user_role = $user_data['role'];
$stmt->close();

//Prepare Faculty List Data for the View
$faculty_sql = "SELECT p.*, GROUP_CONCAT(c.`Course Name` SEPARATOR ', ') as CourseNames 
                FROM professors p 
                LEFT JOIN courses c ON p.P_id = c.P_id 
                GROUP BY p.P_id 
                ORDER BY p.Name ASC";
$faculty_result = $conn->query($faculty_sql);

//CONNECT TO VIEW
include '../html/ManageFacultyView.php';
?>