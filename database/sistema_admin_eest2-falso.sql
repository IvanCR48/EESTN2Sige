-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 25-06-2026 a las 20:01:49
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

INSERT INTO `archivos_subidos` (`id`, `nombre_original`, `nombre_seguro`, `ruta`, `tamaño`, `tipo_mime`, `categoria`, `subido_por`, `subido_en`, `eliminado`, `eliminado_en`) VALUES
(1, 'descarga.jpg', '1782184886_d80492078cf0f560_descarga.jpg.jpg', 'C:\\xampp\\htdocs\\SistemaAdmin\\src\\Services/../../uploads/justificativos/1782184886_d80492078cf0f560_descarga.jpg.jpg', 45460, 'image/jpeg', 'justificativos', 23, '2026-06-23 03:21:26', 0, NULL);

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

INSERT INTO `backups_log` (`id`, `nombre`, `tamaño`, `fecha`, `tipo`, `cifrado`, `usuario_id`) VALUES
(1, 'backup_completo_2026-03-28_23-56-48', 21948, '2026-03-28 22:56:49', 'manual', 1, NULL),
(2, 'backup_completo_2026-03-28_23-58-55', 22120, '2026-03-28 22:58:55', 'manual', 1, NULL),
(3, 'backup_completo_2026-03-28_20-25-03', 22268, '2026-03-28 23:25:03', 'manual', 1, NULL),
(4, 'backup_completo_2026-03-28_20-32-15', 22312, '2026-03-28 23:32:15', 'manual', 1, NULL),
(5, 'backup_completo_2026-06-25_14-59-06', 1048444, '2026-06-25 17:59:06', 'manual', 1, NULL);

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

INSERT INTO `cache_configuraciones` (`cache_key`, `cache_value`, `expires_at`, `created_at`) VALUES
('user_1', '{\"id\":1,\"dni\":\"admin\",\"apellido\":\"Administrador\",\"nombre\":\"Sistema\",\"email\":\"admin@eest2.edu.ar\",\"telefono\":null,\"password_hash\":\"$argon2id$v=19$m=65536,t=4,p=1$VkFPamd6OHBBcDhDSnlKSQ$gu2SKCr5VSzmYizkeTLUhT8SJ7mSy3h1uSku5J\\/PHN4\",\"rol\":\"admin\",\"activo\":1,\"ultimo_acceso\":\"2026-05-02 13:19:47\",\"intentos_fallidos\":0,\"bloqueado_hasta\":null,\"creado_en\":\"2025-09-29 00:18:31\",\"actualizado_en\":\"2026-05-02 13:19:47\"}', '2026-05-03 00:49:47', '2025-09-29 07:18:51'),
('user_15', '{\"id\":15,\"dni\":\"secretario4\",\"apellido\":\"Cardozo\",\"nombre\":\"Informatica\",\"email\":\"wabopellc@gmail.com\",\"telefono\":null,\"password_hash\":\"$argon2id$v=19$m=65536,t=4,p=1$Y2I3Y2VqZ1VBLzVBY2pDTg$d0uy8XpglL7RSco50yfzZju9Etk8n81A3KrxJnr4z94\",\"rol\":\"secretario\",\"activo\":1,\"ultimo_acceso\":\"2026-03-22 20:20:21\",\"intentos_fallidos\":0,\"bloqueado_hasta\":null,\"creado_en\":\"2025-11-13 01:05:27\",\"actualizado_en\":\"2026-03-22 20:20:21\"}', '2026-03-23 03:50:31', '2026-03-22 23:20:23'),
('user_21', '{\"id\":21,\"dni\":\"48678088\",\"apellido\":\"Mart\\u00ednez\",\"nombre\":\"Alan Ezequiel\",\"email\":\"alanme317@gmail.com\",\"telefono\":null,\"password_hash\":\"$argon2id$v=19$m=65536,t=4,p=3$TUEvUUlTUXdRZXZzSld0Tg$6F4udllfAGIS8cBxAXs+DddEpZNnZsX3AEbjr3Aj5nc\",\"rol\":\"profesor\",\"activo\":1,\"ultimo_acceso\":\"2026-03-29 11:45:20\",\"intentos_fallidos\":0,\"bloqueado_hasta\":null,\"creado_en\":\"2026-03-29 02:12:14\",\"actualizado_en\":\"2026-03-29 11:45:20\"}', '2026-03-29 15:16:02', '2026-03-29 05:12:14'),
('user_23', '{\"id\":23,\"dni\":\"alandesalojasistema#tripode\",\"apellido\":\"Admin\",\"nombre\":\"Usuario 2\",\"email\":null,\"telefono\":null,\"password_hash\":\"$argon2id$v=19$m=65536,t=4,p=3$TWVhSklWakFoc0dVU0xMSQ$FoaKhYFoCDpOBsA\\/H7DS0+8ha0lITetnmpGqQaDMfT8\",\"must_change_password\":0,\"rol\":\"admin\",\"activo\":1,\"ultimo_acceso\":\"2026-06-25 14:27:17\",\"intentos_fallidos\":0,\"bloqueado_hasta\":null,\"creado_en\":\"2026-05-02 17:21:51\",\"actualizado_en\":\"2026-06-25 14:27:17\"}', '2026-06-25 18:29:07', '2026-05-02 20:24:29'),
('user_26', '{\"id\":26,\"dni\":\"preceptor1\",\"apellido\":\"Cardozo\",\"nombre\":\"Ivan\",\"email\":\"juan@test.com\",\"telefono\":null,\"password_hash\":\"$argon2id$v=19$m=65536,t=4,p=3$NVFKV3pZMExEejZ1QnM0Tw$4zgIcAT3cGMMM7YKAXk7GrvIvMsmfbbwFfVe08nJIr8\",\"must_change_password\":0,\"rol\":\"preceptor\",\"activo\":1,\"ultimo_acceso\":\"2026-06-25 14:26:11\",\"intentos_fallidos\":0,\"bloqueado_hasta\":null,\"creado_en\":\"2026-06-25 14:25:40\",\"actualizado_en\":\"2026-06-25 14:26:34\"}', '2026-06-25 17:56:53', '2026-06-25 17:26:11'),
('user_3', '{\"id\":3,\"dni\":\"87654321\",\"apellido\":\"L\\u00f3pez\",\"nombre\":\"Carlos Alberto\",\"email\":\"preceptor@eest2.edu.ar\",\"telefono\":null,\"password_hash\":\"$argon2id$v=19$m=65536,t=4,p=1$VkFPamd6OHBBcDhDSnlKSQ$gu2SKCr5VSzmYizkeTLUhT8SJ7mSy3h1uSku5J\\/PHN4\",\"rol\":\"preceptor\",\"activo\":1,\"ultimo_acceso\":\"2025-11-12 17:30:12\",\"intentos_fallidos\":0,\"bloqueado_hasta\":null,\"creado_en\":\"2025-09-29 04:18:31\",\"actualizado_en\":\"2025-11-12 17:30:12\"}', '2025-11-13 01:02:01', '2025-11-12 20:30:23'),
('user_7', '{\"id\":7,\"dni\":\"secretario1\",\"apellido\":\"Mart\\u00ednez\",\"nombre\":\"Alan Ezequiel asas\",\"email\":\"martinez08alan@gmail.com\",\"telefono\":null,\"password_hash\":\"$argon2id$v=19$m=65536,t=4,p=1$c3JoWnhTSXJBcjhJN1ZBag$plpPZw+DeiN9smfyurlmSB2DFE8gHe6RsN0ea2zXxjw\",\"rol\":\"secretario\",\"activo\":1,\"ultimo_acceso\":\"2025-11-13 02:20:30\",\"intentos_fallidos\":0,\"bloqueado_hasta\":null,\"creado_en\":\"2025-11-13 00:16:14\",\"actualizado_en\":\"2025-11-13 02:20:30\"}', '2025-11-13 09:51:29', '2025-11-13 05:20:36'),
('user_username_87654321', '{\"id\":3,\"dni\":\"87654321\",\"apellido\":\"L\\u00f3pez\",\"nombre\":\"Carlos Alberto\",\"email\":\"preceptor@eest2.edu.ar\",\"telefono\":null,\"password_hash\":\"$argon2id$v=19$m=65536,t=4,p=1$VkFPamd6OHBBcDhDSnlKSQ$gu2SKCr5VSzmYizkeTLUhT8SJ7mSy3h1uSku5J\\/PHN4\",\"rol\":\"preceptor\",\"activo\":1,\"ultimo_acceso\":null,\"intentos_fallidos\":0,\"bloqueado_hasta\":null,\"creado_en\":\"2025-09-29 04:18:31\",\"actualizado_en\":\"2025-09-29 04:18:31\"}', '2025-11-13 01:00:12', '2025-11-12 20:30:12'),
('user_username_admin', '{\"id\":1,\"dni\":\"admin\",\"apellido\":\"Administrador\",\"nombre\":\"Sistema\",\"email\":\"admin@eest2.edu.ar\",\"telefono\":null,\"password_hash\":\"$argon2id$v=19$m=65536,t=4,p=1$VkFPamd6OHBBcDhDSnlKSQ$gu2SKCr5VSzmYizkeTLUhT8SJ7mSy3h1uSku5J\\/PHN4\",\"rol\":\"admin\",\"activo\":1,\"ultimo_acceso\":\"2026-03-28 19:03:42\",\"intentos_fallidos\":0,\"bloqueado_hasta\":null,\"creado_en\":\"2025-09-29 04:18:31\",\"actualizado_en\":\"2026-03-28 19:03:42\"}', '2026-03-29 03:08:42', '2025-09-29 07:18:49'),
('user_username_director', 'null', '2026-03-23 03:49:51', '2025-11-13 05:20:16'),
('user_username_preceptor_tarde', 'null', '2025-11-13 00:56:16', '2025-11-12 20:26:14'),
('user_username_preceptor1', 'null', '2025-11-13 00:55:57', '2025-11-12 20:25:57'),
('user_username_secretario1', '{\"id\":7,\"dni\":\"secretario1\",\"apellido\":\"Mart\\u00ednez\",\"nombre\":\"Alan Ezequiel asas\",\"email\":\"martinez08alan@gmail.com\",\"telefono\":null,\"password_hash\":\"$argon2id$v=19$m=65536,t=4,p=1$c3JoWnhTSXJBcjhJN1ZBag$plpPZw+DeiN9smfyurlmSB2DFE8gHe6RsN0ea2zXxjw\",\"rol\":\"secretario\",\"activo\":1,\"ultimo_acceso\":\"2025-11-13 02:20:30\",\"intentos_fallidos\":0,\"bloqueado_hasta\":null,\"creado_en\":\"2025-11-13 00:16:14\",\"actualizado_en\":\"2025-11-13 02:20:30\"}', '2025-11-13 11:43:51', '2025-11-13 05:20:30'),
('user_username_secretario4', '{\"id\":15,\"dni\":\"secretario4\",\"apellido\":\"Cardozo\",\"nombre\":\"Informatica\",\"email\":\"wabopellc@gmail.com\",\"telefono\":null,\"password_hash\":\"$argon2id$v=19$m=65536,t=4,p=1$Y2I3Y2VqZ1VBLzVBY2pDTg$d0uy8XpglL7RSco50yfzZju9Etk8n81A3KrxJnr4z94\",\"rol\":\"secretario\",\"activo\":1,\"ultimo_acceso\":null,\"intentos_fallidos\":0,\"bloqueado_hasta\":null,\"creado_en\":\"2025-11-13 01:05:27\",\"actualizado_en\":\"2025-11-13 01:05:27\"}', '2026-03-23 03:50:21', '2026-03-22 23:20:21'),
('user_username_secretario5', '{\"id\":19,\"dni\":\"secretario5\",\"apellido\":\"Vecchio\",\"nombre\":\"a\",\"email\":\"martinez08ezequiel@gmail.com\",\"telefono\":null,\"password_hash\":\"$argon2id$v=19$m=65536,t=4,p=1$MnRsbWs3ZC5sUWFzUkhFNw$hyh4\\/HJpo6DCXPbaMR7SITxTR60Cl9VLDpitdWf8KlU\",\"rol\":\"secretario\",\"activo\":1,\"ultimo_acceso\":null,\"intentos_fallidos\":0,\"bloqueado_hasta\":null,\"creado_en\":\"2025-11-13 01:12:21\",\"actualizado_en\":\"2025-11-13 01:12:21\"}', '2025-11-13 08:52:07', '2025-11-13 04:22:07'),
('user_username_vicedirector', 'null', '2026-03-23 03:49:57', '2025-11-12 20:26:23');

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

INSERT INTO `contactos_emergencia` (`id`, `estudiante_id`, `nombre`, `telefono`, `parentesco`, `creado_en`, `actualizado_en`) VALUES
(1, 1, 'Alan Ezequiel', '223 671-3071', 'Tío/a', '2025-10-22 13:07:50', '2025-10-22 13:07:50');

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
(1, '', 1, '1', 2, 1, 30, 0, '2025-10-24 17:28:25'),
(2, '', 1, '6', NULL, 1, 30, 0, '2025-11-12 18:32:50'),
(3, '', 6, '2', 1, 2, 30, 0, '2025-11-12 18:39:28'),
(4, '', 1, '1', NULL, 1, 30, 0, '2025-11-12 20:27:14'),
(5, '', 1, '2', NULL, 2, 30, 0, '2025-11-12 20:27:26'),
(6, '', 2, '1', NULL, 1, 30, 0, '2025-11-12 20:32:43'),
(7, '', 3, '1', NULL, 1, 30, 0, '2025-11-12 20:32:57'),
(8, '', 4, '3', 1, 2, 30, 0, '2025-11-12 20:33:09'),
(9, '', 5, '2', 1, 2, 30, 0, '2025-11-12 20:33:31'),
(10, '', 5, '1', 4, 1, 30, 0, '2025-11-13 07:25:42'),
(11, '', 7, '2', 5, 3, 30, 1, '2026-06-25 17:15:20');

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

INSERT INTO `equipo_directivo` (`id`, `usuario_id`, `curso_id`, `apellido`, `nombre`, `cargo`, `telefono`, `email`, `foto`, `activo`) VALUES
(1, NULL, NULL, 'González', 'Juan Carlos', 'Director', '011-4567-8001', 'director@eest2.edu.ar', NULL, 1),
(2, NULL, NULL, 'Martínez', 'Ana María', 'Vicedirectora', '011-4567-8002', 'vicedirectora@eest2.edu.ar', NULL, 0),
(3, NULL, NULL, 'López', 'Carlos Alberto', 'Secretario', '011-4567-8003', 'secretario@eest2.edu.ar', NULL, 0),
(4, NULL, NULL, 'Fernández', 'María Elena', 'Secretaria', '011-4567-8004', 'secretaria@eest2.edu.ar', NULL, 0),
(6, NULL, NULL, 'Martínez', 'Alan Ezequiel asas', 'secretario', '223 671-3071', 'lucas.acosta@email.com', NULL, 0),
(7, NULL, NULL, 'Martínez', 'Alan Ezequiel', 'secretario', '223 671-3071', 'martinez08alan@gmail.com', NULL, 0),
(8, 5, NULL, 'Martínez', 'a', 'preceptor', '223 671-3071', 'martinez08alan@gmail.com', NULL, 0),
(10, 7, NULL, 'Martínez', 'Alan Ezequiel asas', 'secretario', '223 671-3071', 'martinez08alan@gmail.com', NULL, 0),
(11, 8, NULL, 'Martínez', 'Alan Ezequiel', 'secretario', '223 671-3071', 'jess317077@gmail.com', NULL, 0),
(12, 9, NULL, 'Martínez', 'Alan Ezequielzxz', 'secretario', '32', 'lucas.acosta@email.com', NULL, 0),
(18, 15, NULL, 'Cardozo', 'Informatica', 'secretario', '223 671-3071', 'wabopellc@gmail.com', NULL, 0),
(19, 16, NULL, 'Vecchio', 'a', 'secretario', '2131234142', 'juan@test.com', NULL, 0),
(22, 19, NULL, 'Vecchio', 'a', 'secretario', '223 671-3071', 'martinez08ezequiel@gmail.com', NULL, 0),
(23, 20, 4, 'Martínez', 'Alan Ezequiel', 'preceptor', '223 671-3071', 'martinez08alan@gmail.com', NULL, 0),
(25, 25, 3, 'Martínez', 'Alan Ezequiel', 'preceptor', '223 671-3071', 'juan@test.com', NULL, 0),
(26, 26, 11, 'Cardozo', 'Ivan', 'preceptor', '223 671-3071', 'juan@test.com', NULL, 1);

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
(1, 'Técnico en Informática', 'INF', 'Especialización en programación y sistemas', 0, '2025-09-29 07:18:31'),
(2, 'Técnico en Electromecánica', 'EMC', 'Especialización en mecánica y electricidad', 0, '2025-09-29 07:18:31'),
(3, 'Técnico en Construcciones', 'CON', 'Especialización en construcción civil', 0, '2025-09-29 07:18:31'),
(4, 'MAMA', NULL, 'dsdsd', 0, '2025-11-13 07:22:37'),
(5, 'Técnico en programación', NULL, NULL, 1, '2026-06-25 17:14:19'),
(6, 'Técnico en construcciones', NULL, NULL, 1, '2026-06-25 17:14:31'),
(7, 'Técnico en electrónica', NULL, NULL, 1, '2026-06-25 17:14:42');

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

INSERT INTO `estudiantes` (`id`, `dni`, `dni_responsable`, `apellido`, `nombre`, `fecha_nacimiento`, `grupo_sanguineo`, `obra_social`, `domicilio`, `telefono`, `email`, `foto`, `curso_id`, `grupo_taller`, `fecha_ingreso`, `activo`, `creado_en`, `actualizado_en`) VALUES
(1, '12345678', NULL, 'García', 'Juan', '2000-01-01', NULL, NULL, 'Calle Test 123', '2235693071', 'juan@test.com', NULL, 1, NULL, '2025-10-22', 0, '2025-10-22 12:39:12', '2025-11-12 18:08:36'),
(20, '48678088', '48678089', 'Martínez', 'Alan Ezequiel', '2008-04-12', 'A+', 'Ninguna', 'Av. Tarantino 1931', '2235693071', 'martinez08alan@gmail.com', NULL, 11, 'A', '2026-06-25', 1, '2026-06-25 17:16:29', '2026-06-25 17:16:29');

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

INSERT INTO `horarios` (`id`, `curso_id`, `materia_id`, `profesor_id`, `grupo_taller`, `dia_semana`, `hora_inicio`, `hora_fin`, `aula`, `activo`, `creado_en`, `actualizado_en`) VALUES
(19, 11, 13, 7, 'A', 'lunes', '18:25:00', '21:00:00', 'Aula 101', 1, '2026-06-25 17:28:25', '2026-06-25 17:29:37');

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

INSERT INTO `llamados_atencion` (`id`, `estudiante_id`, `usuario_id`, `fecha`, `motivo`, `sancion`, `observaciones`, `creado_en`) VALUES
(1, 1, 22, '2025-11-04', 'Agresión física', 'Citación a padres', 'Describir detalladamente', '2025-11-04 13:17:24');

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

INSERT INTO `logs_auditoria` (`id`, `timestamp`, `accion`, `entidad`, `entidad_id`, `ip`, `usuario_id`, `datos`) VALUES
(1, '2025-11-13 02:14:46', 'CREAR_ESTUDIANTE', 'estudiante', 4, '::1', 1, '{\"id\":4,\"dni\":\"48678088\",\"nombre\":\"Alan Ezequiel asas\",\"apellido\":\"Martínez\",\"nombre_completo\":\"Martínez, Alan Ezequiel asas\",\"fecha_nacimiento\":\"2008-04-12\",\"edad\":17,\"grupo_sanguineo\":\"A+\",\"obra_social\":\"gftgf\",\"domicilio\":\"sfsfs\",\"telefono_fijo\":\"2235692071\",\"telefono_celular\":\"2235693071\",\"email\":\"wabopellc@gmail.com\",\"curso_id\":null,\"activo\":true,\"es_mayor_edad\":false,\"tiene_contacto\":true}'),
(2, '2025-11-13 02:21:29', 'CREAR_ESTUDIANTE', 'estudiante', 5, '::1', 7, '{\"id\":5,\"dni\":\"48678080\",\"nombre\":\"Informatica\",\"apellido\":\"Cardozo\",\"nombre_completo\":\"Cardozo, Informatica\",\"fecha_nacimiento\":\"2005-03-12\",\"edad\":20,\"grupo_sanguineo\":\"A-\",\"obra_social\":\"gftgf\",\"domicilio\":\"efdf\",\"telefono_fijo\":\"2234557867\",\"telefono_celular\":\"2235693071\",\"email\":\"jess317077@gmail.com\",\"curso_id\":null,\"activo\":true,\"es_mayor_edad\":true,\"tiene_contacto\":true}'),
(3, '2025-11-13 04:37:56', 'CREAR_PROFESOR', 'profesor', 4, '::1', 1, '{\"id\":4,\"dni\":\"48678089\",\"nombre\":\"MAMA\",\"apellido\":\"MAMA\",\"nombre_completo\":\"MAMA, MAMA\",\"fecha_nacimiento\":\"1995-12-02\",\"edad\":29,\"domicilio\":\"DSDSD\",\"telefono_fijo\":\"2235692071\",\"telefono_celular\":\"2235693071\",\"email\":\"jess317077@gmail.com\",\"titulo\":\"Licensiado Matematica\",\"especialidad\":null,\"fecha_ingreso\":\"2025-03-12\",\"activo\":true,\"tiene_especialidad\":false,\"tiene_contacto\":true}'),
(4, '2026-03-22 20:06:10', 'CREAR_ESTUDIANTE', 'estudiante', 6, '::1', 1, '{\"id\":6,\"dni\":\"48678679\",\"nombre\":\"Alan Ezequiel\",\"apellido\":\"Martínez\",\"nombre_completo\":\"Martínez, Alan Ezequiel\",\"fecha_nacimiento\":\"2025-04-12\",\"edad\":0,\"grupo_sanguineo\":\"A+\",\"obra_social\":\"dsdsdsd\",\"domicilio\":\"tarantino\",\"telefono_fijo\":\"2235692071\",\"telefono_celular\":\"2235693071\",\"email\":\"martinez08alan@gmail.com\",\"curso_id\":8,\"activo\":true,\"es_mayor_edad\":false,\"tiene_contacto\":true}'),
(5, '2026-03-28 19:46:32', 'ELIMINAR_ESTUDIANTE', 'estudiante', 6, '::1', 1, '{\"id\":6,\"dni\":\"48678679\",\"nombre\":\"Alan Ezequiel\",\"apellido\":\"Martínez\",\"nombre_completo\":\"Martínez, Alan Ezequiel\",\"fecha_nacimiento\":\"2025-04-12\",\"edad\":0,\"grupo_sanguineo\":null,\"obra_social\":null,\"domicilio\":\"tarantino\",\"telefono_fijo\":null,\"telefono_celular\":\"2235693071\",\"email\":\"martinez08alan@gmail.com\",\"curso_id\":8,\"activo\":true,\"es_mayor_edad\":false,\"tiene_contacto\":true}'),
(6, '2026-03-28 19:46:42', 'ELIMINAR_ESTUDIANTE', 'estudiante', 3, '::1', 1, '{\"id\":3,\"dni\":\"48306173\",\"nombre\":\"Iván Ismael\",\"apellido\":\"Cardozo\",\"nombre_completo\":\"Cardozo, Iván Ismael\",\"fecha_nacimiento\":\"2007-11-02\",\"edad\":18,\"grupo_sanguineo\":null,\"obra_social\":null,\"domicilio\":\"Hola\",\"telefono_fijo\":null,\"telefono_celular\":\"12321312323\",\"email\":\"lucas.acosta@email.com\",\"curso_id\":3,\"activo\":true,\"es_mayor_edad\":true,\"tiene_contacto\":true}'),
(7, '2026-03-28 19:47:06', 'ELIMINAR_PROFESOR', 'profesor', 4, '::1', 1, '{\"id\":4,\"dni\":\"48678089\",\"nombre\":\"MAMA\",\"apellido\":\"MAMA\",\"nombre_completo\":\"MAMA, MAMA\",\"fecha_nacimiento\":\"1995-12-02\",\"edad\":30,\"domicilio\":\"DSDSD\",\"telefono_fijo\":\"2235692071\",\"telefono_celular\":\"2235693071\",\"email\":\"jess317077@gmail.com\",\"titulo\":\"Licensiado Matematica\",\"especialidad\":\"MAMA\",\"fecha_ingreso\":\"2025-03-12\",\"activo\":true,\"tiene_especialidad\":true,\"tiene_contacto\":true}'),
(8, '2026-03-28 19:47:09', 'ELIMINAR_PROFESOR', 'profesor', 1, '::1', 1, '{\"id\":1,\"dni\":\"48678088\",\"nombre\":\"Alan Ezequiel\",\"apellido\":\"Martínez\",\"nombre_completo\":\"Martínez, Alan Ezequiel\",\"fecha_nacimiento\":\"2008-04-12\",\"edad\":17,\"domicilio\":\"adsadas\",\"telefono_fijo\":\"2235692071\",\"telefono_celular\":\"2235693071\",\"email\":\"martinez08alan@gmail.com\",\"titulo\":\"Licensiado Matematica\",\"especialidad\":\"Técnico en Informática\",\"fecha_ingreso\":\"2025-04-12\",\"activo\":true,\"tiene_especialidad\":true,\"tiene_contacto\":true}'),
(9, '2026-03-28 19:48:22', 'CREAR_PROFESOR', 'profesor', 5, '::1', 1, '{\"id\":5,\"dni\":\"48678082\",\"nombre\":\"Lucas\",\"apellido\":\"Acosta\",\"nombre_completo\":\"Acosta, Lucas\",\"fecha_nacimiento\":\"1980-04-12\",\"edad\":45,\"domicilio\":\"tarantino\",\"telefono_fijo\":\"2235692071\",\"telefono_celular\":\"223-15-678901\",\"email\":\"lucas.acosta@email.com\",\"titulo\":\"Licensiado en Gestion\",\"especialidad\":null,\"fecha_ingreso\":\"2026-02-04\",\"activo\":true,\"tiene_especialidad\":false,\"tiene_contacto\":true}'),
(10, '2026-03-28 20:43:05', 'ELIMINAR_MIEMBRO', 'equipo_directivo', 11, '::1', 1, '{\"apellido\":\"Martínez\",\"nombre\":\"Alan Ezequiel\",\"cargo\":\"secretario\"}'),
(11, '2026-03-28 20:43:08', 'ELIMINAR_MIEMBRO', 'equipo_directivo', 10, '::1', 1, '{\"apellido\":\"Martínez\",\"nombre\":\"Alan Ezequiel asas\",\"cargo\":\"secretario\"}'),
(12, '2026-03-28 20:43:11', 'ELIMINAR_MIEMBRO', 'equipo_directivo', 22, '::1', 1, '{\"apellido\":\"Vecchio\",\"nombre\":\"a\",\"cargo\":\"secretario\"}'),
(13, '2026-03-28 20:43:25', 'CREAR_MIEMBRO', 'equipo_directivo', 23, '::1', 1, '{\"apellido\":\"Martínez\",\"nombre\":\"Alan Ezequiel\",\"cargo\":\"preceptor\",\"curso_id\":4,\"usuario_generado\":\"preceptor1\"}'),
(14, '2026-03-29 02:10:14', 'CREAR_PROFESOR', 'profesor', 6, '::1', 1, '{\"id\":6,\"dni\":\"48678088\",\"nombre\":\"Alan Ezequiel\",\"apellido\":\"Martínez\",\"nombre_completo\":\"Martínez, Alan Ezequiel\",\"fecha_nacimiento\":\"1995-04-12\",\"edad\":30,\"domicilio\":null,\"telefono_fijo\":\"2235692071\",\"telefono_celular\":\"223-15-678901\",\"email\":\"alanme317@gmail.com\",\"titulo\":\"Licensiado Matematica\",\"especialidad\":null,\"fecha_ingreso\":\"2026-05-12\",\"activo\":true,\"tiene_especialidad\":false,\"tiene_contacto\":true}'),
(15, '2026-05-03 21:07:51', 'CREAR', 'nota', 15, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/notas.php?curso=4&trimestre=\",\"request_method\":\"POST\",\"after\":{\"estudiante_id\":5,\"materia_id\":7,\"calificacion\":9,\"bimestre\":0,\"evaluation_context\":\"intensification_first_semester\",\"recovery_scope\":\"first_semester\",\"school_year\":2026,\"observaciones\":null}}'),
(16, '2026-05-03 21:11:24', 'CREAR', 'nota', 16, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/notas.php?curso=4&trimestre=\",\"request_method\":\"POST\",\"after\":{\"estudiante_id\":5,\"materia_id\":7,\"calificacion\":9,\"bimestre\":0,\"evaluation_context\":\"intensification_first_semester\",\"recovery_scope\":\"first_semester\",\"school_year\":2026,\"observaciones\":null}}'),
(17, '2026-05-03 21:12:04', 'CREAR', 'nota', 17, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/notas.php?curso=4&trimestre=\",\"request_method\":\"POST\",\"after\":{\"estudiante_id\":5,\"materia_id\":7,\"calificacion\":7,\"bimestre\":0,\"evaluation_context\":\"intensification_first_semester\",\"recovery_scope\":\"first_semester\",\"school_year\":2026,\"observaciones\":null}}'),
(18, '2026-05-03 21:24:25', 'CREAR', 'nota', 18, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/notas.php?curso=4&trimestre=\",\"request_method\":\"POST\",\"after\":{\"estudiante_id\":5,\"materia_id\":7,\"calificacion\":9,\"bimestre\":0,\"evaluation_context\":\"intensification_first_semester\",\"recovery_scope\":\"first_semester\",\"school_year\":2026,\"observaciones\":null}}'),
(19, '2026-05-03 21:25:29', 'ACTUALIZAR', 'nota', 12, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/notas.php?curso=4&trimestre=\",\"request_method\":\"POST\",\"before\":{\"id\":12,\"estudiante_id\":5,\"materia_id\":7,\"valor\":0,\"valor_formateado\":\"0,0\",\"bimestre\":\"2\",\"fecha\":\"2026-03-29 00:40:05\",\"observaciones\":\"\",\"es_aprobada\":false,\"concepto\":\"Muy Insuficiente\",\"es_reciente\":false},\"after\":{\"id\":12,\"estudiante_id\":5,\"materia_id\":7,\"valor\":7,\"valor_formateado\":\"7,0\",\"bimestre\":\"2\",\"fecha\":\"2026-03-29 00:40:05\",\"observaciones\":\"\",\"es_aprobada\":true,\"concepto\":\"Bueno\",\"es_reciente\":false}}'),
(20, '2026-05-03 21:26:18', 'ACTUALIZAR', 'materia_previa', 2, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/materias_previas.php\",\"request_method\":\"POST\",\"before\":{\"id\":2,\"estudiante_id\":5,\"materia_id\":7,\"anio_previo\":1,\"estado\":\"pendiente\",\"observaciones\":null,\"mes_aprobacion\":null,\"anio_aprobacion\":null,\"nota\":null,\"creado_en\":\"2026-05-02 17:25:28\",\"actualizado_en\":\"2026-05-02 17:25:28\",\"apellido\":\"Cardozo\",\"nombre\":\"Informatica\",\"materia_nombre\":\"Gestion Y Autogestion\",\"anio_actual\":1},\"after\":{\"id\":2,\"estudiante_id\":5,\"materia_id\":7,\"anio_previo\":1,\"estado\":\"aprobada\",\"observaciones\":\"Aprobada (Diciembre 2026) con 7\",\"mes_aprobacion\":\"Diciembre\",\"anio_aprobacion\":2026,\"nota\":7,\"creado_en\":\"2026-05-02 17:25:28\",\"actualizado_en\":\"2026-05-03 21:26:18\"}}'),
(21, '2026-05-03 22:14:38', 'ELIMINAR_MIEMBRO', 'equipo_directivo', 23, '::1', 23, '{\"apellido\":\"Martínez\",\"nombre\":\"Alan Ezequiel\",\"cargo\":\"preceptor\"}'),
(22, '2026-05-03 22:23:35', 'CREAR_MIEMBRO', 'equipo_directivo', 25, '::1', 23, '{\"apellido\":\"Martínez\",\"nombre\":\"Alan Ezequiel\",\"cargo\":\"preceptor\",\"curso_id\":4,\"usuario_generado\":\"preceptor1\"}'),
(23, '2026-05-07 15:55:58', 'ELIMINAR_MIEMBRO', 'equipo_directivo', 25, '::1', 23, '{\"apellido\":\"Martínez\",\"nombre\":\"Alan Ezequiel\",\"cargo\":\"preceptor\"}'),
(24, '2026-05-07 16:17:15', 'ELIMINAR_MIEMBRO', 'equipo_directivo', 18, '::1', 23, '{\"apellido\":\"Cardozo\",\"nombre\":\"Informatica\",\"cargo\":\"secretario\"}'),
(25, '2026-05-07 16:17:18', 'ELIMINAR_MIEMBRO', 'equipo_directivo', 2, '::1', 23, '{\"apellido\":\"Martínez\",\"nombre\":\"Ana María\",\"cargo\":\"Vicedirectora\"}'),
(26, '2026-05-07 16:20:23', 'GUARDAR_ASISTENCIA', 'asistencia_virtual', 4, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/asistencia_virtual.php\",\"request_method\":\"POST\",\"fecha\":\"2026-05-11\",\"curso_id\":4,\"materia_id\":7,\"registros_guardados\":1,\"cambios\":[{\"estudiante_id\":5,\"before\":{\"estado\":null,\"observacion\":null},\"after\":{\"estado\":\"Media falta\",\"observacion\":null}}],\"cambios_count\":1}'),
(27, '2026-05-07 16:20:24', 'GUARDAR_ASISTENCIA', 'asistencia_virtual', 4, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/asistencia_virtual.php\",\"request_method\":\"POST\",\"fecha\":\"2026-05-11\",\"curso_id\":4,\"materia_id\":7,\"registros_guardados\":1,\"cambios\":[{\"estudiante_id\":5,\"before\":{\"estado\":\"Media falta\",\"observacion\":null},\"after\":{\"estado\":\"Presente\",\"observacion\":null}}],\"cambios_count\":1}'),
(28, '2026-05-21 13:05:50', 'CREAR', 'nota', 19, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/notas.php?curso=4&trimestre=\",\"request_method\":\"POST\",\"after\":{\"estudiante_id\":5,\"materia_id\":6,\"calificacion\":10,\"bimestre\":0,\"evaluation_context\":\"intensification_february_march\",\"recovery_scope\":\"second_semester\",\"school_year\":2026,\"observaciones\":null}}'),
(29, '2026-05-22 03:32:21', 'GUARDAR_ASISTENCIA', 'asistencia_virtual', 4, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/asistencia_virtual.php\",\"request_method\":\"POST\",\"fecha\":\"2026-05-25\",\"curso_id\":4,\"materia_id\":7,\"registros_guardados\":1,\"cambios\":[{\"estudiante_id\":5,\"before\":{\"estado\":null,\"observacion\":null},\"after\":{\"estado\":\"Media falta\",\"observacion\":null}}],\"cambios_count\":1}'),
(30, '2026-05-22 03:32:22', 'GUARDAR_ASISTENCIA', 'asistencia_virtual', 4, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/asistencia_virtual.php\",\"request_method\":\"POST\",\"fecha\":\"2026-05-25\",\"curso_id\":4,\"materia_id\":7,\"registros_guardados\":1,\"cambios\":[{\"estudiante_id\":5,\"before\":{\"estado\":\"Media falta\",\"observacion\":null},\"after\":{\"estado\":\"Tardanza\",\"observacion\":null}}],\"cambios_count\":1}'),
(31, '2026-05-22 03:32:23', 'GUARDAR_ASISTENCIA', 'asistencia_virtual', 4, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/asistencia_virtual.php\",\"request_method\":\"POST\",\"fecha\":\"2026-05-25\",\"curso_id\":4,\"materia_id\":7,\"registros_guardados\":1,\"cambios\":[{\"estudiante_id\":5,\"before\":{\"estado\":\"Tardanza\",\"observacion\":null},\"after\":{\"estado\":\"Presente\",\"observacion\":null}}],\"cambios_count\":1}'),
(32, '2026-05-22 03:32:23', 'GUARDAR_ASISTENCIA', 'asistencia_virtual', 4, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/asistencia_virtual.php\",\"request_method\":\"POST\",\"fecha\":\"2026-05-25\",\"curso_id\":4,\"materia_id\":7,\"registros_guardados\":1,\"cambios\":[{\"estudiante_id\":5,\"before\":{\"estado\":\"Presente\",\"observacion\":null},\"after\":{\"estado\":\"Media falta\",\"observacion\":null}}],\"cambios_count\":1}'),
(33, '2026-05-22 03:32:46', 'CREAR', 'nota', 20, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/notas.php?curso=4&trimestre=\",\"request_method\":\"POST\",\"after\":{\"estudiante_id\":5,\"materia_id\":6,\"calificacion\":8,\"bimestre\":0,\"evaluation_context\":\"intensification_first_semester\",\"recovery_scope\":\"first_semester\",\"school_year\":2026,\"observaciones\":null}}'),
(34, '2026-05-22 03:32:55', 'ACTUALIZAR', 'nota', 11, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/notas.php?curso=4&trimestre=\",\"request_method\":\"POST\",\"before\":{\"id\":11,\"estudiante_id\":5,\"materia_id\":7,\"valor\":9,\"valor_formateado\":\"9,0\",\"bimestre\":\"1\",\"fecha\":\"2026-03-29 00:39:57\",\"observaciones\":\"\",\"es_aprobada\":true,\"concepto\":\"Sobresaliente\",\"es_reciente\":false},\"after\":{\"id\":11,\"estudiante_id\":5,\"materia_id\":7,\"valor\":6,\"valor_formateado\":\"6,0\",\"bimestre\":\"1\",\"fecha\":\"2026-03-29 00:39:57\",\"observaciones\":\"\",\"es_aprobada\":true,\"concepto\":\"Satisfactorio\",\"es_reciente\":false}}'),
(35, '2026-05-22 03:50:05', 'ACTUALIZAR', 'nota', 20, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/notas.php?curso=4&trimestre=1\",\"request_method\":\"POST\",\"after\":{\"estudiante_id\":5,\"materia_id\":6,\"calificacion\":8,\"bimestre\":0,\"evaluation_context\":\"intensification_first_semester\",\"recovery_scope\":\"first_semester\",\"school_year\":2026,\"observaciones\":null},\"before\":{\"calificacion\":\"8.00\"}}'),
(36, '2026-05-22 03:51:04', 'ACTUALIZAR', 'nota', 20, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/notas.php?curso=4&trimestre=1\",\"request_method\":\"POST\",\"after\":{\"estudiante_id\":5,\"materia_id\":6,\"calificacion\":9,\"bimestre\":0,\"evaluation_context\":\"intensification_first_semester\",\"recovery_scope\":\"first_semester\",\"school_year\":2026,\"observaciones\":null},\"before\":{\"calificacion\":\"8.00\"}}'),
(37, '2026-05-22 04:16:52', 'ACTUALIZAR', 'nota', 11, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/notas.php?curso=4&trimestre=1\",\"request_method\":\"POST\",\"before\":{\"id\":11,\"estudiante_id\":5,\"materia_id\":7,\"valor\":6,\"valor_formateado\":\"6,0\",\"bimestre\":\"1\",\"fecha\":\"2026-03-29 00:39:57\",\"observaciones\":\"\",\"es_aprobada\":true,\"concepto\":\"Satisfactorio\",\"es_reciente\":false},\"after\":{\"id\":11,\"estudiante_id\":5,\"materia_id\":7,\"valor\":5,\"valor_formateado\":\"5,0\",\"bimestre\":\"1\",\"fecha\":\"2026-03-29 00:39:57\",\"observaciones\":\"\",\"es_aprobada\":false,\"concepto\":\"Insuficiente\",\"es_reciente\":false}}'),
(38, '2026-05-22 04:17:01', 'ACTUALIZAR', 'nota', 13, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/notas.php\",\"request_method\":\"POST\",\"before\":{\"id\":13,\"estudiante_id\":5,\"materia_id\":6,\"valor\":1,\"valor_formateado\":\"1,0\",\"bimestre\":\"1\",\"fecha\":\"2026-03-29 11:45:00\",\"observaciones\":\"\",\"es_aprobada\":false,\"concepto\":\"Muy Insuficiente\",\"es_reciente\":false},\"after\":{\"id\":13,\"estudiante_id\":5,\"materia_id\":6,\"valor\":6,\"valor_formateado\":\"6,0\",\"bimestre\":\"1\",\"fecha\":\"2026-03-29 11:45:00\",\"observaciones\":\"\",\"es_aprobada\":true,\"concepto\":\"Satisfactorio\",\"es_reciente\":false}}'),
(39, '2026-05-22 04:17:01', 'ACTUALIZAR', 'nota', 13, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/notas.php?curso=4&trimestre=1\",\"request_method\":\"POST\",\"before\":{\"id\":13,\"estudiante_id\":5,\"materia_id\":6,\"valor\":6,\"valor_formateado\":\"6,0\",\"bimestre\":\"1\",\"fecha\":\"2026-03-29 11:45:00\",\"observaciones\":\"\",\"es_aprobada\":true,\"concepto\":\"Satisfactorio\",\"es_reciente\":false},\"after\":{\"id\":13,\"estudiante_id\":5,\"materia_id\":6,\"valor\":6,\"valor_formateado\":\"6,0\",\"bimestre\":\"1\",\"fecha\":\"2026-03-29 11:45:00\",\"observaciones\":\"\",\"es_aprobada\":true,\"concepto\":\"Satisfactorio\",\"es_reciente\":false}}'),
(40, '2026-05-22 04:17:07', 'ACTUALIZAR', 'nota', 13, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/notas.php\",\"request_method\":\"POST\",\"before\":{\"id\":13,\"estudiante_id\":5,\"materia_id\":6,\"valor\":6,\"valor_formateado\":\"6,0\",\"bimestre\":\"1\",\"fecha\":\"2026-03-29 11:45:00\",\"observaciones\":\"\",\"es_aprobada\":true,\"concepto\":\"Satisfactorio\",\"es_reciente\":false},\"after\":{\"id\":13,\"estudiante_id\":5,\"materia_id\":6,\"valor\":7,\"valor_formateado\":\"7,0\",\"bimestre\":\"1\",\"fecha\":\"2026-03-29 11:45:00\",\"observaciones\":\"\",\"es_aprobada\":true,\"concepto\":\"Bueno\",\"es_reciente\":false}}'),
(41, '2026-05-22 04:17:07', 'ACTUALIZAR', 'nota', 13, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/notas.php?curso=4&trimestre=1\",\"request_method\":\"POST\",\"before\":{\"id\":13,\"estudiante_id\":5,\"materia_id\":6,\"valor\":7,\"valor_formateado\":\"7,0\",\"bimestre\":\"1\",\"fecha\":\"2026-03-29 11:45:00\",\"observaciones\":\"\",\"es_aprobada\":true,\"concepto\":\"Bueno\",\"es_reciente\":false},\"after\":{\"id\":13,\"estudiante_id\":5,\"materia_id\":6,\"valor\":7,\"valor_formateado\":\"7,0\",\"bimestre\":\"1\",\"fecha\":\"2026-03-29 11:45:00\",\"observaciones\":\"\",\"es_aprobada\":true,\"concepto\":\"Bueno\",\"es_reciente\":false}}'),
(42, '2026-05-22 04:17:11', 'ACTUALIZAR', 'nota', 11, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/notas.php\",\"request_method\":\"POST\",\"before\":{\"id\":11,\"estudiante_id\":5,\"materia_id\":7,\"valor\":5,\"valor_formateado\":\"5,0\",\"bimestre\":\"1\",\"fecha\":\"2026-03-29 00:39:57\",\"observaciones\":\"\",\"es_aprobada\":false,\"concepto\":\"Insuficiente\",\"es_reciente\":false},\"after\":{\"id\":11,\"estudiante_id\":5,\"materia_id\":7,\"valor\":8,\"valor_formateado\":\"8,0\",\"bimestre\":\"1\",\"fecha\":\"2026-03-29 00:39:57\",\"observaciones\":\"\",\"es_aprobada\":true,\"concepto\":\"Muy Bueno\",\"es_reciente\":false}}'),
(43, '2026-05-22 04:17:11', 'ACTUALIZAR', 'nota', 11, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/notas.php?curso=4&trimestre=1\",\"request_method\":\"POST\",\"before\":{\"id\":11,\"estudiante_id\":5,\"materia_id\":7,\"valor\":8,\"valor_formateado\":\"8,0\",\"bimestre\":\"1\",\"fecha\":\"2026-03-29 00:39:57\",\"observaciones\":\"\",\"es_aprobada\":true,\"concepto\":\"Muy Bueno\",\"es_reciente\":false},\"after\":{\"id\":11,\"estudiante_id\":5,\"materia_id\":7,\"valor\":8,\"valor_formateado\":\"8,0\",\"bimestre\":\"1\",\"fecha\":\"2026-03-29 00:39:57\",\"observaciones\":\"\",\"es_aprobada\":true,\"concepto\":\"Muy Bueno\",\"es_reciente\":false}}'),
(44, '2026-05-22 04:17:26', 'ACTUALIZAR', 'nota', 13, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/notas.php\",\"request_method\":\"POST\",\"before\":{\"id\":13,\"estudiante_id\":5,\"materia_id\":6,\"valor\":7,\"valor_formateado\":\"7,0\",\"bimestre\":\"1\",\"fecha\":\"2026-03-29 11:45:00\",\"observaciones\":\"\",\"es_aprobada\":true,\"concepto\":\"Bueno\",\"es_reciente\":false},\"after\":{\"id\":13,\"estudiante_id\":5,\"materia_id\":6,\"valor\":5,\"valor_formateado\":\"5,0\",\"bimestre\":\"1\",\"fecha\":\"2026-03-29 11:45:00\",\"observaciones\":\"\",\"es_aprobada\":false,\"concepto\":\"Insuficiente\",\"es_reciente\":false}}'),
(45, '2026-05-22 04:17:26', 'ACTUALIZAR', 'nota', 13, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/notas.php?curso=4&trimestre=1\",\"request_method\":\"POST\",\"before\":{\"id\":13,\"estudiante_id\":5,\"materia_id\":6,\"valor\":5,\"valor_formateado\":\"5,0\",\"bimestre\":\"1\",\"fecha\":\"2026-03-29 11:45:00\",\"observaciones\":\"\",\"es_aprobada\":false,\"concepto\":\"Insuficiente\",\"es_reciente\":false},\"after\":{\"id\":13,\"estudiante_id\":5,\"materia_id\":6,\"valor\":5,\"valor_formateado\":\"5,0\",\"bimestre\":\"1\",\"fecha\":\"2026-03-29 11:45:00\",\"observaciones\":\"\",\"es_aprobada\":false,\"concepto\":\"Insuficiente\",\"es_reciente\":false}}'),
(46, '2026-05-22 04:23:13', 'ACTUALIZAR', 'nota', 13, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/notas.php?curso=4&trimestre=1\",\"request_method\":\"POST\",\"before\":{\"id\":13,\"estudiante_id\":5,\"materia_id\":6,\"valor\":5,\"valor_formateado\":\"5,0\",\"bimestre\":\"1\",\"fecha\":\"2026-03-29 11:45:00\",\"observaciones\":\"\",\"es_aprobada\":false,\"concepto\":\"Insuficiente\",\"es_reciente\":false},\"after\":{\"id\":13,\"estudiante_id\":5,\"materia_id\":6,\"valor\":5,\"valor_formateado\":\"5,0\",\"bimestre\":\"1\",\"fecha\":\"2026-03-29 11:45:00\",\"observaciones\":\"\",\"es_aprobada\":false,\"concepto\":\"Insuficiente\",\"es_reciente\":false}}'),
(47, '2026-05-22 04:23:21', 'ACTUALIZAR', 'nota', 11, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/notas.php\",\"request_method\":\"POST\",\"before\":{\"id\":11,\"estudiante_id\":5,\"materia_id\":7,\"valor\":8,\"valor_formateado\":\"8,0\",\"bimestre\":\"1\",\"fecha\":\"2026-03-29 00:39:57\",\"observaciones\":\"\",\"es_aprobada\":true,\"concepto\":\"Muy Bueno\",\"es_reciente\":false},\"after\":{\"id\":11,\"estudiante_id\":5,\"materia_id\":7,\"valor\":7,\"valor_formateado\":\"7,0\",\"bimestre\":\"1\",\"fecha\":\"2026-03-29 00:39:57\",\"observaciones\":\"\",\"es_aprobada\":true,\"concepto\":\"Bueno\",\"es_reciente\":false}}'),
(48, '2026-05-22 04:23:21', 'ACTUALIZAR', 'nota', 11, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/notas.php?curso=4&trimestre=1\",\"request_method\":\"POST\",\"before\":{\"id\":11,\"estudiante_id\":5,\"materia_id\":7,\"valor\":7,\"valor_formateado\":\"7,0\",\"bimestre\":\"1\",\"fecha\":\"2026-03-29 00:39:57\",\"observaciones\":\"\",\"es_aprobada\":true,\"concepto\":\"Bueno\",\"es_reciente\":false},\"after\":{\"id\":11,\"estudiante_id\":5,\"materia_id\":7,\"valor\":7,\"valor_formateado\":\"7,0\",\"bimestre\":\"1\",\"fecha\":\"2026-03-29 00:39:57\",\"observaciones\":\"\",\"es_aprobada\":true,\"concepto\":\"Bueno\",\"es_reciente\":false}}'),
(49, '2026-05-22 04:23:26', 'ACTUALIZAR', 'nota', 13, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/notas.php\",\"request_method\":\"POST\",\"before\":{\"id\":13,\"estudiante_id\":5,\"materia_id\":6,\"valor\":5,\"valor_formateado\":\"5,0\",\"bimestre\":\"1\",\"fecha\":\"2026-03-29 11:45:00\",\"observaciones\":\"\",\"es_aprobada\":false,\"concepto\":\"Insuficiente\",\"es_reciente\":false},\"after\":{\"id\":13,\"estudiante_id\":5,\"materia_id\":6,\"valor\":7,\"valor_formateado\":\"7,0\",\"bimestre\":\"1\",\"fecha\":\"2026-03-29 11:45:00\",\"observaciones\":\"\",\"es_aprobada\":true,\"concepto\":\"Bueno\",\"es_reciente\":false}}'),
(50, '2026-05-22 04:23:26', 'ACTUALIZAR', 'nota', 13, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/notas.php?curso=4&trimestre=1\",\"request_method\":\"POST\",\"before\":{\"id\":13,\"estudiante_id\":5,\"materia_id\":6,\"valor\":7,\"valor_formateado\":\"7,0\",\"bimestre\":\"1\",\"fecha\":\"2026-03-29 11:45:00\",\"observaciones\":\"\",\"es_aprobada\":true,\"concepto\":\"Bueno\",\"es_reciente\":false},\"after\":{\"id\":13,\"estudiante_id\":5,\"materia_id\":6,\"valor\":7,\"valor_formateado\":\"7,0\",\"bimestre\":\"1\",\"fecha\":\"2026-03-29 11:45:00\",\"observaciones\":\"\",\"es_aprobada\":true,\"concepto\":\"Bueno\",\"es_reciente\":false}}'),
(51, '2026-05-22 04:51:13', 'ACTUALIZAR', 'nota', 13, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/notas.php?curso=4&trimestre=1\",\"request_method\":\"POST\",\"before\":{\"id\":13,\"estudiante_id\":5,\"materia_id\":6,\"valor\":7,\"valor_formateado\":\"7,0\",\"bimestre\":\"1\",\"fecha\":\"2026-03-29 11:45:00\",\"observaciones\":\"\",\"es_aprobada\":true,\"concepto\":\"Bueno\",\"es_reciente\":false},\"after\":{\"id\":13,\"estudiante_id\":5,\"materia_id\":6,\"valor\":7,\"valor_formateado\":\"7,0\",\"bimestre\":\"1\",\"fecha\":\"2026-03-29 11:45:00\",\"observaciones\":\"\",\"es_aprobada\":true,\"concepto\":\"Bueno\",\"es_reciente\":false}}'),
(52, '2026-05-22 04:51:19', 'ACTUALIZAR', 'nota', 11, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/notas.php\",\"request_method\":\"POST\",\"before\":{\"id\":11,\"estudiante_id\":5,\"materia_id\":7,\"valor\":7,\"valor_formateado\":\"7,0\",\"bimestre\":\"1\",\"fecha\":\"2026-03-29 00:39:57\",\"observaciones\":\"\",\"es_aprobada\":true,\"concepto\":\"Bueno\",\"es_reciente\":false},\"after\":{\"id\":11,\"estudiante_id\":5,\"materia_id\":7,\"valor\":5,\"valor_formateado\":\"5,0\",\"bimestre\":\"1\",\"fecha\":\"2026-03-29 00:39:57\",\"observaciones\":\"\",\"es_aprobada\":false,\"concepto\":\"Insuficiente\",\"es_reciente\":false}}'),
(53, '2026-05-22 04:51:19', 'ACTUALIZAR', 'nota', 11, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/notas.php?curso=4&trimestre=1\",\"request_method\":\"POST\",\"before\":{\"id\":11,\"estudiante_id\":5,\"materia_id\":7,\"valor\":5,\"valor_formateado\":\"5,0\",\"bimestre\":\"1\",\"fecha\":\"2026-03-29 00:39:57\",\"observaciones\":\"\",\"es_aprobada\":false,\"concepto\":\"Insuficiente\",\"es_reciente\":false},\"after\":{\"id\":11,\"estudiante_id\":5,\"materia_id\":7,\"valor\":5,\"valor_formateado\":\"5,0\",\"bimestre\":\"1\",\"fecha\":\"2026-03-29 00:39:57\",\"observaciones\":\"\",\"es_aprobada\":false,\"concepto\":\"Insuficiente\",\"es_reciente\":false}}'),
(54, '2026-06-23 00:00:57', 'CREAR', 'estudiante', 7, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/estudiantes.php?action=importar\",\"request_method\":\"POST\",\"after\":{\"id\":7,\"dni\":\"50965208\",\"nombre\":\"Josias Simón\",\"apellido\":\"Barrios\",\"nombre_completo\":\"Barrios, Josias Simón\",\"fecha_nacimiento\":null,\"edad\":0,\"grupo_sanguineo\":null,\"obra_social\":null,\"domicilio\":null,\"telefono_fijo\":null,\"telefono_celular\":null,\"email\":null,\"curso_id\":7,\"activo\":true,\"dni_responsable\":\"50965208\",\"grupo_taller\":\"A\",\"fecha_ingreso\":null,\"es_mayor_edad\":false,\"tiene_contacto\":false}}'),
(55, '2026-06-23 00:00:57', 'CREAR', 'estudiante', 8, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/estudiantes.php?action=importar\",\"request_method\":\"POST\",\"after\":{\"id\":8,\"dni\":\"52617850\",\"nombre\":\"Sashenka Zoe\",\"apellido\":\"Belasin Caroleo\",\"nombre_completo\":\"Belasin Caroleo, Sashenka Zoe\",\"fecha_nacimiento\":null,\"edad\":0,\"grupo_sanguineo\":null,\"obra_social\":null,\"domicilio\":null,\"telefono_fijo\":null,\"telefono_celular\":null,\"email\":null,\"curso_id\":7,\"activo\":true,\"dni_responsable\":\"52617850\",\"grupo_taller\":\"A\",\"fecha_ingreso\":null,\"es_mayor_edad\":false,\"tiene_contacto\":false}}'),
(56, '2026-06-23 00:00:57', 'CREAR', 'estudiante', 9, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/estudiantes.php?action=importar\",\"request_method\":\"POST\",\"after\":{\"id\":9,\"dni\":\"52455101\",\"nombre\":\"Mauro Lionel\",\"apellido\":\"Caceres Pintos\",\"nombre_completo\":\"Caceres Pintos, Mauro Lionel\",\"fecha_nacimiento\":null,\"edad\":0,\"grupo_sanguineo\":null,\"obra_social\":null,\"domicilio\":null,\"telefono_fijo\":null,\"telefono_celular\":null,\"email\":null,\"curso_id\":7,\"activo\":true,\"dni_responsable\":\"52455101\",\"grupo_taller\":\"A\",\"fecha_ingreso\":null,\"es_mayor_edad\":false,\"tiene_contacto\":false}}'),
(57, '2026-06-23 00:00:57', 'CREAR', 'estudiante', 10, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/estudiantes.php?action=importar\",\"request_method\":\"POST\",\"after\":{\"id\":10,\"dni\":\"52159174\",\"nombre\":\"Amenabar Gastón\",\"apellido\":\"Cervigni\",\"nombre_completo\":\"Cervigni, Amenabar Gastón\",\"fecha_nacimiento\":null,\"edad\":0,\"grupo_sanguineo\":null,\"obra_social\":null,\"domicilio\":null,\"telefono_fijo\":null,\"telefono_celular\":null,\"email\":null,\"curso_id\":7,\"activo\":true,\"dni_responsable\":\"52159174\",\"grupo_taller\":\"A\",\"fecha_ingreso\":null,\"es_mayor_edad\":false,\"tiene_contacto\":false}}'),
(58, '2026-06-23 00:00:57', 'CREAR', 'estudiante', 11, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/estudiantes.php?action=importar\",\"request_method\":\"POST\",\"after\":{\"id\":11,\"dni\":\"51177794\",\"nombre\":\"Valentino Lucas\",\"apellido\":\"Diez\",\"nombre_completo\":\"Diez, Valentino Lucas\",\"fecha_nacimiento\":null,\"edad\":0,\"grupo_sanguineo\":null,\"obra_social\":null,\"domicilio\":null,\"telefono_fijo\":null,\"telefono_celular\":null,\"email\":null,\"curso_id\":7,\"activo\":true,\"dni_responsable\":\"51177794\",\"grupo_taller\":\"A\",\"fecha_ingreso\":null,\"es_mayor_edad\":false,\"tiene_contacto\":false}}'),
(59, '2026-06-23 00:00:57', 'CREAR', 'estudiante', 12, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/estudiantes.php?action=importar\",\"request_method\":\"POST\",\"after\":{\"id\":12,\"dni\":\"52159333\",\"nombre\":\"Mateo Benjamin\",\"apellido\":\"Fierro\",\"nombre_completo\":\"Fierro, Mateo Benjamin\",\"fecha_nacimiento\":null,\"edad\":0,\"grupo_sanguineo\":null,\"obra_social\":null,\"domicilio\":null,\"telefono_fijo\":null,\"telefono_celular\":null,\"email\":null,\"curso_id\":7,\"activo\":true,\"dni_responsable\":\"52159333\",\"grupo_taller\":\"A\",\"fecha_ingreso\":null,\"es_mayor_edad\":false,\"tiene_contacto\":false}}'),
(60, '2026-06-23 00:00:57', 'CREAR', 'estudiante', 13, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/estudiantes.php?action=importar\",\"request_method\":\"POST\",\"after\":{\"id\":13,\"dni\":\"52605104\",\"nombre\":\"Bruno Lautaro\",\"apellido\":\"Flores\",\"nombre_completo\":\"Flores, Bruno Lautaro\",\"fecha_nacimiento\":null,\"edad\":0,\"grupo_sanguineo\":null,\"obra_social\":null,\"domicilio\":null,\"telefono_fijo\":null,\"telefono_celular\":null,\"email\":null,\"curso_id\":7,\"activo\":true,\"dni_responsable\":\"52605104\",\"grupo_taller\":\"A\",\"fecha_ingreso\":null,\"es_mayor_edad\":false,\"tiene_contacto\":false}}'),
(61, '2026-06-23 00:00:57', 'CREAR', 'estudiante', 14, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/estudiantes.php?action=importar\",\"request_method\":\"POST\",\"after\":{\"id\":14,\"dni\":\"52408053\",\"nombre\":\"Benjamin Nicolas\",\"apellido\":\"Gomez\",\"nombre_completo\":\"Gomez, Benjamin Nicolas\",\"fecha_nacimiento\":null,\"edad\":0,\"grupo_sanguineo\":null,\"obra_social\":null,\"domicilio\":null,\"telefono_fijo\":null,\"telefono_celular\":null,\"email\":null,\"curso_id\":7,\"activo\":true,\"dni_responsable\":\"52408053\",\"grupo_taller\":\"A\",\"fecha_ingreso\":null,\"es_mayor_edad\":false,\"tiene_contacto\":false}}'),
(62, '2026-06-23 00:00:57', 'CREAR', 'estudiante', 15, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/estudiantes.php?action=importar\",\"request_method\":\"POST\",\"after\":{\"id\":15,\"dni\":\"51247402\",\"nombre\":\"Fausto Agustin\",\"apellido\":\"Iglesias\",\"nombre_completo\":\"Iglesias, Fausto Agustin\",\"fecha_nacimiento\":null,\"edad\":0,\"grupo_sanguineo\":null,\"obra_social\":null,\"domicilio\":null,\"telefono_fijo\":null,\"telefono_celular\":null,\"email\":null,\"curso_id\":7,\"activo\":true,\"dni_responsable\":\"51247402\",\"grupo_taller\":\"A\",\"fecha_ingreso\":null,\"es_mayor_edad\":false,\"tiene_contacto\":false}}'),
(63, '2026-06-23 00:00:57', 'CREAR', 'estudiante', 16, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/estudiantes.php?action=importar\",\"request_method\":\"POST\",\"after\":{\"id\":16,\"dni\":\"51457511\",\"nombre\":\"Aaron Gabriel\",\"apellido\":\"Loidi Espindola\",\"nombre_completo\":\"Loidi Espindola, Aaron Gabriel\",\"fecha_nacimiento\":null,\"edad\":0,\"grupo_sanguineo\":null,\"obra_social\":null,\"domicilio\":null,\"telefono_fijo\":null,\"telefono_celular\":null,\"email\":null,\"curso_id\":7,\"activo\":true,\"dni_responsable\":\"51457511\",\"grupo_taller\":\"A\",\"fecha_ingreso\":null,\"es_mayor_edad\":false,\"tiene_contacto\":false}}'),
(64, '2026-06-23 00:00:57', 'CREAR', 'estudiante', 17, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/estudiantes.php?action=importar\",\"request_method\":\"POST\",\"after\":{\"id\":17,\"dni\":\"51457580\",\"nombre\":\"Juan Cruz\",\"apellido\":\"Oro\",\"nombre_completo\":\"Oro, Juan Cruz\",\"fecha_nacimiento\":null,\"edad\":0,\"grupo_sanguineo\":null,\"obra_social\":null,\"domicilio\":null,\"telefono_fijo\":null,\"telefono_celular\":null,\"email\":null,\"curso_id\":7,\"activo\":true,\"dni_responsable\":\"51457580\",\"grupo_taller\":\"A\",\"fecha_ingreso\":null,\"es_mayor_edad\":false,\"tiene_contacto\":false}}'),
(65, '2026-06-23 00:00:57', 'CREAR', 'estudiante', 18, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/estudiantes.php?action=importar\",\"request_method\":\"POST\",\"after\":{\"id\":18,\"dni\":\"51146430\",\"nombre\":\"Joaquin Nicolas\",\"apellido\":\"Rugna\",\"nombre_completo\":\"Rugna, Joaquin Nicolas\",\"fecha_nacimiento\":null,\"edad\":0,\"grupo_sanguineo\":null,\"obra_social\":null,\"domicilio\":null,\"telefono_fijo\":null,\"telefono_celular\":null,\"email\":null,\"curso_id\":7,\"activo\":true,\"dni_responsable\":\"51146430\",\"grupo_taller\":\"A\",\"fecha_ingreso\":null,\"es_mayor_edad\":false,\"tiene_contacto\":false}}'),
(66, '2026-06-23 00:00:57', 'CREAR', 'estudiante', 19, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/estudiantes.php?action=importar\",\"request_method\":\"POST\",\"after\":{\"id\":19,\"dni\":\"52605093\",\"nombre\":\"Joaquin Nahuel\",\"apellido\":\"Urrizaga\",\"nombre_completo\":\"Urrizaga, Joaquin Nahuel\",\"fecha_nacimiento\":null,\"edad\":0,\"grupo_sanguineo\":null,\"obra_social\":null,\"domicilio\":null,\"telefono_fijo\":null,\"telefono_celular\":null,\"email\":null,\"curso_id\":7,\"activo\":true,\"dni_responsable\":\"52605093\",\"grupo_taller\":\"A\",\"fecha_ingreso\":null,\"es_mayor_edad\":false,\"tiene_contacto\":false}}'),
(67, '2026-06-23 00:21:15', 'GUARDAR_ASISTENCIA', 'asistencia_virtual', 4, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/asistencia_virtual.php\",\"request_method\":\"POST\",\"fecha\":\"2026-06-29\",\"curso_id\":4,\"materia_id\":7,\"registros_guardados\":1,\"cambios\":[{\"estudiante_id\":5,\"before\":{\"estado\":null,\"observacion\":null},\"after\":{\"estado\":\"Presente\",\"observacion\":null}}],\"cambios_count\":1}'),
(68, '2026-06-23 00:21:16', 'GUARDAR_ASISTENCIA', 'asistencia_virtual', 4, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/asistencia_virtual.php\",\"request_method\":\"POST\",\"fecha\":\"2026-06-29\",\"curso_id\":4,\"materia_id\":7,\"registros_guardados\":1,\"cambios\":[{\"estudiante_id\":5,\"before\":{\"estado\":\"Presente\",\"observacion\":null},\"after\":{\"estado\":\"Tardanza\",\"observacion\":null}}],\"cambios_count\":1}'),
(69, '2026-06-23 00:21:17', 'GUARDAR_ASISTENCIA', 'asistencia_virtual', 4, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/asistencia_virtual.php\",\"request_method\":\"POST\",\"fecha\":\"2026-06-29\",\"curso_id\":4,\"materia_id\":7,\"registros_guardados\":1,\"cambios\":[{\"estudiante_id\":5,\"before\":{\"estado\":\"Tardanza\",\"observacion\":null},\"after\":{\"estado\":\"Media falta\",\"observacion\":null}}],\"cambios_count\":1}'),
(70, '2026-06-23 00:21:17', 'GUARDAR_ASISTENCIA', 'asistencia_virtual', 4, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/asistencia_virtual.php\",\"request_method\":\"POST\",\"fecha\":\"2026-06-29\",\"curso_id\":4,\"materia_id\":7,\"registros_guardados\":1,\"cambios\":[{\"estudiante_id\":5,\"before\":{\"estado\":\"Media falta\",\"observacion\":null},\"after\":{\"estado\":\"Ausente justificado\",\"observacion\":null}}],\"cambios_count\":1}'),
(71, '2026-06-23 00:21:18', 'GUARDAR_ASISTENCIA', 'asistencia_virtual', 4, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/asistencia_virtual.php\",\"request_method\":\"POST\",\"fecha\":\"2026-06-29\",\"curso_id\":4,\"materia_id\":7,\"registros_guardados\":1,\"cambios\":[{\"estudiante_id\":5,\"before\":{\"estado\":\"Ausente justificado\",\"observacion\":null},\"after\":{\"estado\":\"Ausente\",\"observacion\":null}}],\"cambios_count\":1}'),
(72, '2026-06-23 00:21:24', 'GUARDAR_ASISTENCIA', 'asistencia_virtual', 4, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/asistencia_virtual.php\",\"request_method\":\"POST\",\"fecha\":\"2026-06-29\",\"curso_id\":4,\"materia_id\":7,\"registros_guardados\":1,\"cambios\":[],\"cambios_count\":0}'),
(73, '2026-06-23 00:21:26', 'GUARDAR_ASISTENCIA', 'asistencia_virtual', 4, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/asistencia_virtual.php\",\"request_method\":\"POST\",\"fecha\":\"2026-06-29\",\"curso_id\":4,\"materia_id\":7,\"registros_guardados\":1,\"cambios\":[],\"cambios_count\":0}'),
(74, '2026-06-25 14:07:32', 'ELIMINAR', 'materia_previa', 2, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/materias_previas.php\",\"request_method\":\"POST\",\"before\":{\"id\":2,\"estudiante_id\":5,\"materia_id\":7,\"anio_previo\":1,\"estado\":\"aprobada\",\"observaciones\":\"Aprobada (Diciembre 2026) con 7\",\"mes_aprobacion\":\"Diciembre\",\"anio_aprobacion\":2026,\"nota\":7,\"creado_en\":\"2026-05-02 17:25:28\",\"actualizado_en\":\"2026-05-03 21:26:18\"}}'),
(75, '2026-06-25 14:08:20', 'ELIMINAR', 'estudiante', 7, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/estudiantes.php\",\"request_method\":\"POST\",\"before\":{\"id\":7,\"dni\":\"50965208\",\"nombre\":\"Josias Simón\",\"apellido\":\"Barrios\",\"nombre_completo\":\"Barrios, Josias Simón\",\"fecha_nacimiento\":null,\"edad\":0,\"grupo_sanguineo\":null,\"obra_social\":null,\"domicilio\":null,\"telefono_fijo\":null,\"telefono_celular\":null,\"email\":null,\"curso_id\":7,\"activo\":true,\"dni_responsable\":\"50965208\",\"grupo_taller\":\"A\",\"fecha_ingreso\":\"2026-06-23\",\"es_mayor_edad\":false,\"tiene_contacto\":false}}'),
(76, '2026-06-25 14:08:20', 'ELIMINAR_ESTUDIANTE', 'estudiante', 7, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/estudiantes.php\",\"request_method\":\"POST\",\"id\":7,\"dni\":\"50965208\",\"nombre\":\"Josias Simón\",\"apellido\":\"Barrios\",\"nombre_completo\":\"Barrios, Josias Simón\",\"fecha_nacimiento\":null,\"edad\":0,\"grupo_sanguineo\":null,\"obra_social\":null,\"domicilio\":null,\"telefono_fijo\":null,\"telefono_celular\":null,\"email\":null,\"curso_id\":7,\"activo\":true,\"dni_responsable\":\"50965208\",\"grupo_taller\":\"A\",\"fecha_ingreso\":\"2026-06-23\",\"es_mayor_edad\":false,\"tiene_contacto\":false}'),
(77, '2026-06-25 14:08:23', 'ELIMINAR', 'estudiante', 8, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/estudiantes.php?success=eliminado&nombre=Barrios%2C+Josias+Sim%C3%B3n\",\"request_method\":\"POST\",\"before\":{\"id\":8,\"dni\":\"52617850\",\"nombre\":\"Sashenka Zoe\",\"apellido\":\"Belasin Caroleo\",\"nombre_completo\":\"Belasin Caroleo, Sashenka Zoe\",\"fecha_nacimiento\":null,\"edad\":0,\"grupo_sanguineo\":null,\"obra_social\":null,\"domicilio\":null,\"telefono_fijo\":null,\"telefono_celular\":null,\"email\":null,\"curso_id\":7,\"activo\":true,\"dni_responsable\":\"52617850\",\"grupo_taller\":\"A\",\"fecha_ingreso\":\"2026-06-23\",\"es_mayor_edad\":false,\"tiene_contacto\":false}}'),
(78, '2026-06-25 14:08:23', 'ELIMINAR_ESTUDIANTE', 'estudiante', 8, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/estudiantes.php?success=eliminado&nombre=Barrios%2C+Josias+Sim%C3%B3n\",\"request_method\":\"POST\",\"id\":8,\"dni\":\"52617850\",\"nombre\":\"Sashenka Zoe\",\"apellido\":\"Belasin Caroleo\",\"nombre_completo\":\"Belasin Caroleo, Sashenka Zoe\",\"fecha_nacimiento\":null,\"edad\":0,\"grupo_sanguineo\":null,\"obra_social\":null,\"domicilio\":null,\"telefono_fijo\":null,\"telefono_celular\":null,\"email\":null,\"curso_id\":7,\"activo\":true,\"dni_responsable\":\"52617850\",\"grupo_taller\":\"A\",\"fecha_ingreso\":\"2026-06-23\",\"es_mayor_edad\":false,\"tiene_contacto\":false}'),
(79, '2026-06-25 14:08:24', 'ELIMINAR', 'estudiante', 9, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/estudiantes.php?success=eliminado&nombre=Belasin+Caroleo%2C+Sashenka+Zoe\",\"request_method\":\"POST\",\"before\":{\"id\":9,\"dni\":\"52455101\",\"nombre\":\"Mauro Lionel\",\"apellido\":\"Caceres Pintos\",\"nombre_completo\":\"Caceres Pintos, Mauro Lionel\",\"fecha_nacimiento\":null,\"edad\":0,\"grupo_sanguineo\":null,\"obra_social\":null,\"domicilio\":null,\"telefono_fijo\":null,\"telefono_celular\":null,\"email\":null,\"curso_id\":7,\"activo\":true,\"dni_responsable\":\"52455101\",\"grupo_taller\":\"A\",\"fecha_ingreso\":\"2026-06-23\",\"es_mayor_edad\":false,\"tiene_contacto\":false}}'),
(80, '2026-06-25 14:08:24', 'ELIMINAR_ESTUDIANTE', 'estudiante', 9, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/estudiantes.php?success=eliminado&nombre=Belasin+Caroleo%2C+Sashenka+Zoe\",\"request_method\":\"POST\",\"id\":9,\"dni\":\"52455101\",\"nombre\":\"Mauro Lionel\",\"apellido\":\"Caceres Pintos\",\"nombre_completo\":\"Caceres Pintos, Mauro Lionel\",\"fecha_nacimiento\":null,\"edad\":0,\"grupo_sanguineo\":null,\"obra_social\":null,\"domicilio\":null,\"telefono_fijo\":null,\"telefono_celular\":null,\"email\":null,\"curso_id\":7,\"activo\":true,\"dni_responsable\":\"52455101\",\"grupo_taller\":\"A\",\"fecha_ingreso\":\"2026-06-23\",\"es_mayor_edad\":false,\"tiene_contacto\":false}');
INSERT INTO `logs_auditoria` (`id`, `timestamp`, `accion`, `entidad`, `entidad_id`, `ip`, `usuario_id`, `datos`) VALUES
(81, '2026-06-25 14:08:26', 'ELIMINAR', 'estudiante', 5, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/estudiantes.php?success=eliminado&nombre=Caceres+Pintos%2C+Mauro+Lionel\",\"request_method\":\"POST\",\"before\":{\"id\":5,\"dni\":\"48678080\",\"nombre\":\"Informatica\",\"apellido\":\"Cardozo\",\"nombre_completo\":\"Cardozo, Informatica\",\"fecha_nacimiento\":\"2005-03-12\",\"edad\":21,\"grupo_sanguineo\":null,\"obra_social\":null,\"domicilio\":\"efdf\",\"telefono_fijo\":null,\"telefono_celular\":\"2235693071\",\"email\":\"jess317077@gmail.com\",\"curso_id\":4,\"activo\":true,\"dni_responsable\":null,\"grupo_taller\":\"A\",\"fecha_ingreso\":\"2025-11-13\",\"es_mayor_edad\":true,\"tiene_contacto\":true}}'),
(82, '2026-06-25 14:08:26', 'ELIMINAR_ESTUDIANTE', 'estudiante', 5, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/estudiantes.php?success=eliminado&nombre=Caceres+Pintos%2C+Mauro+Lionel\",\"request_method\":\"POST\",\"id\":5,\"dni\":\"48678080\",\"nombre\":\"Informatica\",\"apellido\":\"Cardozo\",\"nombre_completo\":\"Cardozo, Informatica\",\"fecha_nacimiento\":\"2005-03-12\",\"edad\":21,\"grupo_sanguineo\":null,\"obra_social\":null,\"domicilio\":\"efdf\",\"telefono_fijo\":null,\"telefono_celular\":\"2235693071\",\"email\":\"jess317077@gmail.com\",\"curso_id\":4,\"activo\":true,\"dni_responsable\":null,\"grupo_taller\":\"A\",\"fecha_ingreso\":\"2025-11-13\",\"es_mayor_edad\":true,\"tiene_contacto\":true}'),
(83, '2026-06-25 14:08:28', 'ELIMINAR', 'estudiante', 10, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/estudiantes.php?success=eliminado&nombre=Cardozo%2C+Informatica\",\"request_method\":\"POST\",\"before\":{\"id\":10,\"dni\":\"52159174\",\"nombre\":\"Amenabar Gastón\",\"apellido\":\"Cervigni\",\"nombre_completo\":\"Cervigni, Amenabar Gastón\",\"fecha_nacimiento\":null,\"edad\":0,\"grupo_sanguineo\":null,\"obra_social\":null,\"domicilio\":null,\"telefono_fijo\":null,\"telefono_celular\":null,\"email\":null,\"curso_id\":7,\"activo\":true,\"dni_responsable\":\"52159174\",\"grupo_taller\":\"A\",\"fecha_ingreso\":\"2026-06-23\",\"es_mayor_edad\":false,\"tiene_contacto\":false}}'),
(84, '2026-06-25 14:08:28', 'ELIMINAR_ESTUDIANTE', 'estudiante', 10, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/estudiantes.php?success=eliminado&nombre=Cardozo%2C+Informatica\",\"request_method\":\"POST\",\"id\":10,\"dni\":\"52159174\",\"nombre\":\"Amenabar Gastón\",\"apellido\":\"Cervigni\",\"nombre_completo\":\"Cervigni, Amenabar Gastón\",\"fecha_nacimiento\":null,\"edad\":0,\"grupo_sanguineo\":null,\"obra_social\":null,\"domicilio\":null,\"telefono_fijo\":null,\"telefono_celular\":null,\"email\":null,\"curso_id\":7,\"activo\":true,\"dni_responsable\":\"52159174\",\"grupo_taller\":\"A\",\"fecha_ingreso\":\"2026-06-23\",\"es_mayor_edad\":false,\"tiene_contacto\":false}'),
(85, '2026-06-25 14:08:31', 'ELIMINAR', 'estudiante', 11, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/estudiantes.php?success=eliminado&nombre=Cervigni%2C+Amenabar+Gast%C3%B3n\",\"request_method\":\"POST\",\"before\":{\"id\":11,\"dni\":\"51177794\",\"nombre\":\"Valentino Lucas\",\"apellido\":\"Diez\",\"nombre_completo\":\"Diez, Valentino Lucas\",\"fecha_nacimiento\":null,\"edad\":0,\"grupo_sanguineo\":null,\"obra_social\":null,\"domicilio\":null,\"telefono_fijo\":null,\"telefono_celular\":null,\"email\":null,\"curso_id\":7,\"activo\":true,\"dni_responsable\":\"51177794\",\"grupo_taller\":\"A\",\"fecha_ingreso\":\"2026-06-23\",\"es_mayor_edad\":false,\"tiene_contacto\":false}}'),
(86, '2026-06-25 14:08:31', 'ELIMINAR_ESTUDIANTE', 'estudiante', 11, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/estudiantes.php?success=eliminado&nombre=Cervigni%2C+Amenabar+Gast%C3%B3n\",\"request_method\":\"POST\",\"id\":11,\"dni\":\"51177794\",\"nombre\":\"Valentino Lucas\",\"apellido\":\"Diez\",\"nombre_completo\":\"Diez, Valentino Lucas\",\"fecha_nacimiento\":null,\"edad\":0,\"grupo_sanguineo\":null,\"obra_social\":null,\"domicilio\":null,\"telefono_fijo\":null,\"telefono_celular\":null,\"email\":null,\"curso_id\":7,\"activo\":true,\"dni_responsable\":\"51177794\",\"grupo_taller\":\"A\",\"fecha_ingreso\":\"2026-06-23\",\"es_mayor_edad\":false,\"tiene_contacto\":false}'),
(87, '2026-06-25 14:08:33', 'ELIMINAR', 'estudiante', 12, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/estudiantes.php?success=eliminado&nombre=Diez%2C+Valentino+Lucas\",\"request_method\":\"POST\",\"before\":{\"id\":12,\"dni\":\"52159333\",\"nombre\":\"Mateo Benjamin\",\"apellido\":\"Fierro\",\"nombre_completo\":\"Fierro, Mateo Benjamin\",\"fecha_nacimiento\":null,\"edad\":0,\"grupo_sanguineo\":null,\"obra_social\":null,\"domicilio\":null,\"telefono_fijo\":null,\"telefono_celular\":null,\"email\":null,\"curso_id\":7,\"activo\":true,\"dni_responsable\":\"52159333\",\"grupo_taller\":\"A\",\"fecha_ingreso\":\"2026-06-23\",\"es_mayor_edad\":false,\"tiene_contacto\":false}}'),
(88, '2026-06-25 14:08:33', 'ELIMINAR_ESTUDIANTE', 'estudiante', 12, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/estudiantes.php?success=eliminado&nombre=Diez%2C+Valentino+Lucas\",\"request_method\":\"POST\",\"id\":12,\"dni\":\"52159333\",\"nombre\":\"Mateo Benjamin\",\"apellido\":\"Fierro\",\"nombre_completo\":\"Fierro, Mateo Benjamin\",\"fecha_nacimiento\":null,\"edad\":0,\"grupo_sanguineo\":null,\"obra_social\":null,\"domicilio\":null,\"telefono_fijo\":null,\"telefono_celular\":null,\"email\":null,\"curso_id\":7,\"activo\":true,\"dni_responsable\":\"52159333\",\"grupo_taller\":\"A\",\"fecha_ingreso\":\"2026-06-23\",\"es_mayor_edad\":false,\"tiene_contacto\":false}'),
(89, '2026-06-25 14:08:35', 'ELIMINAR', 'estudiante', 13, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/estudiantes.php?success=eliminado&nombre=Fierro%2C+Mateo+Benjamin\",\"request_method\":\"POST\",\"before\":{\"id\":13,\"dni\":\"52605104\",\"nombre\":\"Bruno Lautaro\",\"apellido\":\"Flores\",\"nombre_completo\":\"Flores, Bruno Lautaro\",\"fecha_nacimiento\":null,\"edad\":0,\"grupo_sanguineo\":null,\"obra_social\":null,\"domicilio\":null,\"telefono_fijo\":null,\"telefono_celular\":null,\"email\":null,\"curso_id\":7,\"activo\":true,\"dni_responsable\":\"52605104\",\"grupo_taller\":\"A\",\"fecha_ingreso\":\"2026-06-23\",\"es_mayor_edad\":false,\"tiene_contacto\":false}}'),
(90, '2026-06-25 14:08:35', 'ELIMINAR_ESTUDIANTE', 'estudiante', 13, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/estudiantes.php?success=eliminado&nombre=Fierro%2C+Mateo+Benjamin\",\"request_method\":\"POST\",\"id\":13,\"dni\":\"52605104\",\"nombre\":\"Bruno Lautaro\",\"apellido\":\"Flores\",\"nombre_completo\":\"Flores, Bruno Lautaro\",\"fecha_nacimiento\":null,\"edad\":0,\"grupo_sanguineo\":null,\"obra_social\":null,\"domicilio\":null,\"telefono_fijo\":null,\"telefono_celular\":null,\"email\":null,\"curso_id\":7,\"activo\":true,\"dni_responsable\":\"52605104\",\"grupo_taller\":\"A\",\"fecha_ingreso\":\"2026-06-23\",\"es_mayor_edad\":false,\"tiene_contacto\":false}'),
(91, '2026-06-25 14:08:38', 'ELIMINAR', 'estudiante', 14, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/estudiantes.php?success=eliminado&nombre=Flores%2C+Bruno+Lautaro\",\"request_method\":\"POST\",\"before\":{\"id\":14,\"dni\":\"52408053\",\"nombre\":\"Benjamin Nicolas\",\"apellido\":\"Gomez\",\"nombre_completo\":\"Gomez, Benjamin Nicolas\",\"fecha_nacimiento\":null,\"edad\":0,\"grupo_sanguineo\":null,\"obra_social\":null,\"domicilio\":null,\"telefono_fijo\":null,\"telefono_celular\":null,\"email\":null,\"curso_id\":7,\"activo\":true,\"dni_responsable\":\"52408053\",\"grupo_taller\":\"A\",\"fecha_ingreso\":\"2026-06-23\",\"es_mayor_edad\":false,\"tiene_contacto\":false}}'),
(92, '2026-06-25 14:08:38', 'ELIMINAR_ESTUDIANTE', 'estudiante', 14, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/estudiantes.php?success=eliminado&nombre=Flores%2C+Bruno+Lautaro\",\"request_method\":\"POST\",\"id\":14,\"dni\":\"52408053\",\"nombre\":\"Benjamin Nicolas\",\"apellido\":\"Gomez\",\"nombre_completo\":\"Gomez, Benjamin Nicolas\",\"fecha_nacimiento\":null,\"edad\":0,\"grupo_sanguineo\":null,\"obra_social\":null,\"domicilio\":null,\"telefono_fijo\":null,\"telefono_celular\":null,\"email\":null,\"curso_id\":7,\"activo\":true,\"dni_responsable\":\"52408053\",\"grupo_taller\":\"A\",\"fecha_ingreso\":\"2026-06-23\",\"es_mayor_edad\":false,\"tiene_contacto\":false}'),
(93, '2026-06-25 14:08:40', 'ELIMINAR', 'estudiante', 15, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/estudiantes.php?success=eliminado&nombre=Gomez%2C+Benjamin+Nicolas\",\"request_method\":\"POST\",\"before\":{\"id\":15,\"dni\":\"51247402\",\"nombre\":\"Fausto Agustin\",\"apellido\":\"Iglesias\",\"nombre_completo\":\"Iglesias, Fausto Agustin\",\"fecha_nacimiento\":null,\"edad\":0,\"grupo_sanguineo\":null,\"obra_social\":null,\"domicilio\":null,\"telefono_fijo\":null,\"telefono_celular\":null,\"email\":null,\"curso_id\":7,\"activo\":true,\"dni_responsable\":\"51247402\",\"grupo_taller\":\"A\",\"fecha_ingreso\":\"2026-06-23\",\"es_mayor_edad\":false,\"tiene_contacto\":false}}'),
(94, '2026-06-25 14:08:40', 'ELIMINAR_ESTUDIANTE', 'estudiante', 15, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/estudiantes.php?success=eliminado&nombre=Gomez%2C+Benjamin+Nicolas\",\"request_method\":\"POST\",\"id\":15,\"dni\":\"51247402\",\"nombre\":\"Fausto Agustin\",\"apellido\":\"Iglesias\",\"nombre_completo\":\"Iglesias, Fausto Agustin\",\"fecha_nacimiento\":null,\"edad\":0,\"grupo_sanguineo\":null,\"obra_social\":null,\"domicilio\":null,\"telefono_fijo\":null,\"telefono_celular\":null,\"email\":null,\"curso_id\":7,\"activo\":true,\"dni_responsable\":\"51247402\",\"grupo_taller\":\"A\",\"fecha_ingreso\":\"2026-06-23\",\"es_mayor_edad\":false,\"tiene_contacto\":false}'),
(95, '2026-06-25 14:08:42', 'ELIMINAR', 'estudiante', 16, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/estudiantes.php?success=eliminado&nombre=Iglesias%2C+Fausto+Agustin\",\"request_method\":\"POST\",\"before\":{\"id\":16,\"dni\":\"51457511\",\"nombre\":\"Aaron Gabriel\",\"apellido\":\"Loidi Espindola\",\"nombre_completo\":\"Loidi Espindola, Aaron Gabriel\",\"fecha_nacimiento\":null,\"edad\":0,\"grupo_sanguineo\":null,\"obra_social\":null,\"domicilio\":null,\"telefono_fijo\":null,\"telefono_celular\":null,\"email\":null,\"curso_id\":7,\"activo\":true,\"dni_responsable\":\"51457511\",\"grupo_taller\":\"A\",\"fecha_ingreso\":\"2026-06-23\",\"es_mayor_edad\":false,\"tiene_contacto\":false}}'),
(96, '2026-06-25 14:08:42', 'ELIMINAR_ESTUDIANTE', 'estudiante', 16, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/estudiantes.php?success=eliminado&nombre=Iglesias%2C+Fausto+Agustin\",\"request_method\":\"POST\",\"id\":16,\"dni\":\"51457511\",\"nombre\":\"Aaron Gabriel\",\"apellido\":\"Loidi Espindola\",\"nombre_completo\":\"Loidi Espindola, Aaron Gabriel\",\"fecha_nacimiento\":null,\"edad\":0,\"grupo_sanguineo\":null,\"obra_social\":null,\"domicilio\":null,\"telefono_fijo\":null,\"telefono_celular\":null,\"email\":null,\"curso_id\":7,\"activo\":true,\"dni_responsable\":\"51457511\",\"grupo_taller\":\"A\",\"fecha_ingreso\":\"2026-06-23\",\"es_mayor_edad\":false,\"tiene_contacto\":false}'),
(97, '2026-06-25 14:08:44', 'ELIMINAR', 'estudiante', 4, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/estudiantes.php?success=eliminado&nombre=Loidi+Espindola%2C+Aaron+Gabriel\",\"request_method\":\"POST\",\"before\":{\"id\":4,\"dni\":\"48678088\",\"nombre\":\"Alan Ezequiel asas\",\"apellido\":\"Martínez\",\"nombre_completo\":\"Martínez, Alan Ezequiel asas\",\"fecha_nacimiento\":\"2008-04-12\",\"edad\":18,\"grupo_sanguineo\":null,\"obra_social\":null,\"domicilio\":\"sfsfs\",\"telefono_fijo\":null,\"telefono_celular\":\"2235693071\",\"email\":\"wabopellc@gmail.com\",\"curso_id\":null,\"activo\":true,\"dni_responsable\":null,\"grupo_taller\":null,\"fecha_ingreso\":\"2025-11-13\",\"es_mayor_edad\":true,\"tiene_contacto\":true}}'),
(98, '2026-06-25 14:08:44', 'ELIMINAR_ESTUDIANTE', 'estudiante', 4, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/estudiantes.php?success=eliminado&nombre=Loidi+Espindola%2C+Aaron+Gabriel\",\"request_method\":\"POST\",\"id\":4,\"dni\":\"48678088\",\"nombre\":\"Alan Ezequiel asas\",\"apellido\":\"Martínez\",\"nombre_completo\":\"Martínez, Alan Ezequiel asas\",\"fecha_nacimiento\":\"2008-04-12\",\"edad\":18,\"grupo_sanguineo\":null,\"obra_social\":null,\"domicilio\":\"sfsfs\",\"telefono_fijo\":null,\"telefono_celular\":\"2235693071\",\"email\":\"wabopellc@gmail.com\",\"curso_id\":null,\"activo\":true,\"dni_responsable\":null,\"grupo_taller\":null,\"fecha_ingreso\":\"2025-11-13\",\"es_mayor_edad\":true,\"tiene_contacto\":true}'),
(99, '2026-06-25 14:08:47', 'ELIMINAR', 'estudiante', 17, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/estudiantes.php?success=eliminado&nombre=Mart%C3%ADnez%2C+Alan+Ezequiel+asas\",\"request_method\":\"POST\",\"before\":{\"id\":17,\"dni\":\"51457580\",\"nombre\":\"Juan Cruz\",\"apellido\":\"Oro\",\"nombre_completo\":\"Oro, Juan Cruz\",\"fecha_nacimiento\":null,\"edad\":0,\"grupo_sanguineo\":null,\"obra_social\":null,\"domicilio\":null,\"telefono_fijo\":null,\"telefono_celular\":null,\"email\":null,\"curso_id\":7,\"activo\":true,\"dni_responsable\":\"51457580\",\"grupo_taller\":\"A\",\"fecha_ingreso\":\"2026-06-23\",\"es_mayor_edad\":false,\"tiene_contacto\":false}}'),
(100, '2026-06-25 14:08:47', 'ELIMINAR_ESTUDIANTE', 'estudiante', 17, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/estudiantes.php?success=eliminado&nombre=Mart%C3%ADnez%2C+Alan+Ezequiel+asas\",\"request_method\":\"POST\",\"id\":17,\"dni\":\"51457580\",\"nombre\":\"Juan Cruz\",\"apellido\":\"Oro\",\"nombre_completo\":\"Oro, Juan Cruz\",\"fecha_nacimiento\":null,\"edad\":0,\"grupo_sanguineo\":null,\"obra_social\":null,\"domicilio\":null,\"telefono_fijo\":null,\"telefono_celular\":null,\"email\":null,\"curso_id\":7,\"activo\":true,\"dni_responsable\":\"51457580\",\"grupo_taller\":\"A\",\"fecha_ingreso\":\"2026-06-23\",\"es_mayor_edad\":false,\"tiene_contacto\":false}'),
(101, '2026-06-25 14:08:48', 'ELIMINAR', 'estudiante', 18, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/estudiantes.php?success=eliminado&nombre=Oro%2C+Juan+Cruz\",\"request_method\":\"POST\",\"before\":{\"id\":18,\"dni\":\"51146430\",\"nombre\":\"Joaquin Nicolas\",\"apellido\":\"Rugna\",\"nombre_completo\":\"Rugna, Joaquin Nicolas\",\"fecha_nacimiento\":null,\"edad\":0,\"grupo_sanguineo\":null,\"obra_social\":null,\"domicilio\":null,\"telefono_fijo\":null,\"telefono_celular\":null,\"email\":null,\"curso_id\":7,\"activo\":true,\"dni_responsable\":\"51146430\",\"grupo_taller\":\"A\",\"fecha_ingreso\":\"2026-06-23\",\"es_mayor_edad\":false,\"tiene_contacto\":false}}'),
(102, '2026-06-25 14:08:48', 'ELIMINAR_ESTUDIANTE', 'estudiante', 18, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/estudiantes.php?success=eliminado&nombre=Oro%2C+Juan+Cruz\",\"request_method\":\"POST\",\"id\":18,\"dni\":\"51146430\",\"nombre\":\"Joaquin Nicolas\",\"apellido\":\"Rugna\",\"nombre_completo\":\"Rugna, Joaquin Nicolas\",\"fecha_nacimiento\":null,\"edad\":0,\"grupo_sanguineo\":null,\"obra_social\":null,\"domicilio\":null,\"telefono_fijo\":null,\"telefono_celular\":null,\"email\":null,\"curso_id\":7,\"activo\":true,\"dni_responsable\":\"51146430\",\"grupo_taller\":\"A\",\"fecha_ingreso\":\"2026-06-23\",\"es_mayor_edad\":false,\"tiene_contacto\":false}'),
(103, '2026-06-25 14:08:51', 'ELIMINAR', 'estudiante', 19, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/estudiantes.php?success=eliminado&nombre=Rugna%2C+Joaquin+Nicolas\",\"request_method\":\"POST\",\"before\":{\"id\":19,\"dni\":\"52605093\",\"nombre\":\"Joaquin Nahuel\",\"apellido\":\"Urrizaga\",\"nombre_completo\":\"Urrizaga, Joaquin Nahuel\",\"fecha_nacimiento\":null,\"edad\":0,\"grupo_sanguineo\":null,\"obra_social\":null,\"domicilio\":null,\"telefono_fijo\":null,\"telefono_celular\":null,\"email\":null,\"curso_id\":7,\"activo\":true,\"dni_responsable\":\"52605093\",\"grupo_taller\":\"A\",\"fecha_ingreso\":\"2026-06-23\",\"es_mayor_edad\":false,\"tiene_contacto\":false}}'),
(104, '2026-06-25 14:08:51', 'ELIMINAR_ESTUDIANTE', 'estudiante', 19, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/estudiantes.php?success=eliminado&nombre=Rugna%2C+Joaquin+Nicolas\",\"request_method\":\"POST\",\"id\":19,\"dni\":\"52605093\",\"nombre\":\"Joaquin Nahuel\",\"apellido\":\"Urrizaga\",\"nombre_completo\":\"Urrizaga, Joaquin Nahuel\",\"fecha_nacimiento\":null,\"edad\":0,\"grupo_sanguineo\":null,\"obra_social\":null,\"domicilio\":null,\"telefono_fijo\":null,\"telefono_celular\":null,\"email\":null,\"curso_id\":7,\"activo\":true,\"dni_responsable\":\"52605093\",\"grupo_taller\":\"A\",\"fecha_ingreso\":\"2026-06-23\",\"es_mayor_edad\":false,\"tiene_contacto\":false}'),
(105, '2026-06-25 14:08:56', 'ELIMINAR_PROFESOR', 'profesor', 5, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/profesores.php\",\"request_method\":\"POST\",\"id\":5,\"dni\":\"48678082\",\"nombre\":\"Lucas\",\"apellido\":\"Acosta\",\"nombre_completo\":\"Acosta, Lucas\",\"fecha_nacimiento\":\"1980-04-12\",\"edad\":46,\"domicilio\":\"tarantino\",\"telefono_fijo\":\"2235692071\",\"telefono_celular\":\"223-15-678901\",\"email\":\"lucas.acosta@email.com\",\"titulo\":\"Licensiado en Gestion\",\"especialidad\":null,\"fecha_ingreso\":\"2026-02-04\",\"activo\":true,\"tiene_especialidad\":false,\"tiene_contacto\":true}'),
(106, '2026-06-25 14:08:59', 'ELIMINAR_PROFESOR', 'profesor', 6, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/profesores.php?success=eliminado&nombre=Acosta%2C+Lucas\",\"request_method\":\"POST\",\"id\":6,\"dni\":\"48678088\",\"nombre\":\"Alan Ezequiel\",\"apellido\":\"Martínez\",\"nombre_completo\":\"Martínez, Alan Ezequiel\",\"fecha_nacimiento\":\"1995-04-12\",\"edad\":31,\"domicilio\":null,\"telefono_fijo\":\"2235692071\",\"telefono_celular\":\"223-15-678901\",\"email\":\"alanme317@gmail.com\",\"titulo\":\"Licensiado Matematica\",\"especialidad\":\"Técnico en Construcciones\",\"fecha_ingreso\":\"2026-05-12\",\"activo\":true,\"tiene_especialidad\":true,\"tiene_contacto\":true}'),
(107, '2026-06-25 14:16:29', 'CREAR', 'estudiante', 20, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/estudiantes.php?action=nuevo\",\"request_method\":\"POST\",\"after\":{\"id\":20,\"dni\":\"48678088\",\"nombre\":\"Alan Ezequiel\",\"apellido\":\"Martínez\",\"nombre_completo\":\"Martínez, Alan Ezequiel\",\"fecha_nacimiento\":\"2008-04-12\",\"edad\":18,\"grupo_sanguineo\":\"A+\",\"obra_social\":\"Ninguna\",\"domicilio\":\"Av. Tarantino 1931\",\"telefono_fijo\":\"2235693071\",\"telefono_celular\":\"2235693071\",\"email\":\"martinez08alan@gmail.com\",\"curso_id\":11,\"activo\":true,\"dni_responsable\":\"48678089\",\"grupo_taller\":\"A\",\"fecha_ingreso\":null,\"es_mayor_edad\":true,\"tiene_contacto\":true}}'),
(108, '2026-06-25 14:16:29', 'CREAR_ESTUDIANTE', 'estudiante', 20, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/estudiantes.php?action=nuevo\",\"request_method\":\"POST\",\"id\":20,\"dni\":\"48678088\",\"nombre\":\"Alan Ezequiel\",\"apellido\":\"Martínez\",\"nombre_completo\":\"Martínez, Alan Ezequiel\",\"fecha_nacimiento\":\"2008-04-12\",\"edad\":18,\"grupo_sanguineo\":\"A+\",\"obra_social\":\"Ninguna\",\"domicilio\":\"Av. Tarantino 1931\",\"telefono_fijo\":\"2235693071\",\"telefono_celular\":\"2235693071\",\"email\":\"martinez08alan@gmail.com\",\"curso_id\":11,\"activo\":true,\"dni_responsable\":\"48678089\",\"grupo_taller\":\"A\",\"fecha_ingreso\":null,\"es_mayor_edad\":true,\"tiene_contacto\":true}'),
(109, '2026-06-25 14:23:59', 'CREAR_PROFESOR', 'profesor', 7, '::1', 23, '{\"user_agent\":\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36\",\"request_uri\":\"/SistemaAdmin/profesores.php?action=nuevo\",\"request_method\":\"POST\",\"id\":7,\"dni\":\"38678088\",\"nombre\":\"Cristian\",\"apellido\":\"Vecchio\",\"nombre_completo\":\"Vecchio, Cristian\",\"fecha_nacimiento\":\"1995-04-12\",\"edad\":31,\"domicilio\":null,\"telefono_fijo\":\"2235692071\",\"telefono_celular\":\"2235693071\",\"email\":\"martinez08ezequiel@gmail.com\",\"titulo\":\"Licensiado en programacion\",\"especialidad\":null,\"fecha_ingreso\":\"2009-04-12\",\"activo\":true,\"tiene_especialidad\":false,\"tiene_contacto\":true}'),
(110, '2026-06-25 14:25:40', 'CREAR_MIEMBRO', 'equipo_directivo', 26, '::1', 23, '{\"apellido\":\"Cardozo\",\"nombre\":\"Ivan\",\"cargo\":\"preceptor\",\"curso_id\":11,\"usuario_generado\":\"preceptor1|393pR^_C}Nwj\"}');

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

INSERT INTO `logs_seguridad` (`id`, `timestamp`, `tipo`, `descripcion`, `ip`, `usuario_id`, `datos`) VALUES
(1, '2025-11-12 21:25:57', 'LOGIN_FAILED', 'Intento de login fallido', '::1', NULL, '{\"username\":\"preceptor1\",\"error\":\"Usuario o contrase\\u00f1a incorrectos\"}'),
(2, '2025-11-12 21:26:14', 'LOGIN_FAILED', 'Intento de login fallido', '::1', NULL, '{\"username\":\"preceptor_tarde\",\"error\":\"Usuario o contrase\\u00f1a incorrectos\"}'),
(3, '2025-11-12 21:26:16', 'LOGIN_FAILED', 'Intento de login fallido', '::1', NULL, '{\"username\":\"preceptor_tarde\",\"error\":\"Usuario o contrase\\u00f1a incorrectos\"}'),
(4, '2025-11-12 21:26:23', 'LOGIN_FAILED', 'Intento de login fallido', '::1', NULL, '{\"username\":\"vicedirector\",\"error\":\"Usuario o contrase\\u00f1a incorrectos\"}'),
(5, '2025-11-13 06:20:16', 'LOGIN_FAILED', 'Intento de login fallido', '::1', NULL, '{\"username\":\"director\",\"error\":\"Usuario o contrase\\u00f1a incorrectos\"}'),
(6, '2025-11-13 08:13:51', 'LOGIN_FAILED', 'Intento de login fallido', '::1', NULL, '{\"username\":\"secretario1\",\"error\":\"Sistema en mantenimiento. Solo el administrador puede acceder temporalmente.\"}'),
(7, '2025-11-13 08:14:00', 'MFA_FAILED', 'Código MFA inválido', '::1', NULL, '{\"username\":\"admin\",\"user_id\":1}'),
(8, '2026-03-23 00:19:51', 'LOGIN_FAILED', 'Intento de login fallido', '::1', NULL, '{\"username\":\"director\",\"error\":\"Usuario o contrase\\u00f1a incorrectos\"}'),
(9, '2026-03-23 00:19:57', 'LOGIN_FAILED', 'Intento de login fallido', '::1', NULL, '{\"username\":\"vicedirector\",\"error\":\"Usuario o contrase\\u00f1a incorrectos\"}'),
(10, '2026-03-28 20:37:05', 'LOGIN_FAILED', 'Intento de login fallido', '::1', NULL, '{\"username\":\"director\",\"error\":\"Usuario o contrase\\u00f1a incorrectos\"}'),
(11, '2026-03-28 20:37:21', 'MFA_FAILED', 'Código MFA inválido', '::1', NULL, '{\"username\":\"admin\",\"user_id\":1}'),
(12, '2026-03-28 20:38:28', 'LOGIN_FAILED', 'Intento de login fallido', '::1', NULL, '{\"username\":\"secretario4\",\"error\":\"Sistema en mantenimiento. Solo el administrador puede acceder temporalmente.\"}'),
(13, '2026-03-28 20:38:39', 'MFA_FAILED', 'Código MFA inválido', '::1', NULL, '{\"username\":\"admin\",\"user_id\":1}'),
(14, '2026-03-28 20:38:52', 'MFA_FAILED', 'Código MFA inválido', '::1', NULL, '{\"username\":\"admin\",\"user_id\":1}'),
(15, '2026-05-02 17:22:12', 'LOGIN_FAILED', 'Intento de login fallido', '::1', NULL, '{\"username\":\"admin\",\"error\":\"Usuario o contrase\\u00f1a incorrectos\"}'),
(16, '2026-05-02 17:22:27', 'LOGIN_FAILED', 'Intento de login fallido', '::1', NULL, '{\"username\":\"Elivaanperejil#software\",\"error\":\"Usuario o contrase\\u00f1a incorrectos\"}'),
(17, '2026-05-02 17:22:45', 'LOGIN_FAILED', 'Intento de login fallido', '::1', NULL, '{\"username\":\"Elivaanperejil#software\",\"error\":\"Usuario o contrase\\u00f1a incorrectos\"}'),
(18, '2026-05-02 17:23:03', 'LOGIN_FAILED', 'Intento de login fallido', '::1', NULL, '{\"username\":\"alandesalojasistema#tripode\",\"error\":\"Usuario o contrase\\u00f1a incorrectos\"}');

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
(1, 'Juan', NULL, 1, 1, NULL, 1, 0, '2025-11-04 13:05:01'),
(2, 'ewewew', NULL, NULL, 1, NULL, 1, 0, '2025-11-04 13:05:13'),
(3, 'Matematica', NULL, NULL, 1, NULL, 1, 0, '2025-11-12 18:37:51'),
(4, 'Programacion ', NULL, 1, 1, NULL, 1, 0, '2025-11-12 18:38:06'),
(5, 'Programacion ', NULL, 1, 1, NULL, 1, 0, '2025-11-12 18:38:32'),
(6, 'Literatura', NULL, NULL, 1, NULL, 1, 0, '2025-11-12 18:42:14'),
(7, 'Gestion Y Autogestion', NULL, NULL, 6, NULL, 1, 0, '2025-11-12 19:10:20'),
(8, 'MAMAMA', NULL, 4, 6, NULL, 1, 0, '2025-11-13 07:23:05'),
(9, 'MAMAMA', NULL, 4, 6, NULL, 1, 0, '2025-11-13 07:23:13'),
(10, 'w', NULL, 4, 1, NULL, 1, 0, '2025-11-13 07:24:36'),
(11, 'w', NULL, 4, 1, NULL, 1, 0, '2025-11-13 07:24:40'),
(12, 'MAMAMA', NULL, 4, 5, NULL, 1, 0, '2025-11-13 07:26:16'),
(13, 'Redes e Internet', NULL, 5, 7, NULL, 1, 1, '2026-06-25 17:21:50');

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
(1, 1, 1, 1, '2025-11-04 13:05:01'),
(2, 2, 1, 1, '2025-11-04 13:05:13'),
(3, 3, 2, 1, '2025-11-12 18:37:51'),
(5, 5, 2, 1, '2025-11-12 18:38:32'),
(6, 4, 3, 1, '2025-11-12 18:39:43'),
(10, 8, 3, 1, '2025-11-13 07:23:05'),
(11, 9, 3, 1, '2025-11-13 07:23:14'),
(12, 10, 4, 1, '2025-11-13 07:24:36'),
(13, 11, 4, 1, '2025-11-13 07:24:40'),
(14, 12, 10, 1, '2025-11-13 07:26:16'),
(15, 7, 4, 1, '2026-03-29 01:34:11'),
(16, 7, 3, 1, '2026-03-29 01:34:11'),
(17, 6, 4, 1, '2026-03-29 14:43:57'),
(18, 6, 3, 1, '2026-03-29 14:43:57'),
(19, 13, 11, 1, '2026-06-25 17:21:50');

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

INSERT INTO `preceptor_curso` (`id`, `equipo_directivo_id`, `curso_id`) VALUES
(1, 23, 4),
(4, 25, 3),
(3, 25, 4),
(5, 26, 11);

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

INSERT INTO `profesores` (`id`, `dni`, `apellido`, `nombre`, `fecha_nacimiento`, `domicilio`, `telefono_fijo`, `telefono_celular`, `email`, `titulo`, `telefono`, `especialidad_id`, `fecha_ingreso`, `activo`, `creado_en`, `actualizado_en`) VALUES
(7, '38678088', 'Vecchio', 'Cristian', '1995-04-12', NULL, '2235692071', '2235693071', 'martinez08ezequiel@gmail.com', 'Licensiado en programacion', NULL, 5, '2009-04-12', 1, '2026-06-25 17:23:59', '2026-06-25 17:23:59');

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

INSERT INTO `profesor_curso` (`id`, `profesor_id`, `curso_id`, `anio_academico`, `activo`, `creado_en`, `actualizado_en`) VALUES
(6, 7, 11, 2026, 1, '2026-06-25 17:28:49', '2026-06-25 17:28:49');

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

INSERT INTO `profesor_materia` (`id`, `profesor_id`, `materia_id`, `curso_id`, `anio_academico`, `grupo_taller`, `activo`, `creado_en`) VALUES
(3, 7, 13, 11, 2026, 'A', 1, '2026-06-25 17:28:56');

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

INSERT INTO `responsables` (`id`, `estudiante_id`, `nombre`, `apellido`, `dni`, `telefono_celular`, `email`, `parentesco`, `es_contacto_emergencia`, `creado_en`, `actualizado_en`) VALUES
(1, 1, 'Alan Ezequiel', 'Martínez', '48678088', '223 671-3071', 'martinez08alan@gmail.com', 'Padre', 0, '2025-10-22 13:06:00', '2025-10-22 13:06:00'),
(2, 1, 'Alan Ezequiel', 'Martínez', '48678088', '223 671-3071', 'martinez08alan@gmail.com', 'Padre', 0, '2025-10-22 13:07:14', '2025-10-22 13:07:14');

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

INSERT INTO `sesiones_usuarios` (`id`, `usuario_id`, `session_id`, `ip_address`, `user_agent`, `creado_en`, `ultima_actividad`, `activa`) VALUES
(1, 23, 'c1lupehmpmpitups2b2b501vii', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-22 02:47:47', '2026-06-22 05:26:49', 1),
(2, 23, '19pu37vca7gc8gu1inos0c72vu', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-22 16:32:33', '2026-06-22 16:46:50', 1),
(3, 23, '86amtd4463a5asbup1c749f6hv', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 03:00:23', '2026-06-23 03:01:26', 0),
(4, 23, '7hl24stfrr16jn4b0gvf89p3ui', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 03:14:10', '2026-06-23 03:41:37', 0),
(5, 23, 'a2jmhsit9bd9g4prnifa204f91', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-25 00:16:27', '2026-06-25 00:17:04', 0),
(6, 23, 'p1dm044ddi6gar7k4tlicq8pc5', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-25 16:29:57', '2026-06-25 17:25:50', 0),
(7, 26, 'd83v919ib6d0ok030k2o4cagom', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-25 17:26:11', '2026-06-25 17:26:56', 0),
(8, 23, 'h52omu9tnv0m3r1mubglinnq12', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-25 17:27:17', '2026-06-25 17:59:07', 1);

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

INSERT INTO `suplentes` (`id`, `dni`, `apellido`, `nombre`, `telefono_celular`, `email`, `especialidad`, `activo`, `creado_en`, `actualizado_en`) VALUES
(1, '2132144324', 'Martinze', 'Añlawe', '2131423423', 'rufehuerhu@gmail.com', 'PalabraporPalabra', 1, '2025-11-04 13:06:10', '2025-11-04 13:06:10'),
(2, '48678088', 'Martínez', 'MAMA', '223-15-678901', 'jess317077@gmail.com', 'Matematica', 1, '2026-03-29 01:40:40', '2026-03-29 01:40:40');

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
(1, 'Mañana', '08:00:00', '12:00:00', 1, '2025-09-29 07:18:31'),
(2, 'Tarde', '13:00:00', '17:00:00', 1, '2025-09-29 07:18:31'),
(3, 'Vespertino', '18:00:00', '22:00:00', 1, '2025-09-29 07:18:31');

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
(2, '12345678', 'García', 'María Elena', 'director@eest2.edu.ar', NULL, '$argon2id$v=19$m=65536,t=4,p=1$VkFPamd6OHBBcDhDSnlKSQ$gu2SKCr5VSzmYizkeTLUhT8SJ7mSy3h1uSku5J/PHN4', 1, 'directivo', 1, NULL, 0, NULL, '2025-09-29 07:18:31', '2025-09-29 07:18:31'),
(3, '87654321', 'López', 'Carlos Alberto', 'preceptor@eest2.edu.ar', NULL, '$argon2id$v=19$m=65536,t=4,p=1$VkFPamd6OHBBcDhDSnlKSQ$gu2SKCr5VSzmYizkeTLUhT8SJ7mSy3h1uSku5J/PHN4', 1, 'preceptor', 1, '2025-11-12 20:30:12', 0, NULL, '2025-09-29 07:18:31', '2025-11-12 20:30:12'),
(4, '11223344', 'Martínez', 'Ana Beatriz', 'secretario@eest2.edu.ar', NULL, '$argon2id$v=19$m=65536,t=4,p=1$VkFPamd6OHBBcDhDSnlKSQ$gu2SKCr5VSzmYizkeTLUhT8SJ7mSy3h1uSku5J/PHN4', 1, 'secretario', 1, NULL, 0, NULL, '2025-09-29 07:18:31', '2025-09-29 07:18:31'),
(9, 'secretario3', 'Martínez', 'Alan Ezequielzxz', 'lucas.acosta@email.com', NULL, '$argon2id$v=19$m=65536,t=4,p=1$YkFWTVhOSjFPMTAuWmU5UA$t37o5zfUdgUhWY0cuXFKqnJuQStpZh3B/VEslT9MSL4', 1, 'secretario', 0, NULL, 0, NULL, '2025-11-13 03:43:19', '2025-11-13 03:43:33'),
(21, '48678088', 'Martínez', 'Alan Ezequiel', 'alanme317@gmail.com', NULL, '$argon2id$v=19$m=65536,t=4,p=3$TUEvUUlTUXdRZXZzSld0Tg$6F4udllfAGIS8cBxAXs+DddEpZNnZsX3AEbjr3Aj5nc', 1, 'profesor', 1, '2026-03-29 14:45:20', 0, NULL, '2026-03-29 05:12:14', '2026-03-29 14:45:20'),
(22, 'Elivaanperejil#software', 'Admin', 'Usuario 1', NULL, NULL, '$2y$10$EplxkdiwmuFiOPZsxl/VEevYc5BCK2FQWerKJ69Z6HtvTn3DKfoZy', 0, 'admin', 1, NULL, 0, NULL, '2026-05-02 20:21:51', '2026-06-22 02:54:07'),
(23, 'alandesalojasistema#tripode', 'Admin', 'Usuario 2', NULL, NULL, '$argon2id$v=19$m=65536,t=4,p=3$TWVhSklWakFoc0dVU0xMSQ$FoaKhYFoCDpOBsA/H7DS0+8ha0lITetnmpGqQaDMfT8', 0, 'admin', 1, '2026-06-25 17:27:17', 0, NULL, '2026-05-02 20:21:51', '2026-06-25 17:27:17'),
(26, 'preceptor1', 'Cardozo', 'Ivan', 'juan@test.com', NULL, '$argon2id$v=19$m=65536,t=4,p=3$NVFKV3pZMExEejZ1QnM0Tw$4zgIcAT3cGMMM7YKAXk7GrvIvMsmfbbwFfVe08nJIr8', 0, 'preceptor', 1, '2026-06-25 17:26:11', 0, NULL, '2026-06-25 17:25:40', '2026-06-25 17:26:34');

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

INSERT INTO `usuarios_mfa_temporal` (`id`, `usuario_id`, `secreto`, `creado_en`) VALUES
(18, 23, 'ZKSG2QBHEIGAYLI6UL7VSXTW5U6IEKQPFQUSN2G6GUF5OPKLG7SQ', '2026-05-04 03:13:34');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `cursos`
--
ALTER TABLE `cursos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `equipo_directivo`
--
ALTER TABLE `equipo_directivo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT de la tabla `especialidades`
--
ALTER TABLE `especialidades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `estudiantes`
--
ALTER TABLE `estudiantes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `estudiante_materias_recursadas`
--
ALTER TABLE `estudiante_materias_recursadas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `horarios`
--
ALTER TABLE `horarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de la tabla `llamados_atencion`
--
ALTER TABLE `llamados_atencion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `logs_auditoria`
--
ALTER TABLE `logs_auditoria`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=111;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `logs_seguridad_avanzados`
--
ALTER TABLE `logs_seguridad_avanzados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `materias`
--
ALTER TABLE `materias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `materias_previas`
--
ALTER TABLE `materias_previas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `materia_curso`
--
ALTER TABLE `materia_curso`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de la tabla `metricas_sistema`
--
ALTER TABLE `metricas_sistema`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `notas`
--
ALTER TABLE `notas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `profesores`
--
ALTER TABLE `profesores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `profesor_curso`
--
ALTER TABLE `profesor_curso`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `profesor_materia`
--
ALTER TABLE `profesor_materia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `rbac_permissions`
--
ALTER TABLE `rbac_permissions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT de la tabla `responsables`
--
ALTER TABLE `responsables`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

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
