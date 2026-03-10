# 📊 Importación Masiva Expandida - Documentación Completa

## 🎯 Descripción General

Se ha expandido el sistema de importación de datos para permitir al administrador importar múltiples tipos de entidades mediante CSV:

- ✅ **Usuarios** (alumnos, tutores, instructores, administradores)
- ✅ **Empresas** (colaboradoras)
- ✅ **Cursos/Grados**
- ✅ **Alumnos** (con creación automática de usuario)
- ✅ **Profesorado** (tutores e instructores con relación a empresas)

---

## 🏗️ Estructura del Backend

### Controllers Creados

#### 1. **UserImportController**
**Ubicación:** `Backend/app/Http/Controllers/UserImportController.php`

- `downloadTemplate()` → Descarga plantilla CSV de usuarios
- `import()` → Importa usuarios con validaciones

**Campos CSV:**
```csv
nombre,apellidos,email,n_tel,password,tipo
```

---

#### 2. **EmpresaImportController**
**Ubicación:** `Backend/app/Http/Controllers/EmpresaImportController.php`

- `downloadTemplate()` → Descarga plantilla CSV de empresas
- `import()` → Importa empresas

**Campos CSV:**
```csv
CIF,Nombre,Direccion,Email,N_Tel
```

**Validaciones:**
- CIF único
- Email válido y único
- Teléfono 9 dígitos único

---

#### 3. **GradoImportController**
**Ubicación:** `Backend/app/Http/Controllers/GradoImportController.php`

- `downloadTemplate()` → Descarga plantilla CSV de grados
- `import()` → Importa grados/cursos

**Campos CSV:**
```csv
nombre,curso
```

---

#### 4. **AlumnoImportController**
**Ubicación:** `Backend/app/Http/Controllers/AlumnoImportController.php`

- `downloadTemplate()` → Descarga plantilla CSV de alumnos
- `import()` → Importa alumnos (crea usuario + registro alumno)

**Campos CSV:**
```csv
nombre,apellidos,email,n_tel,password
```

**Proceso:**
1. Valida datos del alumno
2. Crea usuario con tipo='alumno'
3. Crea registro en tabla `alumno`
4. Todo en transacción ACID

---

#### 5. **TeacherImportController**
**Ubicación:** `Backend/app/Http/Controllers/TeacherImportController.php`

- `downloadTemplate()` → Descarga plantilla CSV de profesorado
- `import()` → Importa tutores e instructores

**Campos CSV:**
```csv
nombre,apellidos,email,n_tel,password,tipo,cif_empresa
```

**Tipos:** tutor, instructor

**Proceso especial para Instructores:**
- `cif_empresa` es opcional pero validado si existe
- Se asigna automáticamente a la empresa

---

### Rutas API

```
GET  /api/users/import/template      → Plantilla usuarios
POST /api/users/import               → Importar usuarios

GET  /api/empresas/import/template   → Plantilla empresas
POST /api/empresas/import            → Importar empresas

GET  /api/grados/import/template     → Plantilla cursos
POST /api/grados/import              → Importar cursos

GET  /api/alumnos/import/template    → Plantilla alumnos
POST /api/alumnos/import             → Importar alumnos

GET  /api/teachers/import/template   → Plantilla profesorado
POST /api/teachers/import            → Importar profesorado
```

Todas protegidas con `auth:sanctum`

---

## 🎨 Estructura del Frontend

### Componentes Modales Creados

#### 1. **ImportUsuariosModal.vue**
```
src/components/ImportUsuariosModal.vue
```

#### 2. **ImportEmpresasModal.vue**
```
src/components/ImportEmpresasModal.vue
```

#### 3. **ImportGradosModal.vue**
```
src/components/ImportGradosModal.vue
```

#### 4. **ImportAlumnosModal.vue**
```
src/components/ImportAlumnosModal.vue
```

#### 5. **ImportTeachersModal.vue**
```
src/components/ImportTeachersModal.vue
```

**Características comunes:**
- ✅ Descarga de plantilla
- ✅ Selector de archivo
- ✅ Validación en frontend
- ✅ Spinner de carga
- ✅ Mensajes de éxito/error
- ✅ Detalle de errores por fila
- ✅ Resumen de importación

---

### Navbar Actualizado

**Ubicación:** `Frontend/src/components/Navbar.vue`

**Menú Admin → Gestión:**
```
├── Usuarios (enlace)
├── Competencias y RAs (enlace)
├── Grados y Asignaturas (enlace)
├── Empresas (enlace)
├── ─────────────────────── (separador)
├── 📤 Importar Datos
│   ├── Usuarios
│   ├── Empresas
│   ├── Cursos
│   ├── Alumnos
│   └── Profesorado
└── (resto de opciones)
```

---

## 📋 Ejemplos de Plantillas CSV

### Usuarios
```csv
nombre,apellidos,email,n_tel,password,tipo
Juan,García López,juan.usuario@centro.local,600123456,pass123,alumno
```

### Empresas
```csv
CIF,Nombre,Direccion,Email,N_Tel
B12345678,TechBizi SL,C/ Gran Vía 12 Bilbao,info@tech.local,944000111
A87654321,IndusGoi SA,Pol. Ugaldeguren,contacto@indus.local,944000222
```

### Cursos/Grados
```csv
nombre,curso
Desarrollo de Aplicaciones Web,1º DAW
Administración Sistemas,2º ASIR
```

### Alumnos
```csv
nombre,apellidos,email,n_tel,password
María,Rodríguez Pérez,maria@centro.local,600111222,pass456
Luis,García López,luis@centro.local,600111333,pass789
```

### Profesorado
```csv
nombre,apellidos,email,n_tel,password,tipo,cif_empresa
Pedro,García López,pedro.tutor@centro.local,600999000,pass123,tutor,
Laura,Martínez Ruiz,laura.instructor@empresa.local,610345678,pass456,instructor,B12345678
```

---

## 🔄 Flujo de Uso

### Paso 1: Admin accede al sistema
```
Login → Home → Navbar
```

### Paso 2: Abre el menú de Gestión
```
Gestión (dropdown) → 📤 Importar Datos
```

### Paso 3: Selecciona el tipo a importar
```
Elegir entre:
- Usuarios
- Empresas
- Cursos
- Alumnos
- Profesorado
```

### Paso 4: En el modal
```
1. Descargar Plantilla (opcional)
2. Seleccionar/rellenar archivo CSV
3. Clic en "Importar"
4. Ver resultados
```

---

## ✅ Validaciones

### Usuarios
- Email válido y único
- Teléfono 9 dígitos único
- Campos obligatorios no vacíos
- Tipo válido: admin, alumno, tutor, instructor

### Empresas
- CIF único
- Email válido y único
- Teléfono 9 dígitos único
- Máximo 150 caracteres Nombre
- Máximo 150 caracteres Dirección

### Grados/Cursos
- Nombre obligatorio (máx 150 caracteres)
- Curso obligatorio (máx 50 caracteres)

### Alumnos
- Email válido y único
- Teléfono 9 dígitos único
- Campos obligatorios
- Se crea usuario automáticamente

### Profesorado
- Email válido y único
- Teléfono 9 dígitos único
- Tipo: tutor o instructor
- Si instructor: CIF_Empresa validado (si existe)

---

## 🛡️ Seguridad

✅ Requiere autenticación (token Sanctum)
✅ Validación completa en backend
✅ Contraseñas encriptadas automáticamente
✅ Transacciones ACID (rollback si error)
✅ Sin vulnerabilidades SQL (Eloquent ORM)
✅ Límite de tamaño 5MB
✅ Solo formatos CSV/TXT

---

## 📊 Respuesta API

### Éxito Total
```json
{
  "status": "success",
  "message": "Se han importado 10 usuarios correctamente.",
  "data": {
    "created": 10,
    "failed": 0,
    "errors": []
  }
}
```

### Éxito Parcial
```json
{
  "status": "success",
  "message": "Se han importado 8 usuarios correctamente.",
  "data": {
    "created": 8,
    "failed": 2,
    "errors": [
      "Fila 3: El email 'juan@test.com' ya está registrado.",
      "Fila 7: El teléfono '123456' debe tener 9 dígitos."
    ]
  }
}
```

---

## 🎯 Características Especiales

### Por Tipo de Importación

**Usuarios:**
- Genera IDs automáticos por rango (10000-20000-30000-40000)
- Encripta contraseñas

**Empresas:**
- Usa CIF como primary key
- Validación de formato de teléfono

**Cursos:**
- Rápido y simple
- Permite duplicados en nombre (diferentes cursos)

**Alumnos:**
- Crea usuario + registro alumno en transacción
- Tipo siempre 'alumno'
- ID autogenerado en rango 20000+

**Profesorado:**
- Crea usuario + tutor O instructor según tipo
- Instructores pueden asignarse a empresa
- Tutores sin empresa obligatoria

---

## 📈 Historial de Importaciones

Todas las importaciones de usuarios se registran en tabla `user_imports`:
- `user_id` → Admin que hizo la importación
- `total_users` → Total procesados
- `successful_users` → Creados exitosamente
- `failed_users` → Con error
- `errors` → JSON con detalles
- `original_filename` → Nombre del archivo

---

## 🚀 Próximas Mejoras Posibles

- [ ] Soporte para Excel (.xlsx)
- [ ] Importación de relaciones (grados↔tutores)
- [ ] Vista de historial de importaciones
- [ ] Exportar usuarios existentes
- [ ] Búsqueda de duplicados antes de importar
- [ ] Importación de asignaturas/materias
- [ ] Importación de horarios

---

## 📞 Instalación Rápida

```bash
# Backend: Ejecutar migraciones
cd Backend
php artisan migrate

# Frontend: Instalar dependencias (si es necesario)
cd Frontend
npm install
```

Luego acceder normalmente a la app.

---

**Versión:** 2.0 (Expandida)
**Fecha:** Febrero 2026
**Estado:** ✅ Completa y funcional
