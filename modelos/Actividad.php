<?php
require_once RUTA_CONFIG . '/conexion.php';

/**
 * Clase Actividad
 * Modelo para gestionar actividades del campamento con auto-limpieza de calendario
 */
class Actividad {
    private $conexion;
    private $tabla = 'actividades';
    
    // Propiedades
    public $id_actividad;
    public $nombre_actividad;
    public $descripcion;
    public $tipo_actividad;
    public $ubicacion;
    public $fecha_actividad; // Nueva propiedad indispensable para el calendario
    public $duracion_minutos;
    public $capacidad_maxima;
    public $edad_minima;
    public $edad_maxima;
    public $materiales_necesarios;
    public $instrucciones;
    public $estado;
    
    public function __construct() {
        $database = new Conexion();
        $this->conexion = $database->obtenerConexion();
    }
    
    /**
     * OBTIENE ACTIVIDADES PARA EL CALENDARIO (Solo las que no han pasado)
     * Este es el método que "vacía" el calendario automáticamente
     */
    public function leerActividadesVigentes($mes = null, $anio = null) {
        $consulta = "SELECT * FROM " . $this->tabla . " 
                    WHERE estado = 'activo' 
                    AND fecha_actividad >= CURDATE()"; // Filtro de auto-vaciado
        
        if ($mes) $consulta .= " AND MONTH(fecha_actividad) = :mes";
        if ($anio) $consulta .= " AND YEAR(fecha_actividad) = :anio";
        
        $consulta .= " ORDER BY fecha_actividad ASC, nombre_actividad ASC";
        
        $stmt = $this->conexion->prepare($consulta);
        if ($mes) $stmt->bindParam(':mes', $mes);
        if ($anio) $stmt->bindParam(':anio', $anio);
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Crea una nueva actividad
     */
    public function crear() {
        $consulta = "INSERT INTO " . $this->tabla . " 
                    (nombre_actividad, descripcion, tipo_actividad, ubicacion, fecha_actividad, duracion_minutos,
                     capacidad_maxima, edad_minima, edad_maxima, materiales_necesarios, 
                     instrucciones, estado)
                    VALUES (:nombre, :descripcion, :tipo, :ubicacion, :fecha, :duracion,
                            :capacidad, :edad_min, :edad_max, :materiales, :instrucciones, :estado)";
        
        $stmt = $this->conexion->prepare($consulta);
        
        // Blindaje PHP 8.2+ para evitar errores con null
        $this->nombre_actividad = htmlspecialchars(strip_tags($this->nombre_actividad ?? ''));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion ?? ''));
        
        $stmt->bindParam(':nombre', $this->nombre_actividad);
        $stmt->bindParam(':descripcion', $this->descripcion);
        $stmt->bindParam(':tipo', $this->tipo_actividad);
        $stmt->bindParam(':ubicacion', $this->ubicacion);
        $stmt->bindParam(':fecha', $this->fecha_actividad);
        $stmt->bindParam(':duracion', $this->duracion_minutos);
        $stmt->bindParam(':capacidad', $this->capacidad_maxima);
        $stmt->bindParam(':edad_min', $this->edad_minima);
        $stmt->bindParam(':edad_max', $this->edad_maxima);
        $stmt->bindParam(':materiales', $this->materiales_necesarios);
        $stmt->bindParam(':instrucciones', $this->instrucciones);
        $stmt->bindParam(':estado', $this->estado);
        
        if ($stmt->execute()) {
            return $this->conexion->lastInsertId();
        }
        return false;
    }
    
    /**
     * Lee una actividad por ID
     */
    public function leerPorId() {
        $consulta = "SELECT * FROM " . $this->tabla . " WHERE id_actividad = :id LIMIT 1";
        $stmt = $this->conexion->prepare($consulta);
        $stmt->bindParam(':id', $this->id_actividad);
        $stmt->execute();
        
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($fila) {
            foreach ($fila as $key => $value) {
                if (property_exists($this, $key)) $this->$key = $value;
            }
            return true;
        }
        return false;
    }
    
    /**
     * Obtiene todas las actividades (Sin filtro de fecha, para administración)
     */
    public function leerTodas($tipo = null, $estado = null) {
        $consulta = "SELECT * FROM " . $this->tabla . " WHERE 1=1";
        
        if ($tipo) $consulta .= " AND tipo_actividad = :tipo";
        if ($estado) $consulta .= " AND estado = :estado";
        
        $consulta .= " ORDER BY fecha_actividad ASC, nombre_actividad ASC";
        
        $stmt = $this->conexion->prepare($consulta);
        if ($tipo) $stmt->bindParam(':tipo', $tipo);
        if ($estado) $stmt->bindParam(':estado', $estado);
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Actualiza una actividad
     */
    public function actualizar() {
        $consulta = "UPDATE " . $this->tabla . "
                    SET nombre_actividad = :nombre,
                        descripcion = :descripcion,
                        tipo_actividad = :tipo,
                        ubicacion = :ubicacion,
                        fecha_actividad = :fecha,
                        duracion_minutos = :duracion,
                        capacidad_maxima = :capacidad,
                        edad_minima = :edad_min,
                        edad_maxima = :edad_max,
                        materiales_necesarios = :materiales,
                        instrucciones = :instrucciones,
                        estado = :estado
                    WHERE id_actividad = :id";
        
        $stmt = $this->conexion->prepare($consulta);
        
        $this->nombre_actividad = htmlspecialchars(strip_tags($this->nombre_actividad ?? ''));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion ?? ''));
        
        $stmt->bindParam(':nombre', $this->nombre_actividad);
        $stmt->bindParam(':descripcion', $this->descripcion);
        $stmt->bindParam(':tipo', $this->tipo_actividad);
        $stmt->bindParam(':ubicacion', $this->ubicacion);
        $stmt->bindParam(':fecha', $this->fecha_actividad);
        $stmt->bindParam(':duracion', $this->duracion_minutos);
        $stmt->bindParam(':capacidad', $this->capacidad_maxima);
        $stmt->bindParam(':edad_min', $this->edad_minima);
        $stmt->bindParam(':edad_max', $this->edad_maxima);
        $stmt->bindParam(':materiales', $this->materiales_necesarios);
        $stmt->bindParam(':instrucciones', $this->instrucciones);
        $stmt->bindParam(':estado', $this->estado);
        $stmt->bindParam(':id', $this->id_actividad);
        
        return $stmt->execute();
    }
    
    /**
     * Elimina una actividad (soft delete)
     */
    public function eliminar() {
        $consulta = "UPDATE " . $this->tabla . " SET estado = 'inactivo' WHERE id_actividad = :id";
        $stmt = $this->conexion->prepare($consulta);
        $stmt->bindParam(':id', $this->id_actividad);
        return $stmt->execute();
    }
}