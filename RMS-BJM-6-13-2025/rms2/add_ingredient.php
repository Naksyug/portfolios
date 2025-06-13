<?php
$pdo = new PDO("mysql:host=localhost;dbname=bjm_menu", "root", "");
$sql = "INSERT INTO ingredients (name, quantity) VALUES (?, ?)";
$stmt = $pdo->prepare($sql);
$stmt->execute([$_POST['name'], $_POST['quantity']]);
header("Location: ingredients.php");
exit;
