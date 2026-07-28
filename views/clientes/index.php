<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<!-- Encabezado de la Sección -->
<div class="row align-items-center mb-4">
    <div class="col-md-6">
        <h2 class="fw-bold mb-0 text-dark">
            <i class="fa-solid fa-users text-primary me-2"></i>Gestión de Clientes
        </h2>
        <p class="text-muted mb-0 small">Administra los datos de los clientes del taller</p>
    </div>
    <div class="col-md-6 text-md-end mt-3 mt-md-0">
        <a href="index.php?action=crear_cliente" class="btn btn-success fw-bold shadow-sm">
            <i class="fa-solid fa-user-plus me-1"></i> Nuevo Cliente
        </a>
    </div>
</div>

<!-- Alertas de estado (Éxito / Edición / Eliminación) -->
<?php if (isset($_GET['msg'])): ?>
    <?php if ($_GET['msg'] === 'guardado'): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i><strong>¡Cliente registrado con éxito!</strong> El registro ya está disponible.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php elseif ($_GET['msg'] === 'actualizado'): ?>
        <div class="alert alert-info alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i><strong>¡Cliente actualizado!</strong> Los cambios se guardaron correctamente.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php elseif ($_GET['msg'] === 'eliminado'): ?>
        <div class="alert alert-warning alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="fa-solid fa-trash me-2"></i><strong>Cliente eliminado.</strong> El registro fue removido del sistema.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
<?php endif; ?>

<!-- Tabla Principal de Clientes -->
<div class="card border-0 shadow-sm rounded-3 overflow-hidden">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-dark text-white">
                    <tr>
                        <th class="py-3 px-3">#</th>
                        <th class="py-3">Documento</th>
                        <th class="py-3">Cliente / Razón Social</th>
                        <th class="py-3">Teléfono</th>
                        <th class="py-3">Correo</th>
                        <th class="py-3">Dirección</th>
                        <th class="py-3 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($clientes)): ?>
                        <?php foreach ($clientes as $c): ?>
                            <tr>
                                <td class="px-3 fw-bold text-secondary">#<?= htmlspecialchars($c['id']); ?></td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        <i class="fa-solid fa-id-card me-1 text-muted"></i><?= htmlspecialchars($c['documento']); ?>
                                    </span>
                                </td>
                                <td class="fw-bold text-dark"><?= htmlspecialchars($c['nombre']); ?></td>
                                <td>
                                    <?php if (!empty($c['telefono'])): ?>
                                        <a href="tel:<?= htmlspecialchars($c['telefono']); ?>" class="text-decoration-none text-dark">
                                            <i class="fa-solid fa-phone text-success me-1"></i><?= htmlspecialchars($c['telefono']); ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted small">Sin Registro</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= !empty($c['email']) ? htmlspecialchars($c['email']) : '<span class="text-muted small">Sin Registro</span>'; ?></td>
                                <td><?= !empty($c['direccion']) ? htmlspecialchars($c['direccion']) : '<span class="text-muted small">Sin Registro</span>'; ?></td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <!-- Botón Editar -->
                                        <a href="index.php?action=editar_cliente&id=<?= $c['id']; ?>" class="btn btn-outline-primary" title="Editar Cliente">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>

                                        <!-- Botón Ver Vehículos -->
                                        <a href="index.php?action=vehiculos_cliente&id=<?= $c['id']; ?>" class="btn btn-outline-info" title="Ver Vehículos">
                                            <i class="fa-solid fa-car"></i>
                                        </a>

                                        <!-- Botón Eliminar (Solo Admin) -->
                                        <?php if (isset($_SESSION['user_rol']) && $_SESSION['user_rol'] === 'admin'): ?>
                                            <form action="index.php?action=eliminar_cliente" method="POST" class="d-inline" onsubmit="return confirm('¿Estás seguro de eliminar este cliente? Esta acción no se puede deshacer.');">
                                                <input type="hidden" name="id" value="<?= $c['id']; ?>">
                                                <button type="submit" class="btn btn-outline-danger" title="Eliminar Cliente">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-folder-open fa-3x mb-3 text-secondary d-block"></i>
                                <h5>No hay clientes registrados en el sistema</h5>
                                <p class="small">Haz clic en <strong>"Nuevo Cliente"</strong> para ingresar el primero.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>