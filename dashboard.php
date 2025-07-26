<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get the user ID from the session
$user_id = $_SESSION['user_id'];

// Get user details from the database
$stmt = $conn->prepare("SELECT full_name, account_number, balance FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Assign the user details to variables
$full_name = $user['full_name'];
$account_number = $user['account_number'];
$balance = $user['balance'];

// Prepare a statement to fetch the user's last 5 transactions
$history_stmt = $conn->prepare("
    SELECT t.created_at, t.transaction_type, t.amount, t.user_id, 
            u.full_name as related_name, u.account_number as related_acc
    FROM transactions t
    LEFT JOIN users u ON t.related_user_id = u.user_id
    WHERE t.user_id = ? OR t.related_user_id = ?
    ORDER BY t.created_at DESC
    LIMIT 5
");
$history_stmt->bind_param("ii", $user_id, $user_id);
$history_stmt->execute();
$history_result = $history_stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - SwiftPay</title>
    <link rel="stylesheet" href="styles.css">
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
            <a href="dashboard.php" class="active">Dashboard</a>
            <a href="transfer.php">Transfer</a>
            <a href="history.php">History</a>
            <a href="profile.php">Profile</a>
        </nav>
        <a href="logout.php" class="btn btn-secondary logout-btn">Logout</a>
    </div>

    <div class="main-content">
        <h2>Welcome, <?= htmlspecialchars($full_name) ?></h2>

        <div class="card balance-card">
            <p>Current Balance</p>
            <h1>₱<?= number_format($balance, 2) ?></h1>
            <!-- Display the account number and a button to copy it to the clipboard -->
            <p>Account: <?= htmlspecialchars($account_number) ?> <button onclick="copyToClipboard('accNum')" class="icon-button" id="accNumContainer"><span id="accNum" style="display:none;"><?= htmlspecialchars($account_number) ?></span><img src="icons/copy-link-icon.png" alt="Copy" class="copy-icon"></button></p>
        </div>

        <h3>Quick Actions</h3>
        <div class="quick-actions">
            <a href="topup.php" class="action-card">Top-up</a>
            <a href="withdraw.php" class="action-card">Withdraw</a>
            <a href="transfer.php" class="action-card">Transfer</a>
        </div>

        <div class="card">
            <h3>Recent Transactions</h3>
            <div class="recent-transactions-list">
                <?php if ($history_result->num_rows > 0): ?>
                    <?php while ($row = $history_result->fetch_assoc()): ?>
                        <div class="transaction-item">
                            <div class="transaction-icon">
                            </div>
                            <div class="transaction-details">
                                <span class="transaction-desc">
                                    <?php
                                        // Display the transaction type and the related user's name and account number
                                        if ($row['transaction_type'] === 'transfer') {
                                            if ($row['user_id'] == $user_id) {
                                                echo "Sent to " . htmlspecialchars($row['related_name']) . " (#" . htmlspecialchars($row['related_acc']) . ")";
                                            } else {
                                                echo "Received from " . htmlspecialchars($row['related_name']) . " (#" . htmlspecialchars($row['related_acc']) . ")";
                                            }
                                        } else {
                                            echo ucfirst(str_replace('-', ' ', $row['transaction_type']));
                                        }
                                    ?>
                                </span>
                                <!-- Display the transaction date -->
                                <span class="transaction-date"><?= date("M d, Y", strtotime($row['created_at'])) ?></span>
                            </div>
                            <div class="transaction-amount 
                                <?php 
                                    // Determine if the transaction is a credit or debit for color coding
                                    $is_credit = ($row['transaction_type'] === 'top-up' || ($row['transaction_type'] === 'transfer' && $row['user_id'] != $user_id));
                                    echo $is_credit ? 'amount-credit' : 'amount-debit';
                                ?>">
                                <?= ($is_credit ? '+ ' : '- ') ?>₱<?= number_format($row['amount'], 2) ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No recent transactions to show.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="scripts.js"></script>
</body>
</html>
