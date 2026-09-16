<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-1"><i class="fa-solid fa-clock-rotate-left me-2 text-primary"></i>Auditoría</h2>
        <p class="text-muted mb-0">Registro de accesos y cambios relevantes del sistema.</p>
    </div>
    <a href="index.php?action=usuarios" class="btn btn-outline-secondary">Volver a usuarios</a>
</div>

<form method="GET" class="card card-body border-0 shadow-sm mb-3">
    <input type="hidden" name="action" value="auditoria">
    <div class="row g-2 align-items-end">
        <div class="col-md-5">
            <label class="form-label">Acción</label>
            <select name="accion" class="form-select">
                <option value="">Todas</option>
                <?php foreach ($acciones as $opcion): ?>
                    <option value="<?= e($opcion); ?>" <?= $accion === $opcion ? 'selected' : ''; ?>><?= e($opcion); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-5">
            <label class="form-label">Usuario</label>
            <select name="usuario_id" class="form-select">
                <option value="">Todos</option>
                <?php foreach ($usuarios as $usuario): ?>
                    <option value="<?= (int) $usuario['id']; ?>" <?= (int) $usuarioId === (int) $usuario['id'] ? 'selected' : ''; ?>><?= e($usuario['nombre'] . ' (' . $usuario['email'] . ')'); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2"><button class="btn btn-primary w-100" type="submit">Filtrar</button></div>
    </div>
</form>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-sm table-hover align-middle mb-0">
            <thead class="table-dark"><tr><th>Fecha</th><th>Usuario</th><th>Acción</th><th>Entidad</th><th>Detalles</th><th>IP</th></tr></thead>
            <tbody>
            <?php if (empty($resultado['items'])): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">No hay registros para los filtros seleccionados.</td></tr>
            <?php else: foreach ($resultado['items'] as $log): ?>
                <tr>
                    <td><?= e(date('d/m/Y H:i:s', strtotime((string) $log['creado_en']))); ?></td>
                    <td><?= e((string) ($log['usuario_nombre'] ?? 'Sistema')); ?></td>
                    <td><code><?= e((string) $log['accion']); ?></code></td>
                    <td><?= e((string) ($log['entidad'] ?? '')); ?><?= $log['entidad_id'] ? ' #' . (int) $log['entidad_id'] : ''; ?></td>
                    <td class="small"><?= e((string) ($log['detalles'] ?? '{}')); ?></td>
                    <td><?= e((string) ($log['ip_address'] ?? '')); ?></td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($resultado['pages'] > 1): ?>
<nav class="mt-3" aria-label="Paginación de auditoría">
    <ul class="pagination justify-content-center">
        <?php for ($pagina = 1; $pagina <= $resultado['pages']; $pagina++): ?>
            <li class="page-item <?= $pagina === $resultado['page'] ? 'active' : ''; ?>">
                <a class="page-link" href="index.php?action=auditoria&page=<?= $pagina; ?>&accion=<?= urlencode($accion); ?>&usuario_id=<?= (int) ($usuarioId ?? 0); ?>"><?= $pagina; ?></a>
            </li>
        <?php endfor; ?>
    </ul>
</nav>
<?php endif; ?>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
