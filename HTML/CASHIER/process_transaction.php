<?php
session_start();
include("../../HTML/LOGIN/database_config.php");
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['acc_code']) || $_SESSION['acc_position'] !== 'Cashier') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Get JSON data
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid data received']);
    exit;
}

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->beginTransaction();

    // Insert into Sales table
    $stmt = $conn->prepare("INSERT INTO sales (cashier_code, total_amount, cash_amount, change_amount, transaction_date) VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([$_SESSION['acc_code'], $data['total_amount'], $data['cash_amount'], $data['change_amount']]);
    $saleId = $conn->lastInsertId();

    // Process each order item
    foreach ($data['orders'] as $order) {
        // Get product details
        $stmt = $conn->prepare("SELECT prod_code, stock_atty, stock_unit FROM products WHERE prod_name = ?");
        $stmt->execute([$order['product_name']]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            throw new Exception("Product not found: " . $order['product_name']);
        }

        // Convert quantity to proper unit if needed
        $deductQuantity = $order['quantity'];
        
        // Check if enough stock (using proper decimal comparison)
        if (bccomp((string)$product['stock_atty'], (string)$deductQuantity, 3) < 0) {
            throw new Exception("Insufficient stock for: " . $order['product_name']);
        }

        // Insert into SalesDetails
        $stmt = $conn->prepare("INSERT INTO salesdetails (sale_id, prod_code, quantity, unit_price) VALUES (?, ?, ?, ?)");
        $stmt->execute([$saleId, $product['prod_code'], $deductQuantity, $order['price'] / $order['quantity']]);

        // Update product quantity using proper decimal arithmetic
        $stmt = $conn->prepare("UPDATE products SET stock_atty = stock_atty - ? WHERE prod_code = ?");
        $stmt->execute([$deductQuantity, $product['prod_code']]);
    }

    $conn->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollBack();
    }
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 