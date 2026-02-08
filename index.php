<?php
$pdo = require __DIR__ . '/db.php';
$newVehicles = getVehiclesByType($pdo, 'nuevo');
$usedVehicles = getVehiclesByType($pdo, 'usado');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grow Partner - FOTON</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&family=Sora:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <header class="nav">
        <div class="nav__logo">Grow Partner <span>FOTON</span></div>
        <nav class="nav__links">
            <a href="#catalogo">Catalogo</a>
            <a href="#nosotros">Nosotros</a>
            <a href="#contacto">Contacto</a>
            <a href="admin/login.php" class="nav__cta">Admin</a>
        </nav>
        <button class="nav__toggle" aria-label="Abrir menu" aria-expanded="false">☰</button>
    </header>

    <main>
        <section class="hero">
            <div class="hero__content reveal">
                <p class="eyebrow">Concesionario oficial FOTON</p>
                <h1>Calidad y confianza para tu negocio</h1>
                <p>Grow Partner selecciona las mejores unidades nuevas y usadas para crecer con seguridad y estilo.</p>
                <div class="hero__actions">
                    <a href="#catalogo" class="btn btn--primary">Ver catalogo</a>
                    <a href="#contacto" class="btn btn--ghost">Agenda una cita</a>
                </div>
            </div>
            <div class="hero__media reveal">
                <img
                    src="https://images.unsplash.com/photo-1503376780353-7e6692767b70?auto=format&fit=crop&w=1200&q=80"
                    alt="Autos FOTON en exhibicion"
                    loading="eager"
                    decoding="async"
                    fetchpriority="high"
                >
                <div class="hero__badge">Grow Partner</div>
            </div>
        </section>

        <section id="catalogo" class="section">
            <div class="section__header reveal">
                <h2>Catalogo de unidades</h2>
                <p>Opciones nuevas y usadas, revisadas con estandares de fabrica.</p>
            </div>

            <div class="catalog">
                <div class="catalog__block">
                    <div class="catalog__title">
                        <h3>Nuevos</h3>
                        <div class="carousel__controls">
                            <button class="carousel__btn" type="button" data-carousel-prev aria-label="Ver anteriores">Anterior</button>
                            <button class="carousel__btn" type="button" data-carousel-next aria-label="Ver siguientes">Siguiente</button>
                        </div>
                    </div>
                    <div class="carousel" data-carousel>
                        <div class="grid carousel__track">
                         <?php if (count($newVehicles) === 0): ?>
                             <div class="card card--empty reveal">Aun no hay unidades nuevas cargadas.</div>
                         <?php endif; ?>
                         <?php foreach ($newVehicles as $vehicle): ?>
                             <article class="card reveal">
                                <div class="card__image">
                                    <img
                                        src="<?php echo htmlspecialchars($vehicle['image_url']); ?>"
                                        alt="<?php echo htmlspecialchars($vehicle['title']); ?>"
                                        loading="lazy"
                                        decoding="async"
                                    >
                                </div>
                                <div class="card__body">
                                    <h4><?php echo htmlspecialchars($vehicle['title']); ?></h4>
                                    <p><?php echo htmlspecialchars($vehicle['description']); ?></p>
                                    <div class="card__meta">
                                        <span class="price">$<?php echo number_format((float) $vehicle['price'], 2); ?></span>
                                        <span class="tag">Nuevo</span>
                                    </div>
                                </div>
                             </article>
                         <?php endforeach; ?>
                        </div>
                    </div>
                 </div>

                 <div class="catalog__block">
                    <div class="catalog__title">
                        <h3>Usados</h3>
                        <div class="carousel__controls">
                            <button class="carousel__btn" type="button" data-carousel-prev aria-label="Ver anteriores">Anterior</button>
                            <button class="carousel__btn" type="button" data-carousel-next aria-label="Ver siguientes">Siguiente</button>
                        </div>
                    </div>
                    <div class="carousel" data-carousel>
                        <div class="grid carousel__track">
                         <?php if (count($usedVehicles) === 0): ?>
                             <div class="card card--empty reveal">Aun no hay unidades usadas cargadas.</div>
                         <?php endif; ?>
                         <?php foreach ($usedVehicles as $vehicle): ?>
                             <article class="card reveal">
                                <div class="card__image">
                                    <img
                                        src="<?php echo htmlspecialchars($vehicle['image_url']); ?>"
                                        alt="<?php echo htmlspecialchars($vehicle['title']); ?>"
                                        loading="lazy"
                                        decoding="async"
                                    >
                                </div>
                                <div class="card__body">
                                    <h4><?php echo htmlspecialchars($vehicle['title']); ?></h4>
                                    <p><?php echo htmlspecialchars($vehicle['description']); ?></p>
                                    <div class="card__meta">
                                        <span class="price">$<?php echo number_format((float) $vehicle['price'], 2); ?></span>
                                        <span class="tag tag--alt">Usado</span>
                                    </div>
                                </div>
                             </article>
                         <?php endforeach; ?>
                        </div>
                    </div>
                 </div>
             </div>
         </section>

         <section id="nosotros" class="section section--alt">
            <div class="section__header reveal">
                <h2>Nosotros</h2>
            </div>
            <div class="about">
                <div class="about__text reveal">
                    <p><strong>Mision:</strong> entregar soluciones de movilidad con respaldo, transparencia y calidad para impulsar el crecimiento de nuestros clientes.</p>
                    <p><strong>Valores:</strong> confianza, excelencia, servicio cercano y compromiso con cada unidad que entregamos.</p>
                </div>
                <div class="about__highlight reveal">
                    <h3>Grow Partner</h3>
                    <p>Somos un equipo especializado en la linea FOTON, con procesos claros y seguimiento postventa.</p>
                </div>
            </div>
        </section>

        <section id="contacto" class="section">
            <div class="section__header reveal">
                <h2>Contacto</h2>
                <p>Agenda tu visita o solicita una cotizacion personalizada.</p>
            </div>
            <div class="contact">
                <form class="form reveal" id="contactForm" novalidate>
                    <div class="form__row">
                        <label for="name">Nombre completo</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    <div class="form__row">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form__row">
                        <label for="message">Mensaje</label>
                        <textarea id="message" name="message" rows="4" required></textarea>
                    </div>
                    <button type="submit" class="btn btn--primary">Enviar consulta</button>
                    <p class="form__note">Te responderemos en menos de 24 horas habiles.</p>
                </form>
                <div class="map reveal">
                    <iframe
                        title="Ubicacion Grow Partner"
                        src="https://maps.google.com/maps?q=buenos%20aires&t=&z=13&ie=UTF8&iwloc=&output=embed"
                        loading="lazy">
                    </iframe>
                </div>
            </div>
        </section>
    </main>

    <footer class="footer">
        <div class="footer__block reveal">
            <h4>Grow Partner FOTON</h4>
            <p>Direccion generica - Ciudad</p>
            <p>+54 11 0000 0000 | contacto@growpartner.com</p>
        </div>
        <div class="footer__block reveal">
            <h4>Redes</h4>
            <div class="footer__links">
                <a href="#">Instagram</a>
                <a href="#">LinkedIn</a>
                <a href="#">Facebook</a>
            </div>
        </div>
        <div class="footer__copy footer__block reveal">
            <a href="https://www.ivan-programador.com" target="_blank" rel="noopener">Todos los derechos reservados por Ivan Svetcoff</a>
        </div>
    </footer>

    <div class="mobile-cta">
        <a href="#catalogo" class="btn btn--ghost">Ver catalogo</a>
        <a href="#contacto" class="btn btn--primary">Cotizar</a>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>
