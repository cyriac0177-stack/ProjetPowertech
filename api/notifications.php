<?php
session_start();
require_once '../config/db.php';

header('Content-Type: application/json');

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['notifications' => []]);
    exit;
}

$user_id = $_SESSION['user_id'];

// Créer la table notifications si elle n'existe pas
try {
    $bdd->exec("CREATE TABLE IF NOT EXISTS notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        type ENUM('order', 'promo', 'cart', 'general') DEFAULT 'general',
        title VARCHAR(255) NOT NULL,
        message TEXT,
        link VARCHAR(255),
        seen TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )");
} catch (Exception $e) {
    // Table existe déjà
}

// Action pour marquer comme vue
if (isset($_POST['mark_seen']) && isset($_POST['notification_id'])) {
    $notification_id = intval($_POST['notification_id']);
    $stmt = $bdd->prepare("UPDATE notifications SET seen = 1 WHERE id = ? AND user_id = ?");
    $stmt->execute([$notification_id, $user_id]);
    echo json_encode(['success' => true]);
    exit;
}

// Récupérer les notifications non lues
$stmt = $bdd->prepare("
    SELECT * FROM notifications 
    WHERE user_id = ? AND seen = 0 
    ORDER BY created_at DESC 
    LIMIT 10
");
$stmt->execute([$user_id]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['notifications' => $notifications]);

