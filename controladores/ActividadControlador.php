<?php
require_once __DIR__ . '/../config/constantes.php';

require_once RUTA_UTILIDADES . '/funciones.php';
require_once RUTA_MODELOS . '/Actividad.php';

class ActividadControlador {
    private $conexion;
    
    public function __construct() {
        $database = new Conexion();
        $this->conexion = $database->obtenerConexion();
    }
    
    /**
     * Crea una nueva actividad
     */
    public function crear($datos) {
        if (empty($datos['nombre_actividad']) || empty($datos['tipo_actividad'])) {
            return ['exito' => false, 'mensaje' => 'Nombre y tipo son obligatorios.'];
        }
        
        try {
            $actividad = new Actividad();
            $actividad->nombre_actividad = limpiar_cadena($datos['nombre_actividad']);
            $actividad->descripcion = !empty($datos['descripcion']) ? limpiar_cadena($datos['descripcion']) : null;
            $actividad->tipo_actividad = $datos['tipo_actividad'];
            $actividad->ubicacion = !empty($datos['ubicacion']) ? limpiar_cadena($datos['ubicacion']) : null;
            $actividad->duracion_minutos = !empty($datos['duracion_minutos']) ? (int)$datos['duracion_minutos'] : null;
            $actividad->capacidad_maxima = !empty($datos['capacidad_maxima']) ? (int)$datos['capacidad_maxima'] : null;
            $actividad->edad_minima = !empty($datos['edad_minima']) ? (int)$datos['edad_minima'] : null;
            $actividad->edad_maxima = !empty($datos['edad_maxima']) ? (int)$datos['edad_maxima'] : null;
            $actividad->materiales_necesarios = !empty($datos['materiales_necesarios']) ? limpiar_cadena($datos['materiales_necesarios']) : null;
            $actividad->instrucciones = !empty($datos['instrucciones']) ? limpiar_cadena($datos['instrucciones']) : null;
            $actividad->estado = $datos['estado'] ?? 'activo';
            
            $id = $actividad->crear();
            if ($id) {
                registrar_log("Actividad creada: ID $id", 'INFO');
                return ['exito' => true, 'mensaje' => 'Actividad creada correctamente.', 'id_actividad' => $id];
            }
        } catch (Exception $e) {
            registrar_log("Error al crear actividad: " . $e->getMessage(), 'ERROR');
        }
        
        return ['exito' => false, 'mensaje' => MSG_ERROR_GENERAL];
    }
    
    /**
     * Programa una actividad para un grupo
     */
    public function programar($datos) {
        if (empty($datos['id_actividad']) || empty($datos['id_grupo']) || 
            empty($datos['fecha_actividad']) || empty($datos['hora_inicio']) || empty($datos['hora_fin'])) {
            return ['exito' => false, 'mensaje' => 'Datos incompletos.'];
        }
        
        try {
            $query = "INSERT INTO actividades_programadas 
                     (id_actividad, id_grupo, fecha_actividad, hora_inicio, hora_fin, 
                      id_responsable, estado, observaciones)
                     VALUES (:actividad, :grupo, :fecha, :hora_inicio, :hora_fin,
                             :responsable, :estado, :observaciones)";
            
            $stmt = $this->conexion->prepare($query);
            $stmt->bindParam(':actividad', $datos['id_actividad']);
            $stmt->bindParam(':grupo', $datos['id_grupo']);
            $stmt->bindParam(':fecha', $datos['fecha_actividad']);
            $stmt->bindParam(':hora_inicio', $datos['hora_inicio']);
            $stmt->bindParam(':hora_fin', $datos['hora_fin']);
            $stmt->bindParam(':responsable', $datos['id_responsable']);
            $estado = $datos['estado'] ?? 'programada';
            $stmt->bindParam(':estado', $estado);
            $stmt->bindParam(':observaciones', $datos['observaciones']);
            
            if ($stmt->execute()) {
                registrar_log("Actividad programada: Actividad {$datos['id_actividad']} - Grupo {$datos['id_grupo']}", 'INFO');
                return ['exito' => true, 'mensaje' => 'Actividad programada correctamente.'];
            }
        } catch (Exception $e) {
            registrar_log("Error al programar actividad: " . $e->getMessage(), 'ERROR');
        }
        
        return ['exito' => false, 'mensaje' => MSG_ERROR_GENERAL];
    }
    
    /**
     * Obtiene actividades programadas
     */
    public function obtenerProgramadas($fecha_inicio = null, $fecha_fin = null, $id_grupo = null) {
        try {
            $query = "SELECT ap.*,
                     a.nombre_actividad, a.tipo_actividad, a.ubicacion,
                     g.nombre_grupo,
                     u.nombre as responsable_nombre, u.apellido as responsable_apellido
                     FROM actividades_programadas ap
                     INNER JOIN actividades a ON ap.id_actividad = a.id_actividad
                     INNER JOIN grupos g ON ap.id_grupo = g.id_grupo
                     LEFT JOIN usuarios u ON ap.id_responsable = u.id_usuario
                     WHERE 1=1";
            
            if ($fecha_inicio && $fecha_fin) {
                $query .= " AND ap.fecha_actividad BETWEEN :inicio AND :fin";
            }
            if ($id_grupo) {
                $query .= " AND ap.id_grupo = :grupo";
            }
            
            $query .= " ORDER BY ap.fecha_actividad ASC, ap.hora_inicio ASC";
            
            $stmt = $this->conexion->prepare($query);
            
            if ($fecha_inicio && $fecha_fin) {
                $stmt->bindParam(':inicio', $fecha_inicio);
                $stmt->bindParam(':fin', $fecha_fin);
            }
            if ($id_grupo) {
                $stmt->bindParam(':grupo', $id_grupo);
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            registrar_log("Error al obtener actividades programadas: " . $e->getMessage(), 'ERROR');
            return [];
        }
    }
    
    /**
     * Actualiza estado de actividad programada
     */
    public function actualizarEstadoProgramada($id, $nuevo_estado) {
        try {
            $query = "UPDATE actividades_programadas SET estado = :estado WHERE id_actividad_programada = :id";
            $stmt = $this->conexion->prepare($query);
            $stmt->bindParam(':estado', $nuevo_estado);
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                return ['exito' => true, 'mensaje' => 'Estado actualizado correctamente.'];
            }
        } catch (Exception $e) {
            registrar_log("Error al actualizar estado: " . $e->getMessage(), 'ERROR');
        }
        
        return ['exito' => false, 'mensaje' => MSG_ERROR_GENERAL];
    }
    
    /**
     * Cancela una actividad programada
     */
    public function cancelarProgramada($id) {
        return $this->actualizarEstadoProgramada($id, 'cancelada');
    }
}
?>