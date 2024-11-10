<?php
// Database connection details
$servername = "localhost";
$username = "root"; // use 'root' or your MySQL/MariaDB username
$password = "your_password"; // use the password you set for the root user
$dbname = "student_notes"; // name of your database (make sure it exists)

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$servername = "localhost";
$username = "root";
$password = "your_password";
$dbname = "student_notes";
$socket = "/var/run/mysqld/mysqld.sock";  // Specify the MySQL socket path

// Create connection with socket option
$conn = new mysqli($servername, $username, $password, $dbname, null, $socket);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// if ($conn->query($sql) === TRUE) {
//     echo "Success!";
// } else {
//     echo "Error: " . $sql . "<br>" . $conn->error;
// }

?>

