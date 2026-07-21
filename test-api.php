<?php
// Test script for Kamau Auto Spares API
$baseUrl = 'http://localhost:8000/api';

function testApi($url, $method = 'GET', $data = null, $token = null) {
    $ch = curl_init();
    
    $headers = ['Content-Type: application/json'];
    if ($token) {
        $headers[] = "Authorization: Bearer {$token}";
    }
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    } elseif ($method === 'PUT' || $method === 'PATCH') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    } elseif ($method === 'DELETE') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'status' => $httpCode,
        'response' => json_decode($response, true)
    ];
}

echo "🧪 Testing Kamau Auto Spares API\n";
echo "================================\n\n";

// Test login
echo "1️⃣ Testing Login...\n";
$loginResult = testApi($baseUrl . '/auth/login', 'POST', [
    'username' => 'admin',
    'password' => 'admin123'
]);

if ($loginResult['status'] === 200 && isset($loginResult['response']['data']['token'])) {
    $token = $loginResult['response']['data']['token'];
    echo "✅ Login successful. Token obtained.\n\n";
    
    // Test inventory
    echo "2️⃣ Testing Inventory...\n";
    $inventoryResult = testApi($baseUrl . '/inventory', 'GET', null, $token);
    if ($inventoryResult['status'] === 200) {
        echo "✅ Inventory fetched. Items: " . count($inventoryResult['response']['data'] ?? []) . "\n\n";
    }
    
    // Test sales today
    echo "3️⃣ Testing Today's Sales...\n";
    $salesResult = testApi($baseUrl . '/sales/today', 'GET', null, $token);
    if ($salesResult['status'] === 200) {
        echo "✅ Today's sales fetched. Sales: " . count($salesResult['response']['data'] ?? []) . "\n\n";
    }
    
    // Test low stock
    echo "4️⃣ Testing Low Stock Alert...\n";
    $lowStockResult = testApi($baseUrl . '/inventory/low-stock', 'GET', null, $token);
    if ($lowStockResult['status'] === 200) {
        echo "✅ Low stock items fetched. Items: " . count($lowStockResult['response']['data'] ?? []) . "\n\n";
    }
    
    // Test create sale
    echo "5️⃣ Testing Create Sale...\n";
    $saleData = [
        'items' => [
            [
                'inventory_id' => 1,
                'quantity' => 2
            ]
        ],
        'staff_id' => 3,
        'staff_name' => 'Peter Otieno',
        'customer_name' => 'Test Customer',
        'payment_method' => 'Cash'
    ];
    $saleResult = testApi($baseUrl . '/sales', 'POST', $saleData, $token);
    if ($saleResult['status'] === 200) {
        echo "✅ Sale created successfully. Invoice: " . ($saleResult['response']['data']['invoice_number'] ?? 'N/A') . "\n\n";
    }
    
} else {
    echo "❌ Login failed: " . ($loginResult['response']['message'] ?? 'Unknown error') . "\n";
}

echo "================================\n";
echo "✅ API Test Complete!\n";