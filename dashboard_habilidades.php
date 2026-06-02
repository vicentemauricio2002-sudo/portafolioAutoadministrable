<?php
// ============================================================
// PANEL DE ADMINISTRACIÓN: dashboard_habilidades.php
// Estructura Académica: Lógica de procesamiento arriba / HTML abajo
// Ubicación: Raíz del proyecto
// ============================================================

// Requerimos la conexión obligatoria a la base de datos
require_once 'db_config.php';

$errores = [];
$mensaje_exito = "";
$hab_a_editar = null; // Guardará la habilidad seleccionada si estamos editando

// ============================================================
// 1. PROCESAMIENTO CRUD (LÓGICA DE BASE DE DATOS)
// ============================================================

// ACCIÓN A: Agregar nueva habilidad o actualizar una existente (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btn_guardar_habilidad'])) {
    $id_habilidad = isset($_POST['id_habilidad']) ? intval($_POST['id_habilidad']) : 0;
    $nombre_input = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    $icono_input  = isset($_POST['icono']) ? trim($_POST['icono']) : '';
    $color_input  = isset($_POST['color']) ? trim($_POST['color']) : '#24292e';

    // Validaciones académicas básicas
    if (empty($nombre_input)) $errores[] = "El nombre de la habilidad es obligatorio.";
    if (empty($icono_input))  $errores[] = "El icono de Bootstrap es obligatorio (ej: bi-github).";

    if (empty($errores)) {
        if ($id_habilidad > 0) {
            // MODO EDICIÓN: Actualizar registro existente
            $stmt = $conn->prepare("UPDATE habilidades SET nombre = ?, icono = ?, color = ? WHERE id = ?");
            $stmt->bind_param("sssi", $nombre_input, $icono_input, $color_input, $id_habilidad);
            if ($stmt->execute()) {
                $mensaje_exito = "¡Habilidad actualizada correctamente con la Base de Datos!";
            } else {
                $errores[] = "Error al actualizar en la base de datos: " . $conn->error;
            }
            $stmt->close();
        } else {
            // MODO CREACIÓN: Insertar nuevo registro
            $stmt = $conn->prepare("INSERT INTO habilidades (nombre, icono, color) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $nombre_input, $icono_input, $color_input);
            if ($stmt->execute()) {
                $mensaje_exito = "¡Nueva habilidad registrada con éxito!";
            } else {
                $errores[] = "Error al insertar en la base de datos: " . $conn->error;
            }
            $stmt->close();
        }
    }
}

// ACCIÓN B: Capturar datos para Editar (GET)
if (isset($_GET['editar_id'])) {
    $id_a_editar = intval($_GET['editar_id']);
    $stmt = $conn->prepare("SELECT * FROM habilidades WHERE id = ?");
    $stmt->bind_param("i", $id_a_editar);
    $stmt->execute();
    $resultado = $stmt->get_result();
    if ($resultado && $resultado->num_rows > 0) {
        $hab_a_editar = $resultado->fetch_assoc();
    }
    $stmt->close();
}

// ACCIÓN C: Eliminar una habilidad (GET)
if (isset($_GET['eliminar_id'])) {
    $id_a_eliminar = intval($_GET['eliminar_id']);
    $stmt = $conn->prepare("DELETE FROM habilidades WHERE id = ?");
    $stmt->bind_param("i", $id_a_eliminar);
    if ($stmt->execute()) {
        header("Location: dashboard_habilidades.php?exito=1");
        exit;
    } else {
        $errores[] = "Error al eliminar el registro: " . $conn->error;
    }
    $stmt->close();
}

// Mensaje de éxito persistente tras redirección de eliminación
if (isset($_GET['exito']) && $_GET['exito'] == 1) {
    $mensaje_exito = "Habilidad eliminada correctamente de la Base de Datos.";
}

// ============================================================
// 2. LECTURA: Obtener todas las habilidades ordenadas descendentemente
// ============================================================
$habilidades = [];
$res_hab = $conn->query("SELECT * FROM habilidades ORDER BY id DESC");
if ($res_hab) {
    while ($fila = $res_hab->fetch_assoc()) {
        $habilidades[] = $fila;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Habilidades</title>
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
                        <a href="dashboard_habilidades.php" class="nav-link text-white active-admin rounded-3 py-2">
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
                    <a href="dashboard_admin.php" class="nav-link text-white rounded-3 py-2.5">
                        <i class="bi bi-person-lines-fill me-2"></i> Mi Biografía
                    </a>
                </li>
                <li class="nav-item">
                    <a href="dashboard_habilidades.php" class="nav-link text-white active-admin rounded-3 py-2.5">
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
                <h1 class="fw-bold text-dark h2 m-0">Gestión de Habilidades Técnicas</h1>
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

            <div class="card admin-card p-4 mb-5">
                <h3 class="fw-bold text-dark mb-4 fs-5 border-bottom pb-2">
                    <?= $hab_a_editar ? '<i class="bi bi-pencil-square text-primary"></i> Editar Habilidad/Herramienta' : '<i class="bi bi-plus-circle text-primary"></i> Agregar Habilidad/Herramienta' ?>
                </h3>
                
                <form action="dashboard_habilidades.php" method="POST">
                    <input type="hidden" name="id_habilidad" value="<?= $hab_a_editar ? $hab_a_editar['id'] : 0 ?>">

                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label for="nombre" class="form-label small fw-bold text-secondary">Nombre de la Habilidad</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Ej: Trabajo en Equipo, Liderazgo..."
                                   value="<?= $hab_a_editar ? htmlspecialchars($hab_a_editar['nombre']) : '' ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label for="icono" class="form-label small fw-bold text-secondary">Icono (Bootstrap Icons)</label>
                            <input type="text" class="form-control" id="icono" name="icono" placeholder="Ej: bi-people-fill, bi-lightbulb"
                                   value="<?= $hab_a_editar ? htmlspecialchars($hab_a_editar['icono']) : '' ?>" required>
                        </div>
                        <div class="col-md-2">
                            <label for="color" class="form-label small fw-bold text-secondary">Color del Icono</label>
                            <input type="color" class="form-control form-control-color w-100" id="color" name="color" 
                                   value="<?= $hab_a_editar ? htmlspecialchars($hab_a_editar['color']) : '#24292e' ?>" title="Selecciona un color">
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex gap-2">
                                <button type="submit" name="btn_guardar_habilidad" class="btn btn-primary w-100 fw-bold py-2 shadow-sm small text-uppercase">
                                    <i class="bi bi-save"></i> <?= $hab_a_editar ? 'Actualizar' : 'Guardar' ?>
                                </button>
                                <?php if ($hab_a_editar): ?>
                                    <a href="dashboard_habilidades.php" class="btn btn-outline-secondary py-2 small" title="Cancelar Edición">
                                        <i class="bi bi-x-circle"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="card admin-card p-4 mb-5">
                <h3 class="fw-bold text-dark mb-4 fs-5 border-bottom pb-2">Habilidades/Herramientas en el Portafolio</h3>
                <p class="text-muted small mb-4">Estas son las herramientas y lenguajes de programación mapeados directamente desde tu diseño.</p>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light text-secondary small text-uppercase">
                            <tr>
                                <th style="width: 80px;">Vista</th>
                                <th>Tecnología</th>
                                <th>Icono</th>
                                <th>ColorHex</th>
                                <th class="text-end" style="width: 140px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            <?php foreach ($habilidades as $hab): ?>
                                <tr>
                                    <td>
                                        <div class="rounded-circle d-flex align-items-center justify-content-center border" 
                                             style="width: 45px; height: 45px; background-color: rgba(0,0,0,0.02); color: <?= $hab['color'] ?>;">
                                            <i class="bi <?= $hab['icono'] ?> fs-4"></i>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-dark fs-6"><?= htmlspecialchars($hab['nombre']) ?></span>
                                    </td>
                                    <td>
                                        <code class="text-muted bg-light px-2 py-1 rounded border"><?= htmlspecialchars($hab['icono']) ?></code>
                                    </td>
                                    <td>
                                        <span class="badge font-monospace text-dark border bg-white px-2 py-1">
                                            <span class="d-inline-block rounded-circle me-1" style="width: 8px; height: 8px; background-color: <?= $hab['color'] ?>;"></span>
                                            <?= strtoupper($hab['color']) ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex justify-content-end gap-2">
                                            <a href="dashboard_habilidades.php?editar_id=<?= $hab['id'] ?>" class="btn btn-light btn-sm border text-primary px-2" title="Editar">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <a href="dashboard_habilidades.php?eliminar_id=<?= $hab['id'] ?>" class="btn btn-light btn-sm border text-danger px-2" onclick="return confirm('¿Seguro que deseas eliminar la habilidad <?= htmlspecialchars($hab['nombre']) ?> de la base de datos?')" title="Eliminar">
                                                <i class="bi bi-trash-fill"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if(empty($habilidades)): ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-secondary small">No hay habilidades registradas en la base de datos.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>