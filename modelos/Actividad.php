<?php
require_once __DIR__ . '/../config/conexion.php';

/**
 * Clase Actividad
 * Modelo para gestionar el catálogo de actividades y su programación
 */
class Actividad {
    private $conexion;
    private $tabla = 'actividades';
    private $tabla_programada = 'actividades_programadas';
    
    // Propiedades de Actividad (Catálogo)
    public $id_actividad;
    public $nombre_actividad;
    public $descripcion;
    public $tipo_actividad;
    public $ubicacion;
    public $duracion_minutos;
    public $capacidad_maxima;
    public $edad_minima;
    public $edad_maxima;
    public $materiales_necesarios;
    public $instrucciones;
    public $estado;

    /**
     * Constructor
     */
    public function __construct() {
        $database = new Conexion();
        $this->conexion = $database->obtenerConexion();
    }

    /**
     * Crea una nueva actividad en el catálogo
     */
    public function crear() {
        $consulta = "INSERT INTO " . $this->tabla . " 
                    (nombre_actividad, descripcion, tipo_actividad, ubicacion, duracion_minutos, 
                     capacidad_maxima, edad_minima, edad_maxima, materiales_necesarios, instrucciones, estado)
                    VALUES (:nombre, :descripcion, :tipo, :ubicacion, :duracion, :capacidad, :emin, :emax, :materiales, :instrucciones, :estado)";
        
        $stmt = $this->conexion->prepare($consulta);

        // Limpieza de datos (Siguiendo tu estilo en Usuario.php)
        $this->nombre_actividad = htmlspecialchars(strip_tags($this->nombre_actividad));
        $this->tipo_actividad = htmlspecialchars(strip_tags($this->tipo_actividad));
        $this->ubicacion = htmlspecialchars(strip_tags($this->ubicacion));

        $stmt->bindParam(':nombre', $this->nombre_actividad);
        $stmt->bindParam(':descripcion', $this->descripcion);
        $stmt->bindParam(':tipo', $this->tipo_actividad);
        $stmt->bindParam(':ubicacion', $this->ubicacion);
        $stmt->bindParam(':duracion', $this->duracion_minutos);
        $stmt->bindParam(':capacidad', $this->capacidad_maxima);
        $stmt->bindParam(':emin', $this->edad_minima);
        $stmt->bindParam(':emax', $this->edad_maxima);
        $stmt->bindParam(':materiales', $this->materiales_necesarios);
        $stmt->bindParam(':instrucciones', $this->instrucciones);
        $stmt->bindParam(':estado', $this->estado);

        if ($stmt->execute()) {
            return $this->conexion->lastInsertId();
        }
        return false;
    }

    /**
     * Programa una actividad para un grupo específico
     */
    public function programarActividad($datos) {
        $consulta = "INSERT INTO " . $this->tabla_programada . " 
                    (id_actividad, id_grupo, fecha_actividad, hora_inicio, hora_fin, id_responsable, observaciones)
                    VALUES (:id_act, :id_grp, :fecha, :h_inicio, :h_fin, :id_resp, :obs)";
        
        $stmt = $this->conexion->prepare($consulta);
        
        $stmt->bindParam(':id_act', $datos['id_actividad']);
        $stmt->bindParam(':id_grp', $datos['id_grupo']);
        $stmt->bindParam(':fecha', $datos['fecha_actividad']);
        $stmt->bindParam(':h_inicio', $datos['hora_inicio']);
        $stmt->bindParam(':h_fin', $datos['hora_fin']);
        $stmt->bindParam(':id_resp', $datos['id_responsable']);
        $stmt->bindParam(':obs', $datos['observaciones']);

        return $stmt->execute();
    }

    /**
     * Obtiene las actividades programadas filtradas (VISTA_ACTIVIDADES_HOY)
     */
    public function leerProgramadasHoy() {
        $consulta = "SELECT * FROM vista_actividades_hoy";
        $stmt = $this->conexion->prepare($consulta);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>