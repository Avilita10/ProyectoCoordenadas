<?php
$host = 'localhost';
$db = 'estudiante_db';
$user = 'root';
$password = '';

// Usar variables de entorno para mayor seguridad
$pdo = null;
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Error al conectar a la base de datos: " . $e->getMessage();
    exit;
}
?>
