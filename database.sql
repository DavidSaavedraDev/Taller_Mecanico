CREATE DATABASE IF NOT EXISTS taller_mecanico
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE taller_mecanico;

CREATE TABLE IF NOT EXISTS usuarios (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol ENUM('admin', 'mecanico', 'recepcion') NOT NULL DEFAULT 'mecanico',
    estado TINYINT(1) NOT NULL DEFAULT 1,
    reset_pin VARCHAR(255) NULL,
    pin_expiracion DATETIME NULL,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Migration for existing installations (run once before deploying the new
-- application code; CREATE TABLE statements below are safe to re-run):
-- ALTER TABLE usuarios MODIFY rol ENUM('admin', 'mecanico', 'recepcion')
--     NOT NULL DEFAULT 'mecanico';

CREATE TABLE IF NOT EXISTS audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED NULL,
    accion VARCHAR(100) NOT NULL,
    entidad VARCHAR(100) NULL,
    entidad_id BIGINT UNSIGNED NULL,
    detalles JSON NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_audit_logs_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios (id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    INDEX idx_audit_logs_accion (accion),
    INDEX idx_audit_logs_usuario (usuario_id),
    INDEX idx_audit_logs_fecha (creado_en)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS clientes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    documento VARCHAR(30) NOT NULL UNIQUE,
    nombre VARCHAR(150) NOT NULL,
    telefono VARCHAR(30) NOT NULL,
    email VARCHAR(150) NULL,
    direccion VARCHAR(255) NULL,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS vehiculos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT UNSIGNED NOT NULL,
    placa VARCHAR(15) NOT NULL UNIQUE,
    marca VARCHAR(80) NOT NULL,
    modelo VARCHAR(80) NOT NULL,
    anio SMALLINT UNSIGNED NOT NULL,
    kilometraje INT UNSIGNED NOT NULL DEFAULT 0,
    vin VARCHAR(17) NULL UNIQUE,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_vehiculos_cliente
        FOREIGN KEY (cliente_id) REFERENCES clientes (id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    INDEX idx_vehiculos_cliente (cliente_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS ordenes_trabajo (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    numero VARCHAR(30) NULL UNIQUE,
    cliente_id INT UNSIGNED NOT NULL,
    vehiculo_id INT UNSIGNED NOT NULL,
    sintomas TEXT NOT NULL,
    estado ENUM(
        'Recepcionado',
        'Diagnóstico',
        'Esperando aprobación',
        'En reparación',
        'Pruebas',
        'Listo para entregar',
        'Entregado'
    ) NOT NULL DEFAULT 'Recepcionado',
    creado_por INT UNSIGNED NULL,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_ordenes_cliente
        FOREIGN KEY (cliente_id) REFERENCES clientes (id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_ordenes_vehiculo
        FOREIGN KEY (vehiculo_id) REFERENCES vehiculos (id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_ordenes_usuario
        FOREIGN KEY (creado_por) REFERENCES usuarios (id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    INDEX idx_ordenes_estado (estado),
    INDEX idx_ordenes_cliente (cliente_id),
    INDEX idx_ordenes_vehiculo (vehiculo_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS orden_estado_historial (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    orden_id INT UNSIGNED NOT NULL,
    estado ENUM(
        'Recepcionado',
        'Diagnóstico',
        'Esperando aprobación',
        'En reparación',
        'Pruebas',
        'Listo para entregar',
        'Entregado'
    ) NOT NULL,
    comentario VARCHAR(1000) NULL,
    usuario_id INT UNSIGNED NULL,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_historial_orden
        FOREIGN KEY (orden_id) REFERENCES ordenes_trabajo (id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_historial_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios (id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    INDEX idx_historial_orden (orden_id),
    INDEX idx_historial_fecha (creado_en)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS vehiculo_fotos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    vehiculo_id INT UNSIGNED NOT NULL,
    orden_id INT UNSIGNED NOT NULL,
    ruta VARCHAR(255) NOT NULL,
    nombre_original VARCHAR(255) NOT NULL,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_fotos_vehiculo
        FOREIGN KEY (vehiculo_id) REFERENCES vehiculos (id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_fotos_orden
        FOREIGN KEY (orden_id) REFERENCES ordenes_trabajo (id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    INDEX idx_fotos_orden (orden_id)
) ENGINE=InnoDB;
