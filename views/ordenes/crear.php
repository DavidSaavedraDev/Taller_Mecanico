<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-1"><i class="fa-solid fa-car-side me-2 text-primary"></i>Nueva recepción</h2>
        <p class="text-muted mb-0">Registra el vehículo y los síntomas reportados por el cliente.</p>
    </div>
    <a href="index.php?action=ordenes" class="btn btn-outline-secondary">
        <i class="fa-solid fa-arrow-left me-1"></i> Volver
    </a>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger" role="alert">
        <i class="fa-solid fa-circle-exclamation me-1"></i><?= e($error); ?>
    </div>
<?php endif; ?>

<form action="index.php?action=guardar_orden" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= e(Session::csrfToken()); ?>">

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-dark text-white fw-bold">
                    <i class="fa-solid fa-car me-2"></i>Datos del vehículo
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="cliente_id" class="form-label fw-bold">Cliente *</label>
                        <select name="cliente_id" id="cliente_id" class="form-select" required>
                            <option value="">Selecciona un cliente</option>
                            <?php foreach ($clientes as $cliente): ?>
                                <option value="<?= (int) $cliente['id']; ?>">
                                    <?= e($cliente['nombre'] . ' - ' . $cliente['documento']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (empty($clientes)): ?>
                            <small class="text-danger">Debes registrar un cliente antes de crear una recepción.</small>
                        <?php endif; ?>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="placa" class="form-label fw-bold">Placa *</label>
                            <input type="text" name="placa" id="placa" class="form-control text-uppercase" maxlength="15" required>
                        </div>
                        <div class="col-md-6">
                            <label for="vin" class="form-label fw-bold">VIN</label>
                            <input type="text" name="vin" id="vin" class="form-control text-uppercase" maxlength="17">
                        </div>
                        <div class="col-md-6">
                            <label for="marca" class="form-label fw-bold">Marca *</label>
                            <input type="text" name="marca" id="marca" class="form-control" maxlength="80" required>
                        </div>
                        <div class="col-md-6">
                            <label for="modelo" class="form-label fw-bold">Modelo *</label>
                            <input type="text" name="modelo" id="modelo" class="form-control" maxlength="80" required>
                        </div>
                        <div class="col-md-6">
                            <label for="anio" class="form-label fw-bold">Año *</label>
                            <input type="number" name="anio" id="anio" class="form-control" min="1900" max="<?= (int) date('Y') + 1; ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="kilometraje" class="form-label fw-bold">Kilometraje *</label>
                            <input type="number" name="kilometraje" id="kilometraje" class="form-control" min="0" required>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary text-white fw-bold">
                    <i class="fa-solid fa-clipboard-question me-2"></i>Motivo de ingreso
                </div>
                <div class="card-body">
                    <label for="sintomas" class="form-label fw-bold">Síntomas o solicitud del cliente *</label>
                    <textarea name="sintomas" id="sintomas" class="form-control" rows="6" maxlength="2000" required placeholder="Describe las fallas, ruidos o servicios solicitados..."></textarea>
                    <div class="form-text">La orden comenzará en estado Recepcionado.</div>
                </div>
            </div>
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light fw-bold">
                    <i class="fa-solid fa-camera me-2"></i>Fotos del vehículo <span class="fw-normal text-muted">(opcional)</span>
                </div>
                <div class="card-body">
                    <input type="file" name="fotos[]" class="form-control" accept="image/jpeg,image/png,image/webp" multiple>
                    <div class="form-text">Hasta 5 MB por foto. Formatos: JPG, PNG o WEBP.</div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-4">
        <a href="index.php?action=ordenes" class="btn btn-secondary">Cancelar</a>
        <button type="submit" class="btn btn-primary fw-bold" <?= empty($clientes) ? 'disabled' : ''; ?>>
            <i class="fa-solid fa-floppy-disk me-1"></i> Registrar recepción
        </button>
    </div>
</form>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
