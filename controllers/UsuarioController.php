<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/AuditLog.php';
require_once __DIR__ . '/../core/Authorization.php';
require_once __DIR__ . '/../core/Session.php';

class UsuarioController
{
    private Usuario $model;
    private AuditLog $audit;

    public function __construct()
    {
        Authorization::requirePermission('manage_users');
        $this->model = new Usuario();
        $this->audit = new AuditLog();
    }

    public function index(): void
    {
        $usuarios = $this->model->obtenerTodos();
        $error = Session::getFlash('error');
        $success = Session::getFlash('success');
        require_once __DIR__ . '/../views/usuarios/index.php';
    }

    public function cambiarRol(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?action=usuarios');
        }

        try {
            Session::verifyCsrf();
        } catch (InvalidArgumentException $exception) {
            Session::setFlash('error', $exception->getMessage());
            redirect('index.php?action=usuarios');
        }

        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $rol = strtolower(sanitizeInput($_POST['rol'] ?? ''));
        if (!is_int($id) || $id < 1 || !in_array($rol, Usuario::ROLES, true)) {
            Session::setFlash('error', 'El usuario o rol seleccionado no es válido.');
            redirect('index.php?action=usuarios');
        }

        if ($id === (int) $_SESSION['user_id']) {
            Session::setFlash('error', 'No puedes cambiar tu propio rol.');
            redirect('index.php?action=usuarios');
        }

        $usuario = $this->model->obtenerPorId($id);
        if (!$usuario) {
            Session::setFlash('error', 'El usuario no existe.');
            redirect('index.php?action=usuarios');
        }

        if ((string) $usuario['rol'] === $rol) {
            Session::setFlash('success', 'El rol no tuvo cambios.');
            redirect('index.php?action=usuarios');
        }

        if (!$this->model->actualizarRol($id, $rol)) {
            Session::setFlash('error', 'No se pudo actualizar el rol.');
            redirect('index.php?action=usuarios');
        }

        try {
            $this->audit->registrar(
                (int) $_SESSION['user_id'],
                'user.role_changed',
                'usuario',
                $id,
                ['from' => $usuario['rol'], 'to' => $rol]
            );
        } catch (Throwable $exception) {
            error_log('No se pudo registrar auditoría: ' . $exception->getMessage());
        }
        Session::setFlash('success', 'Rol actualizado correctamente.');
        redirect('index.php?action=usuarios');
    }
}
