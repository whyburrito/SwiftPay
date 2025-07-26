<?php
session_start();
include 'connect.php';

// Check if the user is logged in and has verified their identity to change their password
if (!isset($_SESSION['user_id']) || !isset($_SESSION['verified_pw_change'])) {
    header("Location: profile.php");
    exit();
}

// Get the user ID from the session
$user_id = $_SESSION['user_id'];
$error = '';

// Only runs if the change password form is submitted
if (isset($_POST['change_password'])) {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Input validation
    if (strlen($new_password) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif ($new_password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Hash the new password using the default php password hashing algorithm
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);

        // Update the user's password in the database
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        $stmt->bind_param("si", $hashed, $user_id);

        if ($stmt->execute()) {
            // Unset the verified_pw_change session variable and redirect to the profile page with a success message
            unset($_SESSION['verified_pw_change']);
            header("Location: profile.php?updated=pw"); 
            exit();
        } else {
            $error = "Error updating password.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Change Password - SwiftPay</title>
    <link rel="stylesheet" href="styles.css?v=1.6">
    <script src="scripts.js"></script>
</head>
<body>

<div class="app-container">
    <div class="sidebar">
        <div class="logo">
            <a href="dashboard.php">
                <img src="icons/SwiftPay-trimmed.png" alt="SwiftPay Logo">
            </a>
        </div>
        <nav class="sidebar-nav">
            <a href="dashboard.php">Dashboard</a>
            <a href="transfer.php">Transfer</a>
            <a href="history.php">History</a>
            <a href="profile.php" class="active">Profile</a>
        </nav>
        <a href="logout.php" class="btn btn-secondary logout-btn">Logout</a>
    </div>

    <div class="main-content">
        <div class="card">
            <h2>Change Password</h2>
            
            <!-- Display error message if there is one -->
            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <!-- Change password form -->
            <form method="post">
                <div style="margin-bottom: 15px;">
                    <label for="new_password">New Password:</label>
                    <div class="password-wrapper">
                        <input type="password" name="new_password" id="new_password" oninput="checkStrength('new_password', 'strength')" required>
                        <button type="button" class="toggle-password" onclick="toggleVisibility('new_password')">Show</button>
                    </div>
                    <div id="strength" style="margin-top: 5px;"></div>
                </div>

                <div style="margin-bottom: 20px;">
                    <label for="confirm_password">Confirm New Password:</label>
                    <div class="password-wrapper">
                        <input type="password" name="confirm_password" id="confirm_password" required>
                        <button type="button" class="toggle-password" onclick="toggleVisibility('confirm_password')">Show</button>
                    </div>
                </div>

                <button type="submit" name="change_password" class="btn btn-primary">Update Password</button>
                <a href="profile.php" class="btn btn-secondary" style="margin-left: 10px;">Cancel</a>
            </form>
        </div>
    </div>
</div>

</body>
</html>