<?php
$host = 'localhost';
$dbname = 'grow_partner';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die('Database connection failed.');
}

function ensureDefaultAdmin(PDO $pdo): void
{
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
        if ($stmt->rowCount() === 0) {
            return;
        }

        $count = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
        if ($count === 0) {
            $email = 'admin@growpartner.com';
            $passwordHash = password_hash('ChangeMe123!', PASSWORD_DEFAULT);
            $insert = $pdo->prepare('INSERT INTO users (email, password_hash) VALUES (:email, :hash)');
            $insert->execute([':email' => $email, ':hash' => $passwordHash]);
        }
    } catch (PDOException $e) {
        return;
    }
}

function getVehiclesByType(PDO $pdo, string $type): array
{
    $stmt = $pdo->prepare('SELECT * FROM vehicles WHERE type = :type ORDER BY created_at DESC');
    $stmt->execute([':type' => $type]);
    return $stmt->fetchAll();
}

function getAllVehicles(PDO $pdo): array
{
    $stmt = $pdo->query('SELECT * FROM vehicles ORDER BY created_at DESC');
    return $stmt->fetchAll();
}

ensureDefaultAdmin($pdo);

return $pdo;
