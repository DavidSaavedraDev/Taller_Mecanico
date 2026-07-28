<?php
require_once __DIR__ . '/../models/Cliente.php';

class ClienteController {
    private $model;

    public function __construct() {
        $this->model = new Cliente();
    }

    public function index() {
        $clientes = $this->model->obtenerTodos();
        require_once __DIR__ . '/../views/clientes/index.php';
    }

    public function crear() {
        require_once __DIR__ . '/../views/clientes/crear.php';
    }

    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $documento = trim($_POST['documento'] ?? '');
            $nombre    = trim($_POST['nombre'] ?? '');
            $telefono  = trim($_POST['telefono'] ?? '');
            $email     = trim($_POST['email'] ?? '');
            $direccion = trim($_POST['direccion'] ?? '');

            if (!empty($documento) && !empty($nombre) && !empty($telefono)) {
                $this->model->guardar($documento, $nombre, $telefono, $email, $direccion);
                header("Location: index.php?action=clientes&msg=guardado");
                exit();
            }
        }
    }

    public function editar() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $cliente = $this->model->obtenerPorId($id);
            if ($cliente) {
                require_once __DIR__ . '/../views/clientes/editar.php';
                return;
            }
        }
        header("Location: index.php?action=clientes");
        exit();
    }

    public function actualizar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id        = $_POST['id'] ?? null;
            $documento = trim($_POST['documento'] ?? '');
            $nombre    = trim($_POST['nombre'] ?? '');
            $telefono  = trim($_POST['telefono'] ?? '');
            $email     = trim($_POST['email'] ?? '');
            $direccion = trim($_POST['direccion'] ?? '');

            if ($id && !empty($documento) && !empty($nombre)) {
                $this->model->actualizar($id, $documento, $nombre, $telefono, $email, $direccion);
                header("Location: index.php?action=clientes&msg=actualizado");
                exit();
            }
        }
    }

    public function eliminar() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $this->model->eliminar($id);
            header("Location: index.php?action=clientes&msg=eliminado");
            exit();
        }
    }
}
?>