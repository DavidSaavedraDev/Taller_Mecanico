<?php

declare(strict_types=1);

require_once __DIR__ . '/../core/Database.php';

class AuditLog
{
    private PDO $conn;

    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    public function registrar(
        ?int $usuarioId,
        string $accion,
        ?string $entidad = null,
        ?int $entidadId = null,
        array $detalles = []
    ): bool {
        $stmt = $this->conn->prepare(
            'INSERT INTO audit_logs
                (usuario_id, accion, entidad, entidad_id, detalles, ip_address, user_agent)
             VALUES
                (:usuario_id, :accion, :entidad, :entidad_id, :detalles, :ip_address, :user_agent)'
        );

        $encodedDetails = json_encode($detalles, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($encodedDetails === false) {
            $encodedDetails = '{}';
        }

        return $stmt->execute([
            ':usuario_id' => $usuarioId,
            ':accion' => $accion,
            ':entidad' => $entidad,
            ':entidad_id' => $entidadId,
            ':detalles' => $encodedDetails,
            ':ip_address' => self::clientIp(),
            ':user_agent' => substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255),
        ]);
    }

    public function obtenerPaginados(
        int $page = 1,
        int $perPage = 25,
        string $accion = '',
        ?int $usuarioId = null
    ): array {
        $page = max(1, $page);
        $perPage = min(100, max(1, $perPage));
        $where = [];
        $params = [];

        if ($accion !== '') {
            $where[] = 'a.accion = :accion';
            $params[':accion'] = $accion;
        }
        if ($usuarioId !== null && $usuarioId > 0) {
            $where[] = 'a.usuario_id = :usuario_id';
            $params[':usuario_id'] = $usuarioId;
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $count = $this->conn->prepare('SELECT COUNT(*) FROM audit_logs a ' . $whereSql);
        $count->execute($params);
        $total = (int) $count->fetchColumn();

        $offset = ($page - 1) * $perPage;
        $stmt = $this->conn->prepare(
            'SELECT a.*, u.nombre AS usuario_nombre, u.email AS usuario_email
             FROM audit_logs a
             LEFT JOIN usuarios u ON u.id = a.usuario_id
             ' . $whereSql . '
             ORDER BY a.id DESC
             LIMIT :limit OFFSET :offset'
        );
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'items' => $stmt->fetchAll(),
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'pages' => max(1, (int) ceil($total / $perPage)),
        ];
    }

    private static function clientIp(): ?string
    {
        $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
        return filter_var($ip, FILTER_VALIDATE_IP) !== false ? $ip : null;
    }
}
