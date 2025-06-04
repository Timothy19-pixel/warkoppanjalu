<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

include 'db.php';

// Fetch completed orders
$query = "SELECT * FROM orders WHERE user_id = (SELECT id FROM users WHERE username = '" . $_SESSION['user'] . "')";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Completed Orders - Warkop Panjalu</title>
    <link rel="stylesheet" href="new_order.css">
</head>
<body>
    <div class="container">
        <h1>Completed Orders</h1>
        <div class="orders-list">
            <?php
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo '<div class="order-card">';
                    echo '<h3>Order ID: ' . htmlspecialchars($row['id']) . '</h3>';
                    echo '<p><strong>Items:</strong> ' . htmlspecialchars($row['items']) . '</p>';
                    echo '<p><strong>Total:</strong> Rp. ' . number_format($row['total_price'], 0, ',', '.') . '</p>';
                    echo '<p><strong>Date:</strong> ' . htmlspecialchars($row['created_at']) . '</p>';
                    echo '</div>';
                }
            } else {
                echo '<div class="no-orders">';
                echo '<p>No completed orders found.</p>';
                echo '<a href="menu.php" class="btn-back">Order Now</a>';
                echo '</div>';
            }
            ?>
        </div>
        <a href="dashboard.php" class="btn-back">Back to Dashboard</a>
    </div>
</body>
</html>
