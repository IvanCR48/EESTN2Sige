<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/sistema_admin_autoload.php';
sistema_admin_load_autoload();

$pageTitle = 'Certificado de alumno regular - Sistema Administrativo E.E.S.T N°2';

// Set a default nonce for inline scripts/styles if not already defined
$nonce = htmlspecialchars($GLOBALS['csp_nonce'] ?? '', ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="css/certificado_alumno.css">
    <style nonce="<?php echo $nonce; ?>">
        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: #f0f9ff;
            margin: 0;
            padding: 2rem;
            display: flex;
            justify-content: center;
        }
        .certificado-page-section {
            width: 100%;
            max-width: 1050px;
        }
        .btn-primary {
            background-color: #2563eb;
            color: #fff;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 6px;
            cursor: pointer;
            font-family: inherit;
            font-weight: 600;
            font-size: 1rem;
            transition: background-color 0.2s;
        }
        .btn-primary:hover {
            background-color: #1d4ed8;
        }
        .btn-secondary {
            background-color: #f1f5f9;
            color: #475569;
            border: 1px solid #cbd5e1;
            padding: 0.75rem 1.5rem;
            border-radius: 6px;
            cursor: pointer;
            font-family: inherit;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-block;
        }
        .btn-secondary:hover {
            background-color: #e2e8f0;
            color: #1e293b;
        }
        .actions-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        @media print {
            body {
                background: #fff;
                padding: 0;
            }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<section class="certificado-page-section">
    <div class="actions-bar no-print">
        <div>
            <h2 style="margin: 0; color: #0f172a; font-size: 1.5rem;">Certificado de alumno regular</h2>
            <p style="margin: 0.5rem 0 0; color: #64748b;">Complete los datos en la pantalla antes de imprimir.</p>
        </div>
        <div style="display: flex; gap: 1rem;">
            <a href="public/inicio.php" class="btn-secondary"><i class="fas fa-arrow-left"></i> Volver</a>
            <button type="button" class="btn-primary" id="btn-imprimir-certificado">
                <i class="fas fa-print"></i> Imprimir certificado
            </button>
        </div>
    </div>

    <div class="certificado-preview-wrap">
        <div id="certificado-print-area" class="certificado-papel" role="document" aria-label="Constancia de alumno regular">
            <header class="certificado-header">
                <h1>Constancia de alumno/a regular</h1>
                <p class="certificado-escuela">E.E.S.T. N° 2</p>
            </header>

            <div class="certificado-cuerpo">
                <div class="certificado-row">
                    <span class="cert-txt">Se hace constar que</span>
                    <input type="text" class="cert-input" style="flex: 1;" placeholder="Nombre y Apellido del alumno/a">
                </div>

                <p class="certificado-par">es alumno/a regular del Establecimiento y está matriculado en el presente</p>

                <div class="certificado-row">
                    <span class="cert-txt">curso escolar en</span>
                    <input type="text" class="cert-input" style="flex: 1;" placeholder="Año y División (ej: 3° 2°) - Especialidad">
                </div>

                <div class="certificado-row">
                    <span class="cert-txt">y concurre a clase en el turno</span>
                    <select class="cert-input cert-select" style="flex: 1;">
                        <option value="Mañana">Mañana</option>
                        <option value="Tarde">Tarde</option>
                        <option value="Vespertino">Vespertino</option>
                        <option value="Doble escolaridad">Doble escolaridad</option>
                    </select>
                </div>

                <p class="certificado-par">A pedido del interesado y al solo efecto de ser presentado ante las</p>

                <div class="certificado-row">
                    <span class="cert-txt">autoridades de</span>
                    <input type="text" class="cert-input" style="flex: 1;" value="E.E.S.T. N°2">
                </div>

                <div class="certificado-row">
                    <span class="cert-txt">se le extiende la presente constancia en</span>
                    <input type="text" class="cert-input" style="flex: 1;" value="Mar Del Plata">
                </div>

                <div class="certificado-row certificado-row-fecha">
                    <span class="cert-txt">a los</span>
                    <input type="text" class="cert-input" style="width: 50px; text-align: center;" value="<?php echo date('d'); ?>">
                    <span class="cert-txt">días del mes de</span>
                    <input type="text" class="cert-input" style="width: 120px; text-align: center;" value="<?php 
                        $meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
                        echo $meses[(int)date('n') - 1]; 
                    ?>">
                    <span class="cert-txt">de</span>
                    <input type="text" class="cert-input" style="width: 60px; text-align: center;" value="<?php echo date('Y'); ?>">
                </div>
            </div>

            <footer class="certificado-pie">
                <div class="certificado-sello">Sello</div>
                <div class="certificado-firma-bloque">
                    <span class="cert-rule" aria-hidden="true"></span>
                    <p class="cert-firma-label">Firma Registrada</p>
                </div>
            </footer>

            <div class="certificado-disclaimer" style="margin-top: 0.85rem; border-top: 1px dotted #000; padding-top: 0.4rem; font-size: 7.5pt; color: #555; text-align: center; font-style: italic; line-height: 1.3;">
                Esta constancia es de carácter puramente informativo. Carece de validez legal u oficial si no cuenta con la firma física autógrafa del directivo/secretario y el sello húmedo oficial de la E.E.S.T. N° 2 "Educación y Trabajo".
            </div>
        </div>
    </div>
</section>

<script nonce="<?php echo $nonce; ?>">
document.getElementById('btn-imprimir-certificado').addEventListener('click', function () {
    window.print();
});
</script>
</body>
</html>
