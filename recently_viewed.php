<?php
session_start();
include 'config/db.php';

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Créer la table recently_viewed si elle n'existe pas
try {
    $bdd->exec("CREATE TABLE IF NOT EXISTS recently_viewed (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        produit_id INT NOT NULL,
        viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (produit_id) REFERENCES produits(produit_id)
    )");
} catch (Exception $e) {
    // Table existe déjà
}

// Récupérer les produits récemment consultés
$products = [];
try {
    $stmt = $bdd->prepare("
        SELECT DISTINCT rv.produit_id, p.*, c.name as categorie_nom 
        FROM recently_viewed rv
        JOIN produits p ON rv.produit_id = p.produit_id
        LEFT JOIN categories c ON p.categorie_id = c.id
        WHERE rv.user_id = ?
        ORDER BY rv.viewed_at DESC
        LIMIT 20
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
    <title>Produits Récemment Consultés - Quick Quick Shopping</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-[#fdf1f7] min-h-screen">
    <div class="bg-white shadow-md sticky top-0 z-50 mb-8">
        <div class="container mx-auto px-4 py-4">
            <a href="index.php" class="inline-flex items-center text-[#b06393] hover:text-[#d87eb6]">
                <i class="fas fa-arrow-left mr-2"></i>
                <span>Retour à l'accueil</span>
            </a>
        </div>
    </div>

    <div class="container mx-auto px-4 py-8">
        <h1 class="text-4xl font-serif text-[#b06393] mb-8">
            <i class="fas fa-history mr-3"></i>Produits récemment consultés
        </h1>

        <?php if (empty($products)): ?>
            <div class="bg-white rounded-lg shadow-md p-12 text-center">
                <i class="fas fa-eye-slash text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-2xl font-semibold text-gray-700 mb-2">Aucun produit consulté</h3>
                <p class="text-gray-600 mb-6">Commencez à explorer nos produits !</p>
                <a href="products_simple.php" class="inline-block px-6 py-3 bg-[#b06393] text-white rounded-full hover:bg-[#d87eb6] transition">
                    <i class="fas fa-shopping-bag mr-2"></i>Découvrir nos produits
                </a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <?php foreach ($products as $product): ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition">
                        <!-- Image -->
                        <div class="h-48 bg-gray-200 flex items-center justify-center relative">
                            <?php if (!empty($product['image_url'])): ?>
                                <img src="<?= htmlspecialchars($product['image_url']) ?>" 
                                     alt="<?= htmlspecialchars($product['nom']) ?>" 
                                     class="w-full h-full object-cover">
                            <?php else: ?>
                                <i class="fas fa-image text-6xl text-gray-300"></i>
                            <?php endif; ?>
                            
                            <!-- Catégorie -->
                            <?php if (!empty($product['categorie_nom'])): ?>
                                <span class="absolute top-2 left-2 px-2 py-1 bg-[#fdf1f7] text-[#b06393] rounded text-xs font-semibold">
                                    <?= htmlspecialchars($product['categorie_nom']) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Contenu -->
                        <div class="p-4">
                            <h3 class="font-semibold text-gray-800 mb-2"><?= htmlspecialchars($product['nom']) ?></h3>
                            
                            <p class="text-2xl font-bold text-[#b06393] mb-4">
                                <?= number_format($product['prix'], 0, ',', ' ') ?> FCFA
                            </p>
                            
                            <div class="flex space-x-2">
                                <a href="product.php?id=<?= $product['produit_id'] ?>" 
                                   class="flex-1 text-center px-4 py-2 bg-[#b06393] text-white rounded hover:bg-[#d87eb6] transition">
                                    Voir
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

