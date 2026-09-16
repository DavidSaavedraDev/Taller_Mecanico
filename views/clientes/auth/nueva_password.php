<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva contraseña - Taller Mecánico</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark d-flex align-items-center justify-content-center vh-100">
<div class="card shadow-lg border-0 rounded-4" style="width: 100%; max-width: 420px;">
    <div class="card-body p-4">
        <h4 class="fw-bold text-center mb-2">Nueva contraseña</h4>
        <p class="text-muted small text-center">Crea una contraseña de al menos 8 caracteres.</p>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger small"><?= e($error); ?></div>
        <?php endif; ?>
        <form action="index.php?action=guardar_password" method="POST">
            <input type="hidden" name="csrf_token" value="<?= e(Session::csrfToken()); ?>">
            <label class="form-label">Contraseña nueva</label>
            <input type="password" name="password" class="form-control mb-3" minlength="8" required>
            <label class="form-label">Confirmar contraseña</label>
            <input type="password" name="confirm_password" class="form-control mb-3" minlength="8" required>
            <button class="btn btn-warning w-100 fw-bold">Guardar contraseña</button>
        </form>
        <a href="index.php?action=validar_pin" class="btn btn-outline-secondary w-100 mt-3">
            <i class="fa-solid fa-arrow-left me-1"></i> Volver a validar el código
        </a>
    </div>
</div>
</body>
</html>
