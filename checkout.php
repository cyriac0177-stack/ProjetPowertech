<?php
session_start();
include 'config/db.php';

// Rediriger si non connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Fonctions du panier
function getCartItems($bdd) {
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        return [];
    }
    
    $items = [];
    foreach ($_SESSION['cart'] as $item) {
        $stmt = $bdd->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$item['produit_id']]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($product) {
            $items[] = [
                'product' => $product,
                'quantity' => $item['quantite'] ?? 1
            ];
        }
    }
    
    return $items;
}

function getCartTotal($bdd) {
    $items = getCartItems($bdd);
    $total = 0;
    foreach ($items as $item) {
        $total += $item['product']['price'] * $item['quantity'];
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

$cartItems = getCartItems($bdd);
$cartTotal = getCartTotal($bdd);

if (empty($cartItems)) {
    header('Location: cart.php');
    exit;
}

$message = null;
$message_type = null;

// Traitement de la commande
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    try {
        $bdd->beginTransaction();
        
        // Récupérer l'adresse de livraison
        $shipping_address = $_POST['shipping_address'] ?? '';
        $payment_method = $_POST['payment_method'] ?? 'mobile_money';
        
        // Créer la commande dans la table orders
        $stmt = $bdd->prepare("INSERT INTO orders (customer_id, total_amount, status, shipping_address, payment_method, payment_status) VALUES (?, ?, 'pending', ?, ?, 'pending')");
        $stmt->execute([$_SESSION['user_id'], $cartTotal, $shipping_address, $payment_method]);
        $commande_id = $bdd->lastInsertId();
        
        // Ajouter les détails de commande dans order_items
        $stmt = $bdd->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        
        foreach ($cartItems as $item) {
            $product = $item['product'];
            $product_id = $product['id'];
            $quantite = $item['quantity'];
            $prix = $product['price'];
            
            $stmt->execute([
                $commande_id,
                $product_id,
                $quantite,
                $prix
            ]);
            
            // Mettre à jour le stock
            $updateStmt = $bdd->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?");
            $updateStmt->execute([$quantite, $product_id]);
        }
        
        $bdd->commit();
        
        // Vider le panier
        $_SESSION['cart'] = [];
        
        $message = "Commande passée avec succès ! Numéro de commande : #{$commande_id}";
        $message_type = "success";
        
        // Rediriger après 2 secondes
        header("Refresh: 2; url=orders.php");
        
    } catch (Exception $e) {
        $bdd->rollBack();
        $message = "Erreur lors de la commande : " . $e->getMessage();
        $message_type = "error";
    }
}

$cartCount = getCartItemCount();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commande - Quick Quick Shopping</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-[#fdf1f7] min-h-screen">
    <!-- Header -->
    <header class="bg-white shadow-md sticky top-0 z-50">
        <div class="container mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <a href="index.php" class="flex items-center space-x-2">
                    <img src="images/logo.png" alt="Logo" class="h-12">
                </a>
                
                <div class="flex items-center space-x-4">
                    <a href="cart.php" class="relative">
                        <i class="fas fa-shopping-cart text-2xl text-[#b06393]"></i>
                        <?php if ($cartCount > 0): ?>
                            <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
                                <?= $cartCount ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <?php if ($message): ?>
                <div class="bg-<?= $message_type === 'success' ? 'green' : 'red' ?>-100 border border-<?= $message_type === 'success' ? 'green' : 'red' ?>-400 text-<?= $message_type === 'success' ? 'green' : 'red' ?>-700 px-4 py-3 rounded mb-6">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <?php if ($message_type === 'success'): ?>
                <div class="text-center mb-8">
                    <a href="index.php" class="inline-block px-6 py-3 bg-[#b06393] text-white rounded-full hover:bg-[#d87eb6] transition">
                        Retour à l'accueil
                    </a>
                </div>
            <?php else: ?>
                <h1 class="text-4xl font-serif text-[#b06393] mb-8 text-center">Valider ma commande</h1>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Formulaire -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-2xl font-semibold mb-4">Informations de livraison</h2>
                        
                        <form method="POST" class="space-y-4">
                            <div>
                                <label for="adresse" class="block text-sm font-medium text-gray-700 mb-1">Adresse</label>
                                <input type="text" 
                                       id="adresse" 
                                       name="adresse" 
                                       required
                                       class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-[#b06393]">
                            </div>
                            
                            <div>
                                <label for="ville" class="block text-sm font-medium text-gray-700 mb-1">Ville</label>
                                <input type="text" 
                                       id="ville" 
                                       name="ville" 
                                       required
                                       class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-[#b06393]">
                            </div>
                            
                            <div>
                                <label for="telephone" class="block text-sm font-medium text-gray-700 mb-1">Téléphone</label>
                                <input type="tel" 
                                       id="telephone" 
                                       name="telephone" 
                                       required
                                       class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-[#b06393]">
                            </div>
                            
                            <div>
                                <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes (optionnel)</label>
                                <textarea id="notes" 
                                          name="notes" 
                                          rows="3"
                                          class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-[#b06393]"></textarea>
                            </div>
                            
                            <button type="submit" 
                                    name="place_order" 
                                    class="w-full bg-[#b06393] text-white py-4 rounded-full hover:bg-[#d87eb6] transition font-semibold text-lg">
                                <i class="fas fa-check-circle mr-2"></i>
                                Confirmer ma commande
                            </button>
                        </form>
                    </div>

                    <!-- Résumé -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-2xl font-semibold mb-4">Résumé de commande</h2>
                        
                        <div class="space-y-3 mb-6">
                            <?php foreach ($cartItems as $item): ?>
                                <div class="flex justify-between border-b pb-2">
                                    <div>
                                        <p class="font-semibold"><?= htmlspecialchars($item['nom']) ?></p>
                                        <p class="text-sm text-gray-600"><?= $item['quantite'] ?> x <?= number_format($item['prix'], 0, ',', ' ') ?> FCFA</p>
                                    </div>
                                    <p class="font-semibold text-[#b06393]">
                                        <?= number_format($item['sous_total'], 0, ',', ' ') ?> FCFA
                                    </p>
                                </div>
                            <?php endforeach; ?>
                            
                            <hr class="my-4">
                            
                            <div class="flex justify-between text-xl font-bold text-[#b06393]">
                                <span>Total</span>
                                <span><?= number_format($cartTotal, 0, ',', ' ') ?> FCFA</span>
                            </div>
                        </div>
                        
                        <a href="cart.php" class="block text-center text-[#b06393] hover:underline">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Modifier mon panier
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>

