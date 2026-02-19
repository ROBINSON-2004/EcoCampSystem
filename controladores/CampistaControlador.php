<?php
/**
 * ============================================
 * CONTROLADOR: CAMPISTAS (VERSIÓN MAESTRA 2026)
 * EcoCampSystem - Robinson Moya
 * Sincronizado para Pujilí, Cotopaxi
 * ============================================
 */
require_once __DIR__ . '/../config/constantes.php';
require_once RUTA_UTILIDADES . '/funciones.php';
require_once RUTA_MODELOS . '/Campista.php';
require_once RUTA_CONFIG . '/conexion.php';

class CampistaControlador {
    
    private $db;

    public function __construct() {
        $database = new Conexion();
        $this->db = $database->obtenerConexion();
    }

    /**
     * OBTENER ESTADÍSTICAS (Resuelve error Fatal en lista.php)
     */
    public function obtenerEstadisticas() {
        $campista_modelo = new Campista();
        $por_estado = $campista_modelo->contarPorEstado();
        
        $stats = [
            'total' => 0,
            'aprobados' => 0,
            'pendientes' => 0,
            'rechazados' => 0,
            'retirados' => 0
        ];
        
        if ($por_estado) {
            foreach ($por_estado as $estado) {
                $count = (int)$estado['total'];
                $stats['total'] += $count;
                
                switch ($estado['estado_inscripcion']) {
                    case INSCRIPCION_APROBADO: $stats['aprobados'] = $count; break;
                    case INSCRIPCION_PENDIENTE: $stats['pendientes'] = $count; break;
                    case INSCRIPCION_RECHAZADO: $stats['rechazados'] = $count; break;
                    case INSCRIPCION_RETIRADO: $stats['retirados'] = $count; break;
                }
            }
        }
        return $stats;
    }

    /**
     * LISTAR TODOS LOS CAMPISTAS
     */
    public function listarTodos($estado = null, $anio = null) {
        return (new Campista())->leerTodos($estado, $anio);
    }

    /**
     * BUSCAR CAMPISTAS POR NOMBRE O ID
     */
    public function buscar($termino) {
        if (empty($termino)) return $this->listarTodos();
        return (new Campista())->buscar($termino);
    }

    /**
     * OBTENER POR ID (Perfil Integral)
     */
    public function obtenerPorId($id_campista) {
        $modelo = new Campista();
        $modelo->id_campista = (int)$id_campista;
        if ($modelo->leerPorId()) {
            return $modelo->obtenerInformacionCompleta();
        }
        return false;
    }

    /**
     * CAMBIAR ESTADO (Actualización Quirúrgica)
     * Resuelve el fallo de actualización de Pendiente a Aprobado.
     */
    public function cambiarEstado($id_campista, $nuevo_estado) {
        try {
            $sql = "UPDATE campistas SET estado_inscripcion = :estado WHERE id_campista = :id";
            $stmt = $this->db->prepare($sql);
            $res = $stmt->execute([
                ':estado' => $nuevo_estado,
                ':id'     => (int)$id_campista
            ]);

            if ($res) {
                registrar_log("Cambio de estado exitoso: ID $id_campista a $nuevo_estado", 'INFO');
                return ['exito' => true, 'mensaje' => 'Estado actualizado a ' . strtoupper($nuevo_estado)];
            }
        } catch (Exception $e) {
            registrar_log("Error en cambiarEstado: " . $e->getMessage(), 'ERROR');
            return ['exito' => false, 'mensaje' => $e->getMessage()];
        }
        return ['exito' => false, 'mensaje' => 'No se pudo procesar el cambio.'];
    }

    /**
     * ACTUALIZAR EXPEDIENTE (Básico + Salud + Emergencia)
     * Procesa tres tablas en una sola transacción segura.
     */
    public function actualizar($datos) {
        if (empty($datos['id_campista'])) return ['exito' => false, 'mensaje' => 'ID no válido.'];

        try {
            $this->db->beginTransaction();

            // 1. Datos Básicos
            $campista = new Campista();
            $campista->id_campista = (int)$datos['id_campista'];
            $campista->nombre = limpiar_cadena($datos['nombre'] ?? '');
            $campista->apellido = limpiar_cadena($datos['apellido'] ?? '');
            $campista->fecha_nacimiento = $datos['fecha_nacimiento'] ?? '';
            $campista->genero = $datos['genero'] ?? '';
            $campista->notas_especiales = limpiar_cadena($datos['notas_especiales'] ?? '');
            
            if (!$campista->actualizar()) throw new Exception("Error al actualizar tabla principal.");

            // 2. Información Médica
            if (isset($datos['tipo_sangre'])) {
                $sql_m = "UPDATE informacion_medica SET 
                          tipo_sangre = ?, alergias = ?, condiciones_especiales = ?, medicamentos = ? 
                          WHERE id_campista = ?";
                $this->db->prepare($sql_m)->execute([
                    $datos['tipo_sangre'],
                    limpiar_cadena($datos['alergias'] ?? 'Ninguna'),
                    limpiar_cadena($datos['condiciones_especiales'] ?? ''),
                    limpiar_cadena($datos['medicamentos'] ?? ''),
                    $datos['id_campista']
                ]);
            }

            // 3. Contactos de Emergencia
            if (isset($datos['emergencia_nombre'])) {
                // Limpiar anteriores para sincronizar
                $this->db->prepare("DELETE FROM informacion_emergencia WHERE id_campista = ?")->execute([$datos['id_campista']]);
                
                $sql_e = "INSERT INTO informacion_emergencia (id_campista, nombre_contacto, parentesco, telefono) VALUES (?, ?, ?, ?)";
                $stmt_e = $this->db->prepare($sql_e);
                
                foreach ($datos['emergencia_nombre'] as $i => $nombre) {
                    if (!empty($nombre)) {
                        $stmt_e->execute([
                            $datos['id_campista'], 
                            limpiar_cadena($nombre), 
                            limpiar_cadena($datos['emergencia_parentesco'][$i] ?? 'Familiar'), 
                            limpiar_cadena($datos['emergencia_telefono'][$i] ?? '000')
                        ]);
                    }
                }
            }

            $this->db->commit();
            return ['exito' => true];

        } catch (Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            return ['exito' => false, 'mensaje' => "Error técnico: " . $e->getMessage()];
        }
    }

    /**
     * CREAR CAMPISTA (Registro Inicial)
     */
    public function crear($datos) {
        try {
            $this->db->beginTransaction();
            $campista = new Campista();
            $campista->nombre = limpiar_cadena($datos['nombre'] ?? '');
            $campista->apellido = limpiar_cadena($datos['apellido'] ?? '');
            $campista->fecha_nacimiento = $datos['fecha_nacimiento'] ?? '';
            $campista->edad = calcular_edad($datos['fecha_nacimiento'] ?? 'now');
            $campista->genero = $datos['genero'] ?? '';
            $campista->id_padre = (int)($datos['id_padre'] ?? 0);
            $campista->estado_inscripcion = INSCRIPCION_PENDIENTE;
            $campista->anio_inscripcion = ANIO_CAMPAMENTO_ACTUAL;

            $id_campista = $campista->crear();

            if ($id_campista) {
                $this->db->prepare("INSERT INTO informacion_medica (id_campista, tipo_sangre) VALUES (?, 'S/N')")->execute([$id_campista]);
                $this->db->commit();
                return ['exito' => true, 'id_campista' => $id_campista];
            }
        } catch (Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            return ['exito' => false, 'mensaje' => $e->getMessage()];
        }
        return ['exito' => false, 'mensaje' => 'Error desconocido.'];
    }

    public function eliminar($id) {
        $modelo = new Campista();
        $modelo->id_campista = (int)$id;
        return $modelo->eliminar() ? ['exito' => true] : ['exito' => false];
    }
}