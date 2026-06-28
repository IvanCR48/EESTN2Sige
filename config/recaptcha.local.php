<?php

declare(strict_types=1);

/**
 * Claves privadas de Google reCAPTCHA v2 ("No soy un robot").
 * Este archivo NO se versiona (está en .gitignore).
 *
 * Obtener las claves en: https://www.google.com/recaptcha/admin
 *   - Tipo de reCAPTCHA: reCAPTCHA v2 → "No soy un robot"
 *   - Dominio permitido: localhost (desarrollo) y el dominio real en producción
 */

return [
    'site_key'   => '6LckMZwsAAAAALAMiCb2S8wjxP3cog1BAO5a8av0',
    'secret_key' => '6LckMZwsAAAAACVTGmKbovQo3cdfVI0jUD2VPGlU',
];
