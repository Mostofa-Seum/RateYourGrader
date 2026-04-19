<?php
session_start(); 

include '../db/Config.php';

// SEARCH LOGIC
$search_term = "";
$search_performed = false;
$professors_data = []; // Array to store prepared data for the view
$count = 0;

if (isset($_GET['q']) && !empty(trim($_GET['q']))) {
    $search_performed = true;
    $search_term = $_GET['q'];
    $safe_search = $conn->real_escape_string($search_term);
    $sql = "SELECT * FROM professors WHERE Name LIKE '%$safe_search%' OR Department LIKE '%$safe_search%' OR University LIKE '%$safe_search%'";
} else {
    $search_performed = true; 
    $sql = "SELECT * FROM professors ORDER BY RAND() LIMIT 3";
}

$result = $conn->query($sql);
if (!$result) { die("Query Failed: " . $conn->error); }
$count = $result->num_rows;

//  PROCESS RESULTS
// We fetch all data here 
if ($count > 0) {
    while($row = $result->fetch_assoc()) {
        $current_p_id = $row['P_id'];
        
        //  Fetch Stats
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

        //  Fetch Latest Review
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

        // Store in array
        $professors_data[] = [
            'info' => $row,
            'stats' => [
                'total' => $total_reviews,
                'overall' => $overall_rating,
                'fairness_score' => $fairness_score,
                'fairness_width' => $fairness_width,
                'clarity_score' => $clarity_score,
                'clarity_width' => $clarity_width
            ],
            'latest_review' => $review_text_display
        ];
    }
}

include '../html/SearchOutput.php';
?>