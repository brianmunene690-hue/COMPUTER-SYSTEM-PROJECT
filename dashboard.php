<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

$user = $_SESSION['user'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kamau Auto Spares Dashboard</title>

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:Arial,Helvetica,sans-serif;
}

body{
    background:#f4f6f9;
}

header{
    background:#1f2937;
    color:white;
    padding:18px;
    display:flex;
    justify-content:space-between;
    align-items:center;
}

header h2{
    font-size:22px;
}

.container{
    padding:30px;
}

.card{
    background:white;
    padding:20px;
    margin-bottom:20px;
    border-radius:8px;
    box-shadow:0 3px 10px rgba(0,0,0,.1);
}

.role{
    color:#2563eb;
    font-weight:bold;
}

button{
    background:#2563eb;
    color:white;
    border:none;
    padding:10px 20px;
    border-radius:5px;
    cursor:pointer;
}

button:hover{
    background:#1d4ed8;
}

.dashboard-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(260px,1fr));
    gap:20px;
}
</style>

</head>

<body>

<header>

<h2>🔧 Kamau Auto Spares</h2>

<div>

Welcome

<strong><?php echo htmlspecialchars($user['name']); ?></strong>

|

<span class="role">

<?php echo htmlspecialchars($user['role']); ?>

</span>

|

<a href="logout.php">

<button>Logout</button>

</a>

</div>

</header>

<div class="container">

<div class="card">

<h2>Dashboard</h2>

<p>

Logged in as

<strong><?php echo htmlspecialchars($user['username']); ?></strong>

</p>

</div>

<?php

switch($user['role']){

case 'Admin':

?>

<div class="dashboard-grid">

<div class="card">
<h3>👥 User Management</h3>
<p>Create, edit and delete users.</p>
</div>

<div class="card">
<h3>📦 Inventory</h3>
<p>Manage spare parts inventory.</p>
</div>

<div class="card">
<h3>📊 Reports</h3>
<p>View sales and stock reports.</p>
</div>

<div class="card">
<h3>🏭 Suppliers</h3>
<p>Manage suppliers.</p>
</div>

</div>

<?php

break;

case 'Branch Manager':

?>

<div class="dashboard-grid">

<div class="card">
<h3>📊 Sales Reports</h3>
<p>Daily sales summary.</p>
</div>

<div class="card">
<h3>📦 Inventory Levels</h3>
<p>Monitor stock levels.</p>
</div>

<div class="card">
<h3>⚠ Low Stock Alerts</h3>
<p>View reorder recommendations.</p>
</div>

</div>

<?php

break;

case 'Sales Staff':

?>

<div class="dashboard-grid">

<div class="card">
<h3>🛒 Sales Terminal</h3>
<p>Process customer sales.</p>
</div>

<div class="card">
<h3>🔍 Search Parts</h3>
<p>Search inventory.</p>
</div>

<div class="card">
<h3>📄 Generate Invoice</h3>
<p>Create customer invoices.</p>
</div>

</div>

<?php

break;

case 'Inventory Clerk':

?>

<div class="dashboard-grid">

<div class="card">
<h3>📥 Receive Stock</h3>
<p>Record incoming stock.</p>
</div>

<div class="card">
<h3>📦 Update Inventory</h3>
<p>Update stock quantities.</p>
</div>

<div class="card">
<h3>⚠ Damaged Stock</h3>
<p>Record damaged items.</p>
</div>

</div>

<?php

break;

default:

?>

<div class="card">

Unknown role.

</div>

<?php

}

?>

</div>

</body>
</html>