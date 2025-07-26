<?php
session_start();

// Check if the user is already logged in and redirect them to the dashboard if so
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

include 'connect.php';

$error = '';

// Only runs if the registration form is submitted
if (isset($_POST['register'])) {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Set default avatar if no file is uploaded
    $avatar = 'default.png';

    // Check if an avatar is uploaded, if it's a valid image file, and to check for errors
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $target_dir = "uploads/";

        // Generate a unique filename for the uploaded avatar
        $filename = uniqid() . "_" . basename($_FILES["avatar"]["name"]);
        $target_file = $target_dir . $filename;

        // Get the file extension of the uploaded avatar and if it's a valid image type
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed_types = ["jpg", "jpeg", "png", "gif"];

        if (in_array($imageFileType, $allowed_types)) {
            if (move_uploaded_file($_FILES["avatar"]["tmp_name"], $target_file)) {
                $avatar = $filename; // Set the avatar filename as the unique filename if the upload is successful
            } else {
                $error = "Failed to upload avatar. Using default.";
            }
        } else {
            $error = "Invalid avatar file type. Using default.";
        }
    }

    // Check if the email already exists in the database
    $check = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $error = "An account with this email already exists.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif ($password !== $confirm_password) {
        $error = "The passwords do not match.";
    } else {
        // Hash the password using the default php password hashing algorithm
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        
        // Generate a unique account number for the user
        $account_number = '';
        do {
            // Generate a random account number between 1000000000 and 9999999999
            $account_number = strval(mt_rand(1000000000, 9999999999));

            // Check if the account number already exists in the database
            $stmt = $conn->prepare("SELECT user_id FROM users WHERE account_number = ?");
            $stmt->bind_param("s", $account_number);
            $stmt->execute();
            $stmt->store_result();
        } while ($stmt->num_rows > 0); // Keep generating account numbers until it's unique

        // Insert the user details into the database
        $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, avatar, account_number) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $full_name, $email, $hashed, $avatar, $account_number);
        
        // If the insert statement is successful, redirect the user to the login page with a success message
        if ($stmt->execute()) {
            header("Location: login.php?registered=1");
            exit();
        } else {
            $error = "Error registering user.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register - SwiftPay</title>
    <link rel="stylesheet" href="styles.css?v=1.6">
    <script src="scripts.js"></script>
</head>
<body>

<div class="auth-container">
    <div class="card">
        <div class="logo">
            <img src="icons/SwiftPay-trimmed.png" alt="SwiftPay Logo">
        </div>
        <h3 style="text-align: center; margin-top: 0;">Create Your Account</h3>

        <!-- Display error message if there is one -->
        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- Registration form -->
        <form method="post" enctype="multipart/form-data">
            <div style="margin-bottom: 15px;">
                <label for="full_name">Full Name:</label>
                <input type="text" id="full_name" name="full_name" required>
            </div>

            <div style="margin-bottom: 15px;">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div style="margin-bottom: 15px;">
                <label for="password">Password:</label>
                <div class="password-wrapper">
                    <input type="password" name="password" id="password" oninput="checkStrength('password', 'strength')" required>
                    <button type="button" class="toggle-password" onclick="toggleVisibility('password')">Show</button>
                </div>
                <div id="strength" style="margin-top: 5px;"></div>
            </div>

            <div style="margin-bottom: 20px;">
                <label for="confirm_password">Confirm Password:</label>
                <div class="password-wrapper">
                    <input type="password" name="confirm_password" id="confirm_password" required>
                    <button type="button" class="toggle-password" onclick="toggleVisibility('confirm_password')">Show</button>
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <label>Avatar (optional):</label>
                <div id="avatarPreviewWrapper" style="margin-bottom: 10px;">
                    <img id="avatarPreview" src="uploads/default.png" alt="Avatar Preview">
                </div>
                <input type="file" name="avatar" id="avatar" accept="image/*" onchange="previewAvatar(event)">
            </div>

            <button type="submit" name="register" class="btn btn-primary" style="width: 100%;">Register</button>
        </form>
    </div>

    <div style="text-align: center; margin-top: 20px;">
        <p>Already have an account? <a href="login.php">Login here.</a></p>
    </div>
</div>

</body>
</html>