<?php
require_once __DIR__ . '/../../../config/constantes.php';
require_once RUTA_UTILIDADES . '/sesion.php';
require_once RUTA_MODELOS . '/FormularioCampista.php';

Sesion::iniciar();
Sesion::requerirTipoUsuario(TIPO_ADMINISTRADOR);

$modelo = new FormularioCampista();
$registros = $modelo->obtenerSeguimientoGeneral();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <title>Seguimiento de Formularios | Admin</title>
    <link rel="stylesheet" href="<?php echo URL_PUBLIC; ?>/css/admin.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="container">
        <h1>📑 Seguimiento de Formularios Entregados</h1>
        <table class="table">
            <thead>
                <tr>
                    <th>Campista</th>
                    <th>Documento</th>
                    <th>Tipo</th>
                    <th>Fecha Firma</th>
                    <th>Firmado por</th>
                    <th>Archivo</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($registros as $r): ?>
                <tr>
                    <td><?php echo $r['nombre'] . " " . $r['apellido']; ?></td>
                    <td><?php echo $r['titulo']; ?></td>
                    <td><span class="badge"><?php echo $r['tipo_formulario']; ?></span></td>
                    <td><?php echo date('d/m/Y H:i', strtotime($r['fecha_firma'])); ?></td>
                    <td><?php echo $r['nombre_padre']; ?></td>
                    <td>
                        <a href="<?php echo URL_UPLOADS . '/documentos/' . $r['archivo_firmado']; ?>" target="_blank" class="btn-small">Ver PDF</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>