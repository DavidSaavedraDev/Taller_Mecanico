<?php
require_once __DIR__ . '/../config/database.php';

class AuthController {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // 1. Mostrar la vista de Login
    public function login() {
        if (isset($_SESSION['user_id'])) {
            header("Location: index.php?action=clientes");
            exit();
        }
        require_once __DIR__ . '/../views/clientes/auth/login.php';
    }

    // 2. Procesar el inicio de sesión
    public function autenticar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email    = trim($_POST['email'] ?? '');
            $password = trim($_POST['password'] ?? '');

            if (!empty($email) && !empty($password)) {
                $query = "SELECT * FROM usuarios WHERE email = :email AND estado = 1 LIMIT 1";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':email', $email);
                $stmt->execute();

                $usuario = $stmt->fetch();

                // Verificar la contraseña cifrada
                if ($usuario && password_verify($password, $usuario['password'])) {
                    $_SESSION['user_id']     = $usuario['id'];
                    $_SESSION['user_nombre'] = $usuario['nombre'];
                    $_SESSION['user_rol']    = $usuario['rol'];

                    header("Location: index.php?action=clientes");
                    exit();
                } else {
                    $error = "Correo o contraseña incorrectos.";
                    require_once __DIR__ . '/../views/clientes/auth/login.php';
                }
            }
        }
    }

    // 3. Mostrar la vista de Registro
    public function registro() {
        require_once __DIR__ . '/../views/clientes/auth/registro.php';
    }

    // 4. Guardar un nuevo usuario con la contraseña encriptada
    public function registrar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre   = trim($_POST['nombre'] ?? '');
            $email    = trim($_POST['email'] ?? '');
            $password = trim($_POST['password'] ?? '');
            $rol      = trim($_POST['rol'] ?? 'mecanico');

            if (!empty($nombre) && !empty($email) && !empty($password)) {
                // Encriptar contraseña de forma segura
                $password_hash = password_hash($password, PASSWORD_BCRYPT);

                $query = "INSERT INTO usuarios (nombre, email, password, rol) VALUES (:nombre, :email, :password, :rol)";
                $stmt = $this->conn->prepare($query);
                
                $stmt->bindParam(':nombre', $nombre);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':password', $password_hash);
                $stmt->bindParam(':rol', $rol);

                if ($stmt->execute()) {
                    header("Location: index.php?action=login&msg=registrado");
                    exit();
                } else {
                    $error = "Error al registrar el usuario. El correo podría estar duplicado.";
                    require_once __DIR__ . '/../views/clientes/auth/registro.php';
                }
            }
        }
    }

    // 5. Cerrar Sesión
    public function logout() {
        session_destroy();
        header("Location: index.php?action=login");
        exit();
    }
}
?>