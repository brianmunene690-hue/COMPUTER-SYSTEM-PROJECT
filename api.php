<?php
namespace Kamau\Routes;

use Kamau\Controllers\AuthController;
use Kamau\Controllers\InventoryController;
use Kamau\Controllers\SalesController;
use Kamau\Controllers\AdminController;
use Kamau\Controllers\ReportsController;
use Kamau\Middleware\AuthMiddleware;
use Kamau\Middleware\RoleCheck;

class Router {
    private static $routes = [];
    
    public static function add($method, $path, $controller, $action, $middleware = null) {
        self::$routes[] = [
            'method' => $method,
            'path' => $path,
            'controller' => $controller,
            'action' => $action,
            'middleware' => $middleware
        ];
    }
    
    public static function dispatch($method, $path) {
        foreach (self::$routes as $route) {
            $pattern = preg_replace('/\{[^\}]+\}/', '([^/]+)', $route['path']);
            $pattern = '#^' . $pattern . '$#';
            
            if ($route['method'] === $method && preg_match($pattern, $path, $matches)) {
                array_shift($matches);
                
                // Apply middleware if exists
                if ($route['middleware']) {
                    if (!$route['middleware']()) {
                        return;
                    }
                }
                
                $controller = new $route['controller']();
                $action = $route['action'];
                
                if (empty($matches)) {
                    $controller->$action();
                } else {
                    $controller->$action($matches[0]);
                }
                return;
            }
        }
        
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Route not found'
        ]);
    }
}

// Public routes
Router::add('POST', '/api/auth/login', AuthController::class, 'login');

// Protected routes with role checks
$authenticate = function() {
    return \Kamau\Middleware\AuthMiddleware::handle();
};

$adminOnly = function() {
    if (!\Kamau\Middleware\AuthMiddleware::handle()) return false;
    return \Kamau\Middleware\RoleCheck::require('admin');
};

$managerPlus = function() {
    if (!\Kamau\Middleware\AuthMiddleware::handle()) return false;
    return \Kamau\Middleware\RoleCheck::require(['admin', 'manager']);
};

$authAny = function() {
    if (!\Kamau\Middleware\AuthMiddleware::handle()) return false;
    return \Kamau\Middleware\RoleCheck::require(['admin', 'manager', 'sales', 'inventory']);
};

// Auth routes
Router::add('GET', '/api/auth/me', AuthController::class, 'me', $authenticate);
Router::add('POST', '/api/auth/logout', AuthController::class, 'logout', $authenticate);

// Inventory routes (all authenticated users)
Router::add('GET', '/api/inventory', InventoryController::class, 'index', $authenticate);
Router::add('GET', '/api/inventory/{id}', InventoryController::class, 'show', $authenticate);
Router::add('GET', '/api/inventory/low-stock', InventoryController::class, 'lowStock', $authenticate);
Router::add('GET', '/api/inventory/top-selling', InventoryController::class, 'topSelling', $authenticate);
Router::add('POST', '/api/inventory', InventoryController::class, 'create', $managerPlus);
Router::add('PUT', '/api/inventory/{id}', InventoryController::class, 'update', $managerPlus);
Router::add('PATCH', '/api/inventory/{id}/stock', InventoryController::class, 'updateStock', $managerPlus);
Router::add('DELETE', '/api/inventory/{id}', InventoryController::class, 'delete', $adminOnly);

// Sales routes
Router::add('POST', '/api/sales', SalesController::class, 'create', $authenticate);
Router::add('GET', '/api/sales/today', SalesController::class, 'today', $authenticate);
Router::add('GET', '/api/sales/date-range', SalesController::class, 'byDateRange', $authenticate);
Router::add('GET', '/api/sales/{id}', SalesController::class, 'show', $authenticate);

// Admin routes
Router::add('GET', '/api/admin/users', AdminController::class, 'getUsers', $adminOnly);
Router::add('POST', '/api/admin/users', AdminController::class, 'createUser', $adminOnly);
Router::add('PUT', '/api/admin/users/{id}', AdminController::class, 'updateUser', $adminOnly);
Router::add('DELETE', '/api/admin/users/{id}', AdminController::class, 'deleteUser', $adminOnly);

Router::add('GET', '/api/admin/suppliers', AdminController::class, 'getSuppliers', $adminOnly);
Router::add('POST', '/api/admin/suppliers', AdminController::class, 'createSupplier', $adminOnly);
Router::add('PUT', '/api/admin/suppliers/{id}', AdminController::class, 'updateSupplier', $adminOnly);
Router::add('DELETE', '/api/admin/suppliers/{id}', AdminController::class, 'deleteSupplier', $adminOnly);

Router::add('GET', '/api/admin/settings', AdminController::class, 'getSettings', $adminOnly);
Router::add('PUT', '/api/admin/settings', AdminController::class, 'updateSettings', $adminOnly);

Router::add('GET', '/api/admin/adjustments', AdminController::class, 'getPendingAdjustments', $adminOnly);
Router::add('POST', '/api/admin/adjustments/{id}/approve', AdminController::class, 'approveAdjustment', $adminOnly);
Router::add('POST', '/api/admin/adjustments/{id}/reject', AdminController::class, 'rejectAdjustment', $adminOnly);

Router::add('GET', '/api/admin/activity-logs', AdminController::class, 'getActivityLogs', $adminOnly);
Router::add('DELETE', '/api/admin/activity-logs', AdminController::class, 'clearActivityLogs', $adminOnly);

// Reports routes
Router::add('GET', '/api/reports/sales-summary', ReportsController::class, 'salesSummary', $managerPlus);
Router::add('GET', '/api/reports/inventory-valuation', ReportsController::class, 'inventoryValuation', $managerPlus);
Router::add('GET', '/api/reports/fast-slow-moving', ReportsController::class, 'fastSlowMoving', $managerPlus);
Router::add('GET', '/api/reports/employee-activity', ReportsController::class, 'employeeActivity', $adminOnly);