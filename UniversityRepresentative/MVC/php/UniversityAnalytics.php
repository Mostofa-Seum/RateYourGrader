<?php
// UniversityAnalytics.php (Controller)
session_start();
include '../db/Config.php';

//HANDLE AJAX REQUESTS
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    if (!isset($_SESSION['s_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        exit;
    }

    $action = $_POST['action'] ?? '';

    //FETCH ANALYTICS FOR A SPECIFIC UNIVERSITY
    if ($action === 'get_uni_stats') {
        $uni_name = $_POST['uni_name'];

        //Count Faculty
        $sql_count = "SELECT COUNT(*) as count FROM professors WHERE University = ?";
        $stmt = $conn->prepare($sql_count);
        $stmt->bind_param("s", $uni_name);
        $stmt->execute();
        $count_res = $stmt->get_result()->fetch_assoc();
        $faculty_count = $count_res['count'];
        $stmt->close();

        //Calculate Average Rating
        $sql_rating = "SELECT AVG(r.`Overall Rating`) as avg_rating 
                        FROM review r
                        JOIN a_review ar ON r.r_id = ar.r_id
                        JOIN professors p ON r.P_id = p.P_id
                        WHERE p.University = ?";
        
        $stmt = $conn->prepare($sql_rating);
        $stmt->bind_param("s", $uni_name);
        $stmt->execute();
        $rating_res = $stmt->get_result()->fetch_assoc();
        
        $avg_rating = $rating_res['avg_rating'] !== null ? round($rating_res['avg_rating'], 1) : "N/A";
        
        echo json_encode([
            'status' => 'success', 
            'data' => [
                'name' => $uni_name,
                'faculty_count' => $faculty_count,
                'rating' => $avg_rating
            ]
        ]);
        exit;
    }
    exit;
}

//PAGE LOAD USER CHECK
if (!isset($_SESSION['s_id'])) { header("Location: Login.php"); exit(); }

$user_id = $_SESSION['s_id'];
$stmt = $conn->prepare("SELECT role FROM users WHERE s_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
$user_data = $res->fetch_assoc();
$user_role = $user_data['role']; 
$stmt->close();

//Fetch University List for View
$uni_list_sql = "SELECT DISTINCT uni_name FROM university ORDER BY uni_name ASC";
$uni_list_result = $conn->query($uni_list_sql);

//CONNECT TO VIEW
include '../html/UniversityAnalyticsView.php';
?>