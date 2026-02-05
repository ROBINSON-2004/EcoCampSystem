# Módulo de Formularios - EcoCamSystem

## Descripción General

El módulo de formularios permite a los administradores del campamento gestionar formularios digitales que deben ser completados y firmados por los padres de los campistas. Incluye funcionalidades de firma digital, seguimiento, notificaciones y reportes.

## Características Principales

### Para Administradores
- ✅ Crear y subir formularios en formato PDF, DOC, DOCX
- ✅ Asignar formularios a campistas específicos o a todos
- ✅ Marcar formularios como obligatorios u opcionales
- ✅ Establecer fechas límite de firma
- ✅ Ver seguimiento en tiempo real de firmas
- ✅ Estadísticas y reportes
- ✅ Notificaciones automáticas

### Para Padres
- ✅ Ver todos los formularios asignados a sus hijos
- ✅ Firmar formularios digitalmente
- ✅ Opción de subir documento firmado físicamente
- ✅ Ver historial de formularios firmados
- ✅ Recibir notificaciones de nuevos formularios

## Estructura de Archivos

```
sistema-campamento/
│
├── modelos/
│   ├── Formulario.php              # Modelo principal de formularios
│   └── FormularioCampista.php      # Relación formularios-campistas
│
├── controladores/
│   └── FormularioControlador.php   # Lógica de negocio
│
├── vistas/
│   ├── admin/formularios/
│   │   ├── lista.php              # Lista de formularios
│   │   ├── subir.php              # Crear nuevo formulario
│   │   ├── editar.php             # Editar formulario
│   │   └── seguimiento.php        # Ver seguimiento
│   │
│   └── padre/formularios/
│       ├── disponibles.php        # Lista de formularios del padre
│       ├── firmar.php             # Firmar formulario
│       └── enviados.php           # Historial de firmados
│
├── api/
│   └── formularios.php            # Endpoints AJAX
│
├── public/
│   ├── js/
│   │   └── formularios.js         # JavaScript para AJAX
│   └── uploads/
│       ├── formularios/           # Formularios originales
│       └── documentos/            # Documentos firmados
│
├── utilidades/
│   └── subir-archivo.php          # Funciones de upload
│
└── bd/
    └── formularios-bd.sql         # Script SQL de creación
```

## Instalación

### 1. Crear las tablas de base de datos

```sql
-- Ejecutar el script SQL
mysql -u usuario -p nombre_base_datos < bd/formularios-bd.sql
```

### 2. Verificar permisos de carpetas

```bash
# Dar permisos de escritura a las carpetas de uploads
chmod -R 755 public/uploads/
chmod -R 755 public/uploads/formularios/
chmod -R 755 public/uploads/documentos/
```

### 3. Configurar límites de upload en PHP

Editar `php.ini`:
```ini
upload_max_filesize = 5M
post_max_size = 10M
max_execution_time = 300
```

## Uso del Sistema

### Administradores

#### 1. Crear un Formulario

```php
// Navegar a: /vistas/admin/formularios/subir.php

Pasos:
1. Completar título y descripción
2. Seleccionar tipo de formulario
3. Subir archivo (PDF, DOC, DOCX)
4. Establecer fecha límite (opcional)
5. Marcar como obligatorio si es necesario
6. Opcionalmente asignar a todos los campistas
7. Guardar
```

#### 2. Asignar Formulario a Campistas

```php
// Desde la lista de formularios

Opciones:
- Asignar a todos los campistas activos (checkbox al crear)
- Asignar individualmente desde el seguimiento
- Usar la API para asignaciones programáticas
```

#### 3. Ver Seguimiento

```php
// Navegar a: /vistas/admin/formularios/seguimiento.php?id=X

Información disponible:
- Total asignados vs firmados
- Porcentaje de cumplimiento
- Lista de campistas con estado
- Información de contacto de padres
- Fechas de firma
- IP de firma (para auditoría)
```

### Padres

#### 1. Ver Formularios Pendientes

```php
// Navegar a: /vistas/padre/formularios/disponibles.php

Se muestran:
- Formularios agrupados por hijo
- Estado (Firmado/Pendiente)
- Prioridad (Obligatorio/Opcional)
- Fecha límite
- Botón para firmar
```

#### 2. Firmar un Formulario

```php
// Navegar a: firmar.php?id_formulario=X&id_campista=Y

Proceso:
1. Descargar y revisar el documento
2. Marcar checkbox de aceptación
3. Opcionalmente subir documento firmado físicamente
4. Confirmar firma digital
5. El sistema registra: usuario, fecha, hora, IP
```

## API Endpoints

### Listar Formularios
```javascript
GET /api/formularios.php?accion=listar&tipo=medico&activo=1

Response:
{
  "success": true,
  "formularios": [...]
}
```

### Obtener Formulario
```javascript
GET /api/formularios.php?accion=obtener&id=1

Response:
{
  "success": true,
  "formulario": {...},
  "estadisticas": {...}
}
```

### Cambiar Estado
```javascript
POST /api/formularios.php?accion=cambiar_estado
Body: id=1&estado=0

Response:
{
  "success": true,
  "mensaje": "Estado actualizado"
}
```

### Asignar a Campistas
```javascript
POST /api/formularios.php?accion=asignar_campistas
Body: id_formulario=1&campistas=[1,2,3]

Response:
{
  "success": true,
  "mensaje": "Formulario asignado a 3 campista(s)"
}
```

### Formularios de Padre
```javascript
GET /api/formularios.php?accion=formularios_padre&firmado=0

Response:
{
  "success": true,
  "formularios": [...],
  "pendientes": {
    "total_pendientes": 5,
    "campistas_con_pendientes": 2
  }
}
```

## JavaScript - Uso

```javascript
// Cambiar estado de formulario
FormulariosManager.cambiarEstado(idFormulario, nuevoEstado);

// Asignar a campistas seleccionados
FormulariosManager.asignarACampistas(idFormulario);

// Asignar a todos
FormulariosManager.asignarATodos(idFormulario);

// Cargar formularios pendientes
FormulariosManager.cargarFormulariosPendientes('container-id');

// Cargar próximos a vencer
FormulariosManager.cargarProximosVencer('container-id', 7);

// Seleccionar todos
FormulariosManager.toggleSeleccionarTodos(checkbox);
```

## Base de Datos

### Tabla: formularios
```sql
Campos principales:
- id: INT PRIMARY KEY
- titulo: VARCHAR(200)
- descripcion: TEXT
- archivo_url: VARCHAR(500)
- tipo: ENUM('consentimiento', 'medico', 'fotografico', 'otro')
- obligatorio: TINYINT(1)
- activo: TINYINT(1)
- fecha_creacion: DATETIME
- fecha_limite: DATE
- creado_por: INT (FK -> usuarios)
```

### Tabla: formularios_campistas
```sql
Campos principales:
- id: INT PRIMARY KEY
- id_formulario: INT (FK -> formularios)
- id_campista: INT (FK -> campistas)
- firmado: TINYINT(1)
- fecha_firma: DATETIME
- fecha_asignacion: DATETIME
- documento_firmado_url: VARCHAR(500)
- ip_firma: VARCHAR(45)
- observaciones: TEXT
```

### Vistas Útiles

#### vista_formularios_estadisticas
```sql
SELECT * FROM vista_formularios_estadisticas;
-- Retorna formularios con totales de asignados, firmados y porcentajes
```

#### vista_formularios_pendientes_padres
```sql
SELECT * FROM vista_formularios_pendientes_padres WHERE id_padre = 1;
-- Retorna formularios pendientes de un padre específico
```

### Procedimientos Almacenados

#### Asignar a todos
```sql
CALL asignar_formulario_todos(1);
-- Asigna el formulario ID 1 a todos los campistas activos
```

#### Próximos a vencer
```sql
CALL formularios_proximos_vencer(7);
-- Retorna formularios que vencen en los próximos 7 días
```

#### Firmar formulario
```sql
CALL firmar_formulario(1, 1, 'ruta/documento.pdf', '192.168.1.1');
-- Marca como firmado el formulario 1 para el campista 1
```

## Seguridad

### Validaciones Implementadas

1. **Upload de Archivos**
   - Extensiones permitidas: PDF, DOC, DOCX
   - Tamaño máximo: 5MB
   - Validación de tipo MIME
   - Nombres únicos con timestamp

2. **Firma Digital**
   - Registro de IP del firmante
   - Registro de fecha y hora exacta
   - Verificación de identidad (padre-campista)
   - No se puede firmar dos veces

3. **Acceso**
   - Verificación de sesión
   - Verificación de permisos por rol
   - Padres solo ven formularios de sus hijos
   - Admins tienen acceso completo

4. **SQL Injection**
   - Uso de PDO con prepared statements
   - Binding de parámetros
   - Sanitización de entradas

## Personalización

### Agregar Nuevo Tipo de Formulario

1. Modificar enum en SQL:
```sql
ALTER TABLE formularios 
MODIFY COLUMN tipo ENUM('consentimiento', 'medico', 'fotografico', 'nuevo_tipo', 'otro');
```

2. Actualizar vistas para incluir el nuevo tipo en los selectores

### Modificar Extensiones Permitidas

Editar en `FormularioControlador.php`:
```php
$resultado = subirArchivo($archivo, 'formularios', ['pdf', 'doc', 'docx', 'odt']);
```

### Cambiar Límite de Tamaño

Editar en `subir-archivo.php`:
```php
function subirArchivo($archivo, $carpeta, $extensiones_permitidas = [], $tamano_maximo = 10485760)
// 10485760 = 10MB
```

## Troubleshooting

### Problema: Archivos no se suben
```
Solución:
1. Verificar permisos de carpeta uploads
2. Revisar php.ini: upload_max_filesize y post_max_size
3. Verificar espacio en disco
```

### Problema: Formularios no aparecen para padres
```
Solución:
1. Verificar que el formulario esté activo
2. Verificar que esté asignado al campista
3. Verificar relación padre-campista en tabla padres_campistas
```

### Problema: Error al firmar
```
Solución:
1. Verificar que no esté ya firmado
2. Verificar permisos de carpeta documentos
3. Revisar logs de PHP para errores específicos
```

## Mejoras Futuras

- [ ] Firma manuscrita en canvas
- [ ] Envío automático de emails recordatorio
- [ ] Exportar reportes a Excel/PDF
- [ ] Versionamiento de formularios
- [ ] Plantillas de formularios predefinidas
- [ ] Integración con servicios de firma electrónica certificada
- [ ] Aplicación móvil para padres
- [ ] Notificaciones push

## Soporte

Para soporte o reportar bugs:
- Email: soporte@ecocamsystem.com
- GitHub Issues: [repositorio]/issues

## Licencia

Este módulo es parte de EcoCamSystem y está sujeto a sus términos de licencia.

---
Desarrollado para EcoCamSystem | Versión 1.0