<?php
session_start();
require_once '../config/db.php';

header('Content-Type: application/json');

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non connecté']);
    exit;
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$produit_id = intval($_POST['produit_id'] ?? $_GET['produit_id'] ?? 0);

// Créer la table wishlist si elle n'existe pas
try {
    $bdd->exec("CREATE TABLE IF NOT EXISTS wishlist (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        produit_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_wishlist (user_id, produit_id),
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (produit_id) REFERENCES produits(produit_id)
    )");
} catch (Exception $e) {
    // Table existe déjà
}

switch ($action) {
    case 'add':
        if ($produit_id > 0) {
            try {
                $stmt = $bdd->prepare("INSERT INTO wishlist (user_id, produit_id) VALUES (?, ?)");
                $stmt->execute([$user_id, $produit_id]);
                echo json_encode(['success' => true, 'message' => 'Produit ajouté aux favoris']);
            } catch (Exception $e) {
                if (strpos($e->getMessage(), 'Duplicate') !== false) {
                    echo json_encode(['success' => false, 'message' => 'Déjà dans les favoris']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Erreur']);
                }
            }
        }
        break;
        
    case 'remove':
        if ($produit_id > 0) {
            try {
                $stmt = $bdd->prepare("DELETE FROM wishlist WHERE user_id = ? AND produit_id = ?");
                $stmt->execute([$user_id, $produit_id]);
                echo json_encode(['success' => true, 'message' => 'Produit retiré des favoris']);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Erreur']);
            }
        }
        break;
        
    case 'check':
        if ($produit_id > 0) {
            try {
                $stmt = $bdd->prepare("SELECT * FROM wishlist WHERE user_id = ? AND produit_id = ?");
                $stmt->execute([$user_id, $produit_id]);
                $result = $stmt->fetch();
                echo json_encode(['success' => true, 'in_wishlist' => $result ? true : false]);
            } catch (Exception $e) {
                echo json_encode(['success' => true, 'in_wishlist' => false]);
            }
        }
        break;
        
    case 'list':
        try {
            $stmt = $bdd->prepare("
                SELECT w.*, p.*, c.name as categorie_nom 
                FROM wishlist w
                JOIN produits p ON w.produit_id = p.produit_id
                LEFT JOIN categories c ON p.categorie_id = c.id
                WHERE w.user_id = ?
                ORDER BY w.created_at DESC
            ");
            $stmt->execute([$user_id]);
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'products' => $products]);
        } catch (Exception $e) {
            echo json_encode(['success' => true, 'products' => []]);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Action invalide']);
}

