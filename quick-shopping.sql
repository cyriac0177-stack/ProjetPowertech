-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost
-- Généré le : dim. 26 oct. 2025 à 10:22
-- Version du serveur : 10.4.28-MariaDB
-- Version de PHP : 8.0.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `quick-shopping`
--

-- --------------------------------------------------------

--
-- Structure de la table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `icon`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Vêtements', 'Robes, tops, pantalons, jupes', 'fas fa-tshirt', 'active', '2025-10-25 19:13:00', '2025-10-25 19:13:00'),
(2, 'Accessoires', 'Sacs, bijoux, chaussures', 'fas fa-gem', 'active', '2025-10-25 19:13:00', '2025-10-25 19:13:00'),
(3, 'Beauté', 'Maquillage, soins, parfums', 'fas fa-palette', 'active', '2025-10-25 19:13:00', '2025-10-25 19:13:00'),
(4, 'Lingerie', 'Sous-vêtements, pyjamas', 'fas fa-heart', 'active', '2025-10-25 19:13:00', '2025-10-25 19:13:00'),
(5, 'Chaussures', 'Escarpins, baskets, sandales', 'fas fa-shoe-prints', 'active', '2025-10-25 19:13:00', '2025-10-25 19:13:00'),
(6, 'Déstockage', 'Ventes flash et promotions', 'fas fa-fire', 'active', '2025-10-25 19:13:00', '2025-10-25 19:13:00');

-- --------------------------------------------------------

--
-- Structure de la table `commissions`
--

CREATE TABLE `commissions` (
  `id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `commission_rate` decimal(5,4) NOT NULL,
  `commission_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','paid','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `type` enum('info','success','warning','error') DEFAULT 'info',
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','confirmed','shipped','delivered','cancelled') DEFAULT 'pending',
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_status` enum('pending','paid','failed','refunded') DEFAULT 'pending',
  `shipping_address` text DEFAULT NULL,
  `tracking_number` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `orders`
--

INSERT INTO `orders` (`id`, `customer_id`, `total_amount`, `status`, `payment_method`, `payment_status`, `shipping_address`, `tracking_number`, `created_at`, `updated_at`) VALUES
(1, 6, 57000.00, 'confirmed', 'mobile_money', 'pending', '', NULL, '2025-10-26 08:31:28', '2025-10-26 09:16:27');

-- --------------------------------------------------------

--
-- Structure de la table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`, `created_at`) VALUES
(1, 1, 5, 3, 10000.00, '2025-10-26 08:31:28'),
(2, 1, 4, 1, 25000.00, '2025-10-26 08:31:28'),
(3, 1, 3, 1, 2000.00, '2025-10-26 08:31:28');

-- --------------------------------------------------------

--
-- Structure de la table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `status` enum('pending','success','failed','refunded') DEFAULT 'pending',
  `payment_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payment_data`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Doublure de structure pour la vue `popular_products`
-- (Voir ci-dessous la vue réelle)
--
CREATE TABLE `popular_products` (
`id` int(11)
,`name` varchar(200)
,`price` decimal(10,2)
,`image` varchar(255)
,`seller_name` varchar(100)
,`times_ordered` bigint(21)
,`total_quantity_sold` decimal(32,0)
);

-- --------------------------------------------------------

--
-- Structure de la table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `category_id` int(11) NOT NULL,
  `reference` varchar(100) DEFAULT NULL,
  `seller_id` int(11) NOT NULL,
  `stock_quantity` int(11) DEFAULT 0,
  `status` enum('active','pending','sold_out','deleted') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `image`, `category_id`, `reference`, `seller_id`, `stock_quantity`, `status`, `created_at`, `updated_at`) VALUES
(2, 'pomme', 'ma pomme d\'amour', 10000.00, 'images/products/1761464844_pomme.jpeg', 2, 'l455', 3, 6, 'pending', '2025-10-26 07:12:56', '2025-10-26 07:47:24'),
(3, 'banana', 'banana des mignons', 2000.00, 'images/products/1761464616_banane.jpeg', 2, 'b8T57', 4, 8, 'active', '2025-10-26 07:43:36', '2025-10-26 08:31:28'),
(4, 'cerise', 'CERISE des latés', 25000.00, 'images/products/1761464791_cerise.jpeg', 2, 'jg757', 5, 11, 'active', '2025-10-26 07:46:31', '2025-10-26 08:31:28'),
(5, 'pomme', 'pommes d\'amour', 10000.00, 'images/products/1761464882_pomme.jpeg', 2, 'jb6R', 3, 3, 'active', '2025-10-26 07:48:02', '2025-10-26 08:31:28');

-- --------------------------------------------------------

--
-- Doublure de structure pour la vue `produits`
-- (Voir ci-dessous la vue réelle)
--
CREATE TABLE `produits` (
`produit_id` int(11)
,`nom` varchar(200)
,`prix` decimal(10,2)
,`description` text
,`image_url` varchar(255)
,`categorie_id` int(11)
,`reference` varchar(100)
,`stock` int(11)
,`status` enum('active','pending','sold_out','deleted')
,`disponible` int(1)
,`created_at` timestamp
,`seller_id` int(11)
);

-- --------------------------------------------------------

--
-- Structure de la table `seller_requirements`
--

CREATE TABLE `seller_requirements` (
  `id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `minimum_products` int(11) DEFAULT 7,
  `products_sold` int(11) DEFAULT 0,
  `requirement_met` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Doublure de structure pour la vue `seller_stats`
-- (Voir ci-dessous la vue réelle)
--
CREATE TABLE `seller_stats` (
`id` int(11)
,`name` varchar(100)
,`email` varchar(100)
,`total_products` bigint(21)
,`total_orders` bigint(21)
,`total_sales` decimal(42,2)
,`total_commissions` decimal(32,2)
);

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','seller','customer') NOT NULL DEFAULT 'customer',
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `status` enum('active','pending','suspended','rejected') DEFAULT 'active',
  `total_sales` decimal(12,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `phone`, `address`, `status`, `total_sales`, `created_at`, `updated_at`) VALUES
(1, 'Administrateur', 'admin@quickquickshopping.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NULL, NULL, 'active', 0.00, '2025-10-25 19:13:00', '2025-10-26 05:46:26'),
(3, 'stide ekw', 'ekwugha344@gmail.com', '$2y$10$c8iTFYkHLPAUNq1F67CBKOKX7gjKC1BY6LjWEfgKYzNFEq4JyU6VG', 'seller', '0703563017', 'Angre Mahou', 'active', 0.00, '2025-10-26 04:38:11', '2025-10-26 06:52:13'),
(4, 'akoumia orne', 'orne@gmail.com', '$2y$10$.qTLMNXnqtRPOPkCl/hnxOX6L2wrZVT4lNOYORdPRG5qGYh7zxIuy', 'seller', NULL, 'Yop city', 'active', 0.00, '2025-10-26 07:39:55', '2025-10-26 07:39:55'),
(5, 'phanie Nounours', 'phanie@gmail.com', '$2y$10$3vTOjeS9CjAK7okb0/lPjufn2qVYBbJId0jQfFGleYYwhvzLR92XS', 'seller', '9080838', 'vallon', 'active', 0.00, '2025-10-26 07:45:41', '2025-10-26 07:45:41'),
(6, 'winnie wiii', 'winnnie@gmail.com', '$2y$10$9s0oupr8FE9NdstVFxkLzej3Zn3.Ou63pa8HgQplpDJlhahv2DZ2C', 'customer', '87682682', 'angre', 'active', 0.00, '2025-10-26 07:49:02', '2025-10-26 07:49:02'),
(7, 'AMIN admin', 'pdg@gmail.com', '$2y$10$hUYn4mGQN4f3gglEE8vneeo3u4IWXwlOQtqBp1rOSwI4rKcoGWtDq', 'admin', '98Y8684', 'ANGRE', 'active', 0.00, '2025-10-26 08:44:05', '2025-10-26 08:44:22');

-- --------------------------------------------------------

--
-- Structure de la vue `popular_products`
--
DROP TABLE IF EXISTS `popular_products`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `popular_products`  AS SELECT `p`.`id` AS `id`, `p`.`name` AS `name`, `p`.`price` AS `price`, `p`.`image` AS `image`, `u`.`name` AS `seller_name`, count(`oi`.`id`) AS `times_ordered`, sum(`oi`.`quantity`) AS `total_quantity_sold` FROM (((`products` `p` join `users` `u` on(`p`.`seller_id` = `u`.`id`)) left join `order_items` `oi` on(`p`.`id` = `oi`.`product_id`)) left join `orders` `o` on(`oi`.`order_id` = `o`.`id` and `o`.`status` = 'completed')) WHERE `p`.`status` = 'active' GROUP BY `p`.`id`, `p`.`name`, `p`.`price`, `p`.`image`, `u`.`name` ORDER BY count(`oi`.`id`) DESC, sum(`oi`.`quantity`) DESC ;

-- --------------------------------------------------------

--
-- Structure de la vue `produits`
--
DROP TABLE IF EXISTS `produits`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `produits`  AS SELECT `products`.`id` AS `produit_id`, `products`.`name` AS `nom`, `products`.`price` AS `prix`, `products`.`description` AS `description`, `products`.`image` AS `image_url`, `products`.`category_id` AS `categorie_id`, `products`.`reference` AS `reference`, `products`.`stock_quantity` AS `stock`, `products`.`status` AS `status`, CASE WHEN `products`.`status` = 'active' THEN 1 ELSE 0 END AS `disponible`, `products`.`created_at` AS `created_at`, `products`.`seller_id` AS `seller_id` FROM `products` ;

-- --------------------------------------------------------

--
-- Structure de la vue `seller_stats`
--
DROP TABLE IF EXISTS `seller_stats`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `seller_stats`  AS SELECT `u`.`id` AS `id`, `u`.`name` AS `name`, `u`.`email` AS `email`, count(distinct `p`.`id`) AS `total_products`, count(distinct `oi`.`order_id`) AS `total_orders`, coalesce(sum(`oi`.`price` * `oi`.`quantity`),0) AS `total_sales`, coalesce(sum(`c`.`commission_amount`),0) AS `total_commissions` FROM ((((`users` `u` left join `products` `p` on(`u`.`id` = `p`.`seller_id` and `p`.`status` = 'active')) left join `order_items` `oi` on(`p`.`id` = `oi`.`product_id`)) left join `orders` `o` on(`oi`.`order_id` = `o`.`id` and `o`.`status` = 'completed')) left join `commissions` `c` on(`u`.`id` = `c`.`seller_id`)) WHERE `u`.`role` = 'seller' GROUP BY `u`.`id`, `u`.`name`, `u`.`email` ;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `commissions`
--
ALTER TABLE `commissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `idx_commissions_seller` (`seller_id`);

--
-- Index pour la table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_orders_customer` (`customer_id`),
  ADD KEY `idx_orders_status` (`status`);

--
-- Index pour la table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Index pour la table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Index pour la table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_products_name` (`name`),
  ADD KEY `idx_products_category` (`category_id`),
  ADD KEY `idx_products_seller` (`seller_id`),
  ADD KEY `idx_products_status` (`status`);

--
-- Index pour la table `seller_requirements`
--
ALTER TABLE `seller_requirements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `seller_id` (`seller_id`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_users_email` (`email`),
  ADD KEY `idx_users_role` (`role`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `commissions`
--
ALTER TABLE `commissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `seller_requirements`
--
ALTER TABLE `seller_requirements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `commissions`
--
ALTER TABLE `commissions`
  ADD CONSTRAINT `commissions_ibfk_1` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `commissions_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `seller_requirements`
--
ALTER TABLE `seller_requirements`
  ADD CONSTRAINT `seller_requirements_ibfk_1` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
