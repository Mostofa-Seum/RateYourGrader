<?php
session_start();
include '../db/Config.php';

$p_id = isset($_GET['P_id']) ? intval($_GET['P_id']) : 0;

if ($p_id == 0) {
    echo "Invalid Professor ID.";
    exit;
}

//  Fetch Professor Details
$sql_prof = "SELECT * FROM professors WHERE P_id = $p_id";
$result_prof = $conn->query($sql_prof);

if ($result_prof->num_rows > 0) {
    $prof = $result_prof->fetch_assoc();
} else {
    echo "Professor not found.";
    exit;
}

//  Fetch Aggregated Stats
$sql_stats = "SELECT COUNT(r.Rv_id) as total_reviews, AVG(r.`Overall Rating`) as avg_overall, AVG(r.`Grading Fairness`) as avg_fairness, AVG(r.`Behavior and Communication`) as avg_behavior, SUM(CASE WHEN r.`Would You Take This Course Again?` = 'Yes' THEN 1 ELSE 0 END) as take_again_count FROM review r INNER JOIN A_Review ar ON r.r_id = ar.R_id WHERE r.P_id = $p_id";
$result_stats = $conn->query($sql_stats);
$stats = $result_stats->fetch_assoc();

$total_reviews = $stats['total_reviews'];
$avg_overall = $total_reviews > 0 ? number_format($stats['avg_overall'], 1) : "N/A";
$avg_fairness = $total_reviews > 0 ? number_format($stats['avg_fairness'], 1) : 0;
$avg_behavior = $total_reviews > 0 ? number_format($stats['avg_behavior'], 1) : 0;
$take_again_percent = ($total_reviews > 0) ? round(($stats['take_again_count'] / $total_reviews) * 100) : 0;

//  Fetch Reviews
$sql_reviews = "SELECT r.*, c.`Course Name` FROM review r INNER JOIN A_Review ar ON r.r_id = ar.R_id LEFT JOIN courses c ON r.C_id = c.c_id WHERE r.P_id = $p_id ORDER BY r.r_id DESC";
$result_reviews = $conn->query($sql_reviews);

// Helper Function for Star Rendering
function renderStars($rating) {
    $output = '';
    $fullStars = floor($rating);
    $hasHalf = ($rating - $fullStars) >= 0.5;
    $emptyStars = 5 - $fullStars - ($hasHalf ? 1 : 0);
    // Path relative to the View file
    $imgDir = '../images/';
    
    for ($i = 0; $i < $fullStars; $i++) { $output .= '<img src="'.$imgDir.'starFull.png" class="star-icon">'; }
    if ($hasHalf) { $output .= '<img src="'.$imgDir.'starHalf.jpg" class="star-icon">'; }
    for ($i = 0; $i < $emptyStars; $i++) { $output .= '<img src="'.$imgDir.'starEmpty.png" class="star-icon">'; }
    return $output;
}

//  Prepare Rate Button Logic
$rateLink = "javascript:void(0);";
$btnID = "id='rateBtnLoggedOut'";

if (isset($_SESSION['user_name'])) {
    $rateLink = "ProfessorReview.php?P_id=$p_id&name=" . urlencode($prof['Name']) . "&dept=" . urlencode($prof['Department']) . "&uni=" . urlencode($prof['University']);
    $btnID = ""; 
}
include '../html/ProfessorProfile.php';
?>