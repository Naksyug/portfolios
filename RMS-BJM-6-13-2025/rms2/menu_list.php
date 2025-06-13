<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Menu List</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<a href="index.php"><button>⬅ Back to Dashboard</button></a>

<h3>All Menus with Ingredients</h3>


<table border="1" cellpadding="10">
    <tr>
        <th>Menu Name</th>
        <th>Price</th>
        <th>Ingredients</th>
        <th>Actions</th>
    </tr>

    <?php
    try {
        $pdo = new PDO("mysql:host=localhost;dbname=bjm_menu", "root", "");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql = "
            SELECT 
                m.id,
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
            echo "<td>
                <button onclick='editMenu(" . $row['id'] . ")'>Edit</button>
                <button class='delete-button' onclick='deleteMenu(" . $row['id'] . ")'>Delete</button>
            </td>";
            echo "</tr>";
        }

    } catch (PDOException $e) {
        echo "<tr><td colspan='4'>Error: " . $e->getMessage() . "</td></tr>";
    }
    ?>
</table>

<!-- Edit Form -->
<div id="editForm" style="display:none; margin-top:20px;">
    <h3>Edit Menu</h3>
    <form onsubmit="submitEdit(event)">
        <input type="hidden" id="editId">
        <label>Menu Name: <input type="text" id="editName" required></label><br>
        <label>Price: <input type="number" id="editPrice" required></label><br>
        <label>Ingredients (comma-separated):<br>
        <textarea id="editIngredients" rows="3" cols="40" required></textarea></label><br>
        <button type="submit">Update</button>
        <button type="button" onclick="cancelEdit()">Cancel</button>
    </form>
</div>

<script>
function editMenu(id) {
    fetch(`get_menus.php?id=${id}`)
        .then(res => res.json())
        .then(data => {
            document.getElementById("editId").value = data.id;
            document.getElementById("editName").value = data.name;
            document.getElementById("editPrice").value = data.price;
            document.getElementById("editIngredients").value = data.ingredients.join(", ");
            document.getElementById("editForm").style.display = "block";
        });
}

function submitEdit(e) {
    e.preventDefault();

    const id = document.getElementById("editId").value;
    const name = document.getElementById("editName").value.trim();
    const price = document.getElementById("editPrice").value;
    const ingredients = document.getElementById("editIngredients").value
        .split(",")
        .map(i => i.trim());

    fetch("update_menu.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ id, name, price, ingredients })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert("Menu updated successfully.");
            location.reload();
        } else {
            alert("Update failed: " + data.error);
        }
    });
}

function cancelEdit() {
    document.getElementById("editForm").style.display = "none";
}

function deleteMenu(id) {
    if (!confirm("Are you sure you want to delete this menu?")) return;

    fetch("delete_menu.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ id })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert("Menu deleted.");
            location.reload();
        } else {
            alert("Delete failed: " + data.error);
        }
    });
}
</script>