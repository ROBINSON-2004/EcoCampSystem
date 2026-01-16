<?php
/**
 * Clase Conexion
 * Maneja la conexión a la base de datos usando PDO
 */
class Conexion {
    private $servidor = "localhost";
    private $puerto = "3307";  // Puerto personalizado
    private $usuario = "root";
    private $contrasena = "root";
    private $base_datos = "campamento_db";
    private $conexion;
    private $charset = "utf8mb4";
    
    /**
     * Constructor - Establece la conexión automáticamente
     */
    public function __construct() {
        $this->conectar();
    }
    
    /**
     * Establece la conexión con la base de datos
     * @return PDO Objeto de conexión PDO
     */
    public function conectar() {
        $this->conexion = null;
        
        try {
            $dsn = "mysql:host=" . $this->servidor . 
                   ";port=" . $this->puerto .
                   ";dbname=" . $this->base_datos . 
                   ";charset=" . $this->charset;
            
            $opciones = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ];
            
            $this->conexion = new PDO($dsn, $this->usuario, $this->contrasena, $opciones);
            
        } catch(PDOException $excepcion) {
            // En producción, registrar en log en lugar de mostrar
            die("Error de conexión: " . $excepcion->getMessage());
        }
        
        return $this->conexion;
    }
    
    /**
     * Obtiene la conexión actual
     * @return PDO
     */
    public function obtenerConexion() {
        return $this->conexion;
    }
    
    /**
     * Cierra la conexión
     */
    public function cerrarConexion() {
        $this->conexion = null;
    }
}
?>