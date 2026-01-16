<?php
require_once __DIR__ . '/../config/conexion.php';

/**
 * Clase Asistencia
 * Maneja el registro de presencia de los campistas
 */
class Asistencia {
    private $conexion;
    private $tabla = 'asistencia';
    
    public $id_asistencia;
    public $id_campista;
    public $fecha_asistencia;
    public $hora_entrada;
    public $hora_salida;
    public $estado_asistencia;
    public $registrado_por;
    public $observaciones;

    public function __construct() {
        $database = new Conexion(); [cite: 24]
        $this->conexion = $database->obtenerConexion(); [cite: 24]
    }

    /**
     * Registra o actualiza la asistencia de un campista
     */
    public function registrar() {
        $consulta = "INSERT INTO " . $this->tabla . " 
                    (id_campista, fecha_asistencia, hora_entrada, estado_asistencia, registrado_por, observaciones)
                    VALUES (:id_campista, :fecha, :entrada, :estado, :registrado_por, :obs)
                    ON DUPLICATE KEY UPDATE 
                    estado_asistencia = :estado, 
                    observaciones = :obs"; [cite: 24]
        
        $stmt = $this->conexion->prepare($consulta);
        
        $stmt->bindParam(':id_campista', $this->id_campista);
        $stmt->bindParam(':fecha', $this->fecha_asistencia);
        $stmt->bindParam(':entrada', $this->hora_entrada);
        $stmt->bindParam(':estado', $this->estado_asistencia);
        $stmt->bindParam(':registrado_por', $this->registrado_por);
        $stmt->bindParam(':obs', $this->observaciones);
        
        return $stmt->execute();
    }

    /**
     * Obtiene la lista de asistencia de un grupo para una fecha específica
     */
    public function leerPorGrupoYFecha($id_grupo, $fecha) {
        $consulta = "SELECT c.id_campista, c.nombre, c.apellido, a.estado_asistencia, a.hora_entrada, a.observaciones
                    FROM campistas c
                    JOIN campistas_grupos cg ON c.id_campista = cg.id_campista
                    LEFT JOIN asistencia a ON c.id_campista = a.id_campista AND a.fecha_asistencia = :fecha
                    WHERE cg.id_grupo = :id_grupo AND cg.estado = 'activo'"; [cite: 24]
        
        $stmt = $this->conexion->prepare($consulta);
        $stmt->bindParam(':id_grupo', $id_grupo);
        $stmt->bindParam(':fecha', $fecha);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}