<?php
/**
 * ============================================
 * CONTROLADOR DE FORMULARIOS - EcoCampSystem
 * ============================================
 */

require_once __DIR__ . '/../config/constantes.php'; 
require_once RUTA_UTILIDADES . '/funciones.php';   // Provee limpiar_cadena()
require_once RUTA_MODELOS . '/Formulario.php';
require_once RUTA_MODELOS . '/FormularioCampista.php';

class FormularioControlador {

    /**
     * Constructor para asegurar que el motor de sesiones esté activo
     */
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Procesa la subida de una nueva plantilla (Administrador)
     * @param array $datos Datos del formulario ($_POST)
     * @param array $archivo Archivo subido ($_FILES['archivo'])
     */
    public function subirPlantilla($datos, $archivo) {
        // CORRECCIÓN: Se usa 'usuario_id' según el diagnóstico de tu sesión
        if (!isset($_SESSION['usuario_id'])) {
            die("Error: Sesión no válida. Por favor, vuelve a iniciar sesión.");
        }

        $modelo = new Formulario();
        
        // Sanitización y preparación de datos
        $modelo->titulo = limpiar_cadena($datos['titulo']); 
        $modelo->descripcion = limpiar_cadena($datos['descripcion']);
        $modelo->tipo_formulario = $datos['tipo'];
        $modelo->es_obligatorio = isset($datos['obligatorio']) ? 1 : 0;
        $modelo->anio_vigencia = date('Y');
        $modelo->subido_por = $_SESSION['usuario_id']; // ID del administrador
        $modelo->estado = 'activo';

        // Validación de formato PDF
        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        if ($extension !== 'pdf') {
            return false;
        }

        // Generar nombre único y definir rutas
        $nombre_archivo = "plantilla_" . time() . "_" . bin2hex(random_bytes(2)) . ".pdf";
        $ruta_carpeta = RUTA_UPLOADS . "/formularios/";
        $ruta_destino = $ruta_carpeta . $nombre_archivo;
        
        // Crear carpeta si no existe físicamente en XAMPP
        if (!is_dir($ruta_carpeta)) {
            mkdir($ruta_carpeta, 0777, true);
        }

        if (move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
            $modelo->archivo_url = $nombre_archivo;
            return $modelo->crear(); // Inserción en base de datos
        }
        
        return false;
    }

    /**
     * Actualiza una plantilla y gestiona el reemplazo del archivo PDF
     */
    /**
 * Actualiza una plantilla y gestiona el reemplazo del archivo PDF
 */
    public function actualizarPlantilla($id, $datos, $nuevo_archivo = null) {
        if (!isset($_SESSION['usuario_id'])) return false;

        $db = (new Conexion())->obtenerConexion();
        
        // Obtener archivo actual para gestionar el reemplazo
        $stmt = $db->prepare("SELECT archivo_url FROM formularios WHERE id_formulario = :id");
        $stmt->execute([':id' => $id]);
        $archivo_actual = $stmt->fetchColumn();
        $nombre_archivo_final = $archivo_actual;

        // Si se sube un nuevo archivo, reemplazamos el anterior
        if ($nuevo_archivo && $nuevo_archivo['error'] === UPLOAD_ERR_OK) {
            $extension = strtolower(pathinfo($nuevo_archivo['name'], PATHINFO_EXTENSION));
            
            if ($extension === 'pdf') {
                // RUTA CORRECTA (Sin guiones bajos extra)
                $ruta_vieja = RUTA_UPLOADS . "/formularios/" . $archivo_actual;
                
                if (!empty($archivo_actual) && file_exists($ruta_vieja)) {
                    unlink($ruta_vieja); // <-- AQUÍ ESTABA EL ERROR: Asegúrate que diga $ruta_vieja
                }

                // Subir el nuevo archivo
                $nombre_archivo_final = "plantilla_" . time() . ".pdf";
                move_uploaded_file($nuevo_archivo['tmp_name'], RUTA_UPLOADS . "/formularios/" . $nombre_archivo_final);
            }
        }

        $sql = "UPDATE formularios SET titulo = :t, descripcion = :d, estado = :e, archivo_url = :url WHERE id_formulario = :id";
        $upd = $db->prepare($sql);
        return $upd->execute([
            ':t'   => limpiar_cadena($datos['titulo']),
            ':d'   => limpiar_cadena($datos['descripcion']),
            ':e'   => $datos['estado'],
            ':url' => $nombre_archivo_final,
            ':id'  => $id
        ]);
    }

    /**
     * Elimina una plantilla y maneja errores de integridad referencial
     */
    public function eliminarPlantilla($id) {
        if (!isset($_SESSION['usuario_id'])) return "Error de sesión.";

        $db = (new Conexion())->obtenerConexion();
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        try {
            $stmt = $db->prepare("SELECT archivo_url FROM formularios WHERE id_formulario = :id");
            $stmt->execute([':id' => $id]);
            $archivo = $stmt->fetchColumn();

            $del = $db->prepare("DELETE FROM formularios WHERE id_formulario = :id");
            $del->execute([':id' => $id]);

            if ($archivo) {
                $ruta_fisica = RUTA_UPLOADS . "/formularios/" . $archivo;
                if (file_exists($ruta_fisica)) {
                    unlink($ruta_fisica);
                }
            }
            return true;

        } catch (PDOException $e) {
            if ($e->getCode() == '23000') {
                return "No se puede eliminar: El formulario ya ha sido firmado por padres.";
            }
            return "Error: " . $e->getMessage();
        }
    }

    /**
     * Procesa la subida del documento firmado por el Padre
     */
    public function firmarFormularioPadre($id_form, $id_campista, $archivo) {
        // CORRECCIÓN: Se usa 'usuario_id' para consistencia con el portal de padres
        if (!isset($_SESSION['usuario_id'])) return false;

        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        if ($extension !== 'pdf') return false;

        $nombre_archivo = "firmado_" . time() . "_c" . $id_campista . ".pdf";
        $ruta_destino = RUTA_UPLOADS . "/documentos/" . $nombre_archivo;

        if (!is_dir(RUTA_UPLOADS . "/documentos/")) {
            mkdir(RUTA_UPLOADS . "/documentos/", 0777, true);
        }

        if (move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
            $modelo_fc = new FormularioCampista();
            // Registra quién firmó usando la variable de sesión corregida
            return $modelo_fc->registrarFirma($id_form, $id_campista, $_SESSION['usuario_id'], $nombre_archivo);
        }
        
        return false;
    }
}
?>