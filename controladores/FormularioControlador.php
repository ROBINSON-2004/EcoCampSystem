<?php
/**
 * Controlador de Formularios
 */

require_once RUTA_CONFIG . '/constantes.php';
require_once RUTA_CONFIG . '/conexion.php';
require_once RUTA_MODELOS . '/Formulario.php';
require_once RUTA_MODELOS . '/FormularioCampista.php';
require_once RUTA_UTILIDADES .'/subir-archivo.php';

class FormularioControlador {

    private $db;
    private $formulario;
    private $formularioCampista;

    public function __construct() {
        // USAMOS Conexion (no Database)
        $conexion = new Conexion();
        $this->db = $conexion->obtenerConexion();

        $this->formulario = new Formulario($this->db);
        $this->formularioCampista = new FormularioCampista($this->db);
    }

    /* =========================
       ADMIN
       ========================= */

    public function listar($filtros = []) {
        try {
            return [
                'success' => true,
                'formularios' => $this->formulario->obtenerTodos($filtros)
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'mensaje' => $e->getMessage()
            ];
        }
    }

    public function obtener($id) {
        $formulario = $this->formulario->obtenerPorId($id);

        if (!$formulario) {
            return [
                'success' => false,
                'mensaje' => 'Formulario no encontrado'
            ];
        }

        return [
            'success' => true,
            'formulario' => $formulario,
            'estadisticas' => $this->formulario->obtenerEstadisticas($id)
        ];
    }

    public function crear($datos, $archivo = null) {
        if (empty($datos['titulo']) || empty($datos['tipo'])) {
            return [
                'success' => false,
                'mensaje' => 'Título y tipo son obligatorios'
            ];
        }

        $archivo_url = null;

        if ($archivo && $archivo['error'] === UPLOAD_ERR_OK) {
            $subida = subirArchivo($archivo, 'formularios', ['pdf', 'doc', 'docx']);
            if (!$subida['success']) return $subida;
            $archivo_url = $subida['ruta'];
        }

        $this->formulario->titulo = $datos['titulo'];
        $this->formulario->descripcion = $datos['descripcion'] ?? '';
        $this->formulario->archivo_url = $archivo_url;
        $this->formulario->tipo = $datos['tipo'];
        $this->formulario->obligatorio = isset($datos['obligatorio']) ? 1 : 0;
        $this->formulario->activo = isset($datos['activo']) ? 1 : 0;
        $this->formulario->fecha_limite = $datos['fecha_limite'] ?? null;
        $this->formulario->creado_por = $_SESSION['usuario_id'] ?? null;

        if ($this->formulario->crear()) {
            if (!empty($datos['asignar_todos'])) {
                $this->formulario->asignarATodos($this->formulario->id);
            }

            return [
                'success' => true,
                'mensaje' => 'Formulario creado correctamente'
            ];
        }

        return [
            'success' => false,
            'mensaje' => 'No se pudo crear el formulario'
        ];
    }

    /* =========================
       PADRE
       ========================= */

    public function obtenerFormulariosPadre($id_padre, $filtros = []) {
        try {
            return [
                'success' => true,
                'formularios' => $this->formularioCampista->obtenerPorPadre($id_padre, $filtros),
                'pendientes' => $this->formularioCampista->obtenerPendientesPadre($id_padre)
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'mensaje' => $e->getMessage()
            ];
        }
    }

    public function firmarFormulario($id_formulario, $id_campista, $archivo = null) {

        $asignado = $this->formularioCampista
            ->obtenerFormularioCampista($id_formulario, $id_campista);

        if (!$asignado) {
            return [
                'success' => false,
                'mensaje' => 'Formulario no asignado'
            ];
        }

        if ($asignado['firmado']) {
            return [
                'success' => false,
                'mensaje' => 'Formulario ya firmado'
            ];
        }

        $documento_url = null;

        if ($archivo && $archivo['error'] === UPLOAD_ERR_OK) {
            $subida = subirArchivo($archivo, 'documentos', ['pdf']);
            if (!$subida['success']) return $subida;
            $documento_url = $subida['ruta'];
        }

        $ip = $_SERVER['REMOTE_ADDR'] ?? null;

        if ($this->formularioCampista
            ->firmar($id_formulario, $id_campista, $documento_url, $ip)) {

            return [
                'success' => true,
                'mensaje' => 'Formulario firmado correctamente'
            ];
        }

        return [
            'success' => false,
            'mensaje' => 'No se pudo firmar'
        ];
    }
}
