<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Menu List</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<h1>All Menus with Ingredients</h1>

<table border="1" cellpadding="10">
    <tr>
        <th>Menu Name</th>
        <th>Price</th>
        <th>Ingredients</th>
    </tr>

    <?php
    try {
        $pdo = new PDO("mysql:host=localhost;dbname=bjm_menu", "root", "");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql = "
            SELECT 
                m.name AS menu_name,
                m.price,
                GROUP_CONCAT(i.name ORDER BY i.name SEPARATOR ', ') AS ingredients
            FROM 
                menus m
            JOIN 
                menu_ingredients mi ON m.id = mi.menu_id
            JOIN 
                ingredients i ON mi.ingredient_id = i.id
            GROUP BY 
                m.id
            ORDER BY 
                m.name ASC
        ";

        $stmt = $pdo->query($sql);

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['menu_name']) . "</td>";
            echo "<td>P" . number_format($row['price'], 2) . "</td>";
            echo "<td>" . htmlspecialchars($row['ingredients']) . "</td>";
            echo "</tr>";
        }

    } catch (PDOException $e) {
        echo "<tr><td colspan='3'>Error: " . $e->getMessage() . "</td></tr>";
    }
    ?>
</table>

<!-- Back button -->
<br>
<a href="index.php">
    <button>Back to Admin Page</button>
</a>

</body>
</html>