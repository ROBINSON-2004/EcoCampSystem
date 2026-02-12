<?php
require_once __DIR__ . '/../../../config/constantes.php';
require_once RUTA_UTILIDADES . '/sesion.php';
require_once RUTA_UTILIDADES . '/funciones.php';
require_once RUTA_CONTROLADORES . '/PadreControlador.php';
require_once RUTA_CONFIG . '/conexion.php';

// Requerir autenticación de administrador
Sesion::requerirTipoUsuario(TIPO_ADMINISTRADOR);

// Verificar ID
if (!isset($_GET['id']) || !es_numero_valido($_GET['id'])) {
    Sesion::establecerMensaje('error', 'ID de padre no válido.');
    header('Location: lista.php');
    exit();
}

$id_padre = (int)$_GET['id'];

// Obtener datos del padre
$controlador = new PadreControlador();
$padre = $controlador->obtenerPorId($id_padre);

if (!$padre) {
    Sesion::establecerMensaje('error', 'Padre no encontrado.');
    header('Location: lista.php');
    exit();
}

// Capturar hijos aprobados
$database = new Conexion();
$db = $database->obtenerConexion();
$stmt_hijos = $db->prepare("SELECT * FROM campistas WHERE id_padre = :id_p AND estado_inscripcion = 'aprobado'");
$stmt_hijos->execute([':id_p' => $id_padre]);
$hijos_aprobados = $stmt_hijos->fetchAll(PDO::FETCH_ASSOC);

// Procesar acciones (activar/desactivar)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    if ($_POST['accion'] === 'desactivar') {
        $resultado = $controlador->desactivar($padre['id_usuario']);
        Sesion::establecerMensaje($resultado['exito'] ? 'exito' : 'error', $resultado['mensaje']);
        header('Location: detalle.php?id=' . $id_padre);
        exit();
    } elseif ($_POST['accion'] === 'activar') {
        $resultado = $controlador->activar($padre['id_usuario']);
        Sesion::establecerMensaje($resultado['exito'] ? 'exito' : 'error', $resultado['mensaje']);
        header('Location: detalle.php?id=' . $id_padre);
        exit();
    }
}

$mensaje = Sesion::obtenerMensaje();
?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de Padre - <?php echo NOMBRE_SITIO; ?></title>
    <link rel="stylesheet" href="<?php echo URL_PUBLIC; ?>/css/admin.css?v=<?php echo time(); ?>">
</head>
<body>

    <div class="container">

        <!-- HEADER -->
        <div class="header">
            <h1>👤 Detalle del Padre</h1>
            <a href="lista.php" class="btn-back">
                <span>←</span> Volver a la lista
            </a>
        </div>

        <!-- MENSAJES -->
        <?php if ($mensaje): ?>
            <div class="alert alert-<?php echo $mensaje['tipo'] === 'exito' ? 'success' : 'error'; ?>">
                <?php echo $mensaje['contenido']; ?>
            </div>
        <?php endif; ?>

        <!-- INFORMACIÓN PERSONAL -->
        <div class="card shadow">
            <div class="info-section">
                <h2>📋 Información Personal</h2>

                <span class="badge 
                    <?php echo $padre['estado'] === ESTADO_ACTIVO ? 'badge-active' : 'badge-inactive'; ?>">
                    <?php echo ucfirst($padre['estado']); ?>
                </span>
            </div>

            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Nombre Completo</div>
                    <div class="info-value">
                        <?php echo htmlspecialchars($padre['nombre'] . ' ' . $padre['apellido']); ?>
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-label">Correo Electrónico</div>
                    <div class="info-value">
                        <?php echo htmlspecialchars($padre['correo_electronico']); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- HIJOS APROBADOS -->
        <div class="card shadow">
            <h2 class="item-title">👶 Hijos Inscritos y Aprobados</h2>

            <?php if (count($hijos_aprobados) > 0): ?>

                <div style="overflow-x:auto;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Edad</th>
                                <th>Género</th>
                                <th>Estado</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($hijos_aprobados as $hijo): ?>
                                <tr>
                                    <td>
                                        <strong>
                                            <?php echo htmlspecialchars($hijo['nombre'] . ' ' . $hijo['apellido']); ?>
                                        </strong>
                                    </td>
                                    <td><?php echo $hijo['edad']; ?> años</td>
                                    <td><?php echo ucfirst($hijo['genero']); ?></td>
                                    <td>
                                        <span class="badge badge-success">
                                            Aprobado
                                        </span>
                                    </td>
                                    <td>
                                        <a href="../campistas/detalle.php?id=<?php echo $hijo['id_campista']; ?>" 
                                           class="btn-secondary">
                                            Ver Perfil
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            <?php else: ?>
                <div class="empty-message">
                    <div class="warning-icon">👶</div>
                    <p>No se encontraron hijos con inscripción <strong>aprobada</strong>.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- GESTIÓN DE CUENTA -->
        <div class="card shadow">
            <h2 class="item-title">⚙️ Gestión de Cuenta</h2>

            <div class="button-group">

                <a href="editar.php?id=<?php echo $id_padre; ?>" class="btn-login">
                    ✏️ Editar Padre
                </a>

                <?php if ($padre['estado'] === ESTADO_ACTIVO): ?>
                    <form method="POST" onsubmit="return confirm('¿Desactivar acceso de este padre?');">
                        <input type="hidden" name="accion" value="desactivar">
                        <button type="submit" class="btn-danger-confirm">
                            🚫 Desactivar
                        </button>
                    </form>
                <?php else: ?>
                    <form method="POST">
                        <input type="hidden" name="accion" value="activar">
                        <button type="submit" class="btn-login">
                            ✅ Activar
                        </button>
                    </form>
                <?php endif; ?>

            </div>
        </div>

    </div>

</body>
</html>