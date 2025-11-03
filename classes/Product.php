<?php
/**
 * Classe Product - Gestion des produits
 * Quick Quick Shopping
 */

class Product {
    private $db;
    private $id;
    private $name;
    private $description;
    private $price;
    private $image;
    private $category_id;
    private $seller_id;
    private $stock_quantity;
    private $status;
    private $created_at;
    private $updated_at;
    
    public function __construct($data = null) {
        $this->db = getConnection();
        
        if ($data) {
            $this->id = $data['id'] ?? null;
            $this->name = $data['name'] ?? '';
            $this->description = $data['description'] ?? '';
            $this->price = $data['price'] ?? 0;
            $this->image = $data['image'] ?? null;
            $this->category_id = $data['category_id'] ?? null;
            $this->seller_id = $data['seller_id'] ?? null;
            $this->stock_quantity = $data['stock_quantity'] ?? 0;
            $this->status = $data['status'] ?? 'pending';
            $this->created_at = $data['created_at'] ?? null;
            $this->updated_at = $data['updated_at'] ?? null;
        }
    }
    
    // ==================== GETTERS ====================
    
    public function getId() { return $this->id; }
    public function getName() { return $this->name; }
    public function getDescription() { return $this->description; }
    public function getPrice() { return $this->price; }
    public function getImage() { return $this->image; }
    public function getCategoryId() { return $this->category_id; }
    public function getSellerId() { return $this->seller_id; }
    public function getStockQuantity() { return $this->stock_quantity; }
    public function getStatus() { return $this->status; }
    public function getCreatedAt() { return $this->created_at; }
    public function getUpdatedAt() { return $this->updated_at; }
    
    // ==================== SETTERS ====================
    
    public function setName($name) { $this->name = $name; }
    public function setDescription($description) { $this->description = $description; }
    public function setPrice($price) { $this->price = $price; }
    public function setImage($image) { $this->image = $image; }
    public function setCategoryId($category_id) { $this->category_id = $category_id; }
    public function setStockQuantity($stock_quantity) { $this->stock_quantity = $stock_quantity; }
    public function setStatus($status) { $this->status = $status; }
    
    // ==================== MÉTHODES STATIQUES ====================
    
    /**
     * Créer un nouveau produit
     */
    public static function create($data) {
        try {
            $sql = "INSERT INTO products (name, description, price, image, category_id, seller_id, stock_quantity, status, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $params = [
                $data['name'],
                $data['description'],
                $data['price'],
                $data['image'] ?? null,
                $data['category_id'],
                $data['seller_id'],
                $data['stock_quantity'],
                $data['status'] ?? 'pending'
            ];
            
            return insertAndGetId($sql, $params);
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la création du produit : " . $e->getMessage());
        }
    }
    
    /**
     * Obtenir un produit par ID
     */
    public static function findById($id) {
        $data = fetchOne("SELECT p.*, u.name as seller_name, c.name as category_name 
                         FROM products p 
                         JOIN users u ON p.seller_id = u.id 
                         JOIN categories c ON p.category_id = c.id 
                         WHERE p.id = ?", [$id]);
        return $data ? new self($data) : null;
    }
    
    /**
     * Obtenir tous les produits avec filtres
     */
    public static function getAll($filters = []) {
        $sql = "SELECT p.*, u.name as seller_name, c.name as category_name 
                FROM products p 
                JOIN users u ON p.seller_id = u.id 
                JOIN categories c ON p.category_id = c.id 
                WHERE 1=1";
        $params = [];
        
        // Filtres
        if (isset($filters['status'])) {
            $sql .= " AND p.status = ?";
            $params[] = $filters['status'];
        }
        
        if (isset($filters['category_id'])) {
            $sql .= " AND p.category_id = ?";
            $params[] = $filters['category_id'];
        }
        
        if (isset($filters['seller_id'])) {
            $sql .= " AND p.seller_id = ?";
            $params[] = $filters['seller_id'];
        }
        
        if (isset($filters['search'])) {
            $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
            $params[] = "%{$filters['search']}%";
            $params[] = "%{$filters['search']}%";
        }
        
        if (isset($filters['min_price'])) {
            $sql .= " AND p.price >= ?";
            $params[] = $filters['min_price'];
        }
        
        if (isset($filters['max_price'])) {
            $sql .= " AND p.price <= ?";
            $params[] = $filters['max_price'];
        }
        
        // Tri
        $order_by = $filters['order_by'] ?? 'created_at';
        $order_direction = $filters['order_direction'] ?? 'DESC';
        $sql .= " ORDER BY p.{$order_by} {$order_direction}";
        
        // Pagination
        if (isset($filters['limit'])) {
            $sql .= " LIMIT ?";
            $params[] = $filters['limit'];
            
            if (isset($filters['offset'])) {
                $sql .= " OFFSET ?";
                $params[] = $filters['offset'];
            }
        }
        
        $products = fetchAll($sql, $params);
        return array_map(function($data) {
            return new self($data);
        }, $products);
    }
    
    /**
     * Obtenir les produits en vedette
     */
    public static function getFeatured($limit = 8) {
        $filters = [
            'status' => 'active',
            'limit' => $limit,
            'order_by' => 'created_at',
            'order_direction' => 'DESC'
        ];
        return self::getAll($filters);
    }
    
    /**
     * Obtenir les produits populaires
     */
    public static function getPopular($limit = 8) {
        $sql = "SELECT p.*, u.name as seller_name, c.name as category_name,
                       COUNT(oi.id) as times_ordered,
                       SUM(oi.quantity) as total_quantity_sold
                FROM products p
                JOIN users u ON p.seller_id = u.id
                JOIN categories c ON p.category_id = c.id
                LEFT JOIN order_items oi ON p.id = oi.product_id
                LEFT JOIN orders o ON oi.order_id = o.id AND o.status = 'completed'
                WHERE p.status = 'active'
                GROUP BY p.id, p.name, p.price, p.image, u.name, c.name
                ORDER BY times_ordered DESC, total_quantity_sold DESC
                LIMIT ?";
        
        $products = fetchAll($sql, [$limit]);
        return array_map(function($data) {
            return new self($data);
        }, $products);
    }
    
    /**
     * Rechercher des produits
     */
    public static function search($query, $filters = []) {
        $filters['search'] = $query;
        return self::getAll($filters);
    }
    
    /**
     * Obtenir les produits d'un vendeur
     */
    public static function getBySeller($seller_id, $filters = []) {
        $filters['seller_id'] = $seller_id;
        return self::getAll($filters);
    }
    
    /**
     * Obtenir les statistiques des produits
     */
    public static function getStats() {
        $stats = [];
        
        $stats['total_products'] = fetchOne("SELECT COUNT(*) as count FROM products WHERE status = 'active'")['count'];
        $stats['pending_products'] = fetchOne("SELECT COUNT(*) as count FROM products WHERE status = 'pending'")['count'];
        $stats['sold_out_products'] = fetchOne("SELECT COUNT(*) as count FROM products WHERE status = 'sold_out'")['count'];
        
        return $stats;
    }
    
    // ==================== MÉTHODES D'INSTANCE ====================
    
    /**
     * Sauvegarder les modifications
     */
    public function save() {
        if ($this->id) {
            // Mise à jour
            $sql = "UPDATE products SET name = ?, description = ?, price = ?, image = ?, 
                    category_id = ?, stock_quantity = ?, status = ?, updated_at = NOW() 
                    WHERE id = ?";
            executeQuery($sql, [
                $this->name,
                $this->description,
                $this->price,
                $this->image,
                $this->category_id,
                $this->stock_quantity,
                $this->status,
                $this->id
            ]);
        } else {
            // Création
            $data = [
                'name' => $this->name,
                'description' => $this->description,
                'price' => $this->price,
                'image' => $this->image,
                'category_id' => $this->category_id,
                'seller_id' => $this->seller_id,
                'stock_quantity' => $this->stock_quantity,
                'status' => $this->status
            ];
            $this->id = self::create($data);
        }
        
        return $this;
    }
    
    /**
     * Approuver le produit
     */
    public function approve() {
        $this->status = 'active';
        executeQuery("UPDATE products SET status = 'active', updated_at = NOW() WHERE id = ?", [$this->id]);
        return $this;
    }
    
    /**
     * Rejeter le produit
     */
    public function reject() {
        $this->status = 'rejected';
        executeQuery("UPDATE products SET status = 'rejected', updated_at = NOW() WHERE id = ?", [$this->id]);
        return $this;
    }
    
    /**
     * Marquer comme épuisé
     */
    public function markAsSoldOut() {
        $this->status = 'sold_out';
        executeQuery("UPDATE products SET status = 'sold_out', updated_at = NOW() WHERE id = ?", [$this->id]);
        return $this;
    }
    
    /**
     * Mettre à jour le stock
     */
    public function updateStock($quantity) {
        $this->stock_quantity = max(0, $quantity);
        
        // Mettre à jour le statut selon le stock
        if ($this->stock_quantity == 0) {
            $this->status = 'sold_out';
        } elseif ($this->status == 'sold_out' && $this->stock_quantity > 0) {
            $this->status = 'active';
        }
        
        executeQuery("UPDATE products SET stock_quantity = ?, status = ?, updated_at = NOW() WHERE id = ?", 
                    [$this->stock_quantity, $this->status, $this->id]);
        return $this;
    }
    
    /**
     * Réduire le stock (lors d'une vente)
     */
    public function reduceStock($quantity) {
        $new_stock = max(0, $this->stock_quantity - $quantity);
        return $this->updateStock($new_stock);
    }
    
    /**
     * Augmenter le stock
     */
    public function increaseStock($quantity) {
        return $this->updateStock($this->stock_quantity + $quantity);
    }
    
    /**
     * Vérifier si le produit est disponible
     */
    public function isAvailable() {
        return $this->status === 'active' && $this->stock_quantity > 0;
    }
    
    /**
     * Obtenir les informations du vendeur
     */
    public function getSeller() {
        return fetchOne("SELECT * FROM users WHERE id = ?", [$this->seller_id]);
    }
    
    /**
     * Obtenir les informations de la catégorie
     */
    public function getCategory() {
        return fetchOne("SELECT * FROM categories WHERE id = ?", [$this->category_id]);
    }
    
    /**
     * Obtenir les statistiques du produit
     */
    public function getStats() {
        $stats = [];
        
        // Nombre de fois commandé
        $stats['times_ordered'] = fetchOne("SELECT COUNT(*) as count FROM order_items WHERE product_id = ?", [$this->id])['count'];
        
        // Quantité totale vendue
        $stats['total_sold'] = fetchOne("SELECT COALESCE(SUM(quantity), 0) as total FROM order_items WHERE product_id = ?", [$this->id])['total'];
        
        // Chiffre d'affaires généré
        $stats['revenue'] = fetchOne("SELECT COALESCE(SUM(oi.price * oi.quantity), 0) as total 
                                     FROM order_items oi 
                                     JOIN orders o ON oi.order_id = o.id 
                                     WHERE oi.product_id = ? AND o.status = 'completed'", [$this->id])['total'];
        
        return $stats;
    }
    
    /**
     * Obtenir les avis du produit
     */
    public function getReviews() {
        return fetchAll("SELECT r.*, u.name as customer_name 
                        FROM product_reviews r 
                        JOIN users u ON r.customer_id = u.id 
                        WHERE r.product_id = ? 
                        ORDER BY r.created_at DESC", [$this->id]);
    }
    
    /**
     * Obtenir la note moyenne
     */
    public function getAverageRating() {
        $result = fetchOne("SELECT AVG(rating) as average FROM product_reviews WHERE product_id = ?", [$this->id]);
        return round($result['average'], 1);
    }
    
    /**
     * Supprimer le produit (soft delete)
     */
    public function delete() {
        $this->status = 'deleted';
        executeQuery("UPDATE products SET status = 'deleted', updated_at = NOW() WHERE id = ?", [$this->id]);
        return true;
    }
    
    /**
     * Obtenir l'URL de l'image
     */
    public function getImageUrl() {
        if ($this->image) {
            return 'assets/images/products/' . $this->image;
        }
        return 'assets/images/no-image.jpg';
    }
    
    /**
     * Formater le prix
     */
    public function getFormattedPrice() {
        return number_format($this->price, 0, ',', ' ') . ' F XOF';
    }
    
    /**
     * Convertir en tableau
     */
    public function toArray() {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'formatted_price' => $this->getFormattedPrice(),
            'image' => $this->image,
            'image_url' => $this->getImageUrl(),
            'category_id' => $this->category_id,
            'seller_id' => $this->seller_id,
            'stock_quantity' => $this->stock_quantity,
            'status' => $this->status,
            'is_available' => $this->isAvailable(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
?>

