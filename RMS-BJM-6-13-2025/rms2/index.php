<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>BJM Admin</title>
  <link rel="stylesheet" href="style.css" />
  <script src="script.js"></script>
</head>
<body>
  <h1>Welcome to BJM</h1>

  <div class="container">
    <!-- Left Sidebar -->
    <aside class="left-column">
      <h3>Functions</h3>
      <!--<button onclick="addMenu()">Add Menu</button>-->
      <a href="ingredients.php"><button>Manage Ingredients</button></a>
       <a href="menu_list.php"><button>View Menu List</button></a>
        <a href="pos.html"><button>POS</button></a>

      <div class="orderList" style="margin-top: 30px;">
    
        <!-- Order List Section -->
<div class="orderList" style="margin-top: 30px;">
  <h3>Order List</h3>

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

  <table style="border-collapse: collapse; width: 100%;">
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
        <form method="POST" style="display:inline;">
            <input type="hidden" name="done_id" value="<?= $order['id'] ?>">
            <button type="submit" style="background-color: #28a745; color: white; border: none; padding: 5px 10px; border-radius: 4px;">Done</button>
        </form>
        <form method="POST" style="display:inline;">
            <input type="hidden" name="cancel_id" value="<?= $order['id'] ?>">
            <button type="submit" style="background-color: #dc3545; color: white; border: none; padding: 5px 10px; border-radius: 4px;">Cancel</button>
        </form>
    <?php elseif (in_array($order['status'], ['Done', 'Cancelled'])): ?>
        <form method="POST" style="display:inline;">
            <input type="hidden" name="delete_id" value="<?= $order['id'] ?>">
            <button type="submit" style="background-color: #6c757d; color: white; border: none; padding: 5px 10px; border-radius: 4px;">Delete</button>
        </form>
    <?php endif; ?>
</td>
        </tr>
    <?php endforeach; ?>
  </table>
</div>

      </div>
    </aside>

    <!-- Main Content Area -->
    <main class="right-column">
      <!-- Quick Nav Buttons -->
      <div class="button-group">
       
       <!-- <a href="kitchen.php"><button>Kitchen Display</button></a> -->
      </div>

      <!-- Ingredient List -->
      <section class="section">
        <h3>All Ingredients</h3>
        <table border="1" cellpadding="10">
          <tr>
            <th>ID</th>
            <th>Ingredient Name</th>
          </tr>
          <?php
          try {
            $pdo = new PDO("mysql:host=localhost;dbname=bjm_menu", "root", "");
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $pdo->query("SELECT * FROM ingredients ORDER BY id ASC");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                echo "</tr>";
            }
          } catch (PDOException $e) {
            echo "<tr><td colspan='2'>Error: " . $e->getMessage() . "</td></tr>";
          }
          ?>
        </table>
      </section>

      <!-- Menu Item Creator -->
      <section class="section">
        <h3>Create New Menu Item</h3>
        <form onsubmit="event.preventDefault(); createMenuItem();">
          <label for="menuName">Menu Name</label>
          <input type="text" id="menuName" placeholder="Enter menu item name" class="hide">

          <label for="menuPrice">Menu Price</label>
          <input type="number" id="menuPrice" placeholder="Enter price" class="hide">

          <label for="ingredientsNum">Number of Ingredients</label>
          <input type="number" id="ingredientsNum" min="1" placeholder="Enter how many ingredients" class="hide">

          <div id="inputContainer"></div>

          <button type="submit" style="margin-top: 10px;">Create Menu Item</button>
        </form>
      </section>

      <div id="menuContainer" style="margin-top: 20px;"></div>
    </main>
  </div>
</body>
</html>
