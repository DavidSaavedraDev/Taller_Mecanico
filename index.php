<?php

declare(strict_types=1);

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/core/helpers.php';
require_once __DIR__ . '/core/Session.php';
require_once __DIR__ . '/core/Authorization.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/ClienteController.php';
require_once __DIR__ . '/controllers/OrdenTrabajoController.php';
require_once __DIR__ . '/controllers/AuditController.php';

if (file_exists(__DIR__ . '/controllers/UsuarioController.php')) {
    require_once __DIR__ . '/controllers/UsuarioController.php';
}

$requestedAction = $_GET['action'] ?? null;
$action = strtolower((string) ($requestedAction ?? ''));
if ($action === '') {
    $action = Session::isAuthenticated() && Authorization::role() === Authorization::MECANICO
        ? 'ordenes'
        : 'clientes';
}
$auth = new AuthController();
$rutasPublicas = [
    'login',
    'autenticar',
    'registro',
    'registrar',
    'olvide_password',
    'enviar_pin',
    'validar_pin',
    'nueva_password',
    'guardar_password',
];

if (!Session::isAuthenticated() && !in_array($action, $rutasPublicas, true)) {
    redirect('index.php?action=login');
}

if (Session::isAuthenticated()) {
    Authorization::authorizeAction($action);
}

switch ($action) {
    case 'login':
        $auth->login();
        break;

    case 'autenticar':
        $auth->autenticar();
        break;

    case 'registro':
        $auth->registro();
        break;

    case 'registrar':
        $auth->registrar();
        break;

    case 'olvide_password':
        $auth->mostrarRecuperacion();
        break;

    case 'enviar_pin':
        $auth->enviarPin();
        break;

    case 'validar_pin':
        $auth->validarPin();
        break;

    case 'reenviar_pin':
        $auth->reenviarPin();
        break;

    case 'nueva_password':
        $auth->nuevaPassword();
        break;

    case 'guardar_password':
        $auth->guardarPassword();
        break;

    case 'logout':
        $auth->logout();
        break;

    case 'clientes':
        $controller = new ClienteController();
        $controller->index();
        break;

    case 'crear_cliente':
        $controller = new ClienteController();
        $controller->crear();
        break;

    case 'guardar_cliente':
        $controller = new ClienteController();
        $controller->guardar();
        break;

    case 'editar_cliente':
        $controller = new ClienteController();
        $controller->editar();
        break;

    case 'actualizar_cliente':
        $controller = new ClienteController();
        $controller->actualizar();
        break;

    case 'eliminar_cliente':
        $controller = new ClienteController();
        $controller->eliminar();
        break;

    case 'ordenes':
        $controller = new OrdenTrabajoController();
        $controller->index();
        break;

    case 'crear_orden':
        $controller = new OrdenTrabajoController();
        $controller->crear();
        break;

    case 'guardar_orden':
        $controller = new OrdenTrabajoController();
        $controller->guardar();
        break;

    case 'ver_orden':
        $controller = new OrdenTrabajoController();
        $controller->ver();
        break;

    case 'actualizar_estado_orden':
        $controller = new OrdenTrabajoController();
        $controller->actualizarEstado();
        break;

    case 'usuarios':
        if (class_exists('UsuarioController')) {
            $userController = new UsuarioController();
            $userController->index();
        } else {
            redirect('index.php?action=clientes');
        }
        break;

    case 'cambiar_rol':
        if (class_exists('UsuarioController')) {
            $userController = new UsuarioController();
            $userController->cambiarRol();
        } else {
            redirect('index.php?action=clientes');
        }
        break;

    case 'auditoria':
    case 'audit_logs':
        (new AuditController())->index();
        break;

    default:
        $controller = new ClienteController();
        $controller->index();
        break;
}
