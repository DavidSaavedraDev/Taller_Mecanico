<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0 fw-bold"><i class="fa-solid fa-user-pen me-2"></i>Editar Cliente</h5>
            </div>
            <div class="card-body p-4">
                <form action="index.php?action=actualizar_cliente" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= e(Session::csrfToken()); ?>">
                    <input type="hidden" name="id" value="<?= $cliente['id']; ?>">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Documento / Cédula / NIT</label>
                            <input type="text" name="documento" class="form-control" value="<?= htmlspecialchars($cliente['documento']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nombre Completo / Razón Social</label>
                            <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($cliente['nombre']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Teléfono</label>
                            <input type="text" name="telefono" class="form-control" value="<?= htmlspecialchars($cliente['telefono']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Correo Electrónico</label>
                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($cliente['email']); ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Dirección</label>
                            <input type="text" name="direccion" class="form-control" value="<?= htmlspecialchars($cliente['direccion']); ?>">
                        </div>
                    </div>

                    <div class="mt-4 text-end">
                        <a href="index.php?action=clientes" class="btn btn-secondary me-2">Cancelar</a>
                        <button type="submit" class="btn btn-primary fw-bold"><i class="fa-solid fa-floppy-disk me-1"></i> Actualizar Cliente</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>