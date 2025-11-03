<?php
/**
 * Classe Order - Gestion des commandes
 * Quick Quick Shopping
 */

class Order {
    private $db;
    private $id;
    private $customer_id;
    private $total_amount;
    private $status;
    private $payment_method;
    private $payment_status;
    private $shipping_address;
    private $tracking_number;
    private $created_at;
    private $updated_at;
    
    public function __construct($data = null) {
        $this->db = getConnection();
        
        if ($data) {
            $this->id = $data['id'] ?? null;
            $this->customer_id = $data['customer_id'] ?? null;
            $this->total_amount = $data['total_amount'] ?? 0;
            $this->status = $data['status'] ?? 'pending';
            $this->payment_method = $data['payment_method'] ?? null;
            $this->payment_status = $data['payment_status'] ?? 'pending';
            $this->shipping_address = $data['shipping_address'] ?? '';
            $this->tracking_number = $data['tracking_number'] ?? null;
            $this->created_at = $data['created_at'] ?? null;
            $this->updated_at = $data['updated_at'] ?? null;
        }
    }
    
    // ==================== GETTERS ====================
    
    public function getId() { return $this->id; }
    public function getCustomerId() { return $this->customer_id; }
    public function getTotalAmount() { return $this->total_amount; }
    public function getStatus() { return $this->status; }
    public function getPaymentMethod() { return $this->payment_method; }
    public function getPaymentStatus() { return $this->payment_status; }
    public function getShippingAddress() { return $this->shipping_address; }
    public function getTrackingNumber() { return $this->tracking_number; }
    public function getCreatedAt() { return $this->created_at; }
    public function getUpdatedAt() { return $this->updated_at; }
    
    // ==================== SETTERS ====================
    
    public function setStatus($status) { $this->status = $status; }
    public function setPaymentStatus($payment_status) { $this->payment_status = $payment_status; }
    public function setTrackingNumber($tracking_number) { $this->tracking_number = $tracking_number; }
    
    // ==================== MÉTHODES STATIQUES ====================
    
    /**
     * Créer une nouvelle commande
     */
    public static function create($data) {
        try {
            $sql = "INSERT INTO orders (customer_id, total_amount, status, payment_method, payment_status, shipping_address, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, NOW())";
            
            $params = [
                $data['customer_id'],
                $data['total_amount'],
                $data['status'] ?? 'pending',
                $data['payment_method'],
                $data['payment_status'] ?? 'pending',
                $data['shipping_address']
            ];
            
            return insertAndGetId($sql, $params);
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la création de la commande : " . $e->getMessage());
        }
    }
    
    /**
     * Obtenir une commande par ID
     */
    public static function findById($id) {
        $data = fetchOne("SELECT * FROM orders WHERE id = ?", [$id]);
        return $data ? new self($data) : null;
    }
    
    /**
     * Obtenir les commandes d'un client
     */
    public static function getByCustomer($customer_id, $limit = 20, $offset = 0) {
        $orders = fetchAll("SELECT * FROM orders WHERE customer_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?", 
                          [$customer_id, $limit, $offset]);
        return array_map(function($data) {
            return new self($data);
        }, $orders);
    }
    
    /**
     * Obtenir les commandes d'un vendeur
     */
    public static function getBySeller($seller_id, $limit = 20, $offset = 0) {
        $orders = fetchAll("SELECT DISTINCT o.* 
                           FROM orders o 
                           JOIN order_items oi ON o.id = oi.order_id 
                           JOIN products p ON oi.product_id = p.id 
                           WHERE p.seller_id = ? 
                           ORDER BY o.created_at DESC 
                           LIMIT ? OFFSET ?", [$seller_id, $limit, $offset]);
        return array_map(function($data) {
            return new self($data);
        }, $orders);
    }
    
    /**
     * Obtenir toutes les commandes avec filtres
     */
    public static function getAll($filters = []) {
        $sql = "SELECT o.*, u.name as customer_name 
                FROM orders o 
                JOIN users u ON o.customer_id = u.id 
                WHERE 1=1";
        $params = [];
        
        // Filtres
        if (isset($filters['status'])) {
            $sql .= " AND o.status = ?";
            $params[] = $filters['status'];
        }
        
        if (isset($filters['payment_status'])) {
            $sql .= " AND o.payment_status = ?";
            $params[] = $filters['payment_status'];
        }
        
        if (isset($filters['date_from'])) {
            $sql .= " AND DATE(o.created_at) >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (isset($filters['date_to'])) {
            $sql .= " AND DATE(o.created_at) <= ?";
            $params[] = $filters['date_to'];
        }
        
        // Tri
        $order_by = $filters['order_by'] ?? 'created_at';
        $order_direction = $filters['order_direction'] ?? 'DESC';
        $sql .= " ORDER BY o.{$order_by} {$order_direction}";
        
        // Pagination
        if (isset($filters['limit'])) {
            $sql .= " LIMIT ?";
            $params[] = $filters['limit'];
            
            if (isset($filters['offset'])) {
                $sql .= " OFFSET ?";
                $params[] = $filters['offset'];
            }
        }
        
        $orders = fetchAll($sql, $params);
        return array_map(function($data) {
            return new self($data);
        }, $orders);
    }
    
    /**
     * Obtenir les statistiques des commandes
     */
    public static function getStats() {
        $stats = [];
        
        $stats['total_orders'] = fetchOne("SELECT COUNT(*) as count FROM orders")['count'];
        $stats['pending_orders'] = fetchOne("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'")['count'];
        $stats['completed_orders'] = fetchOne("SELECT COUNT(*) as count FROM orders WHERE status = 'completed'")['count'];
        $stats['cancelled_orders'] = fetchOne("SELECT COUNT(*) as count FROM orders WHERE status = 'cancelled'")['count'];
        $stats['total_revenue'] = fetchOne("SELECT COALESCE(SUM(total_amount), 0) as total FROM orders WHERE status = 'completed'")['total'];
        
        return $stats;
    }
    
    // ==================== MÉTHODES D'INSTANCE ====================
    
    /**
     * Sauvegarder les modifications
     */
    public function save() {
        if ($this->id) {
            $sql = "UPDATE orders SET status = ?, payment_status = ?, tracking_number = ?, updated_at = NOW() WHERE id = ?";
            executeQuery($sql, [
                $this->status,
                $this->payment_status,
                $this->tracking_number,
                $this->id
            ]);
        }
        
        return $this;
    }
    
    /**
     * Confirmer la commande
     */
    public function confirm() {
        $this->status = 'confirmed';
        $this->save();
        
        // Réduire le stock des produits
        $this->reduceProductStock();
        
        // Calculer et enregistrer les commissions
        $this->calculateCommissions();
        
        return $this;
    }
    
    /**
     * Marquer comme expédiée
     */
    public function ship($tracking_number = null) {
        $this->status = 'shipped';
        if ($tracking_number) {
            $this->tracking_number = $tracking_number;
        }
        $this->save();
        return $this;
    }
    
    /**
     * Marquer comme livrée
     */
    public function deliver() {
        $this->status = 'delivered';
        $this->save();
        return $this;
    }
    
    /**
     * Annuler la commande
     */
    public function cancel() {
        $this->status = 'cancelled';
        $this->save();
        
        // Restaurer le stock des produits
        $this->restoreProductStock();
        
        return $this;
    }
    
    /**
     * Marquer le paiement comme effectué
     */
    public function markAsPaid() {
        $this->payment_status = 'paid';
        $this->save();
        return $this;
    }
    
    /**
     * Marquer le paiement comme échoué
     */
    public function markPaymentFailed() {
        $this->payment_status = 'failed';
        $this->save();
        return $this;
    }
    
    /**
     * Obtenir les articles de la commande
     */
    public function getItems() {
        return fetchAll("SELECT oi.*, p.name as product_name, p.image, u.name as seller_name 
                        FROM order_items oi 
                        JOIN products p ON oi.product_id = p.id 
                        JOIN users u ON p.seller_id = u.id 
                        WHERE oi.order_id = ?", [$this->id]);
    }
    
    /**
     * Ajouter un article à la commande
     */
    public function addItem($product_id, $quantity, $price) {
        $sql = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
        return executeQuery($sql, [$this->id, $product_id, $quantity, $price]);
    }
    
    /**
     * Obtenir les informations du client
     */
    public function getCustomer() {
        return fetchOne("SELECT * FROM users WHERE id = ?", [$this->customer_id]);
    }
    
    /**
     * Réduire le stock des produits
     */
    private function reduceProductStock() {
        $items = $this->getItems();
        foreach ($items as $item) {
            $product = Product::findById($item['product_id']);
            if ($product) {
                $product->reduceStock($item['quantity']);
            }
        }
    }
    
    /**
     * Restaurer le stock des produits
     */
    private function restoreProductStock() {
        $items = $this->getItems();
        foreach ($items as $item) {
            $product = Product::findById($item['product_id']);
            if ($product) {
                $product->increaseStock($item['quantity']);
            }
        }
    }
    
    /**
     * Calculer et enregistrer les commissions
     */
    private function calculateCommissions() {
        $items = $this->getItems();
        
        foreach ($items as $item) {
            $product = Product::findById($item['product_id']);
            if ($product) {
                $seller_id = $product->getSellerId();
                $amount = $item['price'] * $item['quantity'];
                
                // Obtenir le total des ventes du vendeur
                $total_sales = fetchOne("SELECT COALESCE(SUM(oi.price * oi.quantity), 0) as total 
                                       FROM orders o 
                                       JOIN order_items oi ON o.id = oi.order_id 
                                       JOIN products p ON oi.product_id = p.id 
                                       WHERE p.seller_id = ? AND o.status = 'completed'", [$seller_id])['total'];
                
                // Calculer le taux de commission
                $commission_rate = Commission::calculateRate($total_sales);
                $commission_amount = $amount * $commission_rate;
                
                // Enregistrer la commission
                Commission::create([
                    'seller_id' => $seller_id,
                    'order_id' => $this->id,
                    'amount' => $amount,
                    'commission_rate' => $commission_rate,
                    'commission_amount' => $commission_amount
                ]);
            }
        }
    }
    
    /**
     * Obtenir le statut formaté
     */
    public function getStatusLabel() {
        $labels = [
            'pending' => 'En attente',
            'confirmed' => 'Confirmée',
            'shipped' => 'Expédiée',
            'delivered' => 'Livrée',
            'cancelled' => 'Annulée'
        ];
        
        return $labels[$this->status] ?? $this->status;
    }
    
    /**
     * Obtenir le statut de paiement formaté
     */
    public function getPaymentStatusLabel() {
        $labels = [
            'pending' => 'En attente',
            'paid' => 'Payé',
            'failed' => 'Échoué',
            'refunded' => 'Remboursé'
        ];
        
        return $labels[$this->payment_status] ?? $this->payment_status;
    }
    
    /**
     * Formater le montant total
     */
    public function getFormattedTotal() {
        return number_format($this->total_amount, 0, ',', ' ') . ' F XOF';
    }
    
    /**
     * Vérifier si la commande peut être annulée
     */
    public function canBeCancelled() {
        return in_array($this->status, ['pending', 'confirmed']);
    }
    
    /**
     * Vérifier si la commande peut être expédiée
     */
    public function canBeShipped() {
        return $this->status === 'confirmed' && $this->payment_status === 'paid';
    }
    
    /**
     * Convertir en tableau
     */
    public function toArray() {
        return [
            'id' => $this->id,
            'customer_id' => $this->customer_id,
            'total_amount' => $this->total_amount,
            'formatted_total' => $this->getFormattedTotal(),
            'status' => $this->status,
            'status_label' => $this->getStatusLabel(),
            'payment_method' => $this->payment_method,
            'payment_status' => $this->payment_status,
            'payment_status_label' => $this->getPaymentStatusLabel(),
            'shipping_address' => $this->shipping_address,
            'tracking_number' => $this->tracking_number,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'can_be_cancelled' => $this->canBeCancelled(),
            'can_be_shipped' => $this->canBeShipped()
        ];
    }
}
?>

