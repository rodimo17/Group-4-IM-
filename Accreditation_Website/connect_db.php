<?php

// This file is used to communicate with the database from PHP backend to mySQL database. 

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "org_accreditation_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if($conn->connect_error){
    die("connection failed: " . $conn->connect_error);
  
}

?>
