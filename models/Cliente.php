<?php

declare(strict_types=1);

require_once __DIR__ . '/../core/Database.php';

class Cliente
{
    private PDO $conn;

    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    public function obtenerTodos(): array
    {
        $stmt = $this->conn->query('SELECT * FROM clientes ORDER BY id DESC');
        return $stmt->fetchAll();
    }

    public function obtenerPorId(int $id): ?array
    {
        $stmt = $this->conn->prepare('SELECT * FROM clientes WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $cliente = $stmt->fetch();

        return $cliente ?: null;
    }

    public function guardar(string $documento, string $nombre, string $telefono, string $email, string $direccion): bool
    {
        $stmt = $this->conn->prepare(
            'INSERT INTO clientes (documento, nombre, telefono, email, direccion) VALUES (:documento, :nombre, :telefono, :email, :direccion)'
        );

        return $stmt->execute([
            ':documento' => $documento,
            ':nombre' => $nombre,
            ':telefono' => $telefono,
            ':email' => $email,
            ':direccion' => $direccion,
        ]);
    }

    public function actualizar(int $id, string $documento, string $nombre, string $telefono, string $email, string $direccion): bool
    {
        $stmt = $this->conn->prepare(
            'UPDATE clientes SET documento = :documento, nombre = :nombre, telefono = :telefono, email = :email, direccion = :direccion WHERE id = :id'
        );

        return $stmt->execute([
            ':id' => $id,
            ':documento' => $documento,
            ':nombre' => $nombre,
            ':telefono' => $telefono,
            ':email' => $email,
            ':direccion' => $direccion,
        ]);
    }

    public function eliminar(int $id): bool
    {
        $stmt = $this->conn->prepare('DELETE FROM clientes WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }
}
