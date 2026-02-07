<?php
require_once __DIR__ . '/auth.php';
requireLogin();

$pdo = require __DIR__ . '/../db.php';
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare('DELETE FROM vehicles WHERE id = :id');
            $stmt->execute([':id' => $id]);
            $message = 'Vehiculo eliminado.';
        }
    }

    if ($action === 'save') {
        $id = (int) ($_POST['id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $type = $_POST['type'] ?? '';
        $price = $_POST['price'] ?? '';
        $imageUrl = trim($_POST['image_url'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if ($title === '' || $imageUrl === '' || $description === '') {
            $error = 'Completa todos los campos.';
        } elseif (!in_array($type, ['nuevo', 'usado'], true)) {
            $error = 'Tipo de vehiculo invalido.';
        } elseif (!is_numeric($price) || (float) $price <= 0) {
            $error = 'Precio invalido.';
        } else {
            if ($id > 0) {
                $stmt = $pdo->prepare('UPDATE vehicles SET title = :title, type = :type, price = :price, image_url = :image_url, description = :description WHERE id = :id');
                $stmt->execute([
                    ':title' => $title,
                    ':type' => $type,
                    ':price' => $price,
                    ':image_url' => $imageUrl,
                    ':description' => $description,
                    ':id' => $id,
                ]);
                $message = 'Vehiculo actualizado.';
            } else {
                $stmt = $pdo->prepare('INSERT INTO vehicles (title, type, price, image_url, description) VALUES (:title, :type, :price, :image_url, :description)');
                $stmt->execute([
                    ':title' => $title,
                    ':type' => $type,
                    ':price' => $price,
                    ':image_url' => $imageUrl,
                    ':description' => $description,
                ]);
                $message = 'Vehiculo creado.';
            }
        }
    }
}

$vehicles = getAllVehicles($pdo);

$editing = null;
if (isset($_GET['edit'])) {
    $id = (int) $_GET['edit'];
    foreach ($vehicles as $vehicle) {
        if ((int) $vehicle['id'] === $id) {
            $editing = $vehicle;
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Grow Partner</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body class="admin">
    <header class="admin__header">
        <h1>Panel de catalogo</h1>
        <a href="logout.php" class="btn btn--ghost">Cerrar sesion</a>
    </header>

    <main class="admin__content">
        <section class="admin__form">
            <h2><?php echo $editing ? 'Editar vehiculo' : 'Nuevo vehiculo'; ?></h2>
            <?php if ($message): ?>
                <div class="alert alert--success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form method="post" class="form" novalidate>
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="id" value="<?php echo $editing['id'] ?? 0; ?>">
                <div class="form__row">
                    <label for="title">Titulo</label>
                    <input type="text" id="title" name="title" required value="<?php echo htmlspecialchars($editing['title'] ?? ''); ?>">
                </div>
                <div class="form__row">
                    <label for="type">Tipo</label>
                    <select id="type" name="type" required>
                        <option value="nuevo" <?php echo ($editing['type'] ?? '') === 'nuevo' ? 'selected' : ''; ?>>Nuevo</option>
                        <option value="usado" <?php echo ($editing['type'] ?? '') === 'usado' ? 'selected' : ''; ?>>Usado</option>
                    </select>
                </div>
                <div class="form__row">
                    <label for="price">Precio</label>
                    <input type="number" step="0.01" id="price" name="price" required value="<?php echo htmlspecialchars((string) ($editing['price'] ?? '')); ?>">
                </div>
                <div class="form__row">
                    <label for="image_url">URL de imagen</label>
                    <input type="url" id="image_url" name="image_url" required value="<?php echo htmlspecialchars($editing['image_url'] ?? ''); ?>">
                </div>
                <div class="form__row">
                    <label for="description">Descripcion</label>
                    <textarea id="description" name="description" rows="4" required><?php echo htmlspecialchars($editing['description'] ?? ''); ?></textarea>
                </div>
                <button type="submit" class="btn btn--primary">Guardar</button>
            </form>
        </section>

        <section class="admin__list">
            <h2>Catalogo actual</h2>
            <div class="admin__table">
                <?php foreach ($vehicles as $vehicle): ?>
                    <div class="admin__row">
                        <div>
                            <strong><?php echo htmlspecialchars($vehicle['title']); ?></strong>
                            <span class="tag <?php echo $vehicle['type'] === 'usado' ? 'tag--alt' : ''; ?>"><?php echo htmlspecialchars($vehicle['type']); ?></span>
                            <p>$<?php echo number_format((float) $vehicle['price'], 2); ?></p>
                        </div>
                        <div class="admin__actions">
                            <a class="btn btn--ghost" href="?edit=<?php echo (int) $vehicle['id']; ?>">Editar</a>
                            <form method="post" onsubmit="return confirm('Eliminar este vehiculo?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo (int) $vehicle['id']; ?>">
                                <button type="submit" class="btn btn--danger">Eliminar</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if (count($vehicles) === 0): ?>
                    <p>No hay unidades cargadas aun.</p>
                <?php endif; ?>
            </div>
        </section>
    </main>
</body>
</html>
