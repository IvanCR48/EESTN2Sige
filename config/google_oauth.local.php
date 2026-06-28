<?php

/**
 * Configuración local OAuth Google (no versionar: ya está en .gitignore).
 *
 * PRODUCCIÓN: actualiza 'redirect_uri' con la URL real del servidor
 * Y registra esa misma URI en Google Cloud Console → Credenciales → URI de redireccionamiento.
 * También puedes establecer APP_URL en .env y usar el valor dinámicamente (ver abajo).
 *
 * @return array<string, string>
 */

// La redirect_uri debe coincidir EXACTAMENTE con la registrada en Google Cloud Console.
// Local:      http://localhost/SistemaAdmin/public/google_callback.php
// Producción: https://tecnica2.cabrasoft.org/public/google_callback.php
$appUrl = rtrim((string) ($_ENV['APP_URL'] ?? 'http://localhost/SistemaAdmin'), '/');

return [
    'client_id'     => '618786105278-n1jca42na1peskcb77hfhgc0g9uj9rv5.apps.googleusercontent.com',
    'client_secret' => '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',
    'redirect_uri'  => $appUrl . '/public/google_callback.php',
];
