<?php
session_start();
include '../config/db.php';

// Vérifier que l'utilisateur est connecté et est un admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Récupérer tous les utilisateurs
$users = [];
try {
    $stmt = $bdd->query("SELECT * FROM users ORDER BY created_at DESC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $users = [];
}

// Action pour activer/désactiver un utilisateur
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $user_id = intval($_POST['user_id'] ?? 0);
    $action = $_POST['action'];
    
    if ($user_id > 0 && in_array($action, ['activate', 'deactivate'])) {
        try {
            $new_status = ($action === 'activate') ? 'active' : 'suspended';
            $stmt = $bdd->prepare("UPDATE users SET status = ? WHERE id = ?");
            $stmt->execute([$new_status, $user_id]);
            header('Location: users.php');
            exit;
        } catch (Exception $e) {
            $error = "Erreur lors de la modification de l'utilisateur.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Utilisateurs - Admin</title>
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
            <h1 class="text-4xl font-serif text-[#b06393] mb-8">Gestion des Utilisateurs</h1>

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
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Téléphone</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rôle</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date inscription</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($users as $user): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($user['name']) ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-500"><?= htmlspecialchars($user['email']) ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-500"><?= htmlspecialchars($user['phone'] ?? 'N/A') ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php
                                        $role_colors = [
                                            'admin' => 'bg-red-100 text-red-700',
                                            'seller' => 'bg-blue-100 text-blue-700',
                                            'customer' => 'bg-green-100 text-green-700'
                                        ];
                                        $color = $role_colors[$user['role']] ?? 'bg-gray-100 text-gray-700';
                                        ?>
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?= $color ?>">
                                            <?= ucfirst($user['role']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php
                                        $status_colors = [
                                            'active' => 'bg-green-100 text-green-700',
                                            'pending' => 'bg-yellow-100 text-yellow-700',
                                            'suspended' => 'bg-red-100 text-red-700',
                                            'rejected' => 'bg-red-100 text-red-700'
                                        ];
                                        $status_text = [
                                            'active' => 'Actif',
                                            'pending' => 'En attente',
                                            'suspended' => 'Suspendu',
                                            'rejected' => 'Rejeté'
                                        ];
                                        $color = $status_colors[$user['status']] ?? 'bg-gray-100 text-gray-700';
                                        $text = $status_text[$user['status']] ?? 'Inconnu';
                                        ?>
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?= $color ?>">
                                            <?= $text ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?= date('d/m/Y', strtotime($user['created_at'])) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                            <?php if ($user['status'] === 'active'): ?>
                                                <button type="submit" name="action" value="deactivate" class="text-red-600 hover:text-red-900">
                                                    <i class="fas fa-ban"></i> Suspendre
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

