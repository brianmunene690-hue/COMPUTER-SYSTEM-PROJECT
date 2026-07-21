<?php
namespace Kamau\Controllers;

use Kamau\Models\Sales;
use Kamau\Models\Inventory;
use Kamau\Models\ActivityLog;
use Kamau\Helpers;

class SalesController {
    private $salesModel;
    private $inventoryModel;
    private $logModel;
    
    public function __construct() {
        $this->salesModel = new Sales();
        $this->inventoryModel = new Inventory();
        $this->logModel = new ActivityLog();
    }
    
    public function create() {
        $input = Helpers\getInput();
        
        // Validate input
        $errors = Helpers\validateRequired($input, ['items', 'staff_id', 'staff_name']);
        if (!empty($errors)) {
            return Helpers\errorResponse('Validation failed', 400, $errors);
        }
        
        if (!is_array($input['items']) || empty($input['items'])) {
            return Helpers\errorResponse('At least one item is required', 400);
        }
        
        // Calculate totals
        $subtotal = 0;
        $items = [];
        
        foreach ($input['items'] as $item) {
            // Validate item
            $errors = Helpers\validateRequired($item, ['inventory_id', 'quantity']);
            if (!empty($errors)) {
                return Helpers\errorResponse('Invalid item data', 400, $errors);
            }
            
            // Get inventory item
            $inventoryItem = $this->inventoryModel->findById($item['inventory_id']);
            if (!$inventoryItem) {
                return Helpers\errorResponse("Inventory item not found: {$item['inventory_id']}", 404);
            }
            
            if ($inventoryItem['stock'] < $item['quantity']) {
                return Helpers\errorResponse("Insufficient stock for: {$inventoryItem['name']}", 400);
            }
            
            $price = $item['price'] ?? $inventoryItem['price'];
            $total = $price * $item['quantity'];
            $subtotal += $total;
            
            $items[] = [
                'inventory_id' => $item['inventory_id'],
                'sku' => $inventoryItem['sku'],
                'name' => $inventoryItem['name'],
                'quantity' => $item['quantity'],
                'price' => $price,
                'total' => $total
            ];
        }
        
        // Calculate tax
        $taxRate = $input['tax_rate'] ?? 0;
        $tax = $subtotal * ($taxRate / 100);
        $total = $subtotal + $tax;
        
        // Create sale
        $saleData = [
            'items' => $items,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total,
            'payment_method' => $input['payment_method'] ?? 'Cash',
            'customer_name' => $input['customer_name'] ?? null,
            'customer_phone' => $input['customer_phone'] ?? null,
            'staff_id' => $input['staff_id'],
            'staff_name' => $input['staff_name']
        ];
        
        try {
            $sale = $this->salesModel->create($saleData);
            
            $this->logModel->create([
                'user' => $input['staff_name'],
                'user_id' => $input['staff_id'],
                'action' => 'Processed sale',
                'details' => [
                    'invoice' => $sale['invoice_number'],
                    'total' => $total,
                    'items' => count($items)
                ]
            ]);
            
            return Helpers\successResponse($sale, 'Sale completed successfully');
            
        } catch (\Exception $e) {
            return Helpers\errorResponse('Failed to process sale: ' . $e->getMessage(), 500);
        }
    }
    
    public function today() {
        $sales = $this->salesModel->getTodaySales();
        return Helpers\successResponse($sales);
    }
    
    public function byDateRange() {
        $startDate = $_GET['start_date'] ?? date('Y-m-d');
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        
        $sales = $this->salesModel->getSalesByDateRange($startDate, $endDate);
        return Helpers\successResponse($sales);
    }
    
    public function show($id) {
        $sale = $this->salesModel->findById($id);
        if (!$sale) {
            return Helpers\errorResponse('Sale not found', 404);
        }
        return Helpers\successResponse($sale);
    }
}