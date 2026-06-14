<?php

// This file is used to communicate with the database from PHP backend to mySQL database. 

//xampp details to connect to the db
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "org_accreditation_db";

//connects to MySQL using the 4 variables  
$conn = new mysqli($servername, $username, $password, $dbname);

//if connection failed, it kills the entire script and shows the error.
if($conn->connect_error){
    die("connection failed: " . $conn->connect_error);
  
}

?>
