<?php
$pdo = require __DIR__ . '/db.php';
$newVehicles = getVehiclesByType($pdo, 'nuevo');
$usedVehicles = getVehiclesByType($pdo, 'usado');
$logoutMessage = isset($_GET['logout']) && $_GET['logout'] === '1';

$vehicleIds = array_merge(
    array_column($newVehicles, 'id'),
    array_column($usedVehicles, 'id')
);
$vehicleImages = $vehicleIds ? getVehicleImagesForVehicles($pdo, $vehicleIds) : [];
?>
<!DOCTYPE html>
<html lang="es"></html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grow Partner - FOTON</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@500;600;700&family=Manrope:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <header class="navbar navbar-expand-lg sticky-top nav-shell">
        <div class="container-fluid px-4">
            <a class="navbar-brand brand-mark" href="#">
                Grow Partner <span>FOTON</span>
            </a>
            <button class="navbar-toggler shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Abrir menu">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="mainNav">
                <nav class="navbar-nav gap-lg-3 align-items-lg-center">
                    <a class="nav-link" href="#catalogo">Catalogo</a>
                    <a class="nav-link" href="#nosotros">Nosotros</a>
                    <a class="nav-link" href="#contacto">Contacto</a>
                    <a class="btn btn-accent ms-lg-3" href="admin/login.php">Admin</a>
                </nav>
            </div>
        </div>
    </header>

    <?php if ($logoutMessage): ?>
        <div class="gp-toast gp-toast--success" role="status" aria-live="polite" data-toast>
            Sesion cerrada correctamente.
        </div>
    <?php endif; ?>

    <main>
        <section class="hero-section">
            <div class="container">
                <div class="row align-items-center g-5">
                    <div class="col-lg-6 reveal">
                        <p class="eyebrow">Concesionario oficial FOTON</p>
                        <h1 class="hero-title">Calidad y confianza para tu negocio</h1>
                        <p class="hero-subtitle">
                            Grow Partner selecciona las mejores unidades nuevas y usadas para crecer con seguridad y estilo.
                        </p>
                        <div class="d-flex flex-wrap gap-3 mt-4">
                            <a href="#catalogo" class="btn btn-accent">Ver catalogo</a>
                            <a href="#contacto" class="btn btn-outline-graphite">Agenda una cita</a>
                        </div>
                    </div>
                    <div class="col-lg-6 reveal">
                        <div class="hero-media">
                            <img
                                src="https://images.unsplash.com/photo-1503376780353-7e6692767b70?auto=format&fit=crop&w=1400&q=80"
                                alt="Autos FOTON en exhibicion"
                                loading="eager"
                                decoding="async"
                                fetchpriority="high"
                            >
                            <div class="hero-badge">Grow Partner</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="catalogo" class="section-pad">
            <div class="container">
                <div class="section-heading reveal">
                    <h2 class="section-title">Catalogo de unidades</h2>
                    <p class="section-subtitle">Opciones nuevas y usadas, revisadas con estandares de fabrica.</p>
                </div>

                <div class="catalog__block">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
                        <h3 class="h5 mb-0">Nuevos</h3>
                        <div class="carousel__controls">
                            <button class="btn btn-outline-graphite btn-sm" type="button" data-carousel-prev aria-label="Ver anteriores">Anterior</button>
                            <button class="btn btn-outline-graphite btn-sm" type="button" data-carousel-next aria-label="Ver siguientes">Siguiente</button>
                        </div>
                    </div>
                    <div class="carousel" data-carousel>
                        <div class="carousel__track d-flex gap-3">
                            <?php if (count($newVehicles) === 0): ?>
                                <div class="card vehicle-card empty-card reveal">Aun no hay unidades nuevas cargadas.</div>
                            <?php endif; ?>
                            <?php foreach ($newVehicles as $vehicle): ?>
                                <?php
                                $currency = strtoupper($vehicle['currency'] ?? 'ARS');
                                $currencyLabel = $currency === 'USD' ? 'U$S' : '$';
                                $vehicleId = (int) $vehicle['id'];

                                $allImages = $vehicleImages[$vehicleId] ?? [];
                                if ($allImages === [] && ($vehicle['image_url'] ?? '') !== '') {
                                    $allImages[] = $vehicle['image_url'];
                                }

                                $imageUrl = $allImages[0] ?? ($vehicle['image_url'] ?? '');
                                $imagesAttr = $allImages ? htmlspecialchars(implode('|', $allImages)) : '';
                                ?>
                                <article class="card vehicle-card reveal">
                                    <div class="vehicle-media"
                                         data-gallery="<?php echo $imagesAttr; ?>"
                                         role="button"
                                         tabindex="0"
                                         aria-label="Ver fotos de <?php echo htmlspecialchars($vehicle['title']); ?>">
                                        <img
                                            src="<?php echo htmlspecialchars($imageUrl); ?>"
                                            alt="<?php echo htmlspecialchars($vehicle['title']); ?>"
                                            loading="lazy"
                                            decoding="async"
                                        >
                                        <span class="vehicle-tag">Nuevo</span>
                                    </div>
                                    <div class="card-body d-flex flex-column gap-2">
                                        <h4 class="h6 mb-0"><?php echo htmlspecialchars($vehicle['title']); ?></h4>
                                        <p class="text-muted small mb-2"><?php echo htmlspecialchars($vehicle['description']); ?></p>
                                        <div class="d-flex align-items-center justify-content-between">
                                            <span class="price-pill"><?php echo $currencyLabel . number_format((float) $vehicle['price'], 2); ?></span>
                                            <span class="type-pill">Nuevo</span>
                                        </div>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="catalog__block mt-5">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
                        <h3 class="h5 mb-0">Usados</h3>
                        <div class="carousel__controls">
                            <button class="btn btn-outline-graphite btn-sm" type="button" data-carousel-prev aria-label="Ver anteriores">Anterior</button>
                            <button class="btn btn-outline-graphite btn-sm" type="button" data-carousel-next aria-label="Ver siguientes">Siguiente</button>
                        </div>
                    </div>
                    <div class="carousel" data-carousel>
                        <div class="carousel__track d-flex gap-3">
                            <?php if (count($usedVehicles) === 0): ?>
                                <div class="card vehicle-card empty-card reveal">Aun no hay unidades usadas cargadas.</div>
                            <?php endif; ?>
                            <?php foreach ($usedVehicles as $vehicle): ?>
                                <?php
                                $currency = strtoupper($vehicle['currency'] ?? 'ARS');
                                $currencyLabel = $currency === 'USD' ? 'U$S' : '$';
                                $vehicleId = (int) $vehicle['id'];

                                $allImages = $vehicleImages[$vehicleId] ?? [];
                                if ($allImages === [] && ($vehicle['image_url'] ?? '') !== '') {
                                    $allImages[] = $vehicle['image_url'];
                                }

                                $imageUrl = $allImages[0] ?? ($vehicle['image_url'] ?? '');
                                $imagesAttr = $allImages ? htmlspecialchars(implode('|', $allImages)) : '';
                                ?>
                                <article class="card vehicle-card reveal">
                                    <div class="vehicle-media"
                                         data-gallery="<?php echo $imagesAttr; ?>"
                                         role="button"
                                         tabindex="0"
                                         aria-label="Ver fotos de <?php echo htmlspecialchars($vehicle['title']); ?>">
                                        <img
                                            src="<?php echo htmlspecialchars($imageUrl); ?>"
                                            alt="<?php echo htmlspecialchars($vehicle['title']); ?>"
                                            loading="lazy"
                                            decoding="async"
                                        >
                                        <span class="vehicle-tag vehicle-tag--used">Usado</span>
                                    </div>
                                    <div class="card-body d-flex flex-column gap-2">
                                        <h4 class="h6 mb-0"><?php echo htmlspecialchars($vehicle['title']); ?></h4>
                                        <p class="text-muted small mb-2"><?php echo htmlspecialchars($vehicle['description']); ?></p>
                                        <div class="d-flex align-items-center justify-content-between">
                                            <span class="price-pill"><?php echo $currencyLabel . number_format((float) $vehicle['price'], 2); ?></span>
                                            <span class="type-pill type-pill--used">Usado</span>
                                        </div>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="nosotros" class="section-pad section-alt">
            <div class="container">
                <div class="row g-4 align-items-start">
                    <div class="col-lg-6 reveal">
                        <h2 class="section-title mb-3">Nosotros</h2>
                        <p class="lead mb-3">
                            Mision: entregar soluciones de movilidad con respaldo, transparencia y calidad para impulsar el crecimiento de nuestros clientes.
                        </p>
                        <p class="text-muted">
                            Valores: confianza, excelencia, servicio cercano y compromiso con cada unidad que entregamos.
                        </p>
                    </div>
                    <div class="col-lg-6 reveal">
                        <div class="about-card">
                            <h3 class="h5">Grow Partner</h3>
                            <p>Somos un equipo especializado en la linea FOTON, con procesos claros y seguimiento postventa.</p>
                            <div class="about-strip">
                                <span>Postventa</span>
                                <span>Calidad</span>
                                <span>Transparencia</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="contacto" class="section-pad">
            <div class="container">
                <div class="section-heading reveal">
                    <h2 class="section-title">Contacto</h2>
                    <p class="section-subtitle">Agenda tu visita o solicita una cotizacion personalizada.</p>
                </div>
                <div class="row g-4 align-items-start">
                    <div class="col-lg-6 reveal">
                        <form class="contact-card" id="contactForm" novalidate>
                            <div class="mb-3">
                                <label for="name" class="form-label">Nombre completo</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                                <div class="invalid-feedback">Ingresa tu nombre.</div>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                                <div class="invalid-feedback">Ingresa un email valido.</div>
                            </div>
                            <div class="mb-3">
                                <label for="message" class="form-label">Mensaje</label>
                                <textarea class="form-control" id="message" name="message" rows="4" required></textarea>
                                <div class="invalid-feedback">Escribe tu consulta.</div>
                            </div>
                            <button type="submit" class="btn btn-accent w-100">Enviar consulta</button>
                            <p class="form-note mt-3">Te responderemos en menos de 24 horas habiles.</p>
                        </form>
                    </div>
                    <div class="col-lg-6 reveal">
                        <div class="map-card">
                            <iframe
                                title="Ubicacion Grow Partner"
                                src="https://maps.google.com/maps?q=buenos%20aires&t=&z=13&ie=UTF8&iwloc=&output=embed"
                                loading="lazy">
                            </iframe>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="footer-shell">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4 reveal">
                    <h4 class="h6 text-uppercase">Grow Partner FOTON</h4>
                    <p class="mb-1">Direccion generica - Ciudad</p>
                    <p class="mb-0">+54 11 0000 0000 | contacto@growpartner.com</p>
                </div>
                <div class="col-md-4 reveal">
                    <h4 class="h6 text-uppercase">Redes</h4>
                    <div class="footer-links">
                        <a href="#">Instagram</a>
                        <a href="#">LinkedIn</a>
                        <a href="#">Facebook</a>
                    </div>
                </div>
                <div class="col-md-4 reveal">
                    <h4 class="h6 text-uppercase">Creditos</h4>
                    <a class="footer-credit" href="https://www.ivan-programador.com" target="_blank" rel="noopener">Todos los derechos reservados por Ivan Svetcoff</a>
                </div>
            </div>
        </div>
    </footer>

    <div class="floating-cta">
        <a href="#catalogo" class="btn btn-outline-graphite btn-sm">Ver catalogo</a>
        <a href="#contacto" class="btn btn-accent btn-sm">Cotizar</a>
    </div>

    <div class="lightbox" id="lightbox" aria-hidden="true">
        <div class="lightbox__backdrop" data-lightbox-close></div>
        <div class="lightbox__content" role="dialog" aria-modal="true" aria-label="Galeria de fotos">
            <button class="lightbox__close" type="button" data-lightbox-close aria-label="Cerrar">x</button>
            <button class="lightbox__nav lightbox__nav--prev" type="button" data-lightbox-prev aria-label="Anterior">‹</button>
            <img class="lightbox__image" alt="">
            <button class="lightbox__nav lightbox__nav--next" type="button" data-lightbox-next aria-label="Siguiente">›</button>
            <div class="lightbox__count" data-lightbox-count></div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>
