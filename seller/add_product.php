<?php
session_start();
include '../config/db.php';

// Vérifier que l'utilisateur est connecté et est un vendeur
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

if ($_SESSION['role'] !== 'seller' && $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

$message = null;
$message_type = null;

// Récupérer les catégories
$categories = [];
try {
    $stmt = $bdd->query("SELECT * FROM categories WHERE status = 'active' ORDER BY name");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Si la requête échoue, réessayer avec d'autres noms de colonnes
    try {
        $stmt = $bdd->query("SELECT id as categorie_id, name as nom FROM categories");
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e2) {
        $categories = [];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $prix = floatval($_POST['prix'] ?? 0);
    $stock = intval($_POST['stock'] ?? 0);
    $categorie_id = intval($_POST['categorie_id'] ?? 0);
    $reference = trim($_POST['reference'] ?? '');
    
    if ($nom && $description && $prix > 0) {
        try {
            $image_url = null;
            
            // Upload image si fournie
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = '../images/products/';
                
                // Créer le dossier s'il n'existe pas
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $file_name = time() . '_' . basename($_FILES['image']['name']);
                $target_file = $upload_dir . $file_name;
                
                // Debug
                error_log('Upload image: ' . $file_name);
                error_log('Target file: ' . $target_file);
                error_log('File error: ' . $_FILES['image']['error']);
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                    $image_url = 'images/products/' . $file_name;
                    error_log('Image uploadée: ' . $image_url);
                } else {
                    error_log('Échec upload image');
                }
            } else {
                error_log('Pas d\'image ou erreur upload');
            }
            
            $stmt = $bdd->prepare("INSERT INTO products (name, description, price, stock_quantity, category_id, reference, image, status, seller_id) VALUES (?, ?, ?, ?, ?, ?, ?, 'active', ?)");
            $stmt->execute([$nom, $description, $prix, $stock, $categorie_id ?: null, $reference ?: null, $image_url, $_SESSION['user_id']]);
            
            $message = "Produit ajouté avec succès !";
            $message_type = "success";
            
            // Réinitialiser le formulaire
            $_POST = [];
        } catch (Exception $e) {
            $message = "Erreur : " . $e->getMessage();
            $message_type = "error";
            error_log('Erreur ajout produit: ' . $e->getMessage());
        }
    } else {
        $message = "Veuillez remplir tous les champs obligatoires.";
        $message_type = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un produit - Quick Quick Shopping</title>
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
        <div class="max-w-2xl mx-auto">
            <h1 class="text-4xl font-serif text-[#b06393] mb-8">Ajouter un produit</h1>

            <?php if ($message): ?>
                <div class="bg-<?= $message_type === 'success' ? 'green' : 'red' ?>-100 border border-<?= $message_type === 'success' ? 'green' : 'red' ?>-400 text-<?= $message_type === 'success' ? 'green' : 'red' ?>-700 px-4 py-3 rounded mb-6">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-lg shadow-md p-6">
                <form method="POST" enctype="multipart/form-data" class="space-y-4">
                    <!-- Nom et Référence -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="nom" class="block text-sm font-medium text-gray-700 mb-1">Nom du produit *</label>
                            <input type="text" id="nom" name="nom" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-[#b06393]">
                        </div>
                        <div>
                            <label for="reference" class="block text-sm font-medium text-gray-700 mb-1">Référence</label>
                            <input type="text" id="reference" name="reference"
                                   class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-[#b06393]"
                                   placeholder="REF001">
                        </div>
                    </div>
                    
                    <!-- Description -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description *</label>
                        <textarea id="description" name="description" rows="4" required
                                  class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-[#b06393]"
                                  placeholder="Décrivez votre produit..."></textarea>
                    </div>
                    
                    <!-- Catégorie et Prix -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="categorie_id" class="block text-sm font-medium text-gray-700 mb-1">Catégorie</label>
                            <select id="categorie_id" name="categorie_id"
                                    class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-[#b06393]">
                                <option value="">Choisir une catégorie</option>
                                <?php foreach ($categories as $categorie): 
                                    // Adapter selon les noms de colonnes disponibles
                                    $cat_id = $categorie['id'] ?? $categorie['categorie_id'] ?? 0;
                                    $cat_name = $categorie['name'] ?? $categorie['nom'] ?? 'Sans nom';
                                ?>
                                    <option value="<?= $cat_id ?>"><?= htmlspecialchars($cat_name) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label for="prix" class="block text-sm font-medium text-gray-700 mb-1">Prix (FCFA) *</label>
                            <input type="number" id="prix" name="prix" min="0" step="0.01" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-[#b06393]">
                        </div>
                    </div>
                    
                    <!-- Stock et Image -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="stock" class="block text-sm font-medium text-gray-700 mb-1">Stock *</label>
                            <input type="number" id="stock" name="stock" min="0" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-[#b06393]">
                        </div>
                        <div>
                            <label for="image" class="block text-sm font-medium text-gray-700 mb-1">Image du produit</label>
                            <input type="file" id="image" name="image" accept="image/*"
                                   class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-[#b06393]">
                            <p class="text-xs text-gray-500 mt-1">Formats acceptés: JPG, PNG, WebP (max 5MB)</p>
                        </div>
                    </div>
                    
                    <!-- Aperçu image -->
                    <div id="imagePreview" class="hidden">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Aperçu</label>
                        <img id="preview" src="" alt="Aperçu" class="max-w-xs rounded-lg border border-gray-300">
                    </div>
                    
                    <div class="flex space-x-4">
                        <button type="submit" class="flex-1 bg-[#b06393] text-white py-3 rounded-full hover:bg-[#d87eb6] transition font-semibold">
                            <i class="fas fa-plus-circle mr-2"></i>
                            Ajouter le produit
                        </button>
                        <a href="dashboard.php" class="flex-1 bg-gray-200 text-gray-700 py-3 rounded-full hover:bg-gray-300 transition font-semibold text-center">
                            Annuler
                        </a>
                    </div>
                </form>
            </div>
            
            <script>
                // Aperçu image avant upload
                document.getElementById('image').addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            document.getElementById('preview').src = e.target.result;
                            document.getElementById('imagePreview').classList.remove('hidden');
                        }
                        reader.readAsDataURL(file);
                    }
                });
            </script>
        </div>
    </div>
</body>
</html>

