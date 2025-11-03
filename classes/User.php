<?php
/**
 * Classe User - Gestion des utilisateurs
 * Quick Quick Shopping
 */

class User {
    private $db;
    private $id;
    private $name;
    private $email;
    private $role;
    private $phone;
    private $address;
    private $status;
    private $total_sales;
    private $created_at;
    private $updated_at;
    
    public function __construct($data = null) {
        $this->db = getConnection();
        
        if ($data) {
            $this->id = $data['id'] ?? null;
            $this->name = $data['name'] ?? '';
            $this->email = $data['email'] ?? '';
            $this->role = $data['role'] ?? 'customer';
            $this->phone = $data['phone'] ?? '';
            $this->address = $data['address'] ?? '';
            $this->status = $data['status'] ?? 'active';
            $this->total_sales = $data['total_sales'] ?? 0;
            $this->created_at = $data['created_at'] ?? null;
            $this->updated_at = $data['updated_at'] ?? null;
        }
    }
    
    // ==================== GETTERS ====================
    
    public function getId() { return $this->id; }
    public function getName() { return $this->name; }
    public function getEmail() { return $this->email; }
    public function getRole() { return $this->role; }
    public function getPhone() { return $this->phone; }
    public function getAddress() { return $this->address; }
    public function getStatus() { return $this->status; }
    public function getTotalSales() { return $this->total_sales; }
    public function getCreatedAt() { return $this->created_at; }
    public function getUpdatedAt() { return $this->updated_at; }
    
    // ==================== SETTERS ====================
    
    public function setName($name) { $this->name = $name; }
    public function setEmail($email) { $this->email = $email; }
    public function setPhone($phone) { $this->phone = $phone; }
    public function setAddress($address) { $this->address = $address; }
    public function setStatus($status) { $this->status = $status; }
    
    // ==================== MÉTHODES STATIQUES ====================
    
    /**
     * Créer un nouvel utilisateur
     */
    public static function create($data) {
        try {
            $sql = "INSERT INTO users (name, email, password, role, phone, address, status, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $params = [
                $data['name'],
                $data['email'],
                password_hash($data['password'], PASSWORD_DEFAULT),
                $data['role'],
                $data['phone'] ?? null,
                $data['address'] ?? null,
                $data['status'] ?? 'active'
            ];
            
            $user_id = insertAndGetId($sql, $params);
            
            // Créer l'enregistrement des exigences pour les vendeurs
            if ($data['role'] === 'seller') {
                executeQuery("INSERT INTO seller_requirements (seller_id, minimum_products) VALUES (?, 7)", [$user_id]);
            }
            
            return $user_id;
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la création de l'utilisateur : " . $e->getMessage());
        }
    }
    
    /**
     * Obtenir un utilisateur par ID
     */
    public static function findById($id) {
        $data = fetchOne("SELECT * FROM users WHERE id = ?", [$id]);
        return $data ? new self($data) : null;
    }
    
    /**
     * Obtenir un utilisateur par email
     */
    public static function findByEmail($email) {
        $data = fetchOne("SELECT * FROM users WHERE email = ?", [$email]);
        return $data ? new self($data) : null;
    }
    
    /**
     * Vérifier les identifiants de connexion
     */
    public static function authenticate($email, $password) {
        $user = self::findByEmail($email);
        
        if ($user && password_verify($password, $user->getPasswordHash())) {
            return $user;
        }
        
        return false;
    }
    
    /**
     * Obtenir le hash du mot de passe
     */
    private function getPasswordHash() {
        $data = fetchOne("SELECT password FROM users WHERE id = ?", [$this->id]);
        return $data['password'] ?? null;
    }
    
    /**
     * Obtenir tous les utilisateurs avec pagination
     */
    public static function getAll($limit = 20, $offset = 0, $role = null) {
        $sql = "SELECT * FROM users WHERE 1=1";
        $params = [];
        
        if ($role) {
            $sql .= " AND role = ?";
            $params[] = $role;
        }
        
        $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $users = fetchAll($sql, $params);
        return array_map(function($data) {
            return new self($data);
        }, $users);
    }
    
    /**
     * Obtenir les vendeurs en attente de validation
     */
    public static function getPendingSellers() {
        $users = fetchAll("SELECT * FROM users WHERE role = 'seller' AND status = 'pending' ORDER BY created_at DESC");
        return array_map(function($data) {
            return new self($data);
        }, $users);
    }
    
    /**
     * Obtenir les statistiques d'un utilisateur
     */
    public function getStats() {
        $stats = [];
        
        if ($this->role === 'seller') {
            // Statistiques vendeur
            $stats['total_products'] = fetchOne("SELECT COUNT(*) as count FROM products WHERE seller_id = ? AND status = 'active'", [$this->id])['count'];
            $stats['total_orders'] = fetchOne("SELECT COUNT(DISTINCT o.id) as count FROM orders o JOIN order_items oi ON o.id = oi.order_id JOIN products p ON oi.product_id = p.id WHERE p.seller_id = ? AND o.status = 'completed'", [$this->id])['count'];
            $stats['total_sales'] = $this->total_sales;
            $stats['total_commissions'] = fetchOne("SELECT COALESCE(SUM(commission_amount), 0) as total FROM commissions WHERE seller_id = ?", [$this->id])['total'];
        } elseif ($this->role === 'customer') {
            // Statistiques client
            $stats['total_orders'] = fetchOne("SELECT COUNT(*) as count FROM orders WHERE customer_id = ?", [$this->id])['count'];
            $stats['total_spent'] = fetchOne("SELECT COALESCE(SUM(total_amount), 0) as total FROM orders WHERE customer_id = ? AND status = 'completed'", [$this->id])['total'];
        }
        
        return $stats;
    }
    
    // ==================== MÉTHODES D'INSTANCE ====================
    
    /**
     * Sauvegarder les modifications
     */
    public function save() {
        if ($this->id) {
            // Mise à jour
            $sql = "UPDATE users SET name = ?, email = ?, phone = ?, address = ?, status = ?, updated_at = NOW() WHERE id = ?";
            executeQuery($sql, [
                $this->name,
                $this->email,
                $this->phone,
                $this->address,
                $this->status,
                $this->id
            ]);
        } else {
            // Création
            $data = [
                'name' => $this->name,
                'email' => $this->email,
                'role' => $this->role,
                'phone' => $this->phone,
                'address' => $this->address,
                'status' => $this->status
            ];
            $this->id = self::create($data);
        }
        
        return $this;
    }
    
    /**
     * Mettre à jour le mot de passe
     */
    public function updatePassword($new_password) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        executeQuery("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?", [$hashed_password, $this->id]);
        return $this;
    }
    
    /**
     * Activer le compte
     */
    public function activate() {
        $this->status = 'active';
        executeQuery("UPDATE users SET status = 'active', updated_at = NOW() WHERE id = ?", [$this->id]);
        return $this;
    }
    
    /**
     * Suspendre le compte
     */
    public function suspend() {
        $this->status = 'suspended';
        executeQuery("UPDATE users SET status = 'suspended', updated_at = NOW() WHERE id = ?", [$this->id]);
        return $this;
    }
    
    /**
     * Mettre à jour le total des ventes
     */
    public function updateTotalSales() {
        $total_sales = fetchOne("SELECT COALESCE(SUM(oi.price * oi.quantity), 0) as total 
                                 FROM orders o 
                                 JOIN order_items oi ON o.id = oi.order_id 
                                 JOIN products p ON oi.product_id = p.id 
                                 WHERE p.seller_id = ? AND o.status = 'completed'", [$this->id])['total'];
        
        $this->total_sales = $total_sales;
        executeQuery("UPDATE users SET total_sales = ?, updated_at = NOW() WHERE id = ?", [$total_sales, $this->id]);
        return $this;
    }
    
    /**
     * Vérifier si l'utilisateur peut vendre
     */
    public function canSell() {
        if ($this->role !== 'seller') {
            return false;
        }
        
        if ($this->status !== 'active') {
            return false;
        }
        
        // Vérifier l'exigence des 7 articles minimum
        $requirement = fetchOne("SELECT * FROM seller_requirements WHERE seller_id = ?", [$this->id]);
        if ($requirement && !$requirement['requirement_met']) {
            $products_sold = fetchOne("SELECT COUNT(*) as count FROM products WHERE seller_id = ? AND status = 'active'", [$this->id])['count'];
            return $products_sold >= 7;
        }
        
        return true;
    }
    
    /**
     * Obtenir les notifications
     */
    public function getNotifications($limit = 10) {
        return fetchAll("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ?", [$this->id, $limit]);
    }
    
    /**
     * Marquer les notifications comme lues
     */
    public function markNotificationsAsRead() {
        executeQuery("UPDATE notifications SET is_read = TRUE WHERE user_id = ?", [$this->id]);
        return $this;
    }
    
    /**
     * Supprimer l'utilisateur
     */
    public function delete() {
        // Marquer comme supprimé au lieu de supprimer réellement
        executeQuery("UPDATE users SET status = 'deleted', updated_at = NOW() WHERE id = ?", [$this->id]);
        return true;
    }
    
    /**
     * Convertir en tableau
     */
    public function toArray() {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'phone' => $this->phone,
            'address' => $this->address,
            'status' => $this->status,
            'total_sales' => $this->total_sales,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
?>

