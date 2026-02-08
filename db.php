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

function getVehicleImageCount(PDO $pdo, int $vehicleId): int
{
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM vehicle_images WHERE vehicle_id = :id');
    $stmt->execute([':id' => $vehicleId]);
    return (int) $stmt->fetchColumn();
}

function getVehicleImagesForVehicles(PDO $pdo, array $vehicleIds): array
{
    $vehicleIds = array_values(array_filter(array_map('intval', $vehicleIds)));
    if ($vehicleIds === []) {
        return [];
    }

    $placeholders = implode(',', array_fill(0, count($vehicleIds), '?'));
    $stmt = $pdo->prepare("SELECT vehicle_id, image_url FROM vehicle_images WHERE vehicle_id IN ($placeholders) ORDER BY id ASC");
    $stmt->execute($vehicleIds);

    $map = [];
    foreach ($stmt->fetchAll() as $row) {
        $map[(int) $row['vehicle_id']][] = $row['image_url'];
    }

    return $map;
}

ensureDefaultAdmin($pdo);

return $pdo;
