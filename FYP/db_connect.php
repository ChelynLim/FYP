<?php
$host = 'localhost';        // Server address
$user = 'root';             // Default user for XAMPP
$password = '';             // Default password is empty
$dbname = 'bookstore';      // Name of your database

// Create connection
$conn = new mysqli($host, $user, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
