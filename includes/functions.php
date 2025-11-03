<?php
/**
 * Fonctions utilitaires pour Quick Quick Shopping
 */

require_once 'config/database.php';

// ==================== FONCTIONS UTILITAIRES ====================

/**
 * Échapper les données pour l'affichage HTML
 */
function escape($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

/**
 * Générer un token CSRF
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Vérifier le token CSRF
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Rediriger vers une page
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * Afficher un message flash
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash'][$type] = $message;
}

/**
 * Récupérer et supprimer un message flash
 */
function getFlashMessage($type) {
    if (isset($_SESSION['flash'][$type])) {
        $message = $_SESSION['flash'][$type];
        unset($_SESSION['flash'][$type]);
        return $message;
    }
    return null;
}

// ==================== FONCTIONS UTILISATEUR ====================

/**
 * Obtenir un utilisateur par ID
 */
function getUserById($id) {
    return fetchOne("SELECT * FROM users WHERE id = ?", [$id]);
}

/**
 * Obtenir un utilisateur par email
 */
function getUserByEmail($email) {
    return fetchOne("SELECT * FROM users WHERE email = ?", [$email]);
}

/**
 * Créer un nouvel utilisateur
 */
function createUser($data) {
    $sql = "INSERT INTO users (name, email, password, role, phone, address, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())";
    return insertAndGetId($sql, [
        $data['name'],
        $data['email'],
        password_hash($data['password'], PASSWORD_DEFAULT),
        $data['role'],
        $data['phone'] ?? null,
        $data['address'] ?? null
    ]);
}

/**
 * Vérifier les identifiants de connexion
 */
function verifyLogin($email, $password) {
    $user = getUserByEmail($email);
    if ($user && password_verify($password, $user['password'])) {
        return $user;
    }
    return false;
}

/**
 * Mettre à jour le profil utilisateur
 */
function updateUser($id, $data) {
    $sql = "UPDATE users SET name = ?, email = ?, phone = ?, address = ?, updated_at = NOW() WHERE id = ?";
    return executeQuery($sql, [
        $data['name'],
        $data['email'],
        $data['phone'],
        $data['address'],
        $id
    ]);
}

// ==================== FONCTIONS PRODUITS ====================

/**
 * Obtenir tous les produits avec pagination
 */
function getProducts($limit = 12, $offset = 0, $category_id = null, $search = null) {
    $sql = "SELECT p.*, u.name as seller_name, c.name as category_name 
            FROM products p 
            JOIN users u ON p.seller_id = u.id 
            JOIN categories c ON p.category_id = c.id 
            WHERE p.status = 'active'";
    $params = [];
    
    if ($category_id) {
        $sql .= " AND p.category_id = ?";
        $params[] = $category_id;
    }
    
    if ($search) {
        $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    $sql .= " ORDER BY p.created_at DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    
    return fetchAll($sql, $params);
}

/**
 * Obtenir un produit par ID
 */
function getProductById($id) {
    return fetchOne("SELECT p.*, u.name as seller_name, c.name as category_name 
                     FROM products p 
                     JOIN users u ON p.seller_id = u.id 
                     JOIN categories c ON p.category_id = c.id 
                     WHERE p.id = ?", [$id]);
}

/**
 * Obtenir les produits en vedette
 */
function getFeaturedProducts($limit = 8) {
    return fetchAll("SELECT p.*, u.name as seller_name 
                    FROM products p 
                    JOIN users u ON p.seller_id = u.id 
                    WHERE p.status = 'active' 
                    ORDER BY p.created_at DESC 
                    LIMIT ?", [$limit]);
}

/**
 * Créer un nouveau produit
 */
function createProduct($data) {
    $sql = "INSERT INTO products (name, description, price, image, category_id, seller_id, stock_quantity, status, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    return insertAndGetId($sql, [
        $data['name'],
        $data['description'],
        $data['price'],
        $data['image'] ?? null,
        $data['category_id'],
        $data['seller_id'],
        $data['stock_quantity'],
        $data['status'] ?? 'pending'
    ]);
}

/**
 * Mettre à jour un produit
 */
function updateProduct($id, $data) {
    $sql = "UPDATE products SET name = ?, description = ?, price = ?, image = ?, category_id = ?, stock_quantity = ?, updated_at = NOW() WHERE id = ?";
    return executeQuery($sql, [
        $data['name'],
        $data['description'],
        $data['price'],
        $data['image'],
        $data['category_id'],
        $data['stock_quantity'],
        $id
    ]);
}

/**
 * Supprimer un produit
 */
function deleteProduct($id) {
    return executeQuery("UPDATE products SET status = 'deleted', updated_at = NOW() WHERE id = ?", [$id]);
}

// ==================== FONCTIONS CATÉGORIES ====================

/**
 * Obtenir toutes les catégories
 */
function getCategories() {
    return fetchAll("SELECT * FROM categories WHERE status = 'active' ORDER BY name");
}

/**
 * Obtenir une catégorie par ID
 */
function getCategoryById($id) {
    return fetchOne("SELECT * FROM categories WHERE id = ?", [$id]);
}

// ==================== FONCTIONS COMMANDES ====================

/**
 * Créer une nouvelle commande
 */
function createOrder($data) {
    $sql = "INSERT INTO orders (customer_id, total_amount, status, payment_method, shipping_address, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())";
    return insertAndGetId($sql, [
        $data['customer_id'],
        $data['total_amount'],
        $data['status'] ?? 'pending',
        $data['payment_method'],
        $data['shipping_address']
    ]);
}

/**
 * Ajouter un article à une commande
 */
function addOrderItem($order_id, $product_id, $quantity, $price) {
    $sql = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
    return executeQuery($sql, [$order_id, $product_id, $quantity, $price]);
}

/**
 * Obtenir les commandes d'un client
 */
function getCustomerOrders($customer_id) {
    return fetchAll("SELECT * FROM orders WHERE customer_id = ? ORDER BY created_at DESC", [$customer_id]);
}

/**
 * Obtenir les commandes d'un vendeur
 */
function getSellerOrders($seller_id) {
    return fetchAll("SELECT o.*, oi.product_id, oi.quantity, oi.price, p.name as product_name
                    FROM orders o
                    JOIN order_items oi ON o.id = oi.order_id
                    JOIN products p ON oi.product_id = p.id
                    WHERE p.seller_id = ? AND o.status = 'completed'
                    ORDER BY o.created_at DESC", [$seller_id]);
}

// ==================== FONCTIONS COMMISSIONS ====================

/**
 * Calculer le taux de commission selon le montant total des ventes
 */
function calculateCommissionRate($total_sales) {
    if ($total_sales <= 250000) {
        return 0.01; // 1%
    } elseif ($total_sales <= 500000) {
        return 0.0075; // 0.75%
    } elseif ($total_sales <= 1000000) {
        return 0.005; // 0.5%
    } else {
        return 0.0025; // 0.25%
    }
}

/**
 * Calculer la commission pour une vente
 */
function calculateCommission($amount, $total_sales) {
    $rate = calculateCommissionRate($total_sales);
    return $amount * $rate;
}

/**
 * Obtenir le total des ventes d'un vendeur
 */
function getSellerTotalSales($seller_id) {
    $result = fetchOne("SELECT COALESCE(SUM(oi.price * oi.quantity), 0) as total_sales
                       FROM orders o
                       JOIN order_items oi ON o.id = oi.order_id
                       JOIN products p ON oi.product_id = p.id
                       WHERE p.seller_id = ? AND o.status = 'completed'", [$seller_id]);
    return $result['total_sales'];
}

/**
 * Enregistrer une commission
 */
function recordCommission($seller_id, $order_id, $amount, $commission_rate, $commission_amount) {
    $sql = "INSERT INTO commissions (seller_id, order_id, amount, commission_rate, commission_amount, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())";
    return executeQuery($sql, [$seller_id, $order_id, $amount, $commission_rate, $commission_amount]);
}

// ==================== FONCTIONS ADMIN ====================

/**
 * Obtenir les statistiques générales
 */
function getAdminStats() {
    $stats = [];
    
    // Nombre total d'utilisateurs
    $stats['total_users'] = fetchOne("SELECT COUNT(*) as count FROM users")['count'];
    $stats['total_sellers'] = fetchOne("SELECT COUNT(*) as count FROM users WHERE role = 'seller'")['count'];
    $stats['total_customers'] = fetchOne("SELECT COUNT(*) as count FROM users WHERE role = 'customer'")['count'];
    
    // Nombre total de produits
    $stats['total_products'] = fetchOne("SELECT COUNT(*) as count FROM products WHERE status = 'active'")['count'];
    
    // Chiffre d'affaires total
    $stats['total_revenue'] = fetchOne("SELECT COALESCE(SUM(total_amount), 0) as total FROM orders WHERE status = 'completed'")['total'];
    
    // Commissions totales
    $stats['total_commissions'] = fetchOne("SELECT COALESCE(SUM(commission_amount), 0) as total FROM commissions")['total'];
    
    return $stats;
}

/**
 * Obtenir les vendeurs en attente de validation
 */
function getPendingSellers() {
    return fetchAll("SELECT * FROM users WHERE role = 'seller' AND status = 'pending' ORDER BY created_at DESC");
}

/**
 * Valider un vendeur
 */
function approveSeller($seller_id) {
    return executeQuery("UPDATE users SET status = 'active', updated_at = NOW() WHERE id = ?", [$seller_id]);
}

/**
 * Rejeter un vendeur
 */
function rejectSeller($seller_id) {
    return executeQuery("UPDATE users SET status = 'rejected', updated_at = NOW() WHERE id = ?", [$seller_id]);
}
?>

