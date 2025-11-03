<?php
include 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email && $password) {
        $stmt = $bdd->prepare("SELECT id, password, name, role FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nom']     = $user['name'];
            $_SESSION['role']    = $user['role'];
            
            // Redirection selon le rôle
            if ($user['role'] === 'admin') {
                // Admin → tableau de bord admin
                header('Location: admin/dashboard.php');
            } elseif ($user['role'] === 'seller') {
                // Vendeur → tableau de bord vendeur
                header('Location: seller/dashboard.php');
            } else {
                // Client → page d'accueil
                header('Location: index.php');
            }
            exit;
        } else {
            $error = "Email ou mot de passe incorrect.";
        }
    } else {
        $error = "Veuillez remplir tous les champs.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Quick Quick Shopping</title>
    <script src="https://cdn.tailwindcss.com"></script>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body>
    <section class="min-h-screen flex items-center justify-center bg-[#fdf1f7] px-4 py-12">
        <div class="bg-white rounded-xl shadow-lg p-8 w-full max-w-md space-y-6">
            <div class="flex justify-center">
                <img src="images/logo.png" alt="" class="h-[10vh]">
            </div>
            <!-- Titre -->
            <h2 class="text-2xl font-serif font-bold text-orrose text-center">Connexion</h2>
            <p class="text-sm text-gray-600 text-center">Connectez-vous à votre compte</p>
            
            <!-- Message de succès après inscription -->
            <?php if (isset($_GET['register']) && $_GET['register'] === 'success'): ?>
                <div class="bg-green-100 text-green-700 p-3 rounded text-sm text-center mb-4">
                    <i class="fas fa-check-circle"></i> Inscription réussie ! Vous pouvez maintenant vous connecter.
                </div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="bg-red-100 text-red-700 p-3 rounded text-sm text-center mb-4">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            <!-- Formulaire -->
            <form class="space-y-4 " method="POST" action="">
                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" id="email" name="email" placeholder="angeemmanuel@gmail.com"
                        class="w-full px-4 py-3 rounded-full border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orrose">
                </div>

                <!-- Mot de passe -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Mot de passe</label>
                    <input type="password" id="password" name="password" placeholder="••••••••"
                        class="w-full px-4 py-3 rounded-full border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orrose">
                </div>

                <!-- Options -->
                <div class="flex items-center justify-between text-sm">
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" class="rounded text-orrose focus:ring-orrose">
                        <span class="text-gray-600">Se souvenir de moi</span>
                    </label>
                    <a href="#" class="text-orrose hover:underline">Mot de passe oublié ?</a>
                </div>

                <!-- Bouton -->
                <button type="submit"
                    class="w-full bg-[#b06393] text-white py-3 rounded-full font-semibold hover:bg-[#d87eb6] hover:text-black transition">
                    Se connecter
                </button>
            </form>

            <!-- Lien inscription -->
            <p class="text-sm text-center text-gray-600">
                Vous n'avez pas de compte ?
                <a href="register.php" class="text-orrose font-medium hover:underline">S'inscrire</a>
            </p>

            <!-- Choix rôle -->
            <div class="flex justify-center space-x-4 text-sm text-gray-600">
                <span class="cursor-pointer hover:text-orrose">Client</span>
                <span>|</span>
                <span class="cursor-pointer hover:text-orrose">Vendeur</span>
            </div>

        </div>
    </section>
</body>

</html>