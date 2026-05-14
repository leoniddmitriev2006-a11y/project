<?php
session_start();

$host = '127.0.0.1'; // или 'localhost'
$db   = 'i923493f_soulja'; // имя базы данных
$user = 'i923493f_soulja'; // пользователь БД
$pass = 'VraB*6v8CtP1';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

try {
    $pdo = new PDO(
        $dsn,
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );

    $columnGender = $pdo->query("SHOW COLUMNS FROM products LIKE 'gender'")->fetch();
    if (!$columnGender) {
        $pdo->exec("ALTER TABLE products ADD COLUMN gender ENUM('men','women') NOT NULL DEFAULT 'men'");
    }
    $columnCategory = $pdo->query("SHOW COLUMNS FROM products LIKE 'category'")->fetch();
    if (!$columnCategory) {
        $pdo->exec("ALTER TABLE products ADD COLUMN category ENUM('jeans','hoodie','bottom','top') NOT NULL DEFAULT 'jeans'");
    } else {
        $enumDefinition = $columnCategory['Type'] ?? '';
        if (!str_contains($enumDefinition, "'jeans'") || !str_contains($enumDefinition, "'hoodie'") || !str_contains($enumDefinition, "'bottom'") || !str_contains($enumDefinition, "'top'")) {
            $pdo->exec("UPDATE products SET category = 'jeans' WHERE category IN ('pants')");
            $pdo->exec("UPDATE products SET category = 'top' WHERE category IN ('tshirt')");
            $pdo->exec("ALTER TABLE products MODIFY COLUMN category ENUM('jeans','hoodie','bottom','top') NOT NULL DEFAULT 'jeans'");
        }
    }
} catch (PDOException $e) {
    die('Ошибка подключения к базе данных: ' . $e->getMessage());
}
