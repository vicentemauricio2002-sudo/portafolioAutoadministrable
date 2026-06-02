<?php
// ============================================================
// PANEL DE ADMINISTRACIÓN PRINCIPAL: dashboard_admin.php
// Conexión Real a Base de Datos y Procesamiento CRUD Completo
// Ubicación: Raíz del proyecto (al lado de index.php)
// ============================================================

require_once 'db_config.php';

$errores = [];
$mensaje_exito = "";
$etiqueta_a_editar = null; // Guardará la etiqueta seleccionada para edición

// ============================================================
// PRIMERO: LECTURA DE DATOS EN TIEMPO REAL (Movido arriba para inicializar variables)
// ============================================================
$query_bio = "SELECT * FROM biografia WHERE id = 1";
$res_bio = $conn->query($query_bio);
$bio = $res_bio ? $res_bio->fetch_assoc() : null;

$nombre      = $bio['nombre'] ?? 'Vicente Mauricio Ortiz Ortega';
$titulo      = $bio['titulo'] ?? 'Desarrollador Web Full Stack';
$descripcion = $bio['descripcion'] ?? 'Estudiante apasionado por el desarrollo web.';

if (empty($bio['foto_perfil']) || trim($bio['foto_perfil']) === '' || strpos($bio['foto_perfil'], 'placeholder.com') !== false) {
    $foto_actual = 'assets/img/placeholder.png';
} else {
    $foto_actual = $bio['foto_perfil'];
}

// ============================================================
// 1. PROCESAMIENTO: Guardar cambios de la Biografía (POST)
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btn_guardar_bio'])) {
    $nombre_input      = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    $titulo_input      = isset($_POST['titulo']) ? trim($_POST['titulo']) : '';
    $descripcion_input = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '';
    $foto_input        = $foto_actual; // Ahora $foto_actual sí está definida

    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
        
        $nuevo_nombre = "perfil_" . time() . "." . pathinfo($_FILES['foto_perfil']['name'], PATHINFO_EXTENSION);
        $ruta_destino = "assets/img/" . $nuevo_nombre;
        
        if (move_uploaded_file($_FILES['foto_perfil']['tmp_name'], $ruta_destino)) {
            if (
                !empty($foto_actual) && 
                $foto_actual !== 'assets/img/placeholder.png' && 
                file_exists($foto_actual)
            ) {
                unlink($foto_actual);
            }
            
            $foto_input = $ruta_destino;
        } else {
            $errores[] = "Error al guardar la imagen en el servidor.";
        }
    }

    if (empty($nombre_input)) $errores[] = "El campo de nombre no puede estar vacío.";
    if (empty($titulo_input)) $errores[] = "El campo de título profesional no puede estar vacío.";
    if (empty($descripcion_input)) $errores[] = "La descripción o extracto es obligatoria.";

    if (empty($errores)) {
        $stmt = $conn->prepare("UPDATE biografia SET nombre = ?, titulo = ?, descripcion = ?, foto_perfil = ? WHERE id = ?");
        if ($stmt) {
            $id_bio = 1;
            $stmt->bind_param("ssssi", $nombre_input, $titulo_input, $descripcion_input, $foto_input, $id_bio);
            
            if ($stmt->execute()) {
                $mensaje_exito = "¡Biografía académica actualizada correctamente!";
                $foto_actual = $foto_input;
                $nombre      = $nombre_input;
                $titulo      = $titulo_input;
                $descripcion = $descripcion_input;
            } else {
                $errores[] = "Error al actualizar la biografía: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// ============================================================
// 2. PROCESAMIENTO CRUD DE ETIQUETAS (POST y GET)
// ============================================================

// Acción A: Agregar Nueva Etiqueta (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btn_agregar_etiqueta'])) {
    $nombre_etiqueta = isset($_POST['nombre_etiqueta']) ? trim($_POST['nombre_etiqueta']) : '';
    if (!empty($nombre_etiqueta)) {
        $stmt = $conn->prepare("INSERT INTO etiquetas (nombre, destacada) VALUES (?, 0)");
        if ($stmt) {
            $stmt->bind_param("s", $nombre_etiqueta);
            if ($stmt->execute()) {
                $mensaje_exito = "¡Etiqueta agregada con éxito!";
            }
            $stmt->close();
        }
    } else {
        $errores[] = "El nombre de la etiqueta no puede estar vacío.";
    }
}

// Acción B: Guardar Edición de Etiqueta Existente (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btn_actualizar_etiqueta'])) {
    $id_etiqueta = isset($_POST['id_etiqueta']) ? intval($_POST['id_etiqueta']) : 0;
    $nombre_etiqueta = isset($_POST['nombre_etiqueta']) ? trim($_POST['nombre_etiqueta']) : '';
    
    if ($id_etiqueta > 0 && !empty($nombre_etiqueta)) {
        $stmt = $conn->prepare("UPDATE etiquetas SET nombre = ? WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("si", $nombre_etiqueta, $id_etiqueta);
            if ($stmt->execute()) {
                $mensaje_exito = "¡Etiqueta modificada correctamente!";
            }
            $stmt->close();
        }
    } else {
        $errores[] = "El nombre de la etiqueta editada no puede estar vacío.";
    }
}

// Acción C: Destacar Etiqueta (GET)
if (isset($_GET['destacar_id'])) {
    $destacar_id = intval($_GET['destacar_id']);
    $conn->query("UPDATE etiquetas SET destacada = 0");
    $stmt = $conn->prepare("UPDATE etiquetas SET destacada = 1 WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $destacar_id);
        if ($stmt->execute()) {
            $mensaje_exito = "¡Nueva etiqueta destacada en el portafolio!";
        }
        $stmt->close();
    }
}

// Acción D: Eliminar Etiqueta (GET)
if (isset($_GET['eliminar_id'])) {
    $eliminar_id = intval($_GET['eliminar_id']);
    $stmt = $conn->prepare("DELETE FROM etiquetas WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $eliminar_id);
        if ($stmt->execute()) {
            $mensaje_exito = "Etiqueta eliminada del sistema.";
        }
        $stmt->close();
    }
}

// Acción E: Cargar datos en el formulario para Editar (GET)
if (isset($_GET['editar_id'])) {
    $editar_id = intval($_GET['editar_id']);
    $stmt = $conn->prepare("SELECT * FROM etiquetas WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $editar_id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $etiqueta_a_editar = $resultado->fetch_assoc();
        $stmt->close();
    }
}

// ============================================================
// 3. LECTURA DE DATOS EN TIEMPO REAL
// ============================================================
$query_bio = "SELECT * FROM biografia WHERE id = 1";
$res_bio = $conn->query($query_bio);
$bio = $res_bio ? $res_bio->fetch_assoc() : null;

$nombre      = $bio['nombre'] ?? 'Vicente Mauricio Ortiz Ortega';
$titulo      = $bio['titulo'] ?? 'Desarrollador Web Full Stack';
$descripcion = $bio['descripcion'] ?? 'Estudiante apasionado por el desarrollo web.';

if (empty($bio['foto_perfil']) || trim($bio['foto_perfil']) === '' || strpos($bio['foto_perfil'], 'placeholder.com') !== false) {
    $foto_actual = 'assets/img/placeholder.png';
} else {
    $foto_actual = $bio['foto_perfil'];
}

$etiquetas_existentes = [];
$res_etiquetas = $conn->query("SELECT * FROM etiquetas ORDER BY id ASC");
if ($res_etiquetas) {
    while ($fila = $res_etiquetas->fetch_assoc()) {
        $etiquetas_existentes[] = $fila;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Biografía</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="admin-panel">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark d-lg-none p-3 shadow-sm">
        <div class="container-fluid">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-shield-lock-fill text-primary fs-4"></i>
                <span class="fs-6 fw-bold text-uppercase text-white tracking-wider">AdminPanel</span>
            </div>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuResponsivoAdmin" aria-controls="menuResponsivoAdmin" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="menuResponsivoAdmin">
                <ul class="navbar-nav flex-column gap-2 mt-3 w-100">
                    <li class="nav-item">
                        <a href="dashboard_admin.php" class="nav-link text-white active-admin rounded-3 py-2">
                            <i class="bi bi-person-lines-fill me-2"></i> Mi Biografía
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="dashboard_habilidades.php" class="nav-link text-white rounded-3 py-2">
                            <i class="bi bi-tools me-2"></i> Habilidades Técnicas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="dashboard_tecnologias.php" class="nav-link text-white rounded-3 py-2">
                            <i class="bi bi-cpu-fill me-2"></i> Tecnologías
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="dashboard_proyectos.php" class="nav-link text-white rounded-3 py-2">
                            <i class="bi bi-folder-fill me-2"></i> Proyectos
                        </a>
                    </li>
                    <li class="nav-item mt-2">
                        <a href="index.php" class="btn btn-outline-light w-100 btn-sm py-2 rounded-3 fw-bold">
                            <i class="bi bi-box-arrow-left me-1"></i> Salir al Sitio
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <aside class="admin-sidebar d-none d-lg-flex flex-column justify-content-between text-white p-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-4 px-2">
                <i class="bi bi-shield-lock-fill text-primary fs-3"></i>
                <span class="fs-5 fw-bold text-uppercase tracking-wider">AdminPanel</span>
            </div>
            
            <hr class="opacity-20 mb-4">
            
            <ul class="nav nav-pills flex-column gap-2">
                <li class="nav-item">
                    <a href="dashboard_admin.php" class="nav-link text-white active-admin rounded-3 py-2.5">
                        <i class="bi bi-person-lines-fill me-2"></i> Mi Biografía
                    </a>
                </li>
                <li class="nav-item">
                    <a href="dashboard_habilidades.php" class="nav-link text-white rounded-3 py-2.5">
                        <i class="bi bi-tools me-2"></i> Habilidades Técnicas
                    </a>
                </li>
                <li class="nav-item">
                    <a href="dashboard_tecnologias.php" class="nav-link text-white rounded-3 py-2.5">
                        <i class="bi bi-cpu-fill me-2"></i> Tecnologías
                    </a>
                </li>
                <li class="nav-item">
                    <a href="dashboard_proyectos.php" class="nav-link text-white rounded-3 py-2.5">
                        <i class="bi bi-folder-fill me-2"></i> Proyectos
                    </a>
                </li>
            </ul>
        </div>
        
        <div>
            <hr class="opacity-20 mb-3">
            <a href="index.php" class="btn btn-outline-light w-100 btn-sm py-2 rounded-3 fw-bold">
                <i class="bi bi-box-arrow-left me-1"></i> Salir al Sitio
            </a>
        </div>
    </aside>

    <main class="admin-main-content">
        <div class="container-fluid admin-max-container">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="fw-bold text-dark h2 m-0">Gestión de Biografía</h1>
            </div>

            <?php if (!empty($errores)): ?>
                <div class="alert alert-danger border-0 shadow-sm mb-4 rounded-3" role="alert">
                    <h6 class="fw-bold mb-2"><i class="bi bi-exclamation-triangle-fill"></i> Por favor revisa lo siguiente:</h6>
                    <ul class="mb-0 ps-3 small">
                        <?php foreach ($errores as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if (!empty($mensaje_exito)): ?>
                <div class="alert alert-success border-0 shadow-sm mb-4 rounded-3" role="alert">
                    <i class="bi bi-check-circle-fill me-1"></i> <?= htmlspecialchars($mensaje_exito) ?>
                </div>
            <?php endif; ?>

            <form action="dashboard_admin.php" method="POST" enctype="multipart/form-data">
                
                <div class="card admin-card p-3 p-md-5 mb-5">
                    <h3 class="fw-bold text-dark mb-4 fs-5 border-bottom pb-2">Información del Perfil</h3>
                    
                    <div class="row g-4">
                        <div class="col-12 mb-2">
                            <div class="d-flex flex-column flex-sm-row align-items-center gap-4 p-3 border rounded-3 bg-light">
                                <div class="flex-shrink-0 d-flex justify-content-center align-items-center" style="width: 100px; height: 100px;">
                                    <img src="<?= $foto_actual ?>" alt="Miniatura" class="rounded-circle border img-thumbnail shadow-sm bg-white" style="width: 100%; height: 100%; object-fit: cover; aspect-ratio: 1 / 1;">
                                </div>
                                <div class="w-100">
                                    <label for="fotoPerfil" class="form-label fw-bold text-secondary small mb-1">Nueva Foto de Perfil</label>
                                    <input class="form-control form-control-sm" type="file" id="fotoPerfil" name="foto_perfil" accept="image/*">
                                    <div class="form-text small text-muted">Formatos válidos: JPG o PNG. Máximo recomendado: 2MB.</div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control rounded-3" id="nombreInput" name="nombre" placeholder="Nombre" value="<?= htmlspecialchars($nombre) ?>">
                                <label for="nombreInput" class="text-secondary">Nombre Completo</label>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control rounded-3" id="tituloInput" name="titulo" placeholder="Título" value="<?= htmlspecialchars($titulo) ?>">
                                <label for="tituloInput" class="text-secondary">Título Profesional</label>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-floating">
                                <textarea class="form-control rounded-3" id="descripcionTextarea" name="descripcion" placeholder="Descripción" style="min-height: 140px;"><?= htmlspecialchars($descripcion) ?></textarea>
                                <label for="descripcionTextarea" class="text-secondary">Descripción de tu Biografía</label>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 text-center text-md-end">
                        <button type="submit" name="btn_guardar_bio" class="btn btn-primary w-100 w-md-auto px-4 py-2.5 fw-bold rounded-3 shadow-sm text-uppercase small">
                            <i class="bi bi-save me-1"></i> Guardar Cambios
                        </button>
                    </div>
                </div>
            </form>

            <div class="col-12" id="etiquetas">
                <div class="card admin-card p-3 p-md-5 mb-5">
                    <h5 class="fw-bold text-dark mb-4 fs-5 border-bottom pb-2">Etiquetas de Biografía</h5>
                    
                    <p class="text-secondary small mb-4">Administra los tags dinámicos que aparecen debajo de tu título profesional. Destaca la principal en color azul.</p>
                    
                    <div class="list-group list-group-flush rounded-3 border overflow-hidden">
                        <?php foreach ($etiquetas_existentes as $etiqueta): ?>
                            <div class="list-group-item d-flex align-items-center justify-content-between p-3 <?= $etiqueta['destacada'] ? 'bg-light-primary' : '' ?>">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge <?= $etiqueta['destacada'] ? 'bg-primary text-white' : 'bg-light text-dark border' ?> rounded-pill px-3 py-2 fw-bold">
                                        <?= htmlspecialchars($etiqueta['nombre']) ?>
                                    </span>
                                    <?php if ($etiqueta['destacada']): ?>
                                        <small class="text-primary fw-bold text-uppercase p-badge" style="font-size:0.65rem;">Destacada</small>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="btn-group gap-1">
                                    <?php if (!$etiqueta['destacada']): ?>
                                        <a href="dashboard_admin.php?destacar_id=<?= $etiqueta['id'] ?>#etiquetas" class="btn btn-light btn-sm border text-warning px-2.5" title="Destacar esta etiqueta">
                                            <i class="bi bi-star"></i>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <a href="dashboard_admin.php?editar_id=<?= $etiqueta['id'] ?>#etiquetas" class="btn btn-light btn-sm border text-primary px-2.5" title="Editar Nombre">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    
                                    <a href="dashboard_admin.php?eliminar_id=<?= $etiqueta['id'] ?>#etiquetas" class="btn btn-light btn-sm border text-danger px-2.5" onclick="return confirm('¿Seguro que deseas eliminar esta etiqueta?')" title="Eliminar">
                                        <i class="bi bi-trash-fill"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="mt-4 p-3 bg-light rounded-3 border">
                        <?php if ($etiqueta_a_editar): ?>
                            <form action="dashboard_admin.php#etiquetas" method="POST">
                                <input type="hidden" name="id_etiqueta" value="<?= $etiqueta_a_editar['id'] ?>">
                                <label class="form-label small fw-bold text-primary text-uppercase">Modificando Etiqueta</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" name="nombre_etiqueta" value="<?= htmlspecialchars($etiqueta_a_editar['nombre']) ?>" required>
                                    <button type="submit" name="btn_actualizar_etiqueta" class="btn btn-primary fw-bold px-3">
                                        <i class="bi bi-check-lg"></i> Actualizar
                                    </button>
                                    <a href="dashboard_admin.php#etiquetas" class="btn btn-outline-secondary px-2" title="Cancelar">
                                        <i class="bi bi-x-lg"></i>
                                    </a>
                                </div>
                            </form>
                        <?php else: ?>
                            <form action="dashboard_admin.php#etiquetas" method="POST">
                                <label class="form-label small fw-bold text-secondary text-uppercase">Agregar Nueva Etiqueta</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" name="nombre_etiqueta" placeholder="Ej: DevOps" required>
                                    <button type="submit" name="btn_agregar_etiqueta" class="btn btn-outline-primary fw-bold">
                                        <i class="bi bi-plus-lg"></i> Añadir
                                    </button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                    
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>