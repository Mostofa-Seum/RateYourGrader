<?php
$host="localhost";  // server name
$user="root"; // username of the db
$pass=""; // password of the db
$dbname="rateyourgrader"; // database name
//create the connection
$conn=new mysqli($host,$user,$pass,$dbname);
if($conn->connect_error){
    die("Connection failed: ".$conn->connect_error);
}
?>