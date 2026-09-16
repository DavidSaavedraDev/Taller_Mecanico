<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validar código - Taller Mecánico</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark d-flex align-items-center justify-content-center vh-100">
<div class="card shadow-lg border-0 rounded-4" style="width: 100%; max-width: 420px;">
    <div class="card-body p-4 text-center">
        <h4 class="fw-bold mb-2">Validar código</h4>
        <p class="text-muted small">Escribe el código de 6 dígitos recibido por correo. Tiene una vigencia de 5 minutos.</p>
        <?php if ($success = Session::getFlash('success')): ?>
            <div class="alert alert-success small"><?= e($success); ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger small"><?= e($error); ?></div>
        <?php endif; ?>
        <form action="index.php?action=validar_pin" method="POST">
            <input type="hidden" name="csrf_token" value="<?= e(Session::csrfToken()); ?>">
            <input type="text" name="pin" class="form-control text-center fs-4 mb-3" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required autofocus>
            <button class="btn btn-warning w-100 fw-bold">Validar código</button>
        </form>
        <form action="index.php?action=reenviar_pin" method="POST" class="mt-3">
            <input type="hidden" name="csrf_token" value="<?= e(Session::csrfToken()); ?>">
            <button type="submit" class="btn btn-outline-primary w-100">
                <i class="fa-solid fa-rotate me-1"></i> Reenviar código
            </button>
        </form>
        <div class="d-flex gap-2 mt-3">
            <a href="index.php?action=olvide_password" class="btn btn-outline-secondary flex-fill">
                <i class="fa-solid fa-arrow-left me-1"></i> Atrás
            </a>
            <a href="index.php?action=login" class="btn btn-outline-warning flex-fill">
                Ir al inicio
            </a>
        </div>
    </div>
</div>
</body>
</html>
