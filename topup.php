<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get the user ID from the session
$user_id = $_SESSION['user_id'];
$success = false; // Initialize success modal to false
$error = '';

// Only runs if the top-up form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the amount from the form and convert it to float
    $amount = floatval($_POST['amount']);

    // Input validation
    if ($amount <= 0) {
        $error = "Amount must be greater than 0.";
    } else {
        // Start a transaction
        $conn->begin_transaction();
        try {
            // Update the user's balance
            $stmt = $conn->prepare("UPDATE users SET balance = balance + ? WHERE user_id = ?");
            $stmt->bind_param("di", $amount, $user_id);
            $stmt->execute();

            // Insert the transaction as a 'top-up' in the 'transactions' table
            $stmt = $conn->prepare("INSERT INTO transactions (user_id, transaction_type, amount, related_user_id) VALUES (?, 'top-up', ?, NULL)");
            $stmt->bind_param("id", $user_id, $amount);
            $stmt->execute();
            
            // Commit the transaction if there were no errors with the SQL statements
            $conn->commit();
            $success = true; // Set success to true to display a success message

        } catch (Exception $e) {
            // Rollback the transaction if there was an error with the SQL statements
            $conn->rollback();
            // Uncomment the following line for debugging purposes
            //$error = $e->getMessage();
            $error = "An error occurred during the transaction.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Top-up - SwiftPay</title>
    <link rel="stylesheet" href="styles.css?v=1.5">
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
            <a href="profile.php">Profile</a>
        </nav>
        <a href="logout.php" class="btn btn-secondary logout-btn">Logout</a>
    </div>

    <div class="main-content">
        <div class="card">
            <h2>Top-up Balance</h2>
            
            <!-- Display error message if there is one -->
            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <!-- Top-up form -->
            <form method="post">
                <div style="margin-bottom: 20px;">
                    <label for="amount">Amount (₱):</label>
                    <input type="number" id="amount" name="amount" step="0.01" required>
                </div>
                
                <button type="submit" class="btn btn-primary">Confirm Top-up</button>
                <a href="dashboard.php" class="btn btn-secondary" style="margin-left: 10px;">Cancel</a>
            </form>
        </div>
    </div>
</div>

<!-- Modal for success message -->
<div id="successModal" class="modal">
    <div class="modal-content">
        <h3>Top-up Successful!</h3>
        <p>Your new balance is reflected on your dashboard.</p>
        <div style="margin-top: 20px;">
            <a href="dashboard.php" class="btn btn-primary">Go to Dashboard</a>
            <button type="button" onclick="closeModal('successModal')" class="btn btn-secondary">Top-up Again</button>
        </div>
    </div>
</div>

<!-- Trigger the success modal if the transaction was successful -->
<?php if ($success): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        openModal('successModal');
    });
</script>
<?php endif; ?>

</body>
</html>