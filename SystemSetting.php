<?php
namespace Kamau\Models;

use Kamau\Config\Database;
use PDO;

class SystemSetting {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function get($key) {
        $stmt = $this->db->prepare("SELECT * FROM system_settings WHERE `key` = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetch();
        
        if ($result) {
            $result['value'] = json_decode($result['value'], true);
        }
        
        return $result;
    }
    
    public function getAll() {
        $stmt = $this->db->prepare("SELECT * FROM system_settings");
        $stmt->execute();
        $results = $stmt->fetchAll();
        
        foreach ($results as &$result) {
            $result['value'] = json_decode($result['value'], true);
        }
        
        return $results;
    }
    
    public function set($key, $value, $description = null) {
        $jsonValue = json_encode($value);
        
        $stmt = $this->db->prepare("
            INSERT INTO system_settings (`key`, `value`, description) 
            VALUES (?, ?, ?) 
            ON DUPLICATE KEY UPDATE 
            `value` = VALUES(`value`), 
            updated_at = CURRENT_TIMESTAMP,
            description = COALESCE(VALUES(description), description)
        ");
        
        return $stmt->execute([$key, $jsonValue, $description]);
    }
}