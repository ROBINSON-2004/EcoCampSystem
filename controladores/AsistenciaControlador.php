<?php
require_once __DIR__ . '/../config/constantes.php';
require_once __DIR__ . '/../utilidades/sesion.php';
require_once __DIR__ . '/../utilidades/funciones.php';
require_once __DIR__ . '/../modelos/Asistencia.php';

/**
 * Controlador de Asistencia
 * Maneja la lógica de registro de presencia
 */
class AsistenciaControlador {
    private $modelo;

    public function __construct() {
        $this->modelo = new Asistencia();
    }

    /**
     * Guarda la asistencia masiva enviada desde el formulario
     * @param array $datos Datos provenientes de $_POST
     * @return array Respuesta de éxito o error
     */
    public function guardarAsistenciaMasiva($datos) {
        // Validar sesión activa
        if (!Sesion::estaAutenticado()) {
            return ['exito' => false, 'mensaje' => 'Sesión no válida.'];
        }

        $id_usuario_reg = Sesion::obtenerDatosUsuario()['id_usuario'];
        $fecha_asistencia = $datos['fecha'] ?? date('Y-m-d');
        $errores = 0;

        // Verificar que existan datos de asistencia
        if (!isset($datos['asistencia']) || !is_array($datos['asistencia'])) {
            return ['exito' => false, 'mensaje' => 'No hay datos para registrar.'];
        }

        foreach ($datos['asistencia'] as $id_campista => $estado) {
            $this->modelo->id_campista = $id_campista;
            $this->modelo->fecha_asistencia = $fecha_asistencia;
            $this->modelo->estado_asistencia = $estado;
            $this->modelo->hora_entrada = date('H:i:s');
            $this->modelo->registrado_por = $id_usuario_reg;
            $this->modelo->observaciones = $datos['observaciones'][$id_campista] ?? null;

            if (!$this->modelo->registrar()) {
                $errores++;
            }
        }

        if ($errores === 0) {
            registrar_log("Asistencia registrada: Grupo " . ($datos['id_grupo'] ?? 'N/A'), 'INFO');
            return ['exito' => true, 'mensaje' => 'Asistencia guardada correctamente.'];
        }

        return ['exito' => false, 'mensaje' => "Se produjeron $errores errores al guardar."];
    }
}