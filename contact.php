<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

$message = null;
$message_type = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message_text = trim($_POST['message'] ?? '');
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    // Vérification CSRF
    if (!verifyCSRFToken($csrf_token)) {
        $message = "Token de sécurité invalide.";
        $message_type = "error";
    } elseif (empty($name) || empty($email) || empty($subject) || empty($message_text)) {
        $message = "Veuillez remplir tous les champs.";
        $message_type = "error";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "L'adresse email n'est pas valide.";
        $message_type = "error";
    } else {
        // Ici, vous pourriez envoyer un email ou sauvegarder en base de données
        // Pour l'instant, on simule l'envoi
        $message = "Votre message a été envoyé avec succès ! Nous vous répondrons dans les plus brefs délais.";
        $message_type = "success";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact - Quick Quick Shopping</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <nav class="navbar">
            <div class="nav-container">
                <div class="nav-logo">
                    <a href="index.php">
                        <i class="fas fa-shopping-bag"></i>
                        Quick Quick Shopping
                    </a>
                </div>
                
                <div class="nav-menu">
                    <a href="index.php" class="nav-link">Accueil</a>
                    <a href="products.php" class="nav-link">Produits</a>
                    <a href="about.php" class="nav-link">À propos</a>
                    <a href="contact.php" class="nav-link active">Contact</a>
                </div>
                
                <div class="nav-actions">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="logout.php" class="btn btn-outline">Déconnexion</a>
                        <a href="<?php echo $_SESSION['user_role']; ?>/dashboard.php" class="btn btn-primary">Mon compte</a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-outline">Connexion</a>
                        <a href="register.php" class="btn btn-primary">S'inscrire</a>
                    <?php endif; ?>
                </div>
                
                <div class="hamburger">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <!-- Page Header -->
            <div class="page-header">
                <h1>Contactez-nous</h1>
                <p>Nous sommes là pour vous aider</p>
            </div>

            <div class="contact-content">
                <!-- Contact Info -->
                <div class="contact-info">
                    <h2>Informations de contact</h2>
                    <div class="contact-grid">
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div class="contact-details">
                                <h3>Adresse</h3>
                                <p>Abidjan, Côte d'Ivoire<br>Plateau, Boulevard de la République</p>
                            </div>
                        </div>
                        
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div class="contact-details">
                                <h3>Téléphone</h3>
                                <p>+225 XX XX XX XX XX</p>
                            </div>
                        </div>
                        
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="contact-details">
                                <h3>Email</h3>
                                <p>contact@quickquickshopping.com</p>
                            </div>
                        </div>
                        
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="contact-details">
                                <h3>Horaires</h3>
                                <p>Lundi - Vendredi: 8h00 - 18h00<br>Samedi: 9h00 - 16h00</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Form -->
                <div class="contact-form-section">
                    <h2>Envoyez-nous un message</h2>
                    
                    <?php if ($message): ?>
                        <div class="alert alert-<?php echo $message_type; ?>">
                            <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                            <?php echo escape($message); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" class="contact-form">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="name">Nom complet *</label>
                                <input type="text" id="name" name="name" required 
                                       value="<?php echo escape($_POST['name'] ?? ''); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email *</label>
                                <input type="email" id="email" name="email" required 
                                       value="<?php echo escape($_POST['email'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="subject">Sujet *</label>
                            <input type="text" id="subject" name="subject" required 
                                   value="<?php echo escape($_POST['subject'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="message">Message *</label>
                            <textarea id="message" name="message" rows="6" required 
                                      placeholder="Décrivez votre demande ou votre question..."><?php echo escape($_POST['message'] ?? ''); ?></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-large">
                            <i class="fas fa-paper-plane"></i>
                            Envoyer le message
                        </button>
                    </form>
                </div>
            </div>

            <!-- FAQ Section -->
            <section class="faq-section">
                <h2>Questions fréquentes</h2>
                <div class="faq-grid">
                    <div class="faq-item">
                        <h3>Comment devenir vendeur ?</h3>
                        <p>Inscrivez-vous en tant que vendeur, remplissez votre profil et ajoutez vos produits. Votre compte sera validé par notre équipe sous 24h.</p>
                    </div>
                    
                    <div class="faq-item">
                        <h3>Quels sont les frais de commission ?</h3>
                        <p>Nos commissions sont dégressives : 1% jusqu'à 250 000 F, 0.75% jusqu'à 500 000 F, 0.5% jusqu'à 1 000 000 F, et 0.25% au-delà.</p>
                    </div>
                    
                    <div class="faq-item">
                        <h3>Comment fonctionnent les paiements ?</h3>
                        <p>Nous intégrons les opérateurs financiers locaux (Orange Money, Moov Money, Wave) pour des transactions sécurisées.</p>
                    </div>
                    
                    <div class="faq-item">
                        <h3>Puis-je annuler une commande ?</h3>
                        <p>Oui, vous pouvez annuler votre commande tant qu'elle n'a pas été expédiée. Contactez-nous pour toute assistance.</p>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="assets/js/script.js"></script>
</body>
</html>

