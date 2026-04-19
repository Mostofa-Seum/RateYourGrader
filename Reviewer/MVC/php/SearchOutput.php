<?php
session_start(); 
include '../../../Common/MVC/db/Config.php';

// Check if the user is a reviewer based on session data
$user_role = isset($_SESSION['role']) ? strtolower($_SESSION['role']) : ''; 
$is_reviewer = ($user_role === 'reviewer'); 
$is_rep = ($user_role === 'university representative');

// Define Icon Variables for View
$iconUser  = '<img src="../images/iconUser.png" alt="User" style="width: 4rem; height: 4rem; object-fit: contain;">';
$starFull  = '<img src="../images/starFull.png" alt="Star" style="width: 1.2em; height: 1.2em; vertical-align: middle;">';
$starHalf  = '<img src="../images/starHalf.jpg" alt="Half Star" style="width: 1.2em; height: 1.2em; vertical-align: middle;">';
$starEmpty = '<img src="../images/zeroStar.png" alt="Empty Star" style="width: 1.2em; height: 1.2em; vertical-align: middle;">';
$iconArrow = '<img src="../images/iconArrow.png" alt="Arrow" style="width: 1em; height: 1em; vertical-align: middle;">';

// 2. SEARCH LOGIC
$search_term = "";
$search_performed = false;
$professors_data = []; // Array to hold processed data for the view
$count = 0;

if (isset($_GET['q']) && !empty(trim($_GET['q']))) {
    //  User performed a search
    $search_performed = true;
    $search_term = $_GET['q'];
    $safe_search = $conn->real_escape_string($search_term);
    $sql = "SELECT * FROM professors WHERE Name LIKE '%$safe_search%' OR Department LIKE '%$safe_search%' OR University LIKE '%$safe_search%'";
} else {
    // Default View (Show 3 random professors)
    $search_performed = true; 
    $sql = "SELECT * FROM professors ORDER BY RAND() LIMIT 3";
}

$result = $conn->query($sql);
if (!$result) { die("Query Failed: " . $conn->error); }
$count = $result->num_rows;

if ($count > 0) {
    while($row = $result->fetch_assoc()) {
        $current_p_id = $row['P_id'];
        
        //  STATS QUERY
        $stat_sql = "SELECT COUNT(r.r_id) as total_reviews, AVG(r.`Overall Rating`) as avg_overall,
            AVG(r.`Grading Fairness`) as avg_fairness, AVG(r.`Behavior and Communication`) as avg_behavior 
            FROM review r INNER JOIN A_Review ar ON r.r_id = ar.R_id WHERE r.P_id = '$current_p_id'";
        $stat_result = $conn->query($stat_sql);
        $stats = $stat_result->fetch_assoc();

        $total_reviews = $stats['total_reviews'];
        $overall_rating = $total_reviews > 0 ? number_format($stats['avg_overall'], 1) : "0.0";
        $fairness_score = $total_reviews > 0 ? number_format($stats['avg_fairness'], 1) : "0.0";
        $clarity_score  = $total_reviews > 0 ? number_format($stats['avg_behavior'], 1) : "0.0";
        $fairness_width = ($fairness_score / 5) * 100;
        $clarity_width  = ($clarity_score / 5) * 100;

        //  FETCH LATEST REVIEW TEXT
        $review_text_display = "No written reviews yet.";
        $review_text_sql = "SELECT ar.Review FROM A_Review ar 
                            INNER JOIN review r ON ar.r_id = r.r_id 
                            WHERE r.P_id = '$current_p_id' 
                            ORDER BY ar.AR_id DESC LIMIT 1";
        $review_text_result = $conn->query($review_text_sql);
        
        if ($review_text_result && $review_text_result->num_rows > 0) {
            $r_row = $review_text_result->fetch_assoc();
            $review_text_display = '"' . htmlspecialchars($r_row['Review']) . '"';
            if (strlen($review_text_display) > 150) {
                $review_text_display = substr($review_text_display, 0, 150) . '..."';
            }
        }

        // Add processed data to array
        $professors_data[] = [
            'info' => $row,
            'stats' => [
                'total_reviews' => $total_reviews,
                'overall_rating' => $overall_rating,
                'fairness_score' => $fairness_score,
                'clarity_score' => $clarity_score,
                'fairness_width' => $fairness_width,
                'clarity_width' => $clarity_width
            ],
            'latest_review' => $review_text_display
        ];
    }
}
include '../html/SearchOutput.php';
?>