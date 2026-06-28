<?php
/**
 * Dashboard Principal del Sistema Administrativo
 * E.E.S.T. N°2 "Educación y Trabajo"
 */

require_once __DIR__ . '/includes/sistema_admin_session.php';
require_once __DIR__ . '/includes/sistema_admin_http.php';
require_once __DIR__ . '/includes/csrf_functions.php';
require_once __DIR__ . '/includes/auth_helpers.php';
require_once __DIR__ . '/includes/profesor_scope.php';

use SistemaAdmin\Controllers\DashboardController;
use SistemaAdmin\Services\ServicioAutenticacion;
use SistemaAdmin\Services\ServicioDashboardHome;
use SistemaAdmin\Services\ServicioEstudiantes;
use SistemaAdmin\Services\ServicioProfesores;
use SistemaAdmin\Services\BackupSchedulerService;

$databaseAdapter = sistema_admin_db_adapter();

$servicioAutenticacion = new ServicioAutenticacion($databaseAdapter);
$usuario = $servicioAutenticacion->verificarSesion();
if ($usuario === null) {
    header('Location: ' . sistema_admin_login_redirect_url());
    exit();
}

if (hasRole('profesor')) {
    header('Location: cursos.php');
    exit();
}

if (hasRole('admin')) {
    try {
        (new BackupSchedulerService($databaseAdapter))->runIfDue();
    } catch (\Throwable $e) {
        // backup automático no debe tumbar el dashboard
    }
}

require_once __DIR__ . '/includes/preceptor_scope.php';
$preceptor_cursos_index = preceptor_curso_ids();

$estudianteMapper = new \SistemaAdmin\Mappers\EstudianteMapper($databaseAdapter);
$profesorMapper = new \SistemaAdmin\Mappers\ProfesorMapper($databaseAdapter);

$servicioEstudiantes = new ServicioEstudiantes($databaseAdapter, $estudianteMapper);
$servicioProfesores = new ServicioProfesores($databaseAdapter, $profesorMapper);

$dashboardController = new DashboardController(
    $databaseAdapter,
    $servicioEstudiantes,
    $servicioProfesores,
    $servicioAutenticacion
);

$resultadoEstadisticas = $dashboardController->obtenerEstadisticas();
$servicioDashboardHome = new ServicioDashboardHome($databaseAdapter);
extract(
    $servicioDashboardHome->construirVistaIndex($resultadoEstadisticas, $preceptor_cursos_index),
    EXTR_SKIP
);

$ultimoAccesoEtiqueta = 'Sin registro previo';
$ultimoRaw = $usuario['ultimo_acceso'] ?? null;
if ($ultimoRaw !== null && $ultimoRaw !== '') {
    $ultimoTs = strtotime((string) $ultimoRaw);
    if ($ultimoTs !== false) {
        $ultimoAccesoEtiqueta = date('d/m/Y H:i', $ultimoTs);
    }
}

$currentPage = 'index.php';
$pageTitle = 'Dashboard - Sistema Administrativo E.E.S.T N°2';
$bodyClass = 'dashboard-page';

$GLOBALS['extra_css'] = '<link rel="stylesheet" href="css/dashboard_index.css">' . "\n";

sistema_admin_send_html_security_headers();
include 'includes/header.php';
?>
        <div class="container">
            <div class="dashboard-header">
                <h1><i class="fas fa-tachometer-alt"></i> Dashboard</h1>
                <p>Bienvenido, <?php echo htmlspecialchars(trim((string) ($usuario['nombre'] ?? '') . ' ' . (string) ($usuario['apellido'] ?? ''))); ?></p>
                <div class="user-info">
                    <span class="user-role">
                        <i class="fas fa-user-shield"></i>
                        <?php echo ucfirst(htmlspecialchars((string) ($usuario['rol'] ?? ''))); ?>
                    </span>
                    <span class="login-time">
                        <i class="fas fa-clock"></i>
                        Último acceso registrado: <?php echo htmlspecialchars($ultimoAccesoEtiqueta); ?>
                    </span>
                </div>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon primary">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo number_format($total_estudiantes_asignacion, 0, ',', '.'); ?></h3>
                        <p>Estudiantes</p>
                        <small>
                            <i class="fas fa-user-check text-success"></i>
                            Con curso
                        </small>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon success">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo number_format((int) ($estadisticas['total_profesores'] ?? 0), 0, ',', '.'); ?></h3>
                        <p>Profesores</p>
                        <small>
                            <i class="fas fa-check-circle text-success"></i>
                            <?php echo number_format((int) ($estadisticas['profesores_activos'] ?? 0), 0, ',', '.'); ?> activos
                        </small>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon warning">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo number_format($cursos_card_activos, 0, ',', '.'); ?></h3>
                        <p>Cursos activos</p>
                        <small>
                            <?php if ($cursos_card_subtitle_mode === 'bajas'): ?>
                            <i class="fas fa-archive text-warning"></i>
                            <?php echo htmlspecialchars($cursos_card_subtitle_text); ?>
                            <?php else: ?>
                            <i class="fas fa-info-circle text-info"></i>
                            <?php echo htmlspecialchars($cursos_card_subtitle_text); ?>
                            <?php endif; ?>
                        </small>
                    </div>
                </div>

            </div>

            <div
                id="sa-dashboard-charts"
                hidden
                data-labels-anio="<?php echo htmlspecialchars(json_encode($labels_anio, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8'); ?>"
                data-datos-anio="<?php echo htmlspecialchars(json_encode($datos_anio, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8'); ?>"
                data-est-con-curso="<?php echo (int) $estudiantes_con_curso; ?>"
                data-est-sin-curso="<?php echo (int) $estudiantes_sin_curso; ?>"
            ></div>

            <div class="charts-grid">
                <div class="chart-card">
                    <h3><i class="fas fa-chart-bar"></i> Distribución por Año</h3>
                    <canvas id="distribucionAnio"></canvas>
                </div>

                <div class="chart-card">
                    <h3><i class="fas fa-chart-pie"></i> Estado de Estudiantes</h3>
                    <canvas id="estadoEstudiantes"></canvas>
                </div>
            </div>

            <div class="quick-actions">
                <h3><i class="fas fa-bolt"></i> Accesos Rápidos</h3>
                <div class="actions-grid">
                    <a href="estudiantes.php" class="action-card">
                        <i class="fas fa-users"></i>
                        <span>Gestionar Estudiantes</span>
                    </a>
                    <a href="profesores.php" class="action-card">
                        <i class="fas fa-chalkboard-teacher"></i>
                        <span>Gestionar Profesores</span>
                    </a>
                    <a href="cursos.php" class="action-card">
                        <i class="fas fa-graduation-cap"></i>
                        <span>Gestionar Cursos</span>
                    </a>
                    <?php if (hasRole(['admin', 'directivo'])): ?>
                    <a href="notas.php" class="action-card">
                        <i class="fas fa-clipboard-check"></i>
                        <span>Gestionar Notas</span>
                    </a>
                    <a href="materias.php" class="action-card">
                        <i class="fas fa-book"></i>
                        <span>Gestionar Materias</span>
                    </a>
                    <?php endif; ?>
                    <?php if (hasRole('admin')): ?>
                    <a href="admin/admin_tools.php" class="action-card">
                        <i class="fas fa-tools"></i>
                        <span>Herramientas Admin</span>
                    </a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="recent-activity">
                <h3><i class="fas fa-history"></i> Actividad Reciente</h3>
                <div class="activity-list">
                    <?php if (!empty($estadisticas['actividad_reciente'])): ?>
                        <?php foreach ($estadisticas['actividad_reciente'] as $actividad): ?>
                        <?php
                            $iconoClase = htmlspecialchars((string) ($actividad['icono_clase'] ?? 'info'));
                            $icono      = htmlspecialchars((string) ($actividad['icono'] ?? 'history'));
                            $actor      = htmlspecialchars((string) ($actividad['actor'] ?? ''));
                            $rol        = htmlspecialchars((string) ($actividad['rol'] ?? ''));
                            $desc       = htmlspecialchars((string) ($actividad['descripcion'] ?? ''));
                            $fecha      = htmlspecialchars((string) ($actividad['fecha'] ?? ''));
                        ?>
                        <div class="activity-item">
                            <div class="activity-icon <?php echo $iconoClase; ?>">
                                <i class="fas fa-<?php echo $icono; ?>"></i>
                            </div>
                            <div class="activity-content">
                                <p class="activity-action"><?php echo $desc; ?></p>
                                <div class="activity-meta">
                                    <span class="activity-actor"><i class="fas fa-user-circle"></i> <?php echo $actor; ?></span>
                                    <?php if ($rol !== ''): ?>
                                    <span class="activity-rol"><?php echo $rol; ?></span>
                                    <?php endif; ?>
                                    <span class="activity-time"><i class="fas fa-clock"></i> <?php echo $fecha; ?></span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="no-activity">No hay actividad reciente</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

<?php
$nonce = htmlspecialchars((string) ($GLOBALS['csp_nonce'] ?? ''), ENT_QUOTES, 'UTF-8');
?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.6/dist/chart.umd.min.js" crossorigin="anonymous"></script>
<script src="js/dashboard_index.js" defer nonce="<?php echo $nonce; ?>"></script>
<?php include 'includes/footer.php'; ?>
