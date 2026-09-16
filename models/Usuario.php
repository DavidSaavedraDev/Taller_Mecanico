<?php

declare(strict_types=1);

require_once __DIR__ . '/../core/Database.php';

class Usuario
{
    public const ROLES = ['admin', 'mecanico', 'recepcion'];

    private PDO $conn;

    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->conn->prepare('SELECT * FROM usuarios WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        $usuario = $stmt->fetch();

        return $usuario ?: null;
    }

    public function existsEmail(string $email): bool
    {
        $stmt = $this->conn->prepare('SELECT 1 FROM usuarios WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);

        return (bool) $stmt->fetchColumn();
    }

    public function obtenerTodos(): array
    {
        $stmt = $this->conn->query(
            'SELECT id, nombre, email, rol, estado, creado_en
             FROM usuarios ORDER BY id DESC'
        );

        return $stmt->fetchAll();
    }

    public function obtenerPorId(int $id): ?array
    {
        $stmt = $this->conn->prepare(
            'SELECT id, nombre, email, rol, estado FROM usuarios WHERE id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $usuario = $stmt->fetch();

        return $usuario ?: null;
    }

    public function actualizarRol(int $id, string $rol): bool
    {
        if (!in_array($rol, self::ROLES, true)) {
            return false;
        }

        $stmt = $this->conn->prepare(
            'UPDATE usuarios SET rol = :rol WHERE id = :id'
        );

        return $stmt->execute([':rol' => $rol, ':id' => $id]);
    }

    public function create(string $nombre, string $email, string $password, string $rol = 'mecanico'): bool
    {
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $this->conn->prepare(
            'INSERT INTO usuarios (nombre, email, password, rol, estado) VALUES (:nombre, :email, :password, :rol, 1)'
        );

        return $stmt->execute([
            ':nombre' => $nombre,
            ':email' => $email,
            ':password' => $passwordHash,
            ':rol' => $rol,
        ]);
    }

    public function saveResetCode(string $email, string $codeHash, string $expiresAt): bool
    {
        $stmt = $this->conn->prepare(
            'UPDATE usuarios SET reset_pin = :reset_pin, pin_expiracion = :pin_expiracion WHERE email = :email AND estado = 1'
        );

        $stmt->execute([
            ':reset_pin' => $codeHash,
            ':pin_expiracion' => $expiresAt,
            ':email' => $email,
        ]);

        return $stmt->rowCount() > 0;
    }

    public function findValidReset(string $email): ?array
    {
        $stmt = $this->conn->prepare(
            'SELECT id, reset_pin, pin_expiracion FROM usuarios
             WHERE email = :email AND estado = 1
             LIMIT 1'
        );
        $stmt->execute([':email' => $email]);
        $usuario = $stmt->fetch();

        return $usuario ?: null;
    }

    public function updatePasswordAndClearReset(int $id, string $passwordHash): bool
    {
        $stmt = $this->conn->prepare(
            'UPDATE usuarios SET password = :password, reset_pin = NULL, pin_expiracion = NULL
             WHERE id = :id AND estado = 1'
        );

        return $stmt->execute([
            ':password' => $passwordHash,
            ':id' => $id,
        ]);
    }
}
