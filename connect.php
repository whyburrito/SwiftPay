<?php
    // Database connection variables
    $servername = "localhost";
    $username = "root";
    $password = "";
    $db = "swiftpay_db";
    $port = "3306";

    // Create connection MySQLi
    $conn = mysqli_connect($servername, $username, $password, $db, $port);
    // Check connection
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    // Error reporting
    // Set error reporting to strict to catch all errors
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
?>
