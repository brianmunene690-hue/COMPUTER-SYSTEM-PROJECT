<?php
namespace Kamau\Middleware;

use Kamau\Config\Auth;
use Kamau\Helpers;

class AuthMiddleware {
    public static function handle() {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? '';
        
        if (empty($authHeader)) {
            Helpers\errorResponse('Authorization token required', 401);
            return false;
        }
        
        $token = str_replace('Bearer ', '', $authHeader);
        $decoded = Auth::verifyToken($token);
        
        if (!$decoded) {
            Helpers\errorResponse('Invalid or expired token', 401);
            return false;
        }
        
        // Store user data in request
        $_REQUEST['user_id'] = $decoded['id'];
        $_REQUEST['user'] = $decoded['username'];
        $_REQUEST['user_role'] = $decoded['role'];
        
        return true;
    }
}