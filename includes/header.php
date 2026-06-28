<?php
require_once __DIR__ . '/preceptor_scope.php';
require_once __DIR__ . '/profesor_scope.php';
require_once __DIR__ . '/auth_helpers.php';
// Este header funciona con la nueva arquitectura de autenticación
// pero mantiene el diseño original
$currentUser = [
    'nombre' => $_SESSION['nombre'] ?? 'Usuario',
    'apellido' => $_SESSION['apellido'] ?? '',
    'rol' => $_SESSION['rol'] ?? 'usuario'
];
$currentPage = basename($_SERVER['PHP_SELF'] ?? '');

if (empty($GLOBALS['familia_portal_vista']) && ($_SESSION['rol'] ?? '') === 'profesor') {
    $paginasPermitidasProfesor = ['cursos.php', 'estudiantes.php', 'estudiante_ficha.php', 'horarios.php', 'notas.php'];
    $idFichaGet = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    $miProfesorId = function_exists('profesor_id_sesion') ? profesor_id_sesion() : null;
    if (
        $currentPage === 'profesor_ficha.php'
        && $idFichaGet !== false
        && $idFichaGet > 0
        && $miProfesorId !== null
        && $idFichaGet === $miProfesorId
    ) {
        $paginasPermitidasProfesor[] = 'profesor_ficha.php';
    }
    if (!in_array($currentPage, $paginasPermitidasProfesor, true)) {
        $redirProf = (strpos($_SERVER['SCRIPT_NAME'] ?? '', '/admin/') !== false) ? '../cursos.php' : 'cursos.php';
        if (!headers_sent()) {
            header('Location: ' . $redirProf);
            exit();
        }
    }
}

// Función para obtener la ruta base correcta
function getBasePath() {
    return (strpos($_SERVER['PHP_SELF'], '/admin/') !== false) ? '../' : '';
}

$familiaPortalHeader = !empty($GLOBALS['familia_portal_vista']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title><?php
        $saTitleDefault = class_exists(\SistemaAdmin\Bootstrap\AppRequestInit::class, false)
            ? \SistemaAdmin\Bootstrap\AppRequestInit::systemName()
            : 'Sistema Administrativo E.E.S.T N°2';
        echo htmlspecialchars($pageTitle ?? $saTitleDefault, ENT_QUOTES, 'UTF-8');
    ?></title>
    <link rel="stylesheet" href="<?php echo $GLOBALS['css_path'] ?? 'css/style.css'; ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <?php if (isset($GLOBALS['extra_css'])) echo $GLOBALS['extra_css']; ?>
<?php $nonce = $GLOBALS['csp_nonce'] ?? ''; ?>
<script src="<?php echo getBasePath(); ?>js/responsive.js" defer nonce="<?php echo htmlspecialchars($nonce); ?>"></script>
<script src="<?php echo getBasePath(); ?>js/csp-safe-handlers.js" defer nonce="<?php echo htmlspecialchars($nonce); ?>"></script>
</head>
<body<?php
$bodyClasses = [];
if (isset($bodyClass) && $bodyClass !== '') {
    $bodyClasses[] = $bodyClass;
}
if ($familiaPortalHeader) {
    $bodyClasses[] = 'portal-familia';
}
echo $bodyClasses !== [] ? ' class="' . htmlspecialchars(implode(' ', $bodyClasses), ENT_QUOTES, 'UTF-8') . '"' : '';
?>>
<?php if ($familiaPortalHeader): ?>
    <header class="main-header familia-portal-header">
        <div class="header-top">
            <div class="logo-section">
                <img src="<?php echo getBasePath(); ?>img/logo-eest2.png" alt="Logo EEST N°2" class="logo">
                <div class="school-info">
                    <h1 class="brand-title">Portal <span>Familias</span></h1>
                    <h2>E.E.S.T. N°2 "Educación y Trabajo"</h2>
                </div>
            </div>
            <div class="user-section familia-portal-header__actions">
                <?php if (!empty($familia_multihijo)): ?>
                <a href="<?php echo htmlspecialchars(getBasePath() . 'public/familia_seleccion.php', ENT_QUOTES, 'UTF-8'); ?>" class="familia-portal-header__link"><i class="fas fa-users"></i> Elegir estudiante</a>
                <?php endif; ?>
                <a href="<?php echo htmlspecialchars(getBasePath() . 'public/inicio.php', ENT_QUOTES, 'UTF-8'); ?>" class="familia-portal-header__link"><i class="fas fa-home"></i> Inicio portal</a>
                <a href="<?php echo htmlspecialchars(getBasePath() . 'public/familia_logout.php', ENT_QUOTES, 'UTF-8'); ?>" class="logout-btn" title="Salir del portal familias"><i class="fas fa-sign-out-alt"></i></a>
            </div>
        </div>
    </header>
    <div class="app-layout app-layout--familia-portal">
<?php else: ?>
    <header class="main-header">
        <div class="header-top">
            <div class="logo-section">
                <img src="<?php echo getBasePath(); ?>img/logo-eest2.png" alt="Logo EEST N°2" class="logo">
                <div class="school-info">
                    <h1 class="brand-title">Sistema <span>Administrativo</span></h1>
                    <h2>E.E.S.T. N°2 "Educación y Trabajo"</h2>
                </div>
            </div>
            <button class="hamburger" id="hamburger-menu" aria-label="Abrir menú" aria-expanded="false">
                <span></span>
                <span></span>
                <span></span>
            </button>
            <div class="user-section">
                <div class="user-info">
                    <i class="fas fa-user"></i>
                    <span><?php echo htmlspecialchars($currentUser['nombre'] . ' ' . $currentUser['apellido']); ?></span>
                    <span class="role">(<?php echo ucfirst($currentUser['rol']); ?>)</span>
                </div>
                <a href="<?php echo getBasePath(); ?>public/logout.php" class="logout-btn" title="Cerrar sesión">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </div>
        
        <!-- Navegación principal responsive -->
        <nav class="main-nav" id="main-nav">
            <ul class="nav-menu">
                <?php if (!hasRole('profesor')): ?>
                <li><a href="<?php echo getBasePath(); ?>index.php" class="nav-link <?php echo $currentPage==='index.php'?'active':''; ?>">
                    <i class="fas fa-home"></i><span>Dashboard</span>
                </a></li>
                <?php endif; ?>
                <li><a href="<?php echo getBasePath(); ?>estudiantes.php" class="nav-link <?php echo $currentPage==='estudiantes.php'?'active':''; ?>">
                    <i class="fas fa-users"></i><span>Estudiantes</span>
                </a></li>
                <?php if (!hasRole('profesor')): ?>
                <li><a href="<?php echo getBasePath(); ?>profesores.php" class="nav-link <?php echo $currentPage==='profesores.php'?'active':''; ?>">
                    <i class="fas fa-chalkboard-teacher"></i><span>Profesores</span>
                </a></li>
                <?php endif; ?>
                <?php if (puedeAccesoPestanaPreceptor()): ?>
                <li><a href="<?php echo getBasePath(); ?>preceptor.php" class="nav-link <?php echo $currentPage==='preceptor.php'?'active':''; ?>">
                    <i class="fas fa-user-graduate"></i><span>Preceptores</span>
                </a></li>
                <?php endif; ?>
                <li><a href="<?php echo getBasePath(); ?>cursos.php" class="nav-link <?php echo $currentPage==='cursos.php'?'active':''; ?>">
                    <i class="fas fa-graduation-cap"></i><span>Cursos</span>
                </a></li>
                <li><a href="<?php echo getBasePath(); ?>horarios.php" class="nav-link <?php echo $currentPage==='horarios.php'?'active':''; ?>">
                    <i class="fas fa-clock"></i><span>Horarios</span>
                </a></li>
                <?php if (hasRole(['admin', 'preceptor', 'directivo']) || puedeGestionarPreceptoresCurso()): ?>
                <li><a href="<?php echo getBasePath(); ?>asistencia_virtual.php" class="nav-link <?php echo $currentPage==='asistencia_virtual.php'?'active':''; ?>">
                    <i class="fas fa-calendar-check"></i><span>Asistencia</span>
                </a></li>
                <?php endif; ?>
                <?php if (hasRole('profesor') || hasRole('preceptor')): ?>
                <li><a href="<?php echo getBasePath(); ?>notas.php" class="nav-link <?php echo $currentPage==='notas.php'?'active':''; ?>">
                    <i class="fas fa-clipboard-check"></i><span>Notas</span>
                </a></li>
                <?php if (hasRole('preceptor')): ?>
                <li><a href="<?php echo getBasePath(); ?>admin/calendario_escolar.php" class="nav-link <?php echo $currentPage==='calendario_escolar.php'?'active':''; ?>">
                    <i class="fas fa-calendar-alt"></i><span>Calendario escolar</span>
                </a></li>
                <?php endif; ?>
                <?php endif; ?>
                <?php if (hasRole(['admin', 'directivo'])): ?>
                <li><a href="<?php echo getBasePath(); ?>notas.php" class="nav-link <?php echo $currentPage==='notas.php'?'active':''; ?>">
                    <i class="fas fa-clipboard-check"></i><span>Notas</span>
                </a></li>
                <li><a href="<?php echo getBasePath(); ?>materias.php" class="nav-link <?php echo $currentPage==='materias.php'?'active':''; ?>">
                    <i class="fas fa-book"></i><span>Materias</span>
                </a></li>
                <li><a href="<?php echo getBasePath(); ?>admin/calendario_escolar.php" class="nav-link <?php echo $currentPage==='calendario_escolar.php'?'active':''; ?>">
                    <i class="fas fa-calendar-alt"></i><span>Calendario escolar</span>
                </a></li>

                <?php endif; ?>
                <?php if (!hasRole('profesor')): ?>
                <li><a href="<?php echo getBasePath(); ?>documentacion.php?v=<?php echo time(); ?>" class="nav-link <?php echo $currentPage==='documentacion.php'?'active':''; ?>">
                    <i class="fas fa-book-open"></i><span>Documentación</span>
                </a></li>
                <?php endif; ?>
                <?php if (hasRole('admin')): ?>
                <li><a href="<?php echo getBasePath(); ?>usuarios.php" class="nav-link <?php echo $currentPage==='usuarios.php'?'active':''; ?>">
                    <i class="fas fa-users-cog"></i><span>Usuarios</span>
                </a></li>
                <li><a href="<?php echo getBasePath(); ?>admin/admin_tools.php" class="nav-link <?php echo $currentPage==='admin_tools.php'?'active':''; ?>">
                    <i class="fas fa-tools"></i><span>Admin</span>
                </a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <div class="app-layout">
        <div class="sidebar-backdrop" id="sidebar-backdrop" aria-hidden="true" hidden></div>
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-brand">
                <i class="fas fa-school"></i>
                <span>Escuela</span>
            </div>
            <nav class="sidebar-nav">
                <ul class="menu">
                    <?php if (!hasRole('profesor')): ?>
                    <li><a href="<?php echo getBasePath(); ?>index.php" class="menu-link <?php echo $currentPage==='index.php'?'active':''; ?>"><i class="fas fa-home"></i><span>Dashboard</span></a></li>
                    <?php endif; ?>
                    <li><a href="<?php echo getBasePath(); ?>estudiantes.php" class="menu-link <?php echo $currentPage==='estudiantes.php'?'active':''; ?>"><i class="fas fa-users"></i><span>Estudiantes</span></a></li>
                    <?php if (!hasRole('profesor')): ?>
                    <li><a href="<?php echo getBasePath(); ?>profesores.php" class="menu-link <?php echo $currentPage==='profesores.php'?'active':''; ?>"><i class="fas fa-chalkboard-teacher"></i><span>Profesores</span></a></li>
                    <?php endif; ?>
                    <?php if (puedeAccesoPestanaPreceptor()): ?>
                    <li><a href="<?php echo getBasePath(); ?>preceptor.php" class="menu-link <?php echo $currentPage==='preceptor.php'?'active':''; ?>"><i class="fas fa-user-graduate"></i><span>Preceptores</span></a></li>
                    <?php endif; ?>
                    <li><a href="<?php echo getBasePath(); ?>cursos.php" class="menu-link <?php echo $currentPage==='cursos.php'?'active':''; ?>"><i class="fas fa-graduation-cap"></i><span>Cursos</span></a></li>
                    <li><a href="<?php echo getBasePath(); ?>horarios.php" class="menu-link <?php echo $currentPage==='horarios.php'?'active':''; ?>"><i class="fas fa-clock"></i><span>Horarios</span></a></li>
                    <?php if (hasRole(['admin', 'preceptor', 'directivo']) || puedeGestionarPreceptoresCurso()): ?>
                    <li><a href="<?php echo getBasePath(); ?>asistencia_virtual.php" class="menu-link <?php echo $currentPage==='asistencia_virtual.php'?'active':''; ?>"><i class="fas fa-calendar-check"></i><span>Asistencia</span></a></li>
                    <?php endif; ?>
                    <?php if (hasRole('profesor') || hasRole('preceptor')): ?>
                    <li class="menu-section">Académico</li>
                    <li><a href="<?php echo getBasePath(); ?>notas.php" class="menu-link <?php echo $currentPage==='notas.php'?'active':''; ?>"><i class="fas fa-clipboard-check"></i><span>Notas</span></a></li>
                    <?php if (hasRole('preceptor')): ?>
                    <li><a href="<?php echo getBasePath(); ?>admin/calendario_escolar.php" class="menu-link <?php echo $currentPage==='calendario_escolar.php'?'active':''; ?>"><i class="fas fa-calendar-alt"></i><span>Calendario escolar</span></a></li>
                    <?php endif; ?>
                    <?php endif; ?>
                    <?php if (hasRole(['admin', 'directivo'])): ?>
                    <li class="menu-section">Académico</li>
                    <li><a href="<?php echo getBasePath(); ?>notas.php" class="menu-link <?php echo $currentPage==='notas.php'?'active':''; ?>"><i class="fas fa-clipboard-check"></i><span>Notas</span></a></li>
                    <li><a href="<?php echo getBasePath(); ?>materias.php" class="menu-link <?php echo $currentPage==='materias.php'?'active':''; ?>"><i class="fas fa-book"></i><span>Materias</span></a></li>
                    <li><a href="<?php echo getBasePath(); ?>admin/calendario_escolar.php" class="menu-link <?php echo $currentPage==='calendario_escolar.php'?'active':''; ?>"><i class="fas fa-calendar-alt"></i><span>Calendario escolar</span></a></li>
                    <li><a href="<?php echo getBasePath(); ?>materias_previas.php" class="menu-link <?php echo $currentPage==='materias_previas.php'?'active':''; ?>"><i class="fas fa-bookmark"></i><span>Materias Previas</span></a></li>
                    <li><a href="<?php echo getBasePath(); ?>especialidades.php" class="menu-link <?php echo $currentPage==='especialidades.php'?'active':''; ?>"><i class="fas fa-sitemap"></i><span>Especialidades</span></a></li>

                    <li><a href="<?php echo getBasePath(); ?>equipo.php" class="menu-link <?php echo $currentPage==='equipo.php'?'active':''; ?>"><i class="fas fa-user-tie"></i><span>Equipo Directivo</span></a></li>
                    

                    
                    <?php if (hasRole('admin')): ?>
                    <li class="menu-section">Administración</li>
                    <li><a href="<?php echo getBasePath(); ?>usuarios.php" class="menu-link <?php echo $currentPage==='usuarios.php'?'active':''; ?>"><i class="fas fa-users-cog"></i><span>Gestión de Usuarios</span></a></li>
                    <li><a href="<?php echo getBasePath(); ?>admin/admin_tools.php" class="menu-link <?php echo $currentPage==='admin_tools.php'?'active':''; ?>"><i class="fas fa-tools"></i><span>Herramientas Admin</span></a></li>
                    <?php endif; ?>
                    <?php endif; ?>

                    <li><a href="<?php echo getBasePath(); ?>public/logout.php" class="menu-link"><i class="fas fa-sign-out-alt"></i><span>Salir</span></a></li>
                </ul>
            </nav>
        </aside>
<?php endif; ?>
        <main class="main-content<?php echo (isset($mainContentExtraClass) && $mainContentExtraClass !== '') ? ' ' . htmlspecialchars($mainContentExtraClass, ENT_QUOTES, 'UTF-8') : ''; ?>">
            <div class="breadcrumb-bar">
                <div class="crumbs-left">
                    <i class="fas fa-home"></i>
                    <a href="<?php echo getBasePath() . (hasRole('profesor') ? 'cursos.php' : 'index.php'); ?>">Inicio</a>
                    <span>/</span>
                    <strong><?php echo htmlspecialchars($pageTitle ?? ''); ?></strong>
                </div>
                <div class="crumbs-right">
                    <i class="far fa-clock"></i>
                    <span id="clock-time"></span>
                    <i class="far fa-calendar-alt" style="margin-left:.75rem;"></i>
                    <span id="clock-date"></span>
                </div>
            </div>
        <script nonce="<?php echo htmlspecialchars($nonce); ?>">
        document.addEventListener('DOMContentLoaded', function() {
            var hamburger = document.getElementById('hamburger-menu');
            var sidebar = document.getElementById('sidebar');
            var backdrop = document.getElementById('sidebar-backdrop');
            function syncSidebarDrawer() {
                var open = sidebar && sidebar.classList.contains('open');
                var mq = window.matchMedia('(max-width: 992px)');
                if (sidebar) {
                    document.body.classList.toggle('sidebar-drawer-open', !!open && mq.matches);
                }
                if (backdrop) {
                    if (open && mq.matches) {
                        backdrop.hidden = false;
                        backdrop.setAttribute('aria-hidden', 'false');
                    } else {
                        backdrop.hidden = true;
                        backdrop.setAttribute('aria-hidden', 'true');
                    }
                }
                if (hamburger) {
                    hamburger.setAttribute('aria-expanded', open ? 'true' : 'false');
                }
                document.body.style.overflow = (open && mq.matches) ? 'hidden' : '';
            }
            window.__saSyncSidebarDrawer = syncSidebarDrawer;

            /* Tras navegar con “modo dispositivo” del inspector, a veces el primer pintado no aplica bien los @media hasta que hay un resize; esto lo fuerza sin que el usuario toque el toggle. */
            function refreshLayoutAfterPaint() {
                syncSidebarDrawer();
                requestAnimationFrame(function() {
                    requestAnimationFrame(function() {
                        syncSidebarDrawer();
                        try {
                            window.dispatchEvent(new Event('resize'));
                        } catch (e) {}
                    });
                });
            }

            if (hamburger && sidebar) {
                hamburger.addEventListener('click', function(e) {
                    e.stopPropagation();
                    sidebar.classList.toggle('open');
                    syncSidebarDrawer();
                });
                if (backdrop) {
                    backdrop.addEventListener('click', function(e) {
                        e.stopPropagation();
                        sidebar.classList.remove('open');
                        syncSidebarDrawer();
                    });
                }
                document.addEventListener('click', function(e) {
                    if (!sidebar.contains(e.target) && !hamburger.contains(e.target)) {
                        sidebar.classList.remove('open');
                        syncSidebarDrawer();
                    }
                    if (e.target.closest && e.target.closest('.menu-link')) {
                        sidebar.classList.remove('open');
                        syncSidebarDrawer();
                    }
                });
                window.addEventListener('resize', function() {
                    if (!window.matchMedia('(max-width: 992px)').matches) {
                        sidebar.classList.remove('open');
                    }
                    syncSidebarDrawer();
                });
                var mq992 = window.matchMedia('(max-width: 992px)');
                function onViewportBucketChange() {
                    if (!mq992.matches) {
                        sidebar.classList.remove('open');
                    }
                    refreshLayoutAfterPaint();
                }
                if (mq992.addEventListener) {
                    mq992.addEventListener('change', onViewportBucketChange);
                } else if (mq992.addListener) {
                    mq992.addListener(onViewportBucketChange);
                }
            }

            // Reloj local (hora del sistema del usuario)
            function updateClock() {
                try {
                    var now = new Date();
                    var time = new Intl.DateTimeFormat('es-AR', { hour: '2-digit', minute: '2-digit' }).format(now);
                    var date = new Intl.DateTimeFormat('es-AR', { day: '2-digit', month: 'short', year: 'numeric' }).format(now);
                    var tEl = document.getElementById('clock-time');
                    var dEl = document.getElementById('clock-date');
                    if (tEl) tEl.textContent = time;
                    if (dEl) dEl.textContent = date;
                } catch (e) {}
            }
            updateClock();
            setInterval(updateClock, 60000);

            refreshLayoutAfterPaint();
        });
        window.addEventListener('pageshow', function() {
            requestAnimationFrame(function() {
                requestAnimationFrame(function() {
                    if (typeof window.__saSyncSidebarDrawer === 'function') {
                        window.__saSyncSidebarDrawer();
                    }
                    try {
                        window.dispatchEvent(new Event('resize'));
                    } catch (e) {}
                });
            });
        });
        </script>

<?php
// Las funciones CSRF están definidas en includes/csrf_functions.php
?>