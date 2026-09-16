<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/OrdenTrabajo.php';
require_once __DIR__ . '/../models/Cliente.php';
require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../core/Authorization.php';
require_once __DIR__ . '/../models/AuditLog.php';
require_once __DIR__ . '/../core/Mailer.php';

class OrdenTrabajoController
{
    private OrdenTrabajo $model;
    private Cliente $clienteModel;
    private Mailer $mailer;
    private AuditLog $auditLog;

    public function __construct()
    {
        $this->model = new OrdenTrabajo();
        $this->clienteModel = new Cliente();
        $this->mailer = new Mailer();
        $this->auditLog = new AuditLog();
    }

    public function index(): void
    {
        Session::requireAuth();
        Authorization::requirePermission('view_orders');
        $ordenes = $this->model->obtenerTodos();
        require_once __DIR__ . '/../views/ordenes/index.php';
    }

    public function crear(): void
    {
        Session::requireAuth();
        Authorization::requirePermission('create_orders');
        $clientes = $this->clienteModel->obtenerTodos();
        $error = Session::getFlash('error');
        require_once __DIR__ . '/../views/ordenes/crear.php';
    }

    public function guardar(): void
    {
        Session::requireAuth();
        Authorization::requirePermission('create_orders');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?action=ordenes');
        }

        try {
            Session::verifyCsrf();
        } catch (InvalidArgumentException $exception) {
            Session::setFlash('error', $exception->getMessage());
            redirect('index.php?action=crear_orden');
        }

        $clienteId = filter_input(INPUT_POST, 'cliente_id', FILTER_VALIDATE_INT);
        $placa = strtoupper(sanitizeInput($_POST['placa'] ?? ''));
        $marca = sanitizeInput($_POST['marca'] ?? '');
        $modelo = sanitizeInput($_POST['modelo'] ?? '');
        $anio = filter_input(INPUT_POST, 'anio', FILTER_VALIDATE_INT);
        $kilometraje = filter_input(INPUT_POST, 'kilometraje', FILTER_VALIDATE_INT);
        $vin = strtoupper(sanitizeInput($_POST['vin'] ?? ''));
        $sintomas = sanitizeInput($_POST['sintomas'] ?? '');

        $anioValido = is_int($anio) && $anio >= 1900 && $anio <= ((int) date('Y') + 1);
        $kilometrajeValido = is_int($kilometraje) && $kilometraje >= 0;

        if (
            $clienteId === false || $clienteId === null ||
            $placa === '' || $marca === '' || $modelo === '' ||
            !$anioValido || !$kilometrajeValido || $sintomas === ''
        ) {
            Session::setFlash('error', 'Completa los datos obligatorios del cliente, vehículo y recepción.');
            redirect('index.php?action=crear_orden');
        }

        if (!$this->clienteModel->obtenerPorId((int) $clienteId)) {
            Session::setFlash('error', 'El cliente seleccionado no existe.');
            redirect('index.php?action=crear_orden');
        }

        try {
            $resultado = $this->model->crear([
                'cliente_id' => (int) $clienteId,
                'placa' => $placa,
                'marca' => $marca,
                'modelo' => $modelo,
                'anio' => $anio,
                'kilometraje' => $kilometraje,
                'vin' => $vin,
                'sintomas' => $sintomas,
                'usuario_id' => (int) $_SESSION['user_id'],
            ]);
            $this->guardarFotos($resultado['vehiculo_id'], $resultado['orden_id'], $_FILES['fotos'] ?? []);
        } catch (Throwable $exception) {
            error_log('No se pudo crear la orden de trabajo: ' . $exception->getMessage());
            Session::setFlash('error', 'No se pudo crear la orden. Verifica que la placa no esté duplicada.');
            redirect('index.php?action=crear_orden');
        }

        $orden = $this->model->obtenerPorId($resultado['orden_id']);
        $this->registrarAuditoria('order.created', (int) $resultado['orden_id'], [
            'numero' => $orden['numero'] ?? null,
            'cliente_id' => (int) $clienteId,
        ]);

        if ($orden && !$this->enviarNotificacion($orden, 'Recepción confirmada', 'Tu vehículo fue recibido en el taller.')) {
            Session::setFlash('warning', 'La orden se creó, pero no fue posible enviar el correo al cliente.');
        }

        redirect('index.php?action=ver_orden&id=' . $resultado['orden_id'] . '&msg=guardado');
    }

    public function ver(): void
    {
        Session::requireAuth();
        Authorization::requirePermission('view_orders');
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if ($id === false || $id === null) {
            redirect('index.php?action=ordenes');
        }

        $orden = $this->model->obtenerPorId((int) $id);
        if (!$orden) {
            redirect('index.php?action=ordenes');
        }

        $historial = $this->model->obtenerHistorial((int) $id);
        $fotos = $this->model->obtenerFotos((int) $id);
        require_once __DIR__ . '/../views/ordenes/ver.php';
    }

    public function actualizarEstado(): void
    {
        Session::requireAuth();
        Authorization::requirePermission('update_order_status');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?action=ordenes');
        }

        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $estado = sanitizeInput($_POST['estado'] ?? '');
        $comentario = sanitizeInput($_POST['comentario'] ?? '');

        try {
            Session::verifyCsrf();
        } catch (InvalidArgumentException $exception) {
            Session::setFlash('error', $exception->getMessage());
            redirect('index.php?action=ver_orden&id=' . (int) $id);
        }

        if ($id === false || $id === null || !in_array($estado, OrdenTrabajo::ESTADOS, true)) {
            Session::setFlash('error', 'El estado seleccionado no es válido.');
            redirect('index.php?action=ordenes');
        }

        $ordenAnterior = $this->model->obtenerPorId((int) $id);
        if (!$this->model->actualizarEstado((int) $id, $estado, $comentario, (int) $_SESSION['user_id'])) {
            Session::setFlash('error', 'No se pudo actualizar el estado de la orden.');
        } else {
            $this->registrarAuditoria('order.status_changed', (int) $id, [
                'from' => $ordenAnterior['estado'] ?? null,
                'to' => $estado,
                'comentario' => $comentario,
            ]);
            $ordenActualizada = $this->model->obtenerPorId((int) $id);
            if ($ordenActualizada && !$this->enviarNotificacion($ordenActualizada, 'Actualización de tu orden', 'La orden cambió de estado.')) {
                Session::setFlash('warning', 'El estado se actualizó, pero no fue posible enviar el correo al cliente.');
            }
        }

        redirect('index.php?action=ver_orden&id=' . (int) $id . '&msg=estado_actualizado');
    }

    private function registrarAuditoria(string $accion, ?int $entidadId, array $detalles = []): void
    {
        try {
            $this->auditLog->registrar(
                (int) $_SESSION['user_id'],
                $accion,
                'orden_trabajo',
                $entidadId,
                $detalles
            );
        } catch (Throwable $exception) {
            error_log('No se pudo registrar auditoría: ' . $exception->getMessage());
        }
    }

    private function enviarNotificacion(array $orden, string $asunto, string $introduccion): bool
    {
        $email = (string) ($orden['cliente_email'] ?? '');
        if (!isValidEmail($email)) {
            return true;
        }

        $nombre = htmlspecialchars((string) $orden['cliente_nombre'], ENT_QUOTES, 'UTF-8');
        $numero = htmlspecialchars((string) $orden['numero'], ENT_QUOTES, 'UTF-8');
        $placa = htmlspecialchars((string) $orden['placa'], ENT_QUOTES, 'UTF-8');
        $estado = htmlspecialchars((string) $orden['estado'], ENT_QUOTES, 'UTF-8');
        $plain = "{$introduccion}\n\nOrden: {$orden['numero']}\nVehículo: {$orden['placa']} - {$orden['marca']} {$orden['modelo']}\nEstado: {$orden['estado']}\n\nTaller Mecánico";
        $html = '<!doctype html><html lang="es"><head><meta charset="UTF-8"></head><body style="margin:0;padding:32px 12px;background:#f3f4f6;font-family:Arial,sans-serif;color:#1f2937;"><table width="100%" style="max-width:600px;margin:auto;background:#fff;border-radius:16px;overflow:hidden;"><tr><td style="padding:28px;background:#f59e0b;text-align:center;font-size:28px;font-weight:bold;">Taller Mecánico</td></tr><tr><td style="padding:32px;"><p>Hola, ' . $nombre . '.</p><h1 style="font-size:22px;">' . htmlspecialchars($introduccion, ENT_QUOTES, 'UTF-8') . '</h1><div style="padding:18px;background:#fffbeb;border-radius:10px;"><p><strong>Orden:</strong> ' . $numero . '</p><p><strong>Vehículo:</strong> ' . $placa . '</p><p><strong>Estado:</strong> <span style="color:#92400e;font-weight:bold;">' . $estado . '</span></p></div><p style="color:#6b7280;">Te informaremos por este medio cuando haya nuevos avances.</p></td></tr><tr><td style="padding:18px;text-align:center;background:#f9fafb;color:#6b7280;font-size:13px;">Este es un mensaje automático del Taller Mecánico.</td></tr></table></body></html>';

        return $this->mailer->send($email, $asunto . ' - ' . $numero, $plain, $html);
    }

    private function guardarFotos(int $vehiculoId, int $ordenId, array $files): void
    {
        if (empty($files['name']) || !is_array($files['name'])) {
            return;
        }

        $directorio = __DIR__ . '/../uploads/vehiculos';
        if (!is_dir($directorio) && !mkdir($directorio, 0750, true) && !is_dir($directorio)) {
            return;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo === false) {
            return;
        }

        $permitidos = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];

        foreach ($files['name'] as $indice => $nombreOriginal) {
            $error = (int) ($files['error'][$indice] ?? UPLOAD_ERR_NO_FILE);
            $tmpName = (string) ($files['tmp_name'][$indice] ?? '');
            $size = (int) ($files['size'][$indice] ?? 0);

            if ($error !== UPLOAD_ERR_OK || $size > 5 * 1024 * 1024 || !is_uploaded_file($tmpName)) {
                continue;
            }

            $mime = finfo_file($finfo, $tmpName);
            if (!isset($permitidos[$mime])) {
                continue;
            }

            $nombre = 'ot-' . $ordenId . '-' . bin2hex(random_bytes(8)) . '.' . $permitidos[$mime];
            if (move_uploaded_file($tmpName, $directorio . DIRECTORY_SEPARATOR . $nombre)) {
                try {
                    $this->model->agregarFoto(
                        $vehiculoId,
                        $ordenId,
                        'uploads/vehiculos/' . $nombre,
                        sanitizeInput((string) $nombreOriginal)
                    );
                } catch (Throwable $exception) {
                    error_log('No se pudo registrar una foto de la orden: ' . $exception->getMessage());
                }
            }
        }

        finfo_close($finfo);
    }
}
