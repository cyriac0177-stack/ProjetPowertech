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

// Traitement de la modification du statut
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id'] ?? 0);
    $new_status = $_POST['status'] ?? '';
    
    if ($order_id > 0 && in_array($new_status, ['pending', 'confirmed', 'shipped', 'delivered', 'cancelled'])) {
        try {
            // Vérifier que cette commande contient des produits de ce vendeur
            $stmt = $bdd->prepare("
                SELECT DISTINCT oi.order_id 
                FROM order_items oi
                JOIN products p ON oi.product_id = p.id
                WHERE oi.order_id = ? AND p.seller_id = ?
            ");
            $stmt->execute([$order_id, $_SESSION['user_id']]);
            $order_exists = $stmt->fetch();
            
            if ($order_exists) {
                $stmt = $bdd->prepare("UPDATE orders SET status = ? WHERE id = ?");
                $stmt->execute([$new_status, $order_id]);
            }
        } catch (Exception $e) {
            error_log('Erreur modification statut: ' . $e->getMessage());
        }
        
        header('Location: orders.php');
        exit;
    }
}

// Récupérer les infos du vendeur
$stmt = $bdd->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Récupérer les commandes contenant les produits de ce vendeur
$stmt = $bdd->prepare("
    SELECT DISTINCT o.*, u.name as customer_name, u.email as customer_email, u.phone as customer_phone
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    JOIN users u ON o.customer_id = u.id
    WHERE p.seller_id = ?
    ORDER BY o.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Pour chaque commande, récupérer les articles
$orders_with_items = [];
foreach ($orders as $order) {
    $stmt = $bdd->prepare("
        SELECT oi.*, p.name as product_name, p.image
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ? AND p.seller_id = ?
    ");
    $stmt->execute([$order['id'], $_SESSION['user_id']]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $order['items'] = $items;
    $order['total_items'] = array_sum(array_column($items, 'quantity'));
    $order['total_revenue'] = array_sum(array_column($items, 'price')) * array_sum(array_column($items, 'quantity'));
    
    $orders_with_items[] = $order;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Commandes - Espace Vendeur</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-[#fdf1f7] min-h-screen">
    <!-- Header -->
    <header class="bg-white shadow-md sticky top-0 z-50">
        <div class="container mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <a href="dashboard.php" class="flex items-center space-x-2 text-[#b06393] hover:text-[#d87eb6]">
                    <i class="fas fa-arrow-left"></i>
                    <span>Retour au tableau de bord</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-7xl mx-auto">
            <h1 class="text-4xl font-serif text-[#b06393] mb-6">Mes Commandes</h1>
            
            <?php if (empty($orders_with_items)): ?>
                <div class="bg-white rounded-lg shadow-md p-12 text-center">
                    <i class="fas fa-shopping-bag text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-2xl font-serif text-gray-700 mb-2">Aucune commande</h3>
                    <p class="text-gray-600">Vous n'avez pas encore de commandes pour vos produits.</p>
                </div>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($orders_with_items as $order): ?>
                        <div class="bg-white rounded-lg shadow-md p-6">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3 class="text-xl font-semibold text-gray-800">
                                        Commande #<?= $order['id'] ?>
                                    </h3>
                                    <p class="text-sm text-gray-600 mt-1">
                                        <i class="fas fa-calendar mr-1"></i>
                                        <?= date('d/m/Y à H:i', strtotime($order['created_at'])) ?>
                                    </p>
                                </div>
                                <div class="text-right">
                                    <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold
                                        <?= $order['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' ?>
                                        <?= $order['status'] === 'confirmed' ? 'bg-green-100 text-green-800' : '' ?>
                                        <?= $order['status'] === 'shipped' ? 'bg-blue-100 text-blue-800' : '' ?>
                                        <?= $order['status'] === 'delivered' ? 'bg-green-100 text-green-800' : '' ?>
                                        <?= $order['status'] === 'cancelled' ? 'bg-red-100 text-red-800' : '' ?>
                                    ">
                                        <?php
                                        $status_labels = [
                                            'pending' => 'En attente',
                                            'confirmed' => 'Confirmée',
                                            'shipped' => 'Expédiée',
                                            'delivered' => 'Livrée',
                                            'cancelled' => 'Annulée'
                                        ];
                                        echo $status_labels[$order['status']] ?? ucfirst($order['status']);
                                        ?>
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Informations client -->
                            <div class="bg-gray-50 rounded-lg p-4 mb-4">
                                <h4 class="font-semibold text-gray-800 mb-2">
                                    <i class="fas fa-user mr-2"></i>Client
                                </h4>
                                <p class="text-sm text-gray-700"><?= htmlspecialchars($order['customer_name']) ?></p>
                                <?php if (!empty($order['customer_email'])): ?>
                                    <p class="text-sm text-gray-600">
                                        <i class="fas fa-envelope mr-2"></i><?= htmlspecialchars($order['customer_email']) ?>
                                    </p>
                                <?php endif; ?>
                                <?php if (!empty($order['customer_phone'])): ?>
                                    <p class="text-sm text-gray-600">
                                        <i class="fas fa-phone mr-2"></i><?= htmlspecialchars($order['customer_phone']) ?>
                                    </p>
                                <?php endif; ?>
                                <?php if (!empty($order['shipping_address'])): ?>
                                    <p class="text-sm text-gray-600 mt-2">
                                        <i class="fas fa-map-marker-alt mr-2"></i>
                                        <strong>Adresse :</strong> <?= htmlspecialchars($order['shipping_address']) ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Articles -->
                            <div class="border-t pt-4">
                                <h4 class="font-semibold mb-3 flex items-center">
                                    <i class="fas fa-box mr-2 text-[#b06393]"></i>
                                    Vos produits (<?= count($order['items']) ?>)
                                </h4>
                                <div class="space-y-3">
                                    <?php foreach ($order['items'] as $item): ?>
                                        <div class="flex justify-between items-center bg-gray-50 rounded-lg p-4">
                                            <div class="flex items-center space-x-4">
                                                <?php if (!empty($item['image'])): ?>
                                                    <img src="../<?= htmlspecialchars($item['image']) ?>" 
                                                         alt="<?= htmlspecialchars($item['product_name']) ?>" 
                                                         class="w-16 h-16 object-cover rounded">
                                                <?php else: ?>
                                                    <div class="w-16 h-16 bg-gray-200 rounded flex items-center justify-center">
                                                        <i class="fas fa-image text-gray-400"></i>
                                                    </div>
                                                <?php endif; ?>
                                                <div class="flex-1">
                                                    <p class="font-medium text-gray-800"><?= htmlspecialchars($item['product_name']) ?></p>
                                                    <p class="text-sm text-gray-600">
                                                        Quantité : <span class="font-semibold"><?= $item['quantity'] ?></span>
                                                        | Prix unitaire : <span class="font-semibold"><?= number_format($item['price'], 0, ',', ' ') ?> FCFA</span>
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-lg font-bold text-[#b06393]">
                                                    <?= number_format($item['price'] * $item['quantity'], 0, ',', ' ') ?> FCFA
                                                </p>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <!-- Revenus pour cette commande -->
                            <div class="mt-4 pt-4 border-t">
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">Total pour vos produits :</span>
                                    <span class="text-2xl font-bold text-[#b06393]">
                                        <?= number_format($order['total_revenue'], 0, ',', ' ') ?> FCFA
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Changement de statut -->
                            <div class="mt-4 pt-4 border-t">
                                <form method="POST" class="flex items-center space-x-3">
                                    <input type="hidden" name="update_status" value="1">
                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                    <label class="text-sm font-medium text-gray-700">Changer le statut :</label>
                                    <select name="status" onchange="this.form.submit()" 
                                            class="flex-1 border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-[#b06393]">
                                        <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>En attente</option>
                                        <option value="confirmed" <?= $order['status'] === 'confirmed' ? 'selected' : '' ?>>Confirmée</option>
                                        <option value="shipped" <?= $order['status'] === 'shipped' ? 'selected' : '' ?>>Expédiée</option>
                                        <option value="delivered" <?= $order['status'] === 'delivered' ? 'selected' : '' ?>>Livrée</option>
                                        <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>Annulée</option>
                                    </select>
                                    <button type="submit" class="px-4 py-2 bg-[#b06393] text-white rounded hover:bg-[#d87eb6] transition">
                                        <i class="fas fa-save mr-1"></i>Enregistrer
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
