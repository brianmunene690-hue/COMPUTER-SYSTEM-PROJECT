<?php
namespace Kamau\Controllers;

use Kamau\Config\Auth;
use Kamau\Models\User;
use Kamau\Models\ActivityLog;
use Kamau\Helpers;

class AuthController {
    private $userModel;
    private $logModel;
    
    public function __construct() {
        $this->userModel = new User();
        $this->logModel = new ActivityLog();
    }
    
    public function login() {
        $input = Helpers\getInput();
        
        // Validate input
        $errors = Helpers\validateRequired($input, ['username', 'password']);
        if (!empty($errors)) {
            return Helpers\errorResponse('Validation failed', 400, $errors);
        }
        
        // Find user
        $user = $this->userModel->findByUsername($input['username']);
        if (!$user) {
            return Helpers\errorResponse('Invalid credentials', 401);
        }
        
        // Verify password
        if (!$this->userModel->verifyPassword($input['password'], $user['password'])) {
            return Helpers\errorResponse('Invalid credentials', 401);
        }
        
        // Update last login
        $this->userModel->updateLastLogin($user['id']);
        
        // Generate token
        $token = Auth::generateToken($user);
        
        // Log activity
        $this->logModel->create([
            'user' => $user['username'],
            'user_id' => $user['id'],
            'action' => 'User logged in',
            'details' => ['role' => $user['role']]
        ]);
        
        // Return user data (without password)
        unset($user['password']);
        
        return Helpers\successResponse([
            'token' => $token,
            'user' => $user
        ], 'Login successful');
    }
    
    public function me() {
        $userId = $_REQUEST['user_id'] ?? null;
        
        if (!$userId) {
            return Helpers\errorResponse('User not authenticated', 401);
        }
        
        $user = $this->userModel->findById($userId);
        if (!$user) {
            return Helpers\errorResponse('User not found', 404);
        }
        
        return Helpers\successResponse($user);
    }
    
    public function logout() {
        $userId = $_REQUEST['user_id'] ?? null;
        $username = $_REQUEST['user'] ?? 'Unknown';
        
        if ($userId) {
            $this->logModel->create([
                'user' => $username,
                'user_id' => $userId,
                'action' => 'User logged out'
            ]);
        }
        
        return Helpers\successResponse(null, 'Logout successful');
    }
}