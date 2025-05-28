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
        )  ",
        
        
        "CREATE TABLE IF NOT EXISTS Category (
            category_code VARCHAR(20) PRIMARY KEY,
            category_type VARCHAR(50) NOT NULL
        )",
        
        "CREATE TABLE IF NOT EXISTS Products (
            prod_code VARCHAR(20) PRIMARY KEY,
            prod_name VARCHAR(100) NOT NULL,
            prod_price DECIMAL(10,2) NOT NULL,
            stock_atty INT NOT NULL,
            category_code VARCHAR(20) NOT NULL,
            FOREIGN KEY (category_code) REFERENCES Category(category_code)
        )",
        
        "CREATE TABLE IF NOT EXISTS Sale (
            sale_id INT AUTO_INCREMENT PRIMARY KEY,
            sender VARCHAR(255) NOT NULL,
            SaleDate DATETIME NOT NULL,
            TotalAmount DECIMAL(10,2) NOT NULL
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