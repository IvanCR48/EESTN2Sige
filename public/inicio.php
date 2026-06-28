<?php
declare(strict_types=1);

/**
 * Landing pública — Portal familias (acceso con DNI del responsable cargado en el sistema).
 */
require_once __DIR__ . '/../includes/sistema_admin_session.php';
require_once __DIR__ . '/../includes/sistema_admin_http.php';
require_once __DIR__ . '/../includes/csrf_functions.php';

sistema_admin_send_html_security_headers();

$csrfToken = getCSRFToken();
$familiaErrorRaw = filter_input(INPUT_GET, 'familia_error', FILTER_DEFAULT);
$familiaErrorMsg = '';
if (is_string($familiaErrorRaw)) {
    $familiaErrorMsg = mb_substr(trim($familiaErrorRaw), 0, 500);
}
$nonce = htmlspecialchars((string) ($GLOBALS['csp_nonce'] ?? ''), ENT_QUOTES, 'UTF-8');
$scriptName = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? '/public/inicio.php'));
$publicWeb = dirname($scriptName);
$appWebRoot = ($publicWeb !== '' && $publicWeb !== '/') ? dirname($publicWeb) : '';
if ($appWebRoot === '/' || $appWebRoot === '.') {
    $appWebRoot = '';
}
$href = static function (string $path) use ($appWebRoot): string {
    $base = $appWebRoot === '' ? '' : $appWebRoot;
    return htmlspecialchars($base . $path, ENT_QUOTES, 'UTF-8');
};
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Familias · E.E.S.T. N°2 "Educación y Trabajo"</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo $href('/css/inicio.css'); ?>">
</head>
<body id="acceso-familias">

<!-- ═══════════ PARTÍCULAS DE FONDO ═══════════ -->
<div class="particles" id="particles" aria-hidden="true"></div>

<!-- ═══════════ NAVBAR ═══════════ -->
<header class="navbar" id="navbar">
    <div class="navbar__inner">
        <a href="#inicio" class="navbar__brand">
            <img src="<?php echo $href('/img/logo-eest2.png'); ?>" alt="Logo E.E.S.T. N°2" class="navbar__logo">
            <div class="navbar__brand-text">
                <span class="navbar__name">E.E.S.T. N°2</span>
                <span class="navbar__sub">Educación y Trabajo</span>
            </div>
        </a>
        <nav class="navbar__links">
            <a href="#funciones">¿Qué podés ver?</a>
            <a href="#como-funciona">¿Cómo funciona?</a>
            <a href="#preguntas">Preguntas</a>
            <a href="<?php echo $href('/certificado_alumno_regular.php'); ?>"><i class="fas fa-certificate"></i> Certificado Alumno Regular</a>
        </nav>
        <button class="btn-hero btn-hero--outline btn-login-open" data-modal="modal-login">
            <i class="fas fa-sign-in-alt"></i> Acceder
        </button>
        <button class="navbar__hamburger" id="nav-hamburger" aria-label="Menú">
            <span></span><span></span><span></span>
        </button>
    </div>
    <nav class="navbar__mobile" id="navbar-mobile">
        <a href="#funciones">¿Qué podés ver?</a>
        <a href="#como-funciona">¿Cómo funciona?</a>
        <a href="#preguntas">Preguntas</a>
        <a href="<?php echo $href('/certificado_alumno_regular.php'); ?>"><i class="fas fa-certificate"></i> Certificado Alumno Regular</a>
        <button class="btn-hero btn-hero--outline btn-login-open" data-modal="modal-login">
            <i class="fas fa-sign-in-alt"></i> Acceder
        </button>
    </nav>
</header>

<!-- ═══════════ HERO ═══════════ -->
<section class="hero" id="inicio">
    <div class="hero__bg-shapes" aria-hidden="true">
        <div class="shape shape--1"></div>
        <div class="shape shape--2"></div>
        <div class="shape shape--3"></div>
        <div class="shape shape--4"></div>
    </div>
    <div class="hero__wrap">
    <div class="hero__content reveal">
        <div class="hero__tag">
            <i class="fas fa-star"></i> Portal de Familias
        </div>
        <h1 class="hero__title">
            Seguí de cerca<br>
            <span class="gradient-text">el recorrido escolar</span><br>
            de tu hijo
        </h1>
        <p class="hero__desc">
            Accedé a notas, asistencia, comunicados y mucho más desde cualquier dispositivo.
            La E.E.S.T. N°2 conecta a las familias con la vida escolar en tiempo real.
        </p>
        <div class="hero__actions">
            <button type="button" class="btn-hero btn-hero--primary btn-login-open" data-modal="modal-login">
                <i class="fas fa-sign-in-alt"></i> Ingresar con DNI
            </button>
        </div>
        <div class="hero__trust">
            <div class="trust-item"><i class="fas fa-shield-alt"></i> Datos protegidos</div>
            <div class="trust-sep">·</div>
            <div class="trust-item"><i class="fas fa-mobile-alt"></i> 100 % responsivo</div>
            <div class="trust-sep">·</div>
            <div class="trust-item"><i class="fas fa-lock"></i> Acceso seguro</div>
        </div>
    </div>
    <div class="hero__mockup reveal-right" aria-hidden="true">
        <div class="mockup-browser">
            <div class="mockup-bar">
                <span class="dot dot--red"></span>
                <span class="dot dot--yellow"></span>
                <span class="dot dot--green"></span>
                <span class="mockup-url"><i class="fas fa-lock"></i> portal.eest2.edu.ar</span>
            </div>
            <div class="mockup-screen mockup-screen--sistema">
                <?php /* Misma estructura que includes/header.php → familia-portal-header */ ?>
                <header class="mockup-familia-header" aria-hidden="true">
                    <div class="mockup-familia-header__top">
                        <div class="mockup-familia-logo-section">
                            <img src="<?php echo $href('/img/logo-eest2.png'); ?>" alt="" class="mockup-familia-logo" width="40" height="40" decoding="async">
                            <div class="mockup-familia-school-info">
                                <h1 class="mockup-familia-brand-title">Portal <span>Familias</span></h1>
                                <h2 class="mockup-familia-subtitle">E.E.S.T. N°2 "Educación y Trabajo"</h2>
                            </div>
                        </div>
                        <div class="mockup-familia-actions">
                            <span class="mockup-familia-link"><i class="fas fa-users" aria-hidden="true"></i> Elegir estudiante</span>
                            <span class="mockup-familia-link"><i class="fas fa-home" aria-hidden="true"></i> Inicio portal</span>
                            <span class="mockup-familia-logout" title="Salir del portal familias"><i class="fas fa-sign-out-alt" aria-hidden="true"></i></span>
                        </div>
                    </div>
                </header>
                <div class="mockup-sys-body">
                    <div class="mockup-ficha-bar">
                        <div>
                            <span class="mockup-ficha-bar__kicker">Ficha del estudiante</span>
                            <strong class="mockup-ficha-bar__nombre">García, Juan</strong>
                            <span class="mockup-ficha-bar__meta">3° 2° — Informática <small>(Turno Tarde)</small></span>
                            <span class="mockup-ficha-estado"><i class="fas fa-check-circle" aria-hidden="true"></i> Al día</span>
                        </div>
                    </div>
                    <div class="mockup-kpi-row">
                        <div class="mockup-kpi">
                            <span class="mockup-kpi__val">8,7</span>
                            <span class="mockup-kpi__lbl">Prom. general</span>
                        </div>
                        <div class="mockup-kpi">
                            <span class="mockup-kpi__val mockup-kpi__val--ok">95%</span>
                            <span class="mockup-kpi__lbl">Asistencia</span>
                        </div>
                        <div class="mockup-kpi">
                            <span class="mockup-kpi__val">12</span>
                            <span class="mockup-kpi__lbl">Materias cursando</span>
                        </div>
                    </div>
                    <div class="mockup-boletin">
                        <div class="mockup-boletin__title">📊 Boletín de Notas — 3° 2° - Informática</div>
                        <div class="mockup-boletin__frame">
                            <table class="mockup-boletin-table">
                                <thead>
                                    <tr>
                                        <th class="mockup-boletin-table__mat">Materia</th>
                                        <th>Av.&nbsp;1</th>
                                        <th>1°&nbsp;Trim.</th>
                                        <th>Av.&nbsp;2</th>
                                        <th>2°&nbsp;Trim.</th>
                                        <th class="mockup-boletin-table__prom">Promedio</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="mockup-boletin-table__matcell">Matemática</td>
                                        <td><span class="mockup-nota">—</span></td>
                                        <td><span class="mockup-nota mockup-nota--dest">9</span></td>
                                        <td><span class="mockup-nota">—</span></td>
                                        <td><span class="mockup-nota">—</span></td>
                                        <td class="mockup-boletin-table__promcell"><span class="mockup-prom">9</span></td>
                                        <td><span class="mockup-estado mockup-estado--ok">Aprobado</span></td>
                                    </tr>
                                    <tr>
                                        <td class="mockup-boletin-table__matcell">Lengua</td>
                                        <td><span class="mockup-nota">P</span></td>
                                        <td><span class="mockup-nota mockup-nota--dest">8</span></td>
                                        <td><span class="mockup-nota">—</span></td>
                                        <td><span class="mockup-nota">—</span></td>
                                        <td class="mockup-boletin-table__promcell"><span class="mockup-prom">8</span></td>
                                        <td><span class="mockup-estado mockup-estado--ok">Aprobado</span></td>
                                    </tr>
                                    <tr>
                                        <td class="mockup-boletin-table__matcell">Historia</td>
                                        <td><span class="mockup-nota">—</span></td>
                                        <td><span class="mockup-nota mockup-nota--dest mockup-nota--warn">6</span></td>
                                        <td><span class="mockup-nota">—</span></td>
                                        <td><span class="mockup-nota">—</span></td>
                                        <td class="mockup-boletin-table__promcell"><span class="mockup-prom mockup-prom--warn">6</span></td>
                                        <td><span class="mockup-estado mockup-estado--mid">En seguimiento</span></td>
                                    </tr>
                                    <tr>
                                        <td class="mockup-boletin-table__matcell">Física</td>
                                        <td><span class="mockup-nota">—</span></td>
                                        <td><span class="mockup-nota mockup-nota--dest">10</span></td>
                                        <td><span class="mockup-nota">—</span></td>
                                        <td><span class="mockup-nota">—</span></td>
                                        <td class="mockup-boletin-table__promcell"><span class="mockup-prom">10</span></td>
                                        <td><span class="mockup-estado mockup-estado--ok">Aprobado</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div><!-- /.hero__mockup -->
    </div><!-- /.hero__wrap -->
</section>

<!-- ═══════════ FUNCIONES ═══════════ -->
<section class="section funciones" id="funciones">
    <div class="container">
        <div class="section-label reveal">¿Qué podés ver?</div>
        <h2 class="section-title reveal">Todo lo que necesitás saber<br>de tu hijo en un solo lugar</h2>
        <div class="features-grid">
            <div class="feat-card reveal">
                <div class="feat-icon feat-icon--blue"><i class="fas fa-chart-line"></i></div>
                <h3>Notas y calificaciones</h3>
                <p>Consultá el rendimiento académico de tu hijo en cada materia, cuatrimestre y evaluación especial.</p>
            </div>
            <div class="feat-card reveal">
                <div class="feat-icon feat-icon--sky"><i class="fas fa-calendar-check"></i></div>
                <h3>Asistencia actualizada</h3>
                <p>Revisá presencias, ausencias y llegadas tarde al día, con notificaciones cuando sea necesario.</p>
            </div>
            <div class="feat-card reveal">
                <div class="feat-icon feat-icon--indigo"><i class="fas fa-bell"></i></div>
                <h3>Comunicados escolares</h3>
                <p>Recibí avisos importantes, llamados de atención y comunicados de la institución al instante.</p>
            </div>
            <div class="feat-card reveal">
                <div class="feat-icon feat-icon--teal"><i class="fas fa-clock"></i></div>
                <h3>Horarios de cursada</h3>
                <p>Accedé a la grilla semanal de materias, docentes y aulas de tu hijo desde cualquier dispositivo.</p>
            </div>
            <div class="feat-card reveal">
                <div class="feat-icon feat-icon--blue"><i class="fas fa-user-tie"></i></div>
                <h3>Equipo docente</h3>
                <p>Conocé el nombre y contacto de cada profesor, preceptor y directivo del curso.</p>
            </div>
            <div class="feat-card reveal">
                <div class="feat-icon feat-icon--sky"><i class="fas fa-file-alt"></i></div>
                <h3>Boletín digital</h3>
                <p>Descargá o imprimí el boletín de notas de cada cuatrimestre directamente desde el portal.</p>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════ CÓMO FUNCIONA ═══════════ -->
<section class="section como-funciona" id="como-funciona">
    <div class="container">
        <div class="section-label reveal">Fácil y rápido</div>
        <h2 class="section-title reveal">Tres pasos para estar siempre informado</h2>
        <div class="steps">
            <div class="step reveal">
                <div class="step-icon"><i class="fas fa-id-card"></i></div>
                <h3>DNI en la institución</h3>
                <p>Al dar de alta o actualizar la ficha, la escuela registra el DNI del padre, madre o tutor como responsable.</p>
            </div>
            <div class="step-arrow reveal" aria-hidden="true"><i class="fas fa-chevron-right"></i></div>
            <div class="step reveal">
                <div class="step-icon"><i class="fas fa-sign-in-alt"></i></div>
                <h3>Ingresá con ese DNI</h3>
                <p>En este portal solo hace falta el mismo número de documento del responsable, sin contraseña adicional ni registro previo.</p>
            </div>
            <div class="step-arrow reveal" aria-hidden="true"><i class="fas fa-chevron-right"></i></div>
            <div class="step reveal">
                <div class="step-icon"><i class="fas fa-eye"></i></div>
                <h3>Consultá la información</h3>
                <p>Visualizá la ficha del o los estudiantes vinculados, el boletín y el resto de datos habilitados para familias.</p>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════ STATS ═══════════ -->
<section class="stats-band reveal">
    <div class="container">
        <div class="stats-row">
            <div class="stat-band-item">
                <span class="stat-band-val" data-count="1200">0</span>
                <span class="stat-band-lbl">Estudiantes</span>
            </div>
            <div class="stat-band-item">
                <span class="stat-band-val" data-count="85">0</span>
                <span class="stat-band-lbl">Docentes</span>
            </div>
            <div class="stat-band-item">
                <span class="stat-band-val" data-count="32">0</span>
                <span class="stat-band-lbl">Cursos activos</span>
            </div>
            <div class="stat-band-item">
                <span class="stat-band-val" data-count="98">0</span>
                <span class="stat-band-lbl">% satisfacción</span>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════ TESTIMONIOS ═══════════ -->
<section class="section testimonios" id="testimonios">
    <div class="container">
        <div class="section-label reveal">Familias que ya lo usan</div>
        <h2 class="section-title reveal">Lo que dicen los padres</h2>
        <div class="testi-grid">
            <div class="testi-card reveal">
                <div class="testi-stars">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                </div>
                <p>"Antes tenía que llamar al colegio para saber cómo iba mi hijo. Ahora entro al portal y en segundos veo todo."</p>
                <div class="testi-author">
                    <div class="testi-avatar">ML</div>
                    <div>
                        <strong>María Laura S.</strong>
                        <span>Madre de alumno de 4° año</span>
                    </div>
                </div>
            </div>
            <div class="testi-card reveal">
                <div class="testi-stars">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                </div>
                <p>"El boletín digital es fantástico. Lo descargué directo desde el celular y lo mandé a los abuelos al toque."</p>
                <div class="testi-author">
                    <div class="testi-avatar">RG</div>
                    <div>
                        <strong>Roberto G.</strong>
                        <span>Padre de alumna de 2° año</span>
                    </div>
                </div>
            </div>
            <div class="testi-card reveal">
                <div class="testi-stars">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i>
                </div>
                <p>"Me avisaron del llamado de atención antes de que mi hijo llegara a casa. Muy útil para hacer seguimiento."</p>
                <div class="testi-author">
                    <div class="testi-avatar">CA</div>
                    <div>
                        <strong>Claudia A.</strong>
                        <span>Madre de alumno de 6° año</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════ FAQ ═══════════ -->
<section class="section faq" id="preguntas">
    <div class="container faq__inner">
        <div class="faq__side reveal">
            <div class="section-label">Preguntas frecuentes</div>
            <h2 class="section-title">¿Tenés dudas? Acá te respondemos</h2>
            <p>Si tu pregunta no está acá, podés escribirnos a la dirección del colegio o hablar con secretaría.</p>
        </div>
        <div class="faq__list">
            <details class="faq-item reveal">
                <summary>¿Necesito instalar algo en mi celular?</summary>
                <p>No. El portal funciona desde cualquier navegador: Chrome, Safari, Firefox, etc. Sólo necesitás internet.</p>
            </details>
            <details class="faq-item reveal">
                <summary>¿Mis datos están protegidos?</summary>
                <p>Sí. El sistema usa conexión cifrada (HTTPS) y tus datos nunca se comparten con terceros. Podés leer nuestra política de privacidad en la secretaría del colegio.</p>
            </details>
            <details class="faq-item reveal">
                <summary>¿Puedo ver los datos de mis hijos si tengo más de uno?</summary>
                <p>Sí. Si varios hijos comparten el mismo DNI de responsable en la base de datos, al ingresar podés elegir a cuál ver.</p>
            </details>
            <details class="faq-item reveal">
                <summary>¿Qué hago si no puedo ingresar?</summary>
                <p>Verificá que el DNI sea el mismo que figura como responsable en la ficha del estudiante. Si falta o está mal cargado, pedí la corrección en secretaría.</p>
            </details>
            <details class="faq-item reveal">
                <summary>¿Cuándo se actualizan las notas?</summary>
                <p>Las notas se publican en el portal a medida que los docentes las cargan, generalmente dentro de las 48 hs de la evaluación.</p>
            </details>
        </div>
    </div>
</section>

<!-- ═══════════ CTA FINAL ═══════════ -->
<section class="section cta-final reveal">
    <div class="cta-card">
        <div class="cta-deco" aria-hidden="true"></div>
        <h2>¿Listo para conectarte con<br>la escuela de tu hijo?</h2>
        <p>Ingresá con el DNI del responsable que tiene cargada la institución.</p>
        <button type="button" class="btn-hero btn-hero--primary btn-login-open" data-modal="modal-login">
            <i class="fas fa-sign-in-alt"></i> Acceder al portal
        </button>
    </div>
</section>

<!-- ═══════════ FOOTER ═══════════ -->
<footer class="landing-footer">
    <div class="container">
        <div class="footer-top">
            <div class="footer-brand">
                <img src="<?php echo $href('/img/logo-eest2.png'); ?>" alt="Logo" class="footer-logo">
                <div>
                    <strong>E.E.S.T. N°2 "Educación y Trabajo"</strong>
                    <span>Sistema de Gestión Educativa</span>
                </div>
            </div>
            <div class="footer-links">
                <a href="<?php echo $href('/public/login.php'); ?>"><i class="fas fa-sign-in-alt"></i> Acceso personal docente</a>
                <a href="<?php echo $href('/documentacion.php'); ?>"><i class="fas fa-book"></i> Documentación</a>
            </div>
        </div>
        <div class="footer-disclaimer" style="font-size: 0.72rem; color: rgba(255, 255, 255, 0.4); border-top: 1px solid rgba(255, 255, 255, 0.08); padding-top: 1rem; margin-top: 0.5rem; margin-bottom: 1.5rem; line-height: 1.5; text-align: justify;">
            <p style="margin: 0;"><i class="fas fa-info-circle" style="color: #38bdf8; margin-right: 0.35rem;"></i> <strong>Información importante sobre validez de datos:</strong> Los datos sobre calificaciones, asistencia y legajos visualizados a través de este portal digital son de carácter exclusivamente informativo. De acuerdo con las normativas escolares vigentes, la única documentación con validez legal y oficial es aquella firmada y sellada físicamente por las autoridades de la institución o emitida de forma presencial en la secretaría del establecimiento. Ante cualquier discrepancia entre los datos digitales y los registros en soporte físico (libros de actas y matrices), prevalecerán siempre los registros físicos oficiales.</p>
        </div>
        <div class="footer-bottom">
            <span>© <?php echo date('Y'); ?> E.E.S.T. N°2 · Todos los derechos reservados</span>
            <span>Hecho con <i class="fas fa-heart" style="color:#f87171;"></i> para las familias</span>
        </div>
    </div>
</footer>

<!-- ═══════════ MODAL ACCEDER (DNI responsable) ═══════════ -->
<div class="modal-overlay" id="modal-login" role="dialog" aria-modal="true" aria-labelledby="modal-login-title">
    <div class="modal-box">
        <button type="button" class="modal-close" data-close="modal-login" aria-label="Cerrar"><i class="fas fa-times"></i></button>
        <div class="modal-header">
            <div class="modal-icon modal-icon--blue"><i class="fas fa-sign-in-alt"></i></div>
            <h2 id="modal-login-title">Acceder al portal</h2>
            <p>Ingresá el DNI del padre, madre o tutor registrado como responsable en la institución (7 u 8 dígitos, sin puntos).</p>
        </div>
        <?php if ($familiaErrorMsg !== ''): ?>
        <div class="modal-alert modal-alert--error" role="alert"><?php echo htmlspecialchars($familiaErrorMsg, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        <form class="modal-form" id="form-login" method="post" action="familia_login.php" autocomplete="on">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
            <div class="modal-field">
                <label for="login-dni"><i class="fas fa-id-card"></i> DNI del responsable</label>
                <input type="text" id="login-dni" name="dni_responsable" inputmode="numeric" pattern="[0-9.\s-]{7,12}" maxlength="14" placeholder="Ej: 30123456" required autocomplete="off">
            </div>
            <button type="submit" class="btn-modal-submit">
                <i class="fas fa-sign-in-alt"></i> Entrar
            </button>
        </form>
        <div class="modal-separator"><span>o</span></div>
        <a href="<?php echo $href('/public/login.php'); ?>" class="btn-modal-staff">
            <i class="fas fa-user-shield"></i> Soy docente / personal del colegio
        </a>
        <div class="modal-disclaimer" style="margin-top: 1.25rem; font-size: 0.72rem; color: #64748b; text-align: center; line-height: 1.45; border-top: 1px dashed rgba(148, 163, 184, 0.25); padding-top: 0.85rem;">
            <p style="margin-bottom: 0.25rem; font-weight: 600; color: #2563eb; display: flex; align-items: center; justify-content: center; gap: 0.25rem;"><i class="fas fa-shield-alt"></i> Seguridad y Privacidad</p>
            <p style="margin: 0;">El ingreso debe realizarse exclusivamente con el DNI del responsable registrado (Ley N° 25.326). La información mostrada tiene carácter informativo y no oficial.</p>
        </div>
    </div>
</div>

<!-- ═══════════ TOAST ═══════════ -->
<div class="toast" id="toast" role="status" aria-live="polite"></div>

<script type="application/json" id="inicio-page-data"><?php echo json_encode([
    'familiaError' => $familiaErrorMsg,
    'openLoginModal' => $familiaErrorMsg !== '',
], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP); ?></script>

<script nonce="<?php echo $nonce; ?>">
/* ─── Partículas flotantes ─── */
(function () {
    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d');
    const container = document.getElementById('particles');
    container.appendChild(canvas);
    let W, H, particles;

    function resize() {
        W = canvas.width = window.innerWidth;
        H = canvas.height = window.innerHeight;
    }

    function init() {
        resize();
        particles = Array.from({ length: 55 }, () => ({
            x: Math.random() * W,
            y: Math.random() * H,
            r: Math.random() * 3 + 1,
            dx: (Math.random() - 0.5) * 0.4,
            dy: (Math.random() - 0.5) * 0.4,
            alpha: Math.random() * 0.4 + 0.1,
            color: ['#38bdf8', '#7dd3fc', '#bae6fd', '#60a5fa', '#93c5fd'][Math.floor(Math.random() * 5)],
        }));
    }

    function draw() {
        ctx.clearRect(0, 0, W, H);
        particles.forEach(p => {
            ctx.beginPath();
            ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
            ctx.fillStyle = p.color;
            ctx.globalAlpha = p.alpha;
            ctx.fill();
            p.x += p.dx;
            p.y += p.dy;
            if (p.x < 0 || p.x > W) p.dx *= -1;
            if (p.y < 0 || p.y > H) p.dy *= -1;
        });
        ctx.globalAlpha = 1;
        requestAnimationFrame(draw);
    }

    init();
    draw();
    window.addEventListener('resize', () => { resize(); });
})();

/* ─── Navbar scroll ─── */
window.addEventListener('scroll', () => {
    document.getElementById('navbar').classList.toggle('scrolled', window.scrollY > 40);
});

/* ─── Hamburger ─── */
document.getElementById('nav-hamburger').addEventListener('click', () => {
    document.getElementById('navbar-mobile').classList.toggle('open');
});

/* ─── Smooth scroll ─── */
document.querySelectorAll('a[href^="#"]').forEach(a => {
    a.addEventListener('click', e => {
        e.preventDefault();
        const t = document.querySelector(a.getAttribute('href'));
        if (t) t.scrollIntoView({ behavior: 'smooth', block: 'start' });
        document.getElementById('navbar-mobile').classList.remove('open');
    });
});

/* ─── Reveal on scroll ─── */
const observer = new IntersectionObserver(entries => {
    entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('visible'); observer.unobserve(e.target); } });
}, { threshold: 0.12 });
document.querySelectorAll('.reveal, .reveal-right').forEach(el => observer.observe(el));

/* ─── Modales ─── */
function openModal(id) {
    const m = document.getElementById(id);
    if (!m) return;
    m.classList.add('active');
    document.body.style.overflow = 'hidden';
}
function closeModal(id) {
    const m = document.getElementById(id);
    if (!m) return;
    m.classList.remove('active');
    document.body.style.overflow = '';
}

document.querySelectorAll('.btn-login-open').forEach(b => b.addEventListener('click', () => openModal(b.dataset.modal)));
document.querySelectorAll('.modal-close').forEach(b => b.addEventListener('click', () => closeModal(b.dataset.close)));
document.querySelectorAll('.modal-overlay').forEach(m => m.addEventListener('click', e => { if (e.target === m) closeModal(m.id); }));
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') document.querySelectorAll('.modal-overlay.active').forEach(m => closeModal(m.id));
});

/* ─── Hash #acceso-familias / #modal-login → modal de acceso ─── */
(function () {
    function openFromHash() {
        const raw = (location.hash || '').replace(/^#/, '').toLowerCase();
        if (raw === 'acceso-familias' || raw === 'modal-login' || raw === 'login-familias') {
            openModal('modal-login');
            history.replaceState(null, '', location.pathname + location.search);
        }
    }
    openFromHash();
    window.addEventListener('hashchange', openFromHash);
})();

(function () {
    var el = document.getElementById('inicio-page-data');
    if (!el || !el.textContent) return;
    try {
        var data = JSON.parse(el.textContent);
        if (data.openLoginModal) {
            openModal('modal-login');
            if (window.history && window.history.replaceState) {
                try {
                    window.history.replaceState(null, '', window.location.pathname + window.location.hash);
                } catch (e2) {}
            }
        }
    } catch (e) {}
})();

/* ─── Toast ─── */
function showToast(msg, type = 'success') {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.className = 'toast toast--' + type + ' show';
    setTimeout(() => t.classList.remove('show'), 4000);
}

/* ─── Contador animado ─── */
function animateCount(el, target, duration = 1800) {
    let start = 0;
    const step = (timestamp) => {
        if (!start) start = timestamp;
        const progress = Math.min((timestamp - start) / duration, 1);
        const ease = 1 - Math.pow(1 - progress, 3);
        el.textContent = Math.floor(ease * target).toLocaleString('es');
        if (progress < 1) requestAnimationFrame(step);
    };
    requestAnimationFrame(step);
}
const countObserver = new IntersectionObserver(entries => {
    entries.forEach(e => {
        if (e.isIntersecting) {
            const val = parseInt(e.target.dataset.count, 10);
            animateCount(e.target, val);
            countObserver.unobserve(e.target);
        }
    });
}, { threshold: 0.5 });
document.querySelectorAll('.stat-band-val').forEach(el => countObserver.observe(el));
</script>
</body>
</html>
