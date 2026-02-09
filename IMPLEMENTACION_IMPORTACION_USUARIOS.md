# Implementación de Importación Masiva de Usuarios

## 📋 Resumen de Cambios

Se ha implementado una funcionalidad completa para importar usuarios de manera masiva mediante archivos CSV en el panel de administración. Esta funcionalidad permite:

✅ Descargar una plantilla CSV con el formato correcto
✅ Importar múltiples usuarios (alumnos, tutores, instructores, administradores)
✅ Validación automática de datos
✅ Registro del historial de importaciones
✅ Detección y reporte de errores por fila

---

## 🏗️ Cambios Realizados

### Backend (Laravel)

#### 1. **Controlador: UserImportController**
**Archivo:** `Backend/app/Http/Controllers/UserImportController.php`

**Métodos principales:**
- `downloadTemplate()` - Descarga plantilla CSV de ejemplo
- `import(Request $request)` - Procesa y importa archivo CSV
- `validateUserRow()` - Valida datos de cada fila
- `createUserFromRow()` - Crea usuario y registros relacionados

**Características:**
- Parsing de CSV nativo de PHP
- Validaciones completas:
  - Email único y válido
  - Teléfono único con 9 dígitos
  - Tipo de usuario válido
  - Campos requeridos no vacíos
- Transacciones de base de datos para integridad
- Creación automática de registros relacionados (Alumno, Tutor, Instructor)
- Reporte detallado de errores por fila

#### 2. **Rutas API**
**Archivo:** `Backend/routes/api.php`

```php
Route::get('/users/import/template', [UserImportController::class, 'downloadTemplate']);
Route::post('/users/import', [UserImportController::class, 'import']);
```

**Protegidas con:** `auth:sanctum` (solo usuarios autenticados)

#### 3. **Migración: user_imports**
**Archivo:** `Backend/database/migrations/2026_02_09_000000_create_user_imports_table.php`

Tabla para registrar historial de importaciones:
- `id` - ID de importación
- `user_id` - ID del administrador que hizo la importación
- `total_users` - Total de usuarios procesados
- `successful_users` - Usuarios creados exitosamente
- `failed_users` - Usuarios con error
- `errors` - JSON con detalles de errores
- `original_filename` - Nombre del archivo subido
- `created_at`, `updated_at` - Timestamps

#### 4. **Modelo: UserImport**
**Archivo:** `Backend/app/Models/UserImport.php`

Modelo Eloquent para la tabla user_imports con relación a User.

---

### Frontend (Vue 3)

#### 1. **Componente Modal: ImportUsuariosModal**
**Archivo:** `Frontend/src/components/ImportUsuariosModal.vue`

**Características:**
- Sección para descargar plantilla CSV
- Selector de archivo con validación
- Subida de archivo mediante FormData
- Visualización de resultados de importación
- Mensaje de éxito/error
- Listado de errores detallados por fila
- Estados de carga

**Métodos:**
- `descargarPlantilla()` - Descarga CSV de ejemplo
- `importarUsuarios()` - Sube y procesa el archivo
- `onFileSelected()` - Maneja selección de archivo
- `cerrarModal()` - Cierra el modal y limpia estado

#### 2. **Vista: UsersView.vue (Actualizada)**
**Archivo:** `Frontend/src/views/UsersView.vue`

**Cambios:**
- Importación del componente `ImportUsuariosModal`
- Botón "Importar CSV" en la barra de herramientas
- Manejo de evento `@success` para refrescar tabla
- Nuevo estado `mostrarImportModal`

---

## 📝 Formato del CSV

### Campos Requeridos

| Campo | Tipo | Descripción | Ejemplo |
|-------|------|-------------|---------|
| nombre | string | Nombre del usuario | Juan |
| apellidos | string | Apellidos del usuario | García López |
| email | string | Email único | juan.garcia@centro.local |
| n_tel | string | Teléfono (9 dígitos) | 600123456 |
| password | string | Contraseña sin encriptar | miPassword123 |
| tipo | enum | Tipo de usuario | alumno, tutor, instructor, admin |

### Ejemplo de CSV

```csv
nombre,apellidos,email,n_tel,password,tipo
Juan,García López,juan.garcia@centro.local,600123456,password123,alumno
María,Rodríguez Pérez,maria.rodriguez@centro.local,600234567,tutor2024,tutor
Carlos,López Martínez,carlos.lopez@empresa.local,610345678,instr2024,instructor
Admin,User,admin2@test.com,600000000,admin123,admin
```

---

## 🎯 Tipos de Usuarios Soportados

| Tipo | Descripción | Tabla Relacionada |
|------|--------|------------------|
| `admin` | Administrador del sistema | - |
| `alumno` | Estudiante | Alumno |
| `tutor` | Tutor de centro educativo | Tutor |
| `instructor` | Instructor de empresa | Instructor |

---

## 🔐 Validaciones Realizadas

### Por Usuario
1. **Email:**
   - Obligatorio
   - Formato válido (RFC)
   - Debe ser único en la BD

2. **Teléfono:**
   - Obligatorio
   - Exactamente 9 dígitos
   - Debe ser único en la BD

3. **Nombre y Apellidos:**
   - Obligatorios
   - Máximo 255 caracteres

4. **Contraseña:**
   - Obligatoria
   - Se encripta automáticamente con bcrypt

5. **Tipo:**
   - Obligatorio
   - Debe ser: admin, alumno, tutor o instructor

### Limitaciones del Archivo
- Tamaño máximo: 5 MB
- Formatos: CSV o TXT
- Encriptación: UTF-8

---

## 📊 Respuestas de la API

### Descarga de Plantilla
```bash
GET /api/users/import/template
```
**Respuesta:** Archivo CSV descargado

---

### Importación de Usuarios
```bash
POST /api/users/import
Content-Type: multipart/form-data

file: <archive.csv>
```

**Respuesta Exitosa (200):**
```json
{
  "status": "success",
  "message": "Se han importado 5 usuarios correctamente.",
  "data": {
    "created": 5,
    "failed": 0,
    "errors": []
  }
}
```

**Respuesta con Errores (200):**
```json
{
  "status": "success",
  "message": "Se han importado 3 usuarios correctamente.",
  "data": {
    "created": 3,
    "failed": 2,
    "errors": [
      "Fila 3: El email 'juan@test.com' ya está registrado.",
      "Fila 5: El teléfono '123456789' debe tener exactamente 9 dígitos."
    ]
  }
}
```

**Errores Comunes (400):**
```json
{
  "status": "error",
  "message": "El archivo CSV está vacío o mal formateado."
}
```

---

## 🚀 Cómo Usar la Funcionalidad

### Paso 1: Acceder al Panel de Usuarios
1. Inicia sesión como administrador
2. Navega a la sección "Usuarios"
3. Haz clic en el botón **"Importar CSV"** (verde, arriba a la derecha)

### Paso 2: Descargar Plantilla (Opcional)
1. En el modal que aparece, haz clic en **"Descargar Plantilla CSV"**
2. Se descargará un archivo `plantilla_usuarios_YYYY-MM-DD_HHMMSS.csv`
3. Abre el archivo en Excel, Google Sheets o editor de texto

### Paso 3: Rellenar la Plantilla
1. Rellena los datos de los usuarios respetando el formato
2. **Importante:** 
   - No cambies los nombres de las columnas
   - Cada usuario en una fila
   - Email y teléfono deben ser únicos
   - Teléfono debe tener 9 dígitos
   - Tipo debe ser: admin, alumno, tutor o instructor

### Paso 4: Subir el Archivo
1. En el modal, haz clic en **"Seleccionar archivo"**
2. Elige tu archivo CSV completo
3. Haz clic en **"Importar Usuarios"**

### Paso 5: Revisar Resultados
1. El sistema procesará el archivo y mostrará:
   - **Usuarios creados:** ✓ (en verde)
   - **Usuarios fallidos:** ✗ (en rojo, si los hay)
   - **Errores detallados** por fila

---

## 🔧 Instalación y Migración

### 1. Migrar la base de datos
```bash
cd Backend
php artisan migrate
```

Esto creará la tabla `user_imports` para el historial.

### 2. Sin cambios en modelos existentes
La implementación es **no invasiva**:
- No se modifica la tabla `users`
- No se modifica la tabla `alumno`, `tutor`, `instructor`
- Solo se crea una tabla nueva para historial
- El UserController existente se usa sin cambios

---

## 📖 Lógica de Creación de Usuarios

Cuando se importa un usuario correctamente:

1. **Se validan todos los datos** (email único, teléfono único, formato válido, etc.)
2. **Se crea el registro en `users`** con:
   - ID autogenerado según el tipo (10000-20000-30000-40000)
   - Email y teléfono únicos
   - Contraseña encriptada
3. **Se crean registros relacionados** según el tipo:
   - **Alumno:** Se crea registro en tabla `alumno`
   - **Tutor:** Se crea registro en tabla `tutor`
   - **Instructor:** Se crea registro en tabla `instructor`
   - **Admin:** No requiere tabla relacionada

> **Nota:** Esto es transaccional. Si algo falla, todo se revierte (ACID).

---

## 🛡️ Seguridad

✅ **Autenticación:** Solo usuarios con token válido pueden importar
✅ **Validación:** Todos los campos se validan antes de insertar
✅ **Encriptación:** Las contraseñas se encriptan automáticamente
✅ **SQL Injection:** Se usa Eloquent ORM, no raw SQL
✅ **Autorización:** Requiere token `auth:sanctum`
✅ **Límite de Tamaño:** Máximo 5MB por archivo

---

## 📝 Notas Importantes

1. **Las contraseñas:** Se guardan sin encriptar en el CSV pero se encriptan en la BD
2. **IDs automáticos:** Cada tipo de usuario tiene su rango de IDs
3. **Transacciones:** Si hay error en un usuario, se continúa con los siguientes
4. **Historial:** Cada importación queda registrada en la tabla `user_imports`
5. **Errores parciales:** Se pueden importar algunos usuarios aunque otros fallen

---

## 🐛 Solución de Problemas

**Error: "El email ya está registrado"**
- El email ya existe en la BD, revisa que sea único

**Error: "El teléfono debe tener exactamente 9 dígitos"**
- El teléfono debe ser numérico y tener 9 dígitos sin caracteres especiales

**Error: "El tipo no es válido"**
- Usa solo: `admin`, `alumno`, `tutor`, `instructor` (minúsculas)

**Error: "El archivo CSV está vacío"**
- Asegúrate de que el archivo tiene contenido y encabezados

---

## 📌 Próximas Mejoras Posibles

- [ ] Soporte para Excel (.xlsx)
- [ ] Asignación automática de grados/empresas en la importación
- [ ] Vista de historial de importaciones
- [ ] Exportación de usuarios existentes
- [ ] Búsqueda de duplicados antes de importar
- [ ] Validación de contraseñas por política

---

## 📞 Soporte

Si hay problemas:
1. Revisa los errores detallados en el modal
2. Valida el formato del CSV
3. Verifica que no haya datos duplicados
4. Revisa los logs de Laravel: `storage/logs/laravel.log`
