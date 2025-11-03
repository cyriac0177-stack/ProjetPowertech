<?php
include 'config/db.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=EB+Garamond:ital,wght@0,400..800;1,400..800&family=Great+Vibes&family=Lora:ital,wght@0,400..700;1,400..700&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Noto+Serif+Display:ital,wght@0,100..900;1,100..900&family=PT+Serif:ital,wght@0,400;0,700;1,400;1,700&family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Quicksand:wght@300..700&display=swap" rel="stylesheet">
    <title>Accueil-Quick Quick Shopping</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Abril+Fatface&family=Cormorant:ital,wght@0,300..700;1,300..700&family=DM+Serif+Display:ital@0;1&family=EB+Garamond:ital,wght@0,400..800;1,400..800&family=Great+Vibes&family=Lora:ital,wght@0,400..700;1,400..700&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Noto+Serif+Display:ital,wght@0,100..900;1,100..900&family=PT+Serif:ital,wght@0,400;0,700;1,400;1,700&family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Quicksand:wght@300..700&display=swap');

        .font-playfair {
            font-family: 'Playfair Display', serif;

        }
    </style>

</head>

<body>
    <section class="relative  bg-cover bg-center h-[80vh] pt-[100px]" style="background-image: url('images/arriere_plan.png');">

        <header x-data="{ scrolled: false }"
            x-init="window.addEventListener('scroll', () => scrolled = window.scrollY > 50)"
            :class="scrolled ? 'border-black' : 'border-white'"
            class="fixed top-0 left-0 w-full z-50 backdrop-blur-md border-b border-white font-serif font-bold leading-tight text-xl">
            <div class="flex items-center justify-between px-8 py-6 text-black">

                <div class="text-3xl font-serif font-semibold tracking-wide text-orrose font-great ">
                    <img src="images/logo.png" alt="" class="h-[10vh]">
                </div>

                <!-- Barre de recherche avec résultats en temps réel -->
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

                <nav class="hidden md:flex space-x-4 text-sm font-light tracking-wider items-center">
                    <a href="index.php" class="px-3 py-1 rounded-full transition-all duration-300 hover:px-5 hover:py-2 hover:bg-white ">Accueil</a>
                    <a href="products_simple.php" class="px-3 py-1 rounded-full transition-all duration-300 hover:px-5 hover:py-2 hover:bg-white ">Boutique</a>
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <a href="login.php" class="px-5 py-2 rounded-full transition-all duration-300 bg-white">Connexion/Inscription</a>
                    <?php endif; ?> 
                    
                    <a href="cart.php" class="bg-orrose text-blanc px-4 py-2 rounded-full text-sm font-medium shadow hover:bg-nude hover:text-noir transition relative inline-flex items-center">
                        <i class="fas fa-shopping-cart mr-2"></i>Panier
                    </a>
                    
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <!-- Menu profil avec avatar -->
                        <div class="relative group flex items-center">
                            <button id="profileBtnIndex" class="flex items-center space-x-2 px-4 py-2 rounded-full bg-white transition-all hover:shadow-md">
                                <div class="w-10 h-10 rounded-full bg-[#b06393] text-white flex items-center justify-center font-semibold text-lg">
                                    <?= strtoupper(substr($_SESSION['nom'], 0, 1)) ?>
                                </div>
                                <span class="text-sm font-medium text-gray-700 hidden md:block"><?= htmlspecialchars($_SESSION['nom']) ?></span>
                                <i class="fas fa-chevron-down text-gray-600"></i>
                            </button>
                            
                            <!-- Dropdown menu -->
                            <div id="profileMenuIndex" class="absolute right-0 top-full mt-2 w-56 bg-white rounded-lg shadow-xl z-50" style="display: none;">
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
                                    <a href="logout.php" class="block px-4 py-2 hover:bg-gray-100 transition text-red-600">
                                        <i class="fas fa-sign-out-alt mr-2"></i>Déconnexion
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </nav>

                <!-- Mobile -->
                <button class="md:hidden text-orrose ml-auto">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>
        </header>
        <div class="h-[40px]"></div>

        <section class="relative h-[60vh] bg-cover bg-center px-8 py-12" style="background-image: url('/images/hero.jpg');">
            <div class="flex flex-col md:flex-row items-start justify-between">


                <div class="text-white space-y-6 md:w-1/2 ">
                    <h1 class="text-5xl font-serif font-bold leading-tight">
                        Sublimez votre quotidien<br>
                        <span class="text-black">avec élégance</span>
                    </h1>
                    <p class="text-lg font-light max-w-md ">
                        Une boutique pensée pour les femmes modernes, raffinées et audacieuses.
                    </p>
                    <br>
                    <a href="products.php" class="bg-[#fdbbe3] text-blanc px-6 py-3 rounded-full text-sm font-semibold shadow hover:bg-nude hover:bg-white hover:text-black transition mt-4">
                        Découvrir la collection
                    </a>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mt-12 w-full">
                    <!-- Produit 1 -->
                    <div class="bg-white rounded-lg shadow-lg p-4 text-center">
                        <img src="images/1.jpg" alt="Robe satinée" class="rounded-lg mb-4 h-48 w-full object-cover">
                        <h3 class="text-orrose font-semibold text-sm">Sandales</h3>
                        <p class="text-noir font-bold text-sm">29 000 FCFA</p>
                    </div>

                    <!-- Produit 2 -->
                    <div class="bg-white rounded-lg shadow-lg p-4 text-center">
                        <img src="images/2.jpg" alt="Sac pastel" class="rounded-lg mb-4 h-48 w-full object-cover">
                        <h3 class="text-orrose font-semibold text-sm">Sac </h3>
                        <p class="text-noir font-bold text-sm">18 500 FCFA</p>
                    </div>

                    <!-- Produit 3 -->
                    <div class="bg-white rounded-lg shadow-lg p-4 text-center">
                        <img src="images/3.jpg" alt="Sandales rhinestones" class="rounded-lg mb-4 h-48 w-full object-cover">
                        <h3 class="text-orrose font-semibold text-sm">Pochette </h3>
                        <p class="text-noir font-bold text-sm">22 000 FCFA</p>
                    </div>

                    <!-- Produit 4 -->
                    <div class="bg-white rounded-lg shadow-lg p-4 text-center">
                        <img src="images/4.jpg" alt="Blazer oversize" class="rounded-lg mb-4 h-48 w-full object-cover">
                        <h3 class="text-orrose font-semibold text-sm">Palette de maquillage</h3>
                        <p class="text-noir font-bold text-sm">35 000 FCFA</p>
                    </div>
                </div>
            </div>
        </section>
        <section class="py-16 px-6 bg-white text-center mt-0">
            <h2 class="text-3xl font-serif font-playfair mb-10 underline">Explorez nos catégories</h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto">

                <div class="bg-[#fdf1f7] rounded-lg shadow-lg overflow-hidden hover:scale-105 transition">
                    <img src="images/5.jpg" alt="Mode" class="h-64 w-full object-cover object-top">
                    <div class="p-6">
                        <h3 class="text-xl font-semibold text-orrose mb-2">Mode</h3>
                        <p class="text-sm text-gray-600 mb-4">Robes, blazers, ensembles élégants</p>
                        <a href="products_simple.php" class="bg-white text-black px-4 py-2 rounded-full text-sm font-medium hover:bg-[#fdbbe3] transition">
                            Voir la catégorie
                        </a>
                    </div>
                </div>

                <div class="bg-[#fdf1f7] rounded-lg shadow-lg overflow-hidden hover:scale-105 transition">
                    <img src="images/6.jpg" alt="Accessoires" class="h-64 w-full object-cover">
                    <div class="p-6">
                        <h3 class="text-xl font-semibold text-orrose mb-2">Accessoires</h3>
                        <p class="text-sm text-gray-600 mb-4">Sacs, pochettes, bijoux</p>
                        <a href="products_simple.php" class="bg-white text-black px-4 py-2 rounded-full text-sm font-medium hover:bg-[#fdbbe3] transition">
                            Voir la catégorie
                        </a>
                    </div>
                </div>

                <div class="bg-[#fdf1f7] rounded-lg shadow-lg overflow-hidden hover:scale-105 transition">
                    <img src="images/10.jpg" alt="Beauté" class="h-64 w-full object-cover">
                    <div class="p-6">
                        <h3 class="text-xl font-semibold text-orrose mb-2">Beauté</h3>
                        <p class="text-sm text-gray-600 mb-4">Maquillage, soins, palettes</p>
                        <a href="products_simple.php" class="bg-white text-black px-4 py-2 rounded-full text-sm font-medium hover:bg-[#fdbbe3] transition">
                            Voir la catégorie
                        </a>
                    </div>
                </div>

                <div class="bg-[#fdf1f7] rounded-lg shadow-lg overflow-hidden hover:scale-105 transition">
                    <img src="images/8.jpg" alt="Chaussures" class="h-64 w-full object-cover">
                    <div class="p-6">
                        <h3 class="text-xl font-semibold text-orrose mb-2">Chaussures</h3>
                        <p class="text-sm text-gray-600 mb-4">Sandales, escarpins, baskets</p>
                        <a href="products_simple.php" class="bg-white text-black px-4 py-2 rounded-full text-sm font-medium hover:bg-[#fdbbe3] transition">
                            Voir la catégorie
                        </a>
                    </div>
                </div>

                <div class="bg-[#fdf1f7] rounded-lg shadow-lg overflow-hidden hover:scale-105 transition">
                    <img src="images/7.jpg" alt="Soins" class="h-64 w-full object-cover">
                    <div class="p-6">
                        <h3 class="text-xl font-semibold text-orrose mb-2">Soins</h3>
                        <p class="text-sm text-gray-600 mb-4">Visage, corps, cheveux</p>
                        <a href="products_simple.php" class="bg-white text-black px-4 py-2 rounded-full text-sm font-medium hover:bg-[#fdbbe3] transition">
                            Voir la catégorie
                        </a>
                    </div>
                </div>

                <div class="bg-[#fdf1f7] rounded-lg shadow-lg overflow-hidden hover:scale-105 transition">
                    <img src="images/9.jpg" alt="Nouveautés" class="h-64 w-full object-cover">
                    <div class="p-6">
                        <h3 class="text-xl font-semibold text-orrose mb-2">Nouveautés</h3>
                        <p class="text-sm text-gray-600 mb-4">Les dernières tendances à découvrir</p>
                        <a href="products_simple.php" class="bg-white text-black px-4 py-2 rounded-full text-sm font-medium hover:bg-[#fdbbe3] transition">
                            Voir la catégorie
                        </a>
                    </div>
                </div>

            </div>
        </section>

    <?php include 'includes/footer.php'; ?>

<script>
    // Recherche AJAX en temps réel
    document.addEventListener('DOMContentLoaded', function() {
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
            
            // Fermer les résultats quand on clique ailleurs
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.search-container')) {
                    searchResults.classList.add('hidden');
                }
            });
        }
        
        function displaySearchResults(products) {
            if (products.length === 0) {
                searchResults.innerHTML = '<div class="p-4 text-center text-gray-500">Aucun résultat trouvé</div>';
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
    
    // Menu déroulant avec TOGGLE au clic (plus stable!)
    document.addEventListener('DOMContentLoaded', function() {
        const profileBtn = document.getElementById('profileBtnIndex');
        const profileMenu = document.getElementById('profileMenuIndex');
        
        if (profileBtn && profileMenu) {
            // Ouvrir/fermer au clic sur le bouton
            profileBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                if (profileMenu.style.display === 'none' || profileMenu.style.display === '') {
                    profileMenu.style.display = 'block';
                } else {
                    profileMenu.style.display = 'none';
                }
            });
            
            // Fermer si on clique ailleurs
            document.addEventListener('click', function(e) {
                if (!profileBtn.contains(e.target) && !profileMenu.contains(e.target)) {
                    profileMenu.style.display = 'none';
                }
            });
            
            // Fermer au clic sur un lien
            profileMenu.querySelectorAll('a').forEach(link => {
                link.addEventListener('click', function() {
                    setTimeout(function() {
                        profileMenu.style.display = 'none';
                    }, 100);
                });
            });
        }
    });
</script>
</body>

</html>