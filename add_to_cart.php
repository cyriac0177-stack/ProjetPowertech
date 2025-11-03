<?php
session_start();
include 'config/db.php';
include 'includes/cart_functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $produit_id = intval($_POST['produit_id'] ?? 0);
    $quantite = intval($_POST['quantite'] ?? 1);
    
    if ($produit_id > 0) {
        // Vérifier si le produit existe
        $stmt = $bdd->prepare("SELECT * FROM produits WHERE produit_id = ?");
        $stmt->execute([$produit_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($product && $quantite > 0 && $quantite <= $product['stock']) {
            addToCart($produit_id, $quantite);
            
            echo json_encode([
                'success' => true,
                'message' => 'Produit ajouté au panier',
                'cart_count' => getCartItemCount()
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Stock insuffisant'
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Produit invalide'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Méthode non autorisée'
    ]);
}
?>


