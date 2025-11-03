/**
 * Quick Quick Shopping - JavaScript principal
 * Fonctionnalités interactives et animations
 */

document.addEventListener('DOMContentLoaded', function() {
    // ==================== NAVIGATION MOBILE ====================
    
    const hamburger = document.querySelector('.hamburger');
    const navMenu = document.querySelector('.nav-menu');
    
    if (hamburger && navMenu) {
        hamburger.addEventListener('click', function() {
            navMenu.classList.toggle('active');
            hamburger.classList.toggle('active');
        });
    }
    
    // ==================== ANIMATIONS AU SCROLL ====================
    
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in');
            }
        });
    }, observerOptions);
    
    // Observer les éléments à animer
    document.querySelectorAll('.category-card, .product-card, .auth-card').forEach(el => {
        observer.observe(el);
    });
    
    // ==================== GESTION DES FORMULAIRES ====================
    
    // Validation en temps réel des mots de passe
    const passwordInputs = document.querySelectorAll('input[type="password"]');
    passwordInputs.forEach(input => {
        if (input.name === 'confirm_password') {
            input.addEventListener('input', function() {
                const password = document.querySelector('input[name="password"]');
                if (password && password.value !== this.value) {
                    this.setCustomValidity('Les mots de passe ne correspondent pas');
                } else {
                    this.setCustomValidity('');
                }
            });
        }
    });
    
    // Validation des emails
    const emailInputs = document.querySelectorAll('input[type="email"]');
    emailInputs.forEach(input => {
        input.addEventListener('blur', function() {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (this.value && !emailRegex.test(this.value)) {
                this.setCustomValidity('Veuillez entrer une adresse email valide');
            } else {
                this.setCustomValidity('');
            }
        });
    });
    
    // ==================== GESTION DU PANIER ====================
    
    // Ajouter au panier
    function addToCart(productId, quantity = 1) {
        let cart = JSON.parse(localStorage.getItem('cart') || '[]');
        const existingItem = cart.find(item => item.productId === productId);
        
        if (existingItem) {
            existingItem.quantity += quantity;
        } else {
            cart.push({ productId, quantity });
        }
        
        localStorage.setItem('cart', JSON.stringify(cart));
        updateCartDisplay();
        showNotification('Produit ajouté au panier', 'success');
    }
    
    // Mettre à jour l'affichage du panier
    function updateCartDisplay() {
        const cart = JSON.parse(localStorage.getItem('cart') || '[]');
        const cartCount = cart.reduce((total, item => total + item.quantity, 0);
        const cartIcon = document.querySelector('.cart-count');
        
        if (cartIcon) {
            cartIcon.textContent = cartCount;
            cartIcon.style.display = cartCount > 0 ? 'block' : 'none';
        }
    }
    
    // ==================== NOTIFICATIONS ====================
    
    function showNotification(message, type = 'info', duration = 3000) {
        // Supprimer les notifications existantes
        const existingNotifications = document.querySelectorAll('.notification');
        existingNotifications.forEach(notification => notification.remove());
        
        // Créer la nouvelle notification
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <i class="fas fa-${getNotificationIcon(type)}"></i>
                <span>${message}</span>
                <button class="notification-close">&times;</button>
            </div>
        `;
        
        // Styles de la notification
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${getNotificationColor(type)};
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            z-index: 10000;
            transform: translateX(100%);
            transition: transform 0.3s ease;
            max-width: 400px;
        `;
        
        document.body.appendChild(notification);
        
        // Animation d'entrée
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 100);
        
        // Fermeture automatique
        setTimeout(() => {
            closeNotification(notification);
        }, duration);
        
        // Fermeture manuelle
        notification.querySelector('.notification-close').addEventListener('click', () => {
            closeNotification(notification);
        });
    }
    
    function closeNotification(notification) {
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }
    
    function getNotificationIcon(type) {
        const icons = {
            success: 'check-circle',
            error: 'exclamation-circle',
            warning: 'exclamation-triangle',
            info: 'info-circle'
        };
        return icons[type] || 'info-circle';
    }
    
    function getNotificationColor(type) {
        const colors = {
            success: '#4caf50',
            error: '#f44336',
            warning: '#ff9800',
            info: '#2196f3'
        };
        return colors[type] || '#2196f3';
    }
    
    // ==================== RECHERCHE EN TEMPS RÉEL ====================
    
    const searchInput = document.querySelector('#search-input');
    if (searchInput) {
        let searchTimeout;
        
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            
            if (query.length >= 2) {
                searchTimeout = setTimeout(() => {
                    performSearch(query);
                }, 300);
            } else {
                clearSearchResults();
            }
        });
    }
    
    function performSearch(query) {
        // Simulation de recherche (à remplacer par une vraie requête AJAX)
        const results = document.querySelectorAll('.product-card');
        let found = 0;
        
        results.forEach(card => {
            const productName = card.querySelector('h3').textContent.toLowerCase();
            const productDesc = card.querySelector('.product-info p').textContent.toLowerCase();
            
            if (productName.includes(query.toLowerCase()) || productDesc.includes(query.toLowerCase())) {
                card.style.display = 'block';
                found++;
            } else {
                card.style.display = 'none';
            }
        });
        
        // Afficher un message si aucun résultat
        const container = document.querySelector('.products-grid');
        let noResults = container.querySelector('.no-results');
        
        if (found === 0 && !noResults) {
            noResults = document.createElement('div');
            noResults.className = 'no-results';
            noResults.innerHTML = `
                <div class="text-center p-4">
                    <i class="fas fa-search" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem;"></i>
                    <h3>Aucun produit trouvé</h3>
                    <p>Essayez avec d'autres mots-clés</p>
                </div>
            `;
            container.appendChild(noResults);
        } else if (found > 0 && noResults) {
            noResults.remove();
        }
    }
    
    function clearSearchResults() {
        const results = document.querySelectorAll('.product-card');
        results.forEach(card => {
            card.style.display = 'block';
        });
        
        const noResults = document.querySelector('.no-results');
        if (noResults) {
            noResults.remove();
        }
    }
    
    // ==================== GESTION DES FAVORIS ====================
    
    function toggleFavorite(productId) {
        let favorites = JSON.parse(localStorage.getItem('favorites') || '[]');
        const index = favorites.indexOf(productId);
        
        if (index > -1) {
            favorites.splice(index, 1);
            showNotification('Produit retiré des favoris', 'info');
        } else {
            favorites.push(productId);
            showNotification('Produit ajouté aux favoris', 'success');
        }
        
        localStorage.setItem('favorites', JSON.stringify(favorites));
        updateFavoriteDisplay();
    }
    
    function updateFavoriteDisplay() {
        const favorites = JSON.parse(localStorage.getItem('favorites') || '[]');
        const favoriteButtons = document.querySelectorAll('.favorite-btn');
        
        favoriteButtons.forEach(button => {
            const productId = button.dataset.productId;
            const icon = button.querySelector('i');
            
            if (favorites.includes(productId)) {
                icon.classList.remove('far');
                icon.classList.add('fas');
                button.classList.add('active');
            } else {
                icon.classList.remove('fas');
                icon.classList.add('far');
                button.classList.remove('active');
            }
        });
    }
    
    // ==================== GESTION DES MODALES ====================
    
    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
    }
    
    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    }
    
    // Fermer les modales en cliquant à l'extérieur
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal')) {
            closeModal(e.target.id);
        }
    });
    
    // Fermer les modales avec la touche Échap
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const openModal = document.querySelector('.modal[style*="flex"]');
            if (openModal) {
                closeModal(openModal.id);
            }
        }
    });
    
    // ==================== LAZY LOADING DES IMAGES ====================
    
    const images = document.querySelectorAll('img[data-src]');
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.remove('lazy');
                imageObserver.unobserve(img);
            }
        });
    });
    
    images.forEach(img => imageObserver.observe(img));
    
    // ==================== GESTION DES ONGLETS ====================
    
    function switchTab(tabId, contentId) {
        // Masquer tous les contenus d'onglets
        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.remove('active');
        });
        
        // Désactiver tous les onglets
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        
        // Activer l'onglet et le contenu sélectionnés
        document.getElementById(tabId).classList.add('active');
        document.getElementById(contentId).classList.add('active');
    }
    
    // ==================== CATEGORIES INTERACTIONS ====================
    
    // Animation des cartes de catégories au scroll
    function animateCategoriesOnScroll() {
        const categoryCards = document.querySelectorAll('.category-card');
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry, index) => {
                if (entry.isIntersecting) {
                    setTimeout(() => {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }, index * 100);
                }
            });
        }, { threshold: 0.1 });
        
        categoryCards.forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';
            card.style.transition = 'all 0.6s ease';
            observer.observe(card);
        });
    }
    
    // Effet de parallaxe sur les images de catégories
    function initCategoryParallax() {
        const categoryImages = document.querySelectorAll('.category-image');
        
        window.addEventListener('scroll', () => {
            const scrolled = window.pageYOffset;
            const rate = scrolled * -0.5;
            
            categoryImages.forEach(image => {
                image.style.transform = `translateY(${rate}px)`;
            });
        });
    }
    
    // Effet de hover avancé pour les cartes de catégories
    function initCategoryHoverEffects() {
        const categoryCards = document.querySelectorAll('.category-card');
        
        categoryCards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-12px) scale(1.02)';
                this.style.boxShadow = '0 20px 60px rgba(233, 30, 99, 0.2)';
                
                // Animation de l'icône
                const icon = this.querySelector('.category-overlay i');
                if (icon) {
                    icon.style.transform = 'scale(1.2) rotate(5deg)';
                }
                
                // Animation du badge
                const badge = this.querySelector('.category-badge');
                if (badge) {
                    badge.style.transform = 'scale(1.1)';
                    badge.style.boxShadow = '0 4px 15px rgba(233, 30, 99, 0.4)';
                }
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
                this.style.boxShadow = '0 4px 20px rgba(0,0,0,0.08)';
                
                const icon = this.querySelector('.category-overlay i');
                if (icon) {
                    icon.style.transform = 'scale(1) rotate(0deg)';
                }
                
                const badge = this.querySelector('.category-badge');
                if (badge) {
                    badge.style.transform = 'scale(1)';
                    badge.style.boxShadow = '0 2px 10px rgba(233, 30, 99, 0.3)';
                }
            });
        });
    }
    
    // Effet de particules sur les cartes de catégories
    function initCategoryParticles() {
        const categoryCards = document.querySelectorAll('.category-card');
        
        categoryCards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                createParticles(this);
            });
        });
    }
    
    function createParticles(element) {
        const rect = element.getBoundingClientRect();
        const particleCount = 6;
        
        for (let i = 0; i < particleCount; i++) {
            const particle = document.createElement('div');
            particle.style.cssText = `
                position: absolute;
                width: 4px;
                height: 4px;
                background: var(--primary-color);
                border-radius: 50%;
                pointer-events: none;
                z-index: 1000;
                left: ${rect.left + Math.random() * rect.width}px;
                top: ${rect.top + Math.random() * rect.height}px;
                animation: particleFloat 1s ease-out forwards;
            `;
            
            document.body.appendChild(particle);
            
            setTimeout(() => {
                particle.remove();
            }, 1000);
        }
    }
    
    // Ajouter l'animation CSS pour les particules
    const style = document.createElement('style');
    style.textContent = `
        @keyframes particleFloat {
            0% {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
            100% {
                opacity: 0;
                transform: translateY(-50px) scale(0);
            }
        }
    `;
    document.head.appendChild(style);
    
    // ==================== INITIALISATION ====================
    
    // Initialiser l'affichage du panier
    updateCartDisplay();
    
    // Initialiser l'affichage des favoris
    updateFavoriteDisplay();
    
    // Initialiser les interactions des catégories
    animateCategoriesOnScroll();
    initCategoryParallax();
    initCategoryHoverEffects();
    initCategoryParticles();
    
    // Ajouter les événements pour les boutons d'action
    document.addEventListener('click', function(e) {
        // Bouton d'ajout au panier
        if (e.target.classList.contains('add-to-cart') || e.target.closest('.add-to-cart')) {
            e.preventDefault();
            const button = e.target.closest('.add-to-cart');
            const productId = button.dataset.productId;
            const quantity = parseInt(button.dataset.quantity) || 1;
            addToCart(productId, quantity);
        }
        
        // Bouton de favori
        if (e.target.classList.contains('favorite-btn') || e.target.closest('.favorite-btn')) {
            e.preventDefault();
            const button = e.target.closest('.favorite-btn');
            const productId = button.dataset.productId;
            toggleFavorite(productId);
        }
        
        // Boutons de fermeture de modale
        if (e.target.classList.contains('modal-close')) {
            const modal = e.target.closest('.modal');
            if (modal) {
                closeModal(modal.id);
            }
        }
        
        // Boutons d'onglets
        if (e.target.classList.contains('tab-btn')) {
            e.preventDefault();
            const tabId = e.target.id;
            const contentId = e.target.dataset.contentId;
            switchTab(tabId, contentId);
        }
    });
    
    // ==================== GESTION DES ERREURS ====================
    
    window.addEventListener('error', function(e) {
        console.error('Erreur JavaScript:', e.error);
        showNotification('Une erreur est survenue', 'error');
    });
    
    // ==================== PERFORMANCE ====================
    
    // Délai pour les animations lourdes
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
    
    // Optimiser le scroll
    let ticking = false;
    function updateOnScroll() {
        // Logique de mise à jour au scroll
        ticking = false;
    }
    
    window.addEventListener('scroll', function() {
        if (!ticking) {
            requestAnimationFrame(updateOnScroll);
            ticking = true;
        }
    });
});

// ==================== FONCTIONS GLOBALES ====================

// Fonction pour basculer l'affichage des mots de passe
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const button = input.nextElementSibling;
    const icon = button.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Fonction pour formater les prix
function formatPrice(price) {
    return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'XOF',
        minimumFractionDigits: 0
    }).format(price);
}

// Fonction pour valider les formulaires
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;
    
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.classList.add('error');
            isValid = false;
        } else {
            field.classList.remove('error');
        }
    });
    
    return isValid;
}

// Fonction pour afficher les messages de chargement
function showLoading(element, text = 'Chargement...') {
    const originalContent = element.innerHTML;
    element.innerHTML = `
        <div class="loading">
            <i class="fas fa-spinner fa-spin"></i>
            ${text}
        </div>
    `;
    element.disabled = true;
    
    return function hideLoading() {
        element.innerHTML = originalContent;
        element.disabled = false;
    };
}

