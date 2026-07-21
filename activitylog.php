<?php
namespace Kamau\Models;

use Kamau\Config\Database;
use PDO;

class ActivityLog {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO activity_logs (user, user_id, action, details, ip_address, user_agent) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        return $stmt->execute([
            $data['user'],
            $data['user_id'] ?? null,
            $data['action'],
            isset($data['details']) ? json_encode($data['details']) : null,
            $data['ip_address'] ?? $_SERVER['REMOTE_ADDR'] ?? null,
            $data['user_agent'] ?? $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);
    }
    
    public function getRecent($limit = 50) {
        $stmt = $this->db->prepare("
            SELECT * FROM activity_logs 
            ORDER BY created_at DESC 
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
    
    public function getByUser($userId, $limit = 20) {
        $stmt = $this->db->prepare("
            SELECT * FROM activity_logs 
            WHERE user_id = ? 
            ORDER BY created_at DESC 
            LIMIT ?
        ");
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll();
    }
    
    public function clear() {
        $stmt = $this->db->prepare("DELETE FROM activity_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY)");
        return $stmt->execute();
    }
    
    public function deleteAll() {
        $stmt = $this->db->prepare("DELETE FROM activity_logs");
        return $stmt->execute();
    }
}