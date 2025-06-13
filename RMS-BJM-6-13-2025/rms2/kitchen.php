<?php
$pdo = new PDO("mysql:host=localhost;dbname=bjm_menu", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// ✅ Mark as Done
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['done_id'])) {
    $stmt = $pdo->prepare("UPDATE orders SET status = 'Done' WHERE id = ?");
    $stmt->execute([$_POST['done_id']]);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// ❌ Cancel Order and return ingredients
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_id'])) {
    $order_id = $_POST['cancel_id'];

    // Get order details
    $stmt = $pdo->prepare("SELECT menu_id, quantity FROM orders WHERE id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($order) {
        $menu_id = $order['menu_id'];
        $qty = $order['quantity'];

        // Get ingredients for this menu item
        $stmt = $pdo->prepare("SELECT ingredient_id, amount_needed FROM menu_ingredients WHERE menu_id = ?");
        $stmt->execute([$menu_id]);
        $ingredients = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($ingredients as $ingredient) {
            $ingredient_id = $ingredient['ingredient_id'];
            $amount = $ingredient['amount_needed'] * $qty;

            // Return ingredients to stock
            $update = $pdo->prepare("UPDATE ingredients SET quantity = quantity + ? WHERE id = ?");
            $update->execute([$amount, $ingredient_id]);
        }
    }

    // Set status to Cancelled
    $update_status = $pdo->prepare("UPDATE orders SET status = 'Cancelled' WHERE id = ?");
    $update_status->execute([$order_id]);

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// 🗑️ Delete Order
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delete = $pdo->prepare("DELETE FROM orders WHERE id = ?");
    $delete->execute([$_POST['delete_id']]);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Fetch all orders
$stmt = $pdo->query("SELECT o.id, m.name AS menu_name, o.quantity, o.status, o.created_at
                     FROM orders o
                     JOIN menus m ON o.menu_id = m.id
                     ORDER BY o.created_at DESC");
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Kitchen Display</title>
    <meta http-equiv="refresh" content="10"> <!-- Auto-refresh every 10 seconds -->
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid #333;
            padding: 8px;
            text-align: left;
        }
        form {
            display: inline;
        }
        button {
            margin: 2px;
        }
    </style>
</head>
<body>

<h2>Kitchen Orders</h2>

<table>
    <tr>
        <th>Order ID</th>
        <th>Menu Item</th>
        <th>Quantity</th>
        <th>Status</th>
        <th>Time</th>
        <th>Actions</th>
    </tr>

    <?php foreach ($orders as $order): ?>
        <tr>
            <td><?= $order['id'] ?></td>
            <td><?= htmlspecialchars($order['menu_name']) ?></td>
            <td><?= $order['quantity'] ?></td>
            <td><?= htmlspecialchars($order['status']) ?></td>
            <td><?= $order['created_at'] ?></td>
            <td>
                <?php if ($order['status'] === 'Cooking'): ?>
                    <!-- Show Done and Cancel buttons -->
                    <form method="POST">
                        <input type="hidden" name="done_id" value="<?= $order['id'] ?>">
                        <button type="submit">Mark as Done</button>
                    </form>

                    <form method="POST">
                        <input type="hidden" name="cancel_id" value="<?= $order['id'] ?>">
                        <button type="submit">Cancel</button>
                    </form>

                <?php elseif (in_array($order['status'], ['Done', 'Cancelled'])): ?>
                    <!-- Show only Delete button -->
                    <form method="POST">
                        <input type="hidden" name="delete_id" value="<?= $order['id'] ?>">
                        <button type="submit">Delete</button>
                    </form>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

</body>
</html>
