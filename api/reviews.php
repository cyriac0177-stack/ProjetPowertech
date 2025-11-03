<?php
session_start();
require_once '../config/db.php';

header('Content-Type: application/json');

// Créer la table reviews si elle n'existe pas
try {
    $bdd->exec("CREATE TABLE IF NOT EXISTS reviews (
        id INT AUTO_INCREMENT PRIMARY KEY,
        produit_id INT NOT NULL,
        user_id INT,
        note INT NOT NULL CHECK(note >= 1 AND note <= 5),
        commentaire TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (produit_id) REFERENCES produits(produit_id)
    )");
} catch (Exception $e) {
    // Table existe déjà
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'add':
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Connectez-vous pour commenter']);
            exit;
        }
        
        $produit_id = intval($_POST['produit_id'] ?? 0);
        $note = intval($_POST['note'] ?? 0);
        $commentaire = trim($_POST['commentaire'] ?? '');
        
        if ($produit_id > 0 && $note >= 1 && $note <= 5) {
            try {
                $stmt = $bdd->prepare("INSERT INTO reviews (produit_id, user_id, note, commentaire) VALUES (?, ?, ?, ?)");
                $stmt->execute([$produit_id, $_SESSION['user_id'], $note, $commentaire]);
                echo json_encode(['success' => true, 'message' => 'Avis ajouté']);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Erreur']);
            }
        }
        break;
        
    case 'get':
        $produit_id = intval($_GET['produit_id'] ?? 0);
        
        if ($produit_id > 0) {
            try {
                // Récupérer les avis
                $stmt = $bdd->prepare("
                    SELECT r.*, u.name as user_name 
                    FROM reviews r
                    LEFT JOIN users u ON r.user_id = u.id
                    WHERE r.produit_id = ?
                    ORDER BY r.created_at DESC
                ");
                $stmt->execute([$produit_id]);
                $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Calculer la moyenne
                $avg_stmt = $bdd->prepare("SELECT AVG(note) as avg_note, COUNT(*) as count FROM reviews WHERE produit_id = ?");
                $avg_stmt->execute([$produit_id]);
                $avg_data = $avg_stmt->fetch();
                
                echo json_encode([
                    'success' => true, 
                    'reviews' => $reviews,
                    'average' => round($avg_data['avg_note'], 1),
                    'count' => $avg_data['count']
                ]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'reviews' => []]);
            }
        }
        break;
}

