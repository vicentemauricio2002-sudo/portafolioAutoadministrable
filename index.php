<?php
// ============================================================
// PORTAFOLIO PÚBLICO DINÁMICO: index.php (Bloque de Inicialización)
// Conexión real y lectura de datos desde la Base de Datos
// ============================================================
require_once 'db_config.php';

// 1. FUNCIÓN AUXILIAR: Determina automáticamente la clase de Bootstrap según el progreso de la tecnología
if (!function_exists('obtenerColorProgreso')) {
    function obtenerColorProgreso(int $porcentaje) {
        if ($porcentaje >= 0 && $porcentaje <= 25) {
            return 'bg-danger';         // Rojo (Básico)
        } elseif ($porcentaje >= 26 && $porcentaje <= 49) {
            return 'bg-warning text-dark'; // Amarillo (Intermedio Bajo)
        } elseif ($porcentaje >= 50 && $porcentaje <= 74) {
            return 'bg-success';        // Verde (Intermedio Alto)
        } else {
            return 'bg-primary';        // Azul (Avanzado / 75 - 100)
        }
    }
}

// 2. OBTENER LA BIOGRAFÍA (Fila única con ID = 1)
$query_bio = "SELECT * FROM biografia WHERE id = 1";
$res_bio = $conn->query($query_bio);
$bio = $res_bio ? $res_bio->fetch_assoc() : null;

// Variables de respaldo (Fallback) académicas por si la base de datos está vacía
$nombre      = $bio['nombre'] ?? 'Vicente Mauricio Ortiz Ortega';
$titulo      = $bio['titulo'] ?? 'Desarrollador Web Full Stack';
$descripcion = $bio['descripcion'] ?? 'Estudiante apasionado por el desarrollo web.';

// --- LOGICA DE FOTO CONTROLADA ---
if (empty($bio['foto_perfil']) || trim($bio['foto_perfil']) === '' || strpos($bio['foto_perfil'], 'placeholder.com') !== false) {
    $foto_actual = 'assets/img/placeholder.png';
} else {
    // Si ya subiste tu foto real desde el dashboard, se mostrará esa ruta de forma dinámica
    $foto_actual = $bio['foto_perfil'];
}

// 3. OBTENER LAS ETIQUETAS DE LA BIOGRAFÍA
$etiquetas_existentes = [];
$res_etiquetas = $conn->query("SELECT * FROM etiquetas ORDER BY id ASC");
if ($res_etiquetas) {
    while ($fila = $res_etiquetas->fetch_assoc()) {
        $etiquetas_existentes[] = $fila;
    }
}

// 4. OBTENER LAS HABILIDADES (Tarjetas de especialidades)
$habilidades = [];
$res_hab = $conn->query("SELECT * FROM habilidades ORDER BY id ASC");
if ($res_hab) {
    while ($fila = $res_hab->fetch_assoc()) {
        $habilidades[] = $fila;
    }
}

// 5. OBTENER LAS TECNOLOGÍAS (Barras de progreso)
$tecnologias_progreso = [];
$res_tech = $conn->query("SELECT * FROM tecnologias ORDER BY progreso DESC");
if ($res_tech) {
    while ($fila = $res_tech->fetch_assoc()) {
        $tecnologias_progreso[] = $fila;
    }
}

// 6. OBTENER LOS PROYECTOS (Catálogo del Portafolio)
$proyectos_existentes = [];
$res_proj = $conn->query("SELECT * FROM proyectos ORDER BY id ASC");
if ($res_proj) {
    while ($fila = $res_proj->fetch_assoc()) {
        $proyectos_existentes[] = $fila;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portafolio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="container-fluid p-0 sticky-top">
        <nav class="navbar navbar-expand-lg bg-primary p-3 mb-3">
            <div class="container-fluid">
                <div class="navbar-brand d-flex align-items-center text-white">
                    <h3 class="font-monospace fw-bold mb-0 text-uppercase" style="font-size: 1.35rem; letter-spacing: 1px;">
                        Vicente Ortiz
                    </h3>
                </div>
                
                <button class="navbar-toggler navbar-dark border-white text-white" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarNav">
                    <div class="ms-5 d-flex flex-column flex-lg-row gap-5 me-auto my-5 my-lg-0 g-0">
                        <a class="nav-link fs-6 fw-bold text-white text-decoration-none" href="#biografia">Biografía</a>
                        <a class="nav-link fs-6 fw-bold text-white text-decoration-none" href="#habilidad-herramienta">Habilidades/Herramientas</a>
                        <a class="nav-link fs-6 fw-bold text-white text-decoration-none" href="#tecnologias">Tecnologías</a>
                        <a class="nav-link fs-6 fw-bold text-white text-decoration-none" href="#proyectos">Proyectos</a>
                        <a class="nav-link fs-6 fw-bold text-white text-decoration-none" href="#contacto">Contacto</a>
                    </div>
                    <div class="d-grid d-lg-block ps-3 ps-lg-0 pt-3 pt-lg-0 border-top border-white border-opacity-25 border-lg-0">
                        <button type="button" class="btn btn-light fw-bold text-primary" data-bs-toggle="modal" data-bs-target="#loginModal">
                            Iniciar sesión
                        </button>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <main class="container-fluid px-4">
        <section class="container py-4 py-md-5" id="biografia">
            <div class="mb-3 text-center text-md-start">
                <img src="<?= htmlspecialchars($foto_actual) ?>" 
                    class="rounded-circle img-thumbnail shadow-sm object-cover" 
                    id="imgBiografia"
                    alt="Foto de Perfil">
                <h1 class="fw-bold text-dark fs-1 mt-4"><?= htmlspecialchars($nombre) ?></h1>
                <h1 class="text-secondary fs-4 mb-4"><?= htmlspecialchars($titulo) ?></h1>
                <p class="mb-4 py-1"><?= htmlspecialchars($descripcion) ?></p>
                <div class="mb-4">
                    <?php if (!empty($etiquetas_existentes)): ?>
                        <?php foreach ($etiquetas_existentes as $etiqueta): ?>
                            <?php 
                                // Evaluamos dinámicamente si la etiqueta está destacada en la base de datos
                                if ($etiqueta['destacada'] == 1) {
                                    // Diseño para la primera / destacada: Fondo azul, texto blanco
                                    $clase_badge = "bg-primary text-white";
                                } else {
                                    // Diseño para las demás: Fondo gris claro, texto oscuro o bordes
                                    $clase_badge = "bg-light text-dark border";
                                }
                            ?>
                            <span class="badge <?= $clase_badge ?> rounded-pill px-3 py-2 me-1 small fw-bold shadow-xs">
                                <?= htmlspecialchars($etiqueta['nombre']) ?>
                            </span>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <span class="badge bg-primary text-white rounded-pill px-3 py-2 me-1 small fw-bold">Desarrollo Web</span>
                        <span class="badge bg-light text-dark border rounded-pill px-3 py-2 me-1 small fw-bold">Full Stack</span>
                        <span class="badge bg-light text-dark border rounded-pill px-3 py-2 me-1 small fw-bold">Bases de Datos</span>
                    <?php endif; ?>
                </div>
            </div>
        </section>
        <section class="container-fluid" style="background-color: #f8f9fa;" id="habilidad-herramienta">
            <div class="container py-5 text-center">
                <h1 class="fw-bold text-dark fs-3 mb-5">Habilidades y Herramientas</h1>

                <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4 justify-content-center">
                    <?php if (!empty($habilidades)): ?>
                        <?php foreach ($habilidades as $hab): ?>
                            <div class="col d-flex justify-content-center">
                                <div class="card tech-card border-0 shadow-sm text-center p-4">
                                    <div class="icon-circle d-flex align-items-center justify-content-center mb-3 mx-auto" 
                                        style="background-color: <?= htmlspecialchars($hab['color']) ?>1A; color: <?= htmlspecialchars($hab['color']) ?>;">
                                        <i class="bi <?= htmlspecialchars($hab['icono']) ?> fs-2"></i>
                                    </div>
                                    <h5 class="card-title fw-bold text-dark mb-0 fs-6 text-uppercase font-monospace">
                                        <?= htmlspecialchars($hab['nombre']) ?>
                                    </h5>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12 text-center">
                            <p class="text-muted">No hay habilidades registradas en la base de datos.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <section class="container py-5" id="tecnologias">
            <div class="mb-3 py-3">
                <h1 class="fw-bold text-dark fs-3 text-center mb-5">Tecnologías Dominadas</h1>

                <div class="row g-4 justify-content-center">
                    <?php if (!empty($tecnologias_progreso)): ?>
                        <?php foreach ($tecnologias_progreso as $tech): ?>
                            <div class="col-12 col-md-6">
                                <div class="p-3 bg-white rounded-3 shadow-xs border">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="fw-bold text-secondary font-monospace text-uppercase" style="font-size: 0.85rem;">
                                            <i class="bi bi-code-square me-1 text-primary"></i><?= htmlspecialchars($tech['nombre']) ?>
                                        </span>
                                        <span class="badge bg-light border text-dark font-monospace small">
                                            <?= (int)$tech['progreso'] ?>%
                                        </span>
                                    </div>
                                    <div class="progress rounded-pill" style="height: 12px;">
                                        <div class="progress-bar progress-bar-striped progress-bar-animated <?= obtenerColorProgreso((int)$tech['progreso']) ?> rounded-pill" 
                                            role="progressbar" 
                                            style="width: <?= (int)$tech['progreso'] ?>%" 
                                            aria-valuenow="<?= (int)$tech['progreso'] ?>" 
                                            aria-valuemin="0" 
                                            aria-valuemax="100">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12 text-center">
                            <p class="text-muted">No hay indicadores de tecnología registrados.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
       <section class="container-fluid" style="background-color: #f8f9fa;" id="proyectos">
            <div class="mb-3 mx-5 px-5 py-5 text-center">
                <h1 class="fw-bold text-dark fs-3 mb-5">Proyectos Realizados</h1>

                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 justify-content-center">
                    <?php if (!empty($proyectos_existentes)): ?>
                        <?php foreach ($proyectos_existentes as $proj): ?>
                            <div class="col">
                                <div class="card h-100 border-0 shadow-sm overflow-hidden portfolio-card bg-white">
                                    <div class="position-relative overflow-hidden" style="height: 200px;">
                                        <img src="<?= htmlspecialchars($proj['imagen']) ?>" 
                                            class="card-img-top w-100 h-100 object-cover transition-transform" 
                                            alt="<?= htmlspecialchars($proj['titulo']) ?>">
                                    </div>
                                    <div class="card-body p-4 d-flex flex-column">
                                        <h5 class="card-title fw-bold text-dark mb-2"><?= htmlspecialchars($proj['titulo']) ?></h5>
                                        <p class="card-text text-secondary small mb-4 flex-grow-1">
                                            <?= htmlspecialchars($proj['descripcion']) ?>
                                        </p>
                                        <div class="d-flex flex-column flex-sm-row gap-2 mt-auto justify-content-center">
                                            <a href="<?= htmlspecialchars($proj['url_demo']) ?>" class="btn btn-primary btn-sm px-3 fw-bold rounded-2 shadow-xs w-100" target="_blank">
                                                <i class="bi bi-laptop me-1"></i> Demo
                                            </a>
                                            <a href="<?= htmlspecialchars($proj['url_github']) ?>" class="btn btn-light btn-sm border text-dark px-3 fw-bold rounded-2 shadow-xs w-100" target="_blank">
                                                <i class="bi bi-github me-1"></i> Repositorio
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12 text-center">
                            <p class="text-muted">Próximamente se listarán nuevos proyectos académicos.</p>
                        </div>
                    <?php endif; ?>
                </div>
        </section>
        <section class="container py-5" id="contacto">
            <div class="mb-3 py-3">
                <h1 class="fw-bold text-dark fs-3 text-center mb-4">Formulario de Contacto</h1>

                <div class="card border-0 shadow-sm p-3 p-sm-5 contact-card mx-auto" style="max-width: 750px;">
                    <form action="enviar_contacto.php" method="POST">
                        
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <input type="text" name="nombre" class="form-control custom-input" placeholder="Tu nombre..." required>
                            </div>
                            <div class="col-md-6">
                                <input type="email" name="correo" class="form-control custom-input" placeholder="Tu email..." required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <input type="text" name="asunto" class="form-control custom-input" placeholder="Asunto..." required>
                        </div>

                        <div class="mb-4">
                            <textarea name="mensaje" class="form-control custom-input" rows="6" placeholder="Mensaje..." required></textarea>
                        </div>

                        <button type="submit" name="btn_contacto" class="btn btn-primary w-100 fw-bold d-flex align-items-center justify-content-center gap-2 contact-btn">
                            <i class="bi bi-envelope-fill"></i> ENVIAR MENSAJE
                        </button>
                    </form>
                </div>

            </div>
        </section>
    </main>

    <footer class="custom-footer text-white bg-primary py-5">
        <div class="container">
            <div class="row text-center text-md-start g-4">
                
                <div class="col-md-4 d-flex flex-column justify-content-center align-items-center align-items-md-start">
                    <h5 class="fw-bold mb-3">Mi Portafolio</h5>
                    <p class="mb-1 opacity-75 fs-6">&copy; 2026 Vicente Mauricio Ortiz Ortega</p>
                    <p class="mb-0 opacity-75 fs-6">Todos los derechos reservados</p>
                </div>

                <div class="col-md-4 d-flex flex-column justify-content-center align-items-center">
                    <h5 class="fw-bold mb-3">Sígueme en redes sociales</h5>
                    <div class="d-flex gap-3 social-icons">
                        <a href="#" class="text-white fs-3 transition-icon" target="_blank">
                            <i class="bi bi-github"></i>
                        </a>
                        <a href="#" class="text-white fs-3 transition-icon" target="_blank">
                            <i class="bi bi-linkedin"></i>
                        </a>
                        <a href="#" class="text-white fs-3 transition-icon" target="_blank">
                            <i class="bi bi-instagram"></i>
                        </a>
                    </div>
                </div>

                <div class="col-md-4 d-flex flex-column justify-content-center align-items-center align-items-md-end text-md-end">
                    <h5 class="fw-bold mb-3">Correo Personal</h5>
                    <a href="mailto:contacto@ejemplo.com" class="text-white text-decoration-none d-flex align-items-center gap-2 opacity-90 fs-6 transition-link">
                        <i class="bi bi-envelope"></i> vicentemauricio2002@gmail.com
                    </a>
                </div>

            </div>
        </div>
    </footer>
    <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered login-modal-dialog">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                
                <div class="modal-body p-4 p-sm-5 bg-white position-relative">
                    <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Close"></button>

                    <div class="text-center mb-4 mt-2">
                        <div class="text-primary mb-2">
                            <i class="bi bi-shield-lock-fill display-5"></i>
                        </div>
                        <h2 class="fw-bold text-dark h4 mb-1" id="loginModalLabel">Panel de Control</h2>
                        <p class="text-muted small">Ingresa tus credenciales de administrador para continuar</p>
                    </div>

                    <form action="dashboard_admin.php" method="POST" id="loginModalForm">
                        
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control custom-modal-input" id="modalUsuario" name="usuario" placeholder="Usuario">
                            <label for="modalUsuario" class="text-secondary"><i class="bi bi-person me-1"></i> Usuario</label>
                        </div>

                        <div class="form-floating mb-4">
                            <input type="password" class="form-control custom-modal-input" id="modalPassword" name="password" placeholder="Contraseña">
                            <label for="modalPassword" class="text-secondary"><i class="bi bi-key me-1"></i> Contraseña</label>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2.5 fw-bold rounded-3 shadow-sm text-uppercase">
                            Acceder <i class="bi bi-arrow-right-short ms-1"></i>
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>