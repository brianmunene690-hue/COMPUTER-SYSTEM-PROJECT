<?php
namespace Kamau\Controllers;

use Kamau\Models\User;
use Kamau\Models\Supplier;
use Kamau\Models\ActivityLog;
use Kamau\Models\SystemSetting;
use Kamau\Models\PendingAdjustment;
use Kamau\Helpers;

class AdminController {
    private $userModel;
    private $supplierModel;
    private $logModel;
    private $settingsModel;
    private $adjustmentModel;
    
    public function __construct() {
        $this->userModel = new User();
        $this->supplierModel = new Supplier();
        $this->logModel = new ActivityLog();
        $this->settingsModel = new SystemSetting();
        $this->adjustmentModel = new PendingAdjustment();
    }
    
    // User Management
    public function getUsers() {
        $users = $this->userModel->getAll();
        return Helpers\successResponse($users);
    }
    
    public function createUser() {
        $input = Helpers\getInput();
        
        $errors = Helpers\validateRequired($input, ['username', 'password', 'name', 'role']);
        if (!empty($errors)) {
            return Helpers\errorResponse('Validation failed', 400, $errors);
        }
        
        if ($this->userModel->findByUsername($input['username'])) {
            return Helpers\errorResponse('Username already exists', 409);
        }
        
        $result = $this->userModel->create($input);
        if (!$result) {
            return Helpers\errorResponse('Failed to create user', 500);
        }
        
        $this->logModel->create([
            'user' => $_REQUEST['user'] ?? 'System',
            'user_id' => $_REQUEST['user_id'] ?? null,
            'action' => 'Created user',
            'details' => ['username' => $input['username'], 'role' => $input['role']]
        ]);
        
        return Helpers\successResponse(null, 'User created successfully');
    }
    
    public function updateUser($id) {
        $input = Helpers\getInput();
        
        $user = $this->userModel->findById($id);
        if (!$user) {
            return Helpers\errorResponse('User not found', 404);
        }
        
        $result = $this->userModel->update($id, $input);
        if (!$result) {
            return Helpers\errorResponse('Failed to update user', 500);
        }
        
        $this->logModel->create([
            'user' => $_REQUEST['user'] ?? 'System',
            'user_id' => $_REQUEST['user_id'] ?? null,
            'action' => 'Updated user',
            'details' => ['username' => $user['username']]
        ]);
        
        return Helpers\successResponse(null, 'User updated successfully');
    }
    
    public function deleteUser($id) {
        if ($id == $_REQUEST['user_id']) {
            return Helpers\errorResponse('Cannot delete your own account', 403);
        }
        
        $user = $this->userModel->findById($id);
        if (!$user) {
            return Helpers\errorResponse('User not found', 404);
        }
        
        $result = $this->userModel->delete($id);
        if (!$result) {
            return Helpers\errorResponse('Failed to delete user', 500);
        }
        
        $this->logModel->create([
            'user' => $_REQUEST['user'] ?? 'System',
            'user_id' => $_REQUEST['user_id'] ?? null,
            'action' => 'Deleted user',
            'details' => ['username' => $user['username']]
        ]);
        
        return Helpers\successResponse(null, 'User deleted successfully');
    }
    
    // Supplier Management
    public function getSuppliers() {
        $suppliers = $this->supplierModel->getAll();
        return Helpers\successResponse($suppliers);
    }
    
    public function createSupplier() {
        $input = Helpers\getInput();
        
        $errors = Helpers\validateRequired($input, ['name', 'contact']);
        if (!empty($errors)) {
            return Helpers\errorResponse('Validation failed', 400, $errors);
        }
        
        $result = $this->supplierModel->create($input);
        if (!$result) {
            return Helpers\errorResponse('Failed to create supplier', 500);
        }
        
        $this->logModel->create([
            'user' => $_REQUEST['user'] ?? 'System',
            'user_id' => $_REQUEST['user_id'] ?? null,
            'action' => 'Created supplier',
            'details' => ['name' => $input['name']]
        ]);
        
        return Helpers\successResponse(null, 'Supplier created successfully');
    }
    
    public function updateSupplier($id) {
        $input = Helpers\getInput();
        
        $supplier = $this->supplierModel->findById($id);
        if (!$supplier) {
            return Helpers\errorResponse('Supplier not found', 404);
        }
        
        $result = $this->supplierModel->update($id, $input);
        if (!$result) {
            return Helpers\errorResponse('Failed to update supplier', 500);
        }
        
        $this->logModel->create([
            'user' => $_REQUEST['user'] ?? 'System',
            'user_id' => $_REQUEST['user_id'] ?? null,
            'action' => 'Updated supplier',
            'details' => ['name' => $supplier['name']]
        ]);
        
        return Helpers\successResponse(null, 'Supplier updated successfully');
    }
    
    public function deleteSupplier($id) {
        $supplier = $this->supplierModel->findById($id);
        if (!$supplier) {
            return Helpers\errorResponse('Supplier not found', 404);
        }
        
        $result = $this->supplierModel->delete($id);
        if (!$result) {
            return Helpers\errorResponse('Failed to delete supplier', 500);
        }
        
        $this->logModel->create([
            'user' => $_REQUEST['user'] ?? 'System',
            'user_id' => $_REQUEST['user_id'] ?? null,
            'action' => 'Deleted supplier',
            'details' => ['name' => $supplier['name']]
        ]);
        
        return Helpers\successResponse(null, 'Supplier deleted successfully');
    }
    
    // Settings
    public function getSettings() {
        $settings = $this->settingsModel->getAll();
        return Helpers\successResponse($settings);
    }
    
    public function updateSettings() {
        $input = Helpers\getInput();
        
        foreach ($input as $key => $value) {
            $this->settingsModel->set($key, $value);
        }
        
        $this->logModel->create([
            'user' => $_REQUEST['user'] ?? 'System',
            'user_id' => $_REQUEST['user_id'] ?? null,
            'action' => 'Updated system settings',
            'details' => ['keys' => array_keys($input)]
        ]);
        
        return Helpers\successResponse(null, 'Settings updated successfully');
    }
    
    // Pending Adjustments
    public function getPendingAdjustments() {
        $adjustments = $this->adjustmentModel->getAll('Pending');
        return Helpers\successResponse($adjustments);
    }
    
    public function approveAdjustment($id) {
        $result = $this->adjustmentModel->approve($id, $_REQUEST['user_id']);
        if (!$result) {
            return Helpers\errorResponse('Failed to approve adjustment', 500);
        }
        
        $this->logModel->create([
            'user' => $_REQUEST['user'] ?? 'System',
            'user_id' => $_REQUEST['user_id'] ?? null,
            'action' => 'Approved adjustment',
            'details' => ['adjustment_id' => $id]
        ]);
        
        return Helpers\successResponse(null, 'Adjustment approved successfully');
    }
    
    public function rejectAdjustment($id) {
        $result = $this->adjustmentModel->reject($id, $_REQUEST['user_id']);
        if (!$result) {
            return Helpers\errorResponse('Failed to reject adjustment', 500);
        }
        
        $this->logModel->create([
            'user' => $_REQUEST['user'] ?? 'System',
            'user_id' => $_REQUEST['user_id'] ?? null,
            'action' => 'Rejected adjustment',
            'details' => ['adjustment_id' => $id]
        ]);
        
        return Helpers\successResponse(null, 'Adjustment rejected');
    }
    
    // Activity Logs
    public function getActivityLogs() {
        $limit = $_GET['limit'] ?? 50;
        $logs = $this->logModel->getRecent($limit);
        return Helpers\successResponse($logs);
    }
    
    public function clearActivityLogs() {
        $result = $this->logModel->deleteAll();
        if (!$result) {
            return Helpers\errorResponse('Failed to clear logs', 500);
        }
        
        $this->logModel->create([
            'user' => $_REQUEST['user'] ?? 'System',
            'user_id' => $_REQUEST['user_id'] ?? null,
            'action' => 'Cleared all activity logs'
        ]);
        
        return Helpers\successResponse(null, 'Activity logs cleared');
    }
}