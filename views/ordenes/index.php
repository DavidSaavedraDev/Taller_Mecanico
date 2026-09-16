<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-1"><i class="fa-solid fa-clipboard-list me-2 text-primary"></i>Órdenes de trabajo</h2>
        <p class="text-muted mb-0">Recepción y seguimiento del trabajo de cada vehículo.</p>
    </div>
    <?php if (Authorization::can('create_orders')): ?>
        <a href="index.php?action=crear_orden" class="btn btn-primary fw-bold">
            <i class="fa-solid fa-car-side me-1"></i> Nueva recepción
        </a>
    <?php endif; ?>
</div>

<?php if (isset($_GET['msg']) && $_GET['msg'] === 'guardado'): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fa-solid fa-circle-check me-1"></i> Orden creada correctamente.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
<?php endif; ?>

<?php $error = Session::getFlash('error'); ?>
<?php if ($error): ?>
    <div class="alert alert-danger" role="alert">
        <i class="fa-solid fa-circle-exclamation me-1"></i><?= e($error); ?>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Orden</th>
                        <th>Cliente</th>
                        <th>Vehículo</th>
                        <th>Recepción</th>
                        <th>Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($ordenes)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                <i class="fa-solid fa-car fa-2x mb-2 d-block"></i>
                                No hay órdenes de trabajo registradas.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($ordenes as $orden): ?>
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
                            <tr>
                                <td class="fw-bold"><?= e((string) ($orden['numero'] ?? '')); ?></td>
                                <td>
                                    <?= e((string) $orden['cliente_nombre']); ?>
                                    <small class="d-block text-muted"><?= e((string) $orden['sintomas']); ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border"><?= e((string) $orden['placa']); ?></span>
                                    <span class="d-block small"><?= e(trim($orden['marca'] . ' ' . $orden['modelo'])); ?></span>
                                </td>
                                <td><?= e(date('d/m/Y H:i', strtotime((string) $orden['creado_en']))); ?></td>
                                <td><span class="badge <?= e($claseEstado); ?>"><?= e((string) $orden['estado']); ?></span></td>
                                <td class="text-center">
                                    <a href="index.php?action=ver_orden&id=<?= (int) $orden['id']; ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="fa-solid fa-eye me-1"></i> Ver
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
