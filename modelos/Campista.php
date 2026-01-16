<?php
require_once __DIR__ . '/../config/conexion.php';

/**
 * Clase Campista
 * Modelo para gestionar campistas
 */
class Campista {
    private $conexion;
    private $tabla = 'campistas';
    
    // Propiedades
    public $id_campista;
    public $nombre;
    public $apellido;
    public $fecha_nacimiento;
    public $edad;
    public $genero;
    public $id_padre;
    public $foto_perfil;
    public $notas_especiales;
    public $estado_inscripcion;
    public $anio_inscripcion;
    public $fecha_inscripcion;
    
    /**
     * Constructor
     */
    public function __construct() {
        $database = new Conexion();
        $this->conexion = $database->obtenerConexion();
    }
    
    /**
     * Crea un nuevo campista
     * @return bool|int ID del campista creado o false
     */
    public function crear() {
        $consulta = "INSERT INTO " . $this->tabla . " 
                    (nombre, apellido, fecha_nacimiento, edad, genero, id_padre, 
                     foto_perfil, notas_especiales, estado_inscripcion, anio_inscripcion)
                    VALUES (:nombre, :apellido, :fecha_nac, :edad, :genero, :id_padre,
                            :foto, :notas, :estado, :anio)";
        
        $stmt = $this->conexion->prepare($consulta);
        
        // Limpiar datos
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->apellido = htmlspecialchars(strip_tags($this->apellido));
        $this->notas_especiales = htmlspecialchars(strip_tags($this->notas_especiales));
        
        // Bind
        $stmt->bindParam(':nombre', $this->nombre);
        $stmt->bindParam(':apellido', $this->apellido);
        $stmt->bindParam(':fecha_nac', $this->fecha_nacimiento);
        $stmt->bindParam(':edad', $this->edad);
        $stmt->bindParam(':genero', $this->genero);
        $stmt->bindParam(':id_padre', $this->id_padre);
        $stmt->bindParam(':foto', $this->foto_perfil);
        $stmt->bindParam(':notas', $this->notas_especiales);
        $stmt->bindParam(':estado', $this->estado_inscripcion);
        $stmt->bindParam(':anio', $this->anio_inscripcion);
        
        if ($stmt->execute()) {
            return $this->conexion->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * Lee un campista por ID
     * @return bool
     */
    public function leerPorId() {
        $consulta = "SELECT * FROM " . $this->tabla . " WHERE id_campista = :id LIMIT 1";
        
        $stmt = $this->conexion->prepare($consulta);
        $stmt->bindParam(':id', $this->id_campista);
        $stmt->execute();
        
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($fila) {
            $this->nombre = $fila['nombre'];
            $this->apellido = $fila['apellido'];
            $this->fecha_nacimiento = $fila['fecha_nacimiento'];
            $this->edad = $fila['edad'];
            $this->genero = $fila['genero'];
            $this->id_padre = $fila['id_padre'];
            $this->foto_perfil = $fila['foto_perfil'];
            $this->notas_especiales = $fila['notas_especiales'];
            $this->estado_inscripcion = $fila['estado_inscripcion'];
            $this->anio_inscripcion = $fila['anio_inscripcion'];
            $this->fecha_inscripcion = $fila['fecha_inscripcion'];
            return true;
        }
        
        return false;
    }
    
    /**
     * Obtiene información completa del campista con datos del padre
     * @return array|bool
     */
    public function obtenerInformacionCompleta() {
        $consulta = "SELECT c.*, 
                     p.id_padre,
                     u.nombre as nombre_padre,
                     u.apellido as apellido_padre,
                     u.correo_electronico as correo_padre,
                     u.telefono as telefono_padre
                     FROM " . $this->tabla . " c
                     INNER JOIN padres p ON c.id_padre = p.id_padre
                     INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
                     WHERE c.id_campista = :id";
        
        $stmt = $this->conexion->prepare($consulta);
        $stmt->bindParam(':id', $this->id_campista);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Actualiza un campista
     * @return bool
     */
    public function actualizar() {
        $consulta = "UPDATE " . $this->tabla . "
                    SET nombre = :nombre,
                        apellido = :apellido,
                        fecha_nacimiento = :fecha_nac,
                        edad = :edad,
                        genero = :genero,
                        foto_perfil = :foto,
                        notas_especiales = :notas,
                        estado_inscripcion = :estado
                    WHERE id_campista = :id";
        
        $stmt = $this->conexion->prepare($consulta);
        
        // Limpiar datos
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->apellido = htmlspecialchars(strip_tags($this->apellido));
        $this->notas_especiales = htmlspecialchars(strip_tags($this->notas_especiales));
        
        // Bind
        $stmt->bindParam(':nombre', $this->nombre);
        $stmt->bindParam(':apellido', $this->apellido);
        $stmt->bindParam(':fecha_nac', $this->fecha_nacimiento);
        $stmt->bindParam(':edad', $this->edad);
        $stmt->bindParam(':genero', $this->genero);
        $stmt->bindParam(':foto', $this->foto_perfil);
        $stmt->bindParam(':notas', $this->notas_especiales);
        $stmt->bindParam(':estado', $this->estado_inscripcion);
        $stmt->bindParam(':id', $this->id_campista);
        
        return $stmt->execute();
    }
    
    /**
     * Obtiene todos los campistas con información de padre
     * @param string $estado_inscripcion Filtrar por estado
     * @param int $anio Filtrar por año
     * @return array
     */
    public function leerTodos($estado_inscripcion = null, $anio = null) {
        $consulta = "SELECT c.*,
                     u.nombre as nombre_padre,
                     u.apellido as apellido_padre,
                     u.correo_electronico as correo_padre
                     FROM " . $this->tabla . " c
                     INNER JOIN padres p ON c.id_padre = p.id_padre
                     INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
                     WHERE 1=1";
        
        if ($estado_inscripcion) {
            $consulta .= " AND c.estado_inscripcion = :estado";
        }
        
        if ($anio) {
            $consulta .= " AND c.anio_inscripcion = :anio";
        }
        
        $consulta .= " ORDER BY c.fecha_inscripcion DESC";
        
        $stmt = $this->conexion->prepare($consulta);
        
        if ($estado_inscripcion) {
            $stmt->bindParam(':estado', $estado_inscripcion);
        }
        
        if ($anio) {
            $stmt->bindParam(':anio', $anio);
        }
        
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtiene campistas de un padre específico
     * @param int $id_padre ID del padre
     * @return array
     */
    public function leerPorPadre($id_padre) {
        $consulta = "SELECT * FROM " . $this->tabla . " 
                    WHERE id_padre = :id_padre
                    ORDER BY fecha_inscripcion DESC";
        
        $stmt = $this->conexion->prepare($consulta);
        $stmt->bindParam(':id_padre', $id_padre);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Elimina un campista (soft delete - cambiar estado)
     * @return bool
     */
    public function eliminar() {
        $consulta = "UPDATE " . $this->tabla . "
                    SET estado_inscripcion = 'retirado'
                    WHERE id_campista = :id";
        
        $stmt = $this->conexion->prepare($consulta);
        $stmt->bindParam(':id', $this->id_campista);
        
        return $stmt->execute();
    }
    
    /**
     * Cuenta campistas por estado
     * @return array
     */
    public function contarPorEstado() {
        $consulta = "SELECT estado_inscripcion, COUNT(*) as total
                    FROM " . $this->tabla . "
                    WHERE anio_inscripcion = YEAR(CURDATE())
                    GROUP BY estado_inscripcion";
        
        $stmt = $this->conexion->prepare($consulta);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Busca campistas por término
     * @param string $termino Término de búsqueda
     * @return array
     */
    public function buscar($termino) {
        $consulta = "SELECT c.*,
                     u.nombre as nombre_padre,
                     u.apellido as apellido_padre
                     FROM " . $this->tabla . " c
                     INNER JOIN padres p ON c.id_padre = p.id_padre
                     INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
                     WHERE CONCAT(c.nombre, ' ', c.apellido) LIKE :termino
                     OR CONCAT(u.nombre, ' ', u.apellido) LIKE :termino
                     ORDER BY c.fecha_inscripcion DESC";
        
        $stmt = $this->conexion->prepare($consulta);
        $busqueda = "%$termino%";
        $stmt->bindParam(':termino', $busqueda);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>