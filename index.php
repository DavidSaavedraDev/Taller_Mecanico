<?php
// 1. Iniciar sesión para control de acceso
session_start();
    
// 2. Cargar los controladores necesarios
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/ClienteController.php';

// Cargar Controlador de Usuarios solo si el archivo existe
if (file_exists(__DIR__ . '/controllers/UsuarioController.php')) {
    require_once __DIR__ . '/controllers/UsuarioController.php';
}

// 3. Capturar la acción enviada por URL (por defecto 'clientes')
$action = $_GET['action'] ?? 'clientes';

$auth   = new AuthController();
// 4. Definir las rutas públicas (no requieren inicio de sesión)
$rutas_publicas = ['login', 'autenticar', 'registro', 'registrar'];

// 5. Middleware de Seguridad: Si no hay sesión y la ruta no es pública, redirigir al login
if (!isset($_SESSION['user_id']) && !in_array($action, $rutas_publicas)) {
    header("Location: index.php?action=login");
    exit();
}

// 6. Enrutador Principal
switch ($action) {
    // --- AUTENTICACIÓN ---
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

    case 'logout':
        $auth->logout();
        break;

    // --- CRUD DE CLIENTES ---
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

    // --- GESTIÓN DE USUARIOS (Opcional) ---
    case 'usuarios':
        if (class_exists('UsuarioController')) {
            $userController = new UsuarioController();
            $userController->index();
        } else {
            header("Location: index.php?action=clientes");
        }
        break;

    case 'cambiar_rol':
        if (class_exists('UsuarioController')) {
            $userController = new UsuarioController();
            $userController->cambiarRol();
        } else {
            header("Location: index.php?action=clientes");
        }
        break;

    // --- RUTA POR DEFECTO ---
    default:
        $controller = new ClienteController();
        $controller->index();
        break;
}
?>
