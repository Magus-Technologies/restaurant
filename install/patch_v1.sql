-- ============================================================
-- RestaurantOS — Patch v1
-- Aplica correcciones de columnas para alinear con las APIs
-- Ejecutar DESPUÉS de database.sql si ya instalaste el sistema
-- Si instalas desde cero, usa schema_final.sql directamente
-- ============================================================

-- Tabla notificaciones: unificar nombres de columnas
ALTER TABLE notificaciones
    ADD COLUMN IF NOT EXISTS id_referencia INT AFTER id,
    ADD COLUMN IF NOT EXISTS para_rol VARCHAR(50) AFTER mensaje,
    ADD COLUMN IF NOT EXISTS id_usuario INT AFTER para_rol,
    ADD COLUMN IF NOT EXISTS leido TINYINT(1) DEFAULT 0 AFTER id_usuario;

-- Renombrar 'leida' a 'leido' si existe (MySQL no tiene RENAME COLUMN en versiones < 8)
-- En MySQL 8+:
-- ALTER TABLE notificaciones RENAME COLUMN leida TO leido;

-- Tabla delivery: agregar columna numero y updated_at
ALTER TABLE delivery
    ADD COLUMN IF NOT EXISTS numero VARCHAR(30) AFTER id,
    ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP AFTER created_at;

-- Tabla delivery: renombrar estado 'pendiente' -> 'recibido' (ENUM update)
ALTER TABLE delivery
    MODIFY COLUMN estado ENUM('recibido','en_cocina','listo','en_camino','entregado','cancelado') DEFAULT 'recibido';

-- Tabla menu_dia: renombrar columnas de FK
ALTER TABLE menu_dia
    ADD COLUMN IF NOT EXISTS id_plato_entrada INT AFTER nombre,
    ADD COLUMN IF NOT EXISTS id_plato_fondo INT AFTER id_plato_entrada,
    ADD COLUMN IF NOT EXISTS id_plato_bebida INT AFTER id_plato_fondo,
    ADD COLUMN IF NOT EXISTS cantidad_limite INT DEFAULT NULL AFTER id_plato_bebida;

-- Tabla clientes: agregar campo cumpleanos
ALTER TABLE clientes
    ADD COLUMN IF NOT EXISTS cumpleanos DATE AFTER email;

-- Tabla caja_sesiones: columnas adicionales
ALTER TABLE caja_sesiones
    ADD COLUMN IF NOT EXISTS total_ventas DECIMAL(12,2) DEFAULT 0 AFTER monto_final,
    ADD COLUMN IF NOT EXISTS observacion TEXT AFTER total_ventas,
    ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP;

-- Tabla pagos: columnas adicionales para facturación
ALTER TABLE pagos
    ADD COLUMN IF NOT EXISTS ruc_cliente VARCHAR(20) AFTER tipo_comprobante,
    ADD COLUMN IF NOT EXISTS razon_social VARCHAR(200) AFTER ruc_cliente;

-- Tabla ordenes: updated_at
ALTER TABLE ordenes
    ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP;

-- Tabla orden_detalle: updated_at
ALTER TABLE orden_detalle
    ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP;

-- Tabla compras: columnas
ALTER TABLE compras
    ADD COLUMN IF NOT EXISTS total DECIMAL(12,2) DEFAULT 0 AFTER estado,
    ADD COLUMN IF NOT EXISTS fecha_recepcion TIMESTAMP NULL AFTER total;

-- Índices de performance
CREATE INDEX IF NOT EXISTS idx_ordenes_mesa ON ordenes(id_mesa);
CREATE INDEX IF NOT EXISTS idx_ordenes_estado ON ordenes(estado);
CREATE INDEX IF NOT EXISTS idx_detalle_orden ON orden_detalle(id_orden);
CREATE INDEX IF NOT EXISTS idx_detalle_estado ON orden_detalle(estado);
CREATE INDEX IF NOT EXISTS idx_notif_usuario ON notificaciones(id_usuario);
CREATE INDEX IF NOT EXISTS idx_notif_rol ON notificaciones(para_rol);
CREATE INDEX IF NOT EXISTS idx_kardex_insumo ON kardex(id_insumo);

SELECT 'Patch v1 aplicado correctamente' AS resultado;
