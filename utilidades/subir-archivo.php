<?php
/**
 * Funciones para subir archivos de forma segura
 */

/**
 * Subir archivo al servidor
 * 
 * @param array $archivo - Archivo de $_FILES
 * @param string $carpeta - Carpeta destino (formularios, documentos, fotos-campistas)
 * @param array $extensiones_permitidas - Extensiones permitidas
 * @param int $tamano_maximo - Tamaño máximo en bytes (default 5MB)
 * @return array - ['success' => bool, 'mensaje' => string, 'ruta' => string]
 */
function subirArchivo($archivo, $carpeta, $extensiones_permitidas = [], $tamano_maximo = 5242880) {
    
    // Validar que se haya subido un archivo
    if (!isset($archivo) || $archivo['error'] !== UPLOAD_ERR_OK) {
        return [
            'success' => false,
            'mensaje' => 'Error al subir el archivo',
            'ruta' => null
        ];
    }
    
    // Validar tamaño
    if ($archivo['size'] > $tamano_maximo) {
        $mb = round($tamano_maximo / 1048576, 1);
        return [
            'success' => false,
            'mensaje' => "El archivo es muy grande. Tamaño máximo: {$mb}MB",
            'ruta' => null
        ];
    }
    
    // Obtener extensión
    $nombre_original = $archivo['name'];
    $extension = strtolower(pathinfo($nombre_original, PATHINFO_EXTENSION));
    
    // Validar extensión
    if (!empty($extensiones_permitidas) && !in_array($extension, $extensiones_permitidas)) {
        return [
            'success' => false,
            'mensaje' => 'Tipo de archivo no permitido. Extensiones válidas: ' . implode(', ', $extensiones_permitidas),
            'ruta' => null
        ];
    }
    
    // Validar tipo MIME (seguridad adicional)
    $mime_permitidos = [
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif'
    ];
    
    if (isset($mime_permitidos[$extension])) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $archivo['tmp_name']);
        finfo_close($finfo);
        
        if ($mime !== $mime_permitidos[$extension]) {
            return [
                'success' => false,
                'mensaje' => 'El tipo de archivo no coincide con su extensión',
                'ruta' => null
            ];
        }
    }
    
    // Crear nombre único para el archivo
    $nombre_unico = uniqid() . '_' . time() . '.' . $extension;
    
    // Definir ruta de destino
    $ruta_base = __DIR__ . '/../public/uploads/';
    $ruta_carpeta = $ruta_base . $carpeta . '/';
    
    // Crear carpeta si no existe
    if (!is_dir($ruta_carpeta)) {
        if (!mkdir($ruta_carpeta, 0755, true)) {
            return [
                'success' => false,
                'mensaje' => 'Error al crear carpeta de destino',
                'ruta' => null
            ];
        }
    }
    
    // Ruta completa del archivo
    $ruta_completa = $ruta_carpeta . $nombre_unico;
    
    // Mover archivo
    if (move_uploaded_file($archivo['tmp_name'], $ruta_completa)) {
        // Establecer permisos
        chmod($ruta_completa, 0644);
        
        // Ruta relativa para guardar en BD
        $ruta_relativa = 'public/uploads/' . $carpeta . '/' . $nombre_unico;
        
        return [
            'success' => true,
            'mensaje' => 'Archivo subido exitosamente',
            'ruta' => $ruta_relativa,
            'nombre_original' => $nombre_original,
            'nombre_unico' => $nombre_unico,
            'extension' => $extension,
            'tamano' => $archivo['size']
        ];
    }
    
    return [
        'success' => false,
        'mensaje' => 'Error al guardar el archivo',
        'ruta' => null
    ];
}

/**
 * Eliminar archivo del servidor
 * 
 * @param string $ruta - Ruta del archivo a eliminar
 * @return bool
 */
function eliminarArchivo($ruta) {
    if (empty($ruta)) {
        return false;
    }
    
    $ruta_completa = __DIR__ . '/../' . $ruta;
    
    if (file_exists($ruta_completa)) {
        return unlink($ruta_completa);
    }
    
    return false;
}

/**
 * Validar que un archivo exista
 * 
 * @param string $ruta - Ruta del archivo
 * @return bool
 */
function archivoExiste($ruta) {
    if (empty($ruta)) {
        return false;
    }
    
    $ruta_completa = __DIR__ . '/../' . $ruta;
    return file_exists($ruta_completa);
}

/**
 * Obtener información de un archivo
 * 
 * @param string $ruta - Ruta del archivo
 * @return array|null
 */
function obtenerInfoArchivo($ruta) {
    if (!archivoExiste($ruta)) {
        return null;
    }
    
    $ruta_completa = __DIR__ . '/../' . $ruta;
    
    return [
        'nombre' => basename($ruta),
        'extension' => pathinfo($ruta, PATHINFO_EXTENSION),
        'tamano' => filesize($ruta_completa),
        'tamano_formateado' => formatearTamano(filesize($ruta_completa)),
        'fecha_modificacion' => filemtime($ruta_completa),
        'es_imagen' => esImagen($ruta)
    ];
}

/**
 * Formatear tamaño de archivo
 * 
 * @param int $bytes
 * @return string
 */
function formatearTamano($bytes) {
    $unidades = ['B', 'KB', 'MB', 'GB'];
    $i = 0;
    
    while ($bytes >= 1024 && $i < count($unidades) - 1) {
        $bytes /= 1024;
        $i++;
    }
    
    return round($bytes, 2) . ' ' . $unidades[$i];
}

/**
 * Verificar si un archivo es una imagen
 * 
 * @param string $ruta
 * @return bool
 */
function esImagen($ruta) {
    $extension = strtolower(pathinfo($ruta, PATHINFO_EXTENSION));
    return in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
}

/**
 * Limpiar nombre de archivo (para nombres seguros)
 * 
 * @param string $nombre
 * @return string
 */
function limpiarNombreArchivo($nombre) {
    // Eliminar caracteres especiales
    $nombre = preg_replace('/[^A-Za-z0-9\-\_\.]/', '', $nombre);
    
    // Reemplazar espacios y múltiples guiones
    $nombre = preg_replace('/[\s-]+/', '-', $nombre);
    
    // Convertir a minúsculas
    $nombre = strtolower($nombre);
    
    return $nombre;
}

/**
 * Generar miniatura de imagen (opcional)
 * 
 * @param string $ruta_origen
 * @param int $ancho_max
 * @param int $alto_max
 * @return array
 */
function generarMiniatura($ruta_origen, $ancho_max = 200, $alto_max = 200) {
    if (!esImagen($ruta_origen)) {
        return [
            'success' => false,
            'mensaje' => 'El archivo no es una imagen'
        ];
    }
    
    $ruta_completa = __DIR__ . '/../' . $ruta_origen;
    
    if (!file_exists($ruta_completa)) {
        return [
            'success' => false,
            'mensaje' => 'Archivo no encontrado'
        ];
    }
    
    // Obtener información de la imagen
    $info = getimagesize($ruta_completa);
    $ancho_original = $info[0];
    $alto_original = $info[1];
    $tipo = $info[2];
    
    // Calcular nuevas dimensiones manteniendo proporción
    $ratio = min($ancho_max / $ancho_original, $alto_max / $alto_original);
    $nuevo_ancho = round($ancho_original * $ratio);
    $nuevo_alto = round($alto_original * $ratio);
    
    // Crear imagen según el tipo
    switch ($tipo) {
        case IMAGETYPE_JPEG:
            $imagen_origen = imagecreatefromjpeg($ruta_completa);
            break;
        case IMAGETYPE_PNG:
            $imagen_origen = imagecreatefrompng($ruta_completa);
            break;
        case IMAGETYPE_GIF:
            $imagen_origen = imagecreatefromgif($ruta_completa);
            break;
        default:
            return [
                'success' => false,
                'mensaje' => 'Tipo de imagen no soportado'
            ];
    }
    
    // Crear nueva imagen
    $imagen_nueva = imagecreatetruecolor($nuevo_ancho, $nuevo_alto);
    
    // Mantener transparencia para PNG
    if ($tipo == IMAGETYPE_PNG) {
        imagealphablending($imagen_nueva, false);
        imagesavealpha($imagen_nueva, true);
    }
    
    // Redimensionar
    imagecopyresampled(
        $imagen_nueva, 
        $imagen_origen, 
        0, 0, 0, 0, 
        $nuevo_ancho, 
        $nuevo_alto, 
        $ancho_original, 
        $alto_original
    );
    
    // Generar nombre para miniatura
    $info_archivo = pathinfo($ruta_origen);
    $ruta_miniatura = $info_archivo['dirname'] . '/thumb_' . $info_archivo['basename'];
    $ruta_miniatura_completa = __DIR__ . '/../' . $ruta_miniatura;
    
    // Guardar miniatura
    $guardado = false;
    switch ($tipo) {
        case IMAGETYPE_JPEG:
            $guardado = imagejpeg($imagen_nueva, $ruta_miniatura_completa, 85);
            break;
        case IMAGETYPE_PNG:
            $guardado = imagepng($imagen_nueva, $ruta_miniatura_completa, 8);
            break;
        case IMAGETYPE_GIF:
            $guardado = imagegif($imagen_nueva, $ruta_miniatura_completa);
            break;
    }
    
    // Liberar memoria
    imagedestroy($imagen_origen);
    imagedestroy($imagen_nueva);
    
    if ($guardado) {
        return [
            'success' => true,
            'mensaje' => 'Miniatura creada',
            'ruta' => $ruta_miniatura
        ];
    }
    
    return [
        'success' => false,
        'mensaje' => 'Error al guardar miniatura'
    ];
}
?>