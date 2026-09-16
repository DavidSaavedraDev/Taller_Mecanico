<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Taller Mecánico Pro</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body class="bg-light d-flex flex-column min-vh-100">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold text-warning" href="index.php">
            <i class="fa-solid fa-wrench me-2"></i>TALLER MECÁNICO
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <!-- Menú de Navegación Principal -->
            <ul class="navbar-nav me-auto">
                <?php if (Authorization::can('manage_clients')): ?>
                <li class="nav-item">
                    <a class="nav-link <?= strtolower((string) ($_GET['action'] ?? 'clientes')) === 'clientes' ? 'active' : ''; ?>" href="index.php?action=clientes">
                        <i class="fa-solid fa-users me-1"></i> Clientes
                    </a>
                </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link <?= in_array(strtolower((string) ($_GET['action'] ?? '')), ['ordenes', 'crear_orden', 'guardar_orden', 'ver_orden', 'actualizar_estado_orden'], true) ? 'active' : ''; ?>" href="index.php?action=ordenes">
                        <i class="fa-solid fa-clipboard-list me-1"></i> Órdenes
                    </a>
                </li>
                <?php if (Authorization::can('manage_users')): ?>
                <li class="nav-item">
                    <a class="nav-link <?= in_array(strtolower((string) ($_GET['action'] ?? '')), ['usuarios', 'cambiar_rol'], true) ? 'active' : ''; ?>" href="index.php?action=usuarios">
                        <i class="fa-solid fa-user-shield me-1"></i> Usuarios
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= in_array(strtolower((string) ($_GET['action'] ?? '')), ['auditoria', 'audit_logs'], true) ? 'active' : ''; ?>" href="index.php?action=auditoria">
                        <i class="fa-solid fa-clock-rotate-left me-1"></i> Auditoría
                    </a>
                </li>
                <?php endif; ?>
            </ul>

            <!-- Sección de Usuario y Rol -->
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="d-flex align-items-center gap-3">
                    <div class="text-end text-white">
                        <span class="d-block fw-bold small text-light">
                            <i class="fa-solid fa-circle-user text-warning me-1"></i>
                            <?= htmlspecialchars($_SESSION['user_nombre'] ?? 'Usuario'); ?>
                        </span>
                        <span class="badge bg-warning text-dark font-monospace">
                            <?= strtoupper($_SESSION['user_rol'] ?? 'mecanico'); ?>
                        </span>
                    </div>
                    <a href="index.php?action=logout" class="btn btn-outline-danger btn-sm fw-bold">
                        <i class="fa-solid fa-right-from-bracket me-1"></i> Salir
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</nav>

<div class="container flex-grow-1">