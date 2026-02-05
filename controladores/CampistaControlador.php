<?php
require_once __DIR__ . '/../config/constantes.php';
require_once RUTA_UTILIDADES . '/funciones.php';
require_once RUTA_MODELOS . '/Campista.php';

/**
 * Controlador de Campistas
 * Maneja la lógica de negocio y validaciones antes de interactuar con el modelo.
 */
class CampistaControlador {
    
    /**
     * Obtiene la lista de campistas para el administrador.
     */
    public function listarTodos($estado = null, $anio = null) {
        $campista_modelo = new Campista();
        return $campista_modelo->leerTodos($estado, $anio);
    }
    
    /**
     * Obtiene la información completa de un campista por ID.
     */
    public function obtenerPorId($id_campista) {
        $campista_modelo = new Campista();
        $campista_modelo->id_campista = $id_campista;
        
        if ($campista_modelo->leerPorId()) {
            return $campista_modelo->obtenerInformacionCompleta();
        }
        return false;
    }
    
    /**
     * Crea un nuevo campista calculando su edad automáticamente.
     */
    public function crear($datos) {
        if (empty($datos['nombre']) || empty($datos['apellido']) || empty($datos['fecha_nacimiento'])) {
            return ['exito' => false, 'mensaje' => 'Campos obligatorios faltantes.'];
        }

        // Cálculo de edad dinámico
        $edad = calcular_edad($datos['fecha_nacimiento']);
        
        try {
            $campista_modelo = new Campista();
            $campista_modelo->nombre = limpiar_cadena($datos['nombre']);
            $campista_modelo->apellido = limpiar_cadena($datos['apellido']);
            $campista_modelo->fecha_nacimiento = $datos['fecha_nacimiento'];
            $campista_modelo->edad = $edad;
            $campista_modelo->genero = $datos['genero'];
            $campista_modelo->id_padre = $datos['id_padre'];
            $campista_modelo->notas_especiales = !empty($datos['notas_especiales']) ? limpiar_cadena($datos['notas_especiales']) : null;
            $campista_modelo->estado_inscripcion = $datos['estado_inscripcion'] ?? INSCRIPCION_PENDIENTE;
            $campista_modelo->anio_inscripcion = $datos['anio_inscripcion'] ?? ANIO_CAMPAMENTO_ACTUAL;
            
            $id = $campista_modelo->crear();
            if ($id) {
                registrar_log("Campista creado ID: $id", 'INFO');
                return ['exito' => true, 'mensaje' => 'Inscripción realizada con éxito.', 'id' => $id];
            }
        } catch (Exception $e) {
            registrar_log("Error crear campista: " . $e->getMessage(), 'ERROR');
        }
        return ['exito' => false, 'mensaje' => MSG_ERROR_GENERAL];
    }
    
    /**
     * Actualiza un campista y recalcula la edad si cambió la fecha de nacimiento.
     */
    public function actualizar($datos) {
        if (empty($datos['id_campista'])) return ['exito' => false, 'mensaje' => 'ID no válido.'];
        
        // Recalcular edad para evitar el error de "0 años"
        $edad = calcular_edad($datos['fecha_nacimiento']);
        
        try {
            $campista_modelo = new Campista();
            $campista_modelo->id_campista = $datos['id_campista'];
            $campista_modelo->nombre = limpiar_cadena($datos['nombre']);
            $campista_modelo->apellido = limpiar_cadena($datos['apellido']);
            $campista_modelo->fecha_nacimiento = $datos['fecha_nacimiento'];
            $campista_modelo->edad = $edad;
            $campista_modelo->genero = $datos['genero'];
            $campista_modelo->notas_especiales = !empty($datos['notas_especiales']) ? limpiar_cadena($datos['notas_especiales']) : null;
            $campista_modelo->estado_inscripcion = $datos['estado_inscripcion'];
            
            if ($campista_modelo->actualizar()) {
                registrar_log("Campista actualizado ID: {$datos['id_campista']}", 'INFO');
                return ['exito' => true, 'mensaje' => MSG_EXITO_ACTUALIZAR];
            }
        } catch (Exception $e) {
            registrar_log("Error actualizar campista: " . $e->getMessage(), 'ERROR');
        }
        return ['exito' => false, 'mensaje' => MSG_ERROR_GENERAL];
    }
    
    /**
     * Borrado lógico del campista.
     */
    public function eliminar($id_campista) {
        try {
            $campista_modelo = new Campista();
            $campista_modelo->id_campista = $id_campista;
            if ($campista_modelo->eliminar()) {
                registrar_log("Campista eliminado ID: $id_campista", 'INFO');
                return ['exito' => true];
            }
        } catch (Exception $e) {
            registrar_log("Error eliminar campista: " . $e->getMessage(), 'ERROR');
        }
        return ['exito' => false, 'mensaje' => MSG_ERROR_GENERAL];
    }
    
    /**
     * Obtiene estadísticas inicializando todas las llaves para evitar Warnings.
     */
    public function obtenerEstadisticas() {
        $campista_modelo = new Campista();
        $datos = $campista_modelo->contarPorEstado();
        
        // Inicialización crucial para evitar "Undefined array key"
        $stats = [
            'total' => 0, 
            'aprobados' => 0, 
            'pendientes' => 0, 
            'rechazados' => 0, 
            'retirados' => 0
        ];
        
        foreach ($datos as $fila) {
            $stats['total'] += $fila['total'];
            
            switch ($fila['estado_inscripcion']) {
                case INSCRIPCION_APROBADO: $stats['aprobados'] = $fila['total']; break;
                case INSCRIPCION_PENDIENTE: $stats['pendientes'] = $fila['total']; break;
                case INSCRIPCION_RECHAZADO: $stats['rechazados'] = $fila['total']; break;
                case INSCRIPCION_RETIRADO: $stats['retirados'] = $fila['total']; break;
            }
        }
        return $stats;
    }
}