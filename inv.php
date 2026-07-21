<?php

$host = "localhost";
$dbname = "kamau inventory";
$user = "root";
$password = "";

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $user,
        $password
    );

    echo "✅ Connected Successfully!<br><br>";

    $stmt = $pdo->query("SELECT id, sku, name, stock, price FROM inventory LIMIT 200");

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "ID: {$row['id']} | ";
        echo "SKU: {$row['sku']} | ";
        echo "Name: {$row['name']} | ";
        echo "Stock: {$row['stock']} | ";
        echo "Price: KSh {$row['price']}<br>";
    }

} catch (PDOException $e) {
    echo "❌ Connection Failed: " . $e->getMessage();
}