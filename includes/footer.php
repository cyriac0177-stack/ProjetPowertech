<footer class="bg-[#b06393] text-white mt-12">
    <div class="container mx-auto px-4 py-12">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <!-- À propos -->
            <div>
                <h3 class="text-xl font-bold mb-4">powertech+</h3>
                <p class="text-gray-200 text-sm">
                    Votre destination pour les produits de qualité. 
                    Shopping en ligne rapide et sécurisé.
                </p>
            </div>
            
            <!-- Liens rapides -->
            <div>
                <h3 class="text-lg font-semibold mb-4">Liens Rapides</h3>
                <ul class="space-y-2 text-sm">
                    <li><a href="index.php" class="text-gray-200 hover:text-white transition">Accueil</a></li>
                    <li><a href="products_simple.php" class="text-gray-200 hover:text-white transition">Boutique</a></li>
                    <li><a href="about.php" class="text-gray-200 hover:text-white transition">À propos</a></li>
                    <li><a href="contact.php" class="text-gray-200 hover:text-white transition">Contact</a></li>
                </ul>
            </div>
            
            <!-- Compte -->
            <div>
                <h3 class="text-lg font-semibold mb-4">Mon Compte</h3>
                <ul class="space-y-2 text-sm">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li><a href="profile.php" class="text-gray-200 hover:text-white transition">Mon Profil</a></li>
                        <li><a href="orders.php" class="text-gray-200 hover:text-white transition">Mes Commandes</a></li>
                        <li><a href="wishlist.php" class="text-gray-200 hover:text-white transition">Ma Wishlist</a></li>
                        <li><a href="recently_viewed.php" class="text-gray-200 hover:text-white transition">Produits Récents</a></li>
                    <?php else: ?>
                        <li><a href="login.php" class="text-gray-200 hover:text-white transition">Connexion</a></li>
                        <li><a href="register.php" class="text-gray-200 hover:text-white transition">Inscription</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            
            <!-- Contact -->
            <div>
                <h3 class="text-lg font-semibold mb-4">Suivez-nous</h3>
                <div class="flex space-x-4 mb-4">
                    <a href="#" class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center hover:bg-white/30 transition">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center hover:bg-white/30 transition">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="#" class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center hover:bg-white/30 transition">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="#" class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center hover:bg-white/30 transition">
                        <i class="fab fa-whatsapp"></i>
                    </a>
                </div>
                <p class="text-sm text-gray-200">
                    <i class="fas fa-envelope mr-2"></i>contact@quickquickshopping.com
                </p>
                <p class="text-sm text-gray-200">
                    <i class="fas fa-phone mr-2"></i>+225 07 XX XX XX XX
                </p>
            </div>
        </div>
        
        <div class="border-t border-white/20 mt-8 pt-8 text-center text-sm text-gray-200">
            <p>&copy; 2025 powertech+ Tous droits réservés.</p>
        </div>
    </div>
</footer>

