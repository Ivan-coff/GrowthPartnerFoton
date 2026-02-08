<?php
require_once __DIR__ . '/auth.php';
requireLogin();

$pdo = require __DIR__ . '/../db.php';
$message = '';
$error = '';
$uploadDir = __DIR__ . '/../assets/uploads/vehicles';
$uploadUrlBase = 'assets/uploads/vehicles';
$maxUploadBytes = 4 * 1024 * 1024;

function handleImageUploads(array $files, string $uploadDir, string $uploadUrlBase, int $maxUploadBytes, string &$error): array
{
    if (!isset($files['error'])) {
        return [];
    }

    $errors = (array) $files['error'];
    $tmpNames = (array) $files['tmp_name'];
    $sizes = (array) $files['size'];

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    $urls = [];
    $moved = [];

    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true)) {
        $error = 'No se pudo crear el directorio de imagenes.';
        return [];
    }

    $count = count($errors);
    for ($i = 0; $i < $count; $i++) {
        if ($errors[$i] === UPLOAD_ERR_NO_FILE) {
            continue;
        }

        if ($errors[$i] !== UPLOAD_ERR_OK) {
            $error = 'No se pudo subir la imagen.';
            break;
        }

        if ($sizes[$i] > $maxUploadBytes) {
            $error = 'La imagen supera el limite de 4MB.';
            break;
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($tmpNames[$i]);
        if (!isset($allowed[$mime])) {
            $error = 'Formato de imagen no permitido. Usa JPG, PNG o WEBP.';
            break;
        }

        $basename = bin2hex(random_bytes(16));
        $filename = $basename . '.' . $allowed[$mime];
        $target = $uploadDir . '/' . $filename;

        if (!move_uploaded_file($tmpNames[$i], $target)) {
            $error = 'No se pudo guardar la imagen.';
            break;
        }

        $moved[] = $target;
        $urls[] = $uploadUrlBase . '/' . $filename;
    }

    if ($error !== '') {
        foreach ($moved as $path) {
            if (is_file($path)) {
                unlink($path);
            }
        }
        return [];
    }

    return $urls;
}

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
        $currency = strtoupper(trim($_POST['currency'] ?? 'ARS'));
        $manualImageUrl = trim($_POST['image_url'] ?? '');
        $existingImageUrl = trim($_POST['existing_image_url'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $uploadedImageUrls = [];

        if (isset($_FILES['image_files'])) {
            $uploadedImageUrls = handleImageUploads($_FILES['image_files'], $uploadDir, $uploadUrlBase, $maxUploadBytes, $error);
        }

        $imageUrls = [];
        if ($manualImageUrl !== '') {
            $imageUrls[] = $manualImageUrl;
        }
        $imageUrls = array_merge($imageUrls, $uploadedImageUrls);

        $existingImagesCount = $id > 0 ? getVehicleImageCount($pdo, $id) : 0;
        $hasExistingImages = $existingImagesCount > 0 || $existingImageUrl !== '';

        if ($error === '') {
            if ($title === '' || $description === '') {
                $error = 'Completa todos los campos.';
            } elseif (!in_array($type, ['nuevo', 'usado'], true)) {
                $error = 'Tipo de vehiculo invalido.';
            } elseif (!is_numeric($price) || (float) $price <= 0) {
                $error = 'Precio invalido.';
            } elseif (!in_array($currency, ['ARS', 'USD'], true)) {
                $error = 'Moneda invalida.';
            } elseif ($manualImageUrl !== '' && !filter_var($manualImageUrl, FILTER_VALIDATE_URL)) {
                $error = 'URL de imagen invalida.';
            } elseif ($id === 0 && $imageUrls === []) {
                $error = 'Carga al menos una imagen o URL.';
            } elseif ($id > 0 && !$hasExistingImages && $imageUrls === []) {
                $error = 'Carga al menos una imagen o URL.';
            }
        }

        $imageUrlToStore = $existingImageUrl;
        if ($imageUrls !== []) {
            $imageUrlToStore = $imageUrls[0];
        }

        if ($error === '') {
            if ($id > 0) {
                $stmt = $pdo->prepare('UPDATE vehicles SET title = :title, type = :type, price = :price, currency = :currency, image_url = :image_url, description = :description WHERE id = :id');
                $stmt->execute([
                    ':title' => $title,
                    ':type' => $type,
                    ':price' => $price,
                    ':currency' => $currency,
                    ':image_url' => $imageUrlToStore,
                    ':description' => $description,
                    ':id' => $id,
                ]);
                $message = 'Vehiculo actualizado.';
            } else {
                $stmt = $pdo->prepare('INSERT INTO vehicles (title, type, price, currency, image_url, description) VALUES (:title, :type, :price, :currency, :image_url, :description)');
                $stmt->execute([
                    ':title' => $title,
                    ':type' => $type,
                    ':price' => $price,
                    ':currency' => $currency,
                    ':image_url' => $imageUrlToStore,
                    ':description' => $description,
                ]);
                $message = 'Vehiculo creado.';
            }

            $vehicleId = $id > 0 ? $id : (int) $pdo->lastInsertId();
            if ($imageUrls !== []) {
                $stmtImage = $pdo->prepare('INSERT INTO vehicle_images (vehicle_id, image_url) VALUES (:vehicle_id, :image_url)');
                foreach ($imageUrls as $url) {
                    $stmtImage->execute([
                        ':vehicle_id' => $vehicleId,
                        ':image_url' => $url,
                    ]);
                }
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
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@500;600;700&family=Manrope:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUa6mY5D1yS0JmG1k2W6j0D8K2Q1m1CqKpQ8JwQ8wR0F2J9z8z8t2Q8x3R" crossorigin="anonymous">
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body class="admin-shell">
    <header class="admin-header">
        <div class="container d-flex align-items-center justify-content-between flex-wrap gap-3 py-4">
            <div>
                <p class="eyebrow mb-2">Panel interno</p>
                <h1 class="h4 mb-0">Panel de catalogo</h1>
            </div>
            <a href="logout.php" class="btn btn-outline-graphite">Cerrar sesion</a>
        </div>
    </header>

    <main class="container py-4">
        <div class="row g-4">
            <div class="col-lg-5">
                <div class="card admin-card">
                    <div class="card-body p-4">
                        <h2 class="h5 mb-3"><?php echo $editing ? 'Editar vehiculo' : 'Nuevo vehiculo'; ?></h2>
                        <?php if ($message): ?>
                            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
                        <?php endif; ?>
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                        <form method="post" novalidate enctype="multipart/form-data">
                            <input type="hidden" name="action" value="save">
                            <input type="hidden" name="id" value="<?php echo $editing['id'] ?? 0; ?>">
                            <input type="hidden" name="existing_image_url" value="<?php echo htmlspecialchars($editing['image_url'] ?? ''); ?>">
                            <div class="mb-3">
                                <label for="title" class="form-label">Titulo</label>
                                <input type="text" id="title" name="title" class="form-control" required value="<?php echo htmlspecialchars($editing['title'] ?? ''); ?>">
                            </div>
                            <div class="mb-3">
                                <label for="type" class="form-label">Tipo</label>
                                <select id="type" name="type" class="form-select" required>
                                    <option value="nuevo" <?php echo ($editing['type'] ?? '') === 'nuevo' ? 'selected' : ''; ?>>Nuevo</option>
                                    <option value="usado" <?php echo ($editing['type'] ?? '') === 'usado' ? 'selected' : ''; ?>>Usado</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="price" class="form-label">Precio</label>
                                <input type="number" step="0.01" id="price" name="price" class="form-control" required value="<?php echo htmlspecialchars((string) ($editing['price'] ?? '')); ?>">
                            </div>
                            <div class="mb-3">
                                <label for="currency" class="form-label">Moneda</label>
                                <select id="currency" name="currency" class="form-select" required>
                                    <option value="ARS" <?php echo ($editing['currency'] ?? 'ARS') === 'ARS' ? 'selected' : ''; ?>>Pesos ($)</option>
                                    <option value="USD" <?php echo ($editing['currency'] ?? '') === 'USD' ? 'selected' : ''; ?>>Dolares (U$S)</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="image_url" class="form-label">URL de imagen (opcional)</label>
                                <input type="url" id="image_url" name="image_url" class="form-control" value="<?php echo htmlspecialchars($editing['image_url'] ?? ''); ?>">
                            </div>
                            <div class="mb-3">
                                <label for="image_files" class="form-label">Subir imagenes</label>
                                <input type="file" id="image_files" name="image_files[]" class="form-control" accept="image/*" multiple>
                                <div class="form-text">Puedes seleccionar varias imagenes. Las nuevas se suman a las anteriores.</div>
                            </div>
                            <div class="mb-4">
                                <label for="description" class="form-label">Descripcion</label>
                                <textarea id="description" name="description" rows="4" class="form-control" required><?php echo htmlspecialchars($editing['description'] ?? ''); ?></textarea>
                            </div>
                            <button type="submit" class="btn btn-accent w-100">Guardar</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="card admin-card">
                    <div class="card-body p-4">
                        <h2 class="h5 mb-3">Catalogo actual</h2>
                        <div class="d-flex flex-column gap-3">
                            <?php foreach ($vehicles as $vehicle): ?>
                                <div class="admin-row">
                                    <div>
                                        <?php
                                        $currency = strtoupper($vehicle['currency'] ?? 'ARS');
                                        $currencyLabel = $currency === 'USD' ? 'U$S' : '$';
                                        ?>
                                        <strong><?php echo htmlspecialchars($vehicle['title']); ?></strong>
                                        <div class="d-flex align-items-center gap-2 mt-1">
                                            <span class="type-pill <?php echo $vehicle['type'] === 'usado' ? 'type-pill--used' : ''; ?>">
                                                <?php echo htmlspecialchars($vehicle['type']); ?>
                                            </span>
                                            <span class="text-muted small"><?php echo $currencyLabel . number_format((float) $vehicle['price'], 2); ?></span>
                                        </div>
                                    </div>
                                    <div class="admin-actions">
                                        <a class="btn btn-outline-graphite btn-sm" href="?edit=<?php echo (int) $vehicle['id']; ?>">Editar</a>
                                        <form method="post" onsubmit="return confirm('Eliminar este vehiculo?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo (int) $vehicle['id']; ?>">
                                            <button type="submit" class="btn btn-outline-danger btn-sm">Eliminar</button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <?php if (count($vehicles) === 0): ?>
                                <p class="text-muted mb-0">No hay unidades cargadas aun.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
