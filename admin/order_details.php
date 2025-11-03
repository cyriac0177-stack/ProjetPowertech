<?php
session_start();
include '../config/db.php';

// Vérifier que l'utilisateur est connecté et est un admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}

$order_id = intval($_GET['id'] ?? 0);

if ($order_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID de commande invalide']);
    exit;
}

try {
    // Récupérer les informations de la commande
    $stmt = $bdd->prepare("
        SELECT o.*, u.name as customer_name, u.email 
        FROM orders o 
        LEFT JOIN users u ON o.customer_id = u.id 
        WHERE o.id = ?
    ");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        echo json_encode(['success' => false, 'message' => 'Commande introuvable']);
        exit;
    }
    
    // Récupérer les articles de la commande
    $stmt = $bdd->prepare("
        SELECT oi.*, p.name as product_name, p.image
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$order_id]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'order' => $order,
        'items' => $items
    ]);
    
} catch (Exception $e) {
    error_log('Erreur récupération détails: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erreur lors de la récupération des détails']);
}
?>

