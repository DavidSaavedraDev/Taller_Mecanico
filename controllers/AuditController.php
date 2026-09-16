<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/AuditLog.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../core/Authorization.php';

class AuditController
{
    private AuditLog $model;

    public function __construct()
    {
        Authorization::requirePermission('view_audit');
        $this->model = new AuditLog();
    }

    public function index(): void
    {
        $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
        $usuarioId = filter_input(INPUT_GET, 'usuario_id', FILTER_VALIDATE_INT);
        $accion = sanitizeInput($_GET['accion'] ?? '');

        $resultado = $this->model->obtenerPaginados(
            is_int($page) ? $page : 1,
            25,
            $accion,
            is_int($usuarioId) && $usuarioId > 0 ? $usuarioId : null
        );
        $usuarios = (new Usuario())->obtenerTodos();
        $acciones = ['login.success', 'login.failure', 'client.created', 'client.updated', 'client.deleted', 'order.created', 'order.status_changed', 'user.role_changed'];

        require_once __DIR__ . '/../views/auditoria/index.php';
    }
}
