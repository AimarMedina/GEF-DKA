# 🚀 Guía Rápida: Importación de Usuarios

## ✅ Checklist de Instalación

### Backend
- [ ] Correr migración: `php artisan migrate`
- [ ] Verificar rutas en `routes/api.php`
- [ ] Controller `UserImportController.php` creado
- [ ] Modelo `UserImport.php` creado

### Frontend
- [ ] Componente `ImportUsuariosModal.vue` creado en `components/`
- [ ] Vista `UsersView.vue` actualizada
- [ ] Button "Importar CSV" visible en panel

---

## 📋 Flujo de Uso

```
┌─────────────────────────────────────────┐
│   Admin hace clic en "Importar CSV"     │
└────────────────┬────────────────────────┘
                 │
         ┌───────▼────────┐
         │   Modal Abre   │
         └───────┬────────┘
                 │
    ┌────────────┴────────────┐
    │                         │
    ▼                         ▼
┌──────────────────┐  ┌──────────────────┐
│ Descargar CSV    │  │ Subir CSV Propio │
│ (Plantilla)      │  │                  │
└──────────────────┘  └────────┬─────────┘
                               │
                      ┌────────▼────────┐
                      │ Validar archivo │
                      │ en Frontend      │
                      └────────┬────────┘
                               │
                      ┌────────▼────────┐
                      │ POST /users/     │
                      │ import           │
                      │ (Backend)        │
                      └────────┬────────┘
                               │
        ┌──────────────────────┼──────────────────────┐
        │                      │                      │
        ▼                      ▼                      ▼
    ✓ Válido              ⚠️ Errores            ✗ Fallo Total
    Insertar BD           En algunas filas       Error de archivo
        │                      │                      │
        │                      │                      │
        ▼                      ▼                      ▼
    Crear registro      Importar válidas      Mostrar error
    en user_imports     + Listar errores      general
        │                      │                      │
        └──────────────────────┼──────────────────────┘
                               │
                      ┌────────▼────────┐
                      │ Mostrar resultado│
                      │ (Modal)          │
                      └──────────────────┘
```

---

## 📊 Estructura del CSV

Ejemplo de archivo CSV correcto:

**plantilla_usuarios.csv**
```csv
nombre,apellidos,email,n_tel,password,tipo
Juan,García López,juan.garcia@centro.local,600123456,password123,alumno
María,Rodríguez Pérez,maria.rodriguez@centro.local,600234567,password456,tutor
Carlos,López Martínez,carlos@empresa.local,610345678,password789,instructor
Marta,Sánchez Díaz,marta.sanchez@centro.local,650111222,admin2024,admin
```

---

## 🎯 Validaciones en Tiempo Real

**ANTES de enviar al servidor:**

```
✓ Archivo seleccionado
✓ Extensión .csv o .txt
✓ Tamaño < 5MB

DESPUÉS en servidor:

✓ Encabezados correctos
✓ Email válido y único
✓ Teléfono 9 dígitos único
✓ Tipo válido
✓ Campos no vacíos
✓ Datos no duplicados
```

---

## 🔐 Seguridad

| Aspecto | Implementación |
|---------|----------------|
| **Autenticación** | Requiere token API (Sanctum) |
| **Encriptación de contraseña** | bcrypt automático |
| **Inyección SQL** | Eloquent ORM (no raw SQL) |
| **Limitaciones** | Máx 5MB por archivo |
| **Formato** | Solo CSV/TXT |

---

## 📝 Respuestas del API

### ✅ Éxito Total
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

### ⚠️ Éxito Parcial
```json
{
  "status": "success",
  "message": "Se han importado 8 usuarios correctamente.",
  "data": {
    "created": 8,
    "failed": 2,
    "errors": [
      "Fila 3: El email 'juan@test.com' ya está registrado.",
      "Fila 7: El teléfono '123456789' debe tener exactamente 9 dígitos."
    ]
  }
}
```

### ❌ Error
```json
{
  "status": "error",
  "message": "El archivo CSV está vacío o mal formateado."
}
```

---

## 🧪 Prueba Rápida

### Crear archivo de prueba: `test_users.csv`
```csv
nombre,apellidos,email,n_tel,password,tipo
Test,Usuario,test@example.com,600000001,test123,alumno
Test2,Usuario2,test2@example.com,600000002,test456,tutor
```

### Pasos:
1. Login como admin
2. Ir a Panel > Usuarios
3. Clic en "Importar CSV"
4. Descargar plantilla (opcional)
5. Cargar `test_users.csv`
6. Ver resultados

---

## 🐛 Troubleshooting

| Problema | Solución |
|----------|----------|
| *Modal no aparece* | Revisa que ImportUsuariosModal está en UsersView |
| *Error 401* | Asegúrate de estar logueado |
| *Error 404* | Migración no ejecutada: `php artisan migrate` |
| *Email duplicado* | El email ya existe en BD |
| *Teléfono inválido* | Debe ser 9 dígitos: `600123456` |
| *Tipo no válido* | Usa: admin, alumno, tutor, instructor |
| *Archivo muy grande* | Máximo 5MB |

---

## 🔗 Endpoints

| Método | Ruta | Descripción |
|--------|------|-------------|
| GET | `/api/users/import/template` | Descargar plantilla CSV |
| POST | `/api/users/import` | Importar usuarios |

**Headers requeridos:**
```
Authorization: Bearer {token}
Content-Type: multipart/form-data (solo para POST)
```

---

## 📈 Casos de Uso Comunes

### 1️⃣ Importar alumnos de un grado
```csv
nombre,apellidos,email,n_tel,password,tipo
Alumno1,Pérez García,alumno1@centro.local,600000001,pass123,alumno
Alumno2,López Martínez,alumno2@centro.local,600000002,pass456,alumno
Alumno3,González Ruiz,alumno3@centro.local,600000003,pass789,alumno
```

### 2️⃣ Importar tutores
```csv
nombre,apellidos,email,n_tel,password,tipo
Tutor1,García López,tutor1@centro.local,600100001,pass123,tutor
Tutor2,Martínez Ruiz,tutor2@centro.local,600100002,pass456,tutor
```

### 3️⃣ Importar instructores
```csv
nombre,apellidos,email,n_tel,password,tipo
Instructor1,López García,instr1@empresa.local,610000001,pass123,instructor
Instructor2,Pérez Martínez,instr2@empresa.local,610000002,pass456,instructor
```

---

## 📞 Contacto / Soporte

- Revisar logs: `storage/logs/laravel.log`
- Verificar errores: Modal mostrará detalle por fila
- Framework: Laravel 12 + Vue 3
- API: RESTful con Sanctum
