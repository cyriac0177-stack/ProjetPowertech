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

$product_id = intval($_GET['id'] ?? 0);
$message = null;
$message_type = null;
$product = null;

// Récupérer le produit du vendeur connecté uniquement
try {
    $stmt = $bdd->prepare("SELECT p.*, c.name as categorie_nom FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ? AND p.seller_id = ?");
    $stmt->execute([$product_id, $_SESSION['user_id']]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        header('Location: products.php');
        exit;
    }
} catch (Exception $e) {
    header('Location: products.php');
    exit;
}

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
    $disponible = isset($_POST['disponible']) ? 1 : 0;
    $image_url = $product['image_url'] ?? '';
    
    if ($nom && $description && $prix > 0) {
        try {
            // Upload nouvelle image si fournie
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = '../images/products/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                // Supprimer l'ancienne image si existe
                if (!empty($product['image_url'])) {
                    $old_image = '../' . $product['image_url'];
                    if (file_exists($old_image)) {
                        unlink($old_image);
                    }
                }
                
                $file_name = time() . '_' . basename($_FILES['image']['name']);
                $target_file = $upload_dir . $file_name;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                    $image_url = 'images/products/' . $file_name;
                }
            }
            
            $status = $disponible ? 'active' : 'pending';
            $stmt = $bdd->prepare("UPDATE products SET name = ?, description = ?, price = ?, stock_quantity = ?, category_id = ?, reference = ?, image = ?, status = ? WHERE id = ? AND seller_id = ?");
            $stmt->execute([$nom, $description, $prix, $stock, $categorie_id ?: null, $reference ?: null, $image_url, $status, $product_id, $_SESSION['user_id']]);
            
            $message = "Produit modifié avec succès !";
            $message_type = "success";
            
            // Recharger les données du produit
            $stmt = $bdd->prepare("SELECT p.*, c.name as categorie_nom FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ? AND p.seller_id = ?");
            $stmt->execute([$product_id, $_SESSION['user_id']]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $message = "Erreur : " . $e->getMessage();
            $message_type = "error";
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
    <title>Modifier un produit - Quick Quick Shopping</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-[#fdf1f7] min-h-screen">
    <!-- Header -->
    <header class="bg-white shadow-md sticky top-0 z-50">
        <div class="container mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <a href="products.php" class="flex items-center space-x-2">
                    <i class="fas fa-arrow-left text-[#b06393]"></i>
                    <span class="text-xl font-serif text-[#b06393]">Retour aux produits</span>
                </a>
            </div>
        </div>
    </header>

    <div class="container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto">
            <h1 class="text-4xl font-serif text-[#b06393] mb-8">Modifier un produit</h1>

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
                            <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($product['nom'] ?? '') ?>" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-[#b06393]">
                        </div>
                        <div>
                            <label for="reference" class="block text-sm font-medium text-gray-700 mb-1">Référence</label>
                            <input type="text" id="reference" name="reference" value="<?= htmlspecialchars($product['reference'] ?? '') ?>"
                                   class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-[#b06393]"
                                   placeholder="REF001">
                        </div>
                    </div>
                    
                    <!-- Description -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description *</label>
                        <textarea id="description" name="description" rows="4" required
                                  class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-[#b06393]"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
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
                                    $current_cat_id = $product['categorie_id'] ?? 0;
                                    $is_selected = ($current_cat_id == $cat_id) ? 'selected' : '';
                                ?>
                                    <option value="<?= $cat_id ?>" <?= $is_selected ?>>
                                        <?= htmlspecialchars($cat_name) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label for="prix" class="block text-sm font-medium text-gray-700 mb-1">Prix (FCFA) *</label>
                            <input type="number" id="prix" name="prix" value="<?= $product['prix'] ?? 0 ?>" min="0" step="0.01" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-[#b06393]">
                        </div>
                    </div>
                    
                    <!-- Stock et Image -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="stock" class="block text-sm font-medium text-gray-700 mb-1">Stock *</label>
                            <input type="number" id="stock" name="stock" value="<?= $product['stock'] ?? 0 ?>" min="0" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-[#b06393]">
                        </div>
                        <div>
                            <label for="image" class="block text-sm font-medium text-gray-700 mb-1">Image du produit</label>
                            <input type="file" id="image" name="image" accept="image/*"
                                   class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-[#b06393]">
                            <?php if (!empty($product['image_url'])): ?>
                                <p class="text-xs text-gray-500 mt-1">Image actuelle : <?= basename($product['image_url']) ?></p>
                            <?php else: ?>
                                <p class="text-xs text-gray-500 mt-1">Aucune image actuelle</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Image actuelle -->
                    <?php if (!empty($product['image_url'])): ?>
                        <div class="border rounded-lg p-4 bg-gray-50">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Image actuelle</label>
                            <img src="../<?= htmlspecialchars($product['image_url']) ?>" alt="Image actuelle" class="max-w-xs rounded-lg border border-gray-300">
                        </div>
                    <?php endif; ?>
                    
                    <!-- Aperçu nouvelle image -->
                    <div id="imagePreview" class="hidden">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Aperçu nouvelle image</label>
                        <img id="preview" src="" alt="Aperçu" class="max-w-xs rounded-lg border border-gray-300">
                    </div>
                    
                    <!-- Disponible -->
                    <div class="flex items-center">
                        <input type="checkbox" id="disponible" name="disponible" <?= ($product['disponible'] ?? 0) ? 'checked' : '' ?>
                               class="h-4 w-4 text-[#b06393] border-gray-300 rounded">
                        <label for="disponible" class="ml-2 block text-sm text-gray-700">
                            Produit disponible
                        </label>
                    </div>
                    
                    <div class="flex space-x-4">
                        <button type="submit" class="flex-1 bg-[#b06393] text-white py-3 rounded-full hover:bg-[#d87eb6] transition font-semibold">
                            <i class="fas fa-save mr-2"></i>
                            Enregistrer les modifications
                        </button>
                        <a href="products.php" class="flex-1 bg-gray-200 text-gray-700 py-3 rounded-full hover:bg-gray-300 transition font-semibold text-center">
                            Annuler
                        </a>
                    </div>
                </form>
            </div>
        </div>
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
</body>
</html>

