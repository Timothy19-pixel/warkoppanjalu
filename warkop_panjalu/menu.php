<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

include 'db.php';

// Handle adding items to the cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['menu_id'])) {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    $menu_id = $_POST['menu_id'];
    if ($_POST['action'] === 'increase') {
        if (isset($_SESSION['cart'][$menu_id])) {
            $_SESSION['cart'][$menu_id]++;
        } else {
            $_SESSION['cart'][$menu_id] = 1;
        }
    } elseif ($_POST['action'] === 'decrease') {
        if (isset($_SESSION['cart'][$menu_id]) && $_SESSION['cart'][$menu_id] > 1) {
            $_SESSION['cart'][$menu_id]--;
        } else {
            unset($_SESSION['cart'][$menu_id]);
        }
    }
}

// Handle clearing the cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clear_cart'])) {
    unset($_SESSION['cart']);
}

// Fetch menu items based on type
$menuType = isset($_GET['menu']) ? $_GET['menu'] : 'all';
$query = $menuType === 'all' ? "SELECT * FROM menu" : "SELECT * FROM menu WHERE type='$menuType'";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu - Warkop Panjalu</title>
    <link rel="stylesheet" href="menu.css">
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <h2>Warkop Panjalu</h2>
            <nav>
                <ul>
                    <li><a href="?menu=food">Makanan</a></li>
                    <li><a href="?menu=drinks">Minuman</a></li>
                    <li><a href="dashboard.php">Back to Dashboard</a></li>
                </ul>
            </nav>
        </div>
        <div class="main-content">
            <h1>Menu</h1>
            <div class="menu-grid">
                <?php
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $image = isset($row['image']) && !empty($row['image']) ? htmlspecialchars($row['image']) : 'default.jpg';
                        echo '<div class="menu-card">';
                        echo '<img src="images/' . $image . '" alt="' . htmlspecialchars($row['name']) . '">';
                        echo '<h3>' . htmlspecialchars($row['name']) . '</h3>';
                        echo '<p>' . htmlspecialchars($row['description']) . '</p>';
                        echo '<p class="price">Rp. ' . number_format($row['price'], 0, ',', '.') . '</p>';
                        echo '<form method="POST">';
                        echo '<input type="hidden" name="menu_id" value="' . $row['id'] . '">';
                        echo '<button type="submit" name="action" value="increase">Add to Order</button>';
                        echo '</form>';
                        echo '</div>';
                    }
                } else {
                    echo '<p>No menu items available.</p>';
                }
                ?>
            </div>
        </div>
        <div class="order-panel">
            <h2>Pesanan Baru</h2>
            <ul class="order-list">
                <?php
                $total = 0;
                if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
                    $ids = implode(',', array_keys($_SESSION['cart']));
                    $result = $conn->query("SELECT * FROM menu WHERE id IN ($ids)");
                    while ($row = $result->fetch_assoc()) {
                        $quantity = $_SESSION['cart'][$row['id']];
                        $subtotal = $row['price'] * $quantity;
                        $total += $subtotal;
                        echo '<li>';
                        echo '<span>' . htmlspecialchars($row['name']) . '</span>';
                        echo '<span>Rp. ' . number_format($subtotal, 0, ',', '.') . '</span>';
                        echo '<div class="quantity-controls">';
                        echo '<form method="POST">';
                        echo '<input type="hidden" name="menu_id" value="' . $row['id'] . '">';
                        echo '<button type="submit" name="action" value="decrease">-</button>';
                        echo '</form>';
                        echo '<span>' . $quantity . '</span>';
                        echo '<form method="POST">';
                        echo '<input type="hidden" name="menu_id" value="' . $row['id'] . '">';
                        echo '<button type="submit" name="action" value="increase">+</button>';
                        echo '</form>';
                        echo '</div>';
                        echo '</li>';
                    }
                } else {
                    echo '<p>No items in your order.</p>';
                }
                ?>
            </ul>
            <div class="order-total">
                <p>Total: <strong>Rp. <?php echo number_format($total, 0, ',', '.'); ?></strong></p>
                <form method="POST">
                    <button type="submit" name="clear_cart" class="clear-button">Clear Order</button>
                </form>
                <a href="checkout.php" class="pay-button">Bayar</a>
            </div>
        </div>
    </div>
</body>
</html>
