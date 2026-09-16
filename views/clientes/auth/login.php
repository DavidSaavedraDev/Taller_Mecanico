<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Taller Mecánico Pro</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body class="bg-dark d-flex align-items-center justify-content-center vh-100">

<div class="card shadow-lg border-0 rounded-4" style="width: 100%; max-width: 420px;">
    <div class="card-body p-4 text-center">
        <!-- Ícono del Taller -->
        <div class="mb-3 text-warning">
            <i class="fa-solid fa-wrench fa-3x"></i>
        </div>
        <h4 class="fw-bold mb-1 text-dark">Taller Mecánico</h4>
        <p class="text-muted small mb-4">Ingresa tus credenciales para acceder</p>

        <!-- Alerta de éxito tras registrarse -->
        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'registrado'): ?>
            <div class="alert alert-success py-2 small border-0 shadow-sm mb-3" role="alert">
                <i class="fa-solid fa-circle-check me-1"></i> ¡Registro exitoso! Ya puedes iniciar sesión.
            </div>
        <?php endif; ?>
        <?php if (isset($warning)): ?>
            <div class="alert alert-warning py-2 small border-0 shadow-sm mb-3" role="alert">
                <i class="fa-solid fa-triangle-exclamation me-1"></i> <?= e($warning); ?>
            </div>
        <?php endif; ?>

        <!-- Alerta de éxito tras restablecer contraseña -->
        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'password_actualizada'): ?>
            <div class="alert alert-success py-2 small border-0 shadow-sm mb-3" role="alert">
                <i class="fa-solid fa-circle-check me-1"></i> Contraseña actualizada. Inicia sesión con tus nuevas credenciales.
            </div>
        <?php endif; ?>

        <!-- Alerta de error si falla la contraseña/correo -->
        <?php if (isset($error)): ?>
            <div class="alert alert-danger py-2 small border-0 shadow-sm mb-3" role="alert">
                <i class="fa-solid fa-circle-exclamation me-1"></i> <?= htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- Formulario de Login -->
        <form action="index.php?action=autenticar" method="POST">
            <input type="hidden" name="csrf_token" value="<?= e(Session::csrfToken()); ?>">
            <div class="form-floating mb-3 text-start">
                <input type="email" name="email" class="form-control" id="email" placeholder="correo@ejemplo.com" required>
                <label for="email"><i class="fa-solid fa-envelope me-1 text-muted"></i> Correo Electrónico</label>
            </div>
            
            <div class="form-floating mb-2 text-start">
                <input type="password" name="password" class="form-control" id="password" placeholder="Contraseña" required>
                <label for="password"><i class="fa-solid fa-lock me-1 text-muted"></i> Contraseña</label>
            </div>

            <!-- Enlace Olvidé mi Contraseña -->
            <div class="text-end mb-3">
                <a href="index.php?action=olvide_password" class="small text-decoration-none text-muted fw-semibold">
                    ¿Olvidaste tu contraseña?
                </a>
            </div>

            <button type="submit" class="btn btn-warning w-100 fw-bold py-2 mt-1 shadow-sm">
                <i class="fa-solid fa-right-to-bracket me-1"></i> Iniciar Sesión
            </button>
        </form>

        <hr class="my-4">

        <!-- Enlace al Formulario de Registro -->
        <p class="small text-muted mb-0">
            ¿No tienes una cuenta aún? 
            <a href="index.php?action=registro" class="fw-bold text-decoration-none text-primary">Regístrate aquí</a>
        </p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>