<?php
namespace Kamau\Models;

use Kamau\Config\Database;
use PDO;

class Sales {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function create($data) {
        $this->db->beginTransaction();
        
        try {
            // Generate invoice number
            $invoiceNumber = $this->generateInvoiceNumber();
            
            // Insert sale
            $stmt = $this->db->prepare("
                INSERT INTO sales (
                    invoice_number, subtotal, tax, total, payment_method,
                    customer_name, customer_phone, staff_id, staff_name
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $invoiceNumber,
                $data['subtotal'],
                $data['tax'] ?? 0,
                $data['total'],
                $data['payment_method'] ?? 'Cash',
                $data['customer_name'] ?? null,
                $data['customer_phone'] ?? null,
                $data['staff_id'],
                $data['staff_name']
            ]);
            
            $saleId = $this->db->lastInsertId();
            
            // Insert sale items and update inventory
            $inventoryModel = new Inventory();
            
            foreach ($data['items'] as $item) {
                // Insert sale item
                $stmt = $this->db->prepare("
                    INSERT INTO sales_items (
                        sale_id, inventory_id, sku, name, quantity, price, total
                    ) VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $saleId,
                    $item['inventory_id'],
                    $item['sku'],
                    $item['name'],
                    $item['quantity'],
                    $item['price'],
                    $item['total']
                ]);
                
                // Update inventory stock
                $inventoryModel->updateStock($item['inventory_id'], -$item['quantity']);
                $inventoryModel->incrementSoldCount($item['inventory_id'], $item['quantity']);
            }
            
            $this->db->commit();
            return $this->findById($saleId);
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    public function findById($id) {
        $stmt = $this->db->prepare("
            SELECT s.*, u.username as staff_username 
            FROM sales s 
            LEFT JOIN users u ON s.staff_id = u.id 
            WHERE s.id = ?
        ");
        $stmt->execute([$id]);
        $sale = $stmt->fetch();
        
        if ($sale) {
            $sale['items'] = $this->getItemsBySaleId($id);
        }
        
        return $sale;
    }
    
    public function getItemsBySaleId($saleId) {
        $stmt = $this->db->prepare("SELECT * FROM sales_items WHERE sale_id = ?");
        $stmt->execute([$saleId]);
        return $stmt->fetchAll();
    }
    
    public function getTodaySales() {
        $stmt = $this->db->prepare("
            SELECT s.*, u.username as staff_username 
            FROM sales s 
            LEFT JOIN users u ON s.staff_id = u.id 
            WHERE DATE(s.created_at) = CURDATE() 
            ORDER BY s.created_at DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getSalesByDateRange($startDate, $endDate) {
        $stmt = $this->db->prepare("
            SELECT s.*, u.username as staff_username 
            FROM sales s 
            LEFT JOIN users u ON s.staff_id = u.id 
            WHERE DATE(s.created_at) BETWEEN ? AND ? 
            ORDER BY s.created_at DESC
        ");
        $stmt->execute([$startDate, $endDate]);
        return $stmt->fetchAll();
    }
    
    public function getStaffSales($staffId, $startDate = null, $endDate = null) {
        $sql = "SELECT s.* FROM sales s WHERE s.staff_id = ?";
        $params = [$staffId];
        
        if ($startDate && $endDate) {
            $sql .= " AND DATE(s.created_at) BETWEEN ? AND ?";
            $params[] = $startDate;
            $params[] = $endDate;
        }
        
        $sql .= " ORDER BY s.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    private function generateInvoiceNumber() {
        $year = date('Y');
        $month = date('m');
        
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count FROM sales 
            WHERE YEAR(created_at) = ? AND MONTH(created_at) = ?
        ");
        $stmt->execute([$year, $month]);
        $result = $stmt->fetch();
        $count = ($result['count'] ?? 0) + 1;
        
        return sprintf("INV-%s%s-%04d", $year, $month, $count);
    }
}