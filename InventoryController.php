<?php
namespace Kamau\Controllers;

use Kamau\Models\Inventory;
use Kamau\Models\ActivityLog;
use Kamau\Helpers;

class InventoryController {
    private $inventoryModel;
    private $logModel;
    
    public function __construct() {
        $this->inventoryModel = new Inventory();
        $this->logModel = new ActivityLog();
    }
    
    public function index() {
        $search = $_GET['search'] ?? null;
        $items = $this->inventoryModel->getAll($search);
        return Helpers\successResponse($items);
    }
    
    public function show($id) {
        $item = $this->inventoryModel->findById($id);
        if (!$item) {
            return Helpers\errorResponse('Inventory item not found', 404);
        }
        return Helpers\successResponse($item);
    }
    
    public function create() {
        $input = Helpers\getInput();
        
        $errors = Helpers\validateRequired($input, ['sku', 'name', 'location', 'price']);
        if (!empty($errors)) {
            return Helpers\errorResponse('Validation failed', 400, $errors);
        }
        
        // Check if SKU already exists
        if ($this->inventoryModel->findBySku($input['sku'])) {
            return Helpers\errorResponse('SKU already exists', 409);
        }
        
        $result = $this->inventoryModel->create($input);
        if (!$result) {
            return Helpers\errorResponse('Failed to create inventory item', 500);
        }
        
        $this->logModel->create([
            'user' => $_REQUEST['user'] ?? 'System',
            'user_id' => $_REQUEST['user_id'] ?? null,
            'action' => 'Created inventory item',
            'details' => ['sku' => $input['sku'], 'name' => $input['name']]
        ]);
        
        return Helpers\successResponse(null, 'Inventory item created successfully');
    }
    
    public function update($id) {
        $input = Helpers\getInput();
        
        $item = $this->inventoryModel->findById($id);
        if (!$item) {
            return Helpers\errorResponse('Inventory item not found', 404);
        }
        
        $result = $this->inventoryModel->update($id, $input);
        if (!$result) {
            return Helpers\errorResponse('Failed to update inventory item', 500);
        }
        
        $this->logModel->create([
            'user' => $_REQUEST['user'] ?? 'System',
            'user_id' => $_REQUEST['user_id'] ?? null,
            'action' => 'Updated inventory item',
            'details' => ['id' => $id, 'name' => $item['name']]
        ]);
        
        return Helpers\successResponse(null, 'Inventory item updated successfully');
    }
    
    public function updateStock($id) {
        $input = Helpers\getInput();
        
        if (!isset($input['quantity'])) {
            return Helpers\errorResponse('Quantity is required', 400);
        }
        
        $item = $this->inventoryModel->findById($id);
        if (!$item) {
            return Helpers\errorResponse('Inventory item not found', 404);
        }
        
        $result = $this->inventoryModel->updateStock($id, $input['quantity']);
        if (!$result) {
            return Helpers\errorResponse('Failed to update stock', 500);
        }
        
        $this->logModel->create([
            'user' => $_REQUEST['user'] ?? 'System',
            'user_id' => $_REQUEST['user_id'] ?? null,
            'action' => 'Updated stock',
            'details' => [
                'item' => $item['name'],
                'change' => $input['quantity'],
                'new_stock' => $item['stock'] + $input['quantity']
            ]
        ]);
        
        return Helpers\successResponse(null, 'Stock updated successfully');
    }
    
    public function lowStock() {
        $items = $this->inventoryModel->getLowStockItems();
        return Helpers\successResponse($items);
    }
    
    public function topSelling() {
        $limit = $_GET['limit'] ?? 5;
        $items = $this->inventoryModel->getTopSelling($limit);
        return Helpers\successResponse($items);
    }
    
    public function delete($id) {
        $item = $this->inventoryModel->findById($id);
        if (!$item) {
            return Helpers\errorResponse('Inventory item not found', 404);
        }
        
        $result = $this->inventoryModel->delete($id);
        if (!$result) {
            return Helpers\errorResponse('Failed to delete inventory item', 500);
        }
        
        $this->logModel->create([
            'user' => $_REQUEST['user'] ?? 'System',
            'user_id' => $_REQUEST['user_id'] ?? null,
            'action' => 'Deleted inventory item',
            'details' => ['sku' => $item['sku'], 'name' => $item['name']]
        ]);
        
        return Helpers\successResponse(null, 'Inventory item deleted successfully');
    }
}