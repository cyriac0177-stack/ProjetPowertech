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

$produit_id = intval($_GET['id'] ?? 0);
$stmt = $bdd->prepare("SELECT p.*, c.name as categorie_nom FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
$stmt->execute([$produit_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header('HTTP/1.0 404 Not Found');
    echo "Produit non trouvé";
    exit;
}

// Gestion de l'ajout au panier
$message = null;
$message_type = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $quantite = intval($_POST['quantity'] ?? 1);
    
    if ($quantite > 0 && $quantite <= $product['stock_quantity']) {
        // Initialiser le panier si nécessaire
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        
        // Vérifier si le produit est déjà dans le panier
        $found = false;
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['produit_id'] == $produit_id) {
                $item['quantite'] += $quantite;
                $found = true;
                break;
            }
        }
        
        // Si le produit n'est pas dans le panier, l'ajouter
        if (!$found) {
            $_SESSION['cart'][] = [
                'produit_id' => $produit_id,
                'nom' => $product['name'],
                'prix' => $product['price'],
                'quantite' => $quantite,
                'image' => $product['image'] ?? ''
            ];
        }
        
        $message = "Produit ajouté au panier avec succès !";
        $message_type = "success";
        
        // Redirection pour actualiser le compteur
        header("Location: product.php?id=" . $produit_id . "&added=1");
        exit;
    } else {
        $message = "Quantité invalide ou stock insuffisant.";
        $message_type = "error";
    }
}

// Calculer le nombre d'articles dans le panier après traitement
$cartCount = getCartItemCount();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['nom']) ?> - Quick Quick Shopping</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap" rel="stylesheet">
</head>
<body class="bg-[#fdf1f7] min-h-screen">
    <!-- Header -->
    <header class="bg-white shadow-md sticky top-0 z-50">
        <div class="container mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <a href="index.php" class="flex items-center">
                    <img src="images/logo.png" alt="Logo" class="h-12">
                </a>
                
                <nav class="hidden md:flex space-x-6">
                    <a href="index.php" class="text-gray-700 hover:text-[#b06393] transition">Accueil</a>
                    <a href="products_simple.php" class="text-gray-700 hover:text-[#b06393] transition">Boutique</a>
                </nav>
                
                <div class="flex items-center space-x-4">
                    <a href="cart.php" class="relative inline-flex items-center bg-orrose text-blanc px-4 py-2 rounded-full text-sm font-medium shadow hover:bg-nude hover:text-noir transition">
                        <i class="fas fa-shopping-cart mr-2 text-lg"></i>Panier
                        <?php if ($cartCount > 0): ?>
                            <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
                                <?= $cartCount ?>
                            </span>
                        <?php endif; ?>
                    </a>
                    
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="logout.php" class="px-4 py-2 text-gray-700 hover:text-[#b06393]">Déconnexion</a>
                    <?php else: ?>
                        <a href="login.php" class="px-4 py-2 bg-[#b06393] text-white rounded-full hover:bg-[#d87eb6] transition">Connexion</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-8">
        <?php if (isset($_GET['added']) && $_GET['added'] == 1): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                Produit ajouté au panier avec succès !
            </div>
        <?php endif; ?>
        
        <?php if ($message): ?>
            <div class="bg-<?= $message_type === 'success' ? 'green' : 'red' ?>-100 border border-<?= $message_type === 'success' ? 'green' : 'red' ?>-400 text-<?= $message_type === 'success' ? 'green' : 'red' ?>-700 px-4 py-3 rounded mb-4">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 bg-white rounded-lg shadow-lg p-8">
            <!-- Image -->
            <div>
                <img src="<?= htmlspecialchars($product['image'] ?? 'images/no-image.jpg') ?>" 
                     alt="<?= htmlspecialchars($product['name']) ?>" 
                     class="w-full rounded-lg">
                    </div>

            <!-- Détails -->
            <div class="space-y-6">
                <div>
                    <h1 class="text-3xl font-serif text-gray-800 mb-4"><?= htmlspecialchars($product['name']) ?></h1>
                    <p class="text-4xl font-bold text-[#b06393] mb-4">
                        <?= number_format($product['price'], 0, ',', ' ') ?> FCFA
                    </p>
                </div>

                <div>
                    <h3 class="text-xl font-semibold mb-2">Description</h3>
                    <p class="text-gray-600"><?= nl2br(htmlspecialchars($product['description'] ?? 'Aucune description disponible')) ?></p>
                        </div>
                        
                <div>
                    <h3 class="text-xl font-semibold mb-2">Stock</h3>
                    <?php if ($product['stock_quantity'] > 0): ?>
                        <p class="text-green-600">
                                    <i class="fas fa-check-circle"></i>
                            Disponible (<?= $product['stock_quantity'] ?> en stock)
                        </p>
                            <?php else: ?>
                        <p class="text-red-600">
                                    <i class="fas fa-times-circle"></i>
                                    Rupture de stock
                        </p>
                    <?php endif; ?>
                </div>

                <?php if ($product['stock_quantity'] > 0): ?>
                    <form method="POST" class="space-y-4">
                        <div>
                            <label for="quantity" class="block text-sm font-medium text-gray-700 mb-2">Quantité :</label>
                            <div class="flex items-center space-x-3">
                                <button type="button" onclick="decreaseQty()" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300 transition">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <input type="number" 
                                       id="quantity" 
                                       name="quantity" 
                                       value="1" 
                                       min="1" 
                                       max="<?= $product['stock_quantity'] ?>"
                                       class="w-20 text-center border border-gray-300 rounded focus:outline-none focus:border-[#b06393]">
                                <button type="button" onclick="increaseQty()" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300 transition">
                                    <i class="fas fa-plus"></i>
                                </button>
                                </div>
                            </div>
                            
                        <button type="submit" name="add_to_cart" class="w-full bg-[#b06393] text-white py-4 rounded-full hover:bg-[#d87eb6] transition font-semibold text-lg">
                            <i class="fas fa-shopping-cart mr-2"></i>
                                Ajouter au panier
                            </button>
                        </form>
                    <?php else: ?>
                    <button disabled class="w-full bg-gray-400 text-white py-4 rounded-full cursor-not-allowed font-semibold text-lg">
                        <i class="fas fa-times mr-2"></i>
                                Rupture de stock
                            </button>
                    <?php endif; ?>
                </div>
            </div>

        <!-- Retour aux produits -->
        <div class="text-center mt-8">
            <a href="products.php" class="inline-block px-6 py-3 text-[#b06393] border-2 border-[#b06393] rounded-full hover:bg-[#b06393] hover:text-white transition">
                <i class="fas fa-arrow-left mr-2"></i>
                Retour aux produits
            </a>
                        </div>
                    </div>

    <!-- Section Produits Similaires -->
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-7xl mx-auto">
            <h2 class="text-3xl font-serif text-[#b06393] mb-8">
                <i class="fas fa-th-large mr-2"></i>Produits similaires
            </h2>
            
            <?php
            // Récupérer des produits similaires (même catégorie)
            $similar_products = [];
            try {
                $stmt = $bdd->prepare("
                    SELECT p.*, c.name as categorie_nom 
                    FROM products p 
                    LEFT JOIN categories c ON p.category_id = c.id 
                    WHERE p.status = 'active' 
                    AND p.category_id = ? 
                    AND p.id != ?
                    ORDER BY p.id DESC 
                    LIMIT 4
                ");
                $stmt->execute([$product['category_id'], $produit_id]);
                $similar_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                // Pas de produits similaires
            }
            ?>
            
            <?php if (!empty($similar_products)): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <?php foreach ($similar_products as $similar): ?>
                        <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition">
                            <!-- Image -->
                            <div class="h-48 bg-gray-200 flex items-center justify-center">
                                <?php if (!empty($similar['image'])): ?>
                                    <img src="<?= htmlspecialchars($similar['image']) ?>" 
                                         alt="<?= htmlspecialchars($similar['name']) ?>" 
                                         class="w-full h-full object-cover">
                                <?php else: ?>
                                    <i class="fas fa-image text-5xl text-gray-300"></i>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Contenu -->
                            <div class="p-4">
                                <h3 class="font-semibold text-gray-800 mb-2"><?= htmlspecialchars($similar['name']) ?></h3>
                                <p class="text-xl font-bold text-[#b06393] mb-2">
                                    <?= number_format($similar['price'], 0, ',', ' ') ?> FCFA
                                </p>
                                <div class="flex justify-between items-center">
                                    <?php if ($similar['stock_quantity'] > 0): ?>
                                        <span class="text-xs text-green-600">En stock</span>
                                    <?php else: ?>
                                        <span class="text-xs text-red-600">Épuisé</span>
                                    <?php endif; ?>
                                    <a href="product.php?id=<?= $similar['id'] ?>" class="text-[#b06393] hover:text-[#d87eb6] text-sm">
                                        Voir <i class="fas fa-arrow-right"></i>
                                    </a>
                        </div>
                    </div>
                        </div>
                    <?php endforeach; ?>
                    </div>
            <?php else: ?>
                <div class="bg-white rounded-lg shadow-md p-8 text-center text-gray-500">
                    Aucun produit similaire pour le moment
                </div>
            <?php endif; ?>
                </div>
            </div>

    <!-- Section Avis -->
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <h2 class="text-3xl font-serif text-[#b06393] mb-6">
                <i class="fas fa-star mr-2"></i>Avis clients
            </h2>
            
            <!-- Formulaire d'avis -->
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="bg-white rounded-lg shadow-md p-6 mb-8" id="reviewForm">
                    <h3 class="text-xl font-semibold mb-4">Donnez votre avis</h3>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Note</label>
                        <div class="flex space-x-2" id="ratingStars">
                            <?php for($i = 1; $i <= 5; $i++): ?>
                                <button type="button" class="text-3xl text-gray-300 hover:text-yellow-400" onclick="setRating(<?= $i ?>)">
                                    <i class="fas fa-star" data-star="<?= $i ?>"></i>
                                </button>
                            <?php endfor; ?>
                                        </div>
                        <input type="hidden" id="selectedRating" value="0">
                                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Commentaire</label>
                        <textarea id="reviewComment" rows="4" class="w-full px-4 py-2 border border-gray-300 rounded"></textarea>
                                    </div>
                    <button onclick="submitReview()" class="bg-[#b06393] text-white px-6 py-2 rounded hover:bg-[#d87eb6] transition">
                        Envoyer mon avis
                    </button>
                                </div>
            <?php else: ?>
                <div class="bg-white rounded-lg shadow-md p-6 mb-8 text-center">
                    <p class="text-gray-600 mb-4">Connectez-vous pour laisser un avis</p>
                    <a href="login.php" class="text-[#b06393] hover:underline">Se connecter</a>
                    </div>
            <?php endif; ?>
            
            <!-- Liste des avis -->
            <div id="reviewsList" class="space-y-4">
                <!-- Les avis seront chargés ici -->
            </div>
        </div>
    </div>

    <script>
        // Charger les avis au chargement de la page
        loadReviews();
        
        function loadReviews() {
            fetch(`api/reviews.php?action=get&produit_id=<?= $produit_id ?>`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayReviews(data.reviews, data.average, data.count);
                    }
                });
        }
        
        function displayReviews(reviews, average, count) {
            const container = document.getElementById('reviewsList');
            
            if (reviews.length === 0) {
                container.innerHTML = '<div class="bg-white rounded-lg shadow-md p-8 text-center text-gray-500">Aucun avis pour l\'instant</div>';
                return;
            }
            
            let html = `
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <div class="flex items-center space-x-4">
                        <div class="text-6xl font-bold text-[#b06393]">${average.toFixed(1)}</div>
                        <div>
                            ${generateStarRating(average)}
                            <p class="text-gray-600 mt-2">${count} avis</p>
                </div>
                    </div>
                </div>
            `;
            
            reviews.forEach(review => {
                html += `
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center space-x-3">
                                <div class="w-12 h-12 rounded-full bg-[#b06393] text-white flex items-center justify-center font-semibold">
                                    ${review.user_name ? review.user_name.charAt(0).toUpperCase() : 'A'}
            </div>
                                <div>
                                    <p class="font-semibold">${review.user_name || 'Anonyme'}</p>
                                    <p class="text-sm text-gray-500">${new Date(review.created_at).toLocaleDateString('fr-FR')}</p>
            </div>
        </div>
                            ${generateStarRating(review.note)}
                        </div>
                        ${review.commentaire ? '<p class="text-gray-700">' + review.commentaire + '</p>' : ''}
                    </div>
                `;
            });
            
            container.innerHTML = html;
        }
        
        function generateStarRating(rating) {
            let html = '<div class="flex">';
            for (let i = 1; i <= 5; i++) {
                if (i <= rating) {
                    html += '<i class="fas fa-star text-yellow-400"></i>';
                } else {
                    html += '<i class="far fa-star text-gray-300"></i>';
                }
            }
            html += '</div>';
            return html;
        }
        
        function setRating(rating) {
            document.getElementById('selectedRating').value = rating;
            const stars = document.querySelectorAll('#ratingStars i');
            stars.forEach((star, index) => {
                if (index < rating) {
                    star.classList.add('text-yellow-400');
                    star.classList.remove('text-gray-300');
                } else {
                    star.classList.add('text-gray-300');
                    star.classList.remove('text-yellow-400');
                }
            });
        }
        
        function submitReview() {
            const rating = document.getElementById('selectedRating').value;
            const comment = document.getElementById('reviewComment').value;
            
            if (rating == 0) {
                alert('Veuillez donner une note');
                return;
            }
            
            fetch('api/reviews.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=add&produit_id=<?= $produit_id ?>&note=${rating}&commentaire=${encodeURIComponent(comment)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Votre avis a été envoyé !');
                    loadReviews();
                    // Réinitialiser le formulaire
                    document.getElementById('reviewForm').style.display = 'none';
                } else {
                    alert(data.message);
                }
            });
        }
        
        function increaseQty() {
            const input = document.getElementById('quantity');
            const max = parseInt(input.getAttribute('max'));
            const current = parseInt(input.value);
            if (current < max) {
                input.value = current + 1;
            }
        }
        
        function decreaseQty() {
            const input = document.getElementById('quantity');
            const current = parseInt(input.value);
            if (current > 1) {
                input.value = current - 1;
            }
        }
    </script>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
