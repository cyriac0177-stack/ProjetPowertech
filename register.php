<?php
// Activer l'affichage des erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include 'config/db.php';

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom        = trim($_POST['nom'] ?? '');
    $prenom     = trim($_POST['prenom'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $password   = $_POST['password'] ?? '';
    $confirm    = $_POST['confirm'] ?? '';
    $telephone  = trim($_POST['telephone'] ?? '');
    $adresse    = trim($_POST['adresse'] ?? '');
    $role       = $_POST['role'] ?? 'customer';

    // Pour votre structure, on combine nom+prenom en "name"
    $fullName = trim($nom . ' ' . $prenom);
    
    if ($nom && $prenom && $email && $password && $confirm && $password === $confirm) {
        try {
            // Vérifier si l'email existe déjà
            $stmt = $bdd->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = "Cet email est déjà utilisé.";
            } else {
                // Hash du mot de passe
                $hashed = password_hash($password, PASSWORD_DEFAULT);

                // Votre table utilise : name, email, password, role, phone, address
                
                // Utiliser NULL pour les champs optionnels vides
                $telephone = $telephone ?: null;
                $adresse = $adresse ?: null;
                
                // Les rôles autorisés dans votre BDD sont : customer, seller, admin
                // Normaliser le rôle (customer par défaut si non défini)
                $allowedRoles = ['customer', 'seller', 'admin'];
                if (!in_array($role, $allowedRoles)) {
                    $role = 'customer';
                }
                
                // Construire la requête avec les colonnes de VOTRE structure
                $stmt = $bdd->prepare("INSERT INTO users (name, email, password, role, phone, address, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $fullName, // Combiner nom+prenom
                    $email,
                    $hashed,
                    $role,
                    $telephone,
                    $adresse,
                    1 // status actif
                ]);

                // Afficher un message de succès
                $success_message = "Inscription réussie ! Redirection vers la page de connexion...";
                
                // Rediriger après 2 secondes
                echo '<!DOCTYPE html><html><head><meta http-equiv="refresh" content="2;url=login.php?register=success"></head><body style="display:flex;align-items:center;justify-content:center;font-family:Arial;background:#fdf1f7"><div style="text-align:center"><h1 style="color:#b06393">' . htmlspecialchars($success_message) . '</h1></div></body></html>';
                exit;
            }
        } catch (Exception $e) {
            $error = "Erreur lors de l'inscription : " . $e->getMessage();
        }
    } else {
        if ($password !== $confirm) {
            $error = "Les mots de passe ne correspondent pas.";
        } else {
            $error = "Veuillez remplir tous les champs obligatoires.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Quick Quick Shopping</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-[#fdf1f7]">

    <section class="min-h-screen flex items-center justify-center px-4 py-12">
        <div class="bg-white rounded-xl shadow-lg p-8 w-full max-w-md space-y-6">

            <div class="flex justify-center">
                <img src="images/logo.png" alt="Logo" class="h-[10vh]">
            </div>

            <h2 class="text-2xl font-serif font-bold text-orrose text-center">Inscription</h2>
            <p class="text-sm text-gray-600 text-center">Rejoignez-nous aujourd'hui !</p>

            <?php if (isset($error)): ?>
                <div class="bg-red-100 text-red-700 p-3 rounded text-sm text-center mb-4">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <!-- Formulaire -->
            <form method="POST" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Nom -->
                    <div>
                        <label for="nom" class="block text-sm font-medium text-gray-700 mb-1">Nom</label>
                        <input type="text" id="nom" name="nom" placeholder="Nom"
                            class="w-full px-4 py-3 rounded-full border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orrose">
                    </div>

                    <!-- Prénom -->
                    <div>
                        <label for="prenom" class="block text-sm font-medium text-gray-700 mb-1">Prénom</label>
                        <input type="text" id="prenom" name="prenom" placeholder="Prénom"
                            class="w-full px-4 py-3 rounded-full border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orrose">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Société -->
                    <div>
                        <label for="societe" class="block text-sm font-medium text-gray-700 mb-1">Société</label>
                        <input type="text" id="societe" name="societe" placeholder="Nom de la société"
                            class="w-full px-4 py-3 rounded-full border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orrose">
                    </div>

                    <!-- Profession actuelle -->
                    <div>
                        <label for="profession_actuelle" class="block text-sm font-medium text-gray-700 mb-1">Profession actuelle</label>
                        <input type="text" id="profession_actuelle" name="profession_actuelle" placeholder="Styliste, commerçante..."
                            class="w-full px-4 py-3 rounded-full border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orrose">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Mot de passe</label>
                        <input type="password" id="password" name="password" placeholder="••••••••"
                            class="w-full px-4 py-3 rounded-full border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orrose">
                    </div>
                    <div>
                        <label for="confirm" class="block text-sm font-medium text-gray-700 mb-1">Confirmer le mot de passe</label>
                        <input type="password" id="confirm" name="confirm" placeholder="••••••••"
                            class="w-full px-4 py-3 rounded-full border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orrose">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" id="email" name="email" placeholder="exemple@mail.com"
                            class="w-full px-4 py-3 rounded-full border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orrose">
                    </div>

                    <!-- Téléphone -->
                    <div>
                        <label for="telephone" class="block text-sm font-medium text-gray-700 mb-1">Téléphone</label>
                        <input type="text" id="telephone" name="telephone" placeholder="+225 07 00 00 00"
                            class="w-full px-4 py-3 rounded-full border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orrose">
                    </div>
                </div>

                <textarea name="adresse" rows="2" placeholder="Adresse" class="w-full px-4 py-3 rounded-lg border border-gray-300"></textarea>
                
                <!-- Sélection du rôle -->
                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Je souhaite :</label>
                    <select name="role" id="role" class="w-full px-4 py-3 rounded-full border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orrose">
                        <option value="customer" selected>Acheter des produits (Client)</option>
                        <option value="seller">Vendre mes produits (Vendeur)</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">
                        <i class="fas fa-info-circle"></i> 
                        <span id="roleInfo">Rôle Client par défaut. Parfait pour acheter des produits.</span>
                    </p>
                    
                    <script>
                        document.getElementById('role').addEventListener('change', function() {
                            const info = document.getElementById('roleInfo');
                            if (this.value === 'seller') {
                                info.innerHTML = '<i class="fas fa-store"></i> Rôle Vendeur - Vous pourrez créer un compte vendeur et vendre vos produits.';
                                info.classList.remove('text-gray-500');
                                info.classList.add('text-green-600', 'font-semibold');
                            } else {
                                info.innerHTML = '<i class="fas fa-shopping-bag"></i> Rôle Client - Parfait pour acheter des produits de la boutique.';
                                info.classList.remove('text-green-600', 'font-semibold');
                                info.classList.add('text-gray-500');
                            }
                        });
                    </script>
                </div>

                <button type="submit" class="w-full bg-[#b06393] text-white py-3 rounded-full font-semibold hover:bg-[#d87eb6] hover:text-black transition">
                    S'inscrire
                </button>
            </form>

            <p class="text-sm text-center text-gray-600 mt-4">
                Vous avez déjà un compte ?
                <a href="login.php" class="text-orrose font-medium hover:underline">Se connecter</a>
            </p>

        </div>
    </section>

</body>

</html>