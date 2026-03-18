<?php 


$host = 'db';
$username = 'root';
$password = 'root';
$dbname = "voyager";
$conn = new mysqli($host, $username, $password, $dbname);


if ($conn->connect_error) {
    die('Could not Connect MySql Server:' . $conn->connect_error);
}


?>


