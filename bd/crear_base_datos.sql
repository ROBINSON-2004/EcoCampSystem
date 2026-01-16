-- ============================================
-- SISTEMA DE GESTIÓN DE CAMPAMENTO
-- Base de Datos en Español
-- ============================================

CREATE DATABASE IF NOT EXISTS campamento_db 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_spanish_ci;

USE campamento_db;

-- ============================================
-- TABLA: usuarios (Base para todos los tipos)
-- ============================================
CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    correo_electronico VARCHAR(100) UNIQUE NOT NULL,
    contrasena VARCHAR(255) NOT NULL,
    tipo_usuario ENUM('administrador', 'padre', 'trabajador', 'consejero') NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    telefono VARCHAR(20),
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    ultimo_acceso DATETIME,
    estado ENUM('activo', 'inactivo', 'suspendido') DEFAULT 'activo',
    INDEX idx_correo (correo_electronico),
    INDEX idx_tipo (tipo_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- ============================================
-- TABLA: padres (Información adicional)
-- ============================================
CREATE TABLE padres (
    id_padre INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    direccion TEXT,
    ciudad VARCHAR(100),
    codigo_postal VARCHAR(10),
    ocupacion VARCHAR(100),
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    INDEX idx_usuario (id_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- ============================================
-- TABLA: trabajadores
-- ============================================
CREATE TABLE trabajadores (
    id_trabajador INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    puesto VARCHAR(100),
    fecha_contratacion DATE,
    salario DECIMAL(10,2),
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    INDEX idx_usuario (id_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- ============================================
-- TABLA: grupos
-- ============================================
CREATE TABLE grupos (
    id_grupo INT AUTO_INCREMENT PRIMARY KEY,
    nombre_grupo VARCHAR(100) NOT NULL,
    descripcion TEXT,
    edad_minima INT,
    edad_maxima INT,
    capacidad_maxima INT DEFAULT 20,
    id_consejero INT,
    anio_campamento YEAR NOT NULL,
    estado ENUM('activo', 'inactivo', 'completo') DEFAULT 'activo',
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_consejero) REFERENCES usuarios(id_usuario) ON DELETE SET NULL,
    INDEX idx_anio (anio_campamento),
    INDEX idx_consejero (id_consejero)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- ============================================
-- TABLA: campistas
-- ============================================
CREATE TABLE campistas (
    id_campista INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    fecha_nacimiento DATE NOT NULL,
    edad INT,
    genero ENUM('masculino', 'femenino', 'otro', 'prefiero_no_decir'),
    id_padre INT NOT NULL,
    foto_perfil VARCHAR(255),
    notas_especiales TEXT,
    estado_inscripcion ENUM('pendiente', 'aprobado', 'rechazado', 'retirado') DEFAULT 'pendiente',
    anio_inscripcion YEAR NOT NULL,
    fecha_inscripcion DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_padre) REFERENCES padres(id_padre) ON DELETE CASCADE,
    INDEX idx_padre (id_padre),
    INDEX idx_anio (anio_inscripcion),
    INDEX idx_estado (estado_inscripcion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- ============================================
-- TABLA: campistas_grupos (Relación muchos a muchos)
-- ============================================
CREATE TABLE campistas_grupos (
    id_campista_grupo INT AUTO_INCREMENT PRIMARY KEY,
    id_campista INT NOT NULL,
    id_grupo INT NOT NULL,
    fecha_asignacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    estado ENUM('activo', 'retirado') DEFAULT 'activo',
    FOREIGN KEY (id_campista) REFERENCES campistas(id_campista) ON DELETE CASCADE,
    FOREIGN KEY (id_grupo) REFERENCES grupos(id_grupo) ON DELETE CASCADE,
    UNIQUE KEY unico_campista_grupo (id_campista, id_grupo),
    INDEX idx_campista (id_campista),
    INDEX idx_grupo (id_grupo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- ============================================
-- TABLA: informacion_emergencia
-- ============================================
CREATE TABLE informacion_emergencia (
    id_emergencia INT AUTO_INCREMENT PRIMARY KEY,
    id_campista INT NOT NULL,
    nombre_contacto VARCHAR(200) NOT NULL,
    relacion VARCHAR(50) NOT NULL,
    telefono_principal VARCHAR(20) NOT NULL,
    telefono_secundario VARCHAR(20),
    correo_electronico VARCHAR(100),
    direccion TEXT,
    es_contacto_principal BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (id_campista) REFERENCES campistas(id_campista) ON DELETE CASCADE,
    INDEX idx_campista (id_campista)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- ============================================
-- TABLA: informacion_medica
-- ============================================
CREATE TABLE informacion_medica (
    id_informacion_medica INT AUTO_INCREMENT PRIMARY KEY,
    id_campista INT NOT NULL,
    alergias TEXT,
    medicamentos_actuales TEXT,
    condiciones_medicas TEXT,
    tipo_sangre VARCHAR(5),
    seguro_medico VARCHAR(100),
    numero_poliza VARCHAR(100),
    nombre_medico VARCHAR(200),
    telefono_medico VARCHAR(20),
    observaciones TEXT,
    fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_campista) REFERENCES campistas(id_campista) ON DELETE CASCADE,
    INDEX idx_campista (id_campista)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- ============================================
-- TABLA: formularios
-- ============================================
CREATE TABLE formularios (
    id_formulario INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    descripcion TEXT,
    tipo_formulario ENUM('consentimiento', 'medico', 'liberacion', 'otro') NOT NULL,
    archivo_url VARCHAR(255) NOT NULL,
    es_obligatorio BOOLEAN DEFAULT TRUE,
    anio_vigencia YEAR NOT NULL,
    fecha_subida DATETIME DEFAULT CURRENT_TIMESTAMP,
    subido_por INT,
    estado ENUM('activo', 'inactivo', 'archivado') DEFAULT 'activo',
    FOREIGN KEY (subido_por) REFERENCES usuarios(id_usuario) ON DELETE SET NULL,
    INDEX idx_tipo (tipo_formulario),
    INDEX idx_anio (anio_vigencia)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- ============================================
-- TABLA: formularios_campistas (Seguimiento)
-- ============================================
CREATE TABLE formularios_campistas (
    id_formulario_campista INT AUTO_INCREMENT PRIMARY KEY,
    id_formulario INT NOT NULL,
    id_campista INT NOT NULL,
    fecha_firma DATETIME,
    firmado_por INT,
    archivo_firmado VARCHAR(255),
    estado ENUM('pendiente', 'completado', 'rechazado') DEFAULT 'pendiente',
    observaciones TEXT,
    FOREIGN KEY (id_formulario) REFERENCES formularios(id_formulario) ON DELETE CASCADE,
    FOREIGN KEY (id_campista) REFERENCES campistas(id_campista) ON DELETE CASCADE,
    FOREIGN KEY (firmado_por) REFERENCES usuarios(id_usuario) ON DELETE SET NULL,
    UNIQUE KEY unico_formulario_campista (id_formulario, id_campista),
    INDEX idx_formulario (id_formulario),
    INDEX idx_campista (id_campista),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- ============================================
-- TABLA: actividades
-- ============================================
CREATE TABLE actividades (
    id_actividad INT AUTO_INCREMENT PRIMARY KEY,
    nombre_actividad VARCHAR(200) NOT NULL,
    descripcion TEXT,
    tipo_actividad ENUM('deportiva', 'artistica', 'educativa', 'recreativa', 'otra') NOT NULL,
    ubicacion VARCHAR(200),
    duracion_minutos INT,
    capacidad_maxima INT,
    edad_minima INT,
    edad_maxima INT,
    materiales_necesarios TEXT,
    instrucciones TEXT,
    estado ENUM('activo', 'inactivo') DEFAULT 'activo',
    INDEX idx_tipo (tipo_actividad)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- ============================================
-- TABLA: actividades_programadas
-- ============================================
CREATE TABLE actividades_programadas (
    id_actividad_programada INT AUTO_INCREMENT PRIMARY KEY,
    id_actividad INT NOT NULL,
    id_grupo INT NOT NULL,
    fecha_actividad DATE NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    id_responsable INT,
    estado ENUM('programada', 'en_curso', 'completada', 'cancelada') DEFAULT 'programada',
    observaciones TEXT,
    FOREIGN KEY (id_actividad) REFERENCES actividades(id_actividad) ON DELETE CASCADE,
    FOREIGN KEY (id_grupo) REFERENCES grupos(id_grupo) ON DELETE CASCADE,
    FOREIGN KEY (id_responsable) REFERENCES usuarios(id_usuario) ON DELETE SET NULL,
    INDEX idx_fecha (fecha_actividad),
    INDEX idx_grupo (id_grupo),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- ============================================
-- TABLA: asistencia
-- ============================================
CREATE TABLE asistencia (
    id_asistencia INT AUTO_INCREMENT PRIMARY KEY,
    id_campista INT NOT NULL,
    fecha_asistencia DATE NOT NULL,
    hora_entrada TIME,
    hora_salida TIME,
    estado_asistencia ENUM('presente', 'ausente', 'tardanza', 'retiro_temprano') NOT NULL,
    registrado_por INT,
    observaciones TEXT,
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_campista) REFERENCES campistas(id_campista) ON DELETE CASCADE,
    FOREIGN KEY (registrado_por) REFERENCES usuarios(id_usuario) ON DELETE SET NULL,
    UNIQUE KEY unico_campista_fecha (id_campista, fecha_asistencia),
    INDEX idx_fecha (fecha_asistencia),
    INDEX idx_campista (id_campista),
    INDEX idx_estado (estado_asistencia)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- ============================================
-- TABLA: notificaciones
-- ============================================
CREATE TABLE notificaciones (
    id_notificacion INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    mensaje TEXT NOT NULL,
    tipo_notificacion ENUM('general', 'emergencia', 'actividad', 'recordatorio', 'administrativa') NOT NULL,
    enviada_por INT,
    destinatarios ENUM('todos', 'padres', 'trabajadores', 'especifico') NOT NULL,
    fecha_envio DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_programada DATETIME,
    estado ENUM('borrador', 'enviada', 'programada') DEFAULT 'borrador',
    FOREIGN KEY (enviada_por) REFERENCES usuarios(id_usuario) ON DELETE SET NULL,
    INDEX idx_fecha (fecha_envio),
    INDEX idx_tipo (tipo_notificacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- ============================================
-- TABLA: notificaciones_usuarios (Seguimiento individual)
-- ============================================
CREATE TABLE notificaciones_usuarios (
    id_notificacion_usuario INT AUTO_INCREMENT PRIMARY KEY,
    id_notificacion INT NOT NULL,
    id_usuario INT NOT NULL,
    leida BOOLEAN DEFAULT FALSE,
    fecha_lectura DATETIME,
    FOREIGN KEY (id_notificacion) REFERENCES notificaciones(id_notificacion) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    INDEX idx_notificacion (id_notificacion),
    INDEX idx_usuario (id_usuario),
    INDEX idx_leida (leida)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- ============================================
-- TABLA: registros_actividad (Logs del sistema)
-- ============================================
CREATE TABLE registros_actividad (
    id_registro INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT,
    accion VARCHAR(100) NOT NULL,
    tabla_afectada VARCHAR(50),
    id_registro_afectado INT,
    descripcion TEXT,
    ip_address VARCHAR(45),
    fecha_hora DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE SET NULL,
    INDEX idx_usuario (id_usuario),
    INDEX idx_fecha (fecha_hora)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- ============================================
-- VISTAS ÚTILES
-- ============================================

-- Vista: Campistas con información completa
CREATE VIEW vista_campistas_completa AS
SELECT 
    c.id_campista,
    c.nombre,
    c.apellido,
    c.fecha_nacimiento,
    c.edad,
    c.genero,
    c.estado_inscripcion,
    c.anio_inscripcion,
    u.nombre AS nombre_padre,
    u.apellido AS apellido_padre,
    u.correo_electronico AS correo_padre,
    u.telefono AS telefono_padre,
    g.nombre_grupo,
    g.id_consejero
FROM campistas c
INNER JOIN padres p ON c.id_padre = p.id_padre
INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
LEFT JOIN campistas_grupos cg ON c.id_campista = cg.id_campista AND cg.estado = 'activo'
LEFT JOIN grupos g ON cg.id_grupo = g.id_grupo;

-- Vista: Actividades del día
CREATE VIEW vista_actividades_hoy AS
SELECT 
    ap.id_actividad_programada,
    a.nombre_actividad,
    a.tipo_actividad,
    a.ubicacion,
    ap.fecha_actividad,
    ap.hora_inicio,
    ap.hora_fin,
    g.nombre_grupo,
    u.nombre AS responsable_nombre,
    u.apellido AS responsable_apellido,
    ap.estado
FROM actividades_programadas ap
INNER JOIN actividades a ON ap.id_actividad = a.id_actividad
INNER JOIN grupos g ON ap.id_grupo = g.id_grupo
LEFT JOIN usuarios u ON ap.id_responsable = u.id_usuario
WHERE ap.fecha_actividad = CURDATE()
ORDER BY ap.hora_inicio;

-- ============================================
-- DATOS INICIALES
-- ============================================

-- Usuario administrador por defecto
INSERT INTO usuarios (correo_electronico, contrasena, tipo_usuario, nombre, apellido, telefono) 
VALUES ('admin@campamento.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'administrador', 'Administrador', 'Principal', '0999999999');
-- Contraseña: password (cambiar en producción)