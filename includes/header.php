<?php
if (!isset($_SESSION)) {
    session_start();
}
?>
<header class="bg-white shadow-md sticky top-0 z-50">
    <div class="container mx-auto px-4 py-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="index.php" class="flex items-center">
                    <img src="images/logo.png" alt="Logo" class="h-12">
                </a>
            </div>

            <!-- Barre de recherche -->
            <div class="hidden md:flex items-center flex-1 max-w-lg mx-8">
                <div class="relative w-full search-container">
                    <input type="text" id="searchInput" placeholder="Rechercher un produit..." 
                           class="w-full px-4 py-2 rounded-full border border-gray-300 focus:outline-none focus:border-[#b06393]">
                    <button class="absolute right-2 top-1/2 transform -translate-y-1/2 text-[#b06393]">
                        <i class="fas fa-search"></i>
                    </button>
                    <div id="searchResults" class="absolute top-full left-0 right-0 mt-2 bg-white rounded-lg shadow-lg z-50 hidden max-h-96 overflow-y-auto"></div>
                </div>
            </div>

            <div class="flex items-center space-x-4">
                <!-- Panier -->
                <a href="cart.php" class="relative inline-flex items-center bg-orrose text-blanc px-4 py-2 rounded-full text-sm font-medium shadow hover:bg-nude hover:text-noir transition">
                    <i class="fas fa-shopping-cart mr-2 text-lg"></i>Panier
                    <?php if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                        <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
                            <?= count($_SESSION['cart']) ?>
                        </span>
                    <?php endif; ?>
                </a>

                <!-- Wishlist -->
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="wishlist.php" class="relative inline-flex items-center text-gray-700 hover:text-[#b06393] transition">
                        <i class="fas fa-heart text-2xl"></i>
                    </a>
                <?php endif; ?>

                <!-- Menu profil -->
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="relative group flex items-center">
                        <button id="profileBtn" class="flex items-center space-x-2 px-4 py-2 rounded-full bg-white transition-all hover:shadow-md">
                            <div class="w-10 h-10 rounded-full bg-[#b06393] text-white flex items-center justify-center font-semibold text-lg">
                                <?= strtoupper(substr($_SESSION['nom'], 0, 1)) ?>
                            </div>
                            <span class="text-sm font-medium text-gray-700 hidden md:block"><?= htmlspecialchars($_SESSION['nom']) ?></span>
                            <i class="fas fa-chevron-down text-gray-600"></i>
                        </button>
                        
                        <!-- Dropdown menu -->
                        <div id="profileMenu" class="absolute right-0 top-full mt-2 w-56 bg-white rounded-lg shadow-xl z-50" style="display: none;">
                            <div class="p-4 border-b">
                                <p class="font-semibold text-gray-800"><?= htmlspecialchars($_SESSION['nom']) ?></p>
                                <p class="text-sm text-gray-600"><?= ucfirst($_SESSION['role']) ?></p>
                            </div>
                            <div class="py-2">
                                <a href="profile.php" class="block px-4 py-2 hover:bg-gray-100 transition text-gray-700">
                                    <i class="fas fa-user mr-2"></i>Mon Profil
                                </a>
                                <a href="orders.php" class="block px-4 py-2 hover:bg-gray-100 transition text-gray-700">
                                    <i class="fas fa-shopping-bag mr-2"></i>Mes Commandes
                                </a>
                                <a href="wishlist.php" class="block px-4 py-2 hover:bg-gray-100 transition text-gray-700">
                                    <i class="fas fa-heart mr-2"></i>Ma Wishlist
                                </a>
                                <a href="recently_viewed.php" class="block px-4 py-2 hover:bg-gray-100 transition text-gray-700">
                                    <i class="fas fa-history mr-2"></i>Récents
                                </a>
                                <a href="logout.php" class="block px-4 py-2 hover:bg-gray-100 transition text-red-600">
                                    <i class="fas fa-sign-out-alt mr-2"></i>Déconnexion
                                </a>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="px-4 py-2 bg-[#b06393] text-white rounded-full hover:bg-[#d87eb6] transition">
                        Connexion
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const profileBtn = document.getElementById('profileBtn');
    const profileMenu = document.getElementById('profileMenu');
    
    if (profileBtn && profileMenu) {
        profileBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            if (profileMenu.style.display === 'none' || profileMenu.style.display === '') {
                profileMenu.style.display = 'block';
            } else {
                profileMenu.style.display = 'none';
            }
        });
        
        document.addEventListener('click', function(e) {
            if (!profileBtn.contains(e.target) && !profileMenu.contains(e.target)) {
                profileMenu.style.display = 'none';
            }
        });
    }
    
    // Recherche AJAX
    const searchInput = document.getElementById('searchInput');
    const searchResults = document.getElementById('searchResults');
    let searchTimeout;
    
    if (searchInput && searchResults) {
        searchInput.addEventListener('input', function(e) {
            const query = e.target.value.trim();
            clearTimeout(searchTimeout);
            
            if (query.length < 2) {
                searchResults.classList.add('hidden');
                return;
            }
            
            searchTimeout = setTimeout(() => {
                fetch(`api/search.php?q=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => {
                        displaySearchResults(data.products);
                    })
                    .catch(error => console.error('Erreur recherche:', error));
            }, 300);
        });
        
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.search-container')) {
                searchResults.classList.add('hidden');
            }
        });
    }
    
    function displaySearchResults(products) {
        if (products.length === 0) {
            searchResults.innerHTML = '<div class="p-4 text-center text-gray-500">Aucun résultat</div>';
        } else {
            let html = '<div class="p-2">';
            products.forEach(product => {
                html += `
                    <a href="product.php?id=${product.produit_id}" class="flex items-center p-3 hover:bg-gray-100 rounded transition">
                        <img src="${product.image_url || 'images/no-image.png'}" alt="${product.nom}" class="w-16 h-16 object-cover rounded mr-3">
                        <div class="flex-1">
                            <p class="font-semibold text-gray-800">${product.nom}</p>
                            <p class="text-sm text-gray-600">${product.categorie_nom || 'Sans catégorie'}</p>
                            <p class="text-[#b06393] font-bold">${product.prix.toLocaleString('fr-FR')} FCFA</p>
                        </div>
                    </a>
                `;
            });
            html += '</div>';
            searchResults.innerHTML = html;
        }
        searchResults.classList.remove('hidden');
    }
});
</script>

