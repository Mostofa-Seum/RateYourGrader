<?php
//SearchOutput.php (Controller)
session_start(); 
include '../db/Config.php';

//Prepare Icons
$iconUser  = '<img src="../images/iconUser.png" alt="User" style="width: 4rem; height: 4rem; object-fit: contain;">';
$starFull  = '<img src="../images/starFull.png" alt="Star" style="width: 1.2em; height: 1.2em; vertical-align: middle;">';
$starHalf  = '<img src="../images/starHalf.jpg" alt="Half Star" style="width: 1.2em; height: 1.2em; vertical-align: middle;">';
$starEmpty = '<img src="../images/zeroStar.png" alt="Empty Star" style="width: 1.2em; height: 1.2em; vertical-align: middle;">';
$iconArrow = '<img src="../images/iconArrow.png" alt="Arrow" style="width: 1em; height: 1em; vertical-align: middle;">';

//SEARCH LOGIC
$search_term = "";
$search_performed = false;
$result = null;
$count = 0;

if (isset($_GET['q']) && !empty(trim($_GET['q']))) {
    $search_performed = true;
    $search_term = $_GET['q'];
    $safe_search = $conn->real_escape_string($search_term);
    $sql = "SELECT * FROM professors WHERE Name LIKE '%$safe_search%' OR Department LIKE '%$safe_search%' OR University LIKE '%$safe_search%'";
    $result = $conn->query($sql);
    if (!$result) { die("Query Failed: " . $conn->error); }
    $count = $result->num_rows;
} else {
    // Default View (Show 3 random professors)
    $search_performed = true; 
    $sql = "SELECT * FROM professors ORDER BY RAND() LIMIT 3";
    $result = $conn->query($sql);
    if (!$result) { die("Query Failed: " . $conn->error); }
    $count = $result->num_rows;
}

// USER ROLE CHECK
$user_role = null;
if (isset($_SESSION['s_id'])) {
    $user_id = $_SESSION['s_id'];
    $stmt = $conn->prepare("SELECT role FROM users WHERE s_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($user_data = $res->fetch_assoc()) {
        $user_role = $user_data['role'];
    }
    $stmt->close();
}

//CONNECT TO VIEW
include '../html/SearchOutputView.php';
?>