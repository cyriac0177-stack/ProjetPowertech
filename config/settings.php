<?php
/**
 * Configuration générale du site
 * Quick Quick Shopping
 */

// Configuration du site
define('SITE_NAME', 'Quick Quick Shopping');
define('SITE_URL', 'http://localhost/quick-quick-shopping');
define('SITE_EMAIL', 'contact@quickquickshopping.com');
define('SITE_PHONE', '+225 XX XX XX XX XX');
define('SITE_ADDRESS', 'Abidjan, Côte d\'Ivoire');

// Configuration des commissions
define('COMMISSION_TIERS', [
    ['min' => 0, 'max' => 250000, 'rate' => 0.01],      // 1%
    ['min' => 250001, 'max' => 500000, 'rate' => 0.0075], // 0.75%
    ['min' => 500001, 'max' => 1000000, 'rate' => 0.005], // 0.5%
    ['min' => 1000001, 'max' => PHP_INT_MAX, 'rate' => 0.0025] // 0.25%
]);

// Configuration des vendeurs
define('SELLER_MIN_PRODUCTS', 7);
define('SELLER_APPROVAL_REQUIRED', true);

// Configuration des paiements
define('PAYMENT_METHODS', [
    'orange_money' => 'Orange Money',
    'moov_money' => 'Moov Money',
    'wave' => 'Wave',
    'mtn_money' => 'MTN Money'
]);

// Configuration des catégories par défaut
define('DEFAULT_CATEGORIES', [
    ['name' => 'Vêtements', 'description' => 'Robes, tops, pantalons, jupes', 'icon' => 'fas fa-tshirt'],
    ['name' => 'Accessoires', 'description' => 'Sacs, bijoux, chaussures', 'icon' => 'fas fa-gem'],
    ['name' => 'Beauté', 'description' => 'Maquillage, soins, parfums', 'icon' => 'fas fa-palette'],
    ['name' => 'Lingerie', 'description' => 'Sous-vêtements, pyjamas', 'icon' => 'fas fa-heart'],
    ['name' => 'Chaussures', 'description' => 'Escarpins, baskets, sandales', 'icon' => 'fas fa-shoe-prints'],
    ['name' => 'Déstockage', 'description' => 'Ventes flash et promotions', 'icon' => 'fas fa-fire']
]);

// Configuration des images
define('UPLOAD_PATH', 'assets/images/');
define('PRODUCT_IMAGES_PATH', 'assets/images/products/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'webp']);

// Configuration de la pagination
define('ITEMS_PER_PAGE', 12);
define('ADMIN_ITEMS_PER_PAGE', 20);

// Configuration des sessions
define('SESSION_LIFETIME', 3600); // 1 heure
define('REMEMBER_ME_LIFETIME', 30 * 24 * 3600); // 30 jours

// Configuration de sécurité
define('CSRF_TOKEN_LIFETIME', 3600);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutes

// Configuration des notifications
define('NOTIFICATION_TYPES', [
    'info' => 'Information',
    'success' => 'Succès',
    'warning' => 'Avertissement',
    'error' => 'Erreur'
]);

// Configuration des statuts
define('USER_STATUSES', [
    'active' => 'Actif',
    'pending' => 'En attente',
    'suspended' => 'Suspendu',
    'rejected' => 'Rejeté'
]);

define('PRODUCT_STATUSES', [
    'active' => 'Actif',
    'pending' => 'En attente',
    'sold_out' => 'Rupture de stock',
    'deleted' => 'Supprimé'
]);

define('ORDER_STATUSES', [
    'pending' => 'En attente',
    'confirmed' => 'Confirmée',
    'shipped' => 'Expédiée',
    'delivered' => 'Livrée',
    'cancelled' => 'Annulée'
]);

define('PAYMENT_STATUSES', [
    'pending' => 'En attente',
    'paid' => 'Payé',
    'failed' => 'Échoué',
    'refunded' => 'Remboursé'
]);

// Configuration des rôles
define('USER_ROLES', [
    'admin' => 'Administrateur',
    'seller' => 'Vendeur',
    'customer' => 'Client'
]);

// Configuration de l'environnement
define('ENVIRONMENT', 'development'); // development, production
define('DEBUG_MODE', ENVIRONMENT === 'development');

// Configuration des logs
define('LOG_PATH', 'logs/');
define('LOG_LEVEL', DEBUG_MODE ? 'debug' : 'error');

// Configuration des emails
define('SMTP_HOST', 'localhost');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', '');
define('SMTP_PASSWORD', '');
define('SMTP_ENCRYPTION', 'tls');

// Configuration des rapports
define('REPORT_TYPES', [
    'sales' => 'Rapport de ventes',
    'commissions' => 'Rapport de commissions',
    'users' => 'Rapport d\'utilisateurs',
    'products' => 'Rapport de produits'
]);

// Configuration des exports
define('EXPORT_FORMATS', [
    'pdf' => 'PDF',
    'excel' => 'Excel',
    'csv' => 'CSV'
]);

// Configuration des limites
define('MAX_PRODUCTS_PER_SELLER', 1000);
define('MAX_ORDERS_PER_CUSTOMER', 100);
define('MAX_CART_ITEMS', 50);

// Configuration des délais
define('ORDER_CONFIRMATION_DELAY', 24); // heures
define('SELLER_APPROVAL_DELAY', 24); // heures
define('PRODUCT_APPROVAL_DELAY', 12); // heures

// Configuration des fonctionnalités
define('FEATURES', [
    'seller_registration' => true,
    'product_approval' => true,
    'commission_system' => true,
    'payment_integration' => true,
    'notification_system' => true,
    'reporting' => true,
    'mobile_responsive' => true
]);

// Configuration des thèmes
define('THEME_COLORS', [
    'primary' => '#e91e63',
    'primary_light' => '#f8bbd9',
    'primary_dark' => '#ad1457',
    'secondary' => '#fce4ec',
    'accent' => '#ff4081'
]);

// Configuration des réseaux sociaux
define('SOCIAL_LINKS', [
    'facebook' => 'https://facebook.com/quickquickshopping',
    'instagram' => 'https://instagram.com/quickquickshopping',
    'twitter' => 'https://twitter.com/quickquickshopping'
]);

// Configuration des métadonnées SEO
define('SEO_DEFAULT', [
    'title' => 'Quick Quick Shopping - Vente en ligne d\'articles féminins',
    'description' => 'Plateforme de vente en ligne spécialisée dans les articles féminins. Vêtements, accessoires, beauté. Commissions dégressives pour les vendeurs.',
    'keywords' => 'vente en ligne, articles féminins, vêtements, accessoires, beauté, commissions, vendeurs'
]);

// Fonction pour obtenir la configuration
function getConfig($key, $default = null) {
    $config = [
        'site_name' => SITE_NAME,
        'site_url' => SITE_URL,
        'site_email' => SITE_EMAIL,
        'commission_tiers' => COMMISSION_TIERS,
        'seller_min_products' => SELLER_MIN_PRODUCTS,
        'payment_methods' => PAYMENT_METHODS,
        'default_categories' => DEFAULT_CATEGORIES,
        'upload_path' => UPLOAD_PATH,
        'product_images_path' => PRODUCT_IMAGES_PATH,
        'items_per_page' => ITEMS_PER_PAGE,
        'user_statuses' => USER_STATUSES,
        'product_statuses' => PRODUCT_STATUSES,
        'order_statuses' => ORDER_STATUSES,
        'payment_statuses' => PAYMENT_STATUSES,
        'user_roles' => USER_ROLES,
        'features' => FEATURES,
        'theme_colors' => THEME_COLORS,
        'social_links' => SOCIAL_LINKS,
        'seo_default' => SEO_DEFAULT
    ];
    
    return $config[$key] ?? $default;
}

// Fonction pour vérifier si une fonctionnalité est activée
function isFeatureEnabled($feature) {
    return FEATURES[$feature] ?? false;
}

// Fonction pour obtenir le taux de commission
function getCommissionRate($totalSales) {
    foreach (COMMISSION_TIERS as $tier) {
        if ($totalSales >= $tier['min'] && $totalSales <= $tier['max']) {
            return $tier['rate'];
        }
    }
    return 0.01; // Taux par défaut
}

// Fonction pour formater la configuration
function formatConfig($value) {
    if (is_array($value)) {
        return json_encode($value, JSON_PRETTY_PRINT);
    }
    return $value;
}
?>

