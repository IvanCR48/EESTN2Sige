<?php

declare(strict_types=1);

namespace SistemaAdmin\Services;

use SistemaAdmin\Contracts\DatabaseInterface;
use SistemaAdmin\Mappers\NotaMapper;

/**
 * Registro, aprobación y listados de materias previas (materias_previas.php).
 */
class ServicioMateriasPrevias extends BaseService
{
    private const ESTADOS = ['pendiente', 'regularizada', 'aprobada'];

    public function __construct(DatabaseInterface $database)
    {
        parent::__construct($database);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listarCursosActivosParaSelect(): array
    {
        $sql = <<<'SQL'
            SELECT c.id, c.anio, c.division, esp.nombre AS especialidad
            FROM cursos c
            LEFT JOIN especialidades esp ON c.especialidad_id = esp.id
            WHERE c.activo = 1
            ORDER BY c.anio, c.division
            SQL;

        return $this->database->fetchAll($sql, []);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listarEstudiantesActivosParaFiltros(): array
    {
        $sql = <<<'SQL'
            SELECT e.id, e.apellido, e.nombre, e.curso_id, c.anio, c.division, esp.nombre AS especialidad
            FROM estudiantes e
            LEFT JOIN cursos c ON e.curso_id = c.id
            LEFT JOIN especialidades esp ON c.especialidad_id = esp.id
            WHERE e.activo = 1
            ORDER BY e.apellido, e.nombre
            SQL;

        return $this->database->fetchAll($sql, []);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listarMateriasActivas(): array
    {
        return $this->database->fetchAll(
            'SELECT * FROM materias WHERE activa = 1 ORDER BY nombre',
            []
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listarPreviasConFiltros(string $cursoFilter, string $estudianteFilter): array
    {
        $where = ['1=1'];
        $params = [];

        if ($cursoFilter !== '') {
            $where[] = 'e.curso_id = ?';
            $params[] = $cursoFilter;
        }

        if ($estudianteFilter !== '') {
            $where[] = 'e.id = ?';
            $params[] = $estudianteFilter;
        }

        $whereClause = implode(' AND ', $where);

        $sql = "
            SELECT p.*, e.apellido, e.nombre, m.nombre AS materia, c.anio AS anio_actual, c.division, esp.nombre AS especialidad
            FROM materias_previas p
            JOIN estudiantes e ON e.id = p.estudiante_id
            JOIN materias m ON m.id = p.materia_id
            LEFT JOIN cursos c ON e.curso_id = c.id
            LEFT JOIN especialidades esp ON c.especialidad_id = esp.id
            WHERE {$whereClause}
            ORDER BY e.apellido, e.nombre, p.anio_previo DESC
        ";

        return $this->database->fetchAll($sql, $params);
    }

    public function registrarPrevia(
        int $cursoId,
        int $estudianteId,
        int $materiaId,
        int $anioPrevio,
        string $estado,
        ?string $observaciones
    ): void {
        if ($cursoId < 1) {
            throw new \InvalidArgumentException('Debe seleccionar un curso');
        }
        if ($estudianteId < 1) {
            throw new \InvalidArgumentException('Debe seleccionar un estudiante');
        }
        if ($materiaId < 1) {
            throw new \InvalidArgumentException('Debe seleccionar una materia');
        }
        if ($anioPrevio < 1 || $anioPrevio > 7) {
            throw new \InvalidArgumentException('El año previo debe estar entre 1 y 7');
        }
        if (!in_array($estado, self::ESTADOS, true)) {
            throw new \InvalidArgumentException('Estado inválido');
        }

        $pertenece = $this->database->fetch(
            'SELECT id FROM estudiantes WHERE id = ? AND curso_id = ? AND activo = 1',
            [$estudianteId, $cursoId]
        );
        if ($pertenece === null) {
            throw new \InvalidArgumentException('El estudiante no pertenece al curso elegido');
        }

        $this->database->query(
            'INSERT INTO materias_previas (estudiante_id, materia_id, anio_previo, estado, observaciones) VALUES (?, ?, ?, ?, ?)',
            [$estudianteId, $materiaId, $anioPrevio, $estado, $observaciones]
        );

        $idRow = $this->database->fetch('SELECT LAST_INSERT_ID() AS id');
        $newId = (int) ($idRow['id'] ?? 0);
        if ($newId > 0) {
            $this->registrarAuditoria('CREAR', 'materia_previa', $newId, [
                'after' => [
                    'curso_id' => $cursoId,
                    'estudiante_id' => $estudianteId,
                    'materia_id' => $materiaId,
                    'anio_previo' => $anioPrevio,
                    'estado' => $estado,
                    'observaciones' => $observaciones,
                ],
            ]);
        }
    }

    public function eliminarPrevia(int $previaId): void
    {
        if ($previaId < 1) {
            throw new \InvalidArgumentException('Registro inválido');
        }

        $antes = $this->database->fetch('SELECT * FROM materias_previas WHERE id = ?', [$previaId]);
        $this->database->query('DELETE FROM materias_previas WHERE id = ?', [$previaId]);
        if ($antes !== null) {
            $this->registrarAuditoria('ELIMINAR', 'materia_previa', $previaId, ['before' => $antes]);
        }
    }

    public function aprobarPrevia(int $previaId, string $mes = 'Diciembre', int $anio = 0, int $nota = 4): void
    {
        if ($previaId < 1) {
            throw new \InvalidArgumentException('Registro inválido');
        }
        if (!in_array($mes, ['Diciembre', 'Febrero', 'Marzo'], true)) {
            throw new \InvalidArgumentException('Mes inválido');
        }
        if ($nota < 1 || $nota > 10) {
            throw new \InvalidArgumentException('Nota inválida');
        }
        if ($anio === 0) {
            $anio = (int)date('Y');
        }

        $this->database->transaction(function () use ($previaId, $mes, $anio, $nota): void {
            $previa = $this->database->fetch(
                <<<'SQL'
                SELECT p.*, e.apellido, e.nombre, m.nombre AS materia_nombre, c.anio AS anio_actual
                FROM materias_previas p
                JOIN estudiantes e ON e.id = p.estudiante_id
                JOIN materias m ON m.id = p.materia_id
                LEFT JOIN cursos c ON e.curso_id = c.id
                WHERE p.id = ?
                SQL,
                [$previaId]
            );

            if ($previa === null) {
                throw new \InvalidArgumentException('Materia previa no encontrada');
            }

            $antes = $previa;

            $this->database->query(
                'UPDATE materias_previas SET estado = ?, observaciones = ?, mes_aprobacion = ?, anio_aprobacion = ?, nota = ? WHERE id = ?',
                ['aprobada', "Aprobada ($mes $anio) con $nota", $mes, $anio, $nota, $previaId]
            );

            $despues = $this->database->fetch('SELECT * FROM materias_previas WHERE id = ?', [$previaId]);
            $this->registrarAuditoria('ACTUALIZAR', 'materia_previa', $previaId, [
                'before' => $antes,
                'after' => $despues ?? [],
            ]);
        });
    }

    /**
     * Aprobación administrativa desde la ficha del estudiante (sin mesa de examen ni nota mensual).
     * Deja mes_aprobacion y nota en NULL; documenta año calendario de registro.
     */
    public function aprobarPreviaAdministrativaDesdeFicha(int $previaId, int $estudianteIdEsperado): void
    {
        if ($previaId < 1 || $estudianteIdEsperado < 1) {
            throw new \InvalidArgumentException('Datos inválidos');
        }

        $previa = $this->database->fetch(
            'SELECT * FROM materias_previas WHERE id = ? AND estudiante_id = ?',
            [$previaId, $estudianteIdEsperado]
        );
        if ($previa === null) {
            throw new \InvalidArgumentException('Materia previa no encontrada para este estudiante');
        }

        $estado = strtolower((string) ($previa['estado'] ?? ''));
        if (!in_array($estado, ['pendiente', 'reprobada'], true)) {
            throw new \InvalidArgumentException('Solo se pueden confirmar previas pendientes o reprobadas.');
        }

        $anio = (int) date('Y');
        $antes = $previa;
        $this->database->query(
            <<<'SQL'
            UPDATE materias_previas
            SET estado = 'aprobada',
                observaciones = ?,
                mes_aprobacion = NULL,
                anio_aprobacion = ?,
                nota = NULL
            WHERE id = ? AND estudiante_id = ?
            SQL,
            [
                'Aprobación confirmada desde ficha (sin mesa de examen en el sistema).',
                $anio,
                $previaId,
                $estudianteIdEsperado,
            ]
        );

        $despues = $this->database->fetch('SELECT * FROM materias_previas WHERE id = ?', [$previaId]);
        $this->registrarAuditoria('ACTUALIZAR', 'materia_previa', $previaId, [
            'before' => $antes,
            'after' => $despues ?? [],
        ]);
    }
}
