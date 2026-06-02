<?php
// ============================================================
// PANEL DE ADMINISTRACIÓN: dashboard_tecnologias.php
// Estructura Académica: Lógica de procesamiento arriba / HTML abajo
// Ubicación: Raíz del proyecto
// ============================================================

// Requerimos la conexión obligatoria a la base de datos
require_once 'db_config.php';

$errores = [];
$mensaje_exito = "";
$tech_a_editar = null; // Guardará la tecnología seleccionada si estamos editando

// 1. FUNCIÓN AUXILIAR CONGRUENTE: Determina el color según el progreso
if (!function_exists('obtenerColorProgreso')) {
    function obtenerColorProgreso(int $porcentaje) {
        if ($porcentaje >= 0 && $porcentaje <= 25) {
            return 'bg-danger';         // Rojo
        } elseif ($porcentaje >= 26 && $porcentaje <= 49) {
            return 'bg-warning text-dark'; // Amarillo
        } elseif ($porcentaje >= 50 && $porcentaje <= 74) {
            return 'bg-success';        // Verde
        } else {
            return 'bg-primary';        // Azul (75 - 100)
        }
    }
}

// ============================================================
// 2. PROCESAMIENTO CRUD (LÓGICA DE BASE DE DATOS)
// ============================================================

// ACCIÓN A: Agregar nueva tecnología o actualizar una existente (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btn_guardar_tecnologia'])) {
    $id_tecnologia = isset($_POST['id_tecnologia']) ? intval($_POST['id_tecnologia']) : 0;
    $nombre_input  = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    $progreso_input = isset($_POST['progreso']) ? intval($_POST['progreso']) : 0;

    // Validaciones académicas
    if (empty($nombre_input)) {
        $errores[] = "El nombre de la tecnología/barra es obligatorio.";
    }
    if ($progreso_input < 0 || $progreso_input > 100) {
        $errores[] = "El porcentaje de progreso debe ser un número entero entre 0 y 100.";
    }

    if (empty($errores)) {
        if ($id_tecnologia > 0) {
            // Modo Edición: UPDATE
            $sql_update = "UPDATE tecnologias SET nombre = ?, progreso = ? WHERE id = ?";
            $stmt = $conn->prepare($sql_update);
            $stmt->bind_param("sii", $nombre_input, $progreso_input, $id_tecnologia);
            
            if ($stmt->execute()) {
                $mensaje_exito = "¡Tecnología \"{$nombre_input}\" actualizada correctamente!";
            } else {
                $errores[] = "Error al intentar actualizar en la base de datos: " . $conn->error;
            }
            $stmt->close();
        } else {
            // Modo Creación: INSERT
            $sql_insert = "INSERT INTO tecnologias (nombre, progreso) VALUES (?, ?)";
            $stmt = $conn->prepare($sql_insert);
            $stmt->bind_param("si", $nombre_input, $progreso_input);
            
            if ($stmt->execute()) {
                $mensaje_exito = "¡Nueva barra de tecnología \"{$nombre_input}\" añadida con éxito!";
            } else {
                $errores[] = "Error al insertar el registro: " . $conn->error;
            }
            $stmt->close();
        }
    }
}

// ACCIÓN B: Cargar datos en el formulario para editar (GET)
if (isset($_GET['editar_id'])) {
    $id_a_editar = intval($_GET['editar_id']);
    $sql_buscar = "SELECT * FROM tecnologias WHERE id = ?";
    $stmt = $conn->prepare($sql_buscar);
    $stmt->bind_param("i", $id_a_editar);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($resultado && $resultado->num_rows > 0) {
        $tech_a_editar = $resultado->fetch_assoc();
    }
    $stmt->close();
}

// ACCIÓN C: Eliminar registro (GET)
if (isset($_GET['eliminar_id'])) {
    $id_a_eliminar = intval($_GET['eliminar_id']);
    
    $sql_delete = "DELETE FROM tecnologias WHERE id = ?";
    $stmt = $conn->prepare($sql_delete);
    $stmt->bind_param("i", $id_a_eliminar);
    
    if ($stmt->execute()) {
        $mensaje_exito = "La barra de tecnología ha sido removida permanentemente.";
    } else {
        $errores[] = "No se pudo eliminar el registro: " . $conn->error;
    }
    $stmt->close();
}

// ============================================================
// 3. LECTURA COMPLETA: Obtener todas las tecnologías vigentes
// ============================================================
$tecnologias_progreso = [];
$sql_select = "SELECT * FROM tecnologias ORDER BY progreso DESC, nombre ASC";
$res_tech = $conn->query($sql_select);

if ($res_tech) {
    while ($row = $res_tech->fetch_assoc()) {
        $tecnologias_progreso[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Tecnologías Dominadas</title>
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
                        <a href="dashboard_tecnologias.php" class="nav-link text-white active-admin rounded-3 py-2">
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
                    <a href="dashboard_habilidades.php" class="nav-link text-white rounded-3 py-2.5">
                        <i class="bi bi-tools me-2"></i> Habilidades Técnicas
                    </a>
                </li>
                <li class="nav-item">
                    <a href="dashboard_tecnologias.php" class="nav-link text-white active-admin rounded-3 py-2.5">
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
                <h1 class="fw-bold text-dark h2 m-0">Gestión de Tecnologías Dominadas</h1>
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
                    <?= $tech_a_editar ? '<i class="bi bi-pencil-square text-primary"></i> Editar Dominio de Tecnología' : '<i class="bi bi-plus-circle text-primary"></i> Agregar Dominio de Tecnología' ?>
                </h3>
                
                <form action="dashboard_tecnologias.php" method="POST">
                    <input type="hidden" name="id_tecnologia" value="<?= $tech_a_editar ? $tech_a_editar['id'] : 0 ?>">

                    <div class="row g-3 align-items-end">
                        <div class="col-md-5">
                            <label for="nombre" class="form-label small fw-bold text-secondary">Nombre del Lenguaje / Tecnología</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Ej: Python, React, JavaScript..."
                                   value="<?= $tech_a_editar ? htmlspecialchars($tech_a_editar['nombre']) : '' ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label for="progreso" class="form-label small fw-bold text-secondary">Porcentaje de Dominio (0 - 100)</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="progreso" name="progreso" min="0" max="100" placeholder="Ej: 80"
                                       value="<?= $tech_a_editar ? $tech_a_editar['progreso'] : '' ?>" required>
                                <span class="input-group-text fw-bold">%</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex gap-2">
                                <button type="submit" name="btn_guardar_tecnologia" class="btn <?= $tech_a_editar ? 'btn-primary' : 'btn-primary' ?> w-100 fw-bold py-2 shadow-sm small text-uppercase">
                                    <i class="bi bi-save"></i> <?= $tech_a_editar ? 'Actualizar' : 'Guardar' ?>
                                </button>
                                <?php if ($tech_a_editar): ?>
                                    <a href="dashboard_tecnologias.php" class="btn btn-outline-secondary py-2 small" title="Cancelar Edición">
                                        <i class="bi bi-x-circle"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="card admin-card p-4 rounded-4 mb-5">
                <h3 class="fw-bold text-dark mb-3 fs-5 border-bottom pb-2">Barras Activas en el Portafolio</h3>
                <p class="text-muted small mb-4">Los colores cambian automáticamente en base al porcentaje: <span class="text-danger fw-bold">Rojo</span> (0-25%), <span class="text-warning fw-bold">Amarillo</span> (26-49%), <span class="text-success fw-bold">Verde</span> (50-74%), y <span class="text-primary fw-bold">Azul</span> (75-100%).</p>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light text-secondary small">
                            <tr>
                                <th scope="col" class="w-25">Tecnología</th> <th scope="col">Barra</th>                  
                                <th scope="col" class="text-center" style="width: 15%;">Porcentaje</th> 
                                <th scope="col" class="text-end" style="width: 20%;">Acciones</th>     
                            </tr>
                        </thead>
                        <tbody class="small">
                            <?php foreach ($tecnologias_progreso as $tech): ?>
                                <?php 
                                    // Llamamos a la función para inyectar la clase de color correcta automáticamente
                                    $clase_color = obtenerColorProgreso($tech['progreso']); 
                                ?>
                                <tr>
                                    <td>
                                        <span class="fw-bold text-dark fs-6"><?= htmlspecialchars($tech['nombre']) ?></span>
                                    </td>
                                    <td>
                                        <div class="progress" style="height: 18px; border-radius: 6px; background-color: #f0f2f5;">
                                            <div class="progress-bar <?= $clase_color ?> progress-bar-striped progress-bar-animated" 
                                                 role="progressbar" 
                                                 style="width: <?= $tech['progreso'] ?>%;" 
                                                 aria-valuenow="<?= $tech['progreso'] ?>" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100">
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light border text-dark px-2.5 py-1.5 font-monospace fs-6"><?= $tech['progreso'] ?>%</span>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex justify-content-end gap-2">
                                            <a href="dashboard_tecnologias.php?editar_id=<?= $tech['id'] ?>" class="btn btn-light btn-sm border text-primary px-2" title="Editar tecnología">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <a href="dashboard_tecnologias.php?eliminar_id=<?= $tech['id'] ?>" class="btn btn-light btn-sm border text-danger px-2" 
                                               onclick="return confirm('¿Estás completamente seguro de que deseas remover permanentemente la tecnología <?= htmlspecialchars($tech['nombre']) ?>?')" title="Remover barra">
                                                <i class="bi bi-trash-fill"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>

                            <?php if (empty($tecnologias_progreso)): ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-secondary">
                                        <i class="bi bi-info-circle me-1"></i> No se encontraron tecnologías registradas en la base de datos.
                                    </td>
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