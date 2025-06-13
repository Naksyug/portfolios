<?php
header('Content-Type: application/json');

$pdo = new PDO("mysql:host=localhost;dbname=bjm_menu", "root", "");
$data = json_decode(file_get_contents("php://input"), true);
$id = $data['id'];

try {
    $pdo->beginTransaction();
    $pdo->prepare("DELETE FROM menu_ingredients WHERE menu_id = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM menus WHERE id = ?")->execute([$id]);
    $pdo->commit();

    echo json_encode(["success" => true]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(["error" => $e->getMessage()]);
}
