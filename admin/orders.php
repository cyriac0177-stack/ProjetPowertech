<?php
session_start();
include '../config/db.php';

// Vérifier que l'utilisateur est connecté et est un admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Récupérer toutes les commandes avec les informations des clients
$orders = [];
try {
    $stmt = $bdd->query("
        SELECT o.*, u.name as customer_name, u.email 
        FROM orders o 
        LEFT JOIN users u ON o.customer_id = u.id 
        ORDER BY o.created_at DESC
    ");
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log('Erreur récupération commandes: ' . $e->getMessage());
    $orders = [];
}

// Action pour changer le statut de la commande
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $order_id = intval($_POST['order_id'] ?? 0);
    $new_status = $_POST['status'] ?? '';
    
    if ($order_id > 0 && in_array($new_status, ['pending', 'confirmed', 'shipped', 'delivered', 'cancelled'])) {
        try {
            $stmt = $bdd->prepare("UPDATE orders SET status = ? WHERE id = ?");
            $stmt->execute([$new_status, $order_id]);
            header('Location: orders.php');
            exit;
        } catch (Exception $e) {
            error_log('Erreur modification commande: ' . $e->getMessage());
            $error = "Erreur lors de la modification de la commande.";
        }
    }
}

// Récupérer les détails d'une commande
function getOrderDetails($bdd, $order_id) {
    try {
        $stmt = $bdd->prepare("
            SELECT oi.*, p.name as product_name, p.image
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            WHERE oi.order_id = ?
        ");
        $stmt->execute([$order_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Commandes - Admin</title>
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
            </div>
        </div>
    </header>

    <div class="container mx-auto px-4 py-8">
        <div class="max-w-7xl mx-auto">
            <h1 class="text-4xl font-serif text-[#b06393] mb-8">Gestion des Commandes</h1>

            <?php if (isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if (empty($orders)): ?>
                <div class="bg-white rounded-lg shadow-md p-12 text-center">
                    <i class="fas fa-shopping-bag text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-2xl font-serif text-gray-700 mb-2">Aucune commande</h3>
                    <p class="text-gray-600">Il n'y a pas encore de commandes sur la plateforme.</p>
                </div>
            <?php else: ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($orders as $order): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">#<?= $order['id'] ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($order['customer_name'] ?? 'N/A') ?></div>
                                            <div class="text-xs text-gray-500"><?= htmlspecialchars($order['email'] ?? 'N/A') ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-bold text-[#b06393]">
                                                <?= number_format($order['total_amount'], 0, ',', ' ') ?> FCFA
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php
                                            $status_labels = [
                                                'pending' => ['label' => 'En attente', 'color' => 'bg-yellow-100 text-yellow-700'],
                                                'confirmed' => ['label' => 'Confirmée', 'color' => 'bg-green-100 text-green-700'],
                                                'shipped' => ['label' => 'Expédiée', 'color' => 'bg-blue-100 text-blue-700'],
                                                'delivered' => ['label' => 'Livrée', 'color' => 'bg-green-100 text-green-700'],
                                                'cancelled' => ['label' => 'Annulée', 'color' => 'bg-red-100 text-red-700']
                                            ];
                                            $status_info = $status_labels[$order['status']] ?? ['label' => ucfirst($order['status']), 'color' => 'bg-gray-100 text-gray-700'];
                                            ?>
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?= $status_info['color'] ?>">
                                                <?= $status_info['label'] ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="space-y-2">
                                                <button onclick="showOrderDetails(<?= $order['id'] ?>)" 
                                                        class="text-[#b06393] hover:text-[#d87eb6] transition text-xs">
                                                    <i class="fas fa-eye mr-1"></i>Voir détails
                                                </button>
                                                <form method="POST" class="inline">
                                                    <input type="hidden" name="action" value="update">
                                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                                    <select name="status" onchange="this.form.submit()" class="text-xs border border-gray-300 rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-[#b06393]">
                                                        <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>En attente</option>
                                                        <option value="confirmed" <?= $order['status'] === 'confirmed' ? 'selected' : '' ?>>Confirmée</option>
                                                        <option value="shipped" <?= $order['status'] === 'shipped' ? 'selected' : '' ?>>Expédiée</option>
                                                        <option value="delivered" <?= $order['status'] === 'delivered' ? 'selected' : '' ?>>Livrée</option>
                                                        <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>Annulée</option>
                                                    </select>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script>
    // Fonction pour afficher les détails de la commande
    function showOrderDetails(orderId) {
        // Utiliser fetch pour récupérer les détails via AJAX
        fetch(`order_details.php?id=${orderId}`)
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    alert('Erreur lors de la récupération des détails');
                    return;
                }
                
                const order = data.order;
                const items = data.items;
                
                // Créer le contenu du modal
                let itemsList = '';
                items.forEach(item => {
                    itemsList += `
                        <tr class="border-b">
                            <td class="py-2">${item.product_name}</td>
                            <td class="text-center py-2">${item.quantity}</td>
                            <td class="text-center py-2">${new Intl.NumberFormat('fr-FR').format(item.price)} FCFA</td>
                            <td class="text-right font-bold py-2">${new Intl.NumberFormat('fr-FR').format(item.price * item.quantity)} FCFA</td>
                        </tr>
                    `;
                });
                
                const statusLabels = {
                    'pending': 'En attente',
                    'confirmed': 'Confirmée',
                    'shipped': 'Expédiée',
                    'delivered': 'Livrée',
                    'cancelled': 'Annulée'
                };
                
                const modalContent = `
                    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" id="orderModal" onclick="if(event.target.id === 'orderModal') closeOrderModal();">
                        <div class="bg-white rounded-lg shadow-xl max-w-3xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                            <div class="sticky top-0 bg-white border-b px-6 py-4 flex justify-between items-center">
                                <h2 class="text-2xl font-bold text-[#b06393]">Commande #${order.id}</h2>
                                <button onclick="closeOrderModal()" class="text-gray-500 hover:text-gray-700">
                                    <i class="fas fa-times text-2xl"></i>
                                </button>
                            </div>
                            <div class="p-6 space-y-4">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-sm text-gray-600">Client</p>
                                        <p class="font-semibold">${order.customer_name}</p>
                                        <p class="text-sm text-gray-500">${order.email}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Statut</p>
                                        <p class="font-semibold">${statusLabels[order.status] || order.status}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Date de commande</p>
                                        <p class="font-semibold">${new Date(order.created_at).toLocaleDateString('fr-FR')}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Total</p>
                                        <p class="font-bold text-xl text-[#b06393]">${new Intl.NumberFormat('fr-FR').format(order.total_amount)} FCFA</p>
                                    </div>
                                    ${order.shipping_address ? `
                                    <div class="col-span-2">
                                        <p class="text-sm text-gray-600">Adresse de livraison</p>
                                        <p class="font-semibold">${order.shipping_address}</p>
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
                                                <td class="text-right font-bold text-xl text-[#b06393] py-2 px-4">${new Intl.NumberFormat('fr-FR').format(order.total_amount)} FCFA</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                document.body.insertAdjacentHTML('beforeend', modalContent);
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Erreur lors de la récupération des détails');
            });
    }
    
    function closeOrderModal() {
        const modal = document.getElementById('orderModal');
        if (modal) {
            modal.remove();
        }
    }
    </script>
</body>
</html>
