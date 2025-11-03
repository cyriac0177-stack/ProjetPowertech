<?php
/**
 * Classe Commission - Gestion des commissions
 * Quick Quick Shopping
 */

class Commission {
    private $db;
    private $id;
    private $seller_id;
    private $order_id;
    private $amount;
    private $commission_rate;
    private $commission_amount;
    private $status;
    private $created_at;
    private $updated_at;
    
    public function __construct($data = null) {
        $this->db = getConnection();
        
        if ($data) {
            $this->id = $data['id'] ?? null;
            $this->seller_id = $data['seller_id'] ?? null;
            $this->order_id = $data['order_id'] ?? null;
            $this->amount = $data['amount'] ?? 0;
            $this->commission_rate = $data['commission_rate'] ?? 0;
            $this->commission_amount = $data['commission_amount'] ?? 0;
            $this->status = $data['status'] ?? 'pending';
            $this->created_at = $data['created_at'] ?? null;
            $this->updated_at = $data['updated_at'] ?? null;
        }
    }
    
    // ==================== GETTERS ====================
    
    public function getId() { return $this->id; }
    public function getSellerId() { return $this->seller_id; }
    public function getOrderId() { return $this->order_id; }
    public function getAmount() { return $this->amount; }
    public function getCommissionRate() { return $this->commission_rate; }
    public function getCommissionAmount() { return $this->commission_amount; }
    public function getStatus() { return $this->status; }
    public function getCreatedAt() { return $this->created_at; }
    public function getUpdatedAt() { return $this->updated_at; }
    
    // ==================== SETTERS ====================
    
    public function setStatus($status) { $this->status = $status; }
    
    // ==================== MÉTHODES STATIQUES ====================
    
    /**
     * Créer une nouvelle commission
     */
    public static function create($data) {
        try {
            $sql = "INSERT INTO commissions (seller_id, order_id, amount, commission_rate, commission_amount, status, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, NOW())";
            
            $params = [
                $data['seller_id'],
                $data['order_id'],
                $data['amount'],
                $data['commission_rate'],
                $data['commission_amount'],
                $data['status'] ?? 'pending'
            ];
            
            return insertAndGetId($sql, $params);
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la création de la commission : " . $e->getMessage());
        }
    }
    
    /**
     * Obtenir une commission par ID
     */
    public static function findById($id) {
        $data = fetchOne("SELECT * FROM commissions WHERE id = ?", [$id]);
        return $data ? new self($data) : null;
    }
    
    /**
     * Obtenir les commissions d'un vendeur
     */
    public static function getBySeller($seller_id, $limit = 20, $offset = 0) {
        $commissions = fetchAll("SELECT c.*, o.created_at as order_date, u.name as customer_name 
                                FROM commissions c 
                                JOIN orders o ON c.order_id = o.id 
                                JOIN users u ON o.customer_id = u.id 
                                WHERE c.seller_id = ? 
                                ORDER BY c.created_at DESC 
                                LIMIT ? OFFSET ?", [$seller_id, $limit, $offset]);
        
        return array_map(function($data) {
            return new self($data);
        }, $commissions);
    }
    
    /**
     * Obtenir toutes les commissions avec filtres
     */
    public static function getAll($filters = []) {
        $sql = "SELECT c.*, u.name as seller_name, o.created_at as order_date 
                FROM commissions c 
                JOIN users u ON c.seller_id = u.id 
                JOIN orders o ON c.order_id = o.id 
                WHERE 1=1";
        $params = [];
        
        // Filtres
        if (isset($filters['seller_id'])) {
            $sql .= " AND c.seller_id = ?";
            $params[] = $filters['seller_id'];
        }
        
        if (isset($filters['status'])) {
            $sql .= " AND c.status = ?";
            $params[] = $filters['status'];
        }
        
        if (isset($filters['date_from'])) {
            $sql .= " AND DATE(c.created_at) >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (isset($filters['date_to'])) {
            $sql .= " AND DATE(c.created_at) <= ?";
            $params[] = $filters['date_to'];
        }
        
        // Tri
        $order_by = $filters['order_by'] ?? 'created_at';
        $order_direction = $filters['order_direction'] ?? 'DESC';
        $sql .= " ORDER BY c.{$order_by} {$order_direction}";
        
        // Pagination
        if (isset($filters['limit'])) {
            $sql .= " LIMIT ?";
            $params[] = $filters['limit'];
            
            if (isset($filters['offset'])) {
                $sql .= " OFFSET ?";
                $params[] = $filters['offset'];
            }
        }
        
        $commissions = fetchAll($sql, $params);
        return array_map(function($data) {
            return new self($data);
        }, $commissions);
    }
    
    /**
     * Calculer le taux de commission selon le montant total des ventes
     */
    public static function calculateRate($total_sales) {
        if ($total_sales <= 250000) {
            return 0.01; // 1%
        } elseif ($total_sales <= 500000) {
            return 0.0075; // 0.75%
        } elseif ($total_sales <= 1000000) {
            return 0.005; // 0.5%
        } else {
            return 0.0025; // 0.25%
        }
    }
    
    /**
     * Obtenir les statistiques des commissions
     */
    public static function getStats() {
        $stats = [];
        
        $stats['total_commissions'] = fetchOne("SELECT COUNT(*) as count FROM commissions")['count'];
        $stats['pending_commissions'] = fetchOne("SELECT COUNT(*) as count FROM commissions WHERE status = 'pending'")['count'];
        $stats['paid_commissions'] = fetchOne("SELECT COUNT(*) as count FROM commissions WHERE status = 'paid'")['count'];
        $stats['total_amount'] = fetchOne("SELECT COALESCE(SUM(commission_amount), 0) as total FROM commissions")['total'];
        $stats['pending_amount'] = fetchOne("SELECT COALESCE(SUM(commission_amount), 0) as total FROM commissions WHERE status = 'pending'")['total'];
        $stats['paid_amount'] = fetchOne("SELECT COALESCE(SUM(commission_amount), 0) as total FROM commissions WHERE status = 'paid'")['total'];
        
        return $stats;
    }
    
    /**
     * Obtenir les statistiques des commissions par vendeur
     */
    public static function getStatsBySeller($seller_id) {
        $stats = [];
        
        $stats['total_commissions'] = fetchOne("SELECT COUNT(*) as count FROM commissions WHERE seller_id = ?", [$seller_id])['count'];
        $stats['total_amount'] = fetchOne("SELECT COALESCE(SUM(commission_amount), 0) as total FROM commissions WHERE seller_id = ?", [$seller_id])['total'];
        $stats['pending_amount'] = fetchOne("SELECT COALESCE(SUM(commission_amount), 0) as total FROM commissions WHERE seller_id = ? AND status = 'pending'", [$seller_id])['total'];
        $stats['paid_amount'] = fetchOne("SELECT COALESCE(SUM(commission_amount), 0) as total FROM commissions WHERE seller_id = ? AND status = 'paid'", [$seller_id])['total'];
        
        return $stats;
    }
    
    /**
     * Obtenir le taux de commission actuel d'un vendeur
     */
    public static function getCurrentRate($seller_id) {
        $total_sales = fetchOne("SELECT COALESCE(SUM(oi.price * oi.quantity), 0) as total 
                               FROM orders o 
                               JOIN order_items oi ON o.id = oi.order_id 
                               JOIN products p ON oi.product_id = p.id 
                               WHERE p.seller_id = ? AND o.status = 'completed'", [$seller_id])['total'];
        
        return self::calculateRate($total_sales);
    }
    
    /**
     * Obtenir les commissions en attente de paiement
     */
    public static function getPendingCommissions() {
        $commissions = fetchAll("SELECT c.*, u.name as seller_name 
                               FROM commissions c 
                               JOIN users u ON c.seller_id = u.id 
                               WHERE c.status = 'pending' 
                               ORDER BY c.created_at ASC");
        
        return array_map(function($data) {
            return new self($data);
        }, $commissions);
    }
    
    // ==================== MÉTHODES D'INSTANCE ====================
    
    /**
     * Sauvegarder les modifications
     */
    public function save() {
        if ($this->id) {
            $sql = "UPDATE commissions SET status = ?, updated_at = NOW() WHERE id = ?";
            executeQuery($sql, [$this->status, $this->id]);
        }
        
        return $this;
    }
    
    /**
     * Marquer comme payée
     */
    public function markAsPaid() {
        $this->status = 'paid';
        $this->save();
        return $this;
    }
    
    /**
     * Annuler la commission
     */
    public function cancel() {
        $this->status = 'cancelled';
        $this->save();
        return $this;
    }
    
    /**
     * Obtenir les informations du vendeur
     */
    public function getSeller() {
        return fetchOne("SELECT * FROM users WHERE id = ?", [$this->seller_id]);
    }
    
    /**
     * Obtenir les informations de la commande
     */
    public function getOrder() {
        return fetchOne("SELECT * FROM orders WHERE id = ?", [$this->order_id]);
    }
    
    /**
     * Obtenir le statut formaté
     */
    public function getStatusLabel() {
        $labels = [
            'pending' => 'En attente',
            'paid' => 'Payée',
            'cancelled' => 'Annulée'
        ];
        
        return $labels[$this->status] ?? $this->status;
    }
    
    /**
     * Formater le montant de la commission
     */
    public function getFormattedCommissionAmount() {
        return number_format($this->commission_amount, 0, ',', ' ') . ' F XOF';
    }
    
    /**
     * Formater le montant de la vente
     */
    public function getFormattedAmount() {
        return number_format($this->amount, 0, ',', ' ') . ' F XOF';
    }
    
    /**
     * Formater le taux de commission en pourcentage
     */
    public function getFormattedRate() {
        return number_format($this->commission_rate * 100, 2) . '%';
    }
    
    /**
     * Obtenir le montant net pour le vendeur
     */
    public function getNetAmount() {
        return $this->amount - $this->commission_amount;
    }
    
    /**
     * Formater le montant net
     */
    public function getFormattedNetAmount() {
        return number_format($this->getNetAmount(), 0, ',', ' ') . ' F XOF';
    }
    
    /**
     * Vérifier si la commission peut être payée
     */
    public function canBePaid() {
        return $this->status === 'pending';
    }
    
    /**
     * Vérifier si la commission peut être annulée
     */
    public function canBeCancelled() {
        return $this->status === 'pending';
    }
    
    /**
     * Convertir en tableau
     */
    public function toArray() {
        return [
            'id' => $this->id,
            'seller_id' => $this->seller_id,
            'order_id' => $this->order_id,
            'amount' => $this->amount,
            'formatted_amount' => $this->getFormattedAmount(),
            'commission_rate' => $this->commission_rate,
            'formatted_rate' => $this->getFormattedRate(),
            'commission_amount' => $this->commission_amount,
            'formatted_commission_amount' => $this->getFormattedCommissionAmount(),
            'net_amount' => $this->getNetAmount(),
            'formatted_net_amount' => $this->getFormattedNetAmount(),
            'status' => $this->status,
            'status_label' => $this->getStatusLabel(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'can_be_paid' => $this->canBePaid(),
            'can_be_cancelled' => $this->canBeCancelled()
        ];
    }
}
?>

