<?php

declare(strict_types=1);

namespace SistemaAdmin\Controllers;

use SistemaAdmin\Contracts\DatabaseInterface;
use SistemaAdmin\Services\BaseService;
use SistemaAdmin\Services\ServicioMateriasPrevias;

/**
 * Orquesta HTTP/vista de materias previas (materias_previas.php).
 */
class MateriaPreviaController extends BaseService
{
    private ServicioMateriasPrevias $servicioMateriasPrevias;

    public function __construct(DatabaseInterface $database, ServicioMateriasPrevias $servicioMateriasPrevias)
    {
        parent::__construct($database);
        $this->servicioMateriasPrevias = $servicioMateriasPrevias;
    }

    /**
     * @param array<string, mixed> $post
     * @return array{success: bool, error: string}
     */
    public function procesarGuardarDesdePost(array $post): array
    {
        try {
            $cursoId = isset($post['curso_id']) ? (int) $post['curso_id'] : 0;
            $estudianteId = isset($post['estudiante_id']) ? (int) $post['estudiante_id'] : 0;
            $materiaId = isset($post['materia_id']) ? (int) $post['materia_id'] : 0;
            $anioPrevio = isset($post['anio_previo']) ? (int) $post['anio_previo'] : 0;
            $estado = isset($post['estado']) ? (string) $post['estado'] : 'pendiente';
            $obsRaw = isset($post['observaciones']) ? trim((string) $post['observaciones']) : '';
            $observaciones = $obsRaw === '' ? null : $obsRaw;

            $this->servicioMateriasPrevias->registrarPrevia(
                $cursoId,
                $estudianteId,
                $materiaId,
                $anioPrevio,
                $estado,
                $observaciones
            );

            return ['success' => true, 'error' => ''];
        } catch (\InvalidArgumentException $e) {
            return ['success' => false, 'error' => 'Error al registrar: ' . $e->getMessage()];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => 'Error al registrar: ' . $e->getMessage()];
        }
    }

    /**
     * @param array<string, mixed> $post
     * @return array{success: bool, error: string}
     */
    public function procesarEliminarDesdePost(array $post): array
    {
        try {
            $id = isset($post['previa_id']) ? (int) $post['previa_id'] : 0;
            $this->servicioMateriasPrevias->eliminarPrevia($id);

            return ['success' => true, 'error' => ''];
        } catch (\InvalidArgumentException $e) {
            return ['success' => false, 'error' => 'Error al eliminar: ' . $e->getMessage()];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => 'Error al eliminar: ' . $e->getMessage()];
        }
    }

    /**
     * @param array<string, mixed> $post
     * @return array{success: bool, error: string}
     */
    public function procesarAprobarDesdePost(array $post): array
    {
        try {
            $id = isset($post['previa_id']) ? (int) $post['previa_id'] : 0;
            $mes = isset($post['mes_aprobacion']) ? (string) $post['mes_aprobacion'] : 'Diciembre';
            $anio = isset($post['anio_aprobacion']) ? (int) $post['anio_aprobacion'] : (int)date('Y');
            $nota = isset($post['nota']) ? (int) $post['nota'] : 4;
            $this->servicioMateriasPrevias->aprobarPrevia($id, $mes, $anio, $nota);

            return ['success' => true, 'error' => ''];
        } catch (\InvalidArgumentException $e) {
            return ['success' => false, 'error' => 'Error al aprobar: ' . $e->getMessage()];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => 'Error al aprobar: ' . $e->getMessage()];
        }
    }

    /**
     * POST en materias_previas.php con PRG si tiene éxito.
     *
     * @param array<string, mixed> $post
     *
     * @return array{redirect: string|null, error: string, action: string|null}
     */
    public function procesarPostMateriasPrevias(array $post, string $cursoFilter, string $estudianteFilter): array
    {
        $sinCambio = ['redirect' => null, 'error' => '', 'action' => null];

        $redirectConExito = static function (string $success) use ($cursoFilter, $estudianteFilter): string {
            $q = ['success' => $success];
            if ($cursoFilter !== '') {
                $q['curso'] = $cursoFilter;
            }
            if ($estudianteFilter !== '') {
                $q['estudiante'] = $estudianteFilter;
            }

            return 'materias_previas.php?' . http_build_query($q);
        };

        if (isset($post['guardar_previa'])) {
            $r = $this->procesarGuardarDesdePost($post);
            if ($r['success']) {
                return ['redirect' => $redirectConExito('guardada'), 'error' => '', 'action' => null];
            }

            return ['redirect' => null, 'error' => $r['error'], 'action' => 'nueva'];
        }

        if (isset($post['eliminar_previa'])) {
            $r = $this->procesarEliminarDesdePost($post);
            if ($r['success']) {
                return ['redirect' => $redirectConExito('eliminada'), 'error' => '', 'action' => null];
            }

            return ['redirect' => null, 'error' => $r['error'], 'action' => null];
        }

        if (isset($post['aprobar_previa'])) {
            $r = $this->procesarAprobarDesdePost($post);
            if ($r['success']) {
                return ['redirect' => $redirectConExito('aprobada'), 'error' => '', 'action' => null];
            }

            return ['redirect' => null, 'error' => $r['error'], 'action' => null];
        }

        return $sinCambio;
    }

    /**
     * Etiqueta de curso para selects de filtro / listados (guión simple).
     *
     * @param array<string, mixed> $curso
     */
    public static function etiquetaCursoListado(array $curso): string
    {
        $anio = (string) ($curso['anio'] ?? '');
        $div = (string) ($curso['division'] ?? '');
        $esp = trim((string) ($curso['especialidad'] ?? ''));
        $line = $anio . '° ' . $div;
        if ($esp !== '') {
            return $line . ' - ' . $esp;
        }

        return $line;
    }

    /**
     * Etiqueta de curso para el alta de previa (raya larga antes de especialidad).
     *
     * @param array<string, mixed> $curso
     */
    public static function etiquetaCursoAltaPrevia(array $curso): string
    {
        $anio = (string) ($curso['anio'] ?? '');
        $div = (string) ($curso['division'] ?? '');
        $esp = trim((string) ($curso['especialidad'] ?? ''));
        $line = $anio . '° ' . $div;
        if ($esp !== '') {
            return $line . ' — ' . $esp;
        }

        return $line;
    }

    /**
     * @return array{class: string, text: string}
     */
    public static function estadoBadgeParaPrevia(string $estado): array
    {
        switch ($estado) {
            case 'pendiente':
                return ['class' => 'status status-warning', 'text' => 'Pendiente'];
            case 'regularizada':
                return ['class' => 'status status-info', 'text' => 'Regularizada'];
            case 'aprobada':
                return ['class' => 'status status-success', 'text' => 'Aprobada'];
            default:
                $t = trim($estado);

                return [
                    'class' => 'status',
                    'text' => $t === '' ? '—' : ucfirst($t),
                ];
        }
    }

    /**
     * @param array<string, mixed> $p fila de listarPreviasConFiltros
     *
     * @return array<string, mixed>
     */
    private function enriquecerFilaPrevia(array $p): array
    {
        $estado = (string) ($p['estado'] ?? '');
        $badge = self::estadoBadgeParaPrevia($estado);

        $anioAct = $p['anio_actual'] ?? null;
        $cursoPrincipal = '';
        $cursoEsp = null;
        if ($anioAct !== null && $anioAct !== '' && (int) $anioAct > 0) {
            $cursoPrincipal = (string) $anioAct . '° ' . (string) ($p['division'] ?? '');
            $esp = trim((string) ($p['especialidad'] ?? ''));
            $cursoEsp = $esp !== '' ? $esp : null;
        }

        return array_merge($p, [
            'vista_estado_class' => $badge['class'],
            'vista_estado_text' => $badge['text'],
            'vista_curso_principal' => $cursoPrincipal,
            'vista_curso_especialidad' => $cursoEsp,
            'vista_sin_curso' => $cursoPrincipal === '',
        ]);
    }

    /**
     * @param list<array<string, mixed>> $previas
     *
     * @return list<array<string, mixed>>
     */
    private function enriquecerPreviasParaVista(array $previas): array
    {
        return array_map(fn (array $p): array => $this->enriquecerFilaPrevia($p), $previas);
    }

    /**
     * @return array{
     *   cursos: list<array<string, mixed>>,
     *   estudiantes: list<array<string, mixed>>,
     *   estudiantes_filtro_lista: list<array<string, mixed>>,
     *   materias: list<array<string, mixed>>,
     *   estudiantes_js: list<array{id: int, curso_id: int|null, label: string}>,
     *   previas: list<array<string, mixed>>
     * }
     */
    public function datosVista(string $cursoFilter, string $estudianteFilter, int $page = 1, int $perPage = 20): array
    {
        $cursos = $this->servicioMateriasPrevias->listarCursosActivosParaSelect();
        $estudiantes = $this->servicioMateriasPrevias->listarEstudiantesActivosParaFiltros();

        $estudiantesFiltroLista = $estudiantes;
        if ($cursoFilter !== '') {
            $cid = $cursoFilter;
            $estudiantesFiltroLista = array_values(array_filter(
                $estudiantes,
                static function (array $e) use ($cid): bool {
                    return isset($e['curso_id']) && (string) $e['curso_id'] === $cid;
                }
            ));
        }

        $materias = $this->servicioMateriasPrevias->listarMateriasActivas();
        $estudiantesJs = $this->construirEstudiantesParaJs($estudiantes);
        $previasRaw = $this->servicioMateriasPrevias->listarPreviasConFiltros($cursoFilter, $estudianteFilter);
        $previasEnriquecidas = $this->enriquecerPreviasParaVista($previasRaw);

        $totalFiltrado = count($previasEnriquecidas);
        $paginationSvc = new \SistemaAdmin\Services\PaginationService($this->database);
        $meta = $paginationSvc->calculatePagination($totalFiltrado, $page, $perPage);
        $pageNumbers = $paginationSvc->getPageNumbers((int) $meta['total_pages'], (int) $meta['current_page'], 7);
        $pagination = array_merge($meta, ['page_numbers' => $pageNumbers]);

        $previasPaginadas = array_slice($previasEnriquecidas, (int) $meta['offset'], (int) $meta['page_size']);

        return [
            'cursos' => $cursos,
            'estudiantes' => $estudiantes,
            'estudiantes_filtro_lista' => $estudiantesFiltroLista,
            'materias' => $materias,
            'estudiantes_js' => $estudiantesJs,
            'previas' => $previasPaginadas,
            'total_filtrado' => $totalFiltrado,
            'pagination' => $pagination,
        ];
    }

    /**
     * @param list<array<string, mixed>> $estudiantes
     * @return list<array{id: int, curso_id: int|null, label: string}>
     */
    private function construirEstudiantesParaJs(array $estudiantes): array
    {
        return array_values(array_map(static function (array $e): array {
            $cursoId = $e['curso_id'] ?? null;

            return [
                'id' => (int) $e['id'],
                'curso_id' => ($cursoId !== null && $cursoId !== '') ? (int) $cursoId : null,
                'label' => $e['apellido'] . ', ' . $e['nombre'],
            ];
        }, $estudiantes));
    }
}
