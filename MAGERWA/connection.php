<?php

$servername = "localhost";
$username ="root";
$password = "";
$dbname= "magerwa";

$conn = new mysqli($servername, $username, $password , $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

session_start();
function enforce_admin_gate() {
    if (!isset($_SESSION['admin_id'])) {
        header("Location: login.php");
        exit();
    }
}

?>

