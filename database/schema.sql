-- =====================================================
-- Base de données Quick Quick Shopping
-- =====================================================

CREATE DATABASE IF NOT EXISTS quick_quick_shopping CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE quick_quick_shopping;

-- =====================================================
-- TABLE: users (Utilisateurs)
-- =====================================================
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'seller', 'customer') NOT NULL DEFAULT 'customer',
    phone VARCHAR(20),
    address TEXT,
    status ENUM('active', 'pending', 'suspended', 'rejected') DEFAULT 'active',
    total_sales DECIMAL(12,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =====================================================
-- TABLE: categories (Catégories de produits)
-- =====================================================
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    icon VARCHAR(50),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =====================================================
-- TABLE: products (Produits)
-- =====================================================
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255),
    category_id INT NOT NULL,
    seller_id INT NOT NULL,
    stock_quantity INT DEFAULT 0,
    status ENUM('active', 'pending', 'sold_out', 'deleted') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE
);

-- =====================================================
-- TABLE: orders (Commandes)
-- =====================================================
CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    customer_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    payment_method VARCHAR(50),
    payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
    shipping_address TEXT,
    tracking_number VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE
);

-- =====================================================
-- TABLE: order_items (Articles de commande)
-- =====================================================
CREATE TABLE order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- =====================================================
-- TABLE: commissions (Commissions)
-- =====================================================
CREATE TABLE commissions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    seller_id INT NOT NULL,
    order_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    commission_rate DECIMAL(5,4) NOT NULL,
    commission_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'paid', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

-- =====================================================
-- TABLE: payments (Paiements)
-- =====================================================
CREATE TABLE payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    transaction_id VARCHAR(100),
    status ENUM('pending', 'success', 'failed', 'refunded') DEFAULT 'pending',
    payment_data JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

-- =====================================================
-- TABLE: notifications (Notifications)
-- =====================================================
CREATE TABLE notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info', 'success', 'warning', 'error') DEFAULT 'info',
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- =====================================================
-- TABLE: seller_requirements (Exigences vendeur)
-- =====================================================
CREATE TABLE seller_requirements (
    id INT PRIMARY KEY AUTO_INCREMENT,
    seller_id INT NOT NULL,
    minimum_products INT DEFAULT 7,
    products_sold INT DEFAULT 0,
    requirement_met BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE
);

-- =====================================================
-- INSERTION DES DONNÉES INITIALES
-- =====================================================

-- Admin par défaut
INSERT INTO users (name, email, password, role, status) VALUES 
('Administrateur', 'admin@quickquickshopping.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active');

-- Catégories par défaut
INSERT INTO categories (name, description, icon) VALUES 
('Vêtements', 'Robes, tops, pantalons, jupes', 'fas fa-tshirt'),
('Accessoires', 'Sacs, bijoux, chaussures', 'fas fa-gem'),
('Beauté', 'Maquillage, soins, parfums', 'fas fa-palette'),
('Lingerie', 'Sous-vêtements, pyjamas', 'fas fa-heart'),
('Chaussures', 'Escarpins, baskets, sandales', 'fas fa-shoe-prints'),
('Déstockage', 'Ventes flash et promotions', 'fas fa-fire');

-- =====================================================
-- INDEX POUR OPTIMISATION
-- =====================================================

-- Index pour les recherches fréquentes
CREATE INDEX idx_products_name ON products(name);
CREATE INDEX idx_products_category ON products(category_id);
CREATE INDEX idx_products_seller ON products(seller_id);
CREATE INDEX idx_products_status ON products(status);
CREATE INDEX idx_orders_customer ON orders(customer_id);
CREATE INDEX idx_orders_status ON orders(status);
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_commissions_seller ON commissions(seller_id);

-- =====================================================
-- VUES POUR RAPPORTS
-- =====================================================

-- Vue pour les statistiques des vendeurs
CREATE VIEW seller_stats AS
SELECT 
    u.id,
    u.name,
    u.email,
    COUNT(DISTINCT p.id) as total_products,
    COUNT(DISTINCT oi.order_id) as total_orders,
    COALESCE(SUM(oi.price * oi.quantity), 0) as total_sales,
    COALESCE(SUM(c.commission_amount), 0) as total_commissions
FROM users u
LEFT JOIN products p ON u.id = p.seller_id AND p.status = 'active'
LEFT JOIN order_items oi ON p.id = oi.product_id
LEFT JOIN orders o ON oi.order_id = o.id AND o.status = 'completed'
LEFT JOIN commissions c ON u.id = c.seller_id
WHERE u.role = 'seller'
GROUP BY u.id, u.name, u.email;

-- Vue pour les produits populaires
CREATE VIEW popular_products AS
SELECT 
    p.id,
    p.name,
    p.price,
    p.image,
    u.name as seller_name,
    COUNT(oi.id) as times_ordered,
    SUM(oi.quantity) as total_quantity_sold
FROM products p
JOIN users u ON p.seller_id = u.id
LEFT JOIN order_items oi ON p.id = oi.product_id
LEFT JOIN orders o ON oi.order_id = o.id AND o.status = 'completed'
WHERE p.status = 'active'
GROUP BY p.id, p.name, p.price, p.image, u.name
ORDER BY times_ordered DESC, total_quantity_sold DESC;

