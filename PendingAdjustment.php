<?php
namespace Kamau\Models;

use Kamau\Config\Database;
use PDO;

class PendingAdjustment {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function getAll($status = null) {
        $sql = "SELECT * FROM pending_adjustments";
        $params = [];
        
        if ($status) {
            $sql .= " WHERE status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO pending_adjustments (
                item, inventory_id, requested_stock, current_stock, 
                reason, requested_by, requested_by_name
            ) VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        return $stmt->execute([
            $data['item'],
            $data['inventory_id'],
            $data['requested_stock'],
            $data['current_stock'],
            $data['reason'],
            $data['requested_by'],
            $data['requested_by_name']
        ]);
    }
    
    public function approve($id, $approvedBy) {
        $stmt = $this->db->prepare("
            UPDATE pending_adjustments 
            SET status = 'Approved', approved_by = ?, approved_at = NOW() 
            WHERE id = ? AND status = 'Pending'
        ");
        return $stmt->execute([$approvedBy, $id]);
    }
    
    public function reject($id, $approvedBy) {
        $stmt = $this->db->prepare("
            UPDATE pending_adjustments 
            SET status = 'Rejected', approved_by = ?, approved_at = NOW() 
            WHERE id = ? AND status = 'Pending'
        ");
        return $stmt->execute([$approvedBy, $id]);
    }
    
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM pending_adjustments WHERE id = ?");
        return $stmt->execute([$id]);
    }
}