<?php
session_start();
include "../db/config.php";


$searchAction = "../../../Student/MVC/php/SearchOutput.php"; 
$role = ""; 
$dashboardLink = ""; 

// Check if the user is logged in
if (isset($_SESSION['user_name'])) {
    $username = $_SESSION['user_name'];
    $safe_username = mysqli_real_escape_string($conn, $username);

    $sql = "SELECT role FROM users WHERE Username = '$safe_username'";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $role = $row['role']; // Assign role from DB

        if ($role == 'Reviewer') {
            $dashboardLink = "../../../Reviewer/MVC/php/ReviewerDashboard.php"; 
            $searchAction = "../../../Reviewer/MVC/php/SearchOutput.php"; 
        } elseif ($role == 'Student') {
            $dashboardLink = "../../../Student/MVC/php/UserDashboard.php";
            $searchAction = "../../../Student/MVC/php/SearchOutput.php";
        } elseif ($role == 'UniRep') {
            $dashboardLink = "../../../UniversityRepresentative/MVC/php/UniversityRepDashboard.php"; 
            $searchAction = "../../../UniversityRepresentative/MVC/php/SearchOutput.php"; 
        }
    }
}


include '../html/HomePage.php';
?>