<?php
session_start();
include '../config/db.php';

// Vérifier que l'utilisateur est connecté et est un admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Récupérer toutes les catégories
$categories = [];
try {
    $stmt = $bdd->query("SELECT * FROM categories ORDER BY name");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $categories = [];
}

// Action pour ajouter/modifier/supprimer une catégorie
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $icon = trim($_POST['icon'] ?? '');
    $status = $_POST['status'] ?? 'active';
    
    if ($name) {
        try {
            if (isset($_POST['add'])) {
                // Ajouter
                $stmt = $bdd->prepare("INSERT INTO categories (name, description, icon, status) VALUES (?, ?, ?, ?)");
                $stmt->execute([$name, $description, $icon, $status]);
                $success = "Catégorie ajoutée avec succès !";
            } elseif (isset($_POST['edit'])) {
                // Modifier
                $id = intval($_POST['id'] ?? 0);
                $stmt = $bdd->prepare("UPDATE categories SET name = ?, description = ?, icon = ?, status = ? WHERE id = ?");
                $stmt->execute([$name, $description, $icon, $status, $id]);
                $success = "Catégorie modifiée avec succès !";
            }
        } catch (Exception $e) {
            $error = "Erreur : " . $e->getMessage();
        }
    } else {
        $error = "Veuillez remplir le nom de la catégorie.";
    }
}

// Action pour supprimer
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    if ($id > 0) {
        try {
            $stmt = $bdd->prepare("DELETE FROM categories WHERE id = ?");
            $stmt->execute([$id]);
            header('Location: categories.php');
            exit;
        } catch (Exception $e) {
            $error = "Erreur lors de la suppression.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Catégories - Admin</title>
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
            <h1 class="text-4xl font-serif text-[#b06393] mb-8">Gestion des Catégories</h1>

            <?php if (isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if (isset($success)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <!-- Formulaire d'ajout -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                <h2 class="text-2xl font-semibold mb-4">Ajouter une catégorie</h2>
                <form method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nom *</label>
                        <input type="text" name="name" required class="w-full px-4 py-2 border border-gray-300 rounded">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <input type="text" name="description" class="w-full px-4 py-2 border border-gray-300 rounded">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Icône Font Awesome</label>
                        <input type="text" name="icon" placeholder="fas fa-tag" class="w-full px-4 py-2 border border-gray-300 rounded">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
                        <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="md:col-span-4">
                        <button type="submit" name="add" class="bg-[#b06393] text-white px-6 py-2 rounded hover:bg-[#d87eb6] transition">
                            <i class="fas fa-plus mr-2"></i>Ajouter
                        </button>
                    </div>
                </form>
            </div>

            <!-- Liste des catégories -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Icône</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($categories as $cat): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900"><?= $cat['id'] ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if (!empty($cat['icon'])): ?>
                                            <i class="<?= htmlspecialchars($cat['icon']) ?> text-2xl text-[#b06393]"></i>
                                        <?php else: ?>
                                            <i class="fas fa-tag text-2xl text-gray-400"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($cat['name']) ?></div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-500"><?= htmlspecialchars($cat['description'] ?? '-') ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?= $cat['status'] === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                                            <?= $cat['status'] === 'active' ? 'Active' : 'Inactive' ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="?delete=<?= $cat['id'] ?>" 
                                           onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette catégorie ?')"
                                           class="text-red-600 hover:text-red-900">
                                            <i class="fas fa-trash"></i> Supprimer
                                        </a>
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

