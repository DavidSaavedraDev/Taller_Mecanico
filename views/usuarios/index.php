<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-1"><i class="fa-solid fa-user-shield me-2 text-primary"></i>Usuarios</h2>
        <p class="text-muted mb-0">Administra los roles de acceso del personal.</p>
    </div>
    <a href="index.php?action=auditoria" class="btn btn-outline-dark">
        <i class="fa-solid fa-clock-rotate-left me-1"></i> Ver auditoría
    </a>
</div>

<?php if ($error): ?><div class="alert alert-danger"><?= e($error); ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success"><?= e($success); ?></div><?php endif; ?>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-dark"><tr><th>Nombre</th><th>Correo</th><th>Estado</th><th>Rol</th><th>Acción</th></tr></thead>
            <tbody>
            <?php foreach ($usuarios as $usuario): ?>
                <tr>
                    <td><?= e((string) $usuario['nombre']); ?></td>
                    <td><?= e((string) $usuario['email']); ?></td>
                    <td><span class="badge <?= (int) $usuario['estado'] === 1 ? 'bg-success' : 'bg-secondary'; ?>"><?= (int) $usuario['estado'] === 1 ? 'Activo' : 'Inactivo'; ?></span></td>
                    <td><span class="badge bg-warning text-dark"><?= e(strtoupper((string) $usuario['rol'])); ?></span></td>
                    <td>
                        <?php if ((int) $usuario['id'] !== (int) $_SESSION['user_id']): ?>
                            <form method="POST" action="index.php?action=cambiar_rol" class="d-flex gap-2">
                                <input type="hidden" name="csrf_token" value="<?= e(Session::csrfToken()); ?>">
                                <input type="hidden" name="id" value="<?= (int) $usuario['id']; ?>">
                                <select class="form-select form-select-sm" name="rol" aria-label="Rol">
                                    <?php foreach (Usuario::ROLES as $rol): ?>
                                        <option value="<?= e($rol); ?>" <?= $usuario['rol'] === $rol ? 'selected' : ''; ?>><?= e(ucfirst($rol)); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button class="btn btn-sm btn-primary" type="submit">Guardar</button>
                            </form>
                        <?php else: ?><span class="text-muted small">Sesión actual</span><?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
