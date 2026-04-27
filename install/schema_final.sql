-- ============================================================
-- RestaurantOS — Schema Final v1.0
-- Compatible con MySQL 8.0+ / MariaDB 10.5+
-- Para instalación nueva: usa ESTE archivo (no database.sql)
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;
SET sql_mode = 'NO_ENGINE_SUBSTITUTION';

CREATE DATABASE IF NOT EXISTS restaurant_db
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE restaurant_db;

-- ============================================================
-- USUARIOS Y AUTENTICACIÓN
-- ============================================================
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100),
    usuario VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    rol ENUM('administrador','cajero','mozo','cocina','bar','almacen','compras','supervisor') NOT NULL,
    email VARCHAR(100),
    telefono VARCHAR(20),
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- MESAS
-- ============================================================
CREATE TABLE IF NOT EXISTS mesas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero VARCHAR(10) NOT NULL,
    zona VARCHAR(50) DEFAULT 'salon',
    capacidad INT DEFAULT 4,
    estado ENUM('libre','ocupada','reservada','por_limpiar','cerrada') DEFAULT 'libre',
    personas INT DEFAULT NULL,
    cliente_nombre VARCHAR(200) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- MENÚ
-- ============================================================
CREATE TABLE IF NOT EXISTS categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    area ENUM('cocina','bar','postres','otros') DEFAULT 'cocina',
    icono VARCHAR(20) DEFAULT '',
    color VARCHAR(20) DEFAULT '#ff6b35',
    orden INT DEFAULT 0,
    activo TINYINT(1) DEFAULT 1
);

CREATE TABLE IF NOT EXISTS platos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(200) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10,2) NOT NULL,
    id_categoria INT,
    imagen VARCHAR(300),
    disponible TINYINT(1) DEFAULT 1,
    tiempo_prep INT DEFAULT 15, -- minutos
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_categoria) REFERENCES categorias(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS plato_opciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_plato INT NOT NULL,
    tipo VARCHAR(50), -- 'termino','extra','sin','acompanamiento'
    nombre VARCHAR(100) NOT NULL,
    precio_extra DECIMAL(8,2) DEFAULT 0,
    orden INT DEFAULT 0,
    FOREIGN KEY (id_plato) REFERENCES platos(id) ON DELETE CASCADE
);

-- ============================================================
-- INVENTARIO
-- ============================================================
CREATE TABLE IF NOT EXISTS insumos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(200) NOT NULL,
    unidad VARCHAR(20) NOT NULL, -- kg, g, lt, ml, unidad, botella
    stock_actual DECIMAL(12,3) DEFAULT 0,
    stock_minimo DECIMAL(12,3) DEFAULT 0,
    costo_unitario DECIMAL(10,4) DEFAULT 0,
    categoria VARCHAR(100) DEFAULT 'general',
    id_proveedor INT,
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS recetas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_plato INT NOT NULL,
    id_insumo INT NOT NULL,
    cantidad DECIMAL(10,4) NOT NULL,
    UNIQUE KEY uk_receta (id_plato, id_insumo),
    FOREIGN KEY (id_plato) REFERENCES platos(id) ON DELETE CASCADE,
    FOREIGN KEY (id_insumo) REFERENCES insumos(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS kardex (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_insumo INT NOT NULL,
    tipo ENUM('entrada','salida','merma','ajuste','transferencia') NOT NULL,
    cantidad DECIMAL(12,3) NOT NULL,
    stock_resultante DECIMAL(12,3) NOT NULL,
    motivo VARCHAR(200),
    costo_unitario DECIMAL(10,4) DEFAULT 0,
    id_usuario INT,
    lote VARCHAR(100),
    fecha_vencimiento DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_insumo) REFERENCES insumos(id),
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- ============================================================
-- PROVEEDORES Y COMPRAS
-- ============================================================
CREATE TABLE IF NOT EXISTS proveedores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(200) NOT NULL,
    ruc VARCHAR(20),
    contacto VARCHAR(200),
    telefono VARCHAR(20),
    email VARCHAR(100),
    direccion TEXT,
    categoria VARCHAR(100) DEFAULT 'general',
    condicion_pago VARCHAR(50) DEFAULT 'contado',
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- FK diferida para insumos -> proveedor
ALTER TABLE insumos ADD CONSTRAINT fk_insumo_proveedor
    FOREIGN KEY (id_proveedor) REFERENCES proveedores(id) ON DELETE SET NULL;

CREATE TABLE IF NOT EXISTS compras (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero VARCHAR(30) UNIQUE NOT NULL,
    id_proveedor INT NOT NULL,
    id_usuario INT NOT NULL,
    fecha DATE NOT NULL,
    numero_factura VARCHAR(50),
    estado ENUM('pendiente','recibida','cancelada') DEFAULT 'pendiente',
    total DECIMAL(12,2) DEFAULT 0,
    observacion TEXT,
    fecha_recepcion TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_proveedor) REFERENCES proveedores(id),
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id)
);

CREATE TABLE IF NOT EXISTS compra_detalle (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_compra INT NOT NULL,
    id_insumo INT NOT NULL,
    cantidad DECIMAL(12,3) NOT NULL,
    precio_unitario DECIMAL(10,4) NOT NULL,
    subtotal DECIMAL(12,2) NOT NULL,
    lote VARCHAR(100),
    fecha_vencimiento DATE,
    FOREIGN KEY (id_compra) REFERENCES compras(id) ON DELETE CASCADE,
    FOREIGN KEY (id_insumo) REFERENCES insumos(id)
);

-- ============================================================
-- RESERVACIONES
-- ============================================================
CREATE TABLE IF NOT EXISTS reservaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_cliente VARCHAR(200) NOT NULL,
    telefono VARCHAR(20),
    email VARCHAR(100),
    fecha_hora DATETIME NOT NULL,
    personas INT DEFAULT 2,
    id_mesa INT,
    observaciones TEXT,
    estado ENUM('pendiente','confirmada','cancelada','no_show','completada') DEFAULT 'pendiente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_mesa) REFERENCES mesas(id) ON DELETE SET NULL
);

-- ============================================================
-- MENÚ DEL DÍA
-- ============================================================
CREATE TABLE IF NOT EXISTS menu_dia (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fecha DATE NOT NULL,
    nombre VARCHAR(200) DEFAULT 'Menu del Dia',
    id_plato_entrada INT,
    id_plato_fondo INT,
    id_plato_bebida INT,
    precio DECIMAL(10,2) NOT NULL DEFAULT 0,
    cantidad_limite INT DEFAULT NULL, -- NULL = ilimitado
    cantidad_vendida INT DEFAULT 0,
    activo TINYINT(1) DEFAULT 1,
    FOREIGN KEY (id_plato_entrada) REFERENCES platos(id) ON DELETE SET NULL,
    FOREIGN KEY (id_plato_fondo) REFERENCES platos(id) ON DELETE SET NULL,
    FOREIGN KEY (id_plato_bebida) REFERENCES platos(id) ON DELETE SET NULL
);

-- ============================================================
-- CLIENTES (CRM)
-- ============================================================
CREATE TABLE IF NOT EXISTS clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(200) NOT NULL,
    apellido VARCHAR(200),
    telefono VARCHAR(20),
    email VARCHAR(100),
    cumpleanos DATE,
    direccion TEXT,
    dni VARCHAR(20),
    ruc VARCHAR(20),
    razon_social VARCHAR(200),
    notas TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- ÓRDENES
-- ============================================================
CREATE TABLE IF NOT EXISTS ordenes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero VARCHAR(30) UNIQUE NOT NULL,
    id_mesa INT,
    id_mozo INT NOT NULL,
    personas INT DEFAULT 1,
    tipo ENUM('salon','delivery','recojo') DEFAULT 'salon',
    estado ENUM('abierta','en_proceso','lista','pagada','cancelada') DEFAULT 'abierta',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_mesa) REFERENCES mesas(id) ON DELETE SET NULL,
    FOREIGN KEY (id_mozo) REFERENCES usuarios(id)
);

CREATE TABLE IF NOT EXISTS orden_detalle (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_orden INT NOT NULL,
    id_plato INT NOT NULL,
    cantidad INT NOT NULL DEFAULT 1,
    precio_unitario DECIMAL(10,2) NOT NULL,
    opciones_texto TEXT,
    observacion TEXT,
    prioridad ENUM('normal','alta','urgente') DEFAULT 'normal',
    subtotal DECIMAL(10,2) NOT NULL,
    estado ENUM('pendiente','preparando','listo','entregado','cancelado') DEFAULT 'pendiente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_orden) REFERENCES ordenes(id) ON DELETE CASCADE,
    FOREIGN KEY (id_plato) REFERENCES platos(id)
);

-- ============================================================
-- CAJA Y PAGOS
-- ============================================================
CREATE TABLE IF NOT EXISTS caja_sesiones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_cajero INT NOT NULL,
    monto_inicial DECIMAL(12,2) DEFAULT 0,
    monto_final DECIMAL(12,2) DEFAULT 0,
    total_ventas DECIMAL(12,2) DEFAULT 0,
    estado ENUM('abierta','cerrada') DEFAULT 'abierta',
    observacion TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_cajero) REFERENCES usuarios(id)
);

CREATE TABLE IF NOT EXISTS pagos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero VARCHAR(30) UNIQUE NOT NULL,
    id_orden INT NOT NULL,
    id_cajero INT NOT NULL,
    subtotal DECIMAL(12,2) NOT NULL,
    descuento DECIMAL(12,2) DEFAULT 0,
    igv DECIMAL(12,2) NOT NULL,
    propina DECIMAL(12,2) DEFAULT 0,
    total DECIMAL(12,2) NOT NULL,
    tipo_comprobante ENUM('ticket','boleta','factura') DEFAULT 'ticket',
    ruc_cliente VARCHAR(20),
    razon_social VARCHAR(200),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_orden) REFERENCES ordenes(id),
    FOREIGN KEY (id_cajero) REFERENCES usuarios(id)
);

CREATE TABLE IF NOT EXISTS pago_metodos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_pago INT NOT NULL,
    metodo ENUM('efectivo','yape','plin','tarjeta_credito','tarjeta_debito','transferencia','otro') NOT NULL,
    monto DECIMAL(12,2) NOT NULL,
    referencia VARCHAR(100),
    FOREIGN KEY (id_pago) REFERENCES pagos(id) ON DELETE CASCADE
);

-- ============================================================
-- DELIVERY
-- ============================================================
CREATE TABLE IF NOT EXISTS delivery (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero VARCHAR(30) UNIQUE NOT NULL,
    id_orden INT NOT NULL,
    id_cliente INT,
    nombre_cliente VARCHAR(200) NOT NULL,
    telefono VARCHAR(20),
    direccion TEXT NOT NULL,
    referencia TEXT,
    metodo_pago ENUM('efectivo','yape','plin','tarjeta','otro') DEFAULT 'efectivo',
    tiempo_estimado INT DEFAULT 30,
    id_repartidor INT,
    estado ENUM('recibido','en_cocina','listo','en_camino','entregado','cancelado') DEFAULT 'recibido',
    notas TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_orden) REFERENCES ordenes(id),
    FOREIGN KEY (id_cliente) REFERENCES clientes(id) ON DELETE SET NULL,
    FOREIGN KEY (id_repartidor) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- ============================================================
-- NOTIFICACIONES
-- ============================================================
CREATE TABLE IF NOT EXISTS notificaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo VARCHAR(50) NOT NULL,
    mensaje TEXT NOT NULL,
    id_referencia INT,
    para_rol VARCHAR(50),   -- 'mozo','cocina','cajero', etc.
    id_usuario INT,         -- destinatario específico (opcional)
    leido TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- ============================================================
-- ÍNDICES
-- ============================================================
CREATE INDEX idx_ordenes_estado     ON ordenes(estado);
CREATE INDEX idx_ordenes_mesa       ON ordenes(id_mesa);
CREATE INDEX idx_ordenes_fecha      ON ordenes(created_at);
CREATE INDEX idx_detalle_orden      ON orden_detalle(id_orden);
CREATE INDEX idx_detalle_estado     ON orden_detalle(estado);
CREATE INDEX idx_pagos_fecha        ON pagos(created_at);
CREATE INDEX idx_kardex_insumo      ON kardex(id_insumo);
CREATE INDEX idx_notif_usuario      ON notificaciones(id_usuario);
CREATE INDEX idx_notif_rol          ON notificaciones(para_rol);
CREATE INDEX idx_notif_leido        ON notificaciones(leido);
CREATE INDEX idx_delivery_estado    ON delivery(estado);

-- ============================================================
-- TRIGGER: descuento automático de inventario al marcar listo
-- ============================================================
DROP TRIGGER IF EXISTS trg_descuento_inventario;
DELIMITER $$
CREATE TRIGGER trg_descuento_inventario
AFTER UPDATE ON orden_detalle
FOR EACH ROW
BEGIN
    IF NEW.estado = 'listo' AND OLD.estado != 'listo' THEN
        -- Descontar insumos según receta
        UPDATE insumos i
        JOIN recetas r ON r.id_insumo = i.id AND r.id_plato = NEW.id_plato
        SET i.stock_actual = i.stock_actual - (r.cantidad * NEW.cantidad)
        WHERE i.activo = 1;

        -- Registrar en kardex
        INSERT INTO kardex (id_insumo, tipo, cantidad, stock_resultante, motivo)
        SELECT r.id_insumo, 'salida', r.cantidad * NEW.cantidad,
               i.stock_actual, CONCAT('Venta orden #', NEW.id_orden)
        FROM recetas r
        JOIN insumos i ON i.id = r.id_insumo
        WHERE r.id_plato = NEW.id_plato AND i.activo = 1;
    END IF;
END$$
DELIMITER ;

-- ============================================================
-- DATOS DEMO (password: "password" para todos)
-- Hash: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
-- ============================================================
INSERT INTO usuarios (nombre, apellido, usuario, password, rol) VALUES
('Administrador', 'Sistema',  'admin',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'administrador'),
('Juan',          'Pérez',    'mozo1',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mozo'),
('Carlos',        'López',    'cocina1',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cocina'),
('María',         'García',   'cajero1',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cajero');

INSERT INTO mesas (numero, zona, capacidad) VALUES
('01','salon',4),('02','salon',4),('03','salon',6),('04','salon',2),
('05','terraza',4),('06','terraza',4),('07','vip',8),('08','vip',6),
('09','bar',2),('10','bar',2);

INSERT INTO categorias (nombre, area, icono, color, orden) VALUES
('Entradas',        'cocina',  '🥗', '#4CAF50', 1),
('Sopas',           'cocina',  '🍲', '#FF9800', 2),
('Platos de Fondo', 'cocina',  '🍽️', '#F44336', 3),
('Parrillas',       'cocina',  '🔥', '#E91E63', 4),
('Postres',         'postres', '🍰', '#9C27B0', 5),
('Bebidas',         'bar',     '🥤', '#2196F3', 6),
('Cervezas',        'bar',     '🍺', '#FFC107', 7),
('Menú del Día',    'cocina',  '📋', '#00BCD4', 8);

INSERT INTO platos (nombre, descripcion, precio, id_categoria, tiempo_prep) VALUES
-- Entradas
('Ceviche Clásico',      'Pescado fresco marinado en limón con ají amarillo',      28.00, 1, 10),
('Causa Limeña',         'Causa de papa amarilla rellena de pollo o atún',         18.00, 1, 12),
('Tequeños',             '8 unidades de tequeños con queso',                       14.00, 1,  8),
-- Sopas
('Sopa Criolla',         'Tradicional sopa criolla con fideos y carne',             12.00, 2, 15),
('Aguadito de Pollo',    'Caldo verde con arroz y pollo',                          14.00, 2, 20),
-- Platos de Fondo
('Lomo Saltado',         'Lomo fino con papas fritas, tomate y cebolla',           38.00, 3, 18),
('Arroz con Leche',      '',                                                        10.00, 5,  5),
('Pollo a la Brasa ½',  'Medio pollo a la brasa con papas y ensalada',            32.00, 3, 25),
('Ají de Gallina',       'Pollo desmenuzado en salsa de ají amarillo',             28.00, 3, 20),
-- Parrillas
('Parrilla Mixta',       'Costillas, chorizo, pollo y carne',                      65.00, 4, 30),
-- Postres
('Suspiro Limeño',       'Manjar blanco con merengue de oporto',                   12.00, 5,  5),
-- Bebidas
('Chicha Morada',        'Vaso de chicha morada artesanal',                         6.00, 6,  2),
('Gaseosa',              'Coca-Cola, Inca Kola o Sprite',                           5.00, 6,  1),
('Agua Mineral',         'Agua San Luis 625ml',                                     4.00, 6,  1),
-- Cervezas
('Cusqueña',             'Botella 620ml',                                           9.00, 7,  2);

INSERT INTO insumos (nombre, unidad, stock_actual, stock_minimo, costo_unitario, categoria) VALUES
('Pescado fresco',   'kg',   15.000,  5.000,  18.00, 'carnes'),
('Carne de res',     'kg',   20.000,  5.000,  25.00, 'carnes'),
('Pollo entero',     'kg',   30.000, 10.000,   8.50, 'carnes'),
('Papa blanca',      'kg',   50.000, 10.000,   1.20, 'verduras'),
('Papa amarilla',    'kg',   25.000,  5.000,   2.00, 'verduras'),
('Cebolla roja',     'kg',   15.000,  3.000,   1.50, 'verduras'),
('Tomate',           'kg',   10.000,  3.000,   1.80, 'verduras'),
('Ají amarillo',     'kg',    3.000,  0.500,   8.00, 'verduras'),
('Limón',            'kg',    8.000,  2.000,   2.50, 'frutas'),
('Arroz',            'kg',   40.000, 10.000,   2.20, 'abarrotes'),
('Aceite vegetal',   'lt',   10.000,  2.000,   4.50, 'abarrotes'),
('Soya',             'lt',    3.000,  0.500,   5.00, 'abarrotes'),
('Fideos',           'kg',    5.000,  1.000,   2.80, 'abarrotes'),
('Chicha morada',    'lt',   20.000,  5.000,   1.50, 'bebidas'),
('Gaseosa 625ml',    'unidad',48.000, 12.000,   1.80, 'bebidas');

-- Recetas básicas
INSERT INTO recetas (id_plato, id_insumo, cantidad) VALUES
-- Ceviche (id=1): pescado 200g, cebolla 50g, ají 10g, limón 80g
(1, 1, 0.200),(1, 6, 0.050),(1, 8, 0.010),(1, 9, 0.080),
-- Lomo Saltado (id=6): carne 250g, papa 200g, cebolla 60g, tomate 60g, soya 20ml, aceite 15ml
(6, 2, 0.250),(6, 4, 0.200),(6, 6, 0.060),(6, 7, 0.060),(6, 12, 0.020),(6, 11, 0.015),
-- Pollo a la Brasa ½ (id=8): pollo 500g, papa 300g
(8, 3, 0.500),(8, 4, 0.300),
-- Ají de Gallina (id=9): pollo 200g, ají 30g, papa 150g
(9, 3, 0.200),(9, 8, 0.030),(9, 5, 0.150);

-- Opciones para Lomo Saltado (id=6)
INSERT INTO plato_opciones (id_plato, tipo, nombre, precio_extra, orden) VALUES
(6, 'termino', 'Término 3/4',    0,    1),
(6, 'termino', 'Bien cocido',    0,    2),
(6, 'extra',   'Extra papas',    3.00, 3),
(6, 'sin',     'Sin cebolla',    0,    4),
(6, 'sin',     'Sin ají',        0,    5);

-- Opciones para Ceviche (id=1)
INSERT INTO plato_opciones (id_plato, tipo, nombre, precio_extra, orden) VALUES
(1, 'extra', 'Extra leche de tigre', 2.00, 1),
(1, 'extra', 'Extra choclo',         2.00, 2),
(1, 'sin',   'Sin ají',              0,    3);

SET FOREIGN_KEY_CHECKS = 1;

SELECT 'RestaurantOS schema_final.sql instalado correctamente ✓' AS resultado;

-- ============================================================
-- CONFIGURACIÓN DEL SISTEMA (v2)
-- ============================================================
CREATE TABLE IF NOT EXISTS configuracion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    clave VARCHAR(100) UNIQUE NOT NULL,
    valor TEXT,
    grupo VARCHAR(50) DEFAULT 'general',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS sucursales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(200) NOT NULL,
    direccion TEXT,
    telefono VARCHAR(30),
    email VARCHAR(100),
    ruc VARCHAR(20),
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS impresoras (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    tipo ENUM('tickets','cocina','caja','etiquetas') DEFAULT 'tickets',
    cabecera TEXT,
    pie TEXT,
    ancho_papel INT DEFAULT 48,
    activo TINYINT(1) DEFAULT 1,
    id_sucursal INT DEFAULT 1
);

CREATE TABLE IF NOT EXISTS roles_permisos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rol VARCHAR(50) NOT NULL,
    modulo VARCHAR(50) NOT NULL,
    puede_ver TINYINT(1) DEFAULT 1,
    puede_crear TINYINT(1) DEFAULT 0,
    puede_editar TINYINT(1) DEFAULT 0,
    puede_eliminar TINYINT(1) DEFAULT 0,
    UNIQUE KEY uk_rol_modulo (rol, modulo)
);

CREATE TABLE IF NOT EXISTS cuentas_pagar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_compra INT NOT NULL,
    id_proveedor INT NOT NULL,
    numero_documento VARCHAR(50),
    monto_total DECIMAL(12,2) NOT NULL,
    monto_pagado DECIMAL(12,2) DEFAULT 0,
    saldo DECIMAL(12,2) NOT NULL,
    fecha_emision DATE NOT NULL,
    fecha_vencimiento DATE,
    estado ENUM('pendiente','parcial','pagada','vencida') DEFAULT 'pendiente',
    notas TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_compra) REFERENCES compras(id),
    FOREIGN KEY (id_proveedor) REFERENCES proveedores(id)
);

CREATE TABLE IF NOT EXISTS pagos_proveedor (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_cuenta_pagar INT NOT NULL,
    monto DECIMAL(12,2) NOT NULL,
    metodo VARCHAR(50) DEFAULT 'transferencia',
    referencia VARCHAR(100),
    fecha DATE NOT NULL,
    id_usuario INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_cuenta_pagar) REFERENCES cuentas_pagar(id),
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- Sucursal principal por defecto
INSERT IGNORE INTO sucursales (id, nombre, activo) VALUES (1, 'Principal', 1);

-- Configuración inicial
INSERT IGNORE INTO configuracion (clave, valor, grupo) VALUES
('nombre_restaurante', 'Mi Restaurante', 'restaurante'),
('moneda', 'S/', 'fiscal'),
('igv', '18', 'fiscal'),
('propina_default', '0', 'sistema'),
('alerta_cocina_min', '15', 'sistema'),
('alerta_stock', '1', 'sistema'),
('descuento_inventario_auto', '1', 'sistema');

SELECT 'Schema v2 (modulos adicionales) instalado correctamente' AS resultado;
