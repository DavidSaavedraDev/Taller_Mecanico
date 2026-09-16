<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-1"><i class="fa-solid fa-users me-2 text-primary"></i>Clientes</h2>
        <p class="text-muted mb-0">Lista de clientes registrados en el sistema.</p>
    </div>
    <?php if (Authorization::can('manage_clients')): ?>
        <a href="index.php?action=crear_cliente" class="btn btn-primary fw-bold">
            <i class="fa-solid fa-user-plus me-1"></i> Nuevo cliente
        </a>
    <?php endif; ?>
</div>

<?php if (isset($_GET['msg'])): ?>
    <?php
        $messages = [
            'guardado' => ['success', 'Cliente registrado correctamente.'],
            'actualizado' => ['info', 'Cliente actualizado correctamente.'],
            'eliminado' => ['warning', 'Cliente eliminado correctamente.'],
        ];
        $msg = $_GET['msg'];
        $alert = $messages[$msg] ?? ['success', 'Operación realizada correctamente.'];
    ?>
    <div class="alert alert-<?= $alert[0]; ?> alert-dismissible fade show" role="alert">
        <?= e($alert[1]); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
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
                        <th>#</th>
                        <th>Documento</th>
                        <th>Nombre</th>
                        <th>Teléfono</th>
                        <th>Email</th>
                        <th>Dirección</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($clientes)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No hay clientes registrados.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($clientes as $cliente): ?>
                            <tr>
                                <td><?= (int) $cliente['id']; ?></td>
                                <td><?= e($cliente['documento'] ?? ''); ?></td>
                                <td><?= e($cliente['nombre'] ?? ''); ?></td>
                                <td><?= e($cliente['telefono'] ?? ''); ?></td>
                                <td><?= e($cliente['email'] ?? ''); ?></td>
                                <td><?= e($cliente['direccion'] ?? ''); ?></td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <?php if (Authorization::can('manage_clients')): ?>
                                            <a href="index.php?action=editar_cliente&id=<?= (int) $cliente['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </a>
                                            <form action="index.php?action=eliminar_cliente" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar este cliente?');">
                                                <input type="hidden" name="csrf_token" value="<?= e(Session::csrfToken()); ?>">
                                                <input type="hidden" name="id" value="<?= (int) $cliente['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-muted small">Solo lectura</span>
                                        <?php endif; ?>
                                    </div>
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