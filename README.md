# 🎓 Sistema Administrativo - E.E.S.T. N°2 "Educación y Trabajo"

Sistema integral de gestión educativa para la Escuela de Educación Secundaria Técnica N°2.

![Version](https://img.shields.io/badge/version-2.0.0-blue)
![PHP](https://img.shields.io/badge/PHP-8.1+-purple)
![MySQL](https://img.shields.io/badge/MySQL-8.0+-orange)
![License](https://img.shields.io/badge/license-MIT-green)

---

## 📋 Índice

- [Características](#-características)
- [Requisitos](#-requisitos)
- [Instalación](#-instalación)
- [Estructura del Proyecto](#-estructura-del-proyecto)
- [Uso](#-uso)
- [Documentación](#-documentación)
- [Despliegue](#-despliegue)
- [Seguridad](#-seguridad)
- [Contribuir](#-contribuir)
- [Licencia](#-licencia)

---

## ✨ Características

### 👥 Gestión de Personas
- **Estudiantes**: Registro completo, ficha individual, historial académico
- **Profesores**: Perfiles docentes, asignación de materias
- **Usuarios**: Sistema de roles y permisos (Admin, Directivo, Preceptor, Profesor)

### 📚 Gestión Académica
- **Cursos**: Organización por año, división y turno
- **Materias**: Gestión de materias por especialidad
- **Notas**: Sistema de 2 cuatrimestres con avances y promedio final
- **Llamados de Atención**: Registro disciplinario

### 🛠️ Funcionalidades Avanzadas
- **Boletines**: Impresión de boletines profesionales
- **Dashboard**: Estadísticas y análisis en tiempo real
- **Responsive**: Diseño adaptativo para móvil, tablet y desktop
- **Documentación**: Sistema completo de ayuda integrado

### 🔒 Seguridad
- Autenticación multifactor (MFA)
- Protección CSRF
- Headers de seguridad
- Honeypots para detectar atacantes
- Auditoría de acciones
- Sesiones seguras

---

## 💻 Requisitos

### Requisitos Mínimos
- **PHP**: 8.1 o superior
- **MySQL**: 8.0 o superior
- **Apache/Nginx**: Con mod_rewrite
- **Composer**: Para gestión de dependencias
- **Memoria**: 512MB RAM mínimo

### Requisitos Recomendados
- **PHP**: 8.2+
- **MySQL**: 8.0+ con InnoDB
- **Servidor**: Nginx con PHP-FPM
- **Memoria**: 2GB RAM
- **Docker**: Para despliegue containerizado

---

## 🚀 Instalación

### Opción 1: Instalación Automática (Recomendado)

#### Windows (XAMPP)
```batch
# Ejecutar el script de instalación automática
install.bat
```

#### Linux/Mac
```bash
# Hacer ejecutable y ejecutar
chmod +x install.sh
./install.sh
```

### Opción 2: Instalación Manual

#### Configuración Manual en XAMPP/LAMP

```bash
# 1. Copiar archivos al directorio web
cp -r sistema-admin-eest2 /xampp/htdocs/

# 2. Configurar variables de entorno
cp env.example .env
# Editar .env con tus credenciales de base de datos

# 3. Instalar dependencias
composer install --no-dev --optimize-autoloader

# 4. Importar base de datos
mysql -u root -p < database/sistema_admin_eest2.sql

# 5. Acceder a http://localhost/sistema-admin-eest2
```

### Opción 3: Instalación con Docker (Avanzado)

```bash
# Clonar el repositorio
git clone https://github.com/tu-repo/sistema-admin-eest2.git
cd sistema-admin-eest2

# Cambiar al directorio de Docker
cd deployment/docker

# Iniciar con Docker Compose
docker-compose up -d

# El sistema estará disponible en http://localhost:8080
```

---

## 📁 Estructura del Proyecto

```
SistemaAdmin/
├── deployment/           # Archivos de despliegue
│   ├── docker/          # Configuración Docker
│   ├── cloud-configs/   # Configuraciones cloud (Fly.io, Railway, Render)
│   └── scripts/         # Scripts de despliegue y backup
│
├── config/              # Configuraciones del sistema
│   ├── php/            # Configuraciones de PHP
│   ├── nginx/          # Configuraciones de Nginx
│   ├── mysql/          # Configuraciones de MySQL
│   ├── database.php    # Conexión a base de datos
│   └── production.php  # Configuración de producción
│
├── docs/               # Documentación completa
│   ├── guides/        # Guías de usuario y admin
│   ├── security/      # Documentación de seguridad
│   ├── deployment/    # Guías de despliegue
│   ├── api/           # Documentación de API
│   └── architecture/  # Arquitectura del sistema
│
├── src/                # Código fuente PHP
│   ├── controllers/    # Controladores
│   ├── services/       # Lógica de negocio
│   ├── mappers/        # Mapeo de datos
│   ├── models/         # Modelos de dominio
│   ├── middleware/     # Middleware de seguridad
│   └── interfaces/     # Contratos e interfaces
│
├── public/             # Archivos públicos
│   ├── login.php      # Inicio de sesión
│   ├── errors/        # Páginas de error
│   └── honeypot/      # Trampas de seguridad
│
├── admin/              # Panel administrativo
├── api/                # API REST
├── database/           # Scripts de base de datos
├── includes/           # Archivos de inclusión
├── css/                # Estilos CSS
├── js/                 # JavaScript
├── img/                # Imágenes
├── logs/               # Logs del sistema
└── backups/            # Respaldos

# Páginas principales
├── index.php                  # Dashboard
├── estudiantes.php           # Gestión de estudiantes
├── profesores.php            # Gestión de profesores
├── cursos.php                # Gestión de cursos
├── notas.php                 # Gestión de notas
└── documentacion.php         # Hub de documentación
```

Para más detalles, consulta [ESTRUCTURA_PROYECTO.md](ESTRUCTURA_PROYECTO.md)

---

## 📖 Uso

### Usuarios por Defecto

```
👤 Administrador
Usuario: admin
Contraseña: admin123

👤 Directivo
Usuario: director
Contraseña: director123

👤 Preceptor
Usuario: preceptor
Contraseña: preceptor123
```

**⚠️ IMPORTANTE**: Cambiar las contraseñas después de la primera instalación.

### Acceso al Sistema

1. Navega a `http://localhost/sistema-admin-eest2` (o tu URL configurada)
2. Ingresa con uno de los usuarios por defecto
3. Completa la configuración inicial
4. Cambia las contraseñas por defecto

### Funcionalidades Principales

#### Gestión de Estudiantes
- **Nuevo Estudiante**: Botón "Nuevo Estudiante"
- **Editar**: Click en el nombre del estudiante
- **Ver Ficha**: Click en el icono de ver

#### Gestión de Notas
- **Cargar Nota Individual**: Formulario en notas.php
- **Boletín General**: Visualización de todos los estudiantes
- **Filtros**: Por curso, cuatrimestre, materia
- **Imprimir**: Boletín individual desde ficha del estudiante

---

## 📚 Documentación

### Documentación Integrada
Accede desde el login o dentro del sistema al hub de documentación con:
- Guía de Usuario
- Guía de Administrador  
- Documentación de Seguridad
- Guía de Instalación
- Documentación Técnica

### Documentación Externa
- [Guía de Usuario](docs/guides/USER_GUIDE.md)
- [Guía de Administrador](docs/guides/ADMIN_GUIDE.md)
- [Guía de Despliegue](docs/deployment/DESPLIEGUE_UNIFICADO.md)
- [Documentación de Seguridad](docs/security/SEGURIDAD_UNIFICADA.md)
- [Arquitectura del Sistema](docs/architecture/README.md)

---

## 🌐 Despliegue

### Docker Compose

```bash
cd deployment/docker

# Desarrollo
docker-compose up -d

# Producción
docker-compose -f docker-compose.prod.yml up -d
```

### Cloud Platforms

#### Fly.io
```bash
cd deployment/cloud-configs
fly deploy --config fly.toml
```

#### Railway
```bash
railway up
```

#### Render
```bash
# Conectar repositorio en Render.com
# Usar render.yaml para configuración automática
```

### Scripts de Despliegue

```bash
cd deployment/scripts

# Despliegue general
./deploy.sh

# Despliegue en cloud
./deploy-cloud.sh

# Backup
./backup.sh

# Restaurar
./restore.sh backup_file.sql
```

---

## 🔒 Seguridad

### Características de Seguridad Implementadas

✅ **Autenticación**
- Contraseñas hasheadas con bcrypt
- Autenticación multifactor (MFA) opcional
- Sesiones seguras con regeneración

✅ **Protección contra Ataques**
- Protección CSRF en todos los formularios
- Validación de entrada
- Sanitización de salida
- Rate limiting
- Honeypots para detectar bots

✅ **Headers de Seguridad**
- Content-Security-Policy
- X-Frame-Options
- X-Content-Type-Options
- Referrer-Policy

✅ **Auditoría**
- Logs de seguridad
- Logs de auditoría
- Registro de acciones críticas

### Configuración de Seguridad

Consulta [docs/security/SEGURIDAD_UNIFICADA.md](docs/security/SEGURIDAD_UNIFICADA.md) para:
- Configuración de MFA
- Hardening del sistema
- Mejores prácticas
- Checklist de seguridad

---

## 🤝 Contribuir

### Cómo Contribuir

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add: Amazing Feature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

### Estándares de Código

- **PSR-12** para código PHP
- **Comentarios** en español
- **Tests** para nuevas funcionalidades
- **Documentación** actualizada

### Reportar Bugs

Usa el sistema de Issues de GitHub con:
- Descripción clara del bug
- Pasos para reproducir
- Comportamiento esperado vs actual
- Screenshots si es aplicable

---

## 📄 Licencia

Este proyecto está bajo la Licencia MIT - ver [LICENSE](LICENSE) para más detalles.

---

## 👥 Equipo

Desarrollado para la **E.E.S.T. N°2 "Educación y Trabajo"**

### Contacto

- 📧 Email: tecnica2mardel@gmail.com
- 🌐 Web: https://eest2.edu.ar
- 📱 Teléfono: 0223 496-4151

---

## 📝 Changelog

### Versión 2.1.1 (Junio 2026) - "Rediseño de Asistencia & Refactorización de Arquitectura"
- 📱 **Interfaz móvil táctil (App-like UX)** para la toma de asistencia y panel deslizable inferior (Bottom Sheet) responsivo.
- ⚙️ **Refactorización Clean Architecture / OOP:** Migración de la lógica inline de Gestión de Usuarios y procesamiento de Asistencias a controladores (`Controllers`), mapeadores (`Mappers`) y modelos de dominio (`Models`) desacoplados.
- 📐 **Motor de Reglas Académicas Ponderado:** Adaptación de las fórmulas de asistencia e inasistencia según el Régimen Oficial de la Pcia. de Buenos Aires (Tardanza = 0.25, Media falta = 0.50) con soporte para acumulados decimales en los boletines.
- 🔒 **Endurecimiento de Seguridad (RBAC):** Migración de controles rígidos de rol (`hasRole('admin')`) a permisos granulares (`can('permission_slug')`).
- 🐛 Corrección de sintaxis y optimización de consultas SQL.

### Versión 2.1.0 (2025) - "Documentación Completa y Estabilización"
- 📚 **Hub de Documentación:** Hub integrado interactivo en formato web con guías detalladas para administradores, desarrolladores y usuarios finales.
- 🛠️ **Herramientas de Monitoreo:** Panel de estado del sistema (CPU, memoria, disco y estado de BD) en tiempo real.
- 💾 **Backups Automatizados:** Módulo administrativo para realizar y restaurar copias de seguridad de BD + archivos con firma HMAC.

### Versión 2.0.0 (2024) - "Seguridad Avanzada e Integridad"
- 🔒 **Autenticación de Dos Factores (MFA):** Integración con Google Authenticator (TOTP) y códigos de respaldo.
- 🛡️ **Prevención OWASP:** Middleware de seguridad reforzado (prevención SQLi, protección CSRF extendida y headers HTTP seguros).
- ♻️ Reorganización del proyecto bajo estructura estándar PSR-4 y autoloader orientado a objetos.
- ✨ Mejoras de responsive design en dashboards administrativos.

### Versión 1.0.0 (2023) - "Primera versión estable"
- 🎉 Primera versión estable lista para producción.
- 👥 Gestión de estudiantes, profesores y cursos.
- 📚 Carga de notas y generación de boletines iniciales.
- 🔒 Sistema de sesión y seguridad básico.

---

## 🙏 Agradecimientos

- A todo el equipo docente y administrativo de la EEST N°2
- A la comunidad de código abierto por las librerías utilizadas
- A todos los contribuidores del proyecto

---

<div align="center">

**Sistema Admin EEST N°2**  
*Formando Futuros Profesionales* 🎓

[![Made with Love](https://img.shields.io/badge/Made%20with-❤️-red.svg)](https://github.com/tu-repo/sistema-admin-eest2)

</div>

