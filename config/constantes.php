<?php
/**
 * ============================================
 * CONSTANTES DEL SISTEMA - EcoCampSystem
 * ============================================
 */

/* ============================================
 * URL BASE DEL PROYECTO
 * ============================================ */
define('URL_BASE', 'http://localhost/EcoCampSystem');

/* ============================================
 * RUTA FÍSICA RAÍZ DEL PROYECTO
 * ============================================ */
// C:\xampp\htdocs\EcoCampSystem
define('RUTA_RAIZ', realpath(__DIR__ . '/..'));

/* ============================================
 * RUTAS DEL SISTEMA (FÍSICAS)
 * ============================================ */
define('RUTA_CONFIG',       RUTA_RAIZ . '/config');
define('RUTA_CONTROLADORES',RUTA_RAIZ . '/controladores');
define('RUTA_MODELOS',      RUTA_RAIZ . '/modelos');
define('RUTA_VISTAS',       RUTA_RAIZ . '/vistas');
define('RUTA_UTILIDADES',   RUTA_RAIZ . '/utilidades');
define('RUTA_PUBLIC',       RUTA_RAIZ . '/public');
define('RUTA_UPLOADS',      RUTA_PUBLIC . '/uploads');

/* ============================================
 * URLS PÚBLICAS
 * ============================================ */
define('URL_PUBLIC',  URL_BASE . '/public');
define('URL_UPLOADS', URL_PUBLIC . '/uploads');

/* ============================================
 * CONFIGURACIÓN GENERAL
 * ============================================ */
define('NOMBRE_SITIO', 'Sistema de Gestión de Campamento');
define('VERSION', '1.0.0');
define('ZONA_HORARIA', 'America/Guayaquil');
date_default_timezone_set(ZONA_HORARIA);

/* ============================================
 * CONFIGURACIÓN DE SESIÓN
 * ============================================ */
define('NOMBRE_SESION', 'ecocamp_session');
define('TIEMPO_SESION', 3600);

/* ============================================
 * TIPOS DE USUARIO
 * ============================================ */
define('TIPO_ADMINISTRADOR', 'administrador');
define('TIPO_PADRE', 'padre');
define('TIPO_TRABAJADOR', 'trabajador');
define('TIPO_CONSEJERO', 'consejero');

/* ============================================
 * ESTADOS GENERALES
 * ============================================ */
define('ESTADO_ACTIVO', 'activo');
define('ESTADO_INACTIVO', 'inactivo');
define('ESTADO_SUSPENDIDO', 'suspendido');

/* ============================================
 * ESTADOS DE FORMULARIO
 * ============================================ */
define('FORMULARIO_PENDIENTE', 'pendiente');
define('FORMULARIO_COMPLETADO', 'completado');
define('FORMULARIO_RECHAZADO', 'rechazado');

/* ============================================
 * CONFIGURACIÓN DE ARCHIVOS
 * ============================================ */
define('TAMANO_MAX_ARCHIVO', 5242880); // 5MB
define('EXTENSIONES_DOCUMENTOS', ['pdf', 'doc', 'docx']);
define('EXTENSIONES_IMAGENES', ['jpg', 'jpeg', 'png']);

/* ============================================
 * CONFIGURACIÓN EMAIL
 * ============================================ */
define('EMAIL_SISTEMA', 'noreply@campamento.com');
define('NOMBRE_EMAIL_SISTEMA', 'EcoCamp System');

/* ============================================
 * MENSAJES DEL SISTEMA
 * ============================================ */
define('MSG_ERROR_PERMISOS', 'No tienes permisos para acceder a esta sección.');
define('MSG_ERROR_SESION', 'Tu sesión ha expirado.');
define('MSG_ERROR_GENERAL', 'Ha ocurrido un error.');
define('MSG_EXITO_GUARDAR', 'Datos guardados correctamente.');
define('MSG_EXITO_ELIMINAR', 'Registro eliminado correctamente.');
define('MSG_EXITO_ACTUALIZAR', 'Datos actualizados correctamente.');

/* ============================================
 * DEBUG
 * ============================================ */
define('DEBUG_MODE', true);

if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}
/* ============================================
 * ESTADOS DE INSCRIPCIÓN
 * ============================================ */
define('INSCRIPCION_PENDIENTE', 'pendiente');
define('INSCRIPCION_APROBADO', 'aprobado');
define('INSCRIPCION_RECHAZADO', 'rechazado');
define('INSCRIPCION_RETIRADO', 'retirado');

/* ============================================
 * ESTADOS DE ASISTENCIA
 * ============================================ */
define('ASISTENCIA_PRESENTE', 'presente');
define('ASISTENCIA_AUSENTE', 'ausente');
define('ASISTENCIA_TARDANZA', 'tardanza');
define('ASISTENCIA_RETIRO_TEMPRANO', 'retiro_temprano');

/* ============================================
 * CONFIGURACIÓN GENERAL DEL CAMPAMENTO
 * ============================================ */
define('ANIO_CAMPAMENTO_ACTUAL', date('Y'));

/* ============================================
 * PAGINACIÓN
 * ============================================ */
define('REGISTROS_POR_PAGINA', 20);

/* ============================================
 * SEGURIDAD
 * ============================================ */
define('INTENTOS_LOGIN_MAX', 5);
define('TIEMPO_BLOQUEO_LOGIN', 900); // 15 minutos
define('LONGITUD_MIN_CONTRASENA', 8);
