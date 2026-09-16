<?php

declare(strict_types=1);

require_once __DIR__ . '/Session.php';

/**
 * Centralizes role and permission checks so controllers and views use the same
 * policy. Unknown or missing roles are denied by default.
 */
class Authorization
{
    public const ADMIN = 'admin';
    public const MECANICO = 'mecanico';
    public const RECEPCION = 'recepcion';

    private const PERMISSIONS = [
        'manage_users' => [self::ADMIN],
        'view_audit' => [self::ADMIN],
        'manage_clients' => [self::ADMIN, self::RECEPCION],
        'view_orders' => [self::ADMIN, self::MECANICO, self::RECEPCION],
        'create_orders' => [self::ADMIN, self::RECEPCION],
        'update_order_status' => [self::ADMIN, self::MECANICO],
        // Extension points for the invoicing/payment module.
        'manage_invoices' => [self::ADMIN, self::RECEPCION],
        'manage_settings' => [self::ADMIN],
    ];

    public static function role(): ?string
    {
        Session::start();
        $role = strtolower(trim((string) ($_SESSION['user_rol'] ?? '')));

        return in_array($role, [self::ADMIN, self::MECANICO, self::RECEPCION], true)
            ? $role
            : null;
    }

    public static function can(string $permission): bool
    {
        $role = self::role();

        return $role !== null
            && isset(self::PERMISSIONS[$permission])
            && in_array($role, self::PERMISSIONS[$permission], true);
    }

    public static function requirePermission(string $permission): void
    {
        if (!Session::isAuthenticated() || !self::can($permission)) {
            self::deny();
        }
    }

    public static function requireRole(string ...$roles): void
    {
        $role = self::role();
        if (!Session::isAuthenticated() || $role === null || !in_array($role, $roles, true)) {
            self::deny();
        }
    }

    public static function deny(): void
    {
        http_response_code(403);
        echo '<!doctype html><html lang="es"><head><meta charset="UTF-8"><title>Acceso denegado</title></head>';
        echo '<body style="font-family:Arial,sans-serif;padding:2rem"><h1>Acceso denegado</h1>';
        echo '<p>No tienes permisos para realizar esta acción.</p>';
        echo '<a href="index.php?action=clientes">Volver al inicio</a></body></html>';
        exit;
    }

    public static function authorizeAction(string $action): void
    {
        $permissions = [
            'clientes' => 'manage_clients',
            'crear_cliente' => 'manage_clients',
            'guardar_cliente' => 'manage_clients',
            'editar_cliente' => 'manage_clients',
            'actualizar_cliente' => 'manage_clients',
            'eliminar_cliente' => 'manage_clients',
            'ordenes' => 'view_orders',
            'ver_orden' => 'view_orders',
            'crear_orden' => 'create_orders',
            'guardar_orden' => 'create_orders',
            'actualizar_estado_orden' => 'update_order_status',
            'usuarios' => 'manage_users',
            'cambiar_rol' => 'manage_users',
            'auditoria' => 'view_audit',
            'audit_logs' => 'view_audit',
        ];

        if (isset($permissions[$action])) {
            self::requirePermission($permissions[$action]);
        }
    }
}
