<?php
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'DaMeatUp';

try {
    
    $conn = new PDO("mysql:host=$host", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

   
    $conn->exec("CREATE DATABASE IF NOT EXISTS $dbname");
    $conn->exec("USE $dbname");

   
    $sql = [
        "CREATE TABLE IF NOT EXISTS Account (
            acc_code VARCHAR(20) PRIMARY KEY,
            first_name VARCHAR(50) NOT NULL,
            last_name VARCHAR(50) NOT NULL,
            acc_position VARCHAR(50) NOT NULL,
            acc_address VARCHAR(255) NOT NULL,
            gender VARCHAR(10) NOT NULL,
            acc_contact VARCHAR(20) NOT NULL,
            username VARCHAR(50) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL
        )",
        
        "CREATE TABLE IF NOT EXISTS Category (
            category_code VARCHAR(20) PRIMARY KEY,
            category_type VARCHAR(50) NOT NULL
        )",
        
        "INSERT IGNORE INTO Category (category_code, category_type) VALUES 
        ('CAT001', 'Chicken'),
        ('CAT002', 'Beef'),
        ('CAT003', 'Pork'),
        ('CAT004', 'Processed'),
        ('CAT005', 'Sari-sari')",
        
        "CREATE TABLE IF NOT EXISTS Products (
            prod_code VARCHAR(20) PRIMARY KEY,
            prod_name VARCHAR(100) NOT NULL,
            prod_price DECIMAL(10,2) NOT NULL,
            stock_atty INT NOT NULL,
            stock_unit ENUM('kg', 'qty') NOT NULL,
            category_code VARCHAR(20) NOT NULL,
            image_path VARCHAR(255),
            FOREIGN KEY (category_code) REFERENCES Category(category_code)
        )",
        
        "CREATE TABLE IF NOT EXISTS ProductCounter (
            category_code VARCHAR(20) PRIMARY KEY,
            next_value INT NOT NULL DEFAULT 0,
            FOREIGN KEY (category_code) REFERENCES Category(category_code)
        )",
        
        "INSERT IGNORE INTO ProductCounter (category_code, next_value) VALUES 
        ('CAT001', 1000),
        ('CAT002', 2000),
        ('CAT003', 3000),
        ('CAT004', 4000),
        ('CAT005', 5000)",
        
        "CREATE TABLE IF NOT EXISTS Sales (
            sale_id INT PRIMARY KEY AUTO_INCREMENT,
            cashier_code VARCHAR(20) NOT NULL,
            total_amount DECIMAL(10,2) NOT NULL,
            cash_amount DECIMAL(10,2) NOT NULL,
            change_amount DECIMAL(10,2) NOT NULL,
            transaction_date DATETIME NOT NULL,
            FOREIGN KEY (cashier_code) REFERENCES Account(acc_code)
        )",
        
        "CREATE TABLE IF NOT EXISTS SalesDetails (
            sale_detail_id INT PRIMARY KEY AUTO_INCREMENT,
            sale_id INT NOT NULL,
            prod_code VARCHAR(20) NOT NULL,
            quantity DECIMAL(10,3) NOT NULL,
            unit_price DECIMAL(10,2) NOT NULL,
            FOREIGN KEY (sale_id) REFERENCES Sales(sale_id),
            FOREIGN KEY (prod_code) REFERENCES Products(prod_code)
        )"
    ];

    foreach ($sql as $query) {
        $conn->exec($query);
    }

    
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}

$conn = null;
?>