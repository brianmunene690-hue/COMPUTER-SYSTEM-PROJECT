<?php
// Database connection test script for Kamau Auto Spares
// Place this file in your project root and access it via browser

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set header for better display
header('Content-Type: text/html; charset=utf-8');

// Database configuration
$host = 'localhost';
$dbname = 'kamau inventory';
$username = 'root';
$password = '';

// Define colors for CLI-like output
$colors = [
    'green' => "\033[32m",
    'red' => "\033[31m",
    'yellow' => "\033[33m",
    'blue' => "\033[34m",
    'reset' => "\033[0m"
];

// HTML output for better visibility
function printTestResult($message, $status = 'info') {
    $statusColors = [
        'success' => '#28a745',
        'error' => '#dc3545',
        'warning' => '#ffc107',
        'info' => '#17a2b8'
    ];
    
    $color = $statusColors[$status] ?? '#17a2b8';
    $icon = [
        'success' => '✅',
        'error' => '❌',
        'warning' => '⚠️',
        'info' => 'ℹ️'
    ][$status] ?? 'ℹ️';
    
    echo "<div style='padding: 10px; margin: 5px 0; border-left: 4px solid $color; background: #f8f9fa; border-radius: 4px;'>";
    echo "<span style='font-size: 18px;'>$icon</span>";
    echo "<span style='margin-left: 10px; font-family: monospace;'>$message</span>";
    echo "</div>";
}

// Start HTML output
echo "<!DOCTYPE html>";
echo "<html lang='en'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Database Connection Test - Kamau Auto Spares</title>";
echo "<style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: #0f172a;
        color: #e2e8f0;
        margin: 0;
        padding: 20px;
    }
    .container {
        max-width: 900px;
        margin: 0 auto;
        background: #1e293b;
        border-radius: 12px;
        padding: 30px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.3);
        border: 1px solid #334155;
    }
    h1 {
        color: #f59e0b;
        margin-top: 0;
        border-bottom: 2px solid #334155;
        padding-bottom: 15px;
    }
    .test-section {
        background: #0f172a;
        border-radius: 8px;
        padding: 20px;
        margin: 20px 0;
        border: 1px solid #2d3a5e;
    }
    .test-section h3 {
        color: #94a3b8;
        margin-top: 0;
        font-weight: normal;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-size: 14px;
    }
    .success { color: #10b981; }
    .error { color: #ef4444; }
    .warning { color: #f59e0b; }
    .info { color: #60a5fa; }
    .summary {
        background: #0f172a;
        border-radius: 8px;
        padding: 20px;
        margin-top: 20px;
        border: 2px solid #334155;
    }
    .summary h2 {
        color: #f59e0b;
        margin-top: 0;
    }
    .badge {
        display: inline-block;
        padding: 3px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: bold;
    }
    .badge-success { background: #10b981; color: #fff; }
    .badge-error { background: #ef4444; color: #fff; }
    .badge-warning { background: #f59e0b; color: #000; }
    .badge-info { background: #3b82f6; color: #fff; }
    pre {
        background: #0f172a;
        padding: 15px;
        border-radius: 6px;
        overflow-x: auto;
        border: 1px solid #2d3a5e;
        font-size: 13px;
    }
    .server-info {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 10px;
        margin: 15px 0;
    }
    .server-info-item {
        background: #0f172a;
        padding: 10px;
        border-radius: 6px;
        border: 1px solid #2d3a5e;
    }
    .server-info-item label {
        color: #94a3b8;
        font-size: 12px;
        display: block;
        margin-bottom: 5px;
    }
    .server-info-item value {
        color: #f8fafc;
        font-family: monospace;
        font-size: 14px;
    }
</style>";
echo "</head>";
echo "<body>";
echo "<div class='container'>";
echo "<h1>🔧 Kamau Auto Spares - Database Connection Test</h1>";
echo "<p style='color: #94a3b8;'>Testing connection to MySQL database and verifying schema</p>";

// Start tests
echo "<div class='test-section'>";
echo "<h3>📊 Test Results</h3>";

// Test 1: PHP Version
printTestResult("PHP Version: " . phpversion(), 'info');

// Test 2: MySQL extension
if (extension_loaded('pdo_mysql')) {
    printTestResult("PDO MySQL extension is loaded", 'success');
} else {
    printTestResult("PDO MySQL extension is NOT loaded", 'error');
}

// Test 3: Database Connection
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    printTestResult("✅ Successfully connected to database: $dbname at $host", 'success');
    $connectionSuccess = true;
} catch(PDOException $e) {
    printTestResult("❌ Connection failed: " . $e->getMessage(), 'error');
    $connectionSuccess = false;
}

// If connection successful, run additional tests
if ($connectionSuccess) {
    echo "<div class='test-section'>";
    echo "<h3>🗄️ Database Information</h3>";
    
    // Server info
    echo "<div class='server-info'>";
    echo "<div class='server-info-item'><label>MySQL Server Version</label><value>" . $pdo->getAttribute(PDO::ATTR_SERVER_VERSION) . "</value></div>";
    echo "<div class='server-info-item'><label>Client Version</label><value>" . $pdo->getAttribute(PDO::ATTR_CLIENT_VERSION) . "</value></div>";
    echo "<div class='server-info-item'><label>Connection Status</label><value><span class='badge badge-success'>Connected</span></value></div>";
    echo "</div>";
    
    // Test 4: List all tables
    try {
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (count($tables) > 0) {
            printTestResult("Found " . count($tables) . " tables in database", 'success');
            
            // Check for required tables
            $requiredTables = ['users', 'inventory', 'sales', 'suppliers', 'activity_logs'];
            $missingTables = [];
            $existingTables = [];
            
            foreach ($requiredTables as $required) {
                if (in_array($required, $tables)) {
                    $existingTables[] = $required;
                    printTestResult("✅ Table '$required' exists", 'success');
                } else {
                    $missingTables[] = $required;
                    printTestResult("❌ Table '$required' is missing", 'error');
                }
            }
            
            // Show all tables
            echo "<div style='margin-top: 15px;'>";
            echo "<details>";
            echo "<summary style='cursor: pointer; color: #94a3b8;'>📋 View all tables</summary>";
            echo "<div style='margin-top: 10px;'>";
            foreach ($tables as $table) {
                echo "<div style='padding: 5px; font-family: monospace; color: #cbd5e1;'>📄 $table</div>";
            }
            echo "</div>";
            echo "</details>";
            echo "</div>";
            
        } else {
            printTestResult("No tables found in database", 'warning');
        }
    } catch(PDOException $e) {
        printTestResult("Error listing tables: " . $e->getMessage(), 'error');
    }
    
    // Test 5: Check each table structure
    echo "<div class='test-section'>";
    echo "<h3>📋 Table Structure Verification</h3>";
    
    foreach (['users', 'inventory', 'sales', 'suppliers', 'activity_logs'] as $tableName) {
        if (in_array($tableName, $existingTables ?? [])) {
            try {
                $stmt = $pdo->query("DESCRIBE $tableName");
                $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo "<details style='margin: 10px 0;'>";
                echo "<summary style='cursor: pointer; color: #60a5fa;'>📊 Table: $tableName (" . count($columns) . " columns)</summary>";
                echo "<div style='margin-top: 10px;'>";
                echo "<table style='width: 100%; border-collapse: collapse; font-size: 13px;'>";
                echo "<thead><tr style='background: #0f172a;'>";
                echo "<th style='padding: 8px; text-align: left; border-bottom: 2px solid #334155; color: #f59e0b;'>Field</th>";
                echo "<th style='padding: 8px; text-align: left; border-bottom: 2px solid #334155; color: #f59e0b;'>Type</th>";
                echo "<th style='padding: 8px; text-align: left; border-bottom: 2px solid #334155; color: #f59e0b;'>Null</th>";
                echo "<th style='padding: 8px; text-align: left; border-bottom: 2px solid #334155; color: #f59e0b;'>Key</th>";
                echo "<th style='padding: 8px; text-align: left; border-bottom: 2px solid #334155; color: #f59e0b;'>Default</th>";
                echo "<th style='padding: 8px; text-align: left; border-bottom: 2px solid #334155; color: #f59e0b;'>Extra</th>";
                echo "</tr></thead>";
                echo "<tbody>";
                foreach ($columns as $col) {
                    echo "<tr>";
                    echo "<td style='padding: 8px; border-bottom: 1px solid #2d3a5e; font-family: monospace;'>" . htmlspecialchars($col['Field']) . "</td>";
                    echo "<td style='padding: 8px; border-bottom: 1px solid #2d3a5e; font-family: monospace;'>" . htmlspecialchars($col['Type']) . "</td>";
                    echo "<td style='padding: 8px; border-bottom: 1px solid #2d3a5e;'>" . htmlspecialchars($col['Null']) . "</td>";
                    echo "<td style='padding: 8px; border-bottom: 1px solid #2d3a5e;'>" . htmlspecialchars($col['Key']) . "</td>";
                    echo "<td style='padding: 8px; border-bottom: 1px solid #2d3a5e; font-family: monospace;'>" . htmlspecialchars($col['Default'] ?? 'NULL') . "</td>";
                    echo "<td style='padding: 8px; border-bottom: 1px solid #2d3a5e; font-family: monospace;'>" . htmlspecialchars($col['Extra']) . "</td>";
                    echo "</tr>";
                }
                echo "</tbody></table>";
                echo "</div>";
                echo "</details>";
                
                printTestResult("✅ Table '$tableName' structure verified", 'success');
            } catch(PDOException $e) {
                printTestResult("Error checking table $tableName: " . $e->getMessage(), 'error');
            }
        }
    }
    echo "</div>";
    
    // Test 6: Check data in tables
    echo "<div class='test-section'>";
    echo "<h3>📊 Data Count Verification</h3>";
    
    $tablesToCheck = ['users', 'inventory', 'suppliers'];
    foreach ($tablesToCheck as $tableName) {
        if (in_array($tableName, $existingTables ?? [])) {
            try {
                $stmt = $pdo->query("SELECT COUNT(*) as count FROM $tableName");
                $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
                
                if ($count > 0) {
                    printTestResult("✅ Table '$tableName' has $count records", 'success');
                    
                    // Show sample data
                    $stmt = $pdo->query("SELECT * FROM $tableName LIMIT 3");
                    $sampleData = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    if (count($sampleData) > 0) {
                        echo "<details style='margin: 10px 0;'>";
                        echo "<summary style='cursor: pointer; color: #94a3b8;'>👁️ View sample data (3 records)</summary>";
                        echo "<div style='margin-top: 10px;'>";
                        echo "<pre style='background: #0f172a; padding: 10px; border-radius: 6px; overflow-x: auto;'>";
                        print_r($sampleData);
                        echo "</pre>";
                        echo "</div>";
                        echo "</details>";
                    }
                } else {
                    printTestResult("⚠️ Table '$tableName' is empty (no records found)", 'warning');
                }
            } catch(PDOException $e) {
                printTestResult("Error checking table $tableName: " . $e->getMessage(), 'error');
            }
        }
    }
    echo "</div>";
    
    // Test 7: Check for default users
    echo "<div class='test-section'>";
    echo "<h3>👤 User Verification</h3>";
    
    try {
        $stmt = $pdo->query("SELECT username, role FROM users");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($users) > 0) {
            printTestResult("✅ Found " . count($users) . " users in the system", 'success');
            
            echo "<div style='margin-top: 10px;'>";
            echo "<table style='width: 100%; border-collapse: collapse; font-size: 13px;'>";
            echo "<thead><tr style='background: #0f172a;'>";
            echo "<th style='padding: 8px; text-align: left; border-bottom: 2px solid #334155; color: #f59e0b;'>Username</th>";
            echo "<th style='padding: 8px; text-align: left; border-bottom: 2px solid #334155; color: #f59e0b;'>Role</th>";
            echo "</tr></thead>";
            echo "<tbody>";
            foreach ($users as $user) {
                echo "<tr>";
                echo "<td style='padding: 8px; border-bottom: 1px solid #2d3a5e; font-family: monospace;'>" . htmlspecialchars($user['username']) . "</td>";
                echo "<td style='padding: 8px; border-bottom: 1px solid #2d3a5e;'><span class='badge badge-info'>" . htmlspecialchars($user['role']) . "</span></td>";
                echo "</tr>";
            }
            echo "</tbody></table>";
            echo "</div>";
        } else {
            printTestResult("⚠️ No users found in the system", 'warning');
        }
    } catch(PDOException $e) {
        printTestResult("Error checking users: " . $e->getMessage(), 'error');
    }
    echo "</div>";
}

// Summary
echo "<div class='summary'>";
echo "<h2>📊 Connection Test Summary</h2>";

if ($connectionSuccess) {
    echo "<div style='padding: 15px; background: #064e3b; border-radius: 8px; border-left: 4px solid #10b981;'>";
    echo "<strong style='color: #10b981;'>✅ Database connection successful!</strong>";
    echo "<p style='margin: 10px 0 0 0; color: #a7f3d0;'>Your Kamau Auto Spares database is properly configured and ready to use.</p>";
    echo "</div>";
    
    if (!empty($missingTables)) {
        echo "<div style='padding: 15px; background: #78350f; border-radius: 8px; border-left: 4px solid #f59e0b; margin-top: 15px;'>";
        echo "<strong style='color: #f59e0b;'>⚠️ Missing Tables</strong>";
        echo "<p style='margin: 10px 0 0 0; color: #fbbf24;'>The following tables are missing: " . implode(', ', $missingTables) . "</p>";
        echo "<p style='margin: 5px 0 0 0; color: #fbbf24;'>Please run the SQL schema script to create all necessary tables.</p>";
        echo "</div>";
    }
} else {
    echo "<div style='padding: 15px; background: #7f1d1d; border-radius: 8px; border-left: 4px solid #ef4444;'>";
    echo "<strong style='color: #ef4444;'>❌ Database connection failed!</strong>";
    echo "<p style='margin: 10px 0 0 0; color: #fca5a5;'>Please check your database configuration and ensure MySQL is running.</p>";
    echo "<p style='margin: 5px 0 0 0; color: #fca5a5;'>Verify database name, username, and password in db_config.php</p>";
    echo "</div>";
}

echo "</div>";

// Footer
echo "<div style='margin-top: 30px; text-align: center; color: #5b6e8c; font-size: 12px; border-top: 1px solid #1e293b; padding-top: 20px;'>";
echo "Kamau Auto Spares Inventory System &bull; Database Test Utility";
echo "<br>PHP " . phpversion() . " &bull; MySQL " . ($connectionSuccess ? $pdo->getAttribute(PDO::ATTR_SERVER_VERSION) : 'N/A');
echo "</div>";

echo "</div>";
echo "</body>";
echo "</html>";
?>