<?php
session_start();
include 'config/db.php';

// Fonctions du panier
function getCartItemCount() {
    $count = 0;
    if (isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            $count += $item['quantite'] ?? 1;
        }
    }
    return $count;
}

// Récupérer tous les produits disponibles
$stmt = $bdd->query("
    SELECT p.*, c.name as categorie_nom 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.status = 'active' AND p.stock_quantity > 0
    ORDER BY p.id DESC
");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$cartCount = getCartItemCount();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produits - Quick Quick Shopping</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap" rel="stylesheet">
</head>
<body class="bg-[#fdf1f7] min-h-screen">
    <?php include 'includes/header.php'; ?>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-8">
        <div class="mb-8 text-center">
            <h1 class="text-4xl font-serif text-[#b06393] mb-2">Nos Produits</h1>
            <p class="text-gray-600">Découvrez notre collection</p>
        </div>

        <?php if (empty($products)): ?>
            <div class="bg-white rounded-lg shadow-lg p-12 text-center">
                <i class="fas fa-box text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-2xl font-serif text-gray-700 mb-2">Aucun produit disponible</h3>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <?php foreach ($products as $product): ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition">
                        <a href="product.php?id=<?= $product['id'] ?>">
                            <div class="h-64 bg-gray-200 overflow-hidden">
                                <img src="<?= htmlspecialchars($product['image'] ?? 'images/no-image.jpg') ?>" 
                                     alt="<?= htmlspecialchars($product['name']) ?>"
                                     class="w-full h-full object-cover hover:scale-105 transition duration-300">
                            </div>
                        </a>
                        
                        <div class="p-4">
                            <h3 class="text-lg font-semibold text-gray-800 mb-2">
                                <a href="product.php?id=<?= $product['id'] ?>" class="hover:text-[#b06393] transition">
                                    <?= htmlspecialchars($product['name']) ?>
                                </a>
                            </h3>
                            
                            <p class="text-2xl font-bold text-[#b06393] mb-4">
                                <?= number_format($product['price'], 0, ',', ' ') ?> FCFA
                            </p>
            
                            <?php if ($product['categorie_nom']): ?>
                                <p class="text-sm text-gray-500 mb-2">
                                    <i class="fas fa-tag"></i>
                                    <?= htmlspecialchars($product['categorie_nom']) ?>
                                </p>
                            <?php endif; ?>
                            
                            <div class="flex items-center justify-between mt-4">
                                <?php if ($product['stock_quantity'] > 0): ?>
                                    <span class="text-sm text-green-600">
                                        <i class="fas fa-check"></i>
                                        En stock
                                    </span>
                                <?php else: ?>
                                    <span class="text-sm text-red-600">
                                        <i class="fas fa-times"></i>
                                        Épuisé
                                    </span>
                                <?php endif; ?>
                                
                                <a href="product.php?id=<?= $product['id'] ?>" 
                                   class="px-4 py-2 bg-[#b06393] text-white rounded-full hover:bg-[#d87eb6] transition text-sm font-semibold">
                                    Voir détail
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>

