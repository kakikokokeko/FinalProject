<?php
require_once '../LOGIN/database_config.php';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Test sales table
    $query = "SELECT COUNT(*) as count FROM sales";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Total sales records: " . $result['count'] . "<br>";
    
    // Test salesdetails table
    $query = "SELECT COUNT(*) as count FROM salesdetails";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Total sales details records: " . $result['count'] . "<br>";
    
    // Get sample sales data
    $query = "SELECT s.sale_id, s.transaction_date, sd.quantity, sd.unit_price, p.prod_name 
              FROM sales s 
              JOIN salesdetails sd ON s.sale_id = sd.sale_id 
              JOIN products p ON sd.prod_code = p.prod_code 
              LIMIT 5";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $sales = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<br>Sample sales data:<br>";
    foreach ($sales as $sale) {
        echo "Sale ID: " . $sale['sale_id'] . 
             ", Date: " . $sale['transaction_date'] . 
             ", Product: " . $sale['prod_name'] . 
             ", Quantity: " . $sale['quantity'] . 
             ", Price: " . $sale['unit_price'] . "<br>";
    }
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 