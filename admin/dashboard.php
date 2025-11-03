<?php
session_start();
include '../config/db.php';

// Vérifier que l'utilisateur est connecté et est un admin
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

if ($_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

// Récupérer les infos de l'admin
$stmt = $bdd->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Récupérer les statistiques générales
$stats = [
    'total_users' => 0,
    'total_products' => 0,
    'total_orders' => 0,
    'total_revenue' => 0,
    'total_sellers' => 0,
    'total_customers' => 0,
    'pending_orders' => 0
];

try {
    // Total utilisateurs
    $stmt = $bdd->query("SELECT COUNT(*) as count FROM users");
    $stats['total_users'] = $stmt->fetch()['count'];
    
    // Total produits
    $stmt = $bdd->query("SELECT COUNT(*) as count FROM products");
    $stats['total_products'] = $stmt->fetch()['count'];
    
    // Total commandes
    $stmt = $bdd->query("SELECT COUNT(*) as count FROM orders");
    $stats['total_orders'] = $stmt->fetch()['count'];
    
    // Revenus totaux
    $stmt = $bdd->query("SELECT SUM(total_amount) as total FROM orders WHERE payment_status = 'paid'");
    $stats['total_revenue'] = $stmt->fetch()['total'] ?? 0;
    
    // Total vendeurs
    $stmt = $bdd->query("SELECT COUNT(*) as count FROM users WHERE role = 'seller'");
    $stats['total_sellers'] = $stmt->fetch()['count'];
    
    // Total clients
    $stmt = $bdd->query("SELECT COUNT(*) as count FROM users WHERE role = 'customer'");
    $stats['total_customers'] = $stmt->fetch()['count'];
    
    // Commandes en attente
    $stmt = $bdd->query("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'");
    $stats['pending_orders'] = $stmt->fetch()['count'];
} catch (Exception $e) {
    error_log('Erreur stats admin: ' . $e->getMessage());
}

// Récupérer les commandes récentes
$recent_orders = [];
try {
    $stmt = $bdd->query("
        SELECT o.*, u.name as customer_name, u.email 
        FROM orders o 
        LEFT JOIN users u ON o.customer_id = u.id 
        ORDER BY o.created_at DESC 
        LIMIT 10
    ");
    $recent_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log('Erreur récupération commandes: ' . $e->getMessage());
    $recent_orders = [];
}

// Récupérer les utilisateurs récents
$recent_users = [];
try {
    $stmt = $bdd->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 10");
    $recent_users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $recent_users = [];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord Admin - Quick Quick Shopping</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-[#fdf1f7] min-h-screen">
    <!-- Header -->
    <header class="bg-white shadow-md sticky top-0 z-50">
        <div class="container mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <a href="../index.php" class="flex items-center space-x-2">
                    <img src="../images/logo.png" alt="Logo" class="h-12">
                    <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-xs font-semibold">Administrateur</span>
                </a>
                
                <div class="flex items-center space-x-4">
                    <a href="users.php" class="px-4 py-2 text-gray-700 hover:text-[#b06393] transition">
                        <i class="fas fa-users mr-2"></i>Utilisateurs
                    </a>
                    <a href="products.php" class="px-4 py-2 text-gray-700 hover:text-[#b06393] transition">
                        <i class="fas fa-box mr-2"></i>Produits
                    </a>
                    <a href="orders.php" class="px-4 py-2 text-gray-700 hover:text-[#b06393] transition">
                        <i class="fas fa-shopping-cart mr-2"></i>Commandes
                    </a>
                    <a href="../logout.php" class="px-4 py-2 text-red-600 hover:text-red-700 transition">
                        <i class="fas fa-sign-out-alt mr-2"></i>Déconnexion
                    </a>
                </div>
            </div>
        </div>
    </header>

    <div class="container mx-auto px-4 py-8">
        <div class="max-w-7xl mx-auto">
            <!-- Bienvenue -->
            <div class="bg-gradient-to-r from-red-600 to-red-700 rounded-lg shadow-lg p-6 mb-8 text-white">
                <h1 class="text-3xl font-bold mb-2">Bienvenue, <?= htmlspecialchars($user['name']) ?> !</h1>
                <p class="text-white/90">Tableau de bord d'administration</p>
            </div>

            <!-- Statistiques -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Utilisateurs -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm">Utilisateurs</p>
                            <p class="text-3xl font-bold text-[#b06393]"><?= $stats['total_users'] ?></p>
                        </div>
                        <i class="fas fa-users text-4xl text-blue-500 opacity-20"></i>
                    </div>
                </div>

                <!-- Produits -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm">Produits</p>
                            <p class="text-3xl font-bold text-[#b06393]"><?= $stats['total_products'] ?></p>
                        </div>
                        <i class="fas fa-box text-4xl text-green-500 opacity-20"></i>
                    </div>
                </div>

                <!-- Commandes -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm">Commandes</p>
                            <p class="text-3xl font-bold text-[#b06393]"><?= $stats['total_orders'] ?></p>
                        </div>
                        <i class="fas fa-shopping-cart text-4xl text-purple-500 opacity-20"></i>
                    </div>
                </div>

                <!-- Revenus -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm">Revenus</p>
                            <p class="text-3xl font-bold text-[#b06393]"><?= number_format($stats['total_revenue'], 0, ',', ' ') ?> FCFA</p>
                        </div>
                        <i class="fas fa-money-bill-wave text-4xl text-yellow-500 opacity-20"></i>
                    </div>
                </div>
            </div>

            <!-- Détails -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Répartition -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-2xl font-semibold mb-4">
                        <i class="fas fa-chart-pie mr-2"></i>
                        Répartition des utilisateurs
                    </h2>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Vendeurs</span>
                            <span class="font-bold text-[#b06393]"><?= $stats['total_sellers'] ?></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Clients</span>
                            <span class="font-bold text-[#b06393]"><?= $stats['total_customers'] ?></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Commandes en attente</span>
                            <span class="font-bold text-orange-500"><?= $stats['pending_orders'] ?></span>
                        </div>
                    </div>
                </div>

                <!-- Actions rapides -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-2xl font-semibold mb-4">
                        <i class="fas fa-bolt mr-2"></i>
                        Actions rapides
                    </h2>
                    <div class="grid grid-cols-2 gap-4">
                        <a href="users.php" class="bg-blue-500 text-white p-4 rounded-lg hover:bg-blue-600 transition text-center">
                            <i class="fas fa-users text-2xl mb-2"></i>
                            <p class="font-semibold text-sm">Gérer utilisateurs</p>
                        </a>
                        <a href="products.php" class="bg-green-500 text-white p-4 rounded-lg hover:bg-green-600 transition text-center">
                            <i class="fas fa-box text-2xl mb-2"></i>
                            <p class="font-semibold text-sm">Gérer produits</p>
                        </a>
                        <a href="orders.php" class="bg-purple-500 text-white p-4 rounded-lg hover:bg-purple-600 transition text-center">
                            <i class="fas fa-shopping-cart text-2xl mb-2"></i>
                            <p class="font-semibold text-sm">Gérer commandes</p>
                        </a>
                        <a href="categories.php" class="bg-yellow-500 text-white p-4 rounded-lg hover:bg-yellow-600 transition text-center">
                            <i class="fas fa-tags text-2xl mb-2"></i>
                            <p class="font-semibold text-sm">Gérer catégories</p>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Commandes récentes -->
            <?php if (!empty($recent_orders)): ?>
                <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                    <h2 class="text-2xl font-semibold mb-4">
                        <i class="fas fa-shopping-bag mr-2"></i>
                        Commandes récentes
                    </h2>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-left py-2">ID</th>
                                    <th class="text-left py-2">Client</th>
                                    <th class="text-left py-2">Total</th>
                                    <th class="text-left py-2">Statut</th>
                                    <th class="text-left py-2">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_orders as $order): ?>
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="py-2">#<?= $order['id'] ?></td>
                                    <td class="py-2"><?= htmlspecialchars($order['customer_name'] ?? 'N/A') ?></td>
                                    <td class="py-2 font-bold text-[#b06393]"><?= number_format($order['total_amount'], 0, ',', ' ') ?> FCFA</td>
                                    <td class="py-2">
                                        <span class="px-2 py-1 rounded-full text-xs 
                                            <?= $order['status'] === 'pending' ? 'bg-yellow-100 text-yellow-700' : '' ?>
                                            <?= $order['status'] === 'confirmed' ? 'bg-green-100 text-green-700' : '' ?>
                                            <?= $order['status'] === 'shipped' ? 'bg-blue-100 text-blue-700' : '' ?>
                                            <?= $order['status'] === 'delivered' ? 'bg-green-100 text-green-700' : '' ?>
                                            <?= $order['status'] === 'cancelled' ? 'bg-red-100 text-red-700' : '' ?>
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
                                    </td>
                                    <td class="py-2 text-sm text-gray-600"><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Utilisateurs récents -->
            <?php if (!empty($recent_users)): ?>
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-2xl font-semibold mb-4">
                        <i class="fas fa-user-plus mr-2"></i>
                        Utilisateurs récents
                    </h2>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-left py-2">Nom</th>
                                    <th class="text-left py-2">Email</th>
                                    <th class="text-left py-2">Rôle</th>
                                    <th class="text-left py-2">Date inscription</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_users as $u): ?>
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="py-2"><?= htmlspecialchars($u['name']) ?></td>
                                    <td class="py-2 text-sm text-gray-600"><?= htmlspecialchars($u['email']) ?></td>
                                    <td class="py-2">
                                        <span class="px-2 py-1 rounded-full text-xs bg-blue-100 text-blue-700">
                                            <?= ucfirst($u['role']) ?>
                                        </span>
                                    </td>
                                    <td class="py-2 text-sm text-gray-600"><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-white mt-12 py-8 border-t border-gray-200">
        <div class="container mx-auto px-4">
            <div class="text-center">
                <p class="text-gray-600">&copy; 2024 Quick Quick Shopping. Tous droits réservés.</p>
            </div>
        </div>
    </footer>
</body>
</html>


