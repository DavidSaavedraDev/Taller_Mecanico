<?php

declare(strict_types=1);

require_once __DIR__ . '/../core/Database.php';

class OrdenTrabajo
{
    public const ESTADOS = [
        'Recepcionado',
        'Diagnóstico',
        'Esperando aprobación',
        'En reparación',
        'Pruebas',
        'Listo para entregar',
        'Entregado',
    ];

    private PDO $conn;

    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    public function obtenerTodos(): array
    {
        $stmt = $this->conn->query(
            'SELECT o.id, o.numero, o.estado, o.sintomas, o.creado_en, o.actualizado_en,
                    c.nombre AS cliente_nombre, v.placa, v.marca, v.modelo
             FROM ordenes_trabajo o
             INNER JOIN clientes c ON c.id = o.cliente_id
             INNER JOIN vehiculos v ON v.id = o.vehiculo_id
             ORDER BY o.id DESC'
        );

        return $stmt->fetchAll();
    }

    public function obtenerPorId(int $id): ?array
    {
        $stmt = $this->conn->prepare(
            'SELECT o.*, c.nombre AS cliente_nombre, c.documento AS cliente_documento,
                    c.telefono AS cliente_telefono, c.email AS cliente_email,
                    v.placa, v.marca, v.modelo, v.anio, v.kilometraje, v.vin
             FROM ordenes_trabajo o
             INNER JOIN clientes c ON c.id = o.cliente_id
             INNER JOIN vehiculos v ON v.id = o.vehiculo_id
             WHERE o.id = :id
             LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $orden = $stmt->fetch();

        return $orden ?: null;
    }

    public function obtenerHistorial(int $ordenId): array
    {
        $stmt = $this->conn->prepare(
            'SELECT h.estado, h.comentario, h.creado_en, u.nombre AS usuario_nombre
             FROM orden_estado_historial h
             LEFT JOIN usuarios u ON u.id = h.usuario_id
             WHERE h.orden_id = :orden_id
             ORDER BY h.id DESC'
        );
        $stmt->execute([':orden_id' => $ordenId]);

        return $stmt->fetchAll();
    }

    public function obtenerFotos(int $ordenId): array
    {
        $stmt = $this->conn->prepare(
            'SELECT id, ruta, nombre_original, creado_en
             FROM vehiculo_fotos
             WHERE orden_id = :orden_id
             ORDER BY id ASC'
        );
        $stmt->execute([':orden_id' => $ordenId]);

        return $stmt->fetchAll();
    }

    /**
     * @return array{orden_id:int, vehiculo_id:int}
     */
    public function crear(array $datos): array
    {
        $this->conn->beginTransaction();

        try {
            $vehiculo = $this->buscarVehiculoPorPlaca($datos['placa']);

            if ($vehiculo) {
                $vehiculoId = (int) $vehiculo['id'];
                $stmt = $this->conn->prepare(
                    'UPDATE vehiculos
                     SET cliente_id = :cliente_id, marca = :marca, modelo = :modelo,
                         anio = :anio, kilometraje = :kilometraje, vin = :vin
                     WHERE id = :id'
                );
                $stmt->execute([
                    ':id' => $vehiculoId,
                    ':cliente_id' => $datos['cliente_id'],
                    ':marca' => $datos['marca'],
                    ':modelo' => $datos['modelo'],
                    ':anio' => $datos['anio'],
                    ':kilometraje' => $datos['kilometraje'],
                    ':vin' => $datos['vin'] !== '' ? $datos['vin'] : null,
                ]);
            } else {
                $stmt = $this->conn->prepare(
                    'INSERT INTO vehiculos
                        (cliente_id, placa, marca, modelo, anio, kilometraje, vin)
                     VALUES
                        (:cliente_id, :placa, :marca, :modelo, :anio, :kilometraje, :vin)'
                );
                $stmt->execute([
                    ':cliente_id' => $datos['cliente_id'],
                    ':placa' => $datos['placa'],
                    ':marca' => $datos['marca'],
                    ':modelo' => $datos['modelo'],
                    ':anio' => $datos['anio'],
                    ':kilometraje' => $datos['kilometraje'],
                    ':vin' => $datos['vin'] !== '' ? $datos['vin'] : null,
                ]);
                $vehiculoId = (int) $this->conn->lastInsertId();
            }

            $stmt = $this->conn->prepare(
                'INSERT INTO ordenes_trabajo
                    (numero, cliente_id, vehiculo_id, sintomas, estado, creado_por)
                 VALUES
                    (NULL, :cliente_id, :vehiculo_id, :sintomas, :estado, :creado_por)'
            );
            $stmt->execute([
                ':cliente_id' => $datos['cliente_id'],
                ':vehiculo_id' => $vehiculoId,
                ':sintomas' => $datos['sintomas'],
                ':estado' => self::ESTADOS[0],
                ':creado_por' => $datos['usuario_id'],
            ]);
            $ordenId = (int) $this->conn->lastInsertId();
            $numero = 'OT-' . date('Y') . '-' . str_pad((string) $ordenId, 5, '0', STR_PAD_LEFT);

            $stmt = $this->conn->prepare(
                'UPDATE ordenes_trabajo SET numero = :numero WHERE id = :id'
            );
            $stmt->execute([':numero' => $numero, ':id' => $ordenId]);

            $this->registrarHistorial($ordenId, self::ESTADOS[0], 'Orden creada y vehículo recepcionado.', $datos['usuario_id']);
            $this->conn->commit();

            return ['orden_id' => $ordenId, 'vehiculo_id' => $vehiculoId];
        } catch (Throwable $exception) {
            $this->conn->rollBack();
            throw $exception;
        }
    }

    public function agregarFoto(int $vehiculoId, int $ordenId, string $ruta, string $nombreOriginal): bool
    {
        $stmt = $this->conn->prepare(
            'INSERT INTO vehiculo_fotos (vehiculo_id, orden_id, ruta, nombre_original)
             VALUES (:vehiculo_id, :orden_id, :ruta, :nombre_original)'
        );

        return $stmt->execute([
            ':vehiculo_id' => $vehiculoId,
            ':orden_id' => $ordenId,
            ':ruta' => $ruta,
            ':nombre_original' => $nombreOriginal,
        ]);
    }

    public function actualizarEstado(int $ordenId, string $estado, string $comentario, int $usuarioId): bool
    {
        if (!in_array($estado, self::ESTADOS, true)) {
            return false;
        }

        $this->conn->beginTransaction();

        try {
            $stmt = $this->conn->prepare('SELECT estado FROM ordenes_trabajo WHERE id = :id LIMIT 1');
            $stmt->execute([':id' => $ordenId]);
            if (!$stmt->fetch()) {
                $this->conn->rollBack();
                return false;
            }

            $stmt = $this->conn->prepare(
                'UPDATE ordenes_trabajo SET estado = :estado, actualizado_en = CURRENT_TIMESTAMP
                 WHERE id = :id'
            );
            $stmt->execute([':estado' => $estado, ':id' => $ordenId]);
            $this->registrarHistorial($ordenId, $estado, $comentario, $usuarioId);
            $this->conn->commit();

            return true;
        } catch (Throwable $exception) {
            $this->conn->rollBack();
            throw $exception;
        }
    }

    private function buscarVehiculoPorPlaca(string $placa): ?array
    {
        $stmt = $this->conn->prepare(
            'SELECT id FROM vehiculos WHERE placa = :placa LIMIT 1'
        );
        $stmt->execute([':placa' => $placa]);
        $vehiculo = $stmt->fetch();

        return $vehiculo ?: null;
    }

    private function registrarHistorial(int $ordenId, string $estado, string $comentario, int $usuarioId): void
    {
        $stmt = $this->conn->prepare(
            'INSERT INTO orden_estado_historial (orden_id, estado, comentario, usuario_id)
             VALUES (:orden_id, :estado, :comentario, :usuario_id)'
        );
        $stmt->execute([
            ':orden_id' => $ordenId,
            ':estado' => $estado,
            ':comentario' => $comentario !== '' ? $comentario : null,
            ':usuario_id' => $usuarioId,
        ]);
    }
}
