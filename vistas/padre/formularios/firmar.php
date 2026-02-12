<?php
/**
 * ============================================
 * VISTA: FIRMAR DOCUMENTO - EcoCampSystem
 * ============================================
 */

// 1. CARGA DE DEPENDENCIAS
require_once __DIR__ . '/../../../config/constantes.php'; 
require_once RUTA_UTILIDADES . '/sesion.php'; 
require_once RUTA_CONTROLADORES . '/FormularioControlador.php'; 
require_once RUTA_CONFIG . '/conexion.php'; 

// 2. SEGURIDAD DE SESIÓN
Sesion::iniciar(); 
Sesion::requerirTipoUsuario(TIPO_PADRE); 

// 3. CAPTURA DE PARÁMETROS
$id_f = isset($_GET['id_f']) ? intval($_GET['id_f']) : 0;
$id_c = isset($_GET['id_c']) ? intval($_GET['id_c']) : 0;

$database = new Conexion();
$db = $database->obtenerConexion();

// 4. OBTENER INFORMACIÓN DEL CONTEXTO
$stmt = $db->prepare("SELECT f.titulo, c.nombre FROM formularios f, campistas c WHERE f.id_formulario = :id_f AND c.id_campista = :id_c");
$stmt->execute([':id_f' => $id_f, ':id_c' => $id_c]);
$datos = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$datos) { 
    header("Location: disponibles.php"); 
    exit; 
}

$mensaje_error = "";

// 5. PROCESAR SUBIDA DEL ARCHIVO (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CORRECCIÓN: Se usa 'usuario_id' para coincidir con la sesión plana detectada
    if (!isset($_SESSION['usuario_id'])) {
        $mensaje_error = "Error: Sesión no válida o expirada. Por favor reinicia sesión.";
    } else {
        $controlador = new FormularioControlador();
        
        // El nombre del input debe ser 'archivo_firmado' para el controlador
        if ($controlador->firmarFormularioPadre($id_f, $id_c, $_FILES['archivo_firmado'])) {
            header("Location: disponibles.php?exito=1");
            exit;
        } else {
            $mensaje_error = "No se pudo subir el PDF. Verifica el tamaño y formato del archivo.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Firmar Documento | <?php echo NOMBRE_SITIO; ?></title>
    <link rel="stylesheet" href="<?php echo URL_PUBLIC; ?>/css/padre.css?v=<?php echo time(); ?>">
</head>
<body style="background: #f8fafc; font-family: 'Segoe UI', sans-serif;">

    <div class="container" style="max-width: 550px; margin: 60px auto; padding: 0 20px;">
        
        <a href="disponibles.php" style="text-decoration: none; color: #64748b; font-size: 0.9rem; display: inline-block; margin-bottom: 20px;">
            ← Volver a disponibles
        </a>

        <?php if ($mensaje_error): ?>
            <div style="background: #fee2e2; border-left: 4px solid #ef4444; color: #991b1b; padding: 15px; border-radius: 6px; margin-bottom: 20px;">
                <strong>⚠️ Atención:</strong> <?php echo $mensaje_error; ?>
            </div>
        <?php endif; ?>

        <div style="background: white; padding: 40px; border-radius: 16px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); text-align: center;">
            <div style="background: #eff6ff; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                <span style="font-size: 1.5rem;">📩</span>
            </div>
            
            <h2 style="color: #1e293b; margin-bottom: 8px;">Subir Documento</h2>
            <p style="color: #64748b; font-size: 0.95rem; line-height: 1.5;">
                Estás cargando el formulario <br>
                <strong style="color: #334155;"><?php echo htmlspecialchars($datos['titulo']); ?></strong> <br>
                para: <strong style="color: #334155;"><?php echo htmlspecialchars($datos['nombre']); ?></strong>
            </p>

            <hr style="border: 0; border-top: 1px solid #f1f5f9; margin: 25px 0;">

            <form action="" method="POST" enctype="multipart/form-data">
                
                <div style="background: #f8fafc; border: 2px dashed #cbd5e1; padding: 30px; border-radius: 12px; margin-bottom: 30px;">
                    <label style="display: block; margin-bottom: 15px; font-weight: 600; color: #475569;">
                        Selecciona el archivo PDF firmado
                    </label>
                    <input type="file" name="archivo_firmado" accept=".pdf" required style="font-size: 0.9rem;">
                    <p style="font-size: 0.8rem; color: #94a3b8; margin-top: 10px;">Solo se permiten archivos en formato .PDF</p>
                </div>

                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 12px;">
                    <button type="submit" style="background: #22c55e; color: white; border: none; padding: 14px; border-radius: 8px; font-weight: bold; cursor: pointer; font-size: 1rem;">
                        Subir y Firmar
                    </button>
                    <a href="disponibles.php" style="background: #f1f5f9; color: #475569; padding: 14px; border-radius: 8px; text-decoration: none; font-weight: 600; display: flex; align-items: center; justify-content: center;">
                        Cancelar
                    </a>
                </div>

            </form>
        </div>
    </div>

</body>
</html>