<?php
namespace Kamau\Models;

use Kamau\Config\Database;
use PDO;

class Inventory {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function getAll($search = null) {
        $sql = "SELECT i.*, s.name as supplier_name 
                FROM inventory i 
                LEFT JOIN suppliers s ON i.supplier_id = s.id 
                WHERE i.is_active = 1";
        $params = [];
        
        if ($search) {
            $sql .= " AND (i.sku LIKE ? OR i.name LIKE ?)";
            $params = ["%{$search}%", "%{$search}%"];
        }
        
        $sql .= " ORDER BY i.name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function findById($id) {
        $stmt = $this->db->prepare("
            SELECT i.*, s.name as supplier_name 
            FROM inventory i 
            LEFT JOIN suppliers s ON i.supplier_id = s.id 
            WHERE i.id = ? AND i.is_active = 1
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function findBySku($sku) {
        $stmt = $this->db->prepare("SELECT * FROM inventory WHERE sku = ?");
        $stmt->execute([$sku]);
        return $stmt->fetch();
    }
    
    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO inventory (
                sku, name, description, category, stock, location, 
                price, cost_price, reorder_level, compatibility, supplier_id
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        return $stmt->execute([
            $data['sku'],
            $data['name'],
            $data['description'] ?? null,
            $data['category'] ?? 'Other',
            $data['stock'] ?? 0,
            $data['location'],
            $data['price'],
            $data['cost_price'] ?? null,
            $data['reorder_level'] ?? 5,
            $data['compatibility'] ?? null,
            $data['supplier_id'] ?? null
        ]);
    }
    
    public function update($id, $data) {
        $fields = [];
        $params = [];
        
        $allowedFields = ['name', 'description', 'category', 'stock', 'location', 
                         'price', 'cost_price', 'reorder_level', 'compatibility', 
                         'supplier_id', 'is_active'];
        
        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "{$field} = ?";
                $params[] = $data[$field];
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $params[] = $id;
        $sql = "UPDATE inventory SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    
    public function updateStock($id, $quantity) {
        $stmt = $this->db->prepare("UPDATE inventory SET stock = stock + ? WHERE id = ? AND stock + ? >= 0");
        return $stmt->execute([$quantity, $id, $quantity]);
    }
    
    public function incrementSoldCount($id, $quantity = 1) {
        $stmt = $this->db->prepare("UPDATE inventory SET sold_count = sold_count + ? WHERE id = ?");
        return $stmt->execute([$quantity, $id]);
    }
    
    public function getLowStockItems() {
        $stmt = $this->db->prepare("
            SELECT * FROM inventory 
            WHERE stock <= reorder_level AND is_active = 1 
            ORDER BY stock ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getTopSelling($limit = 5) {
        $stmt = $this->db->prepare("
            SELECT * FROM inventory 
            WHERE is_active = 1 
            ORDER BY sold_count DESC 
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
    
    public function delete($id) {
        $stmt = $this->db->prepare("UPDATE inventory SET is_active = 0 WHERE id = ?");
        return $stmt->execute([$id]);
    }
}