<?php
    // Destroy the session and redirect to the login page
    session_start();
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
?>
