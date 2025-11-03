<?php
session_start();
include '../config/db.php';

// Vérifier que l'utilisateur est connecté et est un vendeur
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

if ($_SESSION['role'] !== 'seller' && $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

$product_id = intval($_GET['id'] ?? 0);

if ($product_id > 0) {
    try {
        // Récupérer le produit du vendeur uniquement pour supprimer l'image
        $stmt = $bdd->prepare("SELECT * FROM products WHERE id = ? AND seller_id = ?");
        $stmt->execute([$product_id, $_SESSION['user_id']]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($product) {
            // Supprimer l'image si elle existe
            if (!empty($product['image'])) {
                $image_path = '../' . $product['image'];
                if (file_exists($image_path)) {
                    unlink($image_path);
                }
            }
            
            // Supprimer le produit du vendeur uniquement
            $stmt = $bdd->prepare("DELETE FROM products WHERE id = ? AND seller_id = ?");
            $stmt->execute([$product_id, $_SESSION['user_id']]);
        }
    } catch (Exception $e) {
        error_log('Erreur suppression produit: ' . $e->getMessage());
    }
}

header('Location: products.php');
exit;


