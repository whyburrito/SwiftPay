<?php
session_start();
include 'connect.php';

// Check if the user is logged in and has verified their identity to edit their profile
if (!isset($_SESSION['user_id']) || !isset($_SESSION['verified_edit'])) {
    header("Location: profile.php");
    exit();
}

// Get the user ID from the session
$user_id = $_SESSION['user_id'];
$error = '';

// Get user details from the database
$stmt = $conn->prepare("SELECT full_name, email, avatar FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Assign the user details to variables
$full_name = $user['full_name'];
$email = $user['email'];
$avatar_path = $user['avatar'] ?: 'default.png';

// Only runs if the edit profile form is submitted
if (isset($_POST['update_profile'])) {
    $new_name = trim($_POST['full_name']);
    $new_email = trim($_POST['email']);
    $new_avatar_path = $avatar_path;
    
    // Check if an avatar is uploaded, if it's a valid image file, and to check for errors
    if (!empty($_FILES['avatar']['name'])) {
        $target_dir = "uploads/";
        $filename = uniqid() . "_" . basename($_FILES["avatar"]["name"]);
        $target_file = $target_dir . $filename;
        $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if the file type is a valid image type
        if (in_array($file_type, ['jpg', 'jpeg', 'png'])) {
            if (move_uploaded_file($_FILES["avatar"]["tmp_name"], $target_file)) {
                // If the old avatar was not the default avatar, delete it
                if ($avatar_path !== 'default.png' && file_exists("uploads/" . $avatar_path)) {
                    unlink("uploads/" . $avatar_path);
                }
                $new_avatar_path = $filename;
            } else {
                $error = "Failed to upload avatar.";
            }
        } else {
            $error = "Invalid avatar file type. Only JPG, JPEG, and PNG are allowed.";
        }
    }

    // If there were no errors, update the user's profile
    if (!$error) {
        $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, avatar = ? WHERE user_id = ?");
        $stmt->bind_param("sssi", $new_name, $new_email, $new_avatar_path, $user_id);

        // Execute the statement and redirect to the profile page with a success message
        if ($stmt->execute()) {
            unset($_SESSION['verified_edit']);
            header("Location: profile.php?updated=1");
            exit();
        } else {
            $error = "Error updating profile.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Profile - SwiftPay</title>
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
        <div class="card">
            <h2>Edit Your Profile</h2>

            <!-- Display error message if there is one -->
            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <!-- Edit profile form -->
            <form method="post" enctype="multipart/form-data">
                <div style="margin-bottom: 15px;">
                    <label for="full_name">Full Name:</label>
                    <input type="text" id="full_name" name="full_name" value="<?= htmlspecialchars($full_name) ?>" required>
                </div>

                <div style="margin-bottom: 20px;">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required>
                </div>
                
                <div style="margin-bottom: 20px;">
                    <label>Avatar:</label>
                    <div id="avatarPreviewWrapper" style="margin: 0 0 10px 0;">
                        <img id="avatarPreview" src="uploads/<?= htmlspecialchars($avatar_path) ?>" alt="Avatar Preview">
                    </div>
                    <input type="file" name="avatar" accept=".jpg,.jpeg,.png" onchange="previewAvatar(event)">
                </div>

                <button type="submit" name="update_profile" class="btn btn-primary">Save Changes</button>
                <a href="profile.php" class="btn btn-secondary" style="margin-left: 10px;">Cancel</a>
            </form>
        </div>
    </div>
</div>

</body>
</html>