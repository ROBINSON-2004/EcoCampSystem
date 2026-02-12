<?php
/**
 * ============================================
 * MODELO: NOTIFICACIÓN - EcoCampSystem
 * ============================================
 */
require_once RUTA_CONFIG . '/conexion.php';

class Notificacion {
    private $conexion;
    private $tabla = 'notificaciones';
    private $tabla_vinculo = 'notificaciones_usuarios';

    public function __construct() {
        $database = new Conexion();
        $this->conexion = $database->obtenerConexion();
    }

    /**
     * Historia de Usuario: Crear la notificación base en la DB
     */
    public function crear($datos) {
        $sql = "INSERT INTO " . $this->tabla . " 
                (titulo, mensaje, tipo_notificacion, enviada_por, destinatarios, fecha_envio, estado)
                VALUES (:titulo, :mensaje, :tipo, :emisor, :dest, NOW(), 'enviada')";
        
        $stmt = $this->conexion->prepare($sql);
        
        // Limpiamos datos para evitar inyecciones
        $titulo = htmlspecialchars(strip_tags($datos['titulo']));
        $mensaje = htmlspecialchars(strip_tags($datos['mensaje']));
        
        $stmt->bindParam(':titulo', $titulo);
        $stmt->bindParam(':mensaje', $mensaje);
        $stmt->bindParam(':tipo', $datos['tipo']);
        $stmt->bindParam(':emisor', $datos['enviada_por']);
        $stmt->bindParam(':dest', $datos['destinatarios']);
        
        if ($stmt->execute()) {
            return $this->conexion->lastInsertId();
        }
        return false;
    }

    /**
     * Vincula la notificación con los usuarios receptores
     */
    public function vincularUsuarios($id_notificacion, $usuarios_ids) {
        $sql = "INSERT INTO " . $this->tabla_vinculo . " (id_notificacion, id_usuario, leida) 
                VALUES (:id_n, :id_u, 0)";
        
        $stmt = $this->conexion->prepare($sql);
        
        foreach ($usuarios_ids as $id_u) {
            $stmt->execute([
                ':id_n' => $id_notificacion,
                ':id_u' => $id_u
            ]);
        }
        return true;
    }

    /**
     * Obtiene notificaciones para el panel del Consejero o Padre
     */
// ... dentro de la clase Notificacion ...

    public function obtenerPorUsuario($id_usuario, $solo_no_leidas = false) {
        // CORRECCIÓN: Cambiamos u.nambre por u.nombre
        $sql = "SELECT n.*, nu.id_notificacion_usuario, nu.leida, u.nombre as remitente_nombre
                FROM " . $this->tabla . " n
                JOIN " . $this->tabla_vinculo . " nu ON n.id_notificacion = nu.id_notificacion
                JOIN usuarios u ON n.enviada_por = u.id_usuario
                WHERE nu.id_usuario = :id_u";
        
        if ($solo_no_leidas) {
            $sql .= " AND nu.leida = 0";
        }
        
        $sql .= " ORDER BY n.fecha_envio DESC";
        
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([':id_u' => $id_usuario]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Actualiza el estado de lectura
     */
    public function marcarComoLeida($id_vinculo) {
        $sql = "UPDATE " . $this->tabla_vinculo . " 
                SET leida = 1, fecha_lectura = NOW() 
                WHERE id_notificacion_usuario = :id_v";
        
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute([':id_v' => $id_vinculo]);
    }
}
?>