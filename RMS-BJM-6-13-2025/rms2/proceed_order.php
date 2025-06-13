<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Direct database connection
$host = 'localhost';
$dbname = 'bjm_menu';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['orders']) || !is_array($data['orders'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid or missing order data']);
    exit;
}

try {
    $conn->beginTransaction();

    foreach ($data['orders'] as $order) {
        $menu_id = $order['id'];
        $qty = $order['quantity'];

        // Get ingredients and amount_needed per menu item
        $stmt = $conn->prepare("SELECT ingredient_id, amount_needed FROM menu_ingredients WHERE menu_id = ?");
        $stmt->execute([$menu_id]);
        $ingredients = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($ingredients as $ingredient) {
            $ingredient_id = $ingredient['ingredient_id'];
            $amount_needed = $ingredient['amount_needed'];
            $total_required = $amount_needed * $qty;

            // Get current quantity from ingredients table
            $check = $conn->prepare("SELECT quantity FROM ingredients WHERE id = ?");
            $check->execute([$ingredient_id]);
            $available = $check->fetchColumn();

            if ($available === false) {
                throw new Exception("Ingredient ID $ingredient_id not found.");
            }

            if ($available < $total_required) {
                throw new Exception("Not enough stock for ingredient ID $ingredient_id.");
            }

            // Deduct quantity from ingredients
            $update = $conn->prepare("UPDATE ingredients SET quantity = quantity - ? WHERE id = ?");
            $update->execute([$total_required, $ingredient_id]);
        }

        $insert = $conn->prepare("INSERT INTO orders (menu_id, quantity, status) VALUES (?, ?, 'Cooking')");
        $insert->execute([$menu_id, $qty]);
    }

    $conn->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
