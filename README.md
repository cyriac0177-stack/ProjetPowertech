# Quick Quick Shopping

## Description
Quick Quick Shopping est une plateforme de vente en ligne spécialisée dans les articles féminins (vêtements, accessoires, produits de beauté, etc.). Elle permet aux vendeurs (entreprises, boutiques, particuliers) de vendre leurs produits facilement avec un système de commissions dégressif selon le volume de ventes.

## Fonctionnalités principales

### 🛍️ Pour les clients
- Inscription et connexion
- Navigation par catégories
- Recherche et filtrage de produits
- Panier d'achat
- Historique des commandes
- Paiements sécurisés

### 🏪 Pour les vendeurs
- Inscription et validation
- Tableau de bord avec statistiques
- Gestion des produits (ajout, modification, suppression)
- Suivi des ventes et commissions
- Notifications de vente
- Exigence minimale : 7 articles à la première vente

### 👨‍💼 Pour les administrateurs
- Gestion des utilisateurs (clients et vendeurs)
- Validation des vendeurs et produits
- Gestion des catégories
- Suivi des commissions
- Rapports exportables

## Système de commissions dégressif

| Volume de ventes | Taux de commission |
|------------------|-------------------|
| 0 - 250 000 F    | 1%                |
| 250 001 - 500 000 F | 0.75%          |
| 500 001 - 1 000 000 F | 0.5%         |
| + 1 000 000 F     | 0.25%             |

## Technologies utilisées

- **Backend :** PHP 7.4+
- **Frontend :** HTML5, CSS3, JavaScript (ES6)
- **Base de données :** MySQL 5.7+
- **Serveur :** Apache (XAMPP)
- **Design :** CSS Grid, Flexbox, Responsive Design

## Installation

### Prérequis
- XAMPP installé et configuré
- PHP 7.4 ou supérieur
- MySQL 5.7 ou supérieur
- Navigateur web moderne

### Étapes d'installation

1. **Télécharger le projet**
   ```bash
   # Cloner ou télécharger le projet dans le dossier htdocs de XAMPP
   # Chemin : C:\xampp\htdocs\quick-quick-shopping (Windows)
   # Chemin : /Applications/XAMPP/xamppfiles/htdocs/quick-quick-shopping (macOS)
   ```

2. **Démarrer XAMPP**
   - Lancer XAMPP Control Panel
   - Démarrer Apache et MySQL

3. **Créer la base de données**
   - Ouvrir phpMyAdmin (http://localhost/phpmyadmin)
   - Créer une nouvelle base de données nommée `quick_quick_shopping`
   - Importer le fichier `database/schema.sql`

4. **Configurer la base de données**
   - Ouvrir `config/database.php`
   - Vérifier les paramètres de connexion :
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'quick_quick_shopping');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   ```

5. **Créer les dossiers nécessaires**
   ```bash
   mkdir assets/images/products
   mkdir assets/images/uploads
   chmod 755 assets/images/products
   chmod 755 assets/images/uploads
   ```

6. **Accéder au site**
   - Ouvrir http://localhost/quick-quick-shopping
   - Le site devrait être accessible

## Structure du projet

```
quick-quick-shopping/
├── assets/
│   ├── css/
│   │   └── style.css          # Styles principaux
│   ├── js/
│   │   └── script.js          # JavaScript principal
│   └── images/                # Images du site
├── classes/
│   ├── User.php              # Gestion des utilisateurs
│   ├── Product.php          # Gestion des produits
│   ├── Order.php             # Gestion des commandes
│   └── Commission.php        # Gestion des commissions
├── config/
│   └── database.php          # Configuration BDD
├── includes/
│   └── functions.php         # Fonctions utilitaires
├── database/
│   └── schema.sql            # Schéma de la base de données
├── index.php                 # Page d'accueil
├── login.php                 # Connexion
├── register.php              # Inscription
├── products.php              # Liste des produits
├── product.php               # Détail d'un produit
├── about.php                 # À propos
├── contact.php               # Contact
└── README.md                 # Documentation
```

## Configuration

### Base de données
Les paramètres de connexion à la base de données se trouvent dans `config/database.php` :

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'quick_quick_shopping');
define('DB_USER', 'root');
define('DB_PASS', '');
```

### Images
- Placez les images des produits dans `assets/images/products/`
- Les images doivent être au format JPG, PNG ou WebP
- Taille recommandée : 800x600 pixels minimum

## Utilisation

### Compte administrateur par défaut
- **Email :** admin@quickquickshopping.com
- **Mot de passe :** password (à changer en production)

### Première utilisation
1. Connectez-vous avec le compte administrateur
2. Validez les vendeurs en attente
3. Approuvez les produits soumis
4. Configurez les catégories si nécessaire

### Ajout de produits
1. Inscrivez-vous comme vendeur
2. Attendez la validation de votre compte
3. Ajoutez vos produits via le tableau de bord
4. Vos produits seront validés par un administrateur

## Sécurité

### Mesures implémentées
- Protection CSRF sur tous les formulaires
- Validation et échappement des données
- Hachage sécurisé des mots de passe
- Sessions sécurisées
- Validation des entrées utilisateur

### Recommandations pour la production
- Changer le mot de passe administrateur par défaut
- Configurer HTTPS
- Mettre à jour les paramètres de session
- Activer les logs d'erreur
- Configurer un pare-feu

## Personnalisation

### Couleurs
Les couleurs principales sont définies dans `assets/css/style.css` :
```css
:root {
    --primary-color: #e91e63;      /* Rose principal */
    --primary-light: #f8bbd9;      /* Rose clair */
    --primary-dark: #ad1457;       /* Rose foncé */
    --secondary-color: #fce4ec;    /* Rose secondaire */
}
```

### Logo et branding
- Remplacez les images dans `assets/images/`
- Modifiez les textes dans les fichiers PHP
- Personnalisez les couleurs dans le CSS

## Développement

### Ajout de nouvelles fonctionnalités
1. Créez les classes PHP dans `classes/`
2. Ajoutez les fonctions utilitaires dans `includes/functions.php`
3. Créez les pages dans le répertoire racine
4. Mettez à jour la base de données si nécessaire

### Base de données
- Utilisez les migrations pour les modifications de schéma
- Sauvegardez régulièrement la base de données
- Testez les modifications en local avant la production

## Support

### Problèmes courants

**Erreur de connexion à la base de données**
- Vérifiez que MySQL est démarré
- Vérifiez les paramètres dans `config/database.php`

**Images ne s'affichent pas**
- Vérifiez les permissions des dossiers
- Vérifiez les chemins dans le code

**Erreur 500**
- Activez l'affichage des erreurs PHP
- Vérifiez les logs d'erreur Apache

### Contact
Pour toute question ou problème :
- Email : support@quickquickshopping.com
- Documentation : Consultez ce README
- Issues : Utilisez le système de tickets

## Licence

Ce projet est sous licence MIT. Voir le fichier LICENSE pour plus de détails.

## Changelog

### Version 1.0.0
- Version initiale
- Système d'authentification
- Gestion des produits
- Système de commissions
- Interface responsive
- Tableaux de bord vendeur/admin

## Roadmap

### Version 1.1.0
- [ ] Système de notifications en temps réel
- [ ] API REST pour mobile
- [ ] Intégration paiements mobiles
- [ ] Système d'avis et notes

### Version 1.2.0
- [ ] Chat en direct
- [ ] Système de coupons
- [ ] Analytics avancées
- [ ] Export PDF des rapports

---

**Quick Quick Shopping** - Votre plateforme de vente en ligne pour articles féminins

