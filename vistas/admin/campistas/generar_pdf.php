<?php
/**
 * ============================================
 * GENERADOR DE FICHA MÉDICA (VISTA DE IMPRESIÓN) - EcoCampSystem
 * ============================================
 */
require_once __DIR__ . '/../../../config/constantes.php';
require_once RUTA_UTILIDADES . '/sesion.php';
require_once RUTA_UTILIDADES . '/funciones.php';
require_once RUTA_CONFIG . '/conexion.php';

// 1. SEGURIDAD
Sesion::iniciar();
Sesion::requerirTipoUsuario(TIPO_ADMINISTRADOR);

// 2. OBTENER ID
$id_campista = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id_campista === 0) die("Error: ID no válido.");

try {
    $db = (new Conexion())->obtenerConexion();

    // 3. CONSULTA DE DATOS
    $query = "SELECT c.*, im.tipo_sangre, im.alergias, im.condiciones_especiales, im.medicamentos 
              FROM campistas c 
              LEFT JOIN informacion_medica im ON c.id_campista = im.id_campista 
              WHERE c.id_campista = :id";
    $stmt = $db->prepare($query);
    $stmt->execute([':id' => $id_campista]);
    $datos = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$datos) die("Error: Campista no encontrado.");

    // 4. CONSULTA DE EMERGENCIA
    $sql_e = "SELECT * FROM informacion_emergencia WHERE id_campista = :id";
    $stmt_e = $db->prepare($sql_e);
    $stmt_e->execute([':id' => $id_campista]);
    $emergencias = $stmt_e->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error de BD: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ficha Médica - <?php echo htmlspecialchars($datos['nombre'] . " " . $datos['apellido']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c5282;
            --secondary-color: #edf2f7;
            --accent-color: #e53e3e;
            --text-color: #2d3748;
            --light-text: #718096;
            --border-color: #e2e8f0;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f7fafc;
            color: var(--text-color);
            line-height: 1.6;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        /* Estilos para el botón de imprimir */
        .print-controls {
            text-align: right;
            margin-bottom: 20px;
        }

        .btn-print {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s;
            display: inline-flex;
            align-items: center;
        }

        .btn-print:hover {
            background-color: #1a365d;
        }

        .btn-print svg {
            margin-right: 8px;
        }

        /* Encabezado del Documento */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 3px solid var(--primary-color);
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .org-name {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
        }

        .doc-title h1 {
            margin: 0;
            font-size: 1.8rem;
            color: var(--primary-color);
            text-transform: uppercase;
        }

        .doc-title p {
            margin: 5px 0 0;
            color: var(--light-text);
            font-size: 0.9rem;
            text-align: right;
        }

        /* Secciones */
        .section {
            margin-bottom: 30px;
        }

        .section-header {
            background-color: var(--secondary-color);
            padding: 10px 15px;
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--primary-color);
            border-left: 5px solid var(--primary-color);
            margin-bottom: 15px;
            border-radius: 0 5px 5px 0;
        }

        /* Grilla de Datos */
        .data-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .data-item {
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 10px;
        }

        .label {
            display: block;
            font-weight: 600;
            color: var(--light-text);
            font-size: 0.85rem;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .value {
            font-size: 1.05rem;
            font-weight: 500;
        }

        /* Alerta Médica */
        .medical-alert {
            background-color: #fff5f5;
            border: 2px solid #feb2b2;
            border-radius: 8px;
            padding: 20px;
        }

        .alert-header {
            display: flex;
            align-items: center;
            color: var(--accent-color);
            font-weight: 700;
            font-size: 1.1rem;
            margin-bottom: 15px;
        }

        .alert-header svg {
            margin-right: 10px;
        }

        .blood-badge {
            background-color: var(--accent-color);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 0.9rem;
        }

        /* Tabla de Emergencia */
        .emergency-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            overflow: hidden;
        }

        .emergency-table th {
            background-color: #e2e8f0;
            color: var(--text-color);
            font-weight: 600;
            text-align: left;
            padding: 12px 15px;
        }

        .emergency-table td {
            padding: 12px 15px;
            border-bottom: 1px solid var(--border-color);
        }

        .emergency-table tr:last-child td {
            border-bottom: none;
        }

        .phone-number {
            font-weight: 700;
            font-family: monospace;
            font-size: 1.1rem;
        }

        /* Pie de Página */
        .footer {
            text-align: center;
            font-size: 0.85rem;
            color: var(--light-text);
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid var(--border-color);
        }

        /* ESTILOS ESPECÍFICOS PARA IMPRESIÓN */
        @media print {
            body {
                background: none;
                padding: 0;
            }
            .container {
                box-shadow: none;
                max-width: 100%;
                width: 100%;
                padding: 0;
                margin: 0;
                border-radius: 0;
            }
            .no-print {
                display: none !important;
            }
            .header {
                margin-top: 0;
            }
            .footer {
                position: fixed;
                bottom: 0;
                width: 100%;
                background: white;
            }
            /* Asegurar que los colores de fondo y bordes se impriman */
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>
</head>
<body>

<div class="print-controls no-print">
    <button onclick="window.print()" class="btn-print">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
        Imprimir Ficha
    </button>
</div>

<div class="container">
    <div class="header">
        <div class="org-name">EcoCamp System</div>
        <div class="doc-title">
            <h1>Ficha de Seguridad Médica</h1>
            <p>Ciclo: <?php echo date('Y'); ?></p>
        </div>
    </div>

    <div class="section">
        <div class="section-header">Identificación del Campista</div>
        <div class="data-grid">
            <div class="data-item">
                <span class="label">Nombre Completo</span>
                <span class="value"><?php echo htmlspecialchars($datos['nombre'] . ' ' . $datos['apellido']); ?></span>
            </div>
            <div class="data-item">
                <span class="label">Edad / Género</span>
                <span class="value"><?php echo $datos['edad']; ?> años / <?php echo ucfirst($datos['genero']); ?></span>
            </div>
            <div class="data-item">
                <span class="label">Año de Inscripción</span>
                <span class="value"><?php echo $datos['anio_inscripcion']; ?></span>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-header">Información Médica Crítica</div>
        <div class="medical-alert">
            <div class="alert-header">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                ALERTAS DE SALUD
            </div>
            <div class="data-grid">
                <div class="data-item" style="border-bottom: none;">
                    <span class="label">Tipo de Sangre</span>
                    <span class="value"><span class="blood-badge"><?php echo $datos['tipo_sangre'] ?: 'N/A'; ?></span></span>
                </div>
                <div class="data-item" style="border-bottom: none;">
                    <span class="label">Alergias Conocidas</span>
                    <span class="value" style="color: var(--accent-color); font-weight: 700;">
                        <?php echo strtoupper($datos['alergias'] ?: 'NINGUNA'); ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="data-grid" style="margin-top: 20px;">
            <div class="data-item">
                <span class="label">Condiciones / Diagnósticos</span>
                <span class="value"><?php echo nl2br(htmlspecialchars($datos['condiciones_especiales'] ?: 'Sin observaciones')); ?></span>
            </div>
            <div class="data-item">
                <span class="label">Medicamentos Actuales</span>
                <span class="value"><?php echo nl2br(htmlspecialchars($datos['medicamentos'] ?: 'No consume')); ?></span>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-header">Contactos de Emergencia</div>
        <?php if ($emergencias): ?>
        <table class="emergency-table">
            <thead>
                <tr>
                    <th>Nombre del Contacto</th>
                    <th>Parentesco</th>
                    <th>Teléfono</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($emergencias as $e): ?>
                <tr>
                    <td><?php echo htmlspecialchars($e['nombre_contacto']); ?></td>
                    <td><?php echo htmlspecialchars($e['parentesco']); ?></td>
                    <td class="phone-number"><?php echo htmlspecialchars($e['telefono']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p class="value" style="color: var(--accent-color);">No hay contactos de emergencia registrados.</p>
        <?php endif; ?>
    </div>

    <div class="footer">
        Documento oficial de EcoCampSystem - Pujilí, Ecuador. <br>
        Generado el: <?php echo date('d/m/Y H:i:s'); ?>
    </div>
</div>

</body>
</html>