<?php
require_once RUTA_CONFIG . '/conexion.php';

/**
 * Clase Actividad
 * Maneja todas las operaciones de base de datos relacionadas con el catálogo de actividades.
 */
class Actividad {
    private $conexion;
    private $tabla = 'actividades';
    
    // Propiedades explícitas: Previene el error "Creation of dynamic property is deprecated"
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
    
    public function __construct() {
        $database = new Conexion();
        $this->conexion = $database->obtenerConexion();
    }
    
    /**
     * Inserta una nueva actividad en el catálogo.
     * @return int|bool Retorna el ID generado o false en caso de error.
     */
    public function crear() {
        $consulta = "INSERT INTO " . $this->tabla . " 
                    (nombre_actividad, descripcion, tipo_actividad, ubicacion, duracion_minutos,
                     capacidad_maxima, edad_minima, edad_maxima, materiales_necesarios, 
                     instrucciones, estado)
                    VALUES (:nombre, :descripcion, :tipo, :ubicacion, :duracion,
                            :capacidad, :edad_min, :edad_max, :materiales, :instrucciones, :estado)";
        
        $stmt = $this->conexion->prepare($consulta);
        
        $stmt->bindParam(':nombre', $this->nombre_actividad);
        $stmt->bindParam(':descripcion', $this->descripcion);
        $stmt->bindParam(':tipo', $this->tipo_actividad);
        $stmt->bindParam(':ubicacion', $this->ubicacion);
        $stmt->bindParam(':duracion', $this->duracion_minutos, PDO::PARAM_INT);
        $stmt->bindParam(':capacidad', $this->capacidad_maxima, PDO::PARAM_INT);
        $stmt->bindParam(':edad_min', $this->edad_minima, PDO::PARAM_INT);
        $stmt->bindParam(':edad_max', $this->edad_maxima, PDO::PARAM_INT);
        $stmt->bindParam(':materiales', $this->materiales_necesarios);
        $stmt->bindParam(':instrucciones', $this->instrucciones);
        $stmt->bindParam(':estado', $this->estado);
        
        if ($stmt->execute()) {
            return $this->conexion->lastInsertId();
        }
        return false;
    }
    
    /**
     * Obtiene la lista de actividades.
     * Por defecto solo devuelve las que NO han sido eliminadas lógicamente.
     */
    public function leerTodas($tipo = null, $estado = 'activo') {
        $consulta = "SELECT * FROM " . $this->tabla . " WHERE estado = :estado";
        
        if ($tipo) {
            $consulta .= " AND tipo_actividad = :tipo";
        }
        
        $consulta .= " ORDER BY nombre_actividad ASC";
        
        $stmt = $this->conexion->prepare($consulta);
        $stmt->bindParam(':estado', $estado);
        if ($tipo) $stmt->bindParam(':tipo', $tipo);
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Carga una actividad específica y rellena las propiedades de la clase.
     */
    public function leerPorId() {
        $consulta = "SELECT * FROM " . $this->tabla . " WHERE id_actividad = :id LIMIT 1";
        $stmt = $this->conexion->prepare($consulta);
        $stmt->bindParam(':id', $this->id_actividad, PDO::PARAM_INT);
        $stmt->execute();
        
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($fila) {
            foreach ($fila as $key => $value) {
                if (property_exists($this, $key)) {
                    $this->$key = $value;
                }
            }
            return true;
        }
        return false;
    }
    
    /**
     * Guarda los cambios realizados en una actividad existente.
     */
    public function actualizar() {
        $consulta = "UPDATE " . $this->tabla . "
                    SET nombre_actividad = :nombre,
                        descripcion = :descripcion,
                        tipo_actividad = :tipo,
                        ubicacion = :ubicacion,
                        duracion_minutos = :duracion,
                        capacidad_maxima = :capacidad,
                        edad_minima = :edad_min,
                        edad_maxima = :edad_max,
                        materiales_necesarios = :materiales,
                        instrucciones = :instrucciones,
                        estado = :estado
                    WHERE id_actividad = :id";
        
        $stmt = $this->conexion->prepare($consulta);
        
        $stmt->bindParam(':nombre', $this->nombre_actividad);
        $stmt->bindParam(':descripcion', $this->descripcion);
        $stmt->bindParam(':tipo', $this->tipo_actividad);
        $stmt->bindParam(':ubicacion', $this->ubicacion);
        $stmt->bindParam(':duracion', $this->duracion_minutos, PDO::PARAM_INT);
        $stmt->bindParam(':capacidad', $this->capacidad_maxima, PDO::PARAM_INT);
        $stmt->bindParam(':edad_min', $this->edad_minima, PDO::PARAM_INT);
        $stmt->bindParam(':edad_max', $this->edad_maxima, PDO::PARAM_INT);
        $stmt->bindParam(':materiales', $this->materiales_necesarios);
        $stmt->bindParam(':instrucciones', $this->instrucciones);
        $stmt->bindParam(':estado', $this->estado);
        $stmt->bindParam(':id', $this->id_actividad, PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    /**
     * Borrado lógico: Cambia el estado a 'inactivo' para que no aparezca en las vistas,
     * pero mantenga la integridad referencial en el historial.
     */
    public function eliminar() {
        $consulta = "UPDATE " . $this->tabla . " SET estado = 'inactivo' WHERE id_actividad = :id";
        $stmt = $this->conexion->prepare($consulta);
        $stmt->bindParam(':id', $this->id_actividad, PDO::PARAM_INT);
        return $stmt->execute();
    }
}