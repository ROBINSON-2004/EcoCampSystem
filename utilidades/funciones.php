<?php
/**
 * Funciones Auxiliares del Sistema
 * Funciones reutilizables en toda la aplicación
 */

/**
 * Sanitiza una cadena de texto
 * @param string $cadena Cadena a sanitizar
 * @return string Cadena sanitizada
 */
function limpiar_cadena($cadena) {
    $cadena = trim($cadena);
    $cadena = stripslashes($cadena);
    $cadena = htmlspecialchars($cadena, ENT_QUOTES, 'UTF-8');
    return $cadena;
}

/**
 * Valida un correo electrónico
 * @param string $correo Correo a validar
 * @return bool
 */
function validar_correo($correo) {
    return filter_var($correo, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Valida un teléfono (formato flexible)
 * @param string $telefono Teléfono a validar
 * @return bool
 */
function validar_telefono($telefono) {
    $patron = '/^[0-9]{7,15}$/';
    $telefono_limpio = preg_replace('/[^0-9]/', '', $telefono);
    return preg_match($patron, $telefono_limpio);
}

/**
 * Encripta una contraseña
 * @param string $contrasena Contraseña en texto plano
 * @return string Hash de la contraseña
 */
function encriptar_contrasena($contrasena) {
    return password_hash($contrasena, PASSWORD_BCRYPT, ['cost' => 12]);
}

/**
 * Verifica una contraseña contra su hash
 * @param string $contrasena Contraseña en texto plano
 * @param string $hash Hash almacenado
 * @return bool
 */
function verificar_contrasena($contrasena, $hash) {
    return password_verify($contrasena, $hash);
}

/**
 * Valida la fortaleza de una contraseña
 * @param string $contrasena Contraseña a validar
 * @return array Array con 'valida' (bool) y 'errores' (array)
 */
function validar_fortaleza_contrasena($contrasena) {
    $errores = [];
    
    if (strlen($contrasena) < LONGITUD_MIN_CONTRASENA) {
        $errores[] = "La contraseña debe tener al menos " . LONGITUD_MIN_CONTRASENA . " caracteres.";
    }
    
    if (!preg_match('/[A-Z]/', $contrasena)) {
        $errores[] = "La contraseña debe contener al menos una letra mayúscula.";
    }
    
    if (!preg_match('/[a-z]/', $contrasena)) {
        $errores[] = "La contraseña debe contener al menos una letra minúscula.";
    }
    
    if (!preg_match('/[0-9]/', $contrasena)) {
        $errores[] = "La contraseña debe contener al menos un número.";
    }
    
    return [
        'valida' => empty($errores),
        'errores' => $errores
    ];
}

/**
 * Genera un token aleatorio seguro
 * @param int $longitud Longitud del token
 * @return string Token generado
 */
function generar_token($longitud = 32) {
    return bin2hex(random_bytes($longitud));
}

/**
 * Formatea una fecha al formato español
 * @param string $fecha Fecha en formato Y-m-d
 * @param bool $incluir_hora Si incluir la hora
 * @return string Fecha formateada
 */
function formatear_fecha($fecha, $incluir_hora = false) {
    if (empty($fecha)) return '';
    
    $meses = [
        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
    ];
    
    $timestamp = strtotime($fecha);
    $dia = date('d', $timestamp);
    $mes = $meses[(int)date('m', $timestamp)];
    $anio = date('Y', $timestamp);
    
    $fecha_formateada = "$dia de $mes de $anio";
    
    if ($incluir_hora) {
        $hora = date('H:i', $timestamp);
        $fecha_formateada .= " a las $hora";
    }
    
    return $fecha_formateada;
}

/**
 * Calcula la edad a partir de una fecha de nacimiento
 * @param string $fecha_nacimiento Fecha en formato Y-m-d
 * @return int Edad en años
 */
function calcular_edad($fecha_nacimiento) {
    $fecha_nac = new DateTime($fecha_nacimiento);
    $hoy = new DateTime();
    $edad = $hoy->diff($fecha_nac);
    return $edad->y;
}

/**
 * Valida una fecha
 * @param string $fecha Fecha a validar
 * @param string $formato Formato esperado (por defecto Y-m-d)
 * @return bool
 */
function validar_fecha($fecha, $formato = 'Y-m-d') {
    $d = DateTime::createFromFormat($formato, $fecha);
    return $d && $d->format($formato) === $fecha;
}

/**
 * Redirecciona a una URL
 * @param string $url URL destino
 */
function redirigir($url) {
    header("Location: $url");
    exit();
}

/**
 * Obtiene la URL completa actual
 * @return string
 */
function obtener_url_actual() {
    $protocolo = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    return $protocolo . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

/**
 * Valida si una cadena es un número válido
 * @param mixed $valor Valor a validar
 * @return bool
 */
function es_numero_valido($valor) {
    return is_numeric($valor) && $valor > 0;
}

/**
 * Trunca un texto a cierta longitud
 * @param string $texto Texto a truncar
 * @param int $longitud Longitud máxima
 * @param string $sufijo Sufijo a agregar (por defecto ...)
 * @return string
 */
function truncar_texto($texto, $longitud = 100, $sufijo = '...') {
    if (strlen($texto) <= $longitud) {
        return $texto;
    }
    return substr($texto, 0, $longitud) . $sufijo;
}

/**
 * Convierte un array en opciones HTML para select
 * @param array $array Array de opciones
 * @param string $valor_seleccionado Valor preseleccionado
 * @param string $campo_valor Campo del array para el value
 * @param string $campo_texto Campo del array para el texto visible
 * @return string HTML con las opciones
 */
function generar_opciones_select($array, $valor_seleccionado = '', $campo_valor = 'id', $campo_texto = 'nombre') {
    $html = '';
    foreach ($array as $item) {
        $valor = is_array($item) ? $item[$campo_valor] : $item;
        $texto = is_array($item) ? $item[$campo_texto] : $item;
        $selected = ($valor == $valor_seleccionado) ? 'selected' : '';
        $html .= "<option value='$valor' $selected>$texto</option>";
    }
    return $html;
}

/**
 * Formatea un número a formato de moneda
 * @param float $cantidad Cantidad a formatear
 * @param string $simbolo Símbolo de moneda
 * @return string
 */
function formatear_moneda($cantidad, $simbolo = '$') {
    return $simbolo . number_format($cantidad, 2, '.', ',');
}

/**
 * Obtiene la extensión de un archivo
 * @param string $nombre_archivo Nombre del archivo
 * @return string Extensión en minúsculas
 */
function obtener_extension_archivo($nombre_archivo) {
    return strtolower(pathinfo($nombre_archivo, PATHINFO_EXTENSION));
}

/**
 * Valida el tamaño de un archivo
 * @param int $tamano_bytes Tamaño en bytes
 * @param int $tamano_max Tamaño máximo permitido
 * @return bool
 */
function validar_tamano_archivo($tamano_bytes, $tamano_max = TAMANO_MAX_ARCHIVO) {
    return $tamano_bytes <= $tamano_max;
}

/**
 * Convierte bytes a formato legible
 * @param int $bytes Cantidad de bytes
 * @param int $decimales Decimales a mostrar
 * @return string
 */
function formatear_bytes($bytes, $decimales = 2) {
    $unidades = ['B', 'KB', 'MB', 'GB', 'TB'];
    $factor = floor((strlen($bytes) - 1) / 3);
    return sprintf("%.{$decimales}f", $bytes / pow(1024, $factor)) . ' ' . $unidades[$factor];
}

/**
 * Genera un nombre único para archivo
 * @param string $nombre_original Nombre original del archivo
 * @return string Nombre único
 */
function generar_nombre_archivo_unico($nombre_original) {
    $extension = obtener_extension_archivo($nombre_original);
    return uniqid('file_', true) . '.' . $extension;
}

/**
 * Debug - Imprime variable de forma legible (solo en modo debug)
 * @param mixed $variable Variable a imprimir
 * @param bool $detener Si debe detener la ejecución
 */
function debug($variable, $detener = false) {
    if (DEBUG_MODE) {
        echo '<pre>';
        print_r($variable);
        echo '</pre>';
        if ($detener) {
            die('Debug detenido');
        }
    }
}

/**
 * Registra un evento en el log del sistema
 * @param string $mensaje Mensaje a registrar
 * @param string $nivel Nivel del log (INFO, WARNING, ERROR)
 */
function registrar_log($mensaje, $nivel = 'INFO') {
    $fecha = date('Y-m-d H:i:s');
    $log = "[$fecha] [$nivel] $mensaje" . PHP_EOL;
    
    $archivo_log = RUTA_RAIZ . '/logs/sistema.log';
    
    // Crear directorio si no existe
    if (!file_exists(dirname($archivo_log))) {
        mkdir(dirname($archivo_log), 0777, true);
    }
    
    file_put_contents($archivo_log, $log, FILE_APPEND);
}

/**
 * Obtiene el año académico actual del campamento
 * @return int Año actual
 */
function obtener_anio_campamento() {
    return ANIO_CAMPAMENTO_ACTUAL;
}

/**
 * Genera una respuesta JSON
 * @param bool $exito Si la operación fue exitosa
 * @param string $mensaje Mensaje de respuesta
 * @param mixed $datos Datos adicionales
 */
function respuesta_json($exito, $mensaje, $datos = null) {
    header('Content-Type: application/json');
    echo json_encode([
        'exito' => $exito,
        'mensaje' => $mensaje,
        'datos' => $datos
    ], JSON_UNESCAPED_UNICODE);
    exit();
}
?>