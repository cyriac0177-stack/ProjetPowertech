<?php
session_start();
include 'config/db.php';

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

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

// Récupérer les produits de la wishlist
$products = [];
try {
    $stmt = $bdd->prepare("
        SELECT w.*, p.*, c.name as categorie_nom 
        FROM wishlist w
        JOIN produits p ON w.produit_id = p.produit_id
        LEFT JOIN categories c ON p.categorie_id = c.id
        WHERE w.user_id = ?
        ORDER BY w.created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $products = [];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ma Liste de Souhaits - Quick Quick Shopping</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-[#fdf1f7] min-h-screen">
    <?php include 'includes/header.php'; ?>

    <div class="container mx-auto px-4 py-8 mt-24">
        <h1 class="text-4xl font-serif text-[#b06393] mb-8">
            <i class="fas fa-heart mr-3"></i>Ma Liste de Souhaits
        </h1>

        <?php if (empty($products)): ?>
            <div class="bg-white rounded-lg shadow-md p-12 text-center">
                <i class="fas fa-heart text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-2xl font-semibold text-gray-700 mb-2">Votre liste de souhaits est vide</h3>
                <p class="text-gray-600 mb-6">Découvrez nos produits et ajoutez-les à vos favoris !</p>
                <a href="products_simple.php" class="inline-block px-6 py-3 bg-[#b06393] text-white rounded-full hover:bg-[#d87eb6] transition">
                    <i class="fas fa-shopping-bag mr-2"></i>Voir nos produits
                </a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <?php foreach ($products as $product): ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition group">
                        <!-- Image -->
                        <div class="relative h-64 bg-gray-200">
                            <?php if (!empty($product['image_url'])): ?>
                                <img src="<?= htmlspecialchars($product['image_url']) ?>" 
                                     alt="<?= htmlspecialchars($product['nom']) ?>" 
                                     class="w-full h-full object-cover">
                            <?php else: ?>
                                <i class="fas fa-image text-6xl text-gray-300 absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2"></i>
                            <?php endif; ?>
                            
                            <!-- Bouton retirer de la wishlist -->
                            <button onclick="toggleWishlist(<?= $product['produit_id'] ?>)" 
                                    class="absolute top-2 right-2 w-10 h-10 bg-red-500 text-white rounded-full flex items-center justify-center hover:bg-red-600 transition">
                                <i class="fas fa-heart"></i>
                            </button>
                            
                            <!-- Catégorie -->
                            <?php if (!empty($product['categorie_nom'])): ?>
                                <span class="absolute top-2 left-2 px-3 py-1 bg-[#fdf1f7] text-[#b06393] rounded-full text-xs font-semibold">
                                    <?= htmlspecialchars($product['categorie_nom']) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Contenu -->
                        <div class="p-4">
                            <h3 class="text-lg font-semibold text-gray-800 mb-2"><?= htmlspecialchars($product['nom']) ?></h3>
                            
                            <p class="text-2xl font-bold text-[#b06393] mb-4">
                                <?= number_format($product['prix'], 0, ',', ' ') ?> FCFA
                            </p>
                            
                            <div class="flex items-center justify-between text-sm text-gray-600 mb-4">
                                <span><i class="fas fa-box mr-2"></i> Stock: <?= $product['stock'] ?></span>
                            </div>
                            
                            <div class="flex space-x-2">
                                <a href="product.php?id=<?= $product['produit_id'] ?>" 
                                   class="flex-1 text-center px-4 py-2 bg-[#b06393] text-white rounded hover:bg-[#d87eb6] transition">
                                    <i class="fas fa-eye mr-1"></i>Voir
                                </a>
                                <button onclick="addToCart(<?= $product['produit_id'] ?>)" 
                                        class="flex-1 px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 transition">
                                    <i class="fas fa-cart-plus mr-1"></i>Panier
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function toggleWishlist(produit_id) {
            fetch('api/wishlist.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=remove&produit_id=${produit_id}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            });
        }
        
        function addToCart(produit_id) {
            fetch('add_to_cart.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `produit_id=${produit_id}&quantite=1`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Produit ajouté au panier !');
                }
            });
        }
    </script>
</body>
</html>

