<?php
session_start();

// Check if the user is already logged in and redirect them to the dashboard if so
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

include 'connect.php';

$error = '';

// Only runs if the login form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Check if the email and password match a user in the database
    $stmt = $conn->prepare("SELECT user_id, full_name, password, status, deleted_at FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows === 1) {
        $user = $res->fetch_assoc();

        // Check if the account is active and not deleted
        if ($user['status'] !== 'active' || $user['deleted_at'] !== null) {
            $error = "This account is deactivated.";

        // If the password is correct, set the user details in the session and redirect to the dashboard
        } elseif (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['full_name'] = $user['full_name'];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid email or password."; // If the password is incorrect, display an error message
        }
    } else {
        $error = "Invalid email or password."; // If the email is not found in the database, display an error message
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - SwiftPay</title>
    <link rel="stylesheet" href="styles.css">
    <script src="scripts.js"></script>
</head>
<body>
    <div class="auth-container">
        <div class="card">
            <div class="logo">
                <img src="icons/SwiftPay-trimmed.png" alt="SwiftPay Logo">
            </div>
            <h3 style="text-align: center; margin-top: 0;">Login</h3>

            <!-- Display error message if there is one -->
            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <!-- Display success message if the user successfully registered -->
            <?php if (isset($_GET['registered'])): ?>
                <div class="alert alert-success">Registration successful! You can now log in.</div>
            <?php endif; ?>

            <!-- Display success message if the user successfully deactivated their account -->
            <?php if (isset($_GET['deactivated'])): ?>
                <div class="alert alert-success">Your account has been successfully deactivated.</div>
            <?php endif; ?>

            <!-- Login form -->
            <form method="post" style="margin-top: 20px;">
                <div style="margin-bottom: 15px;">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div style="margin-bottom: 20px;">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">Login</button>
            </form>
        </div>
        <div style="text-align: center; margin-top: 20px;">
            <p>Don't have an account? <a href="register.php">Register here.</a></p>
        </div>
    </div>
</body>
</html>