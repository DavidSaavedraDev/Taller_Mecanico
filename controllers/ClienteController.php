<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/Cliente.php';
require_once __DIR__ . '/../models/AuditLog.php';
require_once __DIR__ . '/../core/Authorization.php';
require_once __DIR__ . '/../core/Session.php';

class ClienteController
{
    private Cliente $model;
    private AuditLog $auditLog;

    public function __construct()
    {
        $this->model = new Cliente();
        $this->auditLog = new AuditLog();
        Authorization::requirePermission('manage_clients');
    }

    public function index(): void
    {
        Session::requireAuth();
        $clientes = $this->model->obtenerTodos();
        require_once __DIR__ . '/../views/clientes/index.php';
    }

    public function crear(): void
    {
        Session::requireAuth();
        $error = Session::getFlash('error');
        require_once __DIR__ . '/../views/clientes/crear.php';
    }

    public function guardar(): void
    {
        Session::requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?action=clientes');
        }

        try {
            Session::verifyCsrf();
        } catch (InvalidArgumentException $e) {
            Session::setFlash('error', $e->getMessage());
            redirect('index.php?action=crear_cliente');
        }

        $documento = sanitizeInput($_POST['documento'] ?? '');
        $nombre = sanitizeInput($_POST['nombre'] ?? '');
        $telefono = sanitizeInput($_POST['telefono'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $direccion = sanitizeInput($_POST['direccion'] ?? '');

        if ($documento === '' || $nombre === '' || $telefono === '') {
            Session::setFlash('error', 'Documentos, nombre y teléfono son obligatorios.');
            redirect('index.php?action=crear_cliente');
        }

        $this->model->guardar($documento, $nombre, $telefono, $email, $direccion);
        $this->registrarAuditoria('client.created', null, [
            'documento' => $documento,
            'nombre' => $nombre,
        ]);
        redirect('index.php?action=clientes&msg=guardado');
    }

    public function editar(): void
    {
        Session::requireAuth();

        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if ($id === false || $id === null) {
            redirect('index.php?action=clientes');
        }

        $cliente = $this->model->obtenerPorId((int) $id);
        if (!$cliente) {
            redirect('index.php?action=clientes');
        }

        require_once __DIR__ . '/../views/clientes/editar.php';
    }

    public function actualizar(): void
    {
        Session::requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?action=clientes');
        }

        try {
            Session::verifyCsrf();
        } catch (InvalidArgumentException $e) {
            Session::setFlash('error', $e->getMessage());
            redirect('index.php?action=clientes');
        }

        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $documento = sanitizeInput($_POST['documento'] ?? '');
        $nombre = sanitizeInput($_POST['nombre'] ?? '');
        $telefono = sanitizeInput($_POST['telefono'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $direccion = sanitizeInput($_POST['direccion'] ?? '');

        if ($id === false || $id === null || $documento === '' || $nombre === '' || $telefono === '') {
            Session::setFlash('error', 'No se pudo actualizar el cliente. Faltan datos obligatorios.');
            redirect('index.php?action=clientes');
        }

        $this->model->actualizar((int) $id, $documento, $nombre, $telefono, $email, $direccion);
        $this->registrarAuditoria('client.updated', (int) $id, [
            'documento' => $documento,
            'nombre' => $nombre,
        ]);
        redirect('index.php?action=clientes&msg=actualizado');
    }

    public function eliminar(): void
    {
        Session::requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?action=clientes');
        }

        try {
            Session::verifyCsrf();
        } catch (InvalidArgumentException $e) {
            Session::setFlash('error', $e->getMessage());
            redirect('index.php?action=clientes');
        }

        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if ($id === false || $id === null) {
            redirect('index.php?action=clientes');
        }

        $this->model->eliminar((int) $id);
        $this->registrarAuditoria('client.deleted', (int) $id);
        redirect('index.php?action=clientes&msg=eliminado');
    }

    private function registrarAuditoria(string $accion, ?int $entidadId, array $detalles = []): void
    {
        try {
            $this->auditLog->registrar(
                (int) $_SESSION['user_id'],
                $accion,
                'cliente',
                $entidadId,
                $detalles
            );
        } catch (Throwable $exception) {
            error_log('No se pudo registrar auditoría: ' . $exception->getMessage());
        }
    }
}
