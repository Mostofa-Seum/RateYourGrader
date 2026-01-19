<?php
session_start();


if (!isset($_SESSION['s_id'])) {

    header("Location: ../../../Common/MVC/php/Login.php");
    exit();
}

include '../db/Config.php';

// Initialize Variables
$p_id = isset($_GET['P_id']) ? intval($_GET['P_id']) : 0;
$prof_name = isset($_GET['name']) ? $_GET['name'] : "Unknown Professor";
$prof_dept = isset($_GET['dept']) ? $_GET['dept'] : "Unknown Department";
$prof_uni  = isset($_GET['uni'])  ? $_GET['uni']  : "Unknown University";

//  Fetch Available Courses for Dropdown (ID and Name)
$available_courses = [];
if ($p_id > 0) {
    // We select both ID and Name. ID will be the value sent to the database.
    $course_sql = "SELECT `c_id`, `Course Name` FROM courses WHERE P_id = '$p_id'";
    $course_result = $conn->query($course_sql);
    if ($course_result) {
        while($row = $course_result->fetch_assoc()) {
            $available_courses[] = $row; 
        }
    }
}

$message = "";
$messageType = "";
$redirect = false;

//  Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $p_id_posted = intval($_POST['p_id']);
    $overall = intval($_POST['overallRating']);
    $fairness = intval($_POST['fairnessRating']);
    $behavior = intval($_POST['feedbackRating']); 
    $take_again = $_POST['takeAgain']; 
    
    // Directly get Course ID from Dropdown
    $final_c_id = isset($_POST['courseSelect']) ? intval($_POST['courseSelect']) : 0;
    
    $department = $conn->real_escape_string($_POST['department']); 
    $review_text = $conn->real_escape_string($_POST['review']);
    $difficulty = $_POST['difficulty']; 
    
    $student_id = $_SESSION['s_id']; 
    $reviewer_id = 1; // Default/Placeholder

    // Validation
    if ($overall == 0 || $fairness == 0 || $behavior == 0) {
        $message = "Please select all star ratings.";
        $messageType = "error";
    } elseif ($final_c_id == 0) {
        $message = "Please select a valid course.";
        $messageType = "error";
    } else {
        // Insert Review
        $sql_review = "INSERT INTO review 
            (`P_id`, `Rv_id`, `S_id`, `C_id`, `Review`, `Overall Rating`, `Grading Fairness`, `Behavior and Communication`, `Would You Take This Course Again?`, `Department`, `Difficulty Level`) 
            VALUES 
            ('$p_id_posted', '$reviewer_id', '$student_id', '$final_c_id', '$review_text', '$overall', '$fairness', '$behavior', '$take_again', '$department', '$difficulty')";

        if ($conn->query($sql_review) === TRUE) {
            $message = "Review submitted successfully! Wait for approval.";
            $messageType = "success";
            $redirect = true; 
        } else {
            $message = "Error submitting review: " . $conn->error;
            $messageType = "error";
        }
    }
}

include '../html/ProfessorReview.php';
?>