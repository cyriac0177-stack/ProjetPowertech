<?php
session_start();
include 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Récupérer les commandes avec les détails depuis la table orders
$stmt = $bdd->prepare("
    SELECT o.*, oi.*, p.name as produit_nom 
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    LEFT JOIN products p ON oi.product_id = p.id
    WHERE o.customer_id = ? 
    ORDER BY o.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$commandes_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Organiser par commande
$commandes = [];
foreach ($commandes_data as $row) {
    $id = $row['id'];
    if (!isset($commandes[$id])) {
        $commandes[$id] = [
            'order_id' => $row['id'],
            'total' => $row['total_amount'],
            'status' => $row['status'],
            'date_commande' => $row['created_at'],
            'payment_status' => $row['payment_status'],
            'shipping_address' => $row['shipping_address'],
            'items' => []
        ];
    }
    if (!empty($row['produit_nom'])) {
        $commandes[$id]['items'][] = [
            'nom' => $row['produit_nom'],
            'quantite' => $row['quantity'],
            'prix' => $row['price']
        ];
    }
}

$stmt = $bdd->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Commandes - Quick Quick Shopping</title>
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
                    <a href="index.php" class="px-4 py-2 text-gray-700 hover:text-[#b06393] transition">Accueil</a>
                    <a href="products_simple.php" class="px-4 py-2 text-gray-700 hover:text-[#b06393] transition">Produits</a>
                    <a href="cart.php" class="relative inline-flex items-center bg-orrose text-blanc px-4 py-2 rounded-full text-sm font-medium shadow hover:bg-nude hover:text-noir transition">
                        <i class="fas fa-shopping-cart mr-2 text-lg"></i>Panier
                        <?php
                        // Récupérer le nombre d'articles dans le panier
                        $cartCount = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
                        if ($cartCount > 0): ?>
                            <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
                                <?= $cartCount ?>
                            </span>
                        <?php endif; ?>
                    </a>
                    
                    <!-- Menu profil -->
                    <div class="relative group">
                        <button class="flex items-center space-x-2 px-4 py-2 text-gray-700 hover:text-[#b06393] transition">
                            <div class="w-10 h-10 rounded-full bg-[#b06393] text-white flex items-center justify-center font-semibold">
                                <?= strtoupper(substr($user['name'], 0, 1)) ?>
                            </div>
                            <span class="hidden md:block"><?= htmlspecialchars($user['name']) ?></span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        
                        <div class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg hidden group-hover:block z-50">
                            <a href="profile.php" class="block px-4 py-2 hover:bg-gray-100 transition">
                                <i class="fas fa-user mr-2"></i>Mon Profil
                            </a>
                            <a href="orders.php" class="block px-4 py-2 bg-gray-100 transition">
                                <i class="fas fa-shopping-bag mr-2"></i>Mes Commandes
                            </a>
                            <a href="logout.php" class="block px-4 py-2 hover:bg-gray-100 transition text-red-600">
                                <i class="fas fa-sign-out-alt mr-2"></i>Déconnexion
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="container mx-auto px-4 py-8">
        <div class="max-w-5xl mx-auto">
            <h1 class="text-4xl font-serif text-[#b06393] mb-8">Mes Commandes</h1>

            <?php if (empty($commandes)): ?>
                <div class="bg-white rounded-lg shadow-md p-12 text-center">
                    <i class="fas fa-shopping-bag text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-2xl font-serif text-gray-700 mb-2">Aucune commande</h3>
                    <p class="text-gray-600 mb-6">Commencez à faire vos achats !</p>
                    <a href="products_simple.php" class="inline-block px-6 py-3 bg-[#b06393] text-white rounded-full hover:bg-[#d87eb6] transition">
                        Découvrir nos produits
                    </a>
                </div>
            <?php else: ?>
                <div class="space-y-6">
                    <?php foreach ($commandes as $commande): ?>
                        <div class="bg-white rounded-lg shadow-md p-6 mb-4">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3 class="text-xl font-semibold text-gray-800">Commande #<?= $commande['order_id'] ?></h3>
                                    <p class="text-sm text-gray-600 mt-1">
                                        <i class="fas fa-calendar mr-1"></i>
                                        <?= date('d/m/Y à H:i', strtotime($commande['date_commande'])) ?>
                                    </p>
                                </div>
                                <div class="text-right">
                                    <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold 
                                        <?= $commande['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' ?>
                                        <?= $commande['status'] === 'confirmed' ? 'bg-green-100 text-green-800' : '' ?>
                                        <?= $commande['status'] === 'shipped' ? 'bg-blue-100 text-blue-800' : '' ?>
                                        <?= $commande['status'] === 'delivered' ? 'bg-green-100 text-green-800' : '' ?>
                                        <?= $commande['status'] === 'cancelled' ? 'bg-red-100 text-red-800' : '' ?>
                                    ">
                                        <?php
                                        $status_labels = [
                                            'pending' => 'En attente',
                                            'confirmed' => 'Confirmée',
                                            'shipped' => 'Expédiée',
                                            'delivered' => 'Livrée',
                                            'cancelled' => 'Annulée'
                                        ];
                                        echo $status_labels[$commande['status']] ?? ucfirst($commande['status']);
                                        ?>
                                    </span>
                                    <p class="text-2xl font-bold text-[#b06393] mt-2">
                                        <?= number_format($commande['total'], 0, ',', ' ') ?> FCFA
                                    </p>
                                </div>
                            </div>
                            
                            <!-- Adresse de livraison -->
                            <?php if (!empty($commande['shipping_address'])): ?>
                                <div class="bg-gray-50 rounded-lg p-3 mb-4">
                                    <p class="text-sm text-gray-600">
                                        <i class="fas fa-map-marker-alt mr-2"></i>
                                        <strong>Adresse :</strong> <?= htmlspecialchars($commande['shipping_address']) ?>
                                    </p>
                                </div>
                            <?php endif; ?>
                            
                            <div class="border-t pt-4">
                                <h4 class="font-semibold mb-3 flex items-center">
                                    <i class="fas fa-box mr-2 text-[#b06393]"></i>
                                    Articles (<?= count($commande['items']) ?>)
                                </h4>
                                <div class="space-y-3">
                                    <?php foreach ($commande['items'] as $item): ?>
                                        <div class="flex justify-between items-center bg-gray-50 rounded-lg p-3">
                                            <div class="flex-1">
                                                <p class="font-medium text-gray-800"><?= htmlspecialchars($item['nom']) ?></p>
                                                <p class="text-sm text-gray-600">
                                                    Quantité : <span class="font-semibold"><?= $item['quantite'] ?></span>
                                                    | Prix unitaire : <span class="font-semibold"><?= number_format($item['prix'], 0, ',', ' ') ?> FCFA</span>
                                                </p>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-lg font-bold text-[#b06393]">
                                                    <?= number_format($item['prix'] * $item['quantite'], 0, ',', ' ') ?> FCFA
                                                </p>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <!-- Button to view details -->
                            <div class="mt-4 pt-4 border-t">
                                <button onclick="showOrderDetails(<?= $commande['order_id'] ?>)" 
                                        class="w-full px-4 py-2 bg-[#b06393] text-white rounded-lg hover:bg-[#d87eb6] transition font-medium">
                                    <i class="fas fa-eye mr-2"></i>Voir les détails
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

<script>
    // Fonction pour afficher les détails de la commande
    function showOrderDetails(orderId) {
        // Trouver la commande correspondante
        const commande = <?= json_encode($commandes) ?>[orderId];
        
        if (!commande) {
            alert('Commande introuvable');
            return;
        }
        
        // Créer un modal avec les détails
        let itemsList = '';
        commande.items.forEach(item => {
            itemsList += `
                <tr class="border-b">
                    <td class="py-2">${item.nom}</td>
                    <td class="text-center py-2">${item.quantite}</td>
                    <td class="text-center py-2">${new Intl.NumberFormat('fr-FR').format(item.prix)} FCFA</td>
                    <td class="text-right font-bold py-2">${new Intl.NumberFormat('fr-FR').format(item.prix * item.quantite)} FCFA</td>
                </tr>
            `;
        });
        
        const modalContent = `
            <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" id="orderModal" onclick="event.stopPropagation(); if(event.target.id === 'orderModal') closeOrderModal();">
                <div class="bg-white rounded-lg shadow-xl max-w-3xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                    <div class="sticky top-0 bg-white border-b px-6 py-4 flex justify-between items-center">
                        <h2 class="text-2xl font-bold text-[#b06393]">Détails de la commande #${orderId}</h2>
                        <button onclick="closeOrderModal()" class="text-gray-500 hover:text-gray-700">
                            <i class="fas fa-times text-2xl"></i>
                        </button>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-600">Date de commande</p>
                                <p class="font-semibold">${new Date('<?= $commande['date_commande'] ?>').toLocaleDateString('fr-FR')}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Statut</p>
                                <p class="font-semibold">${commande.status}</p>
                            </div>
                            ${commande.shipping_address ? `
                            <div class="col-span-2">
                                <p class="text-sm text-gray-600">Adresse de livraison</p>
                                <p class="font-semibold">${commande.shipping_address}</p>
                            </div>
                            ` : ''}
                        </div>
                        <div class="border-t pt-4">
                            <h3 class="font-bold mb-3">Articles</h3>
                            <table class="w-full">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="text-left py-2 px-4">Produit</th>
                                        <th class="text-center py-2 px-4">Qté</th>
                                        <th class="text-center py-2 px-4">Prix</th>
                                        <th class="text-right py-2 px-4">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${itemsList}
                                </tbody>
                                <tfoot class="bg-gray-50">
                                    <tr>
                                        <td colspan="3" class="text-right font-bold py-2 px-4">Total :</td>
                                        <td class="text-right font-bold text-xl text-[#b06393] py-2 px-4">${new Intl.NumberFormat('fr-FR').format(commande.total)} FCFA</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', modalContent);
    }
    
    function closeOrderModal() {
        document.getElementById('orderModal').remove();
    }
    // Améliorer le comportement du menu déroulant
    document.addEventListener('DOMContentLoaded', function() {
        const profilButton = document.querySelector('.relative.group button');
        const dropdownMenu = document.querySelector('.relative.group .absolute');
        
        if (profilButton && dropdownMenu) {
            let timeout;
            
            profilButton.addEventListener('mouseenter', function() {
                clearTimeout(timeout);
                dropdownMenu.style.display = 'block';
                dropdownMenu.style.opacity = '1';
                dropdownMenu.style.visibility = 'visible';
            });
            
            profilButton.addEventListener('mouseleave', function() {
                timeout = setTimeout(function() {
                    dropdownMenu.style.opacity = '0';
                    setTimeout(function() {
                        dropdownMenu.style.display = 'none';
                    }, 300);
                }, 500);
            });
            
            dropdownMenu.addEventListener('mouseenter', function() {
                clearTimeout(timeout);
            });
            
            dropdownMenu.addEventListener('mouseleave', function() {
                timeout = setTimeout(function() {
                    dropdownMenu.style.opacity = '0';
                    setTimeout(function() {
                        dropdownMenu.style.display = 'none';
                    }, 300);
                }, 500);
            });
        }
    });
</script>
</body>
</html>

