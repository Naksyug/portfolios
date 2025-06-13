<?php
$pdo = new PDO("mysql:host=localhost;dbname=bjm_menu", "root", "");
$sql = "DELETE FROM ingredients WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$_GET['id']]);
header("Location: ingredients.php");
exit;
