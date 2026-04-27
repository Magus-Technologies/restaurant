-- ============================================================
-- SISTEMA RESTAURANTE - BASE DE DATOS
-- Compatible con MySQL 5.7+ / MariaDB 10.3+
-- ============================================================

CREATE DATABASE IF NOT EXISTS restaurant_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE restaurant_db;

-- ============================================================
-- USUARIOS Y PERMISOS
-- ============================================================
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100),
    usuario VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    rol ENUM('administrador','cajero','mozo','cocina','bar','almacen','compras','supervisor') NOT NULL DEFAULT 'mozo',
    activo TINYINT(1) DEFAULT 1,
    telefono VARCHAR(20),
    email VARCHAR(100),
    foto VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ============================================================
-- MESAS
-- ============================================================
CREATE TABLE IF NOT EXISTS mesas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero VARCHAR(10) NOT NULL,
    nombre VARCHAR(50),
    zona VARCHAR(50) DEFAULT 'salon',
    capacidad INT DEFAULT 4,
    estado ENUM('libre','ocupada','reservada','por_limpiar','cerrada') DEFAULT 'libre',
    id_mozo INT,
    observaciones TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_mozo) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- ============================================================
-- CATEGORÍAS DEL MENÚ
-- ============================================================
CREATE TABLE IF NOT EXISTS categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    icono VARCHAR(50),
    color VARCHAR(20) DEFAULT '#FF6B35',
    area ENUM('cocina_caliente','cocina_fria','bar','postres','general') DEFAULT 'general',
    orden INT DEFAULT 0,
    activo TINYINT(1) DEFAULT 1
);

-- ============================================================
-- INSUMOS / INGREDIENTES
-- ============================================================
CREATE TABLE IF NOT EXISTS insumos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) UNIQUE,
    nombre VARCHAR(200) NOT NULL,
    unidad ENUM('kg','gr','lt','ml','unidad','botella','caja','bolsa','docena') DEFAULT 'unidad',
    stock_actual DECIMAL(10,3) DEFAULT 0,
    stock_minimo DECIMAL(10,3) DEFAULT 0,
    costo_unitario DECIMAL(10,4) DEFAULT 0,
    proveedor_principal INT,
    categoria_insumo VARCHAR(100),
    fecha_vencimiento DATE,
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- PLATOS / PRODUCTOS
-- ============================================================
CREATE TABLE IF NOT EXISTS platos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50),
    nombre VARCHAR(200) NOT NULL,
    descripcion TEXT,
    id_categoria INT,
    precio DECIMAL(10,2) NOT NULL DEFAULT 0,
    precio_costo DECIMAL(10,2) DEFAULT 0,
    imagen VARCHAR(255),
    area ENUM('cocina_caliente','cocina_fria','bar','postres','general') DEFAULT 'cocina_caliente',
    disponible TINYINT(1) DEFAULT 1,
    activo TINYINT(1) DEFAULT 1,
    tiempo_preparacion INT DEFAULT 15,
    orden INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_categoria) REFERENCES categorias(id) ON DELETE SET NULL
);

-- ============================================================
-- FICHA TÉCNICA / RECETAS
-- ============================================================
CREATE TABLE IF NOT EXISTS recetas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_plato INT NOT NULL,
    id_insumo INT NOT NULL,
    cantidad DECIMAL(10,4) NOT NULL,
    unidad VARCHAR(20),
    FOREIGN KEY (id_plato) REFERENCES platos(id) ON DELETE CASCADE,
    FOREIGN KEY (id_insumo) REFERENCES insumos(id) ON DELETE CASCADE
);

-- ============================================================
-- OPCIONES / EXTRAS DEL PLATO
-- ============================================================
CREATE TABLE IF NOT EXISTS plato_opciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_plato INT NOT NULL,
    tipo ENUM('termino','extra','sin','nota') DEFAULT 'nota',
    nombre VARCHAR(100) NOT NULL,
    precio_adicional DECIMAL(10,2) DEFAULT 0,
    FOREIGN KEY (id_plato) REFERENCES platos(id) ON DELETE CASCADE
);

-- ============================================================
-- RESERVACIONES
-- ============================================================
CREATE TABLE IF NOT EXISTS reservaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_cliente VARCHAR(200) NOT NULL,
    telefono VARCHAR(20),
    email VARCHAR(100),
    fecha DATE NOT NULL,
    hora TIME NOT NULL,
    personas INT DEFAULT 2,
    id_mesa INT,
    observaciones TEXT,
    estado ENUM('pendiente','confirmada','sentada','cancelada','no_show') DEFAULT 'pendiente',
    id_usuario INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_mesa) REFERENCES mesas(id) ON DELETE SET NULL,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- ============================================================
-- MENÚ DEL DÍA
-- ============================================================
CREATE TABLE IF NOT EXISTS menu_dia (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fecha DATE NOT NULL,
    nombre VARCHAR(200),
    id_entrada INT,
    id_fondo INT,
    id_bebida INT,
    precio DECIMAL(10,2) NOT NULL,
    limite_cantidad INT DEFAULT 0,
    cantidad_vendida INT DEFAULT 0,
    activo TINYINT(1) DEFAULT 1,
    FOREIGN KEY (id_entrada) REFERENCES platos(id) ON DELETE SET NULL,
    FOREIGN KEY (id_fondo) REFERENCES platos(id) ON DELETE SET NULL,
    FOREIGN KEY (id_bebida) REFERENCES platos(id) ON DELETE SET NULL
);

-- ============================================================
-- ÓRDENES / PEDIDOS (CABECERA)
-- ============================================================
CREATE TABLE IF NOT EXISTS ordenes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero VARCHAR(20) UNIQUE NOT NULL,
    id_mesa INT,
    id_mozo INT,
    tipo ENUM('salon','delivery','recojo') DEFAULT 'salon',
    estado ENUM('abierta','enviada','preparando','lista','entregada','cobrada','cancelada') DEFAULT 'abierta',
    personas INT DEFAULT 1,
    nombre_cliente VARCHAR(200),
    observaciones TEXT,
    subtotal DECIMAL(10,2) DEFAULT 0,
    descuento DECIMAL(10,2) DEFAULT 0,
    igv DECIMAL(10,2) DEFAULT 0,
    propina DECIMAL(10,2) DEFAULT 0,
    total DECIMAL(10,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_mesa) REFERENCES mesas(id) ON DELETE SET NULL,
    FOREIGN KEY (id_mozo) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- ============================================================
-- DETALLE DE ÓRDENES
-- ============================================================
CREATE TABLE IF NOT EXISTS orden_detalle (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_orden INT NOT NULL,
    id_plato INT,
    nombre_plato VARCHAR(200),
    cantidad INT DEFAULT 1,
    precio_unitario DECIMAL(10,2) DEFAULT 0,
    precio_total DECIMAL(10,2) DEFAULT 0,
    extras TEXT,
    observacion TEXT,
    area ENUM('cocina_caliente','cocina_fria','bar','postres','general') DEFAULT 'cocina_caliente',
    estado ENUM('pendiente','en_preparacion','listo','entregado','cancelado') DEFAULT 'pendiente',
    prioridad ENUM('normal','alta','urgente') DEFAULT 'normal',
    id_cocinero INT,
    tiempo_inicio TIMESTAMP NULL,
    tiempo_listo TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_orden) REFERENCES ordenes(id) ON DELETE CASCADE,
    FOREIGN KEY (id_plato) REFERENCES platos(id) ON DELETE SET NULL,
    FOREIGN KEY (id_cocinero) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- ============================================================
-- PAGOS / COBROS
-- ============================================================
CREATE TABLE IF NOT EXISTS pagos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_orden INT NOT NULL,
    tipo_comprobante ENUM('ticket','boleta','factura') DEFAULT 'ticket',
    numero_comprobante VARCHAR(50),
    subtotal DECIMAL(10,2) DEFAULT 0,
    descuento DECIMAL(10,2) DEFAULT 0,
    igv DECIMAL(10,2) DEFAULT 0,
    propina DECIMAL(10,2) DEFAULT 0,
    total DECIMAL(10,2) DEFAULT 0,
    id_cajero INT,
    estado ENUM('pendiente','pagado','anulado') DEFAULT 'pagado',
    observaciones TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_orden) REFERENCES ordenes(id),
    FOREIGN KEY (id_cajero) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- ============================================================
-- DETALLE DE PAGOS (múltiples métodos)
-- ============================================================
CREATE TABLE IF NOT EXISTS pago_metodos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_pago INT NOT NULL,
    metodo ENUM('efectivo','yape','plin','tarjeta_credito','tarjeta_debito','transferencia','otro') NOT NULL,
    monto DECIMAL(10,2) NOT NULL,
    referencia VARCHAR(100),
    FOREIGN KEY (id_pago) REFERENCES pagos(id) ON DELETE CASCADE
);

-- ============================================================
-- CAJA
-- ============================================================
CREATE TABLE IF NOT EXISTS caja_sesiones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_cajero INT NOT NULL,
    fecha_apertura TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_cierre TIMESTAMP NULL,
    monto_inicial DECIMAL(10,2) DEFAULT 0,
    monto_final DECIMAL(10,2) DEFAULT 0,
    total_ventas DECIMAL(10,2) DEFAULT 0,
    total_efectivo DECIMAL(10,2) DEFAULT 0,
    total_digital DECIMAL(10,2) DEFAULT 0,
    diferencia DECIMAL(10,2) DEFAULT 0,
    estado ENUM('abierta','cerrada') DEFAULT 'abierta',
    observaciones TEXT,
    FOREIGN KEY (id_cajero) REFERENCES usuarios(id)
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
    direccion TEXT,
    dni VARCHAR(20),
    ruc VARCHAR(20),
    razon_social VARCHAR(200),
    fecha_nacimiento DATE,
    notas TEXT,
    total_visitas INT DEFAULT 0,
    total_gastado DECIMAL(12,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- PROVEEDORES
-- ============================================================
CREATE TABLE IF NOT EXISTS proveedores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(200) NOT NULL,
    ruc VARCHAR(20),
    telefono VARCHAR(20),
    email VARCHAR(100),
    direccion TEXT,
    contacto VARCHAR(200),
    categoria ENUM('carnes','verduras','bebidas','descartables','lacteos','abarrotes','otros') DEFAULT 'otros',
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- ÓRDENES DE COMPRA
-- ============================================================
CREATE TABLE IF NOT EXISTS compras (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero VARCHAR(50) UNIQUE,
    id_proveedor INT NOT NULL,
    id_usuario INT,
    tipo_comprobante ENUM('factura','boleta','ticket','otro') DEFAULT 'factura',
    numero_comprobante VARCHAR(100),
    fecha DATE NOT NULL,
    fecha_vencimiento_pago DATE,
    subtotal DECIMAL(10,2) DEFAULT 0,
    igv DECIMAL(10,2) DEFAULT 0,
    total DECIMAL(10,2) DEFAULT 0,
    estado ENUM('pendiente','recibido','parcial','pagado','cancelado') DEFAULT 'pendiente',
    observaciones TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_proveedor) REFERENCES proveedores(id),
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- ============================================================
-- DETALLE DE COMPRAS
-- ============================================================
CREATE TABLE IF NOT EXISTS compra_detalle (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_compra INT NOT NULL,
    id_insumo INT NOT NULL,
    cantidad DECIMAL(10,3) NOT NULL,
    costo_unitario DECIMAL(10,4) NOT NULL,
    costo_total DECIMAL(10,2) NOT NULL,
    lote VARCHAR(100),
    fecha_vencimiento DATE,
    FOREIGN KEY (id_compra) REFERENCES compras(id) ON DELETE CASCADE,
    FOREIGN KEY (id_insumo) REFERENCES insumos(id)
);

-- ============================================================
-- KARDEX (movimientos de inventario)
-- ============================================================
CREATE TABLE IF NOT EXISTS kardex (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_insumo INT NOT NULL,
    tipo ENUM('entrada','salida','merma','ajuste','transferencia') NOT NULL,
    cantidad DECIMAL(10,3) NOT NULL,
    costo_unitario DECIMAL(10,4) DEFAULT 0,
    costo_total DECIMAL(10,2) DEFAULT 0,
    stock_anterior DECIMAL(10,3) DEFAULT 0,
    stock_nuevo DECIMAL(10,3) DEFAULT 0,
    referencia VARCHAR(100),
    id_referencia INT,
    id_usuario INT,
    observacion TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_insumo) REFERENCES insumos(id),
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- ============================================================
-- DELIVERY
-- ============================================================
CREATE TABLE IF NOT EXISTS delivery (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_orden INT NOT NULL,
    id_cliente INT,
    nombre_cliente VARCHAR(200),
    telefono VARCHAR(20),
    direccion TEXT NOT NULL,
    referencia TEXT,
    metodo_pago ENUM('efectivo','yape','plin','tarjeta','otro') DEFAULT 'efectivo',
    tiempo_estimado INT DEFAULT 45,
    id_repartidor INT,
    estado ENUM('pendiente','en_cocina','listo','en_camino','entregado','cancelado') DEFAULT 'pendiente',
    notas TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_orden) REFERENCES ordenes(id),
    FOREIGN KEY (id_cliente) REFERENCES clientes(id) ON DELETE SET NULL,
    FOREIGN KEY (id_repartidor) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- ============================================================
-- NOTIFICACIONES
-- ============================================================
CREATE TABLE IF NOT EXISTS notificaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo VARCHAR(50),
    titulo VARCHAR(200),
    mensaje TEXT,
    id_origen INT,
    tabla_origen VARCHAR(50),
    roles_destino VARCHAR(200),
    leida TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- DATOS INICIALES
-- ============================================================

-- Usuario administrador (password: admin123)
INSERT INTO usuarios (nombre, apellido, usuario, password, rol) VALUES
('Administrador', 'Sistema', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'administrador'),
('Juan', 'Pérez', 'mozo1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mozo'),
('Carlos', 'López', 'cocina1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cocina'),
('María', 'García', 'cajero1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cajero');

-- Mesas
INSERT INTO mesas (numero, nombre, zona, capacidad) VALUES
('01','Mesa 01','Salón Principal',4),
('02','Mesa 02','Salón Principal',4),
('03','Mesa 03','Salón Principal',6),
('04','Mesa 04','Salón Principal',4),
('05','Mesa 05','Terraza',2),
('06','Mesa 06','Terraza',4),
('07','Mesa 07','VIP',8),
('08','Mesa 08','Salón Principal',4),
('09','Mesa 09','Bar',2),
('10','Mesa 10','Bar',2);

-- Categorías
INSERT INTO categorias (nombre, icono, color, area, orden) VALUES
('Entradas','🥗','#4CAF50','cocina_fria',1),
('Sopas','🍲','#FF9800','cocina_caliente',2),
('Platos de Fondo','🍽️','#F44336','cocina_caliente',3),
('Parrillas','🥩','#795548','cocina_caliente',4),
('Bebidas','🥤','#2196F3','bar',5),
('Cervezas','🍺','#FFC107','bar',6),
('Postres','🍰','#E91E63','postres',7),
('Menú del Día','📋','#9C27B0','cocina_caliente',8);

-- Platos
INSERT INTO platos (nombre, descripcion, id_categoria, precio, area, tiempo_preparacion) VALUES
('Ceviche Clásico','Pescado fresco en leche de tigre con choclo y camote',1,28.00,'cocina_fria',20),
('Causa Limeña','Causa de papa amarilla con atún o pollo',1,18.00,'cocina_fria',15),
('Sopa Criolla','Sopa de fideos con carne y leche',2,15.00,'cocina_caliente',20),
('Caldo de Gallina','Caldo reconfortante de gallina criolla',2,18.00,'cocina_caliente',25),
('Lomo Saltado','Clásico lomo saltado con papas fritas y arroz',3,35.00,'cocina_caliente',20),
('Arroz con Leche','Postre cremoso de arroz con leche',7,8.00,'postres',10),
('Aji de Gallina','Gallina en salsa de ají amarillo con arroz',3,30.00,'cocina_caliente',25),
('Chicharrón de Cerdo','Chicharrón crujiente con mote y salsa criolla',4,38.00,'cocina_caliente',30),
('Anticuchos','Brochetas de corazón a la parrilla',4,25.00,'cocina_caliente',20),
('Chicha Morada','Bebida tradicional de maíz morado',5,8.00,'bar',5),
('Inca Kola','Gaseosa nacional 500ml',5,6.00,'bar',2),
('Cerveza Cristal','Cerveza nacional 620ml',6,10.00,'bar',2),
('Cerveza Pilsen','Cerveza nacional 620ml',6,10.00,'bar',2),
('Mazamorra Morada','Postre tradicional de maíz morado',7,8.00,'postres',10),
('Arroz Chaufa','Arroz frito estilo chino peruano',3,28.00,'cocina_caliente',20);

-- Insumos básicos
INSERT INTO insumos (codigo, nombre, unidad, stock_actual, stock_minimo, costo_unitario) VALUES
('INS001','Carne de res','kg',50.0,10.0,22.00),
('INS002','Pollo entero','kg',40.0,10.0,8.50),
('INS003','Pescado fresco','kg',20.0,5.0,18.00),
('INS004','Papa amarilla','kg',80.0,20.0,2.50),
('INS005','Arroz','kg',100.0,20.0,3.20),
('INS006','Cebolla','kg',30.0,10.0,1.80),
('INS007','Tomate','kg',20.0,5.0,2.20),
('INS008','Ají amarillo','kg',5.0,2.0,8.00),
('INS009','Aceite vegetal','lt',20.0,5.0,6.50),
('INS010','Maíz morado','kg',10.0,3.0,5.00),
('INS011','Cerveza Cristal 620ml','unidad',120.0,24.0,6.50),
('INS012','Inca Kola 500ml','unidad',96.0,24.0,3.20),
('INS013','Limón','kg',15.0,5.0,3.50),
('INS014','Leche evaporada','unidad',24.0,6.0,4.20),
('INS015','Corazón de res','kg',10.0,3.0,12.00);

-- Recetas básicas
INSERT INTO recetas (id_plato, id_insumo, cantidad, unidad) VALUES
(5,1,0.250,'kg'),(5,4,0.200,'kg'),(5,6,0.050,'kg'),(5,7,0.040,'kg'),(5,9,0.010,'lt'),
(1,3,0.200,'kg'),(1,6,0.030,'kg'),(1,13,0.050,'kg'),
(10,10,0.150,'kg');

-- Menú del día de hoy
INSERT INTO menu_dia (fecha, nombre, id_entrada, id_fondo, id_bebida, precio, limite_cantidad) VALUES
(CURDATE(), 'Menú del Día', 3, 7, 10, 15.00, 50);

DELIMITER $$

-- Trigger: descontar inventario al marcar ítem como listo
CREATE TRIGGER IF NOT EXISTS after_detalle_listo
AFTER UPDATE ON orden_detalle
FOR EACH ROW
BEGIN
    IF NEW.estado = 'listo' AND OLD.estado != 'listo' THEN
        INSERT INTO kardex (id_insumo, tipo, cantidad, stock_anterior, stock_nuevo, referencia, id_referencia)
        SELECT 
            r.id_insumo,
            'salida',
            r.cantidad * NEW.cantidad,
            i.stock_actual,
            i.stock_actual - (r.cantidad * NEW.cantidad),
            CONCAT('Venta Orden #', NEW.id_orden),
            NEW.id_orden
        FROM recetas r
        JOIN insumos i ON i.id = r.id_insumo
        WHERE r.id_plato = NEW.id_plato;

        UPDATE insumos i
        JOIN recetas r ON r.id_insumo = i.id
        SET i.stock_actual = i.stock_actual - (r.cantidad * NEW.cantidad)
        WHERE r.id_plato = NEW.id_plato;
    END IF;
END$$

DELIMITER ;
