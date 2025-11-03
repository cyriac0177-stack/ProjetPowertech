<?php
session_start();
include '../config/db.php';

// Vérifier que l'utilisateur est connecté et est un vendeur
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

if ($_SESSION['role'] !== 'seller') {
    // Si admin, rediriger vers son dashboard
    if ($_SESSION['role'] === 'admin') {
        header('Location: ../admin/dashboard.php');
    } else {
        header('Location: ../index.php');
    }
    exit;
}

// Récupérer les infos du vendeur
$stmt = $bdd->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Récupérer les statistiques (si les tables existent)
$stats = [
    'total_products' => 0,
    'total_sales' => 0,
    'total_revenue' => 0,
    'pending_orders' => 0
];

// Compter les produits de ce vendeur uniquement
try {
    $stmt = $bdd->prepare("SELECT COUNT(*) as count FROM products WHERE status = 'active' AND seller_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['total_products'] = $result['count'];
} catch (Exception $e) {
    // Table n'existe pas encore
}

// Calculer les ventes (nombre de commandes)
try {
    $stmt = $bdd->prepare("
        SELECT COUNT(DISTINCT o.id) as count 
        FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        JOIN products p ON oi.product_id = p.id
        WHERE p.seller_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['total_sales'] = $result['count'];
} catch (Exception $e) {
    // Pas de ventes
}

// Calculer les revenus totaux
try {
    $stmt = $bdd->prepare("
        SELECT SUM(oi.price * oi.quantity) as total 
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE p.seller_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['total_revenue'] = $result['total'] ?? 0;
} catch (Exception $e) {
    // Pas de revenus
}

// Compter les commandes en attente
try {
    $stmt = $bdd->prepare("
        SELECT COUNT(DISTINCT o.id) as count 
        FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        JOIN products p ON oi.product_id = p.id
        WHERE p.seller_id = ? AND o.status = 'pending'
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['pending_orders'] = $result['count'];
} catch (Exception $e) {
    // Pas de commandes en attente
}

// Récupérer les produits du vendeur s'il en a avec catégories
$products = [];
try {
    $stmt = $bdd->prepare("
        SELECT p.*, c.name as categorie_nom 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.status = 'active' AND p.seller_id = ?
        ORDER BY p.id DESC 
        LIMIT 6
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Pas de produits pour l'instant
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord Vendeur - Quick Quick Shopping</title>
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
                    <span class="px-3 py-1 bg-orange-100 text-orange-700 rounded-full text-xs font-semibold">Vendeur</span>
                </a>
                
                <div class="flex items-center space-x-4">
                    <a href="../index.php" class="px-4 py-2 text-gray-700 hover:text-[#b06393] transition">Accueil</a>
                    <a href="products.php" class="px-4 py-2 text-gray-700 hover:text-[#b06393] transition">Mes Produits</a>
                    <a href="../logout.php" class="px-4 py-2 text-red-600 hover:text-red-700 transition">Déconnexion</a>
                </div>
            </div>
        </div>
    </header>

    <div class="container mx-auto px-4 py-8">
        <div class="max-w-7xl mx-auto">
            <!-- Bienvenue -->
            <div class="bg-gradient-to-r from-[#b06393] to-[#d87eb6] rounded-lg shadow-lg p-6 mb-8 text-white">
                <h1 class="text-3xl font-bold mb-2">Bienvenue, <?= htmlspecialchars($user['name']) ?> !</h1>
                <p class="text-white/90">Gérez vos ventes et vos produits depuis votre tableau de bord</p>
            </div>

            <!-- Statistiques -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Produits -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm">Produits</p>
                            <p class="text-3xl font-bold text-[#b06393]"><?= $stats['total_products'] ?></p>
                        </div>
                        <i class="fas fa-box text-4xl text-[#b06393] opacity-20"></i>
                    </div>
                </div>

                <!-- Ventes -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm">Ventes</p>
                            <p class="text-3xl font-bold text-[#b06393]"><?= $stats['total_sales'] ?></p>
                        </div>
                        <i class="fas fa-shopping-bag text-4xl text-green-500 opacity-20"></i>
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

                <!-- Commandes en attente -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm">En attente</p>
                            <p class="text-3xl font-bold text-[#b06393]"><?= $stats['pending_orders'] ?></p>
                        </div>
                        <i class="fas fa-clock text-4xl text-orange-500 opacity-20"></i>
                    </div>
                </div>
            </div>

            <!-- Actions rapides -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                <h2 class="text-2xl font-semibold mb-4">
                    <i class="fas fa-bolt mr-2"></i>
                    Actions rapides
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <a href="add_product.php" class="bg-[#b06393] text-white p-6 rounded-lg hover:bg-[#d87eb6] transition text-center">
                        <i class="fas fa-plus-circle text-3xl mb-2"></i>
                        <p class="font-semibold">Ajouter un produit</p>
                    </a>
                    <a href="products.php" class="bg-blue-500 text-white p-6 rounded-lg hover:bg-blue-600 transition text-center">
                        <i class="fas fa-list text-3xl mb-2"></i>
                        <p class="font-semibold">Voir mes produits</p>
                    </a>
                    <a href="orders.php" class="bg-green-500 text-white p-6 rounded-lg hover:bg-green-600 transition text-center">
                        <i class="fas fa-shopping-cart text-3xl mb-2"></i>
                        <p class="font-semibold">Mes commandes</p>
                    </a>
                </div>
            </div>

            <!-- Produits récents -->
            <?php if (!empty($products)): ?>
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-2xl font-semibold">
                            <i class="fas fa-box-open mr-2"></i>
                            Mes produits récents
                        </h2>
                        <a href="products.php" class="text-[#b06393] hover:text-[#d87eb6] font-semibold">
                            Voir tout <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php foreach ($products as $product): ?>
                            <div class="border rounded-lg overflow-hidden hover:shadow-lg transition">
                                <!-- Image -->
                                <div class="h-40 bg-gray-200 flex items-center justify-center">
                                    <?php if (!empty($product['image'])): ?>
                                        <img src="../<?= htmlspecialchars($product['image']) ?>" 
                                             alt="<?= htmlspecialchars($product['name']) ?>" 
                                             class="w-full h-full object-cover">
                                    <?php else: ?>
                                        <i class="fas fa-image text-5xl text-gray-300"></i>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Contenu -->
                                <div class="p-4">
                                    <?php if (!empty($product['categorie_nom'])): ?>
                                        <span class="text-xs bg-[#fdf1f7] text-[#b06393] px-2 py-1 rounded-full font-semibold">
                                            <?= htmlspecialchars($product['categorie_nom']) ?>
                                        </span>
                                    <?php endif; ?>
                                    <h3 class="font-semibold text-lg mt-2"><?= htmlspecialchars($product['name']) ?></h3>
                                    <p class="text-[#b06393] font-bold text-xl my-2">
                                        <?= number_format($product['price'], 0, ',', ' ') ?> FCFA
                                    </p>
                                    <div class="flex items-center justify-between text-sm text-gray-600">
                                        <span><i class="fas fa-box mr-1"></i> Stock: <?= $product['stock_quantity'] ?></span>
                                        <a href="products.php" class="text-[#b06393] hover:underline">Voir détails</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="bg-white rounded-lg shadow-md p-12 text-center">
                    <i class="fas fa-box text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-2xl font-serif text-gray-700 mb-2">Aucun produit</h3>
                    <p class="text-gray-600 mb-6">Commencez à vendre en ajoutant vos premiers produits !</p>
                    <a href="add_product.php" class="inline-block px-6 py-3 bg-[#b06393] text-white rounded-full hover:bg-[#d87eb6] transition">
                        Ajouter mon premier produit
                    </a>
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

