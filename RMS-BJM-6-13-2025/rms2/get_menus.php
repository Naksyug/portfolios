<?php
header('Content-Type: application/json');

try {
    $pdo = new PDO("mysql:host=localhost;dbname=bjm_menu", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // If an ID is passed, return a single menu with ingredients
    if (isset($_GET['id'])) {
        $id = $_GET['id'];

        $stmt = $pdo->prepare("
            SELECT m.id, m.name, m.price, GROUP_CONCAT(i.name SEPARATOR ',') AS ingredients
            FROM menus m
            JOIN menu_ingredients mi ON m.id = mi.menu_id
            JOIN ingredients i ON mi.ingredient_id = i.id
            WHERE m.id = ?
            GROUP BY m.id
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            echo json_encode([
                "id" => $row["id"],
                "name" => $row["name"],
                "price" => $row["price"],
                "ingredients" => explode(",", $row["ingredients"])
            ]);
        } else {
            echo json_encode(["error" => "Menu not found"]);
        }
    } else {
    // Return all menus with stock status
    $stmt = $pdo->query("
        SELECT m.id, m.name, m.price, MIN(i.quantity) AS min_quantity
        FROM menus m
        JOIN menu_ingredients mi ON m.id = mi.menu_id
        JOIN ingredients i ON mi.ingredient_id = i.id
        GROUP BY m.id
        ORDER BY m.name ASC
    ");

    $menus = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Add out_of_stock flag
    $menus = array_map(function ($menu) {
        $menu['out_of_stock'] = $menu['min_quantity'] <= 0;
        unset($menu['min_quantity']); // optional
        return $menu;
    }, $menus);

    echo json_encode($menus);
}

} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}