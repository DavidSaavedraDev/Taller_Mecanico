<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - Taller Mecánico Pro</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body class="bg-dark d-flex align-items-center justify-content-center vh-100">

<div class="card shadow-lg border-0 rounded-4" style="width: 100%; max-width: 420px;">
    <div class="card-body p-4 text-center">
        <!-- Ícono -->
        <div class="mb-3 text-warning">
            <i class="fa-solid fa-key fa-3x"></i>
        </div>
        <h4 class="fw-bold mb-1 text-dark">¿Olvidaste tu contraseña?</h4>
        <p class="text-muted small mb-4">
            Ingresa tu correo electrónico registrado y te enviaremos un <b>PIN de seguridad de 6 dígitos</b> (válido por 5 minutos).
        </p>

        <!-- Alerta de error si el correo no existe -->
        <?php if (isset($error)): ?>
            <div class="alert alert-danger py-2 small border-0 shadow-sm mb-3" role="alert">
                <i class="fa-solid fa-circle-exclamation me-1"></i> <?= htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        <?php if ($success = Session::getFlash('success')): ?>
            <div class="alert alert-success py-2 small border-0 shadow-sm mb-3" role="alert">
                <?= e($success); ?>
            </div>
        <?php endif; ?>

        <!-- Formulario para enviar el PIN al correo -->
        <form action="index.php?action=enviar_pin" method="POST">
            <input type="hidden" name="csrf_token" value="<?= e(Session::csrfToken()); ?>">
            <div class="form-floating mb-3 text-start">
                <input type="email" name="email" class="form-control" id="email" placeholder="correo@ejemplo.com" required>
                <label for="email"><i class="fa-solid fa-envelope me-1 text-muted"></i> Correo Electrónico</label>
            </div>

            <button type="submit" class="btn btn-warning w-100 fw-bold py-2 mt-2 shadow-sm">
                <i class="fa-solid fa-paper-plane me-1"></i> Enviar Código PIN
            </button>
        </form>

        <hr class="my-4">

        <a href="index.php?action=login" class="btn btn-outline-secondary w-100 mt-3">
            <i class="fa-solid fa-arrow-left me-1"></i> Volver al inicio de sesión
        </a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>