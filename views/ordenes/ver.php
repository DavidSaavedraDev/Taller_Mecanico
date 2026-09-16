<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<?php
    $estadoClases = [
        'Recepcionado' => 'bg-secondary',
        'Diagnóstico' => 'bg-info text-dark',
        'Esperando aprobación' => 'bg-warning text-dark',
        'En reparación' => 'bg-primary',
        'Pruebas' => 'bg-dark',
        'Listo para entregar' => 'bg-success',
        'Entregado' => 'bg-success',
    ];
    $claseEstado = $estadoClases[$orden['estado']] ?? 'bg-secondary';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-1"><i class="fa-solid fa-file-invoice me-2 text-primary"></i><?= e((string) $orden['numero']); ?></h2>
        <p class="text-muted mb-0">Detalle de recepción y seguimiento de la orden.</p>
    </div>
    <a href="index.php?action=ordenes" class="btn btn-outline-secondary">
        <i class="fa-solid fa-arrow-left me-1"></i> Volver a órdenes
    </a>
</div>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fa-solid fa-circle-check me-1"></i>
        <?= $_GET['msg'] === 'estado_actualizado' ? 'Estado actualizado correctamente.' : 'Orden creada correctamente.'; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
<?php endif; ?>

<?php $error = Session::getFlash('error'); ?>
<?php if ($error): ?>
    <div class="alert alert-danger" role="alert"><?= e($error); ?></div>
<?php endif; ?>
<?php $warning = Session::getFlash('warning'); ?>
<?php if ($warning): ?>
    <div class="alert alert-warning" role="alert">
        <i class="fa-solid fa-triangle-exclamation me-1"></i><?= e($warning); ?>
    </div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header d-flex justify-content-between align-items-center bg-dark text-white">
                <span class="fw-bold"><i class="fa-solid fa-car me-2"></i>Vehículo</span>
                <span class="badge <?= e($claseEstado); ?>"><?= e((string) $orden['estado']); ?></span>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4"><small class="text-muted d-block">Placa</small><strong><?= e((string) $orden['placa']); ?></strong></div>
                    <div class="col-md-4"><small class="text-muted d-block">Marca / modelo</small><strong><?= e($orden['marca'] . ' ' . $orden['modelo']); ?></strong></div>
                    <div class="col-md-4"><small class="text-muted d-block">Año</small><strong><?= (int) $orden['anio']; ?></strong></div>
                    <div class="col-md-4"><small class="text-muted d-block">Kilometraje</small><strong><?= number_format((int) $orden['kilometraje'], 0, ',', '.'); ?> km</strong></div>
                    <div class="col-md-8"><small class="text-muted d-block">VIN</small><strong><?= e((string) ($orden['vin'] ?: 'No registrado')); ?></strong></div>
                </div>
                <hr>
                <small class="text-muted d-block">Síntomas o solicitud</small>
                <p class="mb-0"><?= nl2br(e((string) $orden['sintomas'])); ?></p>
            </div>
        </div>

        <?php if (!empty($fotos)): ?>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light fw-bold"><i class="fa-solid fa-images me-2"></i>Fotos de recepción</div>
                <div class="card-body">
                    <div class="row g-3">
                        <?php foreach ($fotos as $foto): ?>
                            <div class="col-sm-4">
                                <a href="<?= e((string) $foto['ruta']); ?>" target="_blank" rel="noopener">
                                    <img src="<?= e((string) $foto['ruta']); ?>" alt="<?= e((string) $foto['nombre_original']); ?>" class="img-fluid rounded border" style="height: 150px; width: 100%; object-fit: cover;">
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light fw-bold"><i class="fa-solid fa-clock-rotate-left me-2"></i>Historial de estados</div>
            <div class="card-body p-0">
                <?php if (empty($historial)): ?>
                    <p class="text-muted p-3 mb-0">No hay movimientos registrados.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead><tr><th>Fecha</th><th>Estado</th><th>Comentario</th><th>Usuario</th></tr></thead>
                            <tbody>
                                <?php foreach ($historial as $movimiento): ?>
                                    <tr>
                                        <td><?= e(date('d/m/Y H:i', strtotime((string) $movimiento['creado_en']))); ?></td>
                                        <td><span class="badge <?= e($estadoClases[$movimiento['estado']] ?? 'bg-secondary'); ?>"><?= e((string) $movimiento['estado']); ?></span></td>
                                        <td><?= e((string) ($movimiento['comentario'] ?? '')); ?></td>
                                        <td><?= e((string) ($movimiento['usuario_nombre'] ?? 'Sistema')); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-primary text-white fw-bold"><i class="fa-solid fa-user me-2"></i>Cliente</div>
            <div class="card-body">
                <h5 class="mb-1"><?= e((string) $orden['cliente_nombre']); ?></h5>
                <p class="text-muted mb-2"><?= e((string) $orden['cliente_documento']); ?></p>
                <div><i class="fa-solid fa-phone me-2 text-primary"></i><?= e((string) $orden['cliente_telefono']); ?></div>
                <?php if (!empty($orden['cliente_email'])): ?>
                    <div><i class="fa-solid fa-envelope me-2 text-primary"></i><?= e((string) $orden['cliente_email']); ?></div>
                <?php endif; ?>
            </div>
        </div>

        <?php if (Authorization::can('update_order_status')): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light fw-bold"><i class="fa-solid fa-pen-to-square me-2"></i>Actualizar estado</div>
            <div class="card-body">
                <form action="index.php?action=actualizar_estado_orden" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= e(Session::csrfToken()); ?>">
                    <input type="hidden" name="id" value="<?= (int) $orden['id']; ?>">
                    <label for="estado" class="form-label fw-bold">Nuevo estado</label>
                    <select name="estado" id="estado" class="form-select mb-3" required>
                        <?php foreach (OrdenTrabajo::ESTADOS as $estado): ?>
                            <option value="<?= e($estado); ?>" <?= $orden['estado'] === $estado ? 'selected' : ''; ?>><?= e($estado); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label for="comentario" class="form-label fw-bold">Comentario</label>
                    <textarea name="comentario" id="comentario" class="form-control mb-3" rows="3" maxlength="1000" placeholder="Notas del avance (opcional)"></textarea>
                    <button type="submit" class="btn btn-primary w-100 fw-bold">
                        <i class="fa-solid fa-arrows-rotate me-1"></i> Guardar estado
                    </button>
                </form>
            </div>
            <?php else: ?>
                <div class="alert alert-info"><i class="fa-solid fa-circle-info me-1"></i>Esta vista es de solo lectura para tu rol.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
