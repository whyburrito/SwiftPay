<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get the user ID from the session
$user_id = $_SESSION['user_id'];

// Prepare a statement to fetch all of the user's transactions
// The first JOIN fetches the sender's details, the second JOIN fetches the recipient's details (if applicable)
$stmt = $conn->prepare("
    SELECT t.transaction_id, t.transaction_type, t.amount, t.created_at, t.user_id, t.related_user_id, 
           sender.full_name AS sender_name, sender.account_number AS sender_acc,
           recipient.full_name AS recipient_name, recipient.account_number AS recipient_acc
    FROM transactions t
    LEFT JOIN users sender ON t.user_id = sender.user_id
    LEFT JOIN users recipient ON t.related_user_id = recipient.user_id
    WHERE t.user_id = ? OR t.related_user_id = ?
    ORDER BY t.created_at DESC
");
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Transaction History - SwiftPay</title>
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
            <a href="history.php" class="active">History</a>
            <a href="profile.php">Profile</a>
        </nav>
        <a href="logout.php" class="btn btn-secondary logout-btn">Logout</a>
    </div>

    <div class="main-content">
        <div class="card">
            <h2>Transaction History</h2>
            
            <!-- Check if the user has any transactions -->
            <?php if ($result->num_rows > 0): ?>
                <table class="transaction-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Description</th>
                            <th style="text-align: right;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?> <!-- Loop through each transaction -->
                            <tr>
                                <td>
                                    <?= date("M d, Y", strtotime($row['created_at'])) ?><br> <!-- Display the transaction date -->
                                    <span style="font-size: 0.8em; color: var(--secondary-text);"><?= date("h:i A", strtotime($row['created_at'])) ?></span>
                                </td>
                                <td>
                                    <?php
                                        // Display a dynamic description based on the transaction type
                                        $description = '';
                                        if ($row['transaction_type'] === 'transfer') {
                                            // For transfers, specify if it was sent or received, and to/from whom.
                                            if ($row['user_id'] == $user_id) {
                                                // If the user was the sender, display the recipient's name and account number
                                                $description = "Transfer to " . htmlspecialchars($row['recipient_name']) . " (#" . htmlspecialchars($row['recipient_acc']) . ")";
                                            } else {
                                                // If the user was the recipient, display the sender's name and account number
                                                $description = "Transfer from " . htmlspecialchars($row['sender_name']) . " (#" . htmlspecialchars($row['sender_acc']) . ")";
                                            }
                                        } else {
                                            // For all other transactions (like 'top-ups' and 'withdrawals'), display the transaction type in lowercase
                                            $description = ucfirst(str_replace('-', ' ', $row['transaction_type']));
                                        }
                                        echo $description;
                                    ?>
                                </td>
                                <td style="text-align: right;" class="
                                    <?php
                                        // Determine if the transaction is a credit or debit for color coding
                                        $is_credit = ($row['transaction_type'] === 'top-up' || ($row['transaction_type'] === 'transfer' && $row['user_id'] != $user_id));
                                        echo $is_credit ? 'amount-credit' : 'amount-debit';
                                    ?>">
                                    <?= ($is_credit ? '+ ' : '- ') ?>₱<?= number_format($row['amount'], 2) ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?> <!-- Display a message if the user has no transactions -->
                <p>You have no transactions yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>