<?php
$host = 'localhost';
$username = 'u827046868_dameatup';
$password = '+Sl3~TJZ?+w@';
$dbname = 'u827046868_dameatup';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = [
        "CREATE TABLE IF NOT EXISTS account (
            acc_code VARCHAR(20) PRIMARY KEY,
            first_name VARCHAR(50) NOT NULL,
            last_name VARCHAR(50) NOT NULL,
            acc_position VARCHAR(50) NOT NULL,
            acc_address VARCHAR(255) NOT NULL,
            gender VARCHAR(10) NOT NULL,
            acc_contact VARCHAR(20) NOT NULL,
            username VARCHAR(50) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            status ENUM('active', 'inactive') DEFAULT 'active'
        )",
        
        "CREATE TABLE IF NOT EXISTS category (
            category_code VARCHAR(20) PRIMARY KEY,
            category_type VARCHAR(50) NOT NULL
        )",
        
        "INSERT IGNORE INTO category (category_code, category_type) VALUES 
        ('CAT001', 'Chicken'),
        ('CAT002', 'Beef'),
        ('CAT003', 'Pork'),
        ('CAT004', 'Processed'),
        ('CAT005', 'Sari-sari')",
        
        "CREATE TABLE IF NOT EXISTS products (
            prod_code VARCHAR(20) PRIMARY KEY,
            prod_name VARCHAR(100) NOT NULL,
            prod_price DECIMAL(10,2) NOT NULL,
            stock_atty DECIMAL(10,3) NOT NULL,
            stock_unit ENUM('kg', 'qty') NOT NULL,
            category_code VARCHAR(20) NOT NULL,
            image_path VARCHAR(255),
            status ENUM('active', 'inactive') DEFAULT 'active',
            CONSTRAINT fk_category FOREIGN KEY (category_code) REFERENCES category(category_code)
        )",
        
        "CREATE TABLE IF NOT EXISTS productcounter (
            category_code VARCHAR(20) PRIMARY KEY,
            next_value INT NOT NULL DEFAULT 0,
            CONSTRAINT fk_category_counter FOREIGN KEY (category_code) REFERENCES category(category_code)
        )",
        
        "INSERT IGNORE INTO productcounter (category_code, next_value) VALUES 
        ('CAT001', 1000),
        ('CAT002', 2000),
        ('CAT003', 3000),
        ('CAT004', 4000),
        ('CAT005', 5000)",
        
        "CREATE TABLE IF NOT EXISTS sales (
            sale_id INT PRIMARY KEY AUTO_INCREMENT,
            cashier_code VARCHAR(20) NOT NULL,
            total_amount DECIMAL(10,2) NOT NULL,
            cash_amount DECIMAL(10,2) NOT NULL,
            change_amount DECIMAL(10,2) NOT NULL,
            transaction_date DATETIME NOT NULL,
            CONSTRAINT fk_cashier FOREIGN KEY (cashier_code) REFERENCES account(acc_code)
        )",
        
        "CREATE TABLE IF NOT EXISTS salesdetails (
            sale_detail_id INT PRIMARY KEY AUTO_INCREMENT,
            sale_id INT NOT NULL,
            prod_code VARCHAR(20) NOT NULL,
            quantity DECIMAL(10,3) NOT NULL,
            unit_price DECIMAL(10,2) NOT NULL,
            CONSTRAINT fk_sale FOREIGN KEY (sale_id) REFERENCES sales(sale_id),
            CONSTRAINT fk_product FOREIGN KEY (prod_code) REFERENCES products(prod_code)
        )",

    ];

    foreach ($sql as $query) {
        $conn->exec($query);
    }
    
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}

$conn = null;
?>