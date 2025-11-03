<?php
// Mode debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Récupérer les infos de l'utilisateur
try {
    $stmt = $bdd->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        // Debug : afficher l'ID de session
        error_log("User not found with ID: " . $_SESSION['user_id']);
        header('Location: logout.php');
        exit;
    }
} catch (Exception $e) {
    error_log("Error loading user: " . $e->getMessage());
    header('Location: logout.php');
    exit;
}

$message = null;
$message_type = null;

// Traitement des mises à jour
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $name = trim($_POST['name']);
        $phone = trim($_POST['phone']);
        $address = trim($_POST['address']);
        
        $stmt = $bdd->prepare("UPDATE users SET name = ?, phone = ?, address = ? WHERE id = ?");
        $stmt->execute([$name, $phone, $address, $_SESSION['user_id']]);
        
        $_SESSION['nom'] = $name;
        $message = "Profil mis à jour avec succès !";
        $message_type = "success";
        
        // Recharger les infos
        $stmt = $bdd->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if (password_verify($current_password, $user['password'])) {
            if ($new_password === $confirm_password) {
                $hashed = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $bdd->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashed, $_SESSION['user_id']]);
                
                $message = "Mot de passe modifié avec succès !";
                $message_type = "success";
            } else {
                $message = "Les mots de passe ne correspondent pas.";
                $message_type = "error";
            }
        } else {
            $message = "Mot de passe actuel incorrect.";
            $message_type = "error";
        }
    }
}

// Récupérer les commandes de l'utilisateur (si la table existe)
$commandes = [];
try {
    // Vérifier si la table commandes existe
    $stmt = $bdd->query("SHOW TABLES LIKE 'commandes'");
    $table_exists = $stmt->fetch();
    
    if ($table_exists) {
        $stmt = $bdd->prepare("SELECT * FROM commandes WHERE user_id = ? ORDER BY date_commande DESC");
        $stmt->execute([$_SESSION['user_id']]);
        $commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    // Si la table n'existe pas, on continue sans commandes
    $commandes = [];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil - Quick Quick Shopping</title>
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
                        
                        <!-- Dropdown menu -->
                        <div class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg hidden group-hover:block z-50">
                            <a href="profile.php" class="block px-4 py-2 hover:bg-gray-100 transition">
                                <i class="fas fa-user mr-2"></i>Mon Profil
                            </a>
                            <a href="orders.php" class="block px-4 py-2 hover:bg-gray-100 transition">
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

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <?php if ($message): ?>
                <div class="bg-<?= $message_type === 'success' ? 'green' : 'red' ?>-100 border border-<?= $message_type === 'success' ? 'green' : 'red' ?>-400 text-<?= $message_type === 'success' ? 'green' : 'red' ?>-700 px-4 py-3 rounded mb-6">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <h1 class="text-4xl font-serif text-[#b06393] mb-8">Mon Profil</h1>
            
            <?php if (empty($user)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    Erreur : Impossible de charger les informations utilisateur.
                </div>
            <?php else: ?>
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Sidebar -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-lg shadow-md p-6 text-center sticky top-24">
                        <div class="w-24 h-24 rounded-full bg-[#b06393] text-white mx-auto mb-4 flex items-center justify-center text-4xl font-bold">
                            <?= strtoupper(substr($user['name'], 0, 1)) ?>
                        </div>
                        <h2 class="text-xl font-semibold mb-2"><?= htmlspecialchars($user['name']) ?></h2>
                        <p class="text-sm text-gray-600 mb-4"><?= ucfirst($user['role']) ?></p>
                        
                        <div class="space-y-2">
                            <div class="flex items-center justify-center text-sm text-gray-600">
                                <i class="fas fa-envelope mr-2"></i>
                                <?= htmlspecialchars($user['email']) ?>
                            </div>
                            <?php if ($user['phone']): ?>
                                <div class="flex items-center justify-center text-sm text-gray-600">
                                    <i class="fas fa-phone mr-2"></i>
                                    <?= htmlspecialchars($user['phone']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Édition du profil -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-2xl font-semibold mb-4">
                            <i class="fas fa-user-edit mr-2"></i>
                            Modifier mon profil
                        </h2>
                        
                        <form method="POST" class="space-y-4">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nom complet</label>
                                <input type="text" id="name" name="name" value="<?= htmlspecialchars($user['name']) ?>" required
                                       class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-[#b06393]">
                            </div>
                            
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Téléphone</label>
                                <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>"
                                       class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-[#b06393]">
                            </div>
                            
                            <div>
                                <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Adresse</label>
                                <textarea id="address" name="address" rows="3"
                                          class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-[#b06393]"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                            </div>
                            
                            <button type="submit" name="update_profile" class="w-full bg-[#b06393] text-white py-2 rounded hover:bg-[#d87eb6] transition">
                                Enregistrer les modifications
                            </button>
                        </form>
                    </div>

                    <!-- Changer le mot de passe -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-2xl font-semibold mb-4">
                            <i class="fas fa-lock mr-2"></i>
                            Changer mon mot de passe
                        </h2>
                        
                        <form method="POST" class="space-y-4">
                            <div>
                                <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">Mot de passe actuel</label>
                                <input type="password" id="current_password" name="current_password" required
                                       class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-[#b06393]">
                            </div>
                            
                            <div>
                                <label for="new_password" class="block text-sm font-medium text-gray-700 mb-1">Nouveau mot de passe</label>
                                <input type="password" id="new_password" name="new_password" required
                                       class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-[#b06393]">
                            </div>
                            
                            <div>
                                <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Confirmer le mot de passe</label>
                                <input type="password" id="confirm_password" name="confirm_password" required
                                       class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-[#b06393]">
                            </div>
                            
                            <button type="submit" name="change_password" class="w-full bg-[#b06393] text-white py-2 rounded hover:bg-[#d87eb6] transition">
                                Changer le mot de passe
                            </button>
                        </form>
                    </div>

                    <!-- Mes commandes récentes -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-2xl font-semibold mb-4">
                            <i class="fas fa-shopping-bag mr-2"></i>
                            Mes commandes
                        </h2>
                        
                        <?php if (empty($commandes)): ?>
                            <p class="text-gray-600 text-center py-8">Aucune commande pour le moment.</p>
                        <?php else: ?>
                            <div class="space-y-4">
                                <?php foreach ($commandes as $commande): ?>
                                    <div class="border-b pb-4">
                                        <div class="flex justify-between items-center">
                                            <div>
                                                <p class="font-semibold">Commande #<?= $commande['commande_id'] ?></p>
                                                <p class="text-sm text-gray-600"><?= date('d/m/Y à H:i', strtotime($commande['date_commande'])) ?></p>
                                            </div>
                                            <div class="text-right">
                                                <p class="font-bold text-[#b06393]"><?= number_format($commande['total'], 0, ',', ' ') ?> FCFA</p>
                                                <p class="text-xs text-gray-600 uppercase"><?= ucfirst($commande['statut']) ?></p>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <a href="orders.php" class="block text-center mt-4 text-[#b06393] hover:underline">
                                Voir toutes mes commandes →
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php endif; // Fin du if (!empty($user)) ?>

    <?php include 'includes/footer.php'; ?>

<script>
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
                }, 500); // Délai augmenté à 500ms avant de fermer
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

