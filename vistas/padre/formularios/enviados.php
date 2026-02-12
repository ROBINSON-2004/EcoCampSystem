<?php
// ============================================
// CARGA DE CONFIGURACIÓN Y SEGURIDAD
// ============================================
// Se suben tres niveles para acceder a la configuración raíz del sistema.
require_once __DIR__ . '/../../../config/constantes.php';
require_once RUTA_UTILIDADES . '/sesion.php';
require_once RUTA_MODELOS . '/FormularioCampista.php';

// Iniciar el motor de sesiones de PHP.
Sesion::iniciar();

// Validar que el usuario tenga el rol de padre para visualizar esta sección.
Sesion::requerirTipoUsuario(TIPO_PADRE);

// Obtener los datos del usuario actual para filtrar sus envíos.
$usuario = Sesion::obtenerDatosUsuario();

// Si la sesión ha expirado o es inválida, redirigir al login.
if (!$usuario || !isset($usuario['id_usuario'])) {
    header("Location: " . URL_BASE . "/index.php");
    exit;
}

// Instanciar el modelo y obtener la lista de formularios firmados por los hijos de este padre.
$modelo = new FormularioCampista();
$mis_envios = $modelo->obtenerPorPadre($usuario['id_usuario']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial de Envíos | <?php echo NOMBRE_SITIO; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo URL_PUBLIC; ?>/css/estilos.css">
    <link rel="stylesheet" href="<?php echo URL_PUBLIC; ?>/css/padre.css">
</head>
<body>
    <div class="container">
        <header class="header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <div>
                <h1>✅ Formularios Enviados</h1>
                <p>Historial de documentos que has firmado y subido al sistema.</p>
            </div>
            <div class="actions">
                <a href="disponibles.php" class="btn-login" style="text-decoration: none;">Pendientes</a>
                <a href="../dashboard.php" class="btn-secondary" style="text-decoration: none;">Volver al Panel</a>
            </div>
        </header>

        <?php if (isset($_GET['exito'])): ?>
            <div class="alert alert-success" style="margin-bottom: 20px;">
                ¡Documento enviado correctamente! El administrador revisará la información.
            </div>
        <?php endif; ?>

        <div class="card-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
            <?php if (empty($mis_envios)): ?>
                <div class="card shadow" style="grid-column: 1 / -1; text-align: center; padding: 40px; background: #fff;">
                    <p style="color: #666; font-style: italic;">No has enviado ningún formulario todavía.</p>
                    <a href="disponibles.php" style="color: #1976d2; font-weight: bold;">Ver documentos pendientes</a>
                </div>
            <?php else: ?>
                <?php foreach ($mis_envios as $envio): ?>
                <div class="card shadow" style="padding: 20px; background: white; border-radius: 8px; border-top: 4px solid #4caf50;">
                    <h4 style="margin-bottom: 10px; color: #333;"><?php echo htmlspecialchars($envio['titulo']); ?></h4>
                    <p style="margin-bottom: 5px;"><strong>Campista:</strong> <?php echo htmlspecialchars($envio['campista_nombre']); ?></p>
                    <p style="margin-bottom: 5px;"><strong>Fecha de Envío:</strong> <?php echo date('d/m/Y H:i', strtotime($envio['fecha_firma'])); ?></p>
                    <p style="margin-bottom: 15px;"><strong>Estado:</strong> 
                        <span class="badge" style="background: #e8f5e9; color: #2e7d32; padding: 4px 8px; border-radius: 4px; font-size: 11px;">
                            <?php echo strtoupper($envio['estado']); ?>
                        </span>
                    </p>
                    
                    <div style="border-top: 1px solid #eee; padding-top: 15px; display: flex; justify-content: space-between; align-items: center;">
                        <a href="<?php echo URL_UPLOADS . '/documentos/' . $envio['archivo_firmado']; ?>" target="_blank" style="color: #1976d2; text-decoration: none; font-weight: bold; font-size: 14px;">
                            📄 Ver Comprobante PDF
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>