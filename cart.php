<?php
session_start();
include 'config/db.php';

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Fonctions du panier en session
function getCartItems($bdd = null) {
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        return [];
    }
    
    $items = [];
    
    // Si on a une connexion DB, récupérer les infos à jour depuis la base
    foreach ($_SESSION['cart'] as $item) {
        $produit_id = $item['produit_id'] ?? $item['id'] ?? 0;
        
        $nom = $item['nom'] ?? $item['name'] ?? '';
        $prix = $item['prix'] ?? $item['price'] ?? 0;
        $quantite = $item['quantite'] ?? 1;
        $image_url = $item['image_url'] ?? $item['image'] ?? '';
        $stock = $item['stock'] ?? $item['stock_quantity'] ?? 999; // Par défaut stock illimité
        
        // Essayer de récupérer le stock depuis la base de données si possible
        if ($bdd && $produit_id > 0) {
            try {
                $stmt = $bdd->prepare("SELECT stock_quantity FROM products WHERE id = ?");
                $stmt->execute([$produit_id]);
                $product_data = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($product_data) {
                    $stock = $product_data['stock_quantity'] ?? 999;
                }
            } catch (Exception $e) {
                // Erreur, garder la valeur par défaut
            }
        }
        
        // Calculer le sous-total pour chaque article
        $sous_total = $prix * $quantite;
        
        // Formater les données pour le template
        $items[] = [
            'produit_id' => $produit_id,
            'nom' => $nom,
            'prix' => $prix,
            'quantite' => $quantite,
            'image_url' => $image_url,
            'stock' => $stock,
            'sous_total' => $sous_total
        ];
    }
    
    return $items;
}

function getCartTotal() {
    $cart = $_SESSION['cart'] ?? [];
    $total = 0;
    foreach ($cart as $item) {
        $prix = $item['prix'] ?? $item['price'] ?? 0;
        $quantite = $item['quantite'] ?? 1;
        $total += $prix * $quantite;
    }
    return $total;
}

function getCartItemCount() {
    $count = 0;
    if (isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            $count += $item['quantite'] ?? 1;
        }
    }
    return $count;
}

function updateCart($produit_id, $quantite) {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    if ($quantite > 0) {
        // Mise à jour de la quantité
        foreach ($_SESSION['cart'] as &$item) {
            $item_id = $item['produit_id'] ?? $item['id'] ?? null;
            if ($item_id == $produit_id) {
                $item['quantite'] = $quantite;
                return;
            }
        }
    } else {
        // Supprimer le produit
        removeFromCart($produit_id);
    }
}

function removeFromCart($produit_id) {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    $_SESSION['cart'] = array_filter($_SESSION['cart'], function($item) use ($produit_id) {
        $item_id = $item['produit_id'] ?? $item['id'] ?? null;
        return $item_id != $produit_id;
    });
    
    // Réindexer le tableau
    $_SESSION['cart'] = array_values($_SESSION['cart']);
}

// Gérer les actions du panier
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update') {
        $produit_id = intval($_POST['produit_id'] ?? 0);
        $quantite = max(1, intval($_POST['quantite'] ?? 0));
        
        if ($produit_id > 0 && $quantite > 0) {
            updateCart($produit_id, $quantite);
        }
    } elseif ($action === 'remove') {
        $produit_id = intval($_POST['produit_id'] ?? 0);
        
        if ($produit_id > 0) {
            removeFromCart($produit_id);
        }
    }
    
    header('Location: cart.php');
    exit;
}

$cartItems = getCartItems($bdd);
$cartTotal = getCartTotal();
$cartCount = getCartItemCount();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panier - Quick Quick Shopping</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap" rel="stylesheet">
</head>
<body class="bg-[#fdf1f7] min-h-screen">
    <!-- Header -->
    <header class="bg-white shadow-md sticky top-0 z-50">
        <div class="container mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <a href="index.php" class="flex items-center space-x-2">
                    <img src="images/logo.png" alt="Logo" class="h-12">
                </a>
                
                <nav class="hidden md:flex space-x-6">
                    <a href="index.php" class="text-gray-700 hover:text-[#b06393] transition">Accueil</a>
                    <a href="products_simple.php" class="text-gray-700 hover:text-[#b06393] transition">Produits</a>
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
                        <!-- Menu profil avec avatar -->
                        <div class="relative group">
                            <button id="profileBtn" class="flex items-center space-x-2 px-4 py-2 text-gray-700 hover:text-[#b06393] transition">
                                <div class="w-10 h-10 rounded-full bg-[#b06393] text-white flex items-center justify-center font-semibold text-lg">
                                    <?= strtoupper(substr($_SESSION['nom'], 0, 1)) ?>
                                </div>
                                <span class="hidden md:block"><?= htmlspecialchars($_SESSION['nom']) ?></span>
                                <i class="fas fa-chevron-down text-gray-600"></i>
                            </button>
                            
                            <!-- Dropdown menu -->
                            <div id="profileMenu" class="absolute right-0 mt-3 w-56 bg-white rounded-lg shadow-xl z-50" style="display: none;">
                                <div class="p-4 border-b">
                                    <p class="font-semibold text-gray-800"><?= htmlspecialchars($_SESSION['nom']) ?></p>
                                    <p class="text-sm text-gray-600"><?= ucfirst($_SESSION['role']) ?></p>
                                </div>
                                <div class="py-2">
                                    <a href="profile.php" class="block px-4 py-2 hover:bg-gray-100 transition text-gray-700">
                                        <i class="fas fa-user mr-2"></i>Mon Profil
                                    </a>
                                    <a href="orders.php" class="block px-4 py-2 hover:bg-gray-100 transition text-gray-700">
                                        <i class="fas fa-shopping-bag mr-2"></i>Mes Commandes
                                    </a>
                                    <a href="logout.php" class="block px-4 py-2 hover:bg-gray-100 transition text-red-600">
                                        <i class="fas fa-sign-out-alt mr-2"></i>Déconnexion</a>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="px-4 py-2 bg-[#b06393] text-white rounded-full hover:bg-[#d87eb6] transition">Connexion</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-6xl mx-auto">
            <!-- Page Title -->
            <div class="mb-8">
                <h1 class="text-4xl font-serif text-[#b06393] mb-2">Mon Panier</h1>
                <p class="text-gray-600">Gérez vos articles sélectionnés</p>
            </div>

            <?php if (empty($cartItems)): ?>
                <!-- Panier vide -->
                <div class="bg-white rounded-lg shadow-lg p-12 text-center">
                    <i class="fas fa-shopping-cart text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-2xl font-serif text-gray-700 mb-2">Votre panier est vide</h3>
                    <p class="text-gray-600 mb-6">Découvrez nos produits et commencez vos achats !</p>
                    <a href="products.php" class="inline-block px-6 py-3 bg-[#b06393] text-white rounded-full hover:bg-[#d87eb6] transition">
                        Découvrir nos produits
                    </a>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Liste des articles -->
                    <div class="lg:col-span-2 space-y-4">
                        <?php foreach ($cartItems as $item): ?>
                            <div class="bg-white rounded-lg shadow-md p-6">
                                <div class="flex flex-col md:flex-row gap-4">
                                    <!-- Image -->
                                    <div class="w-full md:w-32 h-32 bg-gray-100 rounded-lg overflow-hidden">
                                        <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['nom']) ?>" class="w-full h-full object-cover">
                                    </div>
                                    
                                    <!-- Détails -->
                                    <div class="flex-1">
                                        <h3 class="text-xl font-semibold text-gray-800 mb-2"><?= htmlspecialchars($item['nom']) ?></h3>
                                        <p class="text-2xl font-bold text-[#b06393] mb-4">
                                            <?= number_format($item['prix'], 0, ',', ' ') ?> FCFA
                                        </p>
                                        
                                        <!-- Gestion quantité -->
                                        <form method="POST" class="inline-block" id="updateForm-<?= $item['produit_id'] ?>">
                                            <input type="hidden" name="action" value="update">
                                            <input type="hidden" name="produit_id" value="<?= $item['produit_id'] ?>">
                                            <div class="flex items-center space-x-3">
                                                <label class="text-sm text-gray-600">Quantité:</label>
                                                <input type="number" 
                                                       name="quantite" 
                                                       id="qty-<?= $item['produit_id'] ?>"
                                                       value="<?= $item['quantite'] ?>" 
                                                       min="1" 
                                                       <?php if ($item['stock'] > 0): ?>max="<?= $item['stock'] ?>"<?php endif; ?>
                                                       class="w-20 px-3 py-1 border border-gray-300 rounded focus:outline-none focus:border-[#b06393]"
                                                       onchange="document.getElementById('updateForm-<?= $item['produit_id'] ?>').submit();">
                                                <button type="submit" class="px-4 py-1 bg-[#b06393] text-white rounded hover:bg-[#d87eb6] transition">
                                                    <i class="fas fa-sync-alt"></i> Modifier
                                                </button>
                                            </div>
                                        </form>
                                        
                                        <form method="POST" class="inline-block ml-4">
                                            <input type="hidden" name="action" value="remove">
                                            <input type="hidden" name="produit_id" value="<?= $item['produit_id'] ?>">
                                            <button type="submit" class="text-red-500 hover:text-red-700 transition">
                                                <i class="fas fa-trash"></i> Supprimer
                                            </button>
                                        </form>
                                    </div>
                                    
                                    <!-- Sous-total -->
                                    <div class="text-right">
                                        <p class="text-gray-600 text-sm mb-1">Sous-total</p>
                                        <p class="text-2xl font-bold text-[#b06393]">
                                            <?= number_format($item['sous_total'], 0, ',', ' ') ?> FCFA
                                        </p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <!-- Continuer les achats -->
                        <div class="text-center">
                            <a href="products.php" class="inline-block px-6 py-3 text-[#b06393] border-2 border-[#b06393] rounded-full hover:bg-[#b06393] hover:text-white transition">
                                <i class="fas fa-arrow-left mr-2"></i>
                                Continuer mes achats
                            </a>
                        </div>
                    </div>
                    
                    <!-- Résumé de commande -->
                    <div class="lg:col-span-1">
                        <div class="bg-white rounded-lg shadow-md p-6 sticky top-24">
                            <h2 class="text-2xl font-serif text-[#b06393] mb-4">Résumé</h2>
                            
                            <div class="space-y-3 mb-6">
                                <div class="flex justify-between text-gray-600">
                                    <span>Articles (<?= $cartCount ?>)</span>
                                </div>
                                <div class="flex justify-between text-gray-600">
                                    <span>Livraison</span>
                                    <span>Gratuite</span>
                                </div>
                                <hr class="my-4">
                                <div class="flex justify-between text-xl font-bold text-[#b06393]">
                                    <span>Total</span>
                                    <span><?= number_format($cartTotal, 0, ',', ' ') ?> FCFA</span>
                                </div>
                            </div>
                            
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <a href="checkout.php" class="block w-full text-center px-6 py-4 bg-[#b06393] text-white rounded-full hover:bg-[#d87eb6] transition font-semibold">
                                    Valider ma commande
                                </a>
                            <?php else: ?>
                                <div class="text-center mb-4">
                                    <p class="text-sm text-gray-600 mb-3">Vous devez être connecté pour commander</p>
                                    <a href="login.php" class="block w-full px-6 py-4 bg-[#b06393] text-white rounded-full hover:bg-[#d87eb6] transition font-semibold">
                                        Se connecter
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

<script>
    // Menu déroulant avec TOGGLE au clic
    document.addEventListener('DOMContentLoaded', function() {
        const profileBtn = document.getElementById('profileBtn');
        const profileMenu = document.getElementById('profileMenu');
        
        if (profileBtn && profileMenu) {
            // Ouvrir/fermer au clic sur le bouton
            profileBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                if (profileMenu.style.display === 'none' || profileMenu.style.display === '') {
                    profileMenu.style.display = 'block';
                } else {
                    profileMenu.style.display = 'none';
                }
            });
            
            // Fermer si on clique ailleurs
            document.addEventListener('click', function(e) {
                if (!profileBtn.contains(e.target) && !profileMenu.contains(e.target)) {
                    profileMenu.style.display = 'none';
                }
            });
            
            // Fermer au clic sur un lien
            if (profileMenu.querySelectorAll('a')) {
                profileMenu.querySelectorAll('a').forEach(link => {
                    link.addEventListener('click', function() {
                        setTimeout(function() {
                            profileMenu.style.display = 'none';
                        }, 100);
                    });
                });
            }
        }
    });
</script>
</body>
</html>

