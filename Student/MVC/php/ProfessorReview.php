<?php
session_start();
if (!isset($_SESSION['s_id'])) {
    header("Location: ../../Common/MVC/php/Login.php");
    exit();
}
include '../db/Config.php';

// 1. Initialize Variables
$p_id = isset($_GET['P_id']) ? intval($_GET['P_id']) : 0;
$prof_name = isset($_GET['name']) ? $_GET['name'] : "Unknown Professor";
$prof_dept = isset($_GET['dept']) ? $_GET['dept'] : "Unknown Department";
$prof_uni  = isset($_GET['uni'])  ? $_GET['uni']  : "Unknown University";

// 2. Fetch Available Courses for Dropdown (ID and Name)
$available_courses = [];
if ($p_id > 0) {
    // We select both ID and Name. ID will be the value sent to the database.
    $course_sql = "SELECT `c_id`, `Course Name` FROM courses WHERE P_id = '$p_id'";
    $course_result = $conn->query($course_sql);
    if ($course_result) {
        while($row = $course_result->fetch_assoc()) {
            $available_courses[] = $row; // Stores ['c_id' => 101, 'Course Name' => 'Math']
        }
    }
}

$message = "";
$messageType = "";
$redirect = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $p_id_posted = intval($_POST['p_id']);
    $overall = intval($_POST['overallRating']);
    $fairness = intval($_POST['fairnessRating']);
    $behavior = intval($_POST['feedbackRating']); 
    $take_again = $_POST['takeAgain']; 
    
    // --- UPDATED: Directly get Course ID from Dropdown ---
    // The dropdown value is now the c_id (e.g., "101")
    $final_c_id = isset($_POST['courseSelect']) ? intval($_POST['courseSelect']) : 0;
    
    $department = $conn->real_escape_string($_POST['department']); 
    $review_text = $conn->real_escape_string($_POST['review']);
    $difficulty = $_POST['difficulty']; 
    
    $student_id = $_SESSION['s_id']; 
    $reviewer_id = 1; 

    // Validation
    if ($overall == 0 || $fairness == 0 || $behavior == 0) {
        $message = "Please select all star ratings.";
        $messageType = "error";
    } elseif ($final_c_id == 0) {
        // Validation in case user manipulated the form or didn't select
        $message = "Please select a valid course.";
        $messageType = "error";
    } else {
        // 3. Insert Review (Logic Simplified: No need to check/insert course)
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/ProfessorReview.css">
    <style>
        .icon-img { width: 1.2em; height: 1.2em; vertical-align: middle; object-fit: contain; }
        .professor-avatar img { width: 100%; height: 100%; object-fit: cover; border-radius: 50%; }
        #redirect-signal { display: none; }
        .nav-links a span:hover { color: #1e40af; }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container nav-container">
            <div class="logo-wrapper">
                <div class="logo-icon"><img src="../images/scolar_cap.png" alt="Logo" class="logo-img"></div>
                <span class="logo-text">Rate Your Grader</span>
            </div>
            
            <div class="nav-links">
                <a href="../../../Common/MVC/php/HomePage.php">Home</a> 
                <a href="SearchOutput.php">Search Graders</a>

                <?php if (isset($_SESSION['user_name'])): ?>
                    <a href="UserDashboard.php" style="text-decoration: none;">
                        <span style="margin-right: 15px; font-weight: bold; color: inherit;">Hello, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                    </a>
                    <a href="../../../Common/MVC/php/Logout.php" class="btn btn-primary-nav" style="background-color: #dc3545; color: white;">Logout</a>
                <?php else: ?>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div style="margin-top: 100px;"></div>

    <div class="container">
        <a href="SearchOutput.php" class="back-btn">
            <img src="../images/iconArrow.png" alt="Back" class="icon-img" style="transform: rotate(180deg); margin-right: 5px;"> Back to Search
        </a>

        <?php if ($message != ""): ?>
        <div class="success-message show" style="display: flex; background-color: <?= $messageType == 'error' ? '#f8d7da' : '#d4edda' ?>; color: <?= $messageType == 'error' ? '#721c24' : '#155724' ?>; border-left: 5px solid <?= $messageType == 'error' ? '#f5c6cb' : '#28a745' ?>;">
            <span><?= $message ?></span>
        </div>
        <?php endif; ?>

        <div class="professor-card">
            <div class="professor-avatar"><img src="../images/iconUser.png" alt="Professor"></div>
            <div class="professor-info">
                <h2><?= htmlspecialchars($prof_name) ?></h2>
                <p><?= htmlspecialchars($prof_dept) ?></p>
                <p><?= htmlspecialchars($prof_uni) ?></p>
            </div>
        </div>

        <form class="review-form" method="POST" action="" id="reviewForm" onsubmit="return validateForm()">
            <input type="hidden" name="p_id" value="<?= $p_id ?>">
            <h3 class="form-title">Share Your Experience</h3>

            <div class="form-group">
                <label class="form-label">Overall Rating</label>
                <input type="hidden" name="overallRating" id="inputOverall" value="0">
                <div class="star-rating" id="overallRating">
                    <span class="star" data-value="1">★</span><span class="star" data-value="2">★</span><span class="star" data-value="3">★</span><span class="star" data-value="4">★</span><span class="star" data-value="5">★</span>
                </div>
                <div class="error-text" id="overallError">Please select a rating</div>
            </div>

            <div class="form-group">
                <label class="form-label">Grading Fairness</label>
                <input type="hidden" name="fairnessRating" id="inputFairness" value="0">
                <div class="star-rating" id="fairnessRating">
                    <span class="star" data-value="1">★</span><span class="star" data-value="2">★</span><span class="star" data-value="3">★</span><span class="star" data-value="4">★</span><span class="star" data-value="5">★</span>
                </div>
                <div class="error-text" id="fairnessError">Please select a rating</div>
            </div>

            <div class="form-group">
                <label class="form-label">Behavior and Communication</label>
                <input type="hidden" name="feedbackRating" id="inputFeedback" value="0">
                <div class="star-rating" id="feedbackRating">
                    <span class="star" data-value="1">★</span><span class="star" data-value="2">★</span><span class="star" data-value="3">★</span><span class="star" data-value="4">★</span><span class="star" data-value="5">★</span>
                </div>
                <div class="error-text" id="feedbackError">Please select a rating</div>
            </div>

            <div class="form-group">
                <label class="form-label">Would You Take This Course Again?</label>
                <div class="choice-group">
                    <label class="choice-item"><input type="radio" name="takeAgain" value="Yes" required> <span>Yes</span></label>
                    <label class="choice-item"><input type="radio" name="takeAgain" value="Maybe" required> <span>Maybe</span></label>
                    <label class="choice-item"><input type="radio" name="takeAgain" value="No" required> <span>No</span></label>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Course Name</label>
                    <select name="courseSelect" id="courseSelect" required style="width: 100%; padding: 0.75rem; border: 2px solid #e0e0e0; border-radius: 8px; background-color: white;">
                        <option value="" disabled selected>Select a Course</option>
                        <?php 
                        if (!empty($available_courses)) {
                            foreach($available_courses as $course) {
                                // VALUE is the ID, TEXT is the Name
                                echo '<option value="' . htmlspecialchars($course['c_id']) . '">' . htmlspecialchars($course['Course Name']) . '</option>';
                            }
                        } else {
                            echo '<option value="" disabled>No courses available for this professor</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Department</label>
                    <input type="text" name="department" value="<?= htmlspecialchars($prof_dept) ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Your Review</label>
                <textarea name="review" placeholder="Details..." maxlength="1000"></textarea>
            </div>

            <div class="form-group">
                <label class="form-label">Difficulty Level</label>
                <div class="choice-group">
                    <label class="choice-item"><input type="radio" name="difficulty" value="Easy" required> <span>Easy</span></label>
                    <label class="choice-item"><input type="radio" name="difficulty" value="Moderate" required> <span>Moderate</span></label>
                    <label class="choice-item"><input type="radio" name="difficulty" value="Hard" required> <span>Hard</span></label>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Submit Review</button>
        </form>
    </div>

    <?php if ($redirect): ?>
        <div id="redirect-signal" data-target="SearchOutput.php"></div>
    <?php endif; ?>
    <script src="../js/ProfessorReview.js"></script>
</body>
</html>