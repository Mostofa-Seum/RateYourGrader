<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/ProfessorReview.css">
    <title>Rate Professor</title>
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
                    <input type="text" name="department" value="<?= htmlspecialchars($prof_dept) ?>" readonly>
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