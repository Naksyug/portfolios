<?php
$pdo = new PDO("mysql:host=localhost;dbname=bjm_menu", "root", "");
$sql = "UPDATE ingredients SET name = ?, quantity = ? WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$_POST['name'], $_POST['quantity'], $_POST['id']]);
header("Location: ingredients.php");
exit;
