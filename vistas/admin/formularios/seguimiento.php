<?php
require_once __DIR__ . '/../../../config/constantes.php';
require_once RUTA_UTILIDADES . '/sesion.php';
require_once RUTA_MODELOS . '/FormularioCampista.php';

// Seguridad: Solo administradores
Sesion::iniciar();
Sesion::requerirTipoUsuario(TIPO_ADMINISTRADOR);

$modelo = new FormularioCampista();
$registros = $modelo->obtenerSeguimientoGeneral();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seguimiento de Formularios | Admin</title>
    <link rel="stylesheet" href="<?php echo URL_PUBLIC; ?>/css/admin.css?v=<?php echo time(); ?>">
    <style>
        /* Estilos específicos para la barra de herramientas superior */
        .toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            gap: 20px;
        }
        .search-container {
            position: relative;
            flex-grow: 1;
            max-width: 400px;
        }
        .search-input {
            width: 100%;
            padding: 10px 15px 10px 40px;
            border: 1px solid #cbd5e0;
            border-radius: 8px;
            font-size: 0.9rem;
            outline: none;
            transition: border-color 0.2s;
        }
        .search-input:focus {
            border-color: #3182ce;
            box-shadow: 0 0 0 3px rgba(49, 130, 206, 0.1);
        }
        .search-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #a0aec0;
        }
    </style>
</head>
<body>
    <div class="container">
        
        <div class="toolbar">
            <a href="<?php echo URL_BASE; ?>/vistas/admin/formularios/lista.php" class="btn-back">
                <span>←</span> Volver
            </a>

            <div class="search-container">
                <span class="search-icon">🔍</span>
                <input type="text" id="buscador" class="search-input" placeholder="Buscar por campista, documento o padre...">
            </div>
        </div>

        <header class="header" style="margin-bottom: 20px;">
            <h1>📑 Seguimiento de Formularios</h1>
            <p style="color: #666;">Gestiona y visualiza la documentación entregada por los padres.</p>
        </header>

        <div class="card shadow">
            <table class="table" id="tablaSeguimiento">
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
                    <?php if (!empty($registros)): ?>
                        <?php foreach ($registros as $r): ?>
                        <tr class="fila-registro">
                            <td><strong><?php echo htmlspecialchars($r['nombre'] . " " . $r['apellido']); ?></strong></td>
                            <td><?php echo htmlspecialchars($r['titulo']); ?></td>
                            <td><span class="badge"><?php echo htmlspecialchars($r['tipo_formulario']); ?></span></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($r['fecha_firma'])); ?></td>
                            <td><?php echo htmlspecialchars($r['nombre_padre']); ?></td>
                            <td>
                                <a href="<?php echo URL_UPLOADS . '/documentos/' . $r['archivo_firmado']; ?>" 
                                   target="_blank" class="btn-small" style="background: #3182ce; color: white;">Ver PDF</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 30px; color: #718096;">
                                No se encontraron registros de firmas.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        document.getElementById('buscador').addEventListener('keyup', function() {
            let filtro = this.value.toLowerCase();
            let filas = document.querySelectorAll('.fila-registro');

            filas.forEach(fila => {
                let texto = fila.innerText.toLowerCase();
                fila.style.display = texto.includes(filtro) ? '' : 'none';
            });
        });
    </script>
</body>
</html>