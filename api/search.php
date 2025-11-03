<?php
session_start();
require_once '../config/db.php';

header('Content-Type: application/json');

if (!isset($_GET['q'])) {
    echo json_encode(['products' => []]);
    exit;
}

$search = trim($_GET['q']);

if (strlen($search) < 2) {
    echo json_encode(['products' => []]);
    exit;
}

try {
    $stmt = $bdd->prepare("
        SELECT p.*, c.name as categorie_nom 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.name LIKE ? OR p.description LIKE ?
        AND p.status = 'active'
        ORDER BY p.name ASC
        LIMIT 8
    ");
    $searchTerm = "%$search%";
    $stmt->execute([$searchTerm, $searchTerm]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['products' => $products]);
} catch (Exception $e) {
    echo json_encode(['products' => []]);
}

