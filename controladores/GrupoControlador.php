<?php
require_once __DIR__ . '/../config/constantes.php';
require_once RUTA_UTILIDADES . '/funciones.php';
require_once RUTA_MODELOS . '/Grupo.php';

/**
 * ============================================
 * CONTROLADOR: GRUPOS (Versión Blindada 2026)
 * EcoCampSystem - Robinson Moya
 * ============================================
 */

// 1. Evitamos el Fatal Error: "Cannot declare class GrupoControlador"
if (!class_exists('GrupoControlador')) {

    class GrupoControlador {
        
        private $db;

        /**
         * El constructor centraliza la conexión para todos los métodos
         */
        public function __construct() {
            $conexion = new Conexion();
            $this->db = $conexion->obtenerConexion();
        }

        public function listarTodos($estado = null, $anio = null) {
            $grupo_modelo = new Grupo();
            return $grupo_modelo->leerTodos($estado, $anio);
        }
        
        public function obtenerPorId($id_grupo) {
            $grupo_modelo = new Grupo();
            $grupo_modelo->id_grupo = $id_grupo;
            
            if ($grupo_modelo->leerPorId()) {
                // CORRECCIÓN: Usamos la conexión ya existente ($this->db)
                $query = "SELECT g.*,
                          u.nombre as nombre_consejero,
                          u.apellido as apellido_consejero,
                          u.correo_electronico as correo_consejero,
                          (SELECT COUNT(*) FROM campistas_grupos cg 
                           WHERE cg.id_grupo = g.id_grupo AND cg.estado = 'activo') as total_campistas
                          FROM grupos g
                          LEFT JOIN usuarios u ON g.id_consejero = u.id_usuario
                          WHERE g.id_grupo = :id";
                
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':id', $id_grupo);
                $stmt->execute();
                
                return $stmt->fetch(PDO::FETCH_ASSOC);
            }
            return false;
        }
        
        public function crear($datos) {
            if (empty($datos['nombre_grupo'])) {
                return ['exito' => false, 'mensaje' => 'El nombre del grupo es obligatorio.'];
            }
            
            if (!empty($datos['edad_minima']) && !empty($datos['edad_maxima'])) {
                if ($datos['edad_minima'] > $datos['edad_maxima']) {
                    return ['exito' => false, 'mensaje' => 'La edad mínima no puede ser mayor.'];
                }
            }
            
            try {
                $grupo_modelo = new Grupo();
                $grupo_modelo->nombre_grupo = limpiar_cadena($datos['nombre_grupo']);
                $grupo_modelo->descripcion = !empty($datos['descripcion']) ? limpiar_cadena($datos['descripcion']) : null;
                $grupo_modelo->edad_minima = !empty($datos['edad_minima']) ? (int)$datos['edad_minima'] : null;
                $grupo_modelo->edad_maxima = !empty($datos['edad_maxima']) ? (int)$datos['edad_maxima'] : null;
                $grupo_modelo->capacidad_maxima = !empty($datos['capacidad_maxima']) ? (int)$datos['capacidad_maxima'] : 20;
                $grupo_modelo->id_consejero = !empty($datos['id_consejero']) ? (int)$datos['id_consejero'] : null;
                $grupo_modelo->anio_campamento = $datos['anio_campamento'] ?? ANIO_CAMPAMENTO_ACTUAL;
                $grupo_modelo->estado = $datos['estado'] ?? 'activo';
                
                $id_grupo = $grupo_modelo->crear();
                
                if ($id_grupo) {
                    registrar_log("Grupo creado: ID $id_grupo", 'INFO');
                    return ['exito' => true, 'mensaje' => 'Grupo creado correctamente.', 'id_grupo' => $id_grupo];
                }
            } catch (Exception $e) {
                registrar_log("Error al crear grupo: " . $e->getMessage(), 'ERROR');
            }
            return ['exito' => false, 'mensaje' => MSG_ERROR_GENERAL];
        }
        
        public function actualizar($datos) {
            if (empty($datos['id_grupo'])) return ['exito' => false, 'mensaje' => 'ID no válido.'];
            
            try {
                $grupo_modelo = new Grupo();
                $grupo_modelo->id_grupo = $datos['id_grupo'];
                $grupo_modelo->nombre_grupo = limpiar_cadena($datos['nombre_grupo']);
                $grupo_modelo->descripcion = !empty($datos['descripcion']) ? limpiar_cadena($datos['descripcion']) : null;
                $grupo_modelo->edad_minima = !empty($datos['edad_minima']) ? (int)$datos['edad_minima'] : null;
                $grupo_modelo->edad_maxima = !empty($datos['edad_maxima']) ? (int)$datos['edad_maxima'] : null;
                $grupo_modelo->capacidad_maxima = !empty($datos['capacidad_maxima']) ? (int)$datos['capacidad_maxima'] : 20;
                $grupo_modelo->id_consejero = !empty($datos['id_consejero']) ? (int)$datos['id_consejero'] : null;
                $grupo_modelo->estado = $datos['estado'] ?? 'activo';
                
                if ($grupo_modelo->actualizar()) {
                    registrar_log("Grupo actualizado: ID {$datos['id_grupo']}", 'INFO');
                    return ['exito' => true, 'mensaje' => MSG_EXITO_ACTUALIZAR];
                }
            } catch (Exception $e) {
                registrar_log("Error al actualizar grupo: " . $e->getMessage(), 'ERROR');
            }
            return ['exito' => false, 'mensaje' => MSG_ERROR_GENERAL];
        }
        
        public function eliminar($id_grupo) {
            try {
                $grupo_modelo = new Grupo();
                $grupo_modelo->id_grupo = $id_grupo;
                if ($grupo_modelo->eliminar()) {
                    registrar_log("Grupo eliminado: ID $id_grupo", 'INFO');
                    return ['exito' => true, 'mensaje' => 'Grupo desactivado correctamente.'];
                }
            } catch (Exception $e) {
                registrar_log("Error al eliminar grupo: " . $e->getMessage(), 'ERROR');
            }
            return ['exito' => false, 'mensaje' => MSG_ERROR_GENERAL];
        }
        
        public function obtenerEstadisticas() {
            $grupo_modelo = new Grupo();
            $por_estado = $grupo_modelo->contarPorEstado(ANIO_CAMPAMENTO_ACTUAL);
            $stats = ['total' => 0, 'activos' => 0, 'inactivos' => 0, 'completos' => 0];
            
            foreach ($por_estado as $estado) {
                $stats['total'] += $estado['total'];
                if (isset($stats[$estado['estado'] . 's'])) {
                    $stats[$estado['estado'] . 's'] = $estado['total'];
                }
            }
            return $stats;
        }
        
        public function obtenerCampistas($id_grupo) {
            try {
                $query = "SELECT c.*, cg.fecha_asignacion, cg.estado as estado_grupo
                          FROM campistas c
                          INNER JOIN campistas_grupos cg ON c.id_campista = cg.id_campista
                          WHERE cg.id_grupo = :id_grupo AND cg.estado = 'activo'
                          ORDER BY c.nombre, c.apellido";
                
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':id_grupo', $id_grupo);
                $stmt->execute();
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                registrar_log("Error al obtener campistas: " . $e->getMessage(), 'ERROR');
                return [];
            }
        }
        
        public function asignarCampista($id_campista, $id_grupo) {
            try {
                // Verificar duplicado
                $query_check = "SELECT id_campista_grupo FROM campistas_grupos 
                               WHERE id_campista = :campista AND id_grupo = :grupo AND estado = 'activo'";
                $stmt_check = $this->db->prepare($query_check);
                $stmt_check->execute([':campista' => $id_campista, ':grupo' => $id_grupo]);
                
                if ($stmt_check->rowCount() > 0) {
                    return ['exito' => false, 'mensaje' => 'Ya está asignado a este grupo.'];
                }
                
                $query_insert = "INSERT INTO campistas_grupos (id_campista, id_grupo, estado) 
                                VALUES (:campista, :grupo, 'activo')";
                $stmt_insert = $this->db->prepare($query_insert);
                
                if ($stmt_insert->execute([':campista' => $id_campista, ':grupo' => $id_grupo])) {
                    registrar_log("Campista $id_campista -> Grupo $id_grupo", 'INFO');
                    return ['exito' => true, 'mensaje' => 'Asignación correcta.'];
                }
            } catch (Exception $e) {
                registrar_log("Error al asignar: " . $e->getMessage(), 'ERROR');
            }
            return ['exito' => false, 'mensaje' => MSG_ERROR_GENERAL];
        }
        
        public function removerCampista($id_campista, $id_grupo) {
            try {
                $query = "UPDATE campistas_grupos SET estado = 'retirado' 
                          WHERE id_campista = :campista AND id_grupo = :grupo";
                $stmt = $this->db->prepare($query);
                if ($stmt->execute([':campista' => $id_campista, ':grupo' => $id_grupo])) {
                    return ['exito' => true, 'mensaje' => 'Campista removido.'];
                }
            } catch (Exception $e) {
                registrar_log("Error al remover: " . $e->getMessage(), 'ERROR');
            }
            return ['exito' => false, 'mensaje' => MSG_ERROR_GENERAL];
        }
    }
} // Fin del class_exists
?>