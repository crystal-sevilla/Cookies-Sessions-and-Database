<?php
$host = 'localhost';
$port = '5432';
$dbname = 'classwall';
$user = 'postgres'; 
$password = '1987@4ft0n';

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
