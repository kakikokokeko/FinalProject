<?php
session_start();
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
    $pdo = new PDO('mysql:host=localhost;dbname=DaMeatUp', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->beginTransaction();

    // Insert into Sales table
    $stmt = $pdo->prepare("INSERT INTO Sales (cashier_code, total_amount, cash_amount, change_amount, transaction_date) VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([$_SESSION['acc_code'], $data['total_amount'], $data['cash_amount'], $data['change_amount']]);
    $saleId = $pdo->lastInsertId();

    // Process each order item
    foreach ($data['orders'] as $order) {
        // Get product details
        $stmt = $pdo->prepare("SELECT prod_code, stock_atty FROM Products WHERE prod_name = ?");
        $stmt->execute([$order['product_name']]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            throw new Exception("Product not found: " . $order['product_name']);
        }

        // Check if enough stock
        if ($product['stock_atty'] < $order['quantity']) {
            throw new Exception("Insufficient stock for: " . $order['product_name']);
        }

        // Insert into SalesDetails
        $stmt = $pdo->prepare("INSERT INTO SalesDetails (sale_id, prod_code, quantity, unit_price) VALUES (?, ?, ?, ?)");
        $stmt->execute([$saleId, $product['prod_code'], $order['quantity'], $order['price'] / $order['quantity']]);

        // Update product quantity
        $stmt = $pdo->prepare("UPDATE Products SET stock_atty = stock_atty - ? WHERE prod_code = ?");
        $stmt->execute([$order['quantity'], $product['prod_code']]);
    }

    $pdo->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 