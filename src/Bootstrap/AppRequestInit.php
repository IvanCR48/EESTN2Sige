<?php

declare(strict_types=1);

namespace SistemaAdmin\Bootstrap;

use SistemaAdmin\Contracts\DatabaseInterface;
use SistemaAdmin\Services\ConfigurationService;

/**
 * Aplica timezone, nombre del sistema y parámetros de sesión leídos de configuracion_sistema.
 * Debe ejecutarse justo después de obtener el DatabaseInterface (p. ej. en database_bootstrap).
 */
final class AppRequestInit
{
    private static bool $applied = false;

    public static function applyFromDatabase(DatabaseInterface $database): void
    {
        if (self::$applied) {
            return;
        }
        self::$applied = true;

        $config = new ConfigurationService($database);

        $tz = (string) $config->obtener('sistema.timezone', 'America/Argentina/Buenos_Aires');
        if ($tz !== '' && in_array($tz, timezone_identifiers_list(), true)) {
            date_default_timezone_set($tz);
        }

        $GLOBALS['SA_SYSTEM_NAME'] = (string) $config->obtener('sistema.nombre', 'Sistema Administrativo E.E.S.T N°2');

        $mins = (int) round((float) $config->obtener('seguridad.sesion_duracion', 480));
        if ($mins < 5) {
            $mins = 5;
        }
        if ($mins > 10080) {
            $mins = 10080;
        }
        $secs = $mins * 60;
        $GLOBALS['SA_SESSION_INACTIVITY_SECONDS'] = $secs;
        $GLOBALS['SA_SESSION_MAX_AGE_SECONDS'] = $secs;
    }

    /**
     * Llamar antes de session_start() la primera vez en la petición.
     */
    public static function configureSessionIni(DatabaseInterface $database): void
    {
        self::applyFromDatabase($database);

        $lifetime = (int) ($GLOBALS['SA_SESSION_INACTIVITY_SECONDS'] ?? 1800);
        if ($lifetime < 300) {
            $lifetime = 300;
        }

        ini_set('session.gc_maxlifetime', (string) $lifetime);
    }

    public static function systemName(): string
    {
        return (string) ($GLOBALS['SA_SYSTEM_NAME'] ?? 'Sistema Administrativo E.E.S.T N°2');
    }

    public static function sessionInactivitySeconds(): int
    {
        $s = (int) ($GLOBALS['SA_SESSION_INACTIVITY_SECONDS'] ?? 1800);

        return max(300, min(604800, $s));
    }
}
