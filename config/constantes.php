<?php
/**
 * Constantes del Sistema
 * Define todas las constantes y configuraciones globales
 */

// ============================================
// CONFIGURACIÓN GENERAL
// ============================================
define('NOMBRE_SITIO', 'Sistema de Gestión de Campamento');
define('VERSION', '1.0.0');
define('ZONA_HORARIA', 'America/Guayaquil');

// Establecer zona horaria
date_default_timezone_set(ZONA_HORARIA);

// ============================================
// RUTAS DEL SISTEMA
// ============================================
define('RUTA_RAIZ', dirname(dirname(__FILE__)));
define('RUTA_CONFIG', RUTA_RAIZ . '/config');
define('RUTA_CONTROLADORES', RUTA_RAIZ . '/controladores');
define('RUTA_MODELOS', RUTA_RAIZ . '/modelos');
define('RUTA_VISTAS', RUTA_RAIZ . '/vistas');
define('RUTA_UTILIDADES', RUTA_RAIZ . '/utilidades');
define('RUTA_PUBLIC', RUTA_RAIZ . '/public');
define('RUTA_UPLOADS', RUTA_PUBLIC . '/uploads');

// URLs Base (AJUSTAR SEGÚN TU CONFIGURACIÓN)
define('URL_BASE', 'http://localhost/EcoCampSystem');
define('URL_PUBLIC', URL_BASE . '/public');
define('URL_UPLOADS', URL_PUBLIC . '/uploads');

// ============================================
// CONFIGURACIÓN DE SESIÓN
// ============================================
define('TIEMPO_SESION', 3600); // 1 hora en segundos
define('NOMBRE_SESION', 'campamento_sesion');

// ============================================
// TIPOS DE USUARIO
// ============================================
define('TIPO_ADMINISTRADOR', 'administrador');
define('TIPO_PADRE', 'padre');
define('TIPO_TRABAJADOR', 'trabajador');
define('TIPO_CONSEJERO', 'consejero');

// ============================================
// ESTADOS
// ============================================
// Estados de usuario
define('ESTADO_ACTIVO', 'activo');
define('ESTADO_INACTIVO', 'inactivo');
define('ESTADO_SUSPENDIDO', 'suspendido');

// Estados de inscripción
define('INSCRIPCION_PENDIENTE', 'pendiente');
define('INSCRIPCION_APROBADO', 'aprobado');
define('INSCRIPCION_RECHAZADO', 'rechazado');
define('INSCRIPCION_RETIRADO', 'retirado');

// Estados de formulario
define('FORMULARIO_PENDIENTE', 'pendiente');
define('FORMULARIO_COMPLETADO', 'completado');
define('FORMULARIO_RECHAZADO', 'rechazado');

// Estados de asistencia
define('ASISTENCIA_PRESENTE', 'presente');
define('ASISTENCIA_AUSENTE', 'ausente');
define('ASISTENCIA_TARDANZA', 'tardanza');
define('ASISTENCIA_RETIRO_TEMPRANO', 'retiro_temprano');

// ============================================
// CONFIGURACIÓN DE ARCHIVOS
// ============================================
// Tamaños máximos (en bytes)
define('TAMANO_MAX_ARCHIVO', 5242880); // 5MB
define('TAMANO_MAX_IMAGEN', 2097152); // 2MB

// Extensiones permitidas
define('EXTENSIONES_DOCUMENTOS', ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png']);
define('EXTENSIONES_IMAGENES', ['jpg', 'jpeg', 'png', 'gif']);

// ============================================
// CONFIGURACIÓN DE EMAIL
// ============================================
define('EMAIL_SISTEMA', 'noreply@campamento.com');
define('NOMBRE_EMAIL_SISTEMA', 'Sistema Campamento');
define('USAR_SMTP', false); // Cambiar a true para usar SMTP

// Si USAR_SMTP = true, configurar:
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PUERTO', 587);
define('SMTP_USUARIO', '');
define('SMTP_CONTRASENA', '');

// ============================================
// MENSAJES DEL SISTEMA
// ============================================
define('MSG_ERROR_PERMISOS', 'No tienes permisos para acceder a esta sección.');
define('MSG_ERROR_SESION', 'Tu sesión ha expirado. Por favor inicia sesión nuevamente.');
define('MSG_ERROR_GENERAL', 'Ha ocurrido un error. Por favor intenta nuevamente.');
define('MSG_EXITO_GUARDAR', 'Los datos se guardaron correctamente.');
define('MSG_EXITO_ELIMINAR', 'El registro se eliminó correctamente.');
define('MSG_EXITO_ACTUALIZAR', 'Los datos se actualizaron correctamente.');

// ============================================
// CONFIGURACIÓN DE PAGINACIÓN
// ============================================
define('REGISTROS_POR_PAGINA', 20);

// ============================================
// CONFIGURACIÓN DE SEGURIDAD
// ============================================
define('INTENTOS_LOGIN_MAX', 5);
define('TIEMPO_BLOQUEO_LOGIN', 900); // 15 minutos en segundos
define('LONGITUD_MIN_CONTRASENA', 8);

// ============================================
// AÑO ACTUAL DEL CAMPAMENTO
// ============================================
define('ANIO_CAMPAMENTO_ACTUAL', date('Y'));

// ============================================
// MODO DEBUG
// ============================================
define('DEBUG_MODE', true); // Cambiar a false en producción

// Configurar errores según el modo
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', RUTA_RAIZ . '/logs/errores.log');
}
?>