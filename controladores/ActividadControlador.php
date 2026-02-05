<?php
require_once __DIR__ . '/../config/constantes.php';
require_once RUTA_UTILIDADES . '/funciones.php';
require_once RUTA_MODELOS . '/Actividad.php';

/**
 * Clase ActividadControlador
 * Gestiona el flujo de datos entre las vistas y el modelo de Actividades
 */
class ActividadControlador {
    private $conexion;
    
    public function __construct() {
        $database = new Conexion();
        $this->conexion = $database->obtenerConexion();
    }
    
    /**
     * Procesa la creación de una nueva actividad base
     */
    public function crear($datos) {
        // Validación de campos obligatorios
        if (empty($datos['nombre_actividad']) || empty($datos['tipo_actividad'])) {
            return ['exito' => false, 'mensaje' => 'El nombre y el tipo de actividad son obligatorios.'];
        }
        
        try {
            $actividad = new Actividad();
            
            // Mapeo y limpieza de datos
            $actividad->nombre_actividad = limpiar_cadena($datos['nombre_actividad']);
            $actividad->descripcion = !empty($datos['descripcion']) ? limpiar_cadena($datos['descripcion']) : null;
            $actividad->tipo_actividad = $datos['tipo_actividad'];
            $actividad->ubicacion = !empty($datos['ubicacion']) ? limpiar_cadena($datos['ubicacion']) : null;
            
            // Casting a entero para asegurar integridad en la base de datos
            $actividad->duracion_minutos = !empty($datos['duracion_minutos']) ? (int)$datos['duracion_minutos'] : 0;
            $actividad->capacidad_maxima = !empty($datos['capacidad_maxima']) ? (int)$datos['capacidad_maxima'] : 0;
            $actividad->edad_minima = !empty($datos['edad_minima']) ? (int)$datos['edad_minima'] : 0;
            $actividad->edad_maxima = !empty($datos['edad_maxima']) ? (int)$datos['edad_maxima'] : 0;
            
            $actividad->materiales_necesarios = !empty($datos['materiales_necesarios']) ? limpiar_cadena($datos['materiales_necesarios']) : null;
            $actividad->instrucciones = !empty($datos['instrucciones']) ? limpiar_cadena($datos['instrucciones']) : null;
            $actividad->estado = 'activo';
            
            $id = $actividad->crear();
            
            if ($id) {
                // Registrar en el log para la sección "Actividad Reciente" del dashboard
                registrar_log("Creada actividad: " . $actividad->nombre_actividad, 'actividades', $id);
                return ['exito' => true, 'mensaje' => 'Actividad creada correctamente.', 'id_actividad' => $id];
            }
        } catch (Exception $e) {
            registrar_log("Error al crear actividad: " . $e->getMessage(), 'ERROR');
        }
        
        return ['exito' => false, 'mensaje' => MSG_ERROR_GENERAL];
    }
    
    /**
     * Programa una actividad específica para un grupo en una fecha y hora
     */
    public function programar($datos) {
        if (empty($datos['id_actividad']) || empty($datos['id_grupo']) || 
            empty($datos['fecha_actividad']) || empty($datos['hora_inicio'])) {
            return ['exito' => false, 'mensaje' => 'Faltan datos críticos para la programación.'];
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
                registrar_log("Programada actividad ID {$datos['id_actividad']} para grupo {$datos['id_grupo']}", 'programacion', $this->conexion->lastInsertId());
                return ['exito' => true, 'mensaje' => 'Actividad programada con éxito.'];
            }
        } catch (Exception $e) {
            registrar_log("Error al programar: " . $e->getMessage(), 'ERROR');
        }
        
        return ['exito' => false, 'mensaje' => 'No se pudo programar la actividad.'];
    }
    
    /**
     * Recupera las actividades programadas para visualizarlas en el Calendario
     * SOLUCIONA: Fatal error: Call to undefined method ActividadControlador::obtenerProgramadas()
     */
    public function obtenerProgramadas($fecha_inicio = null, $fecha_fin = null, $id_grupo = null) {
        try {
            $query = "SELECT ap.*, a.nombre_actividad, a.ubicacion, g.nombre_grupo,
                             u.nombre as responsable_nombre, u.apellido as responsable_apellido
                      FROM actividades_programadas ap
                      INNER JOIN actividades a ON ap.id_actividad = a.id_actividad
                      INNER JOIN grupos g ON ap.id_grupo = g.id_grupo
                      LEFT JOIN usuarios u ON ap.id_responsable = u.id_usuario
                      WHERE a.estado = 'activo' AND ap.estado != 'cancelada'";
            
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
            registrar_log("Error al obtener calendario: " . $e->getMessage(), 'ERROR');
            return [];
        }
    }
}