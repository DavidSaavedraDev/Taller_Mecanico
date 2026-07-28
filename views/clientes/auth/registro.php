<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuario - Taller Mecánico</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body class="bg-dark d-flex align-items-center justify-content-center vh-100 py-4">

<div class="card shadow-lg border-0" style="width: 100%; max-width: 450px;">
    <div class="card-body p-4 text-center">
        <div class="mb-3 text-warning">
            <i class="fa-solid fa-user-plus fa-3x"></i>
        </div>
        <h4 class="fw-bold mb-1">Crear Cuenta</h4>
        <p class="text-muted small mb-4">Registra tus datos reales para acceder al taller</p>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger py-2 small" role="alert">
                <i class="fa-solid fa-circle-exclamation me-1"></i> <?= $error; ?>
            </div>
        <?php endif; ?>

        <form action="index.php?action=registrar" method="POST">
            <div class="form-floating mb-3 text-start">
                <input type="text" name="nombre" class="form-control" id="nombre" placeholder="Tu Nombre Completo" required>
                <label for="nombre"><i class="fa-solid fa-user me-1"></i> Nombre Completo</label>
            </div>
            
            <div class="form-floating mb-3 text-start">
                <input type="email" name="email" class="form-control" id="email" placeholder="correo@ejemplo.com" required>
                <label for="email"><i class="fa-solid fa-envelope me-1"></i> Correo Electrónico</label>
            </div>
            
            <div class="form-floating mb-3 text-start">
                <input type="password" name="password" class="form-control" id="password" placeholder="Contraseña" required>
                <label for="password"><i class="fa-solid fa-lock me-1"></i> Contraseña</label>
            </div>

            <div class="form-floating mb-3 text-start">
                <select name="rol" class="form-select" id="rol">
                    <option value="admin">Administrador</option>
                    <option value="mecanico" selected>Mecánico / Recepción</option>
                </select>
                <label for="rol"><i class="fa-solid fa-user-gear me-1"></i> Rol de Usuario</label>
            </div>

            <button type="submit" class="btn btn-warning w-100 fw-bold py-2 mt-2">
                <i class="fa-solid fa-check-circle me-1"></i> Registrarse e Iniciar
            </button>
        </form>

        <hr class="my-3">
        <p class="small mb-0">¿Ya tienes una cuenta? <a href="index.php?action=login" class="fw-bold text-decoration-none">Inicia Sesión aquí</a></p>
    </div>
</div>

</body>
</html>