<?php
namespace Kamau\Config;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Auth {
    private static $secret;
    
    public static function init() {
        self::$secret = $_ENV['JWT_SECRET'] ?? 'your_super_secret_key';
    }
    
    public static function generateToken($user) {
        $payload = [
            'id' => $user['id'],
            'username' => $user['username'],
            'role' => $user['role'],
            'iat' => time(),
            'exp' => time() + ($_ENV['JWT_EXPIRE'] ?? 604800)
        ];
        
        return JWT::encode($payload, self::$secret, 'HS256');
    }
    
    public static function verifyToken($token) {
        try {
            $decoded = JWT::decode($token, new Key(self::$secret, 'HS256'));
            return (array) $decoded;
        } catch (\Exception $e) {
            return null;
        }
    }
}

Auth::init();