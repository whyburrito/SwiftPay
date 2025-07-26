<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get the user ID from the session
$user_id = $_SESSION['user_id'];
$success_message = '';
$error = '';

// Check if the user updated their profile or password
if (isset($_GET['updated'])) {
    switch ($_GET['updated']) {
        case '1':
            $success_message = "Your profile has been updated successfully.";
            break;
        case 'pw':
            $success_message = "Your password has been changed successfully.";
            break;
    }
}

// Get user details from the database
$stmt = $conn->prepare("SELECT full_name, email, account_number, avatar, password FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Assign the user details to variables
$full_name = $user['full_name'];
$email = $user['email'];
$account_number = $user['account_number'];
$avatar = $user['avatar'] ?: 'default.png';
$hashed_password = $user['password'];

// Only runs if the edit profile form is submitted
if (isset($_POST['verify_edit'])) {
    $input_password = $_POST['current_password_edit'];
    // Verify password to confirm identity
    if (password_verify($input_password, $hashed_password)) {
        // If the password is correct, set the verified_edit session variable and redirect to the edit profile page
        $_SESSION['verified_edit'] = true;
        header("Location: profile_edit.php");
        exit();
    } else {
        $error = "Incorrect password. Please try again.";
    }
}

// Only runs if the change password form is submitted
if (isset($_POST['verify_password_change'])) {
    $input_password = $_POST['current_password_pw'];
    // Verify password to confirm identity
    if (password_verify($input_password, $hashed_password)) {
        // If the password is correct, set the verified_pw_change session variable and redirect to the change password page
        $_SESSION['verified_pw_change'] = true;
        header("Location: change_password.php");
        exit();
    } else {
        $error = "Incorrect password. Please try again.";
    }
}

// Only runs if the deactivate account form is submitted
if (isset($_POST['confirm_deactivation'])) {
    $input_password = $_POST['current_password_deactivate'];
    // Verify password to confirm identity
    if (password_verify($input_password, $hashed_password)) {
        // Soft delete: update status and set deleted_at timestamp
        $deactivate_stmt = $conn->prepare("UPDATE users SET status = 'deactivated', deleted_at = NOW() WHERE user_id = ?");
        $deactivate_stmt->bind_param("i", $user_id);
        
        if ($deactivate_stmt->execute()) {
            // Immediately destroy the session (log out)
            session_unset();
            session_destroy();
            // Redirect to login page with a confirmation message of successful deactivation
            header("Location: login.php?deactivated=1");
            exit();
        } else {
            $error = "Could not deactivate account. Please try again.";
        }
    } else {
        $error = "Incorrect password. Deactivation failed.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Profile - SwiftPay</title>
    <link rel="stylesheet" href="styles.css?v=1.6">
    <script src="scripts.js" defer></script>
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
        <h2>Your Profile</h2>

        <!-- Display success message if the user successfully updated their profile or password -->
        <?php if ($success_message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
        <?php endif; ?>
        <!-- Display error message if there is one -->
        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="card profile-header">
            <img src="uploads/<?= htmlspecialchars($avatar) ?>" alt="Avatar" class="profile-avatar">
            <div class="profile-info">
                <h2><?= htmlspecialchars($full_name) ?></h2>
                <p><?= htmlspecialchars($email) ?></p>
            </div>
        </div>

        <div class="card">
            <h3>Account Information</h3>
            <p><strong>Account Number:</strong>
                <span id="accNum"><?= htmlspecialchars($account_number) ?></span>
                <button onclick="copyToClipboard('accNum')" class="icon-button">
                    <img src="icons/copy-link-icon.png" alt="Copy" class="copy-icon">
                </button>
            </p>
        </div>

        <div class="card">
            <h3>Account Actions</h3>
            <button onclick="openModal('editModal')" class="btn btn-primary">Edit Profile</button>
            <button onclick="openModal('pwModal')" class="btn btn-secondary" style="margin-left: 10px;">Change Password</button>
            <button onclick="openModal('deactivateModal')" class="btn btn-danger" style="margin-left: 10px;">Deactivate Account</button>
        </div>
    </div>
</div>

<!-- Modal for checking password to edit user profile -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <form method="post">
            <h3>Enter Password to Edit Profile</h3>
            <input type="password" name="current_password_edit" placeholder="Current Password" required>
            <br><br>
            <button type="submit" name="verify_edit" class="btn btn-primary">Continue</button>
            <button type="button" onclick="closeModal('editModal')" class="btn btn-secondary" style="margin-left: 10px;">Cancel</button>
        </form>
    </div>
</div>

<!-- Modal for checking password to change user password -->
<div id="pwModal" class="modal">
    <div class="modal-content">
        <form method="post">
            <h3>Enter Current Password</h3>
            <input type="password" name="current_password_pw" placeholder="Current Password" required>
            <br><br>
            <button type="submit" name="verify_password_change" class="btn btn-primary">Continue</button>
            <button type="button" onclick="closeModal('pwModal')" class="btn btn-secondary" style="margin-left: 10px;">Cancel</button>
        </form>
    </div>
</div>

<!-- Modal for checking password to deactivate user account -->
<div id="deactivateModal" class="modal">
    <div class="modal-content">
        <form method="post">
            <h3>Are you sure?</h3>
            <p>This action cannot be undone. Please enter your password to confirm deactivation.</p>
            <input type="password" name="current_password_deactivate" placeholder="Current Password" required>
            <br><br>
            <button type="submit" name="confirm_deactivation" class="btn btn-danger">Confirm Deactivation</button>
            <button type="button" onclick="closeModal('deactivateModal')" class="btn btn-secondary" style="margin-left: 10px;">Cancel</button>
        </form>
    </div>
</div>

</body>
</html>