<?php
// 1. DATABASE CONNECTION
include 'Config.php';

// 2. INITIALIZE VARIABLES (From URL)
$p_id = isset($_GET['P_id']) ? intval($_GET['P_id']) : 0;
$prof_name = isset($_GET['name']) ? $_GET['name'] : "Unknown Professor";
$prof_dept = isset($_GET['dept']) ? $_GET['dept'] : "Unknown Department";
$prof_uni  = isset($_GET['uni'])  ? $_GET['uni']  : "Unknown University";

// 3. HANDLE FORM SUBMISSION
$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // A. Collect Form Data
    $p_id_posted = intval($_POST['p_id']);
    
    // Ratings
    $overall = intval($_POST['overallRating']);
    $fairness = intval($_POST['fairnessRating']);
    $behavior = intval($_POST['feedbackRating']); 
    
    // Text & Choices
    $take_again = $_POST['takeAgain']; 
    $course_name = $conn->real_escape_string($_POST['courseName']);
    $department = $conn->real_escape_string($_POST['department']); 
    $review_text = $conn->real_escape_string($_POST['review']);
    $difficulty = $_POST['difficulty']; 
    
    // User IDs (Placeholder)
    $student_id = 1; 
    $reviewer_id = 1; 

    // B. VALIDATION
    if ($overall == 0 || $fairness == 0 || $behavior == 0) {
        $message = "Please select all star ratings.";
        $messageType = "error";
    } else {
        
        // --- C. LOGIC CHANGE: CHECK IF COURSE EXISTS ---
        $final_c_id = 0;
        $course_error = false;

        $check_sql = "SELECT c_id FROM courses WHERE `Course Name` = '$course_name' AND P_id = '$p_id_posted'";
        $check_result = $conn->query($check_sql);

        if ($check_result && $check_result->num_rows > 0) {
            // Course Exists -> Get ID
            $row = $check_result->fetch_assoc();
            $final_c_id = $row['c_id'];
        } else {
            // Course Does Not Exist -> Insert New
            $insert_course_sql = "INSERT INTO courses (`Course Name`, `P_id`) VALUES ('$course_name', '$p_id_posted')";
            if ($conn->query($insert_course_sql) === TRUE) {
                $final_c_id = $conn->insert_id;
            } else {
                $message = "Error saving course: " . $conn->error;
                $messageType = "error";
                $course_error = true;
            }
        }

        // --- D. INSERT REVIEW ---
        if (!$course_error && $final_c_id > 0) {
            $sql_review = "INSERT INTO review 
            (`P_id`, `Rv_id`, `S_id`, `C_id`, `Review`, `Overall Rating`, `Grading Fairness`, `Behavior and Communication`, `Would You Take This Course Again?`, `Department`, `Difficulty Level`) 
            VALUES 
            ('$p_id_posted', '$reviewer_id', '$student_id', '$final_c_id', '$review_text', '$overall', '$fairness', '$behavior', '$take_again', '$department', '$difficulty')";

            if ($conn->query($sql_review) === TRUE) {
                $message = "Review submitted successfully!";
                $messageType = "success";
            } else {
                $message = "Error submitting review: " . $conn->error;
                $messageType = "error";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="ProfessorReview.css">
    <style>
        .icon-img {
            width: 1.2em;
            height: 1.2em;
            vertical-align: middle;
            object-fit: contain;
        }
        .professor-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }
    </style>
</head>
<body>
    
    <nav class="navbar">
        <div class="container nav-container">
            <div class="logo-wrapper">
                <div class="logo-icon">
                    <img src="Images/scolar_cap.png" alt="Logo" class="logo-img">
                </div>
                <span class="logo-text">Rate Your Grader</span>
            </div>
            <div class="nav-links">
                <a href="#how-it-works">How it works</a>
                <a href="#features">Features</a>
                <a href="#search">Search Graders</a>
                <button class="btn btn-primary-nav">Logout</button>
            </div>
            <button class="mobile-menu-btn">
                <img src="Figures/menu.png" alt="Menu" class="mobile-menu-icon">
            </button>
        </div>
    </nav>

    <div style="margin-top: 100px;"></div>

    <div class="container">
        <a href="SearchOutput.php" class="back-btn">
            <img src="Images/iconArrow.png" alt="Back" class="icon-img" style="transform: rotate(180deg); margin-right: 5px;"> 
            Back to Search
        </a>

        <?php if ($message != ""): ?>
        <div class="success-message show" style="display: flex; background-color: <?= $messageType == 'error' ? '#f8d7da' : '#d4edda' ?>; color: <?= $messageType == 'error' ? '#721c24' : '#155724' ?>; border-left: 5px solid <?= $messageType == 'error' ? '#f5c6cb' : '#28a745' ?>;">
            <span><?= $message ?></span>
        </div>
        <?php endif; ?>

        <div class="professor-card">
            <div class="professor-avatar">
                <img src="Images/iconUser.png" alt="Professor">
            </div>
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
                    <span class="star" data-value="1">★</span>
                    <span class="star" data-value="2">★</span>
                    <span class="star" data-value="3">★</span>
                    <span class="star" data-value="4">★</span>
                    <span class="star" data-value="5">★</span>
                </div>
                <div class="error-text" id="overallError">Please select a rating</div>
            </div>

            <div class="form-group">
                <label class="form-label">Grading Fairness</label>
                <input type="hidden" name="fairnessRating" id="inputFairness" value="0">
                <div class="star-rating" id="fairnessRating">
                    <span class="star" data-value="1">★</span>
                    <span class="star" data-value="2">★</span>
                    <span class="star" data-value="3">★</span>
                    <span class="star" data-value="4">★</span>
                    <span class="star" data-value="5">★</span>
                </div>
                <div class="error-text" id="fairnessError">Please select a rating</div>
            </div>

            <div class="form-group">
                <label class="form-label">Behavior and Communication</label>
                <input type="hidden" name="feedbackRating" id="inputFeedback" value="0">
                <div class="star-rating" id="feedbackRating">
                    <span class="star" data-value="1">★</span>
                    <span class="star" data-value="2">★</span>
                    <span class="star" data-value="3">★</span>
                    <span class="star" data-value="4">★</span>
                    <span class="star" data-value="5">★</span>
                </div>
                <div class="error-text" id="feedbackError">Please select a rating</div>
            </div>

            <div class="form-group">
                <label class="form-label">Would You Take This Course Again?</label>
                <div class="choice-group">
                    <label class="choice-item"><input type="radio" name="takeAgain" value="Yes" required> <span>Yes, definitely!</span></label>
                    <label class="choice-item"><input type="radio" name="takeAgain" value="Maybe" required> <span>Maybe, it depends</span></label>
                    <label class="choice-item"><input type="radio" name="takeAgain" value="No" required> <span>No, I would not recommend</span></label>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Course Name</label>
                    <input type="text" name="courseName" placeholder="e.g., Intro to Algorithms" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Department</label>
                    <input type="text" name="department" value="<?= htmlspecialchars($prof_dept) ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Your Review (Optional)</label>
                <textarea name="review" placeholder="Share specific details about your experience..." maxlength="1000"></textarea>
            </div>

            <div class="form-group">
                <label class="form-label">Difficulty Level</label>
                <div class="choice-group">
                    <label class="choice-item"><input type="radio" name="difficulty" value="Easy" required> <span>Easy</span></label>
                    <label class="choice-item"><input type="radio" name="difficulty" value="Moderate" required> <span>Moderate</span></label>
                    <label class="choice-item"><input type="radio" name="difficulty" value="Hard" required> <span>Hard</span></label>
                    <label class="choice-item"><input type="radio" name="difficulty" value="Very Hard" required> <span>Very Hard</span></label>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Submit Review</button>
        </form>
    </div>

    <script src="ProfessorReview.js"></script>
</body>
</html>