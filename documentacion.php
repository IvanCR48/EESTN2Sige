<?php
if (!headers_sent()) {
    header('Content-Type: text/html; charset=UTF-8');
}
/**
 * Hub de Documentación Completa - Sistema Admin EEST2
 *
 * Este archivo proporciona acceso a toda la documentación del sistema
 * organizada por categorías para facilitar la navegación.
 */

require_once __DIR__ . '/includes/sistema_admin_http.php';
sistema_admin_send_html_security_headers();

$pageTitle = 'Documentación del Sistema - E.E.S.T N°2';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/documentacion.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="doc-page">
    <div class="doc-layout">
        <aside class="doc-sidebar" aria-label="Índice de documentación">
            <div class="doc-sidebar__brand">
                <p class="doc-sidebar__brand-title">Documentación</p>
                <p class="doc-sidebar__brand-sub">E.E.S.T. N°2 · Educación y Trabajo</p>
            </div>
            <p class="doc-sidebar__label">Ir a sección</p>
            <nav class="doc-sidebar__nav">
                <a href="#cat-guias-usuario"><i class="fas fa-users"></i> Guías de usuario</a>
                <a href="#cat-seguridad"><i class="fas fa-shield-alt"></i> Seguridad</a>
                <a href="#cat-despliegue"><i class="fas fa-rocket"></i> Despliegue e instalación</a>
                <a href="#cat-desarrollo"><i class="fas fa-code"></i> Desarrollo</a>
                <a href="#cat-funcionalidades"><i class="fas fa-graduation-cap"></i> Funcionalidades</a>
                <a href="#cat-soporte"><i class="fas fa-life-ring"></i> Soporte</a>
                <a href="#cat-legal"><i class="fas fa-gavel"></i> Legal</a>
                <a href="#cat-backup"><i class="fas fa-database"></i> Backup</a>
            </nav>
        </aside>

        <header class="doc-topbar" role="banner">
            <p class="doc-topbar__title">
                <i class="fas fa-book-open" aria-hidden="true"></i>
                Documentación del sistema
            </p>
            <div class="doc-topbar__actions">
                <a class="doc-topbar__link" href="public/inicio.php#acceso-familias"><i class="fas fa-users"></i> Familias</a>
                <a class="doc-topbar__link" href="public/inicio.php"><i class="fas fa-home"></i> Inicio</a>
                <a class="doc-topbar__btn" href="public/login.php"><i class="fas fa-sign-in-alt"></i> Acceso docentes</a>
            </div>
        </header>

        <div class="doc-main">
            <div class="doc-container">
                <div class="doc-header">
                    <div class="doc-header__text">
                        <h1><i class="fas fa-book" aria-hidden="true"></i> Documentación del Sistema</h1>
                        <p>Sistema Administrativo E.E.S.T N°2 "Educación y Trabajo"</p>
                    </div>
                </div>

                <div class="doc-nav">
                    <h2><i class="fas fa-list" aria-hidden="true"></i> Navegación por categorías</h2>

                    <div class="doc-categories">
                        <div class="doc-category" id="cat-guias-usuario">
                            <h3><i class="fas fa-users"></i> Guías de Usuario</h3>
                            <ul>
                                <li><a href="docs/documentacion_completa/guia_usuario.php"><i class="fas fa-user"></i> Guía de Usuario Completa</a></li>
                                <li><a href="docs/documentacion_completa/guia_admin.php"><i class="fas fa-user-shield"></i> Guía de Administrador</a></li>
                                <li><a href="docs/documentacion_completa/herramientas_admin.php"><i class="fas fa-tools"></i> Herramientas de Administración</a></li>
                                <li><a href="docs/documentacion_completa/sistema_completo.php"><i class="fas fa-cogs"></i> Sistema Completo</a></li>
                            </ul>
                        </div>

                        <div class="doc-category" id="cat-seguridad">
                            <h3><i class="fas fa-shield-alt"></i> Seguridad</h3>
                            <ul>
                                <li><a href="docs/documentacion_completa/seguridad.php"><i class="fas fa-lock"></i> Seguridad del Sistema</a></li>
                                <li><a href="docs/documentacion_completa/seguridad_unificada.php"><i class="fas fa-shield-alt"></i> Seguridad Unificada</a></li>
                                <li><a href="docs/documentacion_completa/autenticacion.php"><i class="fas fa-user-check"></i> Autenticación</a></li>
                                <li><a href="docs/documentacion_completa/privacidad.php"><i class="fas fa-user-secret"></i> Privacidad</a></li>
                            </ul>
                        </div>

                        <div class="doc-category" id="cat-despliegue">
                            <h3><i class="fas fa-rocket"></i> Despliegue e Instalación</h3>
                            <ul>
                                <li><a href="docs/documentacion_completa/instalacion.php"><i class="fas fa-download"></i> Instalación Completa</a></li>
                                <li><a href="docs/documentacion_completa/configuracion.php"><i class="fas fa-cog"></i> Configuración del Sistema</a></li>
                                <li><a href="docs/documentacion_completa/mantenimiento.php"><i class="fas fa-wrench"></i> Mantenimiento del Sistema</a></li>
                                <li><a href="docs/documentacion_completa/despliegue_unificado.php"><i class="fas fa-cloud"></i> Despliegue Unificado</a></li>
                            </ul>
                        </div>

                        <div class="doc-category" id="cat-desarrollo">
                            <h3><i class="fas fa-code"></i> Desarrollo</h3>
                            <ul>
                                <li><a href="docs/documentacion_completa/api.php"><i class="fas fa-plug"></i> Documentación API REST</a></li>
                                <li><a href="docs/documentacion_completa/desarrollo_avanzado.php"><i class="fas fa-code-branch"></i> Desarrollo</a></li>
                                <li><a href="docs/documentacion_completa/arquitectura.php"><i class="fas fa-sitemap"></i> Arquitectura</a></li>
                                <li><a href="docs/documentacion_completa/desarrollo.php"><i class="fas fa-laptop-code"></i> Guía de Desarrollo</a></li>
                            </ul>
                        </div>

                        <div class="doc-category" id="cat-funcionalidades">
                            <h3><i class="fas fa-graduation-cap"></i> Funcionalidades</h3>
                            <ul>
                                <li><a href="docs/documentacion_completa/estudiantes.php"><i class="fas fa-user-graduate"></i> Gestión de Estudiantes</a></li>
                                <li><a href="docs/documentacion_completa/profesores.php"><i class="fas fa-chalkboard-teacher"></i> Gestión de Profesores</a></li>
                                <li><a href="docs/documentacion_completa/reportes.php"><i class="fas fa-chart-bar"></i> Reportes</a></li>
                                <li><a href="docs/documentacion_completa/administracion.php"><i class="fas fa-clipboard-list"></i> Administración</a></li>
                            </ul>
                        </div>

                        <div class="doc-category" id="cat-soporte">
                            <h3><i class="fas fa-life-ring"></i> Soporte</h3>
                            <ul>
                                <li><a href="docs/documentacion_completa/faq.php"><i class="fas fa-question-circle"></i> Preguntas Frecuentes (FAQ)</a></li>
                                <li><a href="docs/documentacion_completa/troubleshooting.php"><i class="fas fa-bug"></i> Solución de Problemas</a></li>
                                <li><a href="docs/documentacion_completa/contacto.php"><i class="fas fa-envelope"></i> Contacto</a></li>
                                <li><a href="docs/documentacion_completa/changelog.php"><i class="fas fa-history"></i> Historial de Cambios</a></li>
                            </ul>
                        </div>

                        <div class="doc-category" id="cat-legal">
                            <h3><i class="fas fa-gavel"></i> Legal</h3>
                            <ul>
                                <li><a href="docs/documentacion_completa/licencia.php"><i class="fas fa-file-contract"></i> Licencia</a></li>
                                <li><a href="docs/documentacion_completa/privacidad.php"><i class="fas fa-shield-alt"></i> Política de Privacidad</a></li>
                                <li><a href="docs/documentacion_completa/auditoria.php"><i class="fas fa-search"></i> Auditoría</a></li>
                            </ul>
                        </div>

                        <div class="doc-category" id="cat-backup">
                            <h3><i class="fas fa-database"></i> Backup y Recuperación</h3>
                            <ul>
                                <li><a href="docs/documentacion_completa/backup.php"><i class="fas fa-save"></i> Sistema de Backup Completo</a></li>
                                <li><a href="docs/documentacion_completa/mantenimiento.php"><i class="fas fa-tools"></i> Mantenimiento</a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="doc-footer">
                    <p><i class="fas fa-info-circle" aria-hidden="true"></i> Esta documentación está organizada para facilitar el acceso a toda la información del sistema.</p>
                    <p>Para soporte técnico, consultá la sección de <a href="docs/documentacion_completa/contacto.php">Contacto</a>.</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
