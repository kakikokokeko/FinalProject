<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['acc_code']) || $_SESSION['acc_position'] !== 'Cashier') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

try {
    $pdo = new PDO('mysql:host=localhost;dbname=DaMeatUp', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (isset($_GET['order_id'])) {
        // Fetch specific order details
        $stmt = $pdo->prepare("
            SELECT 
                s.sale_id,
                s.transaction_date,
                s.total_amount,
                s.cash_amount,
                s.change_amount,
                CONCAT(a.first_name, ' ', a.last_name) as cashier_name,
                p.prod_name,
                sd.quantity,
                sd.unit_price,
                (sd.quantity * sd.unit_price) as item_total
            FROM Sales s
            JOIN Account a ON s.cashier_code = a.acc_code
            JOIN SalesDetails sd ON s.sale_id = sd.sale_id
            JOIN Products p ON sd.prod_code = p.prod_code
            WHERE s.sale_id = ?
        ");
        $stmt->execute([$_GET['order_id']]);
        $orderDetails = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($orderDetails) {
            echo json_encode([
                'success' => true,
                'data' => $orderDetails
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Order not found'
            ]);
        }
    } else {
        // Fetch order history with filter
        $whereClause = "";
        if (isset($_GET['filter'])) {
            switch ($_GET['filter']) {
                case 'today':
                    $whereClause = "WHERE DATE(s.transaction_date) = CURDATE()";
                    break;
                case 'week':
                    $whereClause = "WHERE YEARWEEK(s.transaction_date) = YEARWEEK(CURDATE())";
                    break;
                case 'month':
                    $whereClause = "WHERE YEAR(s.transaction_date) = YEAR(CURDATE()) AND MONTH(s.transaction_date) = MONTH(CURDATE())";
                    break;
                default:
                    $whereClause = "";
            }
        }

        $stmt = $pdo->prepare("
            SELECT 
                s.sale_id,
                s.transaction_date,
                s.total_amount,
                s.cash_amount,
                s.change_amount,
                CONCAT(a.first_name, ' ', a.last_name) as cashier_name
            FROM Sales s
            JOIN Account a ON s.cashier_code = a.acc_code
            $whereClause
            ORDER BY s.transaction_date DESC
            LIMIT 100
        ");
        $stmt->execute();
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'data' => $orders
        ]);
    }
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} 