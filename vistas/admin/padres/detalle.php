<?php
// 1. Carga de configuración (Subir 3 niveles desde vistas/admin/padres/)
require_once __DIR__ . '/../../../config/constantes.php';
require_once RUTA_UTILIDADES . '/sesion.php';
require_once RUTA_UTILIDADES . '/funciones.php';
require_once RUTA_MODELOS . '/Padre.php';
require_once RUTA_MODELOS . '/Campista.php';

// 2. Control de Acceso: Solo Administradores
Sesion::iniciar();
Sesion::requerirTipoUsuario(TIPO_ADMIN);

// 3. Obtener y validar el ID de usuario desde la URL
$id_usuario = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_usuario <= 0) {
    header('Location: lista.php');
    exit;
}

// 4. Recuperar información del Padre
$padre = new Padre();
$padre->id_usuario = $id_usuario;

if (!$padre->leerPorIdUsuario()) {
    Sesion::establecerMensaje('error', 'El perfil del padre no existe o no se pudo cargar.');
    header('Location: lista.php');
    exit;
}

// 5. Captura de Hijos vinculados (Usando el id_padre interno)
$campista_modelo = new Campista();
$hijos = $campista_modelo->leerPorPadre($padre->id_padre);

$mensaje = Sesion::obtenerMensaje();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de Padre - <?php echo NOMBRE_SITIO; ?></title>
    <link rel="stylesheet" href="<?php echo URL_BASE; ?>/public/css/admin-estilos.css">
    <style>
        .container { max-width: 1000px; margin: 20px auto; padding: 0 20px; font-family: 'Segoe UI', sans-serif; }
        .card { background: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 20px; padding: 25px; }
        .card-title { border-bottom: 2px solid #f0f0f0; padding-bottom: 10px; margin-bottom: 20px; color: #2d3748; display: flex; align-items: center; gap: 10px; }
        .data-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; }
        .data-label { font-size: 0.85rem; color: #718096; font-weight: bold; text-transform: uppercase; }
        .data-value { font-size: 1rem; color: #2d3748; margin-top: 5px; }
        .table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .table th { background: #f7fafc; text-align: left; padding: 12px; color: #4a5568; border-bottom: 2px solid #edf2f7; }
        .table td { padding: 12px; border-bottom: 1px solid #edf2f7; }
        .status-badge { padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: bold; background: #ebf8ff; color: #2b6cb0; }
        .btn-back { background: #4a5568; color: white; text-decoration: none; padding: 8px 15px; border-radius: 5px; font-size: 0.9rem; }
    </style>
</head>
<body>
    <div style="background: #2d3748; padding: 15px 40px; color: white; display: flex; justify-content: space-between; align-items: center;">
        <h2>Ficha del Representante</h2>
        <a href="lista.php" class="btn-back">← Volver a la Lista</a>
    </div>

    <div class="container">
        <?php if ($mensaje): ?>
            <div style="padding: 15px; border-radius: 5px; margin-bottom: 20px; background: #c6f6d5; color: #22543d;">
                <?php echo $mensaje['contenido']; ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <h3 class="card-title">👤 Información Personal</h3>
            <div class="data-grid">
                <div class="data-item">
                    <p class="data-label">Nombre Completo</p>
                    <p class="data-value"><?php echo htmlspecialchars($padre->nombre . ' ' . $padre->apellido); ?></p>
                </div>
                <div class="data-item">
                    <p class="data-label">Correo Electrónico</p>
                    <p class="data-value"><?php echo htmlspecialchars($padre->correo_electronico); ?></p>
                </div>
                <div class="data-item">
                    <p class="data-label">Teléfono de Contacto</p>
                    <p class="data-value"><?php echo htmlspecialchars($padre->telefono); ?></p>
                </div>
            </div>
        </div>

        <div class="card">
            <h3 class="card-title">📍 Dirección y Ubicación</h3>
            <div class="data-grid">
                <div class="data-item">
                    <p class="data-label">Dirección</p>
                    <p class="data-value"><?php echo htmlspecialchars($padre->direccion); ?></p>
                </div>
                <div class="data-item">
                    <p class="data-label">Ciudad</p>
                    <p class="data-value"><?php echo htmlspecialchars($padre->ciudad); ?></p>
                </div>
                <div class="data-item">
                    <p class="data-label">Código Postal</p>
                    <p class="data-value"><?php echo htmlspecialchars($padre->codigo_postal); ?></p>
                </div>
            </div>
        </div>

        <div class="card">
            <h3 class="card-title">👦 Hijos Inscritos</h3>
            <?php if (!empty($hijos)): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nombre del Hijo</th>
                            <th>Edad</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($hijos as $hijo): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($hijo['nombre'] . ' ' . $hijo['apellido']); ?></strong></td>
                                <td><?php echo $hijo['edad']; ?> años</td>
                                <td><span class="status-badge"><?php echo ucfirst($hijo['estado_inscripcion']); ?></span></td>
                                <td>
                                    <a href="../campistas/detalle.php?id=<?php echo $hijo['id_campista']; ?>" style="color: #3182ce; text-decoration: none; font-weight: bold;">Ver Ficha</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="color: #a0aec0; font-style: italic; text-align: center; padding: 20px;">
                    Este representante no tiene hijos inscritos actualmente.
                </p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>