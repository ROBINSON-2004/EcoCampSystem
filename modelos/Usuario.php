<?php
require_once __DIR__ . '/../config/conexion.php';

/**
 * Clase Usuario
 * Modelo para gestionar usuarios del sistema
 */
class Usuario {
    private $conexion;
    private $tabla = 'usuarios';
    
    // Propiedades
    public $id_usuario;
    public $correo_electronico;
    public $contrasena;
    public $tipo_usuario;
    public $nombre;
    public $apellido;
    public $telefono;
    public $fecha_registro;
    public $ultimo_acceso;
    public $estado;
    
    /**
     * Constructor
     */
    public function __construct() {
        $database = new Conexion();
        $this->conexion = $database->obtenerConexion();
    }
    
    /**
     * Crea un nuevo usuario
     * @return bool|int ID del usuario creado o false
     */
    public function crear() {
        $consulta = "INSERT INTO " . $this->tabla . " 
                    (correo_electronico, contrasena, tipo_usuario, nombre, apellido, telefono, estado)
                    VALUES (:correo, :contrasena, :tipo, :nombre, :apellido, :telefono, :estado)";
        
        $stmt = $this->conexion->prepare($consulta);
        
        // Limpiar datos
        $this->correo_electronico = htmlspecialchars(strip_tags($this->correo_electronico));
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->apellido = htmlspecialchars(strip_tags($this->apellido));
        $this->tipo_usuario = htmlspecialchars(strip_tags($this->tipo_usuario));
        $this->telefono = htmlspecialchars(strip_tags($this->telefono));
        
        // Encriptar contraseña
        $contrasena_hash = password_hash($this->contrasena, PASSWORD_BCRYPT);
        
        // Bind
        $stmt->bindParam(':correo', $this->correo_electronico);
        $stmt->bindParam(':contrasena', $contrasena_hash);
        $stmt->bindParam(':tipo', $this->tipo_usuario);
        $stmt->bindParam(':nombre', $this->nombre);
        $stmt->bindParam(':apellido', $this->apellido);
        $stmt->bindParam(':telefono', $this->telefono);
        $stmt->bindParam(':estado', $this->estado);
        
        if ($stmt->execute()) {
            return $this->conexion->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * Lee un usuario por ID
     * @return bool
     */
    public function leerPorId() {
        $consulta = "SELECT * FROM " . $this->tabla . " WHERE id_usuario = :id LIMIT 1";
        
        $stmt = $this->conexion->prepare($consulta);
        $stmt->bindParam(':id', $this->id_usuario);
        $stmt->execute();
        
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($fila) {
            $this->correo_electronico = $fila['correo_electronico'];
            $this->tipo_usuario = $fila['tipo_usuario'];
            $this->nombre = $fila['nombre'];
            $this->apellido = $fila['apellido'];
            $this->telefono = $fila['telefono'];
            $this->fecha_registro = $fila['fecha_registro'];
            $this->ultimo_acceso = $fila['ultimo_acceso'];
            $this->estado = $fila['estado'];
            return true;
        }
        
        return false;
    }
    
    /**
     * Lee un usuario por correo electrónico
     * @return bool
     */
    public function leerPorCorreo() {
        $consulta = "SELECT * FROM " . $this->tabla . " 
                    WHERE correo_electronico = :correo LIMIT 1";
        
        $stmt = $this->conexion->prepare($consulta);
        $stmt->bindParam(':correo', $this->correo_electronico);
        $stmt->execute();
        
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($fila) {
            $this->id_usuario = $fila['id_usuario'];
            $this->contrasena = $fila['contrasena'];
            $this->tipo_usuario = $fila['tipo_usuario'];
            $this->nombre = $fila['nombre'];
            $this->apellido = $fila['apellido'];
            $this->telefono = $fila['telefono'];
            $this->fecha_registro = $fila['fecha_registro'];
            $this->ultimo_acceso = $fila['ultimo_acceso'];
            $this->estado = $fila['estado'];
            return true;
        }
        
        return false;
    }
    
    /**
     * Actualiza un usuario
     * @return bool
     */
    public function actualizar() {
        $consulta = "UPDATE " . $this->tabla . "
                    SET nombre = :nombre,
                        apellido = :apellido,
                        telefono = :telefono,
                        estado = :estado
                    WHERE id_usuario = :id";
        
        $stmt = $this->conexion->prepare($consulta);
        
        // Limpiar datos
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->apellido = htmlspecialchars(strip_tags($this->apellido));
        $this->telefono = htmlspecialchars(strip_tags($this->telefono));
        
        // Bind
        $stmt->bindParam(':nombre', $this->nombre);
        $stmt->bindParam(':apellido', $this->apellido);
        $stmt->bindParam(':telefono', $this->telefono);
        $stmt->bindParam(':estado', $this->estado);
        $stmt->bindParam(':id', $this->id_usuario);
        
        return $stmt->execute();
    }
    
    /**
     * Actualiza la contraseña del usuario
     * @return bool
     */
    public function actualizarContrasena() {
        $consulta = "UPDATE " . $this->tabla . "
                    SET contrasena = :contrasena
                    WHERE id_usuario = :id";
        
        $stmt = $this->conexion->prepare($consulta);
        
        $contrasena_hash = password_hash($this->contrasena, PASSWORD_BCRYPT);
        
        $stmt->bindParam(':contrasena', $contrasena_hash);
        $stmt->bindParam(':id', $this->id_usuario);
        
        return $stmt->execute();
    }
    
    /**
     * Actualiza el último acceso del usuario
     * @return bool
     */
    public function actualizarUltimoAcceso() {
        $consulta = "UPDATE " . $this->tabla . "
                    SET ultimo_acceso = NOW()
                    WHERE id_usuario = :id";
        
        $stmt = $this->conexion->prepare($consulta);
        $stmt->bindParam(':id', $this->id_usuario);
        
        return $stmt->execute();
    }
    
    /**
     * Verifica si un correo ya existe
     * @param string $correo Correo a verificar
     * @param int $excluir_id ID a excluir de la búsqueda (para edición)
     * @return bool
     */
    public function correoExiste($correo, $excluir_id = null) {
        $consulta = "SELECT id_usuario FROM " . $this->tabla . " 
                    WHERE correo_electronico = :correo";
        
        if ($excluir_id) {
            $consulta .= " AND id_usuario != :excluir_id";
        }
        
        $stmt = $this->conexion->prepare($consulta);
        $stmt->bindParam(':correo', $correo);
        
        if ($excluir_id) {
            $stmt->bindParam(':excluir_id', $excluir_id);
        }
        
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Autentica un usuario
     * @param string $correo Correo del usuario
     * @param string $contrasena Contraseña del usuario
     * @return array|bool Datos del usuario o false
     */
    public function autenticar($correo, $contrasena) {
        $this->correo_electronico = $correo;
        
        if ($this->leerPorCorreo()) {
            // Verificar estado del usuario
            if ($this->estado !== ESTADO_ACTIVO) {
                return false;
            }
            
            // Verificar contraseña
            if (password_verify($contrasena, $this->contrasena)) {
                // Actualizar último acceso
                $this->actualizarUltimoAcceso();
                
                // Retornar datos del usuario
                return [
                    'id_usuario' => $this->id_usuario,
                    'correo_electronico' => $this->correo_electronico,
                    'tipo_usuario' => $this->tipo_usuario,
                    'nombre' => $this->nombre,
                    'apellido' => $this->apellido,
                    'telefono' => $this->telefono
                ];
            }
        }
        
        return false;
    }
    
    /**
     * Obtiene todos los usuarios con filtros opcionales
     * @param string $tipo Filtrar por tipo de usuario
     * @param string $estado Filtrar por estado
     * @return array
     */
    public function leerTodos($tipo = null, $estado = null) {
        $consulta = "SELECT * FROM " . $this->tabla . " WHERE 1=1";
        
        if ($tipo) {
            $consulta .= " AND tipo_usuario = :tipo";
        }
        
        if ($estado) {
            $consulta .= " AND estado = :estado";
        }
        
        $consulta .= " ORDER BY fecha_registro DESC";
        
        $stmt = $this->conexion->prepare($consulta);
        
        if ($tipo) {
            $stmt->bindParam(':tipo', $tipo);
        }
        
        if ($estado) {
            $stmt->bindParam(':estado', $estado);
        }
        
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Elimina un usuario (soft delete)
     * @return bool
     */
    public function eliminar() {
        $consulta = "UPDATE " . $this->tabla . "
                    SET estado = 'inactivo'
                    WHERE id_usuario = :id";
        
        $stmt = $this->conexion->prepare($consulta);
        $stmt->bindParam(':id', $this->id_usuario);
        
        return $stmt->execute();
    }
}
?>