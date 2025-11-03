<?php
/**
 * Script d'installation automatique
 * Quick Quick Shopping
 */

// Vérifier si l'installation a déjà été effectuée
if (file_exists('config/installed.lock')) {
    die('L\'installation a déjà été effectuée. Supprimez le fichier config/installed.lock pour réinstaller.');
}

$step = $_GET['step'] ?? 1;
$error = null;
$success = null;

// Traitement des étapes
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($step) {
        case 1:
            // Vérification des prérequis
            $step = 2;
            break;
            
        case 2:
            // Configuration de la base de données
            $host = $_POST['db_host'] ?? 'localhost';
            $name = $_POST['db_name'] ?? 'quick_quick_shopping';
            $user = $_POST['db_user'] ?? 'root';
            $pass = $_POST['db_pass'] ?? '';
            
            try {
                $pdo = new PDO("mysql:host=$host", $user, $pass);
                $pdo->exec("CREATE DATABASE IF NOT EXISTS `$name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                $pdo->exec("USE `$name`");
                
                // Lire et exécuter le schéma SQL
                $sql = file_get_contents('database/schema.sql');
                $pdo->exec($sql);
                
                // Mettre à jour le fichier de configuration
                $config_content = "<?php
/**
 * Configuration de la base de données
 * Quick Quick Shopping
 */

// Configuration de la base de données
define('DB_HOST', '$host');
define('DB_NAME', '$name');
define('DB_USER', '$user');
define('DB_PASS', '$pass');
define('DB_CHARSET', 'utf8mb4');

// Connexion à la base de données
function getConnection() {
    static \$pdo = null;
    
    if (\$pdo === null) {
        try {
            \$dsn = \"mysql:host=\" . DB_HOST . \";dbname=\" . DB_NAME . \";charset=\" . DB_CHARSET;
            \$pdo = new PDO(\$dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException \$e) {
            die(\"Erreur de connexion à la base de données : \" . \$e->getMessage());
        }
    }
    
    return \$pdo;
}

// Fonction pour exécuter une requête préparée
function executeQuery(\$sql, \$params = []) {
    \$pdo = getConnection();
    \$stmt = \$pdo->prepare(\$sql);
    \$stmt->execute(\$params);
    return \$stmt;
}

// Fonction pour récupérer un seul résultat
function fetchOne(\$sql, \$params = []) {
    \$stmt = executeQuery(\$sql, \$params);
    return \$stmt->fetch();
}

// Fonction pour récupérer plusieurs résultats
function fetchAll(\$sql, \$params = []) {
    \$stmt = executeQuery(\$sql, \$params);
    return \$stmt->fetchAll();
}

// Fonction pour insérer et récupérer l'ID
function insertAndGetId(\$sql, \$params = []) {
    \$pdo = getConnection();
    \$stmt = \$pdo->prepare(\$sql);
    \$stmt->execute(\$params);
    return \$pdo->lastInsertId();
}
?>";
                
                file_put_contents('config/database.php', $config_content);
                $step = 3;
                $success = "Base de données configurée avec succès !";
            } catch (Exception $e) {
                $error = "Erreur lors de la configuration de la base de données : " . $e->getMessage();
            }
            break;
            
        case 3:
            // Configuration de l'administrateur
            $admin_name = $_POST['admin_name'] ?? '';
            $admin_email = $_POST['admin_email'] ?? '';
            $admin_password = $_POST['admin_password'] ?? '';
            
            if (empty($admin_name) || empty($admin_email) || empty($admin_password)) {
                $error = "Veuillez remplir tous les champs.";
            } else {
                try {
                    require_once 'config/database.php';
                    require_once 'includes/functions.php';
                    
                    // Créer l'administrateur
                    $admin_id = createUser([
                        'name' => $admin_name,
                        'email' => $admin_email,
                        'password' => $admin_password,
                        'role' => 'admin',
                        'status' => 'active'
                    ]);
                    
                    // Créer le fichier de verrouillage
                    file_put_contents('config/installed.lock', date('Y-m-d H:i:s'));
                    
                    $step = 4;
                    $success = "Installation terminée avec succès !";
                } catch (Exception $e) {
                    $error = "Erreur lors de la création de l'administrateur : " . $e->getMessage();
                }
            }
            break;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation - Quick Quick Shopping</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .install-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .install-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .install-steps {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
        }
        .step {
            display: flex;
            align-items: center;
            margin: 0 1rem;
        }
        .step-number {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #e0e0e0;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 0.5rem;
            font-weight: bold;
        }
        .step.active .step-number {
            background: var(--primary-color);
            color: white;
        }
        .step.completed .step-number {
            background: var(--success-color);
            color: white;
        }
        .requirements {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin: 2rem 0;
        }
        .requirement {
            display: flex;
            align-items: center;
            padding: 1rem;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
        }
        .requirement i {
            margin-right: 0.5rem;
            font-size: 1.2rem;
        }
        .requirement.success i {
            color: var(--success-color);
        }
        .requirement.error i {
            color: var(--error-color);
        }
    </style>
</head>
<body class="auth-page">
    <div class="install-container">
        <div class="install-header">
            <h1><i class="fas fa-shopping-bag"></i> Quick Quick Shopping</h1>
            <p>Installation de la plateforme</p>
        </div>

        <!-- Steps -->
        <div class="install-steps">
            <div class="step <?php echo $step >= 1 ? 'active' : ''; ?> <?php echo $step > 1 ? 'completed' : ''; ?>">
                <div class="step-number">1</div>
                <span>Prérequis</span>
            </div>
            <div class="step <?php echo $step >= 2 ? 'active' : ''; ?> <?php echo $step > 2 ? 'completed' : ''; ?>">
                <div class="step-number">2</div>
                <span>Base de données</span>
            </div>
            <div class="step <?php echo $step >= 3 ? 'active' : ''; ?> <?php echo $step > 3 ? 'completed' : ''; ?>">
                <div class="step-number">3</div>
                <span>Administrateur</span>
            </div>
            <div class="step <?php echo $step >= 4 ? 'active' : ''; ?>">
                <div class="step-number">4</div>
                <span>Terminé</span>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <!-- Step 1: Prérequis -->
        <?php if ($step == 1): ?>
            <h2>Vérification des prérequis</h2>
            <div class="requirements">
                <?php
                $requirements = [
                    'PHP Version' => version_compare(PHP_VERSION, '7.4.0', '>='),
                    'PDO Extension' => extension_loaded('pdo'),
                    'PDO MySQL' => extension_loaded('pdo_mysql'),
                    'JSON Extension' => extension_loaded('json'),
                    'Session Support' => function_exists('session_start'),
                    'File Permissions' => is_writable('.')
                ];
                
                foreach ($requirements as $name => $status):
                ?>
                    <div class="requirement <?php echo $status ? 'success' : 'error'; ?>">
                        <i class="fas fa-<?php echo $status ? 'check-circle' : 'times-circle'; ?>"></i>
                        <span><?php echo $name; ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <form method="POST">
                <button type="submit" class="btn btn-primary btn-large">
                    <i class="fas fa-arrow-right"></i>
                    Continuer
                </button>
            </form>
        <?php endif; ?>

        <!-- Step 2: Base de données -->
        <?php if ($step == 2): ?>
            <h2>Configuration de la base de données</h2>
            <form method="POST">
                <div class="form-group">
                    <label for="db_host">Hôte de la base de données</label>
                    <input type="text" id="db_host" name="db_host" value="localhost" required>
                </div>
                
                <div class="form-group">
                    <label for="db_name">Nom de la base de données</label>
                    <input type="text" id="db_name" name="db_name" value="quick_quick_shopping" required>
                </div>
                
                <div class="form-group">
                    <label for="db_user">Utilisateur</label>
                    <input type="text" id="db_user" name="db_user" value="root" required>
                </div>
                
                <div class="form-group">
                    <label for="db_pass">Mot de passe</label>
                    <input type="password" id="db_pass" name="db_pass">
                </div>
                
                <button type="submit" class="btn btn-primary btn-large">
                    <i class="fas fa-database"></i>
                    Configurer la base de données
                </button>
            </form>
        <?php endif; ?>

        <!-- Step 3: Administrateur -->
        <?php if ($step == 3): ?>
            <h2>Configuration de l'administrateur</h2>
            <form method="POST">
                <div class="form-group">
                    <label for="admin_name">Nom complet</label>
                    <input type="text" id="admin_name" name="admin_name" required>
                </div>
                
                <div class="form-group">
                    <label for="admin_email">Email</label>
                    <input type="email" id="admin_email" name="admin_email" required>
                </div>
                
                <div class="form-group">
                    <label for="admin_password">Mot de passe</label>
                    <input type="password" id="admin_password" name="admin_password" required minlength="6">
                </div>
                
                <button type="submit" class="btn btn-primary btn-large">
                    <i class="fas fa-user-shield"></i>
                    Créer l'administrateur
                </button>
            </form>
        <?php endif; ?>

        <!-- Step 4: Terminé -->
        <?php if ($step == 4): ?>
            <div class="text-center">
                <h2>Installation terminée !</h2>
                <p>Votre plateforme Quick Quick Shopping est maintenant prête à être utilisée.</p>
                
                <div class="install-actions">
                    <a href="index.php" class="btn btn-primary btn-large">
                        <i class="fas fa-home"></i>
                        Aller au site
                    </a>
                    <a href="login.php" class="btn btn-outline btn-large">
                        <i class="fas fa-sign-in-alt"></i>
                        Se connecter
                    </a>
                </div>
                
                <div class="install-info">
                    <h3>Informations importantes :</h3>
                    <ul style="text-align: left; max-width: 500px; margin: 0 auto;">
                        <li>Supprimez le fichier <code>install.php</code> pour des raisons de sécurité</li>
                        <li>Changez le mot de passe administrateur par défaut</li>
                        <li>Configurez les paramètres de production</li>
                        <li>Créez une sauvegarde de la base de données</li>
                    </ul>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

