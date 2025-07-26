<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get the user ID from the session
$user_id = $_SESSION['user_id'];
$success = false;
$error = '';

// Get the user's current balance and account number
$stmt = $conn->prepare("SELECT balance, account_number FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$sender = $result->fetch_assoc();

$current_balance = $sender['balance'];
$sender_acc = $sender['account_number'];

// Only runs if the transfer form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Get the amount from the form and convert it to float
    $amount = floatval($_POST['amount']);
    $target_account = trim($_POST['recipient']);

    // Input validation
    if ($amount <= 0) {
        $error = "Invalid amount.";
    } else {
        // Prepare a statement to fetch the recipient's account details
        $stmt = $conn->prepare("SELECT user_id, full_name, status, deleted_at FROM users WHERE account_number = ?");
        $stmt->bind_param("s", $target_account);
        $stmt->execute();
        $target_result = $stmt->get_result();

        // Check if the recipient's account exists
        if ($target_result->num_rows === 0) {
            $error = "Recipient account not found.";
        } else {
            $target = $target_result->fetch_assoc();
            $target_id = $target['user_id'];

            // Check if the recipient's account is active and not deleted
            if ($target['status'] !== 'active' || $target['deleted_at'] !== null) {
                // Prevents transfers to deactivated accounts
                $error = "This account is deactivated and cannot receive funds.";
            } elseif ($target_id === $user_id) {
                // Prevents transfers to the user's own account
                $error = "You cannot transfer to your own account.";
            } elseif ($current_balance < $amount) {
                // Prevents transfers if the user does not have enough balance
                $error = "Insufficient balance.";
            } else {
                // Start a transaction
                $conn->begin_transaction();
                try {
                    // Update the user's balance
                    $stmt = $conn->prepare("UPDATE users SET balance = balance - ? WHERE user_id = ?");
                    $stmt->bind_param("di", $amount, $user_id);
                    $stmt->execute();

                    // Update the recipient's balance
                    $stmt = $conn->prepare("UPDATE users SET balance = balance + ? WHERE user_id = ?");
                    $stmt->bind_param("di", $amount, $target_id);
                    $stmt->execute();

                    // Insert the transaction as a 'transfer' in the 'transactions' table
                    $transaction_type = 'transfer';
                    $stmt = $conn->prepare(
                        "INSERT INTO transactions (user_id, transaction_type, amount, related_user_id) VALUES (?, ?, ?, ?)"
                    );
                    $stmt->bind_param("isdi", $user_id, $transaction_type, $amount, $target_id);
                    $stmt->execute();

                    // Commit the transaction if there were no errors with the SQL statements
                    $conn->commit();
                    $success = true;
                    $current_balance -= $amount;

                } catch (Exception $e) {
                    // Rollback the transaction if there was an error with the SQL statements
                    $conn->rollback();
                    $error = "Transaction failed. Please try again.";
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Transfer - SwiftPay</title>
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
            <a href="transfer.php" class="active">Transfer</a>
            <a href="history.php">History</a>
            <a href="profile.php">Profile</a>
        </nav>
        <a href="logout.php" class="btn btn-secondary logout-btn">Logout</a>
    </div>

    <div class="main-content">
        <div class="card">
            <h2>Transfer Funds</h2>
            <p style="margin-top: -10px; margin-bottom: 20px; color: var(--secondary-text);">
                Your Balance: ₱<?= number_format($current_balance, 2) ?>
            </p>

            <!-- Display error message if there is one -->
            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <!-- Transfer form -->
            <form method="post">
                <div style="margin-bottom: 15px;">
                    <label for="recipient">Recipient's Account Number:</label>
                    <input type="text" id="recipient" name="recipient" value="<?= htmlspecialchars($_POST['recipient'] ?? '') ?>" required>
                </div>

                <div style="margin-bottom: 20px;">
                    <label for="amount">Amount (₱):</label>
                    <input type="number" id="amount" name="amount" step="0.01" min="0.01" value="<?= htmlspecialchars($_POST['amount'] ?? '') ?>" required>
                </div>

                <button type="submit" class="btn btn-primary">Confirm Transfer</button>
                <a href="dashboard.php" class="btn btn-secondary" style="margin-left: 10px;">Cancel</a>
            </form>
        </div>
    </div>
</div>

<!-- Modal for success message -->
<div id="successModal" class="modal">
    <div class="modal-content">
        <h3>Transfer Successful!</h3>
        <p>The funds have been sent.</p>
        <div style="margin-top: 20px;">
            <a href="dashboard.php" class="btn btn-primary">Go to Dashboard</a>
            <button type="button" onclick="closeModal('successModal')" class="btn btn-secondary">Make Another Transfer</button>
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