<?php
/**
 * ============================================
 * CONTROLADOR: NOTIFICACIONES - EcoCampSystem
 * ============================================
 */
require_once __DIR__ . '/../config/constantes.php';
require_once RUTA_UTILIDADES . '/funciones.php';
require_once RUTA_MODELOS . '/Notificacion.php';

class NotificacionControlador {

    /**
     * HISTORIA 2: Notificar a todos los padres a la vez.
     * Este método es llamado desde vistas/admin/notificaciones/enviar.php.
     */
    public function enviarMasiva($titulo, $mensaje) {
        try {
            $notificacion_modelo = new Notificacion();
            $conexion = new Conexion();
            $db = $conexion->obtenerConexion();

            // 1. Obtener los IDs de todos los usuarios con rol de padre
            // Nota: Se usa 'nambre' tal como aparece en tu diagrama ER
            // Cambia la consulta de selección de padres para usar 'id_usuario' correctamente
            $query = "SELECT id_usuario FROM usuarios WHERE tipo_usuario = 'padre' AND estado = 'activo'";
            // ... resto del código ...
            $stmt = $db->query($query);
            $padres = $stmt->fetchAll(PDO::FETCH_COLUMN);

            if (empty($padres)) {
                return [
                    'exito' => false, 
                    'mensaje' => 'No se encontraron padres activos en la base de datos para recibir la notificación.'
                ];
            }

            // 2. Crear el registro maestro de la notificación
            // IMPORTANTE: Verifica que tu tabla 'notificaciones' acepte 'evento' en el ENUM de tipo_notificacion.
            $id_notif = $notificacion_modelo->crear([
                'titulo' => limpiar_cadena($titulo),
                'mensaje' => limpiar_cadena($mensaje),
                'tipo' => 'evento', 
                'enviada_por' => $_SESSION['usuario_id'], 
                'destinatarios' => 'todos'
            ]);

            if ($id_notif) {
                // 3. Vincular el mensaje con cada padre en la tabla notificaciones_usuarios
                $notificacion_modelo->vincularUsuarios($id_notif, $padres);
                
                // Registrar actividad en el log para auditoría
                registrar_log(" Robinson Moya envió notificación masiva ID: $id_notif", 'INFO');
                
                return [
                    'exito' => true, 
                    'mensaje' => '¡Éxito! La notificación ha sido enviada a todos los padres registrados.'
                ];
            } else {
                return [
                    'exito' => false, 
                    'mensaje' => 'La base de datos rechazó la creación de la notificación. Revisa los valores ENUM.'
                ];
            }

        } catch (PDOException $e) {
            // Reporte detallado del error de SQL para facilitar el debug en 2026
            registrar_log("Error SQL en enviarMasiva: " . $e->getMessage(), 'ERROR');
            return [
                'exito' => false, 
                'mensaje' => 'Error técnico de Base de Datos: ' . $e->getMessage()
            ];
        } catch (Exception $e) {
            registrar_log("Error general en enviarMasiva: " . $e->getMessage(), 'ERROR');
            return [
                'exito' => false, 
                'mensaje' => 'Error inesperado: ' . $e->getMessage()
            ];
        }
    }

    /**
     * HISTORIA 3: Enviar la agenda diaria a un consejero específico.
     */
    public function enviarAgendaConsejero($id_usuario_consejero, $titulo, $mensaje) {
        try {
            $notificacion_modelo = new Notificacion();
            
            // Crear notificación informativa dirigida a un trabajador
            $id_notif = $notificacion_modelo->crear([
                'titulo' => limpiar_cadena($titulo),
                'mensaje' => limpiar_cadena($mensaje),
                'tipo' => 'informativa',
                'enviada_por' => $_SESSION['usuario_id'],
                'destinatarios' => 'especifico'
            ]);

            if ($id_notif) {
                // Vincular solo al consejero seleccionado en la tabla intermedia
                $notificacion_modelo->vincularUsuarios($id_notif, [$id_usuario_consejero]);
                return [
                    'exito' => true, 
                    'mensaje' => 'La agenda del día ha sido enviada al consejero asignado.'
                ];
            }
        } catch (Exception $e) {
            registrar_log("Error al notificar consejero: " . $e->getMessage(), 'ERROR');
            return [
                'exito' => false, 
                'mensaje' => 'Error al procesar el envío al consejero.'
            ];
        }
        
        return ['exito' => false, 'mensaje' => 'No se pudo completar la operación.'];
    }

} // FIN DE LA CLASE NotificacionControlador