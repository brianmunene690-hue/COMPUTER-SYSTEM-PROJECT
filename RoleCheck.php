<?php
namespace Kamau\Middleware;

use Kamau\Helpers;

class RoleCheck {
    public static function require($roles) {
        $userRole = $_REQUEST['user_role'] ?? null;
        
        if (!$userRole) {
            Helpers\errorResponse('User role not found', 403);
            return false;
        }
        
        if (!in_array($userRole, (array)$roles)) {
            Helpers\errorResponse('Access denied. Required role: ' . implode(' or ', (array)$roles), 403);
            return false;
        }
        
        return true;
    }
}