<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white fw-bold">
                <i class="fa-solid fa-user-plus me-2"></i> Registrar Nuevo Cliente
            </div>
            <div class="card-body p-4">
                <form action="index.php?action=guardar_cliente" method="POST">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Documento / Cédula / NIT *</label>
                            <input type="text" name="documento" class="form-control" placeholder="Ej: 12345678" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nombre Completo *</label>
                            <input type="text" name="nombre" class="form-control" placeholder="Ej: Juan Pérez" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Teléfono *</label>
                            <input type="text" name="telefono" class="form-control" placeholder="Ej: +57 300 1234567" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Correo Electrónico</label>
                            <input type="email" name="email" class="form-control" placeholder="cliente@correo.com">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Dirección</label>
                            <textarea name="direccion" class="form-control" rows="2" placeholder="Dirección de residencia o taller"></textarea>
                        </div>
                    </div>
                    <hr class="my-4">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="index.php?action=clientes" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary fw-bold">
                            <i class="fa-solid fa-floppy-disk me-1"></i> Guardar Cliente
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>