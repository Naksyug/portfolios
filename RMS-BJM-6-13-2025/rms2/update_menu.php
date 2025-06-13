<?php
header('Content-Type: application/json');

$pdo = new PDO("mysql:host=localhost;dbname=bjm_menu", "root", "");
$data = json_decode(file_get_contents("php://input"), true);

$id = $data['id'];
$name = trim($data['name']);
$price = $data['price'];
$ingredients = $data['ingredients'];

try {
    $pdo->beginTransaction();

    $pdo->prepare("UPDATE menus SET name=?, price=? WHERE id=?")
        ->execute([$name, $price, $id]);

    // Delete old ingredients links
    $pdo->prepare("DELETE FROM menu_ingredients WHERE menu_id=?")
        ->execute([$id]);

    foreach ($ingredients as $ingredient) {
        $ingredient = trim($ingredient);
        if (!$ingredient) continue;

        $pdo->prepare("INSERT IGNORE INTO ingredients (name) VALUES (?)")
            ->execute([$ingredient]);

        $stmt = $pdo->prepare("SELECT id FROM ingredients WHERE name = ?");
        $stmt->execute([$ingredient]);
        $ingr_id = $stmt->fetchColumn();

        $pdo->prepare("INSERT INTO menu_ingredients (menu_id, ingredient_id) VALUES (?, ?)")
            ->execute([$id, $ingr_id]);
    }

    $pdo->commit();
    echo json_encode(["success" => true]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(["error" => $e->getMessage()]);
}
