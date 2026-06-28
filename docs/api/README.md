# 🔌 API Reference - Sistema Integral de Gestión Educativa

## 📋 Índice

- [Autenticación](#autenticación)
- [Estudiantes](#estudiantes)
- [Profesores](#profesores)
- [Cursos](#cursos)
- [Notas](#notas)
- [Llamados de Atención](#llamados-de-atención)
- [Horarios](#horarios)
- [Materias](#materias)
- [Especialidades](#especialidades)
- [Equipo Directivo](#equipo-directivo)
- [Cache y Paginación](#cache-y-paginación)
- [Errores](#errores)

## 🔐 Autenticación

### **POST /login.php**

Autentica un usuario en el sistema.

**Parámetros:**
```json
{
  "username": "string",
  "password": "string",
  "csrf_token": "string"
}
```

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "message": "Autenticación exitosa",
  "data": {
    "id": 1,
    "username": "admin",
    "nombre": "Administrador",
    "apellido": "Sistema",
    "email": "admin@eest2.edu.ar",
    "rol": "admin",
    "ultimo_acceso": "2024-01-15 10:30:00"
  }
}
```

**Respuesta de Error (400):**
```json
{
  "success": false,
  "error": "Usuario o contraseña incorrectos"
}
```

**Respuesta de Rate Limit (429):**
```json
{
  "success": false,
  "error": "Demasiados intentos de login. Intente nuevamente en 5 minutos",
  "rate_limited": true
}
```

### **GET /logout.php**

Cierra la sesión del usuario actual.

**Respuesta (200):**
```json
{
  "success": true,
  "message": "Sesión cerrada exitosamente"
}
```

## 👥 Estudiantes

### **GET /estudiantes.php**

Obtiene la lista de estudiantes con paginación.

**Parámetros de Query:**
- `page` (int): Número de página (default: 1)
- `page_size` (int): Elementos por página (default: 20, max: 100)
- `curso_id` (int): Filtrar por curso
- `search` (string): Búsqueda por nombre/apellido

**Respuesta (200):**
```json
{
  "success": true,
  "data": {
    "estudiantes": [
      {
        "id": 1,
        "dni": "12345678",
        "nombre": "Juan",
        "apellido": "Pérez",
        "nombreCompleto": "Juan Pérez",
        "fechaNacimiento": "2005-03-15",
        "edad": 19,
        "esMayorDeEdad": true,
        "grupoSanguineo": "O+",
        "obraSocial": "OSDE",
        "domicilio": "Av. Principal 123",
        "telefonoFijo": "011-1234-5678",
        "telefonoCelular": "11-9876-5432",
        "email": "juan.perez@email.com",
        "cursoId": 1,
        "tieneContacto": true,
        "activo": true
      }
    ],
    "pagination": {
      "current_page": 1,
      "page_size": 20,
      "total_items": 150,
      "total_pages": 8,
      "has_previous": false,
      "has_next": true,
      "start_item": 1,
      "end_item": 20
    }
  }
}
```

### **POST /estudiantes.php**

Crea un nuevo estudiante.

**Parámetros:**
```json
{
  "dni": "string",
  "nombre": "string",
  "apellido": "string",
  "fecha_nacimiento": "YYYY-MM-DD",
  "grupo_sanguineo": "string",
  "obra_social": "string",
  "domicilio": "string",
  "telefono_fijo": "string",
  "telefono_celular": "string",
  "email": "string",
  "curso_id": "integer",
  "csrf_token": "string"
}
```

**Respuesta Exitosa (201):**
```json
{
  "success": true,
  "message": "Estudiante creado exitosamente",
  "data": {
    "id": 1,
    "dni": "12345678",
    "nombre": "Juan",
    "apellido": "Pérez"
  }
}
```

### **PUT /estudiantes.php**

Actualiza un estudiante existente.

**Parámetros:**
```json
{
  "id": "integer",
  "dni": "string",
  "nombre": "string",
  "apellido": "string",
  "fecha_nacimiento": "YYYY-MM-DD",
  "grupo_sanguineo": "string",
  "obra_social": "string",
  "domicilio": "string",
  "telefono_fijo": "string",
  "telefono_celular": "string",
  "email": "string",
  "curso_id": "integer",
  "csrf_token": "string"
}
```

### **DELETE /estudiantes.php**

Elimina (soft delete) un estudiante.

**Parámetros:**
```json
{
  "id": "integer",
  "csrf_token": "string"
}
```

### **GET /estudiante_ficha.php?id={id}**

Obtiene la ficha completa de un estudiante.

**Respuesta (200):**
```json
{
  "success": true,
  "data": {
    "estudiante": {
      "id": 1,
      "dni": "12345678",
      "nombre": "Juan",
      "apellido": "Pérez",
      "fechaNacimiento": "2005-03-15",
      "edad": 19,
      "grupoSanguineo": "O+",
      "obraSocial": "OSDE",
      "domicilio": "Av. Principal 123",
      "telefonoFijo": "011-1234-5678",
      "telefonoCelular": "11-9876-5432",
      "email": "juan.perez@email.com",
      "cursoId": 1,
      "curso": {
        "id": 1,
        "nombre": "1° Año",
        "especialidad": "Informática"
      },
      "notas": [
        {
          "id": 1,
          "materia": "Matemática",
          "nota": 8,
          "fecha": "2024-01-15",
          "cuatrimestre": 1
        }
      ],
      "llamados": [
        {
          "id": 1,
          "tipo": "Amonestación",
          "descripcion": "Falta de respeto",
          "fecha": "2024-01-10",
          "estado": "Activo"
        }
      ]
    }
  }
}
```

## 👨‍🏫 Profesores

### **GET /profesores.php**

Obtiene la lista de profesores con paginación.

**Parámetros de Query:**
- `page` (int): Número de página
- `page_size` (int): Elementos por página
- `search` (string): Búsqueda por nombre/apellido

**Respuesta (200):**
```json
{
  "success": true,
  "data": {
    "profesores": [
      {
        "id": 1,
        "dni": "87654321",
        "nombre": "María",
        "apellido": "García",
        "nombreCompleto": "María García",
        "fechaNacimiento": "1980-05-20",
        "edad": 44,
        "domicilio": "Calle Secundaria 456",
        "telefonoFijo": "011-8765-4321",
        "telefonoCelular": "11-1234-5678",
        "email": "maria.garcia@eest2.edu.ar",
        "titulo": "Profesora en Matemática",
        "fechaIngreso": "2010-03-01",
        "activo": true
      }
    ],
    "pagination": {
      "current_page": 1,
      "page_size": 20,
      "total_items": 25,
      "total_pages": 2
    }
  }
}
```

### **GET /profesor_ficha.php?id={id}**

Obtiene la ficha completa de un profesor.

## 🎓 Cursos

### **GET /cursos.php**

Obtiene la lista de cursos.

**Respuesta (200):**
```json
{
  "success": true,
  "data": {
    "cursos": [
      {
        "id": 1,
        "nombre": "1° Año",
        "especialidad": "Informática",
        "turno": "Mañana",
        "capacidad": 30,
        "estudiantes_inscriptos": 25,
        "activo": true
      }
    ]
  }
}
```

## 📊 Notas

### **GET /notas.php**

Obtiene las notas con filtros.

**Parámetros de Query:**
- `estudiante_id` (int): Filtrar por estudiante
- `materia_id` (int): Filtrar por materia
- `cuatrimestre` (int): Filtrar por cuatrimestre
- `page` (int): Número de página

**Respuesta (200):**
```json
{
  "success": true,
  "data": {
    "notas": [
      {
        "id": 1,
        "estudiante": {
          "id": 1,
          "nombre": "Juan",
          "apellido": "Pérez"
        },
        "materia": {
          "id": 1,
          "nombre": "Matemática"
        },
        "nota": 8,
        "fecha": "2024-01-15",
        "cuatrimestre": 1,
        "observaciones": "Muy buen desempeño"
      }
    ],
    "pagination": {
      "current_page": 1,
      "total_pages": 5
    }
  }
}
```

### **POST /notas.php**

Crea una nueva nota.

**Parámetros:**
```json
{
  "estudiante_id": "integer",
  "materia_id": "integer",
  "nota": "integer",
  "cuatrimestre": "integer",
  "observaciones": "string",
  "csrf_token": "string"
}
```

## ⚠️ Llamados de Atención

### **GET /llamados.php**

Obtiene los llamados de atención.

**Parámetros de Query:**
- `estudiante_id` (int): Filtrar por estudiante
- `tipo` (string): Filtrar por tipo
- `estado` (string): Filtrar por estado
- `page` (int): Número de página

**Respuesta (200):**
```json
{
  "success": true,
  "data": {
    "llamados": [
      {
        "id": 1,
        "estudiante": {
          "id": 1,
          "nombre": "Juan",
          "apellido": "Pérez",
          "curso": "1° Año A"
        },
        "tipo": "Amonestación",
        "descripcion": "Falta de respeto hacia el profesor",
        "fecha": "2024-01-10",
        "estado": "Activo",
        "sancion": null,
        "observaciones": "Requiere seguimiento"
      }
    ],
    "pagination": {
      "current_page": 1,
      "total_pages": 3
    }
  }
}
```

### **POST /llamados.php**

Crea un nuevo llamado de atención.

**Parámetros:**
```json
{
  "estudiante_id": "integer",
  "tipo": "string",
  "descripcion": "string",
  "observaciones": "string",
  "csrf_token": "string"
}
```

## 🕐 Horarios

### **GET /horarios.php**

Obtiene los horarios de clases.

**Respuesta (200):**
```json
{
  "success": true,
  "data": {
    "horarios": [
      {
        "id": 1,
        "curso": "1° Año A",
        "materia": "Matemática",
        "profesor": "María García",
        "dia": "Lunes",
        "hora_inicio": "08:00",
        "hora_fin": "09:30",
        "aula": "Aula 101"
      }
    ]
  }
}
```

## 📚 Materias

### **GET /materias.php**

Obtiene la lista de materias.

**Respuesta (200):**
```json
{
  "success": true,
  "data": {
    "materias": [
      {
        "id": 1,
        "nombre": "Matemática",
        "codigo": "MAT001",
        "especialidad": "Informática",
        "horas_semanales": 6,
        "activo": true
      }
    ]
  }
}
```

## 🏢 Especialidades

### **GET /especialidades.php**

Obtiene las especialidades disponibles.

**Respuesta (200):**
```json
{
  "success": true,
  "data": {
    "especialidades": [
      {
        "id": 1,
        "nombre": "Informática",
        "descripcion": "Técnico en Informática",
        "duracion_anos": 6,
        "activo": true
      }
    ]
  }
}
```

## 👔 Equipo Directivo

### **GET /equipo.php**

Obtiene el equipo directivo.

**Respuesta (200):**
```json
{
  "success": true,
  "data": {
    "equipo": [
      {
        "id": 1,
        "nombre": "Carlos",
        "apellido": "Rodríguez",
        "cargo": "Director",
        "email": "director@eest2.edu.ar",
        "telefono": "011-1111-2222",
        "activo": true
      }
    ]
  }
}
```

## ⚡ Cache y Paginación

### **Cache**

El sistema implementa un cache híbrido (memoria + base de datos) para optimizar el rendimiento:

- **TTL por defecto**: 1 hora
- **Cache de autenticación**: 30 minutos
- **Cache de estadísticas**: 10 minutos
- **Cache de listados**: 5 minutos

### **Paginación**

Todos los endpoints de listado soportan paginación:

- **Página por defecto**: 1
- **Tamaño por defecto**: 20 elementos
- **Tamaño máximo**: 100 elementos

**Parámetros estándar:**
- `page`: Número de página
- `page_size`: Elementos por página

**Respuesta de paginación:**
```json
{
  "pagination": {
    "current_page": 1,
    "page_size": 20,
    "total_items": 150,
    "total_pages": 8,
    "has_previous": false,
    "has_next": true,
    "previous_page": null,
    "next_page": 2,
    "start_item": 1,
    "end_item": 20
  }
}
```

## ❌ Errores

### **Códigos de Estado HTTP**

- **200 OK**: Operación exitosa
- **201 Created**: Recurso creado exitosamente
- **400 Bad Request**: Error en los parámetros
- **401 Unauthorized**: No autenticado
- **403 Forbidden**: Sin permisos
- **404 Not Found**: Recurso no encontrado
- **422 Unprocessable Entity**: Error de validación
- **429 Too Many Requests**: Rate limit excedido
- **500 Internal Server Error**: Error interno

### **Formato de Error**

```json
{
  "success": false,
  "error": "Descripción del error",
  "code": "ERROR_CODE",
  "details": {
    "field": "Campo específico con error",
    "message": "Mensaje detallado"
  }
}
```

### **Errores Comunes**

| Código | Descripción |
|--------|-------------|
| `VALIDATION_ERROR` | Error de validación de datos |
| `DUPLICATE_DNI` | DNI ya existe |
| `INVALID_EMAIL` | Email inválido |
| `INVALID_DATE` | Fecha inválida |
| `INSUFFICIENT_PERMISSIONS` | Permisos insuficientes |
| `RESOURCE_NOT_FOUND` | Recurso no encontrado |
| `RATE_LIMIT_EXCEEDED` | Límite de requests excedido |
| `CSRF_TOKEN_INVALID` | Token CSRF inválido |

## 🔒 Seguridad

### **Autenticación**

- **CSRF Protection**: Todos los formularios requieren token CSRF
- **Rate Limiting**: 5 intentos de login por IP cada 5 minutos
- **Session Management**: Sesiones seguras con timeout
- **Password Hashing**: Argon2ID para hash de contraseñas

### **Autorización**

- **Role-based Access Control**: Control de acceso basado en roles
- **Permission System**: Permisos granulares por funcionalidad
- **Audit Logging**: Registro de todas las acciones críticas

### **Headers de Seguridad**

```
X-Frame-Options: DENY
X-Content-Type-Options: nosniff
X-XSS-Protection: 1; mode=block
Strict-Transport-Security: max-age=31536000
Content-Security-Policy: default-src 'self'
```

---

*Documentación API v2.0.0 - Sistema Integral de Gestión Educativa*
