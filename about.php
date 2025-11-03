<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>À propos - Quick Quick Shopping</title>
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
                    <a href="about.php" class="nav-link active">À propos</a>
                    <a href="contact.php" class="nav-link">Contact</a>
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
                <h1>À propos de Quick Quick Shopping</h1>
                <p>Votre plateforme de vente en ligne spécialisée dans les articles féminins</p>
            </div>

            <!-- About Content -->
            <div class="about-content">
                <section class="about-section">
                    <div class="about-text">
                        <h2>Notre Mission</h2>
                        <p>Quick Quick Shopping est une plateforme innovante dédiée à la vente en ligne d'articles féminins. Nous connectons les vendeurs (entreprises, boutiques, particuliers) avec des clients à la recherche de produits de qualité.</p>
                        
                        <p>Notre objectif est de faciliter la vente et l'achat d'articles féminins tout en offrant un système de commissions dégressif qui récompense les vendeurs les plus performants.</p>
                    </div>
                    <div class="about-image">
                        <img src="assets/images/about-mission.jpg" alt="Notre mission">
                    </div>
                </section>

                <section class="about-section">
                    <div class="about-image">
                        <img src="assets/images/about-features.jpg" alt="Nos fonctionnalités">
                    </div>
                    <div class="about-text">
                        <h2>Nos Fonctionnalités</h2>
                        <ul class="features-list">
                            <li><i class="fas fa-check-circle"></i> <strong>Vente facile :</strong> Les vendeurs peuvent ajouter leurs produits en quelques clics</li>
                            <li><i class="fas fa-check-circle"></i> <strong>Commissions dégressives :</strong> Plus vous vendez, moins vous payez de commission</li>
                            <li><i class="fas fa-check-circle"></i> <strong>Paiements sécurisés :</strong> Intégration avec les opérateurs financiers locaux</li>
                            <li><i class="fas fa-check-circle"></i> <strong>Suivi des ventes :</strong> Tableaux de bord détaillés pour vendeurs et administrateurs</li>
                            <li><i class="fas fa-check-circle"></i> <strong>Mobile-first :</strong> Interface optimisée pour tous les appareils</li>
                        </ul>
                    </div>
                </section>

                <section class="commission-section">
                    <h2>Système de Commissions Dégressif</h2>
                    <div class="commission-grid">
                        <div class="commission-tier">
                            <div class="tier-header">
                                <h3>Débutant</h3>
                                <span class="tier-range">0 - 250 000 F</span>
                            </div>
                            <div class="tier-rate">1%</div>
                            <p>Commission pour les nouveaux vendeurs</p>
                        </div>
                        
                        <div class="commission-tier">
                            <div class="tier-header">
                                <h3>Intermédiaire</h3>
                                <span class="tier-range">250 001 - 500 000 F</span>
                            </div>
                            <div class="tier-rate">0.75%</div>
                            <p>Commission réduite pour les vendeurs actifs</p>
                        </div>
                        
                        <div class="commission-tier">
                            <div class="tier-header">
                                <h3>Avancé</h3>
                                <span class="tier-range">500 001 - 1 000 000 F</span>
                            </div>
                            <div class="tier-rate">0.5%</div>
                            <p>Commission préférentielle pour les gros vendeurs</p>
                        </div>
                        
                        <div class="commission-tier">
                            <div class="tier-header">
                                <h3>Expert</h3>
                                <span class="tier-range">+ 1 000 000 F</span>
                            </div>
                            <div class="tier-rate">0.25%</div>
                            <p>Commission minimale pour les experts</p>
                        </div>
                    </div>
                </section>

                <section class="values-section">
                    <h2>Nos Valeurs</h2>
                    <div class="values-grid">
                        <div class="value-item">
                            <div class="value-icon">
                                <i class="fas fa-heart"></i>
                            </div>
                            <h3>Féminin</h3>
                            <p>Nous nous spécialisons dans les articles qui mettent en valeur la féminité et l'élégance.</p>
                        </div>
                        
                        <div class="value-item">
                            <div class="value-icon">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <h3>Sécurité</h3>
                            <p>Transactions sécurisées et protection des données personnelles de nos utilisateurs.</p>
                        </div>
                        
                        <div class="value-item">
                            <div class="value-icon">
                                <i class="fas fa-handshake"></i>
                            </div>
                            <h3>Confiance</h3>
                            <p>Relations de confiance entre vendeurs et clients grâce à notre système de validation.</p>
                        </div>
                        
                        <div class="value-item">
                            <div class="value-icon">
                                <i class="fas fa-rocket"></i>
                            </div>
                            <h3>Innovation</h3>
                            <p>Technologies modernes pour une expérience utilisateur fluide et intuitive.</p>
                        </div>
                    </div>
                </section>

                <section class="team-section">
                    <h2>Notre Équipe</h2>
                    <p>Une équipe passionnée et dédiée à votre succès. Nous travaillons sans relâche pour améliorer votre expérience sur notre plateforme.</p>
                    
                    <div class="cta-section">
                        <h3>Rejoignez-nous !</h3>
                        <p>Que vous soyez vendeur ou client, Quick Quick Shopping vous offre les outils pour réussir.</p>
                        <div class="cta-buttons">
                            <a href="register.php?type=seller" class="btn btn-primary btn-large">
                                <i class="fas fa-store"></i>
                                Devenir vendeur
                            </a>
                            <a href="register.php?type=customer" class="btn btn-outline btn-large">
                                <i class="fas fa-shopping-cart"></i>
                                Devenir client
                            </a>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="assets/js/script.js"></script>
</body>
</html>

