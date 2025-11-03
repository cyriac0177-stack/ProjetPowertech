<?php
session_start();
include '../config/db.php';

// Vérifier que l'utilisateur est connecté et est un admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Récupérer tous les produits avec leurs catégories
$products = [];
try {
    $stmt = $bdd->query("
        SELECT p.*, c.name as categorie_nom 
        FROM produits p 
        LEFT JOIN categories c ON p.categorie_id = c.id 
        ORDER BY p.created_at DESC
    ");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $products = [];
}

// Action pour activer/désactiver un produit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $product_id = intval($_POST['product_id'] ?? 0);
    $action = $_POST['action'];
    
    if ($product_id > 0 && in_array($action, ['activate', 'deactivate'])) {
        try {
            $status = ($action === 'activate') ? 1 : 0;
            $stmt = $bdd->prepare("UPDATE produits SET disponible = ? WHERE produit_id = ?");
            $stmt->execute([$status, $product_id]);
            header('Location: products.php');
            exit;
        } catch (Exception $e) {
            $error = "Erreur lors de la modification du produit.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Produits - Admin</title>
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
            <h1 class="text-4xl font-serif text-[#b06393] mb-8">Gestion des Produits</h1>

            <?php if (isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Image</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Catégorie</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prix</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($products as $product): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="h-12 w-12 bg-gray-200 rounded overflow-hidden">
                                            <?php if (!empty($product['image_url'])): ?>
                                                <img src="../<?= htmlspecialchars($product['image_url']) ?>" 
                                                     alt="<?= htmlspecialchars($product['nom']) ?>" 
                                                     class="h-full w-full object-cover">
                                            <?php else: ?>
                                                <i class="fas fa-image text-gray-400 text-2xl flex items-center justify-center h-full"></i>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($product['nom']) ?></div>
                                        <?php if (!empty($product['reference'])): ?>
                                            <div class="text-xs text-gray-500">Ref: <?= htmlspecialchars($product['reference']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if (!empty($product['categorie_nom'])): ?>
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-700">
                                                <?= htmlspecialchars($product['categorie_nom']) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-gray-400">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-bold text-[#b06393]">
                                            <?= number_format($product['prix'], 0, ',', ' ') ?> FCFA
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900"><?= $product['stock'] ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?= ($product['disponible'] ?? 1) ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                                            <?= ($product['disponible'] ?? 1) ? 'Disponible' : 'Indisponible' ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="product_id" value="<?= $product['produit_id'] ?>">
                                            <?php if ($product['disponible'] ?? 1): ?>
                                                <button type="submit" name="action" value="deactivate" class="text-red-600 hover:text-red-900">
                                                    <i class="fas fa-ban"></i> Désactiver
                                                </button>
                                            <?php else: ?>
                                                <button type="submit" name="action" value="activate" class="text-green-600 hover:text-green-900">
                                                    <i class="fas fa-check"></i> Activer
                                                </button>
                                            <?php endif; ?>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

