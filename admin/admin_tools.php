<?php 
// Herramientas Administrativas Avanzadas
require_once __DIR__ . '/../includes/sistema_admin_session.php';
require_once __DIR__ . '/../includes/sistema_admin_http.php';

use SistemaAdmin\Services\ServicioAutenticacion;
use SistemaAdmin\Services\PermissionService;
use SistemaAdmin\Services\SessionService;
use SistemaAdmin\Services\ValidationService;
use SistemaAdmin\Controllers\AdminToolsController;

$databaseAdapter = sistema_admin_db_adapter();
$servicioAutenticacion = new ServicioAutenticacion($databaseAdapter);
$usuario = $servicioAutenticacion->verificarSesion();

if (!$usuario) {
    header('Location: ' . sistema_admin_login_redirect_url());
    exit();
}

// Verificar permisos de administrador
$sessionService = new SessionService($databaseAdapter);
$permissionService = new PermissionService($databaseAdapter, $sessionService);

if (!$permissionService->tienePermiso('administrar_sistema')) {
    header('Location: ../index.php?error=unauthorized');
    exit();
}

$controller = new AdminToolsController($databaseAdapter);
$validationService = new ValidationService($databaseAdapter);

// Manejar acciones con validación
$action = $_GET['action'] ?? 'dashboard';

// Validar acción
if (!$validationService->validateAction($action)) {
    header('Location: ../index.php?error=invalid_action');
    exit();
}

$result = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? $action;
    
    // Validar acción POST
    if (!$validationService->validateAction($action)) {
        header('Location: ../index.php?error=invalid_action');
        exit();
    }
    
    // Rate limiting
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    if (!$validationService->checkRateLimit($action, $ip)) {
        $result = ['success' => false, 'mensaje' => 'Demasiadas solicitudes. Intenta más tarde.'];
    } else {
        switch ($action) {
            case 'crear_backup':
                $result = $controller->crearBackup();
                break;
            case 'restaurar_backup':
                $archivo = $validationService->sanitizeInput($_POST['archivo'] ?? '');
                $result = $controller->restaurarBackup($archivo);
                break;
            case 'eliminar_backup':
                $archivo = $validationService->sanitizeInput($_POST['archivo'] ?? '');
                $result = $controller->eliminarBackup($archivo);
                break;
            case 'actualizar_config':
                $config = [];
                if (isset($_POST['config']) && is_array($_POST['config'])) {
                    foreach ($_POST['config'] as $key => $value) {
                        $config[$validationService->sanitizeInput($key)] = $validationService->sanitizeInput($value);
                    }
                }
                $result = $controller->actualizarConfiguracion($config);
                break;
            case 'limpiar_cache':
                $result = $controller->limpiarCache();
                break;
            case 'optimizar_db':
                $result = $controller->optimizarBaseDatos();
                break;
            case 'revocar_sesion':
                $sessId = $validationService->sanitizeInput($_POST['session_id'] ?? '');
                $result = $controller->revocarSesion($sessId);
                break;
            case 'revocar_otras_sesiones':
                $result = $controller->revocarTodasLasSesionesExcepto(session_id());
                break;
            case 'limpiar_log':
                $tipo = $validationService->sanitizeInput($_POST['tipo'] ?? '');
                $result = $controller->limpiarLog($tipo);
                break;
        }
    }
}

if ($action === 'descargar_backup') {
    if (isset($_GET['token'])) {
        $controller->descargarBackupConToken($_GET['token']);
        exit;
    }
    header('Location: ../index.php?error=missing_token');
    exit();
}

$pageTitle = 'Herramientas Administrativas - Sistema E.E.S.T N°2';
$currentPage = 'admin_tools.php';
$bodyClass = 'admin-tools-page';

// Asegurar que la ruta del CSS sea correcta desde admin/
$GLOBALS['css_path'] = '../css/style.css';

sistema_admin_send_html_security_headers();
include __DIR__ . '/../includes/header.php';

// Generar token CSRF
$csrfToken = $validationService->generateCSRFToken();
?>
<?php $nonce = $GLOBALS['csp_nonce'] ?? ''; ?>
<script src="../js/admin_tools.js" nonce="<?php echo htmlspecialchars($nonce); ?>"></script>
<?php

// Obtener datos del dashboard
$dashboard = $controller->obtenerDashboard();
$metricas = $dashboard['data']['metricas'] ?? [];
$alertas = $dashboard['data']['alertas'] ?? [];
$backups = $dashboard['data']['backups_recientes'] ?? [];
$config = $dashboard['data']['configuracion'] ?? [];
$backupCron = $dashboard['data']['backup_cron'] ?? [];
$queueCron = $dashboard['data']['queue_cron'] ?? [];
$queueStats = $dashboard['data']['queue_stats'] ?? [];
?>

<style>
.admin-tools-container {
    padding: 2rem;
    background: #f8fafc;
    min-height: 100vh;
}

.section-header {
    background: white;
    padding: 2rem;
    border-radius: 12px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    border: 1px solid #e2e8f0;
    margin-bottom: 2rem;
}

.section-header h2 {
    margin: 0;
    font-size: 1.875rem;
    font-weight: 700;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.5rem 0;
}

.section-header h2 i {
    color: #3b82f6;
    font-size: 1.5rem;
}

.alert-banner {
    padding: 1rem;
    margin-bottom: 2rem;
    border-radius: 8px;
    display: flex;
    align-items: center;
    gap: 1rem;
}

.alert-banner.success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-banner.error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.tabs-container {
    margin-top: 2rem;
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    border: 1px solid #e2e8f0;
    overflow: hidden;
}

.tabs {
    display: flex;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    margin: 0;
    padding: 0;
}

.tab {
    flex: 1;
    padding: 1.25rem 1.5rem;
    background: none;
    border: none;
    cursor: pointer;
    font-size: 1rem;
    font-weight: 600;
    color: #64748b;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    position: relative;
    border-right: 1px solid #e2e8f0;
}

.tab:last-child {
    border-right: none;
}

.tab:hover {
    color: #3b82f6;
    background: #f1f5f9;
}

.tab.active {
    color: #3b82f6;
    background: white;
    box-shadow: 0 -2px 0 0 #3b82f6 inset;
}

.tab i {
    font-size: 1.125rem;
}

.tab-content {
    display: none;
    padding: 3rem;
    min-height: 400px;
}

.tab-content h3 {
    padding: 1rem 0;
    margin-bottom: 2rem;
    font-size: 1.5rem;
    font-weight: 600;
    color: #1e293b;
    border-bottom: 2px solid #e2e8f0;
}

.section-title {
    padding: 1.5rem 0;
    margin-bottom: 2rem;
    font-size: 1.5rem;
    font-weight: 600;
    color: #1e293b;
    border-bottom: 2px solid #e2e8f0;
}

.card-title {
    padding: 1rem 0;
    margin-bottom: 1.5rem;
    font-size: 1.25rem;
    font-weight: 600;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.tab-content.active {
    display: block;
    animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.metrics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.metric-card {
    background: white;
    padding: 2.5rem;
    border-radius: 12px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    border: 1px solid #e2e8f0;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.metric-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

.metric-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #3b82f6, #1d4ed8);
}

.metric-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1rem;
}

.metric-title {
    font-size: 0.875rem;
    color: var(--secondary-color);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 0.5rem 0;
    margin-bottom: 0.5rem;
}

.metric-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    position: relative;
    z-index: 2;
}

.metric-icon.primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
.metric-icon.success { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white; }
.metric-icon.warning { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; }
.metric-icon.info { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; }

.metric-value {
    font-size: 2rem;
    font-weight: 700;
    color: var(--text-color);
    margin-bottom: 0.5rem;
    padding: 0.5rem 0;
}

.metric-label {
    font-size: 0.875rem;
    color: var(--secondary-color);
    padding: 0.25rem 0;
}

.alerts-container {
    margin-bottom: 2rem;
    padding: 2rem;
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    border: 1px solid #e2e8f0;
}

.card {
    padding: 2rem;
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    border: 1px solid #e2e8f0;
    margin-bottom: 2rem;
}

.alert-item {
    padding: 1rem;
    margin-bottom: 0.5rem;
    border-radius: 6px;
    display: flex;
    align-items: center;
    gap: 1rem;
}

.alert-item.warning {
    background: #fff3cd;
    border-left: 4px solid #ffc107;
}

.alert-item.error {
    background: #f8d7da;
    border-left: 4px solid #dc3545;
}

.action-buttons {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
    margin-bottom: 2rem;
}

.btn-action {
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 0.875rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.3s;
}

.btn-action.primary {
    background: var(--primary-color);
    color: white;
}

.btn-action.primary:hover {
    background: var(--primary-hover, var(--primary-dark));
    color: #fff;
}

.btn-action.success {
    background: var(--success-color);
    color: white;
}

.btn-action.success:hover {
    opacity: 0.9;
}

.btn-action.danger {
    background: var(--danger-color);
    color: white;
}

.btn-action.danger:hover {
    opacity: 0.9;
}

.config-form {
    background: white;
    padding: 2rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.config-section {
    margin-bottom: 2rem;
}

.config-section-title {
    font-size: 1.125rem;
    font-weight: 600;
    margin-bottom: 1rem;
    color: var(--text-color);
}

.config-field {
    margin-bottom: 1.5rem;
}

.config-field label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: var(--text-color);
}

.config-field input,
.config-field select {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid var(--medium-gray);
    border-radius: 6px;
    font-size: 1rem;
}

.config-field small {
    display: block;
    margin-top: 0.25rem;
    color: var(--secondary-color);
    font-size: 0.875rem;
}

.backup-list {
    background: white;
    padding: 2rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.backup-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    border-bottom: 1px solid var(--medium-gray);
}

.backup-item:last-child {
    border-bottom: none;
}

.backup-info {
    flex: 1;
}

.backup-name {
    font-weight: 600;
    color: var(--text-color);
    margin-bottom: 0.25rem;
}

.backup-meta {
    font-size: 0.875rem;
    color: var(--secondary-color);
}

.backup-actions {
    display: flex;
    gap: 0.5rem;
}

.btn-small {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-small.download {
    background: var(--primary-color);
    color: white;
}

.btn-small.restore {
    background: var(--success-color);
    color: white;
}

.btn-small.delete {
    background: var(--danger-color, #dc2626);
    color: white;
}

form.backup-delete-form {
    display: inline;
    margin: 0;
}

.health-indicator {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 600;
}

.health-indicator.excelente {
    background: #d4edda;
    color: #155724;
}

.health-indicator.bueno {
    background: #d1ecf1;
    color: #0c5460;
}

.health-indicator.precaucion {
    background: #fff3cd;
    color: #856404;
}

.health-indicator.critico {
    background: #f8d7da;
    color: #721c24;
}

@media (max-width: 768px) {
    .metrics-grid {
        grid-template-columns: 1fr;
    }
    
    .tabs {
        overflow-x: auto;
    }
    
    .tab {
        white-space: nowrap;
    }
}
</style>

<div class="admin-tools-container">
    <div class="section-header">
        <h2><i class="fas fa-tools"></i> Herramientas Administrativas</h2>
        <p style="color: var(--secondary-color); margin-top: 0.5rem;">
            Panel de administración y mantenimiento del sistema
        </p>
    </div>

    <?php if ($result): ?>
        <div class="alert-banner <?php echo $result['success'] ? 'success' : 'error'; ?>">
            <i class="fas fa-<?php echo $result['success'] ? 'check-circle' : 'exclamation-circle'; ?>"></i>
            <span><?php echo htmlspecialchars($result['mensaje']); ?></span>
        </div>
    <?php endif; ?>

    <?php if (!empty($alertas)): ?>
        <div class="alerts-container card">
            <h3 class="card-title"><i class="fas fa-exclamation-triangle"></i> Alertas Activas</h3>
            <?php foreach ($alertas as $alerta): ?>
                <div class="alert-item <?php echo $alerta['tipo']; ?>">
                    <i class="fas fa-<?php echo $alerta['tipo'] === 'error' ? 'times-circle' : 'exclamation-triangle'; ?>"></i>
                    <div>
                        <strong><?php echo htmlspecialchars($alerta['mensaje']); ?></strong>
                        <?php if (isset($alerta['detalles'])): ?>
                            <br><small><?php echo htmlspecialchars($alerta['detalles']); ?></small>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="card" style="margin-bottom: 1.5rem; border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,.08);">
        <div class="card-header" style="padding: 1rem 1.25rem; border-bottom: 1px solid #e2e8f0;">
            <h3 class="card-title" style="margin: 0; font-size: 1.1rem;"><i class="fas fa-calendar-check"></i> Calendario escolar (cierre Feb/Mar)</h3>
        </div>
        <div class="card-body" style="padding: 1.25rem;">
            <p style="margin: 0 0 1rem; color: #64748b;">Definí la fecha límite del período de recuperación febrero/marzo por año lectivo. Usa el mismo criterio que el motor de estados de materias.</p>
            <a href="calendario_escolar.php" class="btn btn-primary"><i class="fas fa-external-link-alt"></i> Abrir configuración de calendario</a>
        </div>
    </div>

    <!-- Tabs -->
    <div class="tabs-container">
        <div class="tabs">
            <button class="tab active" data-tab="monitoring">
                <i class="fas fa-chart-line"></i> Monitoreo
            </button>
            <button class="tab" data-tab="backups">
                <i class="fas fa-database"></i> Backups
            </button>
            <button class="tab" data-tab="configuration">
                <i class="fas fa-cog"></i> Configuración
            </button>
            <button class="tab" data-tab="sessions">
                <i class="fas fa-user-shield"></i> Sesiones Activas
            </button>
            <button class="tab" data-tab="logs">
                <i class="fas fa-file-alt"></i> Visor de Logs
            </button>
            <button class="tab" data-tab="maintenance">
                <i class="fas fa-wrench"></i> Mantenimiento
            </button>
        </div>

        <!-- Tab: Monitoreo -->
        <div class="tab-content active" id="tab-monitoring">
            <h3 class="section-title">Estado del Sistema</h3>
            
            <div class="metrics-grid">
                <!-- Memoria PHP -->
                <div class="metric-card">
                    <div class="metric-header">
                        <span class="metric-title">Memoria PHP</span>
                        <div class="metric-icon primary">
                            <i class="fas fa-memory"></i>
                        </div>
                    </div>
                    <div class="metric-value">
                        <?php echo $metricas['sistema']['memoria_php']['uso_actual_formateado'] ?? 'N/A'; ?>
                    </div>
                    <div class="metric-label">
                        Pico: <?php echo $metricas['sistema']['memoria_php']['pico_maximo_formateado'] ?? 'N/A'; ?>
                    </div>
                </div>

                <!-- Base de Datos -->
                <div class="metric-card">
                    <div class="metric-header">
                        <span class="metric-title">Base de Datos</span>
                        <div class="metric-icon success">
                            <i class="fas fa-database"></i>
                        </div>
                    </div>
                    <div class="metric-value">
                        <?php echo $metricas['base_datos']['tamaño_db_formateado'] ?? 'N/A'; ?>
                    </div>
                    <div class="metric-label">
                        <?php echo $metricas['base_datos']['numero_tablas'] ?? 0; ?> tablas
                    </div>
                </div>

                <!-- Disco -->
                <?php if (isset($metricas['sistema']['espacio_disco']['disponible']) && $metricas['sistema']['espacio_disco']['disponible']): ?>
                <div class="metric-card">
                    <div class="metric-header">
                        <span class="metric-title">Espacio en Disco</span>
                        <div class="metric-icon warning">
                            <i class="fas fa-hdd"></i>
                        </div>
                    </div>
                    <div class="metric-value">
                        <?php echo round($metricas['sistema']['espacio_disco']['porcentaje_uso'], 1); ?>%
                    </div>
                    <div class="metric-label">
                        <?php echo $metricas['sistema']['espacio_disco']['libre_formateado']; ?> libre
                    </div>
                </div>
                <?php endif; ?>

                <!-- Usuarios Activos -->
                <div class="metric-card">
                    <div class="metric-header">
                        <span class="metric-title">Usuarios</span>
                        <div class="metric-icon info">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <div class="metric-value">
                        <?php echo $metricas['aplicacion']['usuarios']['sesiones_activas'] ?? 0; ?>
                    </div>
                    <div class="metric-label">Sesiones activas</div>
                </div>
            </div>

            <!-- Cola de trabajos -->
            <div class="card" style="margin-top: 2rem;">
                <h3 class="card-title"><i class="fas fa-tasks"></i> Cola de trabajos (asíncronos)</h3>
                <p style="color: var(--secondary-color); margin: 0 0 1rem 0; font-size: 0.9rem;">
                    Trabajos pesados se guardan en la base de datos y los procesa el cron HTTP. Cola por defecto: <code>default</code>.
                </p>
                <div class="metrics-grid" style="margin-bottom: 1rem;">
                    <div>
                        <strong>Pendientes (listos para ejecutar):</strong>
                        <span><?php echo (int) ($queueStats['pendientes'] ?? 0); ?></span>
                    </div>
                    <div>
                        <strong>Fallidos (últimos 7 días):</strong>
                        <span style="color: var(--danger-color);"><?php echo (int) ($queueStats['fallidos_7d'] ?? 0); ?></span>
                    </div>
                </div>
                <?php if (!empty($queueCron['path_con_token'])): ?>
                <div style="padding: 1rem; background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 8px;">
                    <h4 style="margin: 0 0 0.5rem 0; font-size: 1rem;">Cron del worker</h4>
                    <p style="margin: 0 0 0.5rem 0; font-size: 0.875rem; color: #475569;">
                        Programe cada 1–5 minutos (por ejemplo con <code>curl</code>). Parámetros opcionales: <code>queue=default</code>, <code>limit=10</code>.
                    </p>
                    <code style="display: block; word-break: break-all; font-size: 0.8rem; padding: 0.5rem; background: #fff; border-radius: 4px;">
                        <?php
                        $schemeQ = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                        $hostQ = $_SERVER['HTTP_HOST'] ?? 'localhost';
                        echo htmlspecialchars($schemeQ . '://' . $hostQ . ($queueCron['path_con_token'] ?? ''), ENT_QUOTES, 'UTF-8');
                        ?>
                    </code>
                </div>
                <?php endif; ?>
            </div>

            <!-- Salud del Sistema -->
            <div class="card">
                <h3 class="card-title">Salud del Sistema</h3>
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <span class="health-indicator <?php echo $metricas['rendimiento']['salud']['nivel'] ?? 'bueno'; ?>">
                        <i class="fas fa-heartbeat"></i>
                        <?php echo strtoupper($metricas['rendimiento']['salud']['nivel'] ?? 'Desconocido'); ?>
                    </span>
                    <span style="color: var(--secondary-color);">
                        Puntuación: <?php echo $metricas['rendimiento']['salud']['puntuacion'] ?? 0; ?>/100
                    </span>
                </div>
            </div>

            <!-- Seguridad -->
            <div class="card" style="margin-top: 2rem;">
                <h3 class="card-title"><i class="fas fa-shield-alt"></i> Estado de Seguridad</h3>
                <div class="metrics-grid">
                    <div>
                        <strong>Logins fallidos (24h):</strong>
                        <span style="color: var(--danger-color);">
                            <?php echo $metricas['seguridad']['logins_fallidos_24h'] ?? 0; ?>
                        </span>
                    </div>
                    <div>
                        <strong>Usuarios bloqueados:</strong>
                        <span><?php echo $metricas['seguridad']['usuarios_bloqueados'] ?? 0; ?></span>
                    </div>
                    <div>
                        <strong>Nivel de amenaza:</strong>
                        <span style="text-transform: uppercase; font-weight: 600;">
                            <?php echo $metricas['seguridad']['nivel_amenaza'] ?? 'bajo'; ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab: Backups -->
        <div class="tab-content" id="tab-backups">
            <h3 class="section-title">Gestión de Backups</h3>
            
            <div class="action-buttons">
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="action" value="crear_backup">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                    <button type="submit" class="btn-action primary">
                        <i class="fas fa-plus"></i>
                        Crear Backup Ahora
                    </button>
                </form>
            </div>

            <?php if (!empty($backupCron['path_con_token'])): ?>
            <div class="card" style="margin-bottom: 1.5rem; padding: 1rem 1.25rem; background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 8px;">
                <h4 style="margin: 0 0 0.5rem 0; font-size: 1rem;">Backup automático por cron</h4>
                <p style="margin: 0 0 0.5rem 0; font-size: 0.875rem; color: #475569;">
                    Si activó backups automáticos en Configuración, programe el sistema operativo para llamar a esta URL cada hora (el propio script solo ejecuta el backup cuando corresponde según frecuencia y hora).
                </p>
                <code style="display: block; word-break: break-all; font-size: 0.8rem; padding: 0.5rem; background: #fff; border-radius: 4px;">
                    <?php
                    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
                    echo htmlspecialchars($scheme . '://' . $host . ($backupCron['path_con_token'] ?? ''), ENT_QUOTES, 'UTF-8');
                    ?>
                </code>
            </div>
            <?php endif; ?>

            <div class="backup-list">
                <h4 style="margin-bottom: 1rem;">Backups Recientes</h4>
                <?php if (!empty($backups)): ?>
                    <?php foreach ($backups as $backup): ?>
                        <div class="backup-item">
                            <div class="backup-info">
                                <div class="backup-name">
                                    <i class="fas fa-file-archive"></i>
                                    <?php echo htmlspecialchars($backup['nombre']); ?>
                                </div>
                                <div class="backup-meta">
                                    <?php echo $backup['tamaño_formateado']; ?> • 
                                    <?php echo date('d/m/Y H:i', strtotime($backup['fecha'])); ?>
                                </div>
                            </div>
                            <div class="backup-actions">
                                <?php $token = $controller->getBackupDownloadToken($backup['nombre']); ?>
                                <a href="?action=descargar_backup&token=<?php echo urlencode($token); ?>" 
                                   class="btn-small download">
                                    <i class="fas fa-download"></i> Descargar
                                </a>
                                <form method="POST" class="backup-delete-form js-confirm-submit"
                                      data-confirm-message="<?php echo htmlspecialchars('¿Eliminar permanentemente este backup del servidor? No se puede deshacer.', ENT_QUOTES, 'UTF-8'); ?>">
                                    <input type="hidden" name="action" value="eliminar_backup">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                                    <input type="hidden" name="archivo" value="<?php echo htmlspecialchars($backup['nombre'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <button type="submit" class="btn-small delete" title="Eliminar backup">
                                        <i class="fas fa-trash-alt"></i> Borrar
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="color: var(--secondary-color); text-align: center; padding: 2rem;">
                        No hay backups disponibles
                    </p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Tab: Configuración -->
        <div class="tab-content" id="tab-configuration">
            <h3 class="section-title">Configuración del Sistema</h3>
            
            <form method="POST" class="config-form">
                <input type="hidden" name="action" value="actualizar_config">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                
                <?php foreach ($config as $categoria => $configs): ?>
                    <div class="config-section">
                        <h4 class="config-section-title">
                            <i class="fas fa-cog"></i> <?php echo ucfirst($categoria); ?>
                        </h4>
                        
                        <?php foreach ($configs as $clave => $data): ?>
                            <div class="config-field">
                                <label for="<?php echo htmlspecialchars($clave); ?>">
                                    <?php echo htmlspecialchars(ucwords(str_replace('_', ' ', explode('.', $clave)[1] ?? $clave))); ?>
                                </label>
                                
                                <?php if ($data['tipo'] === 'boolean'): ?>
                                    <select name="config[<?php echo htmlspecialchars($clave); ?>]" 
                                            id="<?php echo htmlspecialchars($clave); ?>">
                                        <option value="1" <?php echo $data['valor'] ? 'selected' : ''; ?>>Activado</option>
                                        <option value="0" <?php echo !$data['valor'] ? 'selected' : ''; ?>>Desactivado</option>
                                    </select>
                                <?php else: ?>
                                    <input type="<?php echo $data['tipo'] === 'number' ? 'number' : 'text'; ?>" 
                                           name="config[<?php echo htmlspecialchars($clave); ?>]"
                                           id="<?php echo htmlspecialchars($clave); ?>"
                                           value="<?php echo htmlspecialchars($data['valor']); ?>">
                                <?php endif; ?>
                                
                                <?php if (!empty($data['descripcion'])): ?>
                                    <small><?php echo htmlspecialchars($data['descripcion']); ?></small>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
                
                <div class="action-buttons">
                    <button type="submit" class="btn-action success">
                        <i class="fas fa-save"></i>
                        Guardar Cambios
                    </button>
                </div>
            </form>
        </div>

        <!-- Tab: Sesiones Activas -->
        <div class="tab-content" id="tab-sessions">
            <h3 class="section-title">Control de Sesiones Activas</h3>
            
            <div class="action-buttons" style="margin-bottom: 2rem;">
                <form method="POST" class="js-confirm-submit" style="display: inline;" data-confirm-message="¿Cerrar todas las demás sesiones de usuario activas en el sistema?">
                    <input type="hidden" name="action" value="revocar_otras_sesiones">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                    <button type="submit" class="btn-action danger">
                        <i class="fas fa-user-slash"></i>
                        Cerrar todas las demás sesiones
                    </button>
                </form>
            </div>

            <div class="card" style="padding: 1.5rem; overflow-x: auto;">
                <table class="table" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 2px solid #e2e8f0; text-align: left;">
                            <th style="padding: 0.75rem;">Usuario / DNI</th>
                            <th style="padding: 0.75rem;">Nombre Completo</th>
                            <th style="padding: 0.75rem;">Rol</th>
                            <th style="padding: 0.75rem;">Dirección IP</th>
                            <th style="padding: 0.75rem;">Dispositivo / Navegador</th>
                            <th style="padding: 0.75rem;">Inicio</th>
                            <th style="padding: 0.75rem;">Última Actividad</th>
                            <th style="padding: 0.75rem; text-align: center;">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $sesResp = $controller->obtenerSesionesActivasConDetalle();
                        $sesList = $sesResp['data'] ?? [];
                        $currentSessId = session_id();
                        if (!empty($sesList)): 
                            foreach ($sesList as $s): 
                                $isCurrent = ($s['session_id'] === $currentSessId);
                                $ua = htmlspecialchars($s['user_agent'] ?? '');
                                $uaShort = strlen($ua) > 40 ? substr($ua, 0, 37) . '...' : $ua;
                        ?>
                            <tr style="border-bottom: 1px solid #e2e8f0; vertical-align: middle;">
                                <td style="padding: 0.75rem;"><strong><?php echo htmlspecialchars($s['dni'] ?? 'N/A'); ?></strong></td>
                                <td style="padding: 0.75rem;"><?php echo htmlspecialchars(($s['nombre'] ?? '') . ' ' . ($s['apellido'] ?? '')); ?></td>
                                <td style="padding: 0.75rem;">
                                    <span class="badge" style="background: #e2e8f0; color: #475569; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem; text-transform: uppercase;">
                                        <?php echo htmlspecialchars($s['rol'] ?? 'N/A'); ?>
                                    </span>
                                </td>
                                <td style="padding: 0.75rem;"><code><?php echo htmlspecialchars($s['ip_address'] ?? 'N/A'); ?></code></td>
                                <td style="padding: 0.75rem;"><span title="<?php echo $ua; ?>" style="cursor: help; font-size: 0.85rem; color: #64748b;"><?php echo $uaShort; ?></span></td>
                                <td style="padding: 0.75rem; font-size: 0.85rem;"><?php echo date('d/m/Y H:i', strtotime($s['creado_en'])); ?></td>
                                <td style="padding: 0.75rem; font-size: 0.85rem;"><?php echo date('d/m/Y H:i', strtotime($s['ultima_actividad'])); ?></td>
                                <td style="padding: 0.75rem; text-align: center;">
                                    <?php if ($isCurrent): ?>
                                        <span style="color: #22c55e; font-weight: 600; font-size: 0.85rem;"><i class="fas fa-check-circle"></i> Sesión Actual</span>
                                    <?php else: ?>
                                        <form method="POST" class="js-confirm-submit" style="display: inline;" data-confirm-message="¿Cerrar forzosamente esta sesión de usuario?">
                                            <input type="hidden" name="action" value="revocar_sesion">
                                            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                            <input type="hidden" name="session_id" value="<?php echo htmlspecialchars($s['session_id']); ?>">
                                            <button type="submit" class="btn-small delete">
                                                <i class="fas fa-sign-out-alt"></i> Cerrar
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php 
                            endforeach; 
                        else: 
                        ?>
                            <tr>
                                <td colspan="8" style="padding: 2rem; text-align: center; color: #64748b;">No hay sesiones activas registradas.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab: Visor de Logs -->
        <div class="tab-content" id="tab-logs">
            <h3 class="section-title">Visor de Logs del Servidor</h3>
            
            <div class="card" style="padding: 1.5rem; margin-bottom: 2rem; background: #fff; border: 1px solid #e2e8f0; border-radius: 12px;">
                <div style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end;">
                    <div style="flex: 1; min-width: 200px;">
                        <label for="log-type" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Archivo de Log</label>
                        <select id="log-type" class="form-control" style="width: 100%; padding: 0.75rem; border: 1px solid #cbd5e1; border-radius: 6px;">
                            <option value="error">Errores del Sistema (error.log.php)</option>
                            <option value="security">Eventos de Seguridad (security.log.php)</option>
                            <option value="audit">Logs de Auditoría Legal (audit.log.php)</option>
                        </select>
                    </div>
                    
                    <div style="flex: 1; min-width: 200px;">
                        <label for="log-filter" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Buscar en logs</label>
                        <input type="text" id="log-filter" placeholder="Buscar texto, IP, usuario..." style="width: 100%; padding: 0.75rem; border: 1px solid #cbd5e1; border-radius: 6px;">
                    </div>
                    
                    <div style="width: 120px;">
                        <label for="log-limit" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Límite</label>
                        <select id="log-limit" class="form-control" style="width: 100%; padding: 0.75rem; border: 1px solid #cbd5e1; border-radius: 6px;">
                            <option value="50">50 líneas</option>
                            <option value="100" selected>100 líneas</option>
                            <option value="200">200 líneas</option>
                            <option value="500">500 líneas</option>
                        </select>
                    </div>
                    
                    <div>
                        <button type="button" id="btn-refresh-logs" class="btn-action primary" style="height: 46px;">
                            <i class="fas fa-sync-alt"></i> Recargar
                        </button>
                    </div>
                    
                    <div>
                        <form method="POST" id="form-clear-log" class="js-confirm-submit" style="display: inline;" data-confirm-message="¿Estás seguro de que quieres vaciar este archivo de log? Esta acción es irreversible.">
                            <input type="hidden" name="action" value="limpiar_log">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                            <input type="hidden" name="tipo" id="clear-log-type" value="error">
                            <button type="submit" id="btn-clear-log" class="btn-action danger" style="height: 46px;">
                                <i class="fas fa-trash-alt"></i> Vaciar Log
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="card" style="padding: 0; background: #0f172a; border-radius: 12px; overflow: hidden; border: 1px solid #1e293b; box-shadow: 0 4px 10px rgba(0,0,0,0.15);">
                <div style="padding: 0.75rem 1.5rem; background: #1e293b; color: #94a3b8; font-family: monospace; font-size: 0.85rem; border-bottom: 1px solid #334155; display: flex; justify-content: space-between; align-items: center;">
                    <span>CONTENIDO DEL LOG</span>
                    <span id="log-stats" style="color: #38bdf8;">Cargando...</span>
                </div>
                <div id="log-viewer-content" style="max-height: 600px; overflow-y: auto; padding: 1.5rem; font-family: 'Courier New', Courier, monospace; font-size: 0.9rem; line-height: 1.5; color: #e2e8f0;">
                    <!-- Los logs se cargarán dinámicamente -->
                </div>
            </div>
        </div>

        <!-- Tab: Mantenimiento -->
        <div class="tab-content" id="tab-maintenance">
            <h3 class="section-title">Tareas de Mantenimiento</h3>
            
            <div class="action-buttons">
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="action" value="limpiar_cache">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                    <button type="submit" class="btn-action primary">
                        <i class="fas fa-broom"></i>
                        Limpiar Caché
                    </button>
                </form>
                
                <form method="POST" style="display: inline;" class="js-confirm-submit" data-confirm-message="<?php echo htmlspecialchars('¿Optimizar la base de datos? Esto puede tomar varios minutos.', ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="action" value="optimizar_db">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                    <button type="submit" class="btn-action success">
                        <i class="fas fa-database"></i>
                        Optimizar Base de Datos
                    </button>
                </form>
            </div>

            <!-- Información del Sistema -->
            <div class="card">
                <h4 style="margin-bottom: 1rem;">Información del Sistema</h4>
                <table class="table">
                    <tr>
                        <td><strong>PHP Version:</strong></td>
                        <td><?php echo $metricas['sistema']['php_version'] ?? PHP_VERSION; ?></td>
                    </tr>
                    <tr>
                        <td><strong>Sistema Operativo:</strong></td>
                        <td><?php echo $metricas['sistema']['sistema_operativo'] ?? PHP_OS; ?></td>
                    </tr>
                    <tr>
                        <td><strong>Límite de Memoria:</strong></td>
                        <td><?php echo $metricas['sistema']['memoria_php']['limite'] ?? ini_get('memory_limit'); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Base de Datos:</strong></td>
                        <td><?php echo $metricas['base_datos']['estado'] ?? 'Operativo'; ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<script nonce="<?php echo htmlspecialchars($GLOBALS['csp_nonce'] ?? ''); ?>">
// Confirmación para acciones peligrosas
document.querySelectorAll('form[method="POST"]').forEach(form => {
    const action = form.querySelector('input[name="action"]')?.value;
    if (action === 'restaurar_backup') {
        form.addEventListener('submit', function(e) {
            if (!confirm('¿Estás seguro de que quieres restaurar este backup? Esta acción sobrescribirá los datos actuales.')) {
                e.preventDefault();
            }
        });
    }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
