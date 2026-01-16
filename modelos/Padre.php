<?php
require_once __DIR__ . '/../config/conexion.php';

/**
 * Clase Padre
 * Modelo para gestionar información adicional de padres/tutores
 */
class Padre {
    private $conexion;
    private $tabla = 'padres';
    
    // Propiedades
    public $id_padre;
    public $id_usuario;
    public $direccion;
    public $ciudad;
    public $codigo_postal;
    public $ocupacion;
    
    /**
     * Constructor
     */
    public function __construct() {
        $database = new Conexion();
        $this->conexion = $database->obtenerConexion();
    }
    
    /**
     * Crea un nuevo registro de padre
     * @return bool|int ID del padre creado o false
     */
    public function crear() {
        $consulta = "INSERT INTO " . $this->tabla . " 
                    (id_usuario, direccion, ciudad, codigo_postal, ocupacion)
                    VALUES (:id_usuario, :direccion, :ciudad, :codigo_postal, :ocupacion)";
        
        $stmt = $this->conexion->prepare($consulta);
        
        // Limpiar datos
        $this->direccion = htmlspecialchars(strip_tags($this->direccion));
        $this->ciudad = htmlspecialchars(strip_tags($this->ciudad));
        $this->codigo_postal = htmlspecialchars(strip_tags($this->codigo_postal));
        $this->ocupacion = htmlspecialchars(strip_tags($this->ocupacion));
        
        // Bind
        $stmt->bindParam(':id_usuario', $this->id_usuario);
        $stmt->bindParam(':direccion', $this->direccion);
        $stmt->bindParam(':ciudad', $this->ciudad);
        $stmt->bindParam(':codigo_postal', $this->codigo_postal);
        $stmt->bindParam(':ocupacion', $this->ocupacion);
        
        if ($stmt->execute()) {
            return $this->conexion->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * Lee un padre por ID de usuario
     * @return bool
     */
    public function leerPorIdUsuario() {
        $consulta = "SELECT * FROM " . $this->tabla . " 
                    WHERE id_usuario = :id_usuario LIMIT 1";
        
        $stmt = $this->conexion->prepare($consulta);
        $stmt->bindParam(':id_usuario', $this->id_usuario);
        $stmt->execute();
        
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($fila) {
            $this->id_padre = $fila['id_padre'];
            $this->direccion = $fila['direccion'];
            $this->ciudad = $fila['ciudad'];
            $this->codigo_postal = $fila['codigo_postal'];
            $this->ocupacion = $fila['ocupacion'];
            return true;
        }
        
        return false;
    }
    
    /**
     * Lee un padre por ID
     * @return bool
     */
    public function leerPorId() {
        $consulta = "SELECT * FROM " . $this->tabla . " 
                    WHERE id_padre = :id LIMIT 1";
        
        $stmt = $this->conexion->prepare($consulta);
        $stmt->bindParam(':id', $this->id_padre);
        $stmt->execute();
        
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($fila) {
            $this->id_usuario = $fila['id_usuario'];
            $this->direccion = $fila['direccion'];
            $this->ciudad = $fila['ciudad'];
            $this->codigo_postal = $fila['codigo_postal'];
            $this->ocupacion = $fila['ocupacion'];
            return true;
        }
        
        return false;
    }
    
    /**
     * Actualiza información del padre
     * @return bool
     */
    public function actualizar() {
        $consulta = "UPDATE " . $this->tabla . "
                    SET direccion = :direccion,
                        ciudad = :ciudad,
                        codigo_postal = :codigo_postal,
                        ocupacion = :ocupacion
                    WHERE id_padre = :id";
        
        $stmt = $this->conexion->prepare($consulta);
        
        // Limpiar datos
        $this->direccion = htmlspecialchars(strip_tags($this->direccion));
        $this->ciudad = htmlspecialchars(strip_tags($this->ciudad));
        $this->codigo_postal = htmlspecialchars(strip_tags($this->codigo_postal));
        $this->ocupacion = htmlspecialchars(strip_tags($this->ocupacion));
        
        // Bind
        $stmt->bindParam(':direccion', $this->direccion);
        $stmt->bindParam(':ciudad', $this->ciudad);
        $stmt->bindParam(':codigo_postal', $this->codigo_postal);
        $stmt->bindParam(':ocupacion', $this->ocupacion);
        $stmt->bindParam(':id', $this->id_padre);
        
        return $stmt->execute();
    }
    
    /**
     * Obtiene información completa del padre con datos de usuario
     * @return array|bool
     */
    public function obtenerInformacionCompleta() {
        $consulta = "SELECT p.*, u.nombre, u.apellido, u.correo_electronico, 
                    u.telefono, u.estado, u.fecha_registro, u.ultimo_acceso
                    FROM " . $this->tabla . " p
                    INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
                    WHERE p.id_padre = :id";
        
        $stmt = $this->conexion->prepare($consulta);
        $stmt->bindParam(':id', $this->id_padre);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtiene todos los padres con su información de usuario
     * @return array
     */
    public function leerTodos() {
        $consulta = "SELECT p.*, u.nombre, u.apellido, u.correo_electronico, 
                    u.telefono, u.estado, u.fecha_registro, u.ultimo_acceso
                    FROM " . $this->tabla . " p
                    INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
                    ORDER BY u.fecha_registro DESC";
        
        $stmt = $this->conexion->prepare($consulta);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>