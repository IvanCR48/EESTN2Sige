# :mortar_board: Sistema Administrativo - E.E.S.T. N°2 "Educación y Trabajo"
Sistema integral de gestión educativa para la Escuela de Educación Secundaria Técnica N°2.

![Version](https://img.shields.io/badge/version-2.0.0-blue)
![PHP](https://img.shields.io/badge/PHP-8.1+-purple)
![MySQL](https://img.shields.io/badge/MySQL-8.0+-orange)
![License](https://img.shields.io/badge/license-MIT-green)

---
## :clipboard: Índice
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
## :sparkles: Características
### :busts_in_silhouette: Gestión de Personas
- **Estudiantes**: Registro completo, ficha individual, historial académico
- **Profesores**: Perfiles docentes, asignación de materias
- **Usuarios**: Sistema de roles y permisos (Admin, Directivo, Preceptor, Profesor)

### :books: Gestión Académica
- **Cursos**: Organización por año, división y turno
- **Materias**: Gestión de materias por especialidad
- **Notas**: Sistema de 2 cuatrimestres con avances y promedio final
- **Llamados de Atención**: Registro disciplinario

### :hammer_and_wrench: Funcionalidades Avanzadas
- **Boletines**: Impresión de boletines profesionales
- **Dashboard**: Estadísticas y análisis en tiempo real
- **Responsive**: Diseño adaptativo para móvil, tablet y desktop
- **Documentación**: Sistema completo de ayuda integrado

### :lock: Seguridad
- Autenticación multifactor (MFA)
- Protección CSRF
- Headers de seguridad
- Honeypots para detectar atacantes
- Auditoría de acciones
- Sesiones seguras

---
## :computer: Requisitos
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
## :rocket: Instalación
### Opción 1: Instalación Automática (Recomendado)
#### Windows (XAMPP)
```batch
# Ejecutar el script de instalación automática
install.bat
