<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ingredient Inventory</title>
    <link rel="stylesheet" href="style.css">
    <script src="script.js"></script>
</head>
<body>
    <a href="index.php"><button>⬅ Back to Dashboard</button></a>
    
    <h2>Ingredient Inventory</h2>

    <form method="POST" action="add_ingredient.php">
        <input type="text" name="name" placeholder="Ingredient name" required>
        <input type="number" name="quantity" placeholder="Quantity" required>
        <button type="submit">Add Ingredient</button>
    </form>

    <table border="1" cellpadding="8">
        <tr>
            <th>Name</th>
            <th>Quantity</th>
            <th>Actions</th>
        </tr>
        <?php
        $pdo = new PDO("mysql:host=localhost;dbname=bjm_menu", "root", "");
        $stmt = $pdo->query("SELECT * FROM ingredients ORDER BY name ASC");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<form method='POST' action='update_ingredient.php'>";
            echo "<td><input type='text' name='name' value='".htmlspecialchars($row['name'])."'></td>";
            echo "<td><input type='number' name='quantity' value='".$row['quantity']."'></td>";
            echo "<td>
                    <input type='hidden' name='id' value='".$row['id']."'>
                    <button type='submit'>Update</button>
                    <a href='delete_ingredient.php?id=".$row['id']."' class='delete-button' onclick='return confirm(\"Delete this ingredient?\")'>Delete</a>
                  </td>";
            echo "</form>";
            echo "</tr>";
        }
        ?>
    </table>
</body>
</html>