-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 25-06-2026 a las 21:53:07
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `sistema_admin_eest2`
--

DELIMITER $$
--
-- Procedimientos
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_limpiar_cache_expirado` ()   BEGIN
    DELETE FROM cache_data 
    WHERE expira_en IS NOT NULL 
    AND expira_en < NOW();
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_limpiar_logs_antiguos` (IN `dias_retencion` INT)   BEGIN
    DELETE FROM logs_errores 
    WHERE creado_en < DATE_SUB(NOW(), INTERVAL dias_retencion DAY);
    
    DELETE FROM logs_eventos 
    WHERE creado_en < DATE_SUB(NOW(), INTERVAL dias_retencion DAY);
    
    DELETE FROM logs_seguridad_avanzados 
    WHERE creado_en < DATE_SUB(NOW(), INTERVAL dias_retencion DAY);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_limpiar_sesiones_expiradas` ()   BEGIN
    DELETE FROM sesiones_usuarios 
    WHERE activa = 1 
    AND ultima_actividad < DATE_SUB(NOW(), INTERVAL 24 HOUR);
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `archivos_subidos`
--

CREATE TABLE `archivos_subidos` (
  `id` int(11) NOT NULL,
  `nombre_original` varchar(255) NOT NULL,
  `nombre_seguro` varchar(255) NOT NULL,
  `ruta` varchar(500) NOT NULL,
  `tamaño` int(11) NOT NULL,
  `tipo_mime` varchar(100) NOT NULL,
  `categoria` varchar(50) NOT NULL,
  `subido_por` int(11) DEFAULT NULL,
  `subido_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `eliminado` tinyint(1) DEFAULT 0,
  `eliminado_en` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `archivos_subidos`
--


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asistencia_periodos`
--

CREATE TABLE `asistencia_periodos` (
  `id` int(11) NOT NULL,
  `anio` year(4) NOT NULL,
  `nombre` varchar(100) NOT NULL COMMENT 'Ej: Trimestre 1, Ciclo completo',
  `fecha_desde` date NOT NULL,
  `fecha_hasta` date NOT NULL,
  `cerrado` tinyint(1) NOT NULL DEFAULT 0,
  `cerrado_por` int(11) DEFAULT NULL,
  `cerrado_en` timestamp NULL DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `asistencia_periodos`
--

INSERT INTO `asistencia_periodos` (`id`, `anio`, `nombre`, `fecha_desde`, `fecha_hasta`, `cerrado`, `cerrado_por`, `cerrado_en`, `creado_en`) VALUES
(1, '2026', 'Ciclo lectivo completo', '2026-03-01', '2026-12-20', 0, NULL, NULL, '2026-05-02 20:18:54');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asistencia_virtual`
--

CREATE TABLE `asistencia_virtual` (
  `id` int(11) NOT NULL,
  `estudiante_id` int(11) NOT NULL,
  `curso_id` int(11) NOT NULL,
  `materia_id` int(11) DEFAULT NULL,
  `grupo_taller` enum('A','B','C','D','E') DEFAULT NULL,
  `fecha` date NOT NULL,
  `estado` enum('Presente','Ausente','Tardanza','Media falta','Ausente justificado') NOT NULL DEFAULT 'Ausente',
  `observacion` varchar(500) DEFAULT NULL COMMENT 'Nota o comentario del docente para este registro',
  `adjunto` varchar(500) DEFAULT NULL COMMENT 'Ruta relativa al archivo adjunto (foto/PDF del justificativo)',
  `registrado_por` int(11) NOT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `actualizado_en` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `asistencia_virtual`
--


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `backups_log`
--

CREATE TABLE `backups_log` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `tamaño` bigint(20) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  `tipo` enum('manual','automatico') DEFAULT 'manual',
  `cifrado` tinyint(1) DEFAULT 0,
  `usuario_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `backups_log`
--


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cache_configuraciones`
--

CREATE TABLE `cache_configuraciones` (
  `cache_key` varchar(255) NOT NULL,
  `cache_value` longtext DEFAULT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `cache_configuraciones`
--


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cache_data`
--

CREATE TABLE `cache_data` (
  `id` int(11) NOT NULL,
  `clave` varchar(255) NOT NULL,
  `valor` longtext NOT NULL,
  `tipo` varchar(50) DEFAULT 'string',
  `expira_en` timestamp NULL DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `actualizado_en` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuraciones_sistema`
--

CREATE TABLE `configuraciones_sistema` (
  `id` int(11) NOT NULL,
  `clave` varchar(100) NOT NULL,
  `valor` text NOT NULL,
  `descripcion` text DEFAULT NULL,
  `tipo` enum('string','integer','float','boolean','json','array') DEFAULT 'string',
  `categoria` varchar(50) DEFAULT 'general',
  `editable` tinyint(1) DEFAULT 1,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `actualizado_en` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `configuraciones_sistema`
--

INSERT INTO `configuraciones_sistema` (`id`, `clave`, `valor`, `descripcion`, `tipo`, `categoria`, `editable`, `creado_en`, `actualizado_en`) VALUES
(1, 'system_name', 'Sistema Administrativo E.E.S.T N°2', 'Nombre del sistema', 'string', 'general', 1, '2025-09-29 07:18:31', '2025-09-29 07:18:31'),
(2, 'system_version', '2.0.0', 'Versión del sistema', 'string', 'general', 1, '2025-09-29 07:18:31', '2025-09-29 07:18:31'),
(3, 'system_description', 'Sistema Integral de Gestión Educativa', 'Descripción del sistema', 'string', 'general', 1, '2025-09-29 07:18:31', '2025-09-29 07:18:31'),
(4, 'maintenance_mode', 'false', 'Modo de mantenimiento', 'boolean', 'general', 1, '2025-09-29 07:18:31', '2025-09-29 07:18:31'),
(5, 'debug_mode', 'false', 'Modo de depuración', 'boolean', 'general', 1, '2025-09-29 07:18:31', '2025-09-29 07:18:31'),
(6, 'session_timeout', '1800', 'Timeout de sesión en segundos', 'integer', 'session', 1, '2025-09-29 07:18:31', '2025-09-29 07:18:31'),
(7, 'session_regenerate_id', 'true', 'Regenerar ID de sesión', 'boolean', 'session', 1, '2025-09-29 07:18:31', '2025-09-29 07:18:31'),
(8, 'session_secure_cookies', 'true', 'Cookies seguras', 'boolean', 'session', 1, '2025-09-29 07:18:31', '2025-09-29 07:18:31'),
(9, 'max_login_attempts', '5', 'Máximo intentos de login', 'integer', 'security', 1, '2025-09-29 07:18:31', '2025-09-29 07:18:31'),
(10, 'lockout_duration', '900', 'Duración del bloqueo en segundos', 'integer', 'security', 1, '2025-09-29 07:18:31', '2025-09-29 07:18:31'),
(11, 'password_min_length', '8', 'Longitud mínima de contraseña', 'integer', 'security', 1, '2025-09-29 07:18:31', '2025-09-29 07:18:31'),
(12, 'mfa_required', 'false', 'MFA obligatorio', 'boolean', 'security', 1, '2025-09-29 07:18:31', '2025-09-29 07:18:31'),
(13, 'cache_enabled', 'true', 'Cache habilitado', 'boolean', 'cache', 1, '2025-09-29 07:18:31', '2025-09-29 07:18:31'),
(14, 'cache_ttl', '3600', 'TTL del cache en segundos', 'integer', 'cache', 1, '2025-09-29 07:18:31', '2025-09-29 07:18:31'),
(15, 'reports_retention_days', '30', 'Días de retención de reportes', 'integer', 'reports', 1, '2025-09-29 07:18:31', '2025-09-29 07:18:31'),
(16, 'max_report_size_mb', '50', 'Tamaño máximo de reportes en MB', 'integer', 'reports', 1, '2025-09-29 07:18:31', '2025-09-29 07:18:31'),
(17, 'mfa_required_roles', '[\"admin\",\"directivo\"]', NULL, 'string', 'general', 1, '2025-09-29 07:18:38', '2025-09-29 07:18:38'),
(18, 'captcha_enabled', '1', NULL, 'string', 'general', 1, '2025-09-29 07:18:38', '2025-09-29 07:18:38'),
(19, 'password_require_special', '1', NULL, 'string', 'general', 1, '2025-09-29 07:18:38', '2025-09-29 07:18:38'),
(20, 'password_require_numbers', '1', NULL, 'string', 'general', 1, '2025-09-29 07:18:38', '2025-09-29 07:18:38'),
(21, 'password_require_uppercase', '1', NULL, 'string', 'general', 1, '2025-09-29 07:18:38', '2025-09-29 07:18:38'),
(22, 'password_max_age_days', '90', NULL, 'string', 'general', 1, '2025-09-29 07:18:38', '2025-09-29 07:18:38'),
(23, 'log_retention_days', '90', NULL, 'string', 'general', 1, '2025-09-29 07:18:38', '2025-09-29 07:18:38'),
(24, 'mfa_backup_codes_count', '10', NULL, 'string', 'general', 1, '2025-09-29 07:18:38', '2025-09-29 07:18:38'),
(25, 'mfa_window_tolerance', '1', NULL, 'string', 'general', 1, '2025-09-29 07:18:38', '2025-09-29 07:18:38'),
(26, 'mfa_time_window', '30', NULL, 'string', 'general', 1, '2025-09-29 07:18:38', '2025-09-29 07:18:38'),
(27, 'captcha_expiration_time', '600', NULL, 'string', 'general', 1, '2025-09-29 07:18:38', '2025-09-29 07:18:38'),
(28, 'captcha_required_actions', '[\"login\",\"registro\",\"cambiar_password\"]', NULL, 'string', 'general', 1, '2025-09-29 07:18:38', '2025-09-29 07:18:38'),
(29, 'rate_limit_window_duration', '300', NULL, 'string', 'general', 1, '2025-09-29 07:18:38', '2025-09-29 07:18:38'),
(30, 'email_habilitado', 'true', 'Notificaciones por email habilitadas', 'boolean', 'notificaciones', 1, '2025-10-01 00:56:12', '2025-10-01 00:56:12'),
(31, 'slack_habilitado', 'false', 'Notificaciones por Slack habilitadas', 'boolean', 'notificaciones', 1, '2025-10-01 00:56:12', '2025-10-01 00:56:12'),
(32, 'sms_habilitado', 'false', 'Notificaciones por SMS habilitadas', 'boolean', 'notificaciones', 1, '2025-10-01 00:56:12', '2025-10-01 00:56:12'),
(33, 'smtp_host', 'localhost', 'Servidor SMTP para emails', 'string', 'notificaciones', 1, '2025-10-01 00:56:12', '2025-10-01 00:56:12'),
(34, 'smtp_port', '587', 'Puerto del servidor SMTP', 'integer', 'notificaciones', 1, '2025-10-01 00:56:12', '2025-10-01 00:56:12'),
(35, 'smtp_user', '', 'Usuario SMTP', 'string', 'notificaciones', 1, '2025-10-01 00:56:12', '2025-10-01 00:56:12'),
(36, 'smtp_pass', '', 'Contraseña SMTP', 'string', 'notificaciones', 1, '2025-10-01 00:56:12', '2025-10-01 00:56:12'),
(37, 'from_email', 'sistema@eest2.edu.ar', 'Email remitente', 'string', 'notificaciones', 1, '2025-10-01 00:56:12', '2025-10-01 00:56:12'),
(38, 'from_name', 'Sistema EEST2', 'Nombre del remitente', 'string', 'notificaciones', 1, '2025-10-01 00:56:12', '2025-10-01 00:56:12'),
(39, 'slack_webhook', '', 'URL del webhook de Slack', 'string', 'notificaciones', 1, '2025-10-01 00:56:12', '2025-10-01 00:56:12'),
(40, 'slack_channel', '#sistema-admin', 'Canal de Slack para alertas', 'string', 'notificaciones', 1, '2025-10-01 00:56:12', '2025-10-01 00:56:12'),
(41, 'slack_username', 'SistemaBot', 'Usuario bot de Slack', 'string', 'notificaciones', 1, '2025-10-01 00:56:12', '2025-10-01 00:56:12'),
(42, 'sms_api_key', '', 'API Key para servicio SMS', 'string', 'notificaciones', 1, '2025-10-01 00:56:12', '2025-10-01 00:56:12'),
(43, 'sms_provider', 'twilio', 'Proveedor de SMS', 'string', 'notificaciones', 1, '2025-10-01 00:56:12', '2025-10-01 00:56:12'),
(44, 'memory_threshold', '90', 'Umbral de memoria para alertas (%)', 'integer', 'alertas', 1, '2025-10-01 00:56:12', '2025-10-01 00:56:12'),
(45, 'disk_threshold', '90', 'Umbral de disco para alertas (%)', 'integer', 'alertas', 1, '2025-10-01 00:56:12', '2025-10-01 00:56:12'),
(46, 'failed_logins_threshold', '50', 'Umbral de logins fallidos para alertas', 'integer', 'alertas', 1, '2025-10-01 00:56:12', '2025-10-01 00:56:12'),
(47, 'cooldown_minutes', '5', 'Cooldown entre alertas (minutos)', 'integer', 'alertas', 1, '2025-10-01 00:56:12', '2025-10-01 00:56:12');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion_sistema`
--

CREATE TABLE `configuracion_sistema` (
  `id` int(11) NOT NULL,
  `clave` varchar(255) NOT NULL,
  `valor` text DEFAULT NULL,
  `tipo` enum('string','number','boolean','json') DEFAULT 'string',
  `categoria` varchar(100) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `modificado_por` int(11) DEFAULT NULL,
  `modificado_en` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `configuracion_sistema`
--

INSERT INTO `configuracion_sistema` (`id`, `clave`, `valor`, `tipo`, `categoria`, `descripcion`, `modificado_por`, `modificado_en`, `creado_en`) VALUES
(1, 'sistema.nombre', 'Sistema Administrativo E.E.S.T N°2', 'string', 'sistema', 'Nombre del sistema', 23, '2026-06-23 03:34:56', '2025-09-30 03:27:37'),
(2, 'sistema.timezone', 'America/Argentina/Buenos_Aires', 'string', 'sistema', 'Zona horaria del sistema', 23, '2026-06-23 03:34:56', '2025-09-30 03:27:37'),
(3, 'sistema.mantenimiento', '0', 'boolean', 'sistema', 'Modo mantenimiento activado', 23, '2026-06-23 03:34:56', '2025-09-30 03:27:37'),
(4, 'seguridad.max_intentos_login', '5', 'number', 'seguridad', 'Máximo de intentos de login fallidos', 23, '2026-06-23 03:34:56', '2025-09-30 03:27:37'),
(5, 'seguridad.tiempo_bloqueo', '30', 'number', 'seguridad', 'Tiempo de bloqueo en minutos', 23, '2026-06-23 03:34:56', '2025-09-30 03:27:37'),
(6, 'seguridad.sesion_duracion', '480', 'number', 'seguridad', 'Duración de sesión en minutos', 23, '2026-06-23 03:34:56', '2025-09-30 03:27:37'),
(7, 'seguridad.requiere_2fa', '0', 'boolean', 'seguridad', 'Requerir 2FA para todos los usuarios', 23, '2026-06-23 03:34:56', '2025-09-30 03:27:37'),
(8, 'seguridad.password_min_longitud', '8', 'number', 'seguridad', 'Longitud mínima de contraseña', 23, '2026-06-23 03:34:56', '2025-09-30 03:27:37'),
(9, 'backup.automatico', '0', 'boolean', 'backup', 'Backups automáticos activados', 23, '2026-06-23 03:34:56', '2025-09-30 03:27:37'),
(10, 'backup.frecuencia', 'diario', 'string', 'backup', 'Frecuencia de backups (diario, semanal)', 23, '2026-06-23 03:34:56', '2025-09-30 03:27:37'),
(11, 'backup.hora', '03:00', 'string', 'backup', 'Hora de ejecución de backup automático', 23, '2026-06-23 03:34:56', '2025-09-30 03:27:37'),
(12, 'backup.max_backups', '30', 'number', 'backup', 'Número máximo de backups a mantener', 23, '2026-06-23 03:34:56', '2025-09-30 03:27:37'),
(13, 'notificaciones.email_activo', '0', 'boolean', 'notificaciones', 'Notificaciones por email activadas', NULL, '2025-09-30 03:27:37', '2025-09-30 03:27:37'),
(14, 'notificaciones.email_admin', 'admin@eest2.edu.ar', 'string', 'notificaciones', 'Email del administrador', NULL, '2025-09-30 03:27:37', '2025-09-30 03:27:37'),
(15, 'rendimiento.cache_activo', '1', 'boolean', 'rendimiento', 'Sistema de caché activado', NULL, '2025-09-30 03:27:37', '2025-09-30 03:27:37'),
(16, 'rendimiento.cache_duracion', '3600', 'number', 'rendimiento', 'Duración del caché en segundos', NULL, '2025-09-30 03:27:37', '2025-09-30 03:27:37'),
(17, 'rendimiento.logs_nivel', 'INFO', 'string', 'rendimiento', 'Nivel de logging (DEBUG, INFO, WARNING, ERROR)', NULL, '2025-09-30 03:27:37', '2025-09-30 03:27:37'),
(18, 'academico.anio_lectivo', '2025', 'number', 'academico', 'Año lectivo actual', NULL, '2025-09-30 03:27:37', '2025-09-30 03:27:37'),
(19, 'academico.periodo_actual', '1', 'number', 'academico', 'Período académico actual', NULL, '2025-09-30 03:27:37', '2025-09-30 03:27:37'),
(20, 'backup.last_automatic_run', '2026-03-28 20:32:15', 'string', 'backup', 'Última ejecución de backup automático (ISO fecha/hora)', 23, '2026-06-23 03:34:56', '2026-03-28 23:25:03'),
(21, 'backup.cron_token', '138faf5c4c3687855867d1dc34bb5b12d790a53c08584d34', 'string', 'backup', 'Token secreto para URL de cron (backup automático)', 23, '2026-06-23 03:34:56', '2026-03-28 23:25:03'),
(22, 'queue.cron_token', '70a111cc387e3ef64c90c32ba32285a0a1f9fafaa178ade6', 'string', 'sistema', 'Token secreto para cron del worker de colas (jobs en segundo plano)', 23, '2026-06-23 03:34:56', '2026-05-03 18:44:07');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `contactos_emergencia`
--

CREATE TABLE `contactos_emergencia` (
  `id` int(11) NOT NULL,
  `estudiante_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `parentesco` varchar(50) DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `actualizado_en` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `contactos_emergencia`
--


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cursos`
--

CREATE TABLE `cursos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `anio` int(11) NOT NULL,
  `division` varchar(2) NOT NULL,
  `especialidad_id` int(11) DEFAULT NULL,
  `turno_id` int(11) DEFAULT NULL,
  `capacidad_maxima` int(11) DEFAULT 30,
  `activo` tinyint(1) DEFAULT 1,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `cursos`
--

INSERT INTO `cursos` (`id`, `nombre`, `anio`, `division`, `especialidad_id`, `turno_id`, `capacidad_maxima`, `activo`, `creado_en`) VALUES
(1, '1°1', 1, '1', NULL, 1, 35, 1, '2026-06-25 18:22:28'),
(2, '1°2', 1, '2', NULL, 2, 35, 1, '2026-06-25 18:22:28'),
(3, '2°1', 2, '1', NULL, 1, 35, 1, '2026-06-25 18:22:28'),
(4, '2°2', 2, '2', NULL, 2, 35, 1, '2026-06-25 18:22:28'),
(5, '3°1', 3, '1', NULL, 1, 35, 1, '2026-06-25 18:22:28'),
(6, '3°2', 3, '2', NULL, 2, 35, 1, '2026-06-25 18:22:28'),
(7, '4°1', 4, '1', 1, 1, 35, 1, '2026-06-25 18:22:28'),
(8, '5°1', 5, '1', 1, 1, 35, 1, '2026-06-25 18:22:28'),
(9, '6°1', 6, '1', 1, 2, 35, 1, '2026-06-25 18:22:28'),
(10, '7°1', 7, '1', 1, 2, 35, 1, '2026-06-25 18:22:28'),
(11, '4°2', 4, '2', 2, 1, 35, 1, '2026-06-25 18:22:28'),
(12, '5°2', 5, '2', 2, 1, 35, 1, '2026-06-25 18:22:28'),
(13, '6°2', 6, '2', 2, 2, 35, 1, '2026-06-25 18:22:28'),
(14, '7°2', 7, '2', 2, 2, 35, 1, '2026-06-25 18:22:28'),
(15, '4°3', 4, '3', 3, 1, 35, 1, '2026-06-25 18:22:28'),
(16, '5°3', 5, '3', 3, 2, 35, 1, '2026-06-25 18:22:28'),
(17, '6°3', 6, '3', 3, 3, 35, 1, '2026-06-25 18:22:28'),
(18, '7°3', 7, '3', 3, 3, 35, 1, '2026-06-25 18:22:28');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `equipo_directivo`
--

CREATE TABLE `equipo_directivo` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `curso_id` int(11) DEFAULT NULL,
  `apellido` varchar(100) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `cargo` varchar(100) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `equipo_directivo`
--


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `especialidades`
--

CREATE TABLE `especialidades` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `codigo` varchar(10) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `activa` tinyint(1) DEFAULT 1,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `especialidades`
--

INSERT INTO `especialidades` (`id`, `nombre`, `codigo`, `descripcion`, `activa`, `creado_en`) VALUES
(1, 'Técnico en Informática', 'INF', 'Especialización en programación y sistemas', 1, '2026-06-25 18:22:28'),
(2, 'Técnico en Electromecánica', 'EMC', 'Especialización en mecánica y electricidad', 1, '2026-06-25 18:22:28'),
(3, 'Técnico en Construcciones', 'CON', 'Especialización en construcción civil', 1, '2026-06-25 18:22:28');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estudiantes`
--

CREATE TABLE `estudiantes` (
  `id` int(11) NOT NULL,
  `dni` varchar(20) NOT NULL,
  `dni_responsable` varchar(20) DEFAULT NULL COMMENT 'DNI del responsable para portal familias',
  `apellido` varchar(100) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `grupo_sanguineo` varchar(5) DEFAULT NULL,
  `obra_social` varchar(100) DEFAULT NULL,
  `domicilio` varchar(255) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `curso_id` int(11) DEFAULT NULL,
  `grupo_taller` enum('A','B','C','D','E') DEFAULT NULL,
  `fecha_ingreso` date DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `actualizado_en` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `estudiantes`
--


--
-- Disparadores `estudiantes`
--
DELIMITER $$
CREATE TRIGGER `tr_estudiantes_updated` BEFORE UPDATE ON `estudiantes` FOR EACH ROW BEGIN
    SET NEW.actualizado_en = CURRENT_TIMESTAMP;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estudiante_materias_recursadas`
--

CREATE TABLE `estudiante_materias_recursadas` (
  `id` int(11) NOT NULL,
  `estudiante_id` int(11) NOT NULL,
  `materia_id` int(11) NOT NULL,
  `curso_id` int(11) NOT NULL,
  `school_year` int(11) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `horarios`
--

CREATE TABLE `horarios` (
  `id` int(11) NOT NULL,
  `curso_id` int(11) NOT NULL,
  `materia_id` int(11) NOT NULL,
  `profesor_id` int(11) DEFAULT NULL,
  `grupo_taller` enum('A','B','C','D','E') DEFAULT NULL,
  `dia_semana` enum('lunes','martes','miercoles','jueves','viernes','sabado') NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL,
  `aula` varchar(20) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `actualizado_en` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `horarios`
--


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `llamados_atencion`
--

CREATE TABLE `llamados_atencion` (
  `id` int(11) NOT NULL,
  `estudiante_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `motivo` text NOT NULL,
  `sancion` text DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `llamados_atencion`
--


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `logs_auditoria`
--

CREATE TABLE `logs_auditoria` (
  `id` int(11) NOT NULL,
  `timestamp` datetime NOT NULL,
  `accion` varchar(100) NOT NULL,
  `entidad` varchar(100) NOT NULL,
  `entidad_id` int(11) DEFAULT NULL,
  `ip` varchar(45) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `datos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`datos`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `logs_auditoria`
--


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `logs_errores`
--

CREATE TABLE `logs_errores` (
  `id` int(11) NOT NULL,
  `nivel` enum('DEBUG','INFO','WARNING','ERROR','CRITICAL') NOT NULL,
  `mensaje` text NOT NULL,
  `contexto` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`contexto`)),
  `archivo` varchar(255) DEFAULT NULL,
  `linea` int(11) DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `logs_eventos`
--

CREATE TABLE `logs_eventos` (
  `id` int(11) NOT NULL,
  `tipo_evento` varchar(50) NOT NULL,
  `descripcion` text NOT NULL,
  `datos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`datos`)),
  `usuario_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `logs_seguridad`
--

CREATE TABLE `logs_seguridad` (
  `id` int(11) NOT NULL,
  `timestamp` datetime NOT NULL,
  `tipo` varchar(50) NOT NULL,
  `descripcion` text NOT NULL,
  `ip` varchar(45) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `datos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`datos`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `logs_seguridad`
--


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `logs_seguridad_avanzados`
--

CREATE TABLE `logs_seguridad_avanzados` (
  `id` int(11) NOT NULL,
  `tipo_evento` varchar(50) NOT NULL,
  `severidad` enum('LOW','MEDIUM','HIGH','CRITICAL') NOT NULL,
  `descripcion` text NOT NULL,
  `ip_origen` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `datos_adicionales` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`datos_adicionales`)),
  `usuario_id` int(11) DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `materias`
--

CREATE TABLE `materias` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `codigo` varchar(20) DEFAULT NULL,
  `especialidad_id` int(11) DEFAULT NULL,
  `anio_materia` int(11) NOT NULL,
  `carga_horaria` int(11) DEFAULT NULL,
  `es_taller` tinyint(1) DEFAULT 0,
  `activa` tinyint(1) DEFAULT 1,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `materias`
--

INSERT INTO `materias` (`id`, `nombre`, `codigo`, `especialidad_id`, `anio_materia`, `carga_horaria`, `es_taller`, `activa`, `creado_en`) VALUES
(1, 'Matemática', 'MAT', NULL, 7, 4, 0, 1, '2026-06-25 18:22:28'),
(2, 'Lengua y Literatura', 'LEN', NULL, 5, 3, 0, 1, '2026-06-25 18:22:28'),
(3, 'Educación Física', 'EF', NULL, 5, 4, 0, 1, '2026-06-25 18:22:28'),
(4, 'Inglés', 'ING', NULL, 3, 2, 0, 1, '2026-06-25 18:22:28'),
(5, 'Historia', 'HIS', NULL, 5, 5, 0, 1, '2026-06-25 18:22:28'),
(6, 'Procedimientos Técnicos (Taller)', 'PT_TAL', NULL, 5, 5, 1, 1, '2026-06-25 18:22:28'),
(7, 'Sistemas Tecnológicos (Taller)', 'ST_TAL', NULL, 4, 6, 1, 1, '2026-06-25 18:22:28'),
(8, 'Lenguajes Tecnológicos', 'LT_TAL', NULL, 4, 3, 1, 1, '2026-06-25 18:22:28'),
(9, 'Programación Orientada a Objetos', 'POO', 1, 1, 5, 0, 1, '2026-06-25 18:22:28'),
(10, 'Bases de Datos', 'BD', 1, 2, 4, 0, 1, '2026-06-25 18:22:28'),
(11, 'Diseño de Aplicaciones Web', 'DAW', 1, 6, 2, 0, 1, '2026-06-25 18:22:28'),
(12, 'Redes y Seguridad', 'RED', 1, 1, 2, 0, 1, '2026-06-25 18:22:28'),
(13, 'Sistemas Operativos', 'SO', 1, 5, 5, 0, 1, '2026-06-25 18:22:28'),
(14, 'Electrotecnia General', 'ELT', 2, 2, 4, 0, 1, '2026-06-25 18:22:28'),
(15, 'Sistemas Mecánicos', 'MEC', 2, 5, 5, 0, 1, '2026-06-25 18:22:28'),
(16, 'Automatización y Control', 'AUT', 2, 6, 2, 0, 1, '2026-06-25 18:22:28'),
(17, 'Termodinámica', 'TER', 2, 4, 3, 0, 1, '2026-06-25 18:22:28'),
(18, 'Estructuras Civiles', 'EST', 3, 6, 2, 0, 1, '2026-06-25 18:22:28'),
(19, 'Materiales de Construcción', 'MCO', 3, 4, 5, 0, 1, '2026-06-25 18:22:28'),
(20, 'Topografía', 'TOP', 3, 5, 4, 0, 1, '2026-06-25 18:22:28'),
(21, 'Instalaciones Sanitarias', 'SAN', 3, 4, 4, 0, 1, '2026-06-25 18:22:28');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `materias_previas`
--

CREATE TABLE `materias_previas` (
  `id` int(11) NOT NULL,
  `estudiante_id` int(11) NOT NULL,
  `materia_id` int(11) NOT NULL,
  `anio_previo` int(11) NOT NULL,
  `estado` enum('pendiente','aprobada','reprobada') DEFAULT 'pendiente',
  `observaciones` text DEFAULT NULL,
  `mes_aprobacion` enum('Diciembre','Febrero','Marzo') DEFAULT NULL,
  `anio_aprobacion` int(11) DEFAULT NULL,
  `nota` int(11) DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `actualizado_en` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `materia_curso`
--

CREATE TABLE `materia_curso` (
  `id` int(11) NOT NULL,
  `materia_id` int(11) NOT NULL,
  `curso_id` int(11) NOT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `materia_curso`
--

INSERT INTO `materia_curso` (`id`, `materia_id`, `curso_id`, `activo`, `creado_en`) VALUES
(1, 1, 1, 1, '2026-06-25 18:22:28'),
(2, 2, 1, 1, '2026-06-25 18:22:28'),
(3, 3, 1, 1, '2026-06-25 18:22:28'),
(4, 4, 1, 1, '2026-06-25 18:22:28'),
(5, 5, 1, 1, '2026-06-25 18:22:28'),
(6, 6, 1, 1, '2026-06-25 18:22:28'),
(7, 7, 1, 1, '2026-06-25 18:22:28'),
(8, 8, 1, 1, '2026-06-25 18:22:28'),
(9, 1, 2, 1, '2026-06-25 18:22:28'),
(10, 2, 2, 1, '2026-06-25 18:22:28'),
(11, 3, 2, 1, '2026-06-25 18:22:28'),
(12, 4, 2, 1, '2026-06-25 18:22:28'),
(13, 5, 2, 1, '2026-06-25 18:22:28'),
(14, 6, 2, 1, '2026-06-25 18:22:28'),
(15, 7, 2, 1, '2026-06-25 18:22:28'),
(16, 8, 2, 1, '2026-06-25 18:22:28'),
(17, 1, 3, 1, '2026-06-25 18:22:28'),
(18, 2, 3, 1, '2026-06-25 18:22:28'),
(19, 3, 3, 1, '2026-06-25 18:22:28'),
(20, 4, 3, 1, '2026-06-25 18:22:28'),
(21, 5, 3, 1, '2026-06-25 18:22:28'),
(22, 6, 3, 1, '2026-06-25 18:22:28'),
(23, 7, 3, 1, '2026-06-25 18:22:28'),
(24, 8, 3, 1, '2026-06-25 18:22:28'),
(25, 1, 4, 1, '2026-06-25 18:22:28'),
(26, 2, 4, 1, '2026-06-25 18:22:28'),
(27, 3, 4, 1, '2026-06-25 18:22:28'),
(28, 4, 4, 1, '2026-06-25 18:22:28'),
(29, 5, 4, 1, '2026-06-25 18:22:28'),
(30, 6, 4, 1, '2026-06-25 18:22:28'),
(31, 7, 4, 1, '2026-06-25 18:22:28'),
(32, 8, 4, 1, '2026-06-25 18:22:28'),
(33, 1, 5, 1, '2026-06-25 18:22:28'),
(34, 2, 5, 1, '2026-06-25 18:22:28'),
(35, 3, 5, 1, '2026-06-25 18:22:28'),
(36, 4, 5, 1, '2026-06-25 18:22:28'),
(37, 5, 5, 1, '2026-06-25 18:22:28'),
(38, 6, 5, 1, '2026-06-25 18:22:28'),
(39, 7, 5, 1, '2026-06-25 18:22:28'),
(40, 8, 5, 1, '2026-06-25 18:22:28'),
(41, 1, 6, 1, '2026-06-25 18:22:28'),
(42, 2, 6, 1, '2026-06-25 18:22:28'),
(43, 3, 6, 1, '2026-06-25 18:22:28'),
(44, 4, 6, 1, '2026-06-25 18:22:28'),
(45, 5, 6, 1, '2026-06-25 18:22:28'),
(46, 6, 6, 1, '2026-06-25 18:22:28'),
(47, 7, 6, 1, '2026-06-25 18:22:28'),
(48, 8, 6, 1, '2026-06-25 18:22:28'),
(49, 1, 7, 1, '2026-06-25 18:22:28'),
(50, 2, 7, 1, '2026-06-25 18:22:28'),
(51, 3, 7, 1, '2026-06-25 18:22:28'),
(52, 4, 7, 1, '2026-06-25 18:22:28'),
(53, 5, 7, 1, '2026-06-25 18:22:28'),
(54, 9, 7, 1, '2026-06-25 18:22:28'),
(55, 10, 7, 1, '2026-06-25 18:22:28'),
(56, 11, 7, 1, '2026-06-25 18:22:28'),
(57, 12, 7, 1, '2026-06-25 18:22:28'),
(58, 13, 7, 1, '2026-06-25 18:22:28'),
(59, 1, 8, 1, '2026-06-25 18:22:28'),
(60, 2, 8, 1, '2026-06-25 18:22:28'),
(61, 3, 8, 1, '2026-06-25 18:22:28'),
(62, 4, 8, 1, '2026-06-25 18:22:28'),
(63, 5, 8, 1, '2026-06-25 18:22:28'),
(64, 9, 8, 1, '2026-06-25 18:22:28'),
(65, 10, 8, 1, '2026-06-25 18:22:28'),
(66, 11, 8, 1, '2026-06-25 18:22:28'),
(67, 12, 8, 1, '2026-06-25 18:22:28'),
(68, 13, 8, 1, '2026-06-25 18:22:28'),
(69, 1, 9, 1, '2026-06-25 18:22:28'),
(70, 2, 9, 1, '2026-06-25 18:22:28'),
(71, 3, 9, 1, '2026-06-25 18:22:28'),
(72, 4, 9, 1, '2026-06-25 18:22:28'),
(73, 5, 9, 1, '2026-06-25 18:22:28'),
(74, 9, 9, 1, '2026-06-25 18:22:28'),
(75, 10, 9, 1, '2026-06-25 18:22:28'),
(76, 11, 9, 1, '2026-06-25 18:22:28'),
(77, 12, 9, 1, '2026-06-25 18:22:28'),
(78, 13, 9, 1, '2026-06-25 18:22:28'),
(79, 1, 10, 1, '2026-06-25 18:22:28'),
(80, 2, 10, 1, '2026-06-25 18:22:28'),
(81, 3, 10, 1, '2026-06-25 18:22:28'),
(82, 4, 10, 1, '2026-06-25 18:22:28'),
(83, 5, 10, 1, '2026-06-25 18:22:28'),
(84, 9, 10, 1, '2026-06-25 18:22:28'),
(85, 10, 10, 1, '2026-06-25 18:22:28'),
(86, 11, 10, 1, '2026-06-25 18:22:28'),
(87, 12, 10, 1, '2026-06-25 18:22:28'),
(88, 13, 10, 1, '2026-06-25 18:22:28'),
(89, 1, 11, 1, '2026-06-25 18:22:28'),
(90, 2, 11, 1, '2026-06-25 18:22:28'),
(91, 3, 11, 1, '2026-06-25 18:22:28'),
(92, 4, 11, 1, '2026-06-25 18:22:28'),
(93, 5, 11, 1, '2026-06-25 18:22:28'),
(94, 14, 11, 1, '2026-06-25 18:22:28'),
(95, 15, 11, 1, '2026-06-25 18:22:28'),
(96, 16, 11, 1, '2026-06-25 18:22:28'),
(97, 17, 11, 1, '2026-06-25 18:22:28'),
(98, 1, 12, 1, '2026-06-25 18:22:28'),
(99, 2, 12, 1, '2026-06-25 18:22:28'),
(100, 3, 12, 1, '2026-06-25 18:22:28'),
(101, 4, 12, 1, '2026-06-25 18:22:28'),
(102, 5, 12, 1, '2026-06-25 18:22:28'),
(103, 14, 12, 1, '2026-06-25 18:22:28'),
(104, 15, 12, 1, '2026-06-25 18:22:28'),
(105, 16, 12, 1, '2026-06-25 18:22:28'),
(106, 17, 12, 1, '2026-06-25 18:22:28'),
(107, 1, 13, 1, '2026-06-25 18:22:28'),
(108, 2, 13, 1, '2026-06-25 18:22:28'),
(109, 3, 13, 1, '2026-06-25 18:22:28'),
(110, 4, 13, 1, '2026-06-25 18:22:28'),
(111, 5, 13, 1, '2026-06-25 18:22:28'),
(112, 14, 13, 1, '2026-06-25 18:22:28'),
(113, 15, 13, 1, '2026-06-25 18:22:28'),
(114, 16, 13, 1, '2026-06-25 18:22:28'),
(115, 17, 13, 1, '2026-06-25 18:22:28'),
(116, 1, 14, 1, '2026-06-25 18:22:28'),
(117, 2, 14, 1, '2026-06-25 18:22:28'),
(118, 3, 14, 1, '2026-06-25 18:22:28'),
(119, 4, 14, 1, '2026-06-25 18:22:28'),
(120, 5, 14, 1, '2026-06-25 18:22:28'),
(121, 14, 14, 1, '2026-06-25 18:22:28'),
(122, 15, 14, 1, '2026-06-25 18:22:28'),
(123, 16, 14, 1, '2026-06-25 18:22:28'),
(124, 17, 14, 1, '2026-06-25 18:22:28'),
(125, 1, 15, 1, '2026-06-25 18:22:28'),
(126, 2, 15, 1, '2026-06-25 18:22:28'),
(127, 3, 15, 1, '2026-06-25 18:22:28'),
(128, 4, 15, 1, '2026-06-25 18:22:28'),
(129, 5, 15, 1, '2026-06-25 18:22:28'),
(130, 18, 15, 1, '2026-06-25 18:22:28'),
(131, 19, 15, 1, '2026-06-25 18:22:28'),
(132, 20, 15, 1, '2026-06-25 18:22:28'),
(133, 21, 15, 1, '2026-06-25 18:22:28'),
(134, 1, 16, 1, '2026-06-25 18:22:28'),
(135, 2, 16, 1, '2026-06-25 18:22:28'),
(136, 3, 16, 1, '2026-06-25 18:22:28'),
(137, 4, 16, 1, '2026-06-25 18:22:28'),
(138, 5, 16, 1, '2026-06-25 18:22:28'),
(139, 18, 16, 1, '2026-06-25 18:22:28'),
(140, 19, 16, 1, '2026-06-25 18:22:28'),
(141, 20, 16, 1, '2026-06-25 18:22:28'),
(142, 21, 16, 1, '2026-06-25 18:22:28'),
(143, 1, 17, 1, '2026-06-25 18:22:28'),
(144, 2, 17, 1, '2026-06-25 18:22:28'),
(145, 3, 17, 1, '2026-06-25 18:22:28'),
(146, 4, 17, 1, '2026-06-25 18:22:28'),
(147, 5, 17, 1, '2026-06-25 18:22:28'),
(148, 18, 17, 1, '2026-06-25 18:22:28'),
(149, 19, 17, 1, '2026-06-25 18:22:28'),
(150, 20, 17, 1, '2026-06-25 18:22:28'),
(151, 21, 17, 1, '2026-06-25 18:22:28'),
(152, 1, 18, 1, '2026-06-25 18:22:28'),
(153, 2, 18, 1, '2026-06-25 18:22:28'),
(154, 3, 18, 1, '2026-06-25 18:22:28'),
(155, 4, 18, 1, '2026-06-25 18:22:28'),
(156, 5, 18, 1, '2026-06-25 18:22:28'),
(157, 18, 18, 1, '2026-06-25 18:22:28'),
(158, 19, 18, 1, '2026-06-25 18:22:28'),
(159, 20, 18, 1, '2026-06-25 18:22:28'),
(160, 21, 18, 1, '2026-06-25 18:22:28');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `metricas_sistema`
--

CREATE TABLE `metricas_sistema` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `valor` decimal(15,4) NOT NULL,
  `unidad` varchar(20) DEFAULT NULL,
  `categoria` varchar(50) DEFAULT 'general',
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notas`
--

CREATE TABLE `notas` (
  `id` int(11) NOT NULL,
  `estudiante_id` int(11) NOT NULL,
  `materia_id` int(11) NOT NULL,
  `profesor_id` int(11) DEFAULT NULL,
  `calificacion` decimal(4,2) NOT NULL,
  `bimestre` int(11) NOT NULL,
  `evaluation_context` enum('regular','intensification_first_semester','intensification_december','intensification_february_march') NOT NULL DEFAULT 'regular' COMMENT 'Origen de la calificación',
  `recovery_scope` enum('first_semester','second_semester','both') DEFAULT NULL COMMENT 'Alcance obligatorio en Dic/Feb-Mar; 1er sem intens.',
  `school_year` smallint(5) UNSIGNED NOT NULL COMMENT 'Año lectivo (ej. 2025 = ciclo 2025)',
  `tipo_evaluacion` enum('parcial','trabajo_practico','examen','otro') DEFAULT 'parcial',
  `fecha` date NOT NULL,
  `observaciones` text DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `actualizado_en` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `notas`
--


--
-- Disparadores `notas`
--
DELIMITER $$
CREATE TRIGGER `tr_notas_updated` BEFORE UPDATE ON `notas` FOR EACH ROW BEGIN
    SET NEW.actualizado_en = CURRENT_TIMESTAMP;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notas_avance`
--

CREATE TABLE `notas_avance` (
  `id` int(11) NOT NULL,
  `estudiante_id` int(11) NOT NULL,
  `materia_id` int(11) NOT NULL,
  `etapa` enum('avance1','avance2') NOT NULL,
  `valor` enum('TEA','TEP','TED') NOT NULL,
  `observaciones` varchar(255) DEFAULT NULL,
  `fecha` date NOT NULL DEFAULT curdate(),
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `actualizado_en` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones_enviadas`
--

CREATE TABLE `notificaciones_enviadas` (
  `id` int(11) NOT NULL,
  `tipo` varchar(50) NOT NULL,
  `mensaje` text NOT NULL,
  `canales` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`canales`)),
  `enviado` tinyint(1) DEFAULT 0,
  `error_mensaje` text DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `preceptor_curso`
--

CREATE TABLE `preceptor_curso` (
  `id` int(11) NOT NULL,
  `equipo_directivo_id` int(11) NOT NULL,
  `curso_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `preceptor_curso`
--


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `profesores`
--

CREATE TABLE `profesores` (
  `id` int(11) NOT NULL,
  `dni` varchar(20) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `domicilio` varchar(255) DEFAULT NULL,
  `telefono_fijo` varchar(20) DEFAULT NULL,
  `telefono_celular` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `titulo` varchar(100) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `especialidad_id` int(11) DEFAULT NULL,
  `fecha_ingreso` date DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `actualizado_en` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `profesores`
--


--
-- Disparadores `profesores`
--
DELIMITER $$
CREATE TRIGGER `tr_profesores_updated` BEFORE UPDATE ON `profesores` FOR EACH ROW BEGIN
    SET NEW.actualizado_en = CURRENT_TIMESTAMP;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `profesor_curso`
--

CREATE TABLE `profesor_curso` (
  `id` int(11) NOT NULL,
  `profesor_id` int(11) NOT NULL,
  `curso_id` int(11) NOT NULL,
  `anio_academico` int(4) NOT NULL DEFAULT year(curdate()),
  `activo` tinyint(1) DEFAULT 1,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `actualizado_en` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `profesor_curso`
--


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `profesor_materia`
--

CREATE TABLE `profesor_materia` (
  `id` int(11) NOT NULL,
  `profesor_id` int(11) NOT NULL,
  `materia_id` int(11) NOT NULL,
  `curso_id` int(11) NOT NULL,
  `anio_academico` int(11) NOT NULL,
  `grupo_taller` enum('A','B','C','D','E') DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `profesor_materia`
--


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rbac_permissions`
--

CREATE TABLE `rbac_permissions` (
  `id` int(10) UNSIGNED NOT NULL,
  `slug` varchar(96) NOT NULL,
  `label` varchar(191) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `rbac_permissions`
--

INSERT INTO `rbac_permissions` (`id`, `slug`, `label`) VALUES
(1, 'ver_estudiantes', 'Ver estudiantes'),
(2, 'ver_profesores', 'Ver profesores'),
(3, 'ver_cursos', 'Ver cursos'),
(4, 'ver_materias', 'Ver materias'),
(5, 'ver_especialidades', 'Ver especialidades'),
(6, 'ver_horarios', 'Ver horarios'),
(7, 'ver_llamados', 'Ver llamados de atención'),
(8, 'ver_notas', 'Ver notas'),
(9, 'ver_equipo', 'Ver equipo directivo'),
(10, 'modificar_estudiantes', 'Modificar estudiantes'),
(11, 'modificar_profesores', 'Modificar profesores'),
(12, 'modificar_cursos', 'Modificar cursos'),
(13, 'modificar_materias', 'Modificar materias'),
(14, 'modificar_especialidades', 'Modificar especialidades'),
(15, 'modificar_horarios', 'Modificar horarios'),
(16, 'modificar_llamados', 'Modificar llamados'),
(17, 'modificar_notas', 'Modificar notas'),
(18, 'modificar_equipo', 'Modificar equipo directivo'),
(19, 'crear_estudiantes', 'Crear estudiantes'),
(20, 'crear_profesores', 'Crear profesores'),
(21, 'crear_cursos', 'Crear cursos'),
(22, 'crear_materias', 'Crear materias'),
(23, 'crear_especialidades', 'Crear especialidades'),
(24, 'crear_horarios', 'Crear horarios'),
(25, 'crear_llamados', 'Crear llamados'),
(26, 'crear_notas', 'Crear notas'),
(27, 'crear_equipo', 'Crear equipo directivo'),
(28, 'eliminar_estudiantes', 'Eliminar estudiantes'),
(29, 'eliminar_profesores', 'Eliminar profesores'),
(30, 'eliminar_cursos', 'Eliminar cursos'),
(31, 'eliminar_materias', 'Eliminar materias'),
(32, 'eliminar_especialidades', 'Eliminar especialidades'),
(33, 'eliminar_horarios', 'Eliminar horarios'),
(34, 'eliminar_llamados', 'Eliminar llamados'),
(35, 'eliminar_notas', 'Eliminar notas'),
(36, 'eliminar_equipo', 'Eliminar equipo directivo'),
(37, 'gestionar_usuarios', 'Gestionar usuarios'),
(38, 'ver_reportes', 'Ver reportes'),
(39, 'exportar_datos', 'Exportar datos'),
(40, 'ver_mis_cursos', 'Ver mis cursos (docente)'),
(41, 'ver_mis_materias', 'Ver mis materias (docente)'),
(42, 'ver_mis_horarios', 'Ver mis horarios (docente)'),
(43, 'ver_asistencia', 'Ver asistencia');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rbac_role_permissions`
--

CREATE TABLE `rbac_role_permissions` (
  `role` varchar(32) NOT NULL,
  `permission_slug` varchar(96) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `rbac_role_permissions`
--

INSERT INTO `rbac_role_permissions` (`role`, `permission_slug`) VALUES
('directivo', 'crear_cursos'),
('directivo', 'crear_equipo'),
('directivo', 'crear_especialidades'),
('directivo', 'crear_estudiantes'),
('directivo', 'crear_horarios'),
('directivo', 'crear_llamados'),
('directivo', 'crear_materias'),
('directivo', 'crear_notas'),
('directivo', 'crear_profesores'),
('directivo', 'eliminar_cursos'),
('directivo', 'eliminar_equipo'),
('directivo', 'eliminar_especialidades'),
('directivo', 'eliminar_estudiantes'),
('directivo', 'eliminar_horarios'),
('directivo', 'eliminar_llamados'),
('directivo', 'eliminar_materias'),
('directivo', 'eliminar_notas'),
('directivo', 'eliminar_profesores'),
('directivo', 'exportar_datos'),
('directivo', 'gestionar_usuarios'),
('directivo', 'modificar_cursos'),
('directivo', 'modificar_equipo'),
('directivo', 'modificar_especialidades'),
('directivo', 'modificar_estudiantes'),
('directivo', 'modificar_horarios'),
('directivo', 'modificar_llamados'),
('directivo', 'modificar_materias'),
('directivo', 'modificar_notas'),
('directivo', 'modificar_profesores'),
('directivo', 'ver_cursos'),
('directivo', 'ver_equipo'),
('directivo', 'ver_especialidades'),
('directivo', 'ver_estudiantes'),
('directivo', 'ver_horarios'),
('directivo', 'ver_llamados'),
('directivo', 'ver_materias'),
('directivo', 'ver_notas'),
('directivo', 'ver_profesores'),
('directivo', 'ver_reportes'),
('preceptor', 'crear_llamados'),
('preceptor', 'modificar_estudiantes'),
('preceptor', 'modificar_llamados'),
('preceptor', 'ver_asistencia'),
('preceptor', 'ver_cursos'),
('preceptor', 'ver_estudiantes'),
('preceptor', 'ver_horarios'),
('preceptor', 'ver_llamados'),
('profesor', 'crear_notas'),
('profesor', 'modificar_notas'),
('profesor', 'ver_cursos'),
('profesor', 'ver_estudiantes'),
('profesor', 'ver_horarios'),
('profesor', 'ver_mis_cursos'),
('profesor', 'ver_mis_horarios'),
('profesor', 'ver_mis_materias'),
('profesor', 'ver_notas'),
('secretario', 'crear_estudiantes'),
('secretario', 'crear_profesores'),
('secretario', 'exportar_datos'),
('secretario', 'modificar_estudiantes'),
('secretario', 'modificar_profesores'),
('secretario', 'ver_cursos'),
('secretario', 'ver_especialidades'),
('secretario', 'ver_estudiantes'),
('secretario', 'ver_materias'),
('secretario', 'ver_profesores'),
('secretario', 'ver_reportes');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `responsables`
--

CREATE TABLE `responsables` (
  `id` int(11) NOT NULL,
  `estudiante_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `dni` varchar(20) DEFAULT NULL,
  `telefono_celular` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `parentesco` varchar(50) DEFAULT NULL,
  `es_contacto_emergencia` tinyint(1) DEFAULT 0,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `actualizado_en` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `responsables`
--


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `schema_migrations`
--

CREATE TABLE `schema_migrations` (
  `id` int(11) NOT NULL,
  `version` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `executed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `environment` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `school_year_milestones`
--

CREATE TABLE `school_year_milestones` (
  `id` int(10) UNSIGNED NOT NULL,
  `school_year` smallint(5) UNSIGNED NOT NULL COMMENT 'Año de inicio del ciclo lectivo (único)',
  `february_march_closure_date` date NOT NULL COMMENT 'Último día inclusive del período Feb/Mar (escudo activo hasta esta fecha)',
  `grade_correction_enabled` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Habilitacion manual de correccion de notas',
  `grade_correction_start_date` date DEFAULT NULL COMMENT 'Fecha de inicio del periodo de correcciones',
  `grade_correction_end_date` date DEFAULT NULL COMMENT 'Fecha de fin del periodo de correcciones',
  `notes` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Fechas de cierre por ciclo; inyectar en SubjectStatusService vía SchoolYearMilestoneService';

--
-- Volcado de datos para la tabla `school_year_milestones`
--

INSERT INTO `school_year_milestones` (`id`, `school_year`, `february_march_closure_date`, `grade_correction_enabled`, `grade_correction_start_date`, `grade_correction_end_date`, `notes`, `created_at`, `updated_at`) VALUES
(2, 2026, '2027-02-16', 0, NULL, NULL, NULL, '2026-05-04 00:04:49', '2026-06-23 03:25:22');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sesiones_usuarios`
--

CREATE TABLE `sesiones_usuarios` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `session_id` varchar(255) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `ultima_actividad` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `activa` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `sesiones_usuarios`
--


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `suplencias`
--

CREATE TABLE `suplencias` (
  `id` int(11) NOT NULL,
  `profesor_id` int(11) NOT NULL,
  `suplente_id` int(11) DEFAULT NULL,
  `materia_id` int(11) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date DEFAULT NULL,
  `motivo` varchar(255) NOT NULL,
  `fuera_servicio` tinyint(1) DEFAULT 0,
  `estado` enum('activa','finalizada','cancelada') DEFAULT 'activa',
  `usuario_id` int(11) DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `actualizado_en` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `suplentes`
--

CREATE TABLE `suplentes` (
  `id` int(11) NOT NULL,
  `dni` varchar(20) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `telefono_celular` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `especialidad` varchar(100) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `actualizado_en` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `suplentes`
--


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `system_background_exports`
--

CREATE TABLE `system_background_exports` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `usuario_id` int(10) UNSIGNED NOT NULL,
  `tipo_reporte` varchar(64) NOT NULL,
  `formato` varchar(16) NOT NULL,
  `filtros_json` text NOT NULL,
  `estado` varchar(20) NOT NULL DEFAULT 'pending',
  `archivo_nombre` varchar(255) DEFAULT NULL,
  `error` text DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `completed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `system_queue_jobs`
--

CREATE TABLE `system_queue_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(64) NOT NULL DEFAULT 'default',
  `job_class` varchar(255) NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`payload`)),
  `attempts` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `max_attempts` tinyint(3) UNSIGNED NOT NULL DEFAULT 3,
  `available_at` datetime NOT NULL,
  `reserved_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `last_error` text DEFAULT NULL,
  `failed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `turnos`
--

CREATE TABLE `turnos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `turnos`
--

INSERT INTO `turnos` (`id`, `nombre`, `hora_inicio`, `hora_fin`, `activo`, `creado_en`) VALUES
(1, 'Mañana', '08:00:00', '12:00:00', 1, '2026-06-25 18:22:28'),
(2, 'Tarde', '13:00:00', '17:00:00', 1, '2026-06-25 18:22:28'),
(3, 'Vespertino', '18:00:00', '22:00:00', 1, '2026-06-25 18:22:28');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `dni` varchar(60) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `must_change_password` tinyint(1) NOT NULL DEFAULT 1,
  `rol` enum('admin','directivo','profesor','preceptor','secretario') NOT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `ultimo_acceso` timestamp NULL DEFAULT NULL,
  `intentos_fallidos` int(11) DEFAULT 0,
  `bloqueado_hasta` timestamp NULL DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `actualizado_en` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `dni`, `apellido`, `nombre`, `email`, `telefono`, `password_hash`, `must_change_password`, `rol`, `activo`, `ultimo_acceso`, `intentos_fallidos`, `bloqueado_hasta`, `creado_en`, `actualizado_en`) VALUES
(22, 'Elivaanperejil#software', 'Admin', 'Usuario 1', NULL, NULL, '$argon2id$v=19$m=65536,t=4,p=1$aUFHZkhpdjBvaG53Tm8zLw$GNcfXHki6wf9QH2XJ6vE4clfli9FzDcnuLULi44OhkI', 1, 'admin', 1, NULL, 0, NULL, '2026-06-25 18:22:28', '2026-06-25 18:22:28'),
(23, 'alandesalojasistema#tripode', 'Admin', 'Usuario 2', NULL, NULL, '$argon2id$v=19$m=65536,t=4,p=3$TWVhSklWakFoc0dVU0xMSQ$FoaKhYFoCDpOBsA/H7DS0+8ha0lITetnmpGqQaDMfT8', 0, 'admin', 1, '2026-06-25 19:28:41', 0, NULL, '2026-06-25 18:22:28', '2026-06-25 19:28:41');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios_codigos_respaldo`
--

CREATE TABLE `usuarios_codigos_respaldo` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `codigo` varchar(10) NOT NULL,
  `usado` tinyint(1) DEFAULT 0,
  `usado_en` timestamp NULL DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios_mfa`
--

CREATE TABLE `usuarios_mfa` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `secreto` varchar(255) NOT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `actualizado_en` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios_mfa_temporal`
--

CREATE TABLE `usuarios_mfa_temporal` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `secreto` varchar(255) NOT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios_mfa_temporal`
--


-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_estudiantes_completos`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_estudiantes_completos` (
`id` int(11)
,`dni` varchar(20)
,`apellido` varchar(100)
,`nombre` varchar(100)
,`fecha_nacimiento` date
,`email` varchar(255)
,`telefono` varchar(20)
,`curso_nombre` varchar(100)
,`anio` int(11)
,`division` varchar(2)
,`especialidad_nombre` varchar(100)
,`activo` tinyint(1)
,`fecha_ingreso` date
,`creado_en` timestamp
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_notas_completas`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_notas_completas` (
`id` int(11)
,`calificacion` decimal(4,2)
,`bimestre` int(11)
,`tipo_evaluacion` enum('parcial','trabajo_practico','examen','otro')
,`fecha` date
,`estudiante_apellido` varchar(100)
,`estudiante_nombre` varchar(100)
,`estudiante_dni` varchar(20)
,`materia_nombre` varchar(100)
,`profesor_apellido` varchar(100)
,`profesor_nombre` varchar(100)
,`curso_nombre` varchar(100)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_profesores_completos`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_profesores_completos` (
`id` int(11)
,`dni` varchar(20)
,`apellido` varchar(100)
,`nombre` varchar(100)
,`email` varchar(255)
,`telefono` varchar(20)
,`especialidad_nombre` varchar(100)
,`activo` tinyint(1)
,`fecha_ingreso` date
,`creado_en` timestamp
);

-- --------------------------------------------------------

--
-- Estructura para la vista `v_estudiantes_completos`
--
DROP TABLE IF EXISTS `v_estudiantes_completos`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_estudiantes_completos`  AS SELECT `e`.`id` AS `id`, `e`.`dni` AS `dni`, `e`.`apellido` AS `apellido`, `e`.`nombre` AS `nombre`, `e`.`fecha_nacimiento` AS `fecha_nacimiento`, `e`.`email` AS `email`, `e`.`telefono` AS `telefono`, `c`.`nombre` AS `curso_nombre`, `c`.`anio` AS `anio`, `c`.`division` AS `division`, `esp`.`nombre` AS `especialidad_nombre`, `e`.`activo` AS `activo`, `e`.`fecha_ingreso` AS `fecha_ingreso`, `e`.`creado_en` AS `creado_en` FROM ((`estudiantes` `e` join `cursos` `c` on(`e`.`curso_id` = `c`.`id`)) left join `especialidades` `esp` on(`c`.`especialidad_id` = `esp`.`id`)) ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_notas_completas`
--
DROP TABLE IF EXISTS `v_notas_completas`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_notas_completas`  AS SELECT `n`.`id` AS `id`, `n`.`calificacion` AS `calificacion`, `n`.`bimestre` AS `bimestre`, `n`.`tipo_evaluacion` AS `tipo_evaluacion`, `n`.`fecha` AS `fecha`, `e`.`apellido` AS `estudiante_apellido`, `e`.`nombre` AS `estudiante_nombre`, `e`.`dni` AS `estudiante_dni`, `m`.`nombre` AS `materia_nombre`, `p`.`apellido` AS `profesor_apellido`, `p`.`nombre` AS `profesor_nombre`, `c`.`nombre` AS `curso_nombre` FROM ((((`notas` `n` join `estudiantes` `e` on(`n`.`estudiante_id` = `e`.`id`)) join `materias` `m` on(`n`.`materia_id` = `m`.`id`)) join `profesores` `p` on(`n`.`profesor_id` = `p`.`id`)) join `cursos` `c` on(`e`.`curso_id` = `c`.`id`)) ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_profesores_completos`
--
DROP TABLE IF EXISTS `v_profesores_completos`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_profesores_completos`  AS SELECT `p`.`id` AS `id`, `p`.`dni` AS `dni`, `p`.`apellido` AS `apellido`, `p`.`nombre` AS `nombre`, `p`.`email` AS `email`, `p`.`telefono` AS `telefono`, `esp`.`nombre` AS `especialidad_nombre`, `p`.`activo` AS `activo`, `p`.`fecha_ingreso` AS `fecha_ingreso`, `p`.`creado_en` AS `creado_en` FROM (`profesores` `p` left join `especialidades` `esp` on(`p`.`especialidad_id` = `esp`.`id`)) ;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `archivos_subidos`
--
ALTER TABLE `archivos_subidos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_categoria` (`categoria`),
  ADD KEY `idx_subido_por` (`subido_por`),
  ADD KEY `idx_eliminado` (`eliminado`),
  ADD KEY `idx_subido_en` (`subido_en`);

--
-- Indices de la tabla `asistencia_periodos`
--
ALTER TABLE `asistencia_periodos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_periodo_anio_nombre` (`anio`,`nombre`),
  ADD KEY `idx_periodo_fechas` (`fecha_desde`,`fecha_hasta`,`cerrado`);

--
-- Indices de la tabla `asistencia_virtual`
--
ALTER TABLE `asistencia_virtual`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_asistencia_estudiante_fecha_materia` (`estudiante_id`,`fecha`,`materia_id`),
  ADD KEY `idx_asistencia_fecha_curso` (`fecha`,`curso_id`),
  ADD KEY `idx_asistencia_curso_materia_fecha` (`curso_id`,`materia_id`,`fecha`),
  ADD KEY `idx_asistencia_estudiante_fecha_estado` (`estudiante_id`,`fecha`,`estado`),
  ADD KEY `idx_asistencia_registrado_por` (`registrado_por`),
  ADD KEY `fk_av_materia` (`materia_id`);

--
-- Indices de la tabla `backups_log`
--
ALTER TABLE `backups_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_fecha` (`fecha`);

--
-- Indices de la tabla `cache_configuraciones`
--
ALTER TABLE `cache_configuraciones`
  ADD PRIMARY KEY (`cache_key`),
  ADD KEY `idx_expires` (`expires_at`);

--
-- Indices de la tabla `cache_data`
--
ALTER TABLE `cache_data`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_clave` (`clave`),
  ADD KEY `idx_expira_en` (`expira_en`),
  ADD KEY `idx_tipo` (`tipo`);

--
-- Indices de la tabla `configuraciones_sistema`
--
ALTER TABLE `configuraciones_sistema`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_clave` (`clave`),
  ADD KEY `idx_categoria` (`categoria`),
  ADD KEY `idx_editable` (`editable`);

--
-- Indices de la tabla `configuracion_sistema`
--
ALTER TABLE `configuracion_sistema`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `clave` (`clave`),
  ADD KEY `idx_clave` (`clave`),
  ADD KEY `idx_categoria` (`categoria`);

--
-- Indices de la tabla `contactos_emergencia`
--
ALTER TABLE `contactos_emergencia`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_estudiante_id` (`estudiante_id`);

--
-- Indices de la tabla `cursos`
--
ALTER TABLE `cursos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_especialidad` (`especialidad_id`),
  ADD KEY `idx_turno` (`turno_id`),
  ADD KEY `idx_anio_division` (`anio`,`division`),
  ADD KEY `idx_activo` (`activo`);

--
-- Indices de la tabla `equipo_directivo`
--
ALTER TABLE `equipo_directivo`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `idx_cargo` (`cargo`),
  ADD KEY `idx_activo` (`activo`),
  ADD KEY `fk_equipo_directivo_curso` (`curso_id`);

--
-- Indices de la tabla `especialidades`
--
ALTER TABLE `especialidades`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_codigo` (`codigo`),
  ADD KEY `idx_activa` (`activa`);

--
-- Indices de la tabla `estudiantes`
--
ALTER TABLE `estudiantes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_dni` (`dni`),
  ADD KEY `idx_curso` (`curso_id`),
  ADD KEY `idx_nombre_apellido` (`apellido`,`nombre`),
  ADD KEY `idx_activo` (`activo`),
  ADD KEY `idx_fecha_ingreso` (`fecha_ingreso`),
  ADD KEY `idx_estudiantes_curso_id` (`curso_id`),
  ADD KEY `idx_estudiantes_dni` (`dni`),
  ADD KEY `idx_estudiantes_activo` (`activo`),
  ADD KEY `idx_estudiantes_nombre_apellido` (`nombre`,`apellido`);

--
-- Indices de la tabla `estudiante_materias_recursadas`
--
ALTER TABLE `estudiante_materias_recursadas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_estudiante_recursada` (`estudiante_id`,`school_year`),
  ADD KEY `idx_curso_recursada` (`curso_id`,`school_year`),
  ADD KEY `fk_recursada_materia` (`materia_id`);

--
-- Indices de la tabla `horarios`
--
ALTER TABLE `horarios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_curso_id` (`curso_id`),
  ADD KEY `idx_materia_id` (`materia_id`),
  ADD KEY `idx_profesor_id` (`profesor_id`),
  ADD KEY `idx_dia_semana` (`dia_semana`),
  ADD KEY `idx_activo` (`activo`);

--
-- Indices de la tabla `llamados_atencion`
--
ALTER TABLE `llamados_atencion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_estudiante` (`estudiante_id`),
  ADD KEY `idx_usuario` (`usuario_id`),
  ADD KEY `idx_fecha` (`fecha`),
  ADD KEY `idx_llamados_estudiante` (`estudiante_id`),
  ADD KEY `idx_llamados_usuario` (`usuario_id`),
  ADD KEY `idx_llamados_fecha` (`fecha`);

--
-- Indices de la tabla `logs_auditoria`
--
ALTER TABLE `logs_auditoria`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_timestamp` (`timestamp`),
  ADD KEY `idx_entidad` (`entidad`),
  ADD KEY `idx_usuario` (`usuario_id`),
  ADD KEY `idx_entidad_accion_ts` (`entidad`,`accion`,`timestamp`),
  ADD KEY `idx_entidad_id_ts` (`entidad`,`entidad_id`,`timestamp`);

--
-- Indices de la tabla `logs_errores`
--
ALTER TABLE `logs_errores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_nivel` (`nivel`),
  ADD KEY `idx_usuario` (`usuario_id`),
  ADD KEY `idx_creado_en` (`creado_en`);

--
-- Indices de la tabla `logs_eventos`
--
ALTER TABLE `logs_eventos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tipo_evento` (`tipo_evento`),
  ADD KEY `idx_usuario` (`usuario_id`),
  ADD KEY `idx_creado_en` (`creado_en`);

--
-- Indices de la tabla `logs_seguridad`
--
ALTER TABLE `logs_seguridad`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_timestamp` (`timestamp`),
  ADD KEY `idx_tipo` (`tipo`),
  ADD KEY `idx_usuario` (`usuario_id`);

--
-- Indices de la tabla `logs_seguridad_avanzados`
--
ALTER TABLE `logs_seguridad_avanzados`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tipo_evento` (`tipo_evento`),
  ADD KEY `idx_severidad` (`severidad`),
  ADD KEY `idx_usuario` (`usuario_id`),
  ADD KEY `idx_creado_en` (`creado_en`);

--
-- Indices de la tabla `materias`
--
ALTER TABLE `materias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_codigo` (`codigo`),
  ADD KEY `idx_especialidad` (`especialidad_id`),
  ADD KEY `idx_anio_materia` (`anio_materia`),
  ADD KEY `idx_activa` (`activa`),
  ADD KEY `idx_es_taller` (`es_taller`);

--
-- Indices de la tabla `materias_previas`
--
ALTER TABLE `materias_previas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_estudiante_materia_anio` (`estudiante_id`,`materia_id`,`anio_previo`),
  ADD KEY `idx_estudiante` (`estudiante_id`),
  ADD KEY `idx_materia` (`materia_id`),
  ADD KEY `idx_anio` (`anio_previo`),
  ADD KEY `idx_estado` (`estado`);

--
-- Indices de la tabla `materia_curso`
--
ALTER TABLE `materia_curso`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_materia_curso` (`materia_id`,`curso_id`),
  ADD KEY `idx_materia` (`materia_id`),
  ADD KEY `idx_curso` (`curso_id`),
  ADD KEY `idx_activo` (`activo`);

--
-- Indices de la tabla `metricas_sistema`
--
ALTER TABLE `metricas_sistema`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_nombre` (`nombre`),
  ADD KEY `idx_categoria` (`categoria`),
  ADD KEY `idx_creado_en` (`creado_en`);

--
-- Indices de la tabla `notas`
--
ALTER TABLE `notas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_estudiante` (`estudiante_id`),
  ADD KEY `idx_materia` (`materia_id`),
  ADD KEY `idx_profesor` (`profesor_id`),
  ADD KEY `idx_bimestre` (`bimestre`),
  ADD KEY `idx_fecha` (`fecha`),
  ADD KEY `idx_calificacion` (`calificacion`),
  ADD KEY `idx_notas_estudiante_materia` (`estudiante_id`,`materia_id`),
  ADD KEY `idx_notas_profesor` (`profesor_id`),
  ADD KEY `idx_notas_bimestre` (`bimestre`),
  ADD KEY `idx_notas_fecha` (`fecha`),
  ADD KEY `idx_notas_est_mat_year_ctx` (`estudiante_id`,`materia_id`,`school_year`,`evaluation_context`);

--
-- Indices de la tabla `notas_avance`
--
ALTER TABLE `notas_avance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_avance_estudiante_materia_etapa` (`estudiante_id`,`materia_id`,`etapa`),
  ADD KEY `idx_avance_materia` (`materia_id`),
  ADD KEY `idx_avance_etapa` (`etapa`);

--
-- Indices de la tabla `notificaciones_enviadas`
--
ALTER TABLE `notificaciones_enviadas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tipo` (`tipo`),
  ADD KEY `idx_enviado` (`enviado`),
  ADD KEY `idx_creado_en` (`creado_en`);

--
-- Indices de la tabla `preceptor_curso`
--
ALTER TABLE `preceptor_curso`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_preceptor_curso` (`equipo_directivo_id`,`curso_id`),
  ADD KEY `idx_pc_curso` (`curso_id`);

--
-- Indices de la tabla `profesores`
--
ALTER TABLE `profesores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_dni` (`dni`),
  ADD UNIQUE KEY `unique_email` (`email`),
  ADD KEY `idx_especialidad` (`especialidad_id`),
  ADD KEY `idx_nombre_apellido` (`apellido`,`nombre`),
  ADD KEY `idx_activo` (`activo`),
  ADD KEY `idx_profesores_dni` (`dni`),
  ADD KEY `idx_profesores_activo` (`activo`),
  ADD KEY `idx_profesores_nombre_apellido` (`nombre`,`apellido`);

--
-- Indices de la tabla `profesor_curso`
--
ALTER TABLE `profesor_curso`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_profesor_curso_anio` (`profesor_id`,`curso_id`,`anio_academico`),
  ADD KEY `idx_profesor` (`profesor_id`),
  ADD KEY `idx_curso` (`curso_id`),
  ADD KEY `idx_anio_academico` (`anio_academico`),
  ADD KEY `idx_activo` (`activo`);

--
-- Indices de la tabla `profesor_materia`
--
ALTER TABLE `profesor_materia`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_profesor_materia_curso_anio_grupo` (`profesor_id`,`materia_id`,`curso_id`,`anio_academico`,`grupo_taller`),
  ADD KEY `idx_profesor` (`profesor_id`),
  ADD KEY `idx_materia` (`materia_id`),
  ADD KEY `idx_curso` (`curso_id`),
  ADD KEY `idx_activo` (`activo`);

--
-- Indices de la tabla `rbac_permissions`
--
ALTER TABLE `rbac_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_rbac_perm_slug` (`slug`);

--
-- Indices de la tabla `rbac_role_permissions`
--
ALTER TABLE `rbac_role_permissions`
  ADD PRIMARY KEY (`role`,`permission_slug`),
  ADD KEY `fk_rbac_rp_permission` (`permission_slug`);

--
-- Indices de la tabla `responsables`
--
ALTER TABLE `responsables`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_estudiante_id` (`estudiante_id`),
  ADD KEY `idx_dni` (`dni`);

--
-- Indices de la tabla `schema_migrations`
--
ALTER TABLE `schema_migrations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `version` (`version`),
  ADD KEY `idx_environment` (`environment`);

--
-- Indices de la tabla `school_year_milestones`
--
ALTER TABLE `school_year_milestones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_school_year_milestones_year` (`school_year`);

--
-- Indices de la tabla `sesiones_usuarios`
--
ALTER TABLE `sesiones_usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_session` (`session_id`),
  ADD KEY `idx_usuario` (`usuario_id`),
  ADD KEY `idx_activa` (`activa`),
  ADD KEY `idx_ultima_actividad` (`ultima_actividad`);

--
-- Indices de la tabla `suplencias`
--
ALTER TABLE `suplencias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_profesor` (`profesor_id`),
  ADD KEY `idx_suplente` (`suplente_id`),
  ADD KEY `idx_materia` (`materia_id`),
  ADD KEY `idx_estado` (`estado`),
  ADD KEY `idx_fecha_inicio` (`fecha_inicio`),
  ADD KEY `idx_fecha_fin` (`fecha_fin`),
  ADD KEY `fk_suplencias_usuario` (`usuario_id`);

--
-- Indices de la tabla `suplentes`
--
ALTER TABLE `suplentes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_suplente_dni` (`dni`),
  ADD KEY `idx_apellido_nombre` (`apellido`,`nombre`),
  ADD KEY `idx_activo` (`activo`);

--
-- Indices de la tabla `system_background_exports`
--
ALTER TABLE `system_background_exports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_created` (`usuario_id`,`created_at`),
  ADD KEY `idx_estado` (`estado`,`created_at`);

--
-- Indices de la tabla `system_queue_jobs`
--
ALTER TABLE `system_queue_jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_queue_poll` (`queue`,`failed_at`,`reserved_at`,`available_at`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indices de la tabla `turnos`
--
ALTER TABLE `turnos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_nombre` (`nombre`),
  ADD KEY `idx_activo` (`activo`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_dni` (`dni`),
  ADD UNIQUE KEY `unique_email` (`email`),
  ADD KEY `idx_rol` (`rol`),
  ADD KEY `idx_activo` (`activo`),
  ADD KEY `idx_ultimo_acceso` (`ultimo_acceso`);

--
-- Indices de la tabla `usuarios_codigos_respaldo`
--
ALTER TABLE `usuarios_codigos_respaldo`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_usuario` (`usuario_id`),
  ADD KEY `idx_usado` (`usado`);

--
-- Indices de la tabla `usuarios_mfa`
--
ALTER TABLE `usuarios_mfa`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_usuario` (`usuario_id`),
  ADD KEY `idx_activo` (`activo`);

--
-- Indices de la tabla `usuarios_mfa_temporal`
--
ALTER TABLE `usuarios_mfa_temporal`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_usuario` (`usuario_id`),
  ADD KEY `idx_creado` (`creado_en`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `archivos_subidos`
--
ALTER TABLE `archivos_subidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `asistencia_periodos`
--
ALTER TABLE `asistencia_periodos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `asistencia_virtual`
--
ALTER TABLE `asistencia_virtual`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1251;

--
-- AUTO_INCREMENT de la tabla `backups_log`
--
ALTER TABLE `backups_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `cache_data`
--
ALTER TABLE `cache_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `configuraciones_sistema`
--
ALTER TABLE `configuraciones_sistema`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT de la tabla `configuracion_sistema`
--
ALTER TABLE `configuracion_sistema`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de la tabla `contactos_emergencia`
--
ALTER TABLE `contactos_emergencia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=251;

--
-- AUTO_INCREMENT de la tabla `cursos`
--
ALTER TABLE `cursos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `equipo_directivo`
--
ALTER TABLE `equipo_directivo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `especialidades`
--
ALTER TABLE `especialidades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `estudiantes`
--
ALTER TABLE `estudiantes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=251;

--
-- AUTO_INCREMENT de la tabla `estudiante_materias_recursadas`
--
ALTER TABLE `estudiante_materias_recursadas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `horarios`
--
ALTER TABLE `horarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=135;

--
-- AUTO_INCREMENT de la tabla `llamados_atencion`
--
ALTER TABLE `llamados_atencion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `logs_auditoria`
--
ALTER TABLE `logs_auditoria`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=117;

--
-- AUTO_INCREMENT de la tabla `logs_errores`
--
ALTER TABLE `logs_errores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `logs_eventos`
--
ALTER TABLE `logs_eventos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `logs_seguridad`
--
ALTER TABLE `logs_seguridad`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de la tabla `logs_seguridad_avanzados`
--
ALTER TABLE `logs_seguridad_avanzados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `materias`
--
ALTER TABLE `materias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `materias_previas`
--
ALTER TABLE `materias_previas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `materia_curso`
--
ALTER TABLE `materia_curso`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=161;

--
-- AUTO_INCREMENT de la tabla `metricas_sistema`
--
ALTER TABLE `metricas_sistema`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `notas`
--
ALTER TABLE `notas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6651;

--
-- AUTO_INCREMENT de la tabla `notas_avance`
--
ALTER TABLE `notas_avance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `notificaciones_enviadas`
--
ALTER TABLE `notificaciones_enviadas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `preceptor_curso`
--
ALTER TABLE `preceptor_curso`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `profesores`
--
ALTER TABLE `profesores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de la tabla `profesor_curso`
--
ALTER TABLE `profesor_curso`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=130;

--
-- AUTO_INCREMENT de la tabla `profesor_materia`
--
ALTER TABLE `profesor_materia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=161;

--
-- AUTO_INCREMENT de la tabla `rbac_permissions`
--
ALTER TABLE `rbac_permissions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT de la tabla `responsables`
--
ALTER TABLE `responsables`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `schema_migrations`
--
ALTER TABLE `schema_migrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `school_year_milestones`
--
ALTER TABLE `school_year_milestones`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `sesiones_usuarios`
--
ALTER TABLE `sesiones_usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT de la tabla `suplencias`
--
ALTER TABLE `suplencias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `suplentes`
--
ALTER TABLE `suplentes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `system_background_exports`
--
ALTER TABLE `system_background_exports`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `system_queue_jobs`
--
ALTER TABLE `system_queue_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `turnos`
--
ALTER TABLE `turnos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT de la tabla `usuarios_codigos_respaldo`
--
ALTER TABLE `usuarios_codigos_respaldo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios_mfa`
--
ALTER TABLE `usuarios_mfa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `usuarios_mfa_temporal`
--
ALTER TABLE `usuarios_mfa_temporal`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `archivos_subidos`
--
ALTER TABLE `archivos_subidos`
  ADD CONSTRAINT `archivos_subidos_ibfk_1` FOREIGN KEY (`subido_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `asistencia_virtual`
--
ALTER TABLE `asistencia_virtual`
  ADD CONSTRAINT `fk_av_curso` FOREIGN KEY (`curso_id`) REFERENCES `cursos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_av_estudiante` FOREIGN KEY (`estudiante_id`) REFERENCES `estudiantes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_av_materia` FOREIGN KEY (`materia_id`) REFERENCES `materias` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_av_registrado_por` FOREIGN KEY (`registrado_por`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `contactos_emergencia`
--
ALTER TABLE `contactos_emergencia`
  ADD CONSTRAINT `contactos_emergencia_ibfk_1` FOREIGN KEY (`estudiante_id`) REFERENCES `estudiantes` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `cursos`
--
ALTER TABLE `cursos`
  ADD CONSTRAINT `cursos_ibfk_1` FOREIGN KEY (`especialidad_id`) REFERENCES `especialidades` (`id`),
  ADD CONSTRAINT `cursos_ibfk_2` FOREIGN KEY (`turno_id`) REFERENCES `turnos` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `equipo_directivo`
--
ALTER TABLE `equipo_directivo`
  ADD CONSTRAINT `fk_equipo_directivo_curso` FOREIGN KEY (`curso_id`) REFERENCES `cursos` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `estudiantes`
--
ALTER TABLE `estudiantes`
  ADD CONSTRAINT `estudiantes_ibfk_1` FOREIGN KEY (`curso_id`) REFERENCES `cursos` (`id`);

--
-- Filtros para la tabla `estudiante_materias_recursadas`
--
ALTER TABLE `estudiante_materias_recursadas`
  ADD CONSTRAINT `fk_recursada_curso` FOREIGN KEY (`curso_id`) REFERENCES `cursos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_recursada_estudiante` FOREIGN KEY (`estudiante_id`) REFERENCES `estudiantes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_recursada_materia` FOREIGN KEY (`materia_id`) REFERENCES `materias` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `horarios`
--
ALTER TABLE `horarios`
  ADD CONSTRAINT `horarios_ibfk_1` FOREIGN KEY (`curso_id`) REFERENCES `cursos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `horarios_ibfk_2` FOREIGN KEY (`materia_id`) REFERENCES `materias` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `horarios_ibfk_3` FOREIGN KEY (`profesor_id`) REFERENCES `profesores` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `llamados_atencion`
--
ALTER TABLE `llamados_atencion`
  ADD CONSTRAINT `llamados_atencion_ibfk_1` FOREIGN KEY (`estudiante_id`) REFERENCES `estudiantes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `llamados_atencion_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `logs_errores`
--
ALTER TABLE `logs_errores`
  ADD CONSTRAINT `logs_errores_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `logs_eventos`
--
ALTER TABLE `logs_eventos`
  ADD CONSTRAINT `logs_eventos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `logs_seguridad_avanzados`
--
ALTER TABLE `logs_seguridad_avanzados`
  ADD CONSTRAINT `logs_seguridad_avanzados_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `materias`
--
ALTER TABLE `materias`
  ADD CONSTRAINT `materias_ibfk_1` FOREIGN KEY (`especialidad_id`) REFERENCES `especialidades` (`id`);

--
-- Filtros para la tabla `materias_previas`
--
ALTER TABLE `materias_previas`
  ADD CONSTRAINT `materias_previas_ibfk_1` FOREIGN KEY (`estudiante_id`) REFERENCES `estudiantes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `materias_previas_ibfk_2` FOREIGN KEY (`materia_id`) REFERENCES `materias` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `materia_curso`
--
ALTER TABLE `materia_curso`
  ADD CONSTRAINT `materia_curso_ibfk_1` FOREIGN KEY (`materia_id`) REFERENCES `materias` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `materia_curso_ibfk_2` FOREIGN KEY (`curso_id`) REFERENCES `cursos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `notas`
--
ALTER TABLE `notas`
  ADD CONSTRAINT `notas_ibfk_1` FOREIGN KEY (`estudiante_id`) REFERENCES `estudiantes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notas_ibfk_2` FOREIGN KEY (`materia_id`) REFERENCES `materias` (`id`),
  ADD CONSTRAINT `notas_ibfk_3` FOREIGN KEY (`profesor_id`) REFERENCES `profesores` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `notas_avance`
--
ALTER TABLE `notas_avance`
  ADD CONSTRAINT `fk_avance_estudiante` FOREIGN KEY (`estudiante_id`) REFERENCES `estudiantes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_avance_materia` FOREIGN KEY (`materia_id`) REFERENCES `materias` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `preceptor_curso`
--
ALTER TABLE `preceptor_curso`
  ADD CONSTRAINT `fk_preceptor_curso_curso` FOREIGN KEY (`curso_id`) REFERENCES `cursos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_preceptor_curso_equipo` FOREIGN KEY (`equipo_directivo_id`) REFERENCES `equipo_directivo` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `profesores`
--
ALTER TABLE `profesores`
  ADD CONSTRAINT `profesores_ibfk_1` FOREIGN KEY (`especialidad_id`) REFERENCES `especialidades` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `profesor_curso`
--
ALTER TABLE `profesor_curso`
  ADD CONSTRAINT `fk_profesor_curso_curso` FOREIGN KEY (`curso_id`) REFERENCES `cursos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_profesor_curso_profesor` FOREIGN KEY (`profesor_id`) REFERENCES `profesores` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `profesor_materia`
--
ALTER TABLE `profesor_materia`
  ADD CONSTRAINT `profesor_materia_ibfk_1` FOREIGN KEY (`profesor_id`) REFERENCES `profesores` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `profesor_materia_ibfk_2` FOREIGN KEY (`materia_id`) REFERENCES `materias` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `profesor_materia_ibfk_3` FOREIGN KEY (`curso_id`) REFERENCES `cursos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `rbac_role_permissions`
--
ALTER TABLE `rbac_role_permissions`
  ADD CONSTRAINT `fk_rbac_rp_permission` FOREIGN KEY (`permission_slug`) REFERENCES `rbac_permissions` (`slug`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `responsables`
--
ALTER TABLE `responsables`
  ADD CONSTRAINT `responsables_ibfk_1` FOREIGN KEY (`estudiante_id`) REFERENCES `estudiantes` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `sesiones_usuarios`
--
ALTER TABLE `sesiones_usuarios`
  ADD CONSTRAINT `sesiones_usuarios_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `suplencias`
--
ALTER TABLE `suplencias`
  ADD CONSTRAINT `fk_suplencias_materia` FOREIGN KEY (`materia_id`) REFERENCES `materias` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_suplencias_profesor` FOREIGN KEY (`profesor_id`) REFERENCES `profesores` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_suplencias_suplente` FOREIGN KEY (`suplente_id`) REFERENCES `suplentes` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_suplencias_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuarios_codigos_respaldo`
--
ALTER TABLE `usuarios_codigos_respaldo`
  ADD CONSTRAINT `usuarios_codigos_respaldo_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `usuarios_mfa`
--
ALTER TABLE `usuarios_mfa`
  ADD CONSTRAINT `usuarios_mfa_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `usuarios_mfa_temporal`
--
ALTER TABLE `usuarios_mfa_temporal`
  ADD CONSTRAINT `usuarios_mfa_temporal_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

DELIMITER $$
--
-- Eventos
--
CREATE DEFINER=`root`@`localhost` EVENT `ev_limpiar_sesiones` ON SCHEDULE EVERY 1 HOUR STARTS '2025-09-28 14:47:25' ON COMPLETION NOT PRESERVE ENABLE DO CALL sp_limpiar_sesiones_expiradas()$$

CREATE DEFINER=`root`@`localhost` EVENT `ev_limpiar_cache` ON SCHEDULE EVERY 30 MINUTE STARTS '2025-09-28 14:47:25' ON COMPLETION NOT PRESERVE ENABLE DO CALL sp_limpiar_cache_expirado()$$

CREATE DEFINER=`root`@`localhost` EVENT `ev_limpiar_logs` ON SCHEDULE EVERY 1 DAY STARTS '2025-09-28 14:47:25' ON COMPLETION NOT PRESERVE ENABLE DO CALL sp_limpiar_logs_antiguos(30)$$

DELIMITER ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
