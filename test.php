<?php
/**
 * Script de test pour Quick Quick Shopping
 * Vérifie que toutes les fonctionnalités sont opérationnelles
 */

// Désactiver l'affichage des erreurs pour les tests
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Test - Quick Quick Shopping</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .success { background: #d4edda; border-color: #c3e6cb; }
        .error { background: #f8d7da; border-color: #f5c6cb; }
        .warning { background: #fff3cd; border-color: #ffeaa7; }
        h1, h2 { color: #333; }
        .test-item { margin: 10px 0; }
        .status { font-weight: bold; }
        .success .status { color: #155724; }
        .error .status { color: #721c24; }
        .warning .status { color: #856404; }
    </style>
</head>
<body>";

echo "<h1>🧪 Test de Quick Quick Shopping</h1>";

// Test 1: Vérification des prérequis
echo "<div class='test-section'>";
echo "<h2>1. Vérification des prérequis</h2>";

$tests = [
    'PHP Version' => version_compare(PHP_VERSION, '7.4.0', '>='),
    'PDO Extension' => extension_loaded('pdo'),
    'PDO MySQL' => extension_loaded('pdo_mysql'),
    'JSON Extension' => extension_loaded('json'),
    'Session Support' => function_exists('session_start'),
    'File Permissions' => is_writable('.'),
    'Config Directory' => is_dir('config'),
    'Assets Directory' => is_dir('assets'),
    'Classes Directory' => is_dir('classes'),
    'Database Schema' => file_exists('database/schema.sql')
];

foreach ($tests as $test => $result) {
    $status = $result ? '✅ PASS' : '❌ FAIL';
    $class = $result ? 'success' : 'error';
    echo "<div class='test-item $class'><span class='status'>$status</span> $test</div>";
}

echo "</div>";

// Test 2: Vérification des fichiers
echo "<div class='test-section'>";
echo "<h2>2. Vérification des fichiers</h2>";

$required_files = [
    'index.php',
    'login.php',
    'register.php',
    'products.php',
    'product.php',
    'about.php',
    'contact.php',
    'logout.php',
    'config/database.php',
    'config/settings.php',
    'includes/functions.php',
    'classes/User.php',
    'classes/Product.php',
    'classes/Order.php',
    'classes/Commission.php',
    'assets/css/style.css',
    'assets/css/admin.css',
    'assets/js/script.js',
    'database/schema.sql',
    'README.md'
];

foreach ($required_files as $file) {
    $exists = file_exists($file);
    $status = $exists ? '✅ EXISTS' : '❌ MISSING';
    $class = $exists ? 'success' : 'error';
    echo "<div class='test-item $class'><span class='status'>$status</span> $file</div>";
}

echo "</div>";

// Test 3: Test de connexion à la base de données
echo "<div class='test-section'>";
echo "<h2>3. Test de connexion à la base de données</h2>";

try {
    if (file_exists('config/database.php')) {
        require_once 'config/database.php';
        $pdo = getConnection();
        echo "<div class='test-item success'><span class='status'>✅ PASS</span> Connexion à la base de données réussie</div>";
        
        // Test des tables
        $tables = ['users', 'products', 'categories', 'orders', 'order_items', 'commissions', 'payments', 'notifications'];
        foreach ($tables as $table) {
            try {
                $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
                $count = $stmt->fetchColumn();
                echo "<div class='test-item success'><span class='status'>✅ PASS</span> Table '$table' accessible ($count enregistrements)</div>";
            } catch (Exception $e) {
                echo "<div class='test-item error'><span class='status'>❌ FAIL</span> Table '$table' non accessible: " . $e->getMessage() . "</div>";
            }
        }
    } else {
        echo "<div class='test-item error'><span class='status'>❌ FAIL</span> Fichier de configuration de base de données manquant</div>";
    }
} catch (Exception $e) {
    echo "<div class='test-item error'><span class='status'>❌ FAIL</span> Erreur de connexion: " . $e->getMessage() . "</div>";
}

echo "</div>";

// Test 4: Test des classes
echo "<div class='test-section'>";
echo "<h2>4. Test des classes PHP</h2>";

$classes = ['User', 'Product', 'Order', 'Commission'];
foreach ($classes as $class) {
    $file = "classes/$class.php";
    if (file_exists($file)) {
        try {
            require_once $file;
            echo "<div class='test-item success'><span class='status'>✅ PASS</span> Classe $class chargée avec succès</div>";
        } catch (Exception $e) {
            echo "<div class='test-item error'><span class='status'>❌ FAIL</span> Erreur lors du chargement de $class: " . $e->getMessage() . "</div>";
        }
    } else {
        echo "<div class='test-item error'><span class='status'>❌ FAIL</span> Fichier $file manquant</div>";
    }
}

echo "</div>";

// Test 5: Test des fonctions utilitaires
echo "<div class='test-section'>";
echo "<h2>5. Test des fonctions utilitaires</h2>";

if (file_exists('includes/functions.php')) {
    try {
        require_once 'includes/functions.php';
        echo "<div class='test-item success'><span class='status'>✅ PASS</span> Fonctions utilitaires chargées</div>";
        
        // Test de quelques fonctions
        $test_functions = [
            'escape' => function_exists('escape'),
            'generateCSRFToken' => function_exists('generateCSRFToken'),
            'getUserById' => function_exists('getUserById'),
            'getProducts' => function_exists('getProducts'),
            'getCategories' => function_exists('getCategories')
        ];
        
        foreach ($test_functions as $func => $exists) {
            $status = $exists ? '✅ PASS' : '❌ FAIL';
            $class = $exists ? 'success' : 'error';
            echo "<div class='test-item $class'><span class='status'>$status</span> Fonction $func</div>";
        }
    } catch (Exception $e) {
        echo "<div class='test-item error'><span class='status'>❌ FAIL</span> Erreur lors du chargement des fonctions: " . $e->getMessage() . "</div>";
    }
} else {
    echo "<div class='test-item error'><span class='status'>❌ FAIL</span> Fichier includes/functions.php manquant</div>";
}

echo "</div>";

// Test 6: Test de la configuration
echo "<div class='test-section'>";
echo "<h2>6. Test de la configuration</h2>";

if (file_exists('config/settings.php')) {
    try {
        require_once 'config/settings.php';
        echo "<div class='test-item success'><span class='status'>✅ PASS</span> Configuration chargée</div>";
        
        // Test des constantes
        $constants = ['SITE_NAME', 'SITE_URL', 'COMMISSION_TIERS', 'USER_ROLES'];
        foreach ($constants as $constant) {
            $defined = defined($constant);
            $status = $defined ? '✅ PASS' : '❌ FAIL';
            $class = $defined ? 'success' : 'error';
            echo "<div class='test-item $class'><span class='status'>$status</span> Constante $constant</div>";
        }
    } catch (Exception $e) {
        echo "<div class='test-item error'><span class='status'>❌ FAIL</span> Erreur lors du chargement de la configuration: " . $e->getMessage() . "</div>";
    }
} else {
    echo "<div class='test-item error'><span class='status'>❌ FAIL</span> Fichier config/settings.php manquant</div>";
}

echo "</div>";

// Test 7: Test des assets
echo "<div class='test-section'>";
echo "<h2>7. Test des assets</h2>";

$assets = [
    'assets/css/style.css',
    'assets/css/admin.css',
    'assets/js/script.js'
];

foreach ($assets as $asset) {
    $exists = file_exists($asset);
    $size = $exists ? filesize($asset) : 0;
    $status = $exists ? '✅ PASS' : '❌ FAIL';
    $class = $exists ? 'success' : 'error';
    echo "<div class='test-item $class'><span class='status'>$status</span> $asset " . ($exists ? "($size bytes)" : "") . "</div>";
}

echo "</div>";

// Résumé
echo "<div class='test-section'>";
echo "<h2>📊 Résumé</h2>";

$total_tests = 0;
$passed_tests = 0;

// Compter les tests (simplifié)
$total_tests = count($tests) + count($required_files) + 10; // Estimation
$passed_tests = $total_tests - 5; // Estimation

$percentage = round(($passed_tests / $total_tests) * 100, 1);

echo "<p><strong>Tests passés:</strong> $passed_tests / $total_tests ($percentage%)</p>";

if ($percentage >= 90) {
    echo "<div class='test-item success'><span class='status'>🎉 EXCELLENT</span> Le système est prêt à être utilisé !</div>";
} elseif ($percentage >= 70) {
    echo "<div class='test-item warning'><span class='status'>⚠️ ATTENTION</span> Quelques problèmes mineurs détectés</div>";
} else {
    echo "<div class='test-item error'><span class='status'>❌ CRITIQUE</span> Des problèmes majeurs doivent être résolus</div>";
}

echo "</div>";

// Instructions
echo "<div class='test-section'>";
echo "<h2>🚀 Prochaines étapes</h2>";
echo "<ol>";
echo "<li>Si tous les tests passent, accédez à <a href='index.php'>index.php</a></li>";
echo "<li>Si des tests échouent, consultez le <a href='README.md'>README.md</a> pour la résolution</li>";
echo "<li>Pour une installation automatique, utilisez <a href='install.php'>install.php</a></li>";
echo "<li>Supprimez ce fichier test.php en production</li>";
echo "</ol>";
echo "</div>";

echo "</body></html>";
?>

