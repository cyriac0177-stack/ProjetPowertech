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

// Récupérer les infos du vendeur
$stmt = $bdd->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Récupérer les produits du vendeur connecté avec leurs catégories
$products = [];
try {
    $stmt = $bdd->prepare("
        SELECT p.*, c.name as categorie_nom 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.status = 'active' AND p.seller_id = ?
        ORDER BY p.id DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Pas de produits
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Produits - Quick Quick Shopping</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-[#fdf1f7] min-h-screen">
    <!-- Header -->
    <header class="bg-white shadow-md sticky top-0 z-50">
        <div class="container mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <a href="dashboard.php" class="flex items-center space-x-2">
                    <i class="fas fa-arrow-left text-[#b06393]"></i>
                    <span class="text-xl font-serif text-[#b06393]">Retour au tableau de bord</span>
                </a>
                
                <a href="add_product.php" class="px-6 py-2 bg-[#b06393] text-white rounded-full hover:bg-[#d87eb6] transition">
                    <i class="fas fa-plus mr-2"></i>Ajouter un produit
                </a>
            </div>
        </div>
    </header>

    <div class="container mx-auto px-4 py-8">
        <div class="max-w-7xl mx-auto">
            <h1 class="text-4xl font-serif text-[#b06393] mb-8">Mes Produits</h1>

            <?php if (empty($products)): ?>
                <div class="bg-white rounded-lg shadow-md p-12 text-center">
                    <i class="fas fa-box text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-2xl font-serif text-gray-700 mb-2">Aucun produit</h3>
                    <p class="text-gray-600 mb-6">Commencez à vendre en ajoutant vos premiers produits !</p>
                    <a href="add_product.php" class="inline-block px-6 py-3 bg-[#b06393] text-white rounded-full hover:bg-[#d87eb6] transition">
                        Ajouter mon premier produit
                    </a>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($products as $product): ?>
                        <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition">
                            <!-- Image -->
                            <div class="h-48 bg-gray-200 flex items-center justify-center">
                                <?php if (!empty($product['image'])): ?>
                                    <img src="../<?= htmlspecialchars($product['image']) ?>" 
                                         alt="<?= htmlspecialchars($product['name']) ?>" 
                                         class="w-full h-full object-cover">
                                <?php else: ?>
                                    <i class="fas fa-image text-6xl text-gray-300"></i>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Contenu -->
                            <div class="p-6">
                                <div class="mb-3">
                                    <?php if (!empty($product['categorie_nom'])): ?>
                                        <span class="inline-block bg-[#fdf1f7] text-[#b06393] px-3 py-1 rounded-full text-xs font-semibold mb-2">
                                            <?= htmlspecialchars($product['categorie_nom']) ?>
                                        </span>
                                    <?php endif; ?>
                                    <h3 class="text-xl font-semibold text-gray-800 mb-2"><?= htmlspecialchars($product['name']) ?></h3>
                                    <?php if (!empty($product['description'])): ?>
                                        <p class="text-sm text-gray-600 line-clamp-2"><?= htmlspecialchars($product['description']) ?></p>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="mb-4">
                                    <p class="text-2xl font-bold text-[#b06393]">
                                        <?= number_format($product['price'], 0, ',', ' ') ?> FCFA
                                    </p>
                                </div>
                                
                                <div class="space-y-2 text-sm text-gray-600 mb-4 pb-4 border-b">
                                    <p><i class="fas fa-box mr-2"></i>Stock: <span class="font-semibold"><?= $product['stock_quantity'] ?></span></p>
                                    <?php if (!empty($product['reference'])): ?>
                                        <p><i class="fas fa-barcode mr-2"></i>Réf: <span class="font-semibold"><?= htmlspecialchars($product['reference']) ?></span></p>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="flex space-x-2">
                                    <a href="edit_product.php?id=<?= $product['id'] ?>" 
                                       class="flex-1 text-center px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition">
                                        <i class="fas fa-edit mr-1"></i>Modifier
                                    </a>
                                    <a href="delete_product.php?id=<?= $product['id'] ?>" 
                                       class="flex-1 text-center px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600 transition"
                                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce produit ?')">
                                        <i class="fas fa-trash mr-1"></i>Supprimer
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

