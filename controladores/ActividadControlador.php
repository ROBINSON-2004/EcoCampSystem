<?php
require_once __DIR__ . '/../config/constantes.php';
require_once __DIR__ . '/../utilidades/sesion.php';
require_once __DIR__ . '/../utilidades/funciones.php';
require_once __DIR__ . '/../modelos/Actividad.php';

class ActividadControlador {
    
    private $modelo;

    public function __construct() {
        $this->modelo = new Actividad();
    }

    /**
     * Obtiene el listado para el calendario
     */
    public function listarAgendaHoy() {
        // Solo usuarios autenticados pueden ver la agenda
        if (!Sesion::estaAutenticado()) {
            return [];
        }
        return $this->modelo->leerProgramadasHoy();
    }

    /**
     * Procesa la programación de una actividad
     */
    public function guardarProgramacion($datos) {
        // Validar que el usuario sea administrador o trabajador con permisos
        if (Sesion::obtenerTipoUsuario() !== 'administrador') {
            return ['exito' => false, 'mensaje' => 'No tienes permiso para programar actividades.'];
        }

        if (empty($datos['id_actividad']) || empty($datos['id_grupo']) || empty($datos['fecha_actividad'])) {
            return ['exito' => false, 'mensaje' => 'Campos obligatorios faltantes.'];
        }

        if ($this->modelo->programarActividad($datos)) {
            registrar_log("Actividad programada: ID {$datos['id_actividad']} para grupo {$datos['id_grupo']}", 'INFO');
            return ['exito' => true, 'mensaje' => 'Actividad programada correctamente.'];
        }

        return ['exito' => false, 'mensaje' => 'Error al guardar la programación.'];
    }
}