<?php
/**
 * ============================================
 * MODELO: FORMULARIO CAMPISTA - EcoCampSystem
 * ============================================
 */
require_once RUTA_CONFIG . '/conexion.php';

class FormularioCampista {
    private $conexion;
    private $tabla = 'formularios_campistas';

    public function __construct() {
        $database = new Conexion();
        $this->conexion = $database->obtenerConexion();
    }

    /**
     * Registra la firma o la actualiza si ya existe (Evita el error HY093)
     */
    public function registrarFirma($id_form, $id_campista, $id_usuario, $archivo) {
        // Usamos VALUES(columna) en el UPDATE para no repetir parámetros vinculados
        $sql = "INSERT INTO " . $this->tabla . " 
                (id_formulario, id_campista, fecha_firma, firmado_por, archivo_firmado, estado)
                VALUES (:id_f, :id_c, CURRENT_TIMESTAMP, :firmado, :archivo, 'completado')
                ON DUPLICATE KEY UPDATE 
                fecha_firma = CURRENT_TIMESTAMP, 
                firmado_por = VALUES(firmado_por), 
                archivo_firmado = VALUES(archivo_firmado), 
                estado = 'completado'";
        
        $stmt = $this->conexion->prepare($sql);
        
        // Vinculamos cada marcador exactamente una vez
        $stmt->bindParam(':id_f', $id_form);
        $stmt->bindParam(':id_c', $id_campista);
        $stmt->bindParam(':firmado', $id_usuario);
        $stmt->bindParam(':archivo', $archivo);
        
        return $stmt->execute();
    }

    /**
     * Seguimiento para el Administrador
     */
    public function obtenerSeguimientoGeneral() {
        // Traemos datos cruzados para ver quién firmó y de qué campista es
        $sql = "SELECT fc.*, f.titulo, f.tipo_formulario, c.nombre, c.apellido, u.nombre as nombre_padre
                FROM " . $this->tabla . " fc
                JOIN formularios f ON fc.id_formulario = f.id_formulario
                JOIN campistas c ON fc.id_campista = c.id_campista
                LEFT JOIN usuarios u ON fc.firmado_por = u.id_usuario
                ORDER BY fc.fecha_firma DESC";
                
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene formularios firmados por un padre específico
     */
    public function obtenerPorPadre($id_usuario_padre) {
        // Ajustado para usar la estructura de tu base de datos
        $sql = "SELECT fc.*, f.titulo, c.nombre as campista_nombre
                FROM campistas c
                JOIN padres p ON c.id_padre = p.id_padre
                JOIN " . $this->tabla . " fc ON c.id_campista = fc.id_campista
                JOIN formularios f ON fc.id_formulario = f.id_formulario
                WHERE p.id_usuario = :id_u";
                
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':id_u', $id_usuario_padre);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>