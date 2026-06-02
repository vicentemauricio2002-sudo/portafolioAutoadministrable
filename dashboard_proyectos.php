<?php
// ============================================================
// PANEL DE ADMINISTRACIÓN: dashboard_proyectos.php
// Estructura Académica: Lógica de procesamiento arriba / HTML abajo
// Ubicación: Raíz del proyecto
// ============================================================

// Requerimos la conexión obligatoria a la base de datos
require_once 'db_config.php';

$errores = [];
$mensaje_exito = "";
$proj_a_editar = null; // Guardará el proyecto seleccionado si estamos editando

// ============================================================
// 1. PROCESAMIENTO CRUD (LÓGICA DE BASE DE DATOS)
// ============================================================

// ACCIÓN A: Agregar nuevo proyecto o actualizar uno existente (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btn_guardar_proyecto'])) {
    $id_proyecto   = isset($_POST['id_proyecto']) ? intval($_POST['id_proyecto']) : 0;
    $titulo_input  = isset($_POST['titulo']) ? trim($_POST['titulo']) : '';
    $desc_input    = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '';
    $demo_input    = isset($_POST['url_demo']) ? trim($_POST['url_demo']) : '#';
    $github_input  = isset($_POST['url_github']) ? trim($_POST['url_github']) : '#';
    
    // 1. Definimos la imagen base predeterminada local
    $imagen_final  = 'assets/img/placeholder.png';
    $imagen_anterior = ''; // Variable auxiliar para saber si había una foto previa

    // 2. Si estamos editando, primero recuperamos la imagen que ya está guardada en la BD por si no se sube una nueva
    if ($id_proyecto > 0) {
        $stmt_check = $conn->prepare("SELECT imagen FROM proyectos WHERE id = ?");
        $stmt_check->bind_param("i", $id_proyecto);
        $stmt_check->execute();
        $res_check = $stmt_check->get_result()->fetch_assoc();
        if ($res_check) {
            $imagen_final = $res_check['imagen'];
            $imagen_anterior = $res_check['imagen']; // Guardamos la ruta previa
        }
        $stmt_check->close();
    }

    // Validaciones académicas básicas
    if (empty($titulo_input)) {
        $errores[] = "El título del proyecto es obligatorio.";
    }
    if (empty($desc_input)) {
        $errores[] = "La descripción del proyecto no puede estar vacía.";
    }

    // 3. Procesamos el archivo binario subido si no hay errores previos
    if (empty($errores) && isset($_FILES['imagen_file']) && $_FILES['imagen_file']['error'] === UPLOAD_ERR_OK) {
        $file_tmp  = $_FILES['imagen_file']['tmp_name'];
        $file_name = $_FILES['imagen_file']['name'];
        $file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $extensiones_permitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (in_array($file_ext, $extensiones_permitidas)) {
            // Nombre único para que las imágenes de proyectos distintos no se pisen
            $nuevo_nombre = "proyecto_" . time() . "_" . uniqid() . "." . $file_ext;
            $directorio_destino = "assets/img/" . $nuevo_nombre; 

            if (move_uploaded_file($file_tmp, $directorio_destino)) {
                
                // ============================================================
                // CONTROL DE BASURA AL EDITAR: Borrar imagen anterior si aplica
                // ============================================================
                if (
                    $id_proyecto > 0 && 
                    !empty($imagen_anterior) && 
                    $imagen_anterior !== 'assets/img/placeholder.png' && 
                    file_exists($imagen_anterior)
                ) {
                    unlink($imagen_anterior);
                }

                $imagen_final = $directorio_destino;
            } else {
                $errores[] = "Error al mover la imagen cargada a la carpeta de destino.";
            }
        } else {
            $errores[] = "Formato de imagen no válido. Usa JPG, JPEG, PNG, GIF o WEBP.";
        }
    }

    // Guardar en la Base de Datos si pasa las validaciones
    if (empty($errores)) {
        if ($id_proyecto > 0) {
            // Modo Edición: UPDATE
            $sql_update = "UPDATE proyectos SET titulo = ?, descripcion = ?, url_demo = ?, url_github = ?, imagen = ? WHERE id = ?";
            $stmt = $conn->prepare($sql_update);
            $stmt->bind_param("sssssi", $titulo_input, $desc_input, $demo_input, $github_input, $imagen_final, $id_proyecto);
            
            if ($stmt->execute()) {
                $mensaje_exito = "¡El proyecto \"{$titulo_input}\" ha sido actualizado correctamente!";
            } else {
                $errores[] = "Error al intentar actualizar el proyecto: " . $conn->error;
            }
            $stmt->close();
        } else {
            // Modo Creación: INSERT
            $sql_insert = "INSERT INTO proyectos (titulo, descripcion, url_demo, url_github, imagen) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql_insert);
            $stmt->bind_param("sssss", $titulo_input, $desc_input, $demo_input, $github_input, $imagen_final);
            
            if ($stmt->execute()) {
                $mensaje_exito = "¡Nuevo proyecto \"{$titulo_input}\" publicado con éxito!";
            } else {
                $errores[] = "Error al insertar el proyecto en la base de datos: " . $conn->error;
            }
            $stmt->close();
        }
        
        $proj_a_editar = null;
    }
}

// ACCIÓN B: Cargar datos en el formulario para editar (GET)
if (isset($_GET['editar_id'])) {
    $id_a_editar = intval($_GET['editar_id']);
    $sql_buscar = "SELECT * FROM proyectos WHERE id = ?";
    $stmt = $conn->prepare($sql_buscar);
    $stmt->bind_param("i", $id_a_editar);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($resultado && $resultado->num_rows > 0) {
        $proj_a_editar = $resultado->fetch_assoc();
    }
    $stmt->close();
}

// ACCIÓN C: Eliminar registro (GET)
if (isset($_GET['eliminar_id'])) {
    $id_a_eliminar = intval($_GET['eliminar_id']);
    
    // ============================================================
    // CONTROL DE BASURA AL ELIMINAR: Obtener la ruta de la imagen antes de borrar el registro
    // ============================================================
    $imagen_a_borrar = "";
    $stmt_img = $conn->prepare("SELECT imagen FROM proyectos WHERE id = ?");
    if ($stmt_img) {
        $stmt_img->bind_param("i", $id_a_eliminar);
        $stmt_img->execute();
        $res_img = $stmt_img->get_result()->fetch_assoc();
        if ($res_img) {
            $imagen_a_borrar = $res_img['imagen'];
        }
        $stmt_img->close();
    }

    $sql_delete = "DELETE FROM proyectos WHERE id = ?";
    $stmt = $conn->prepare($sql_delete);
    $stmt->bind_param("i", $id_a_eliminar);
    
    if ($stmt->execute()) {
        $mensaje_exito = "El bloque de proyecto ha sido eliminado de forma permanente.";
        
        // Si el registro se borró con éxito de la BD, procedemos a borrar su archivo del servidor
        if (
            !empty($imagen_a_borrar) && 
            $imagen_a_borrar !== 'assets/img/placeholder.png' && 
            file_exists($imagen_a_borrar)
        ) {
            unlink($imagen_a_borrar);
        }
    } else {
        $errores[] = "No se pudo eliminar el proyecto: " . $conn->error;
    }
    $stmt->close();
}

// ============================================================
// 2. LECTURA COMPLETA: Obtener todos los proyectos vigentes
// ============================================================
$proyectos_existentes = [];
$sql_select = "SELECT * FROM proyectos ORDER BY id DESC";
$res_proj = $conn->query($sql_select);

if ($res_proj) {
    while ($row = $res_proj->fetch_assoc()) {
        $proyectos_existentes[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Proyectos Realizados</title>
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
                        <a href="dashboard_admin.php" class="nav-link text-white rounded-3 py-2">
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
                        <a href="dashboard_proyectos.php" class="nav-link text-white active-admin rounded-3 py-2">
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
                    <a href="dashboard_admin.php" class="nav-link text-white rounded-3 py-2.5">
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
                    <a href="dashboard_proyectos.php" class="nav-link text-white active-admin rounded-3 py-2.5">
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
                <h1 class="fw-bold text-dark h2 m-0">Gestión de Proyectos</h1>
            </div>

            <?php if (!empty($errores)): ?>
                <div class="alert alert-danger border-0 shadow-sm mb-4 rounded-3" role="alert">
                    <h6 class="fw-bold mb-2"><i class="bi bi-exclamation-triangle-fill"></i> Verifica los datos ingresados:</h6>
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

            <div class="card admin-card p-4 mb-5">
                <h3 class="fw-bold text-dark mb-4 fs-5 border-bottom pb-2">
                    <?= $proj_a_editar ? '<i class="bi bi-pencil-square text-primary"></i> Editar Información del Proyecto' : '<i class="bi bi-plus-circle text-primary"></i> Agregar Bloque de Proyecto al Portafolio' ?>
                </h3>

                <form action="dashboard_proyectos.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id_proyecto" value="<?= $proj_a_editar ? $proj_a_editar['id'] : 0 ?>">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="titulo" class="form-label small fw-bold text-secondary">Título del Proyecto</label>
                            <input type="text" class="form-control" id="titulo" name="titulo" placeholder="Ej: E-commerce Web, App de Tareas..." 
                                   value="<?= $proj_a_editar ? htmlspecialchars($proj_a_editar['titulo']) : '' ?>" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="imagen_file" class="form-label small fw-bold text-secondary">Imagen de Portada (Subir Archivo)</label>
                            <input type="file" class="form-control" id="imagen_file" name="imagen_file" accept="image/*">
                            <div class="form-text text-muted mb-0" style="font-size: 0.75rem;">
                                Dejar vacío para usar el placeholder local predeterminado o mantener la imagen actual.
                            </div>
                        </div>

                        <div class="col-12">
                            <label for="descripcion" class="form-label small fw-bold text-secondary">Descripción Breve (Objetivos y tecnologías usadas)</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" rows="3" placeholder="Describe brevemente de qué trata el proyecto..." required><?= $proj_a_editar ? htmlspecialchars($proj_a_editar['descripcion']) : '' ?></textarea>
                        </div>

                        <div class="col-md-6">
                            <label for="url_demo" class="form-label small fw-bold text-secondary">Enlace de la Demo en Vivo (Live Demo)</label>
                            <input type="text" class="form-control" id="url_demo" name="url_demo" placeholder="Ej: https://demo.com o #"
                                   value="<?= $proj_a_editar ? htmlspecialchars($proj_a_editar['url_demo']) : '#' ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="url_github" class="form-label small fw-bold text-secondary">Enlace del Repositorio (GitHub)</label>
                            <input type="text" class="form-control" id="url_github" name="url_github" placeholder="Ej: https://github.com/usuario/repo o #"
                                   value="<?= $proj_a_editar ? htmlspecialchars($proj_a_editar['url_github']) : '#' ?>">
                        </div>

                        <div class="col-md-3 ms-auto">
                            <div class="d-flex gap-2">
                                <button type="submit" name="btn_guardar_proyecto" class="btn btn-primary w-100 fw-bold py-2 shadow-sm small text-uppercase">
                                    <i class="bi bi-save"></i> <?= $proj_a_editar ? 'Actualizar' : 'Guardar' ?>
                                </button>
                                <?php if ($proj_a_editar): ?>
                                    <a href="dashboard_proyectos.php" class="btn btn-outline-secondary py-2 small" title="Cancelar Edición">
                                        <i class="bi bi-x-circle"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="mb-5">
                <h3 class="fw-bold text-dark mb-2 fs-5 border-bottom pb-2">Proyectos Visibles en el Portafolio</h3>
                <p class="text-muted small mb-4">Estructura visual idéntica a los bloques públicos del portafolio.</p>

                <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
                    <?php foreach ($proyectos_existentes as $proj): ?>
                        <div class="col">
                            <div class="card h-100 project-card-view border-0 shadow-sm">
                                <?php 
                                    $img_src = (!empty($proj['imagen'])) ? $proj['imagen'] : 'assets/img/placeholder.png';
                                ?>
                                <img src="<?= htmlspecialchars($img_src) ?>" class="card-img-top object-cover" alt="Portada de <?= htmlspecialchars($proj['titulo']) ?>" style="height: 180px; object-fit: cover;">
                                
                                <div class="card-body d-flex flex-column p-3.5">
                                    <h5 class="fw-bold text-dark mb-2"><?= htmlspecialchars($proj['titulo']) ?></h5>
                                    <p class="text-muted small card-text flex-grow-1 mb-3">
                                        <?= htmlspecialchars($proj['descripcion']) ?>
                                    </p>
                                    
                                    <div class="bg-light rounded-3 p-2.5 mb-3 extra-small text-secondary">
                                        <div class="d-flex align-items-center mb-1.5 text-truncate">
                                            <i class="bi bi-globe me-1 text-primary"></i> <strong>Demo:</strong> <span class="ms-1 text-dark"><?= htmlspecialchars($proj['url_demo']) ?></span>
                                        </div>
                                        <div class="d-flex align-items-center text-truncate">
                                            <i class="bi bi-github me-1 text-dark"></i> <strong>Repo:</strong> <span class="ms-1 text-dark"><?= htmlspecialchars($proj['url_github']) ?></span>
                                        </div>
                                    </div>

                                    <div class="d-flex gap-2 mt-auto pt-2 border-top justify-content-end">
                                        <a href="dashboard_proyectos.php?editar_id=<?= $proj['id'] ?>" class="btn btn-light btn-sm border text-primary px-3 fw-bold" title="Editar">
                                            <i class="bi bi-pencil-square me-1"></i> Editar
                                        </a>
                                        <a href="dashboard_proyectos.php?eliminar_id=<?= $proj['id'] ?>" class="btn btn-light btn-sm border text-danger px-2" 
                                           onclick="return confirm('¿Estás completamente seguro de que deseas remover permanentemente este bloque de proyecto?')" title="Eliminar">
                                            <i class="bi bi-trash-fill"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <?php if (empty($proyectos_existentes)): ?>
                        <div class="col-12 w-100 text-center py-5 text-secondary">
                            <i class="bi bi-folder-x fs-1 d-block mb-2"></i>
                            <span class="small">No hay bloques de proyectos registrados en la base de datos. ¡Agrega el primero arriba!</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>