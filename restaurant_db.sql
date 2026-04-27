/*
 Navicat Premium Dump SQL

 Source Server         : ecommerce
 Source Server Type    : MySQL
 Source Server Version : 100527 (10.5.27-MariaDB)
 Source Host           : 173.249.36.119:3306
 Source Schema         : restaurant_db

 Target Server Type    : MySQL
 Target Server Version : 100527 (10.5.27-MariaDB)
 File Encoding         : 65001

 Date: 27/04/2026 09:02:25
*/
create database if not exists `restaurant_db` default character set latin1 collate latin1_swedish_ci;
use `restaurant_db`;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for caja_sesiones
-- ----------------------------
DROP TABLE IF EXISTS `caja_sesiones`;
CREATE TABLE `caja_sesiones`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_cajero` int NOT NULL,
  `monto_inicial` decimal(12, 2) NULL DEFAULT 0.00,
  `monto_final` decimal(12, 2) NULL DEFAULT 0.00,
  `total_ventas` decimal(12, 2) NULL DEFAULT 0.00,
  `estado` enum('abierta','cerrada') CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT 'abierta',
  `observacion` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `id_cajero`(`id_cajero` ASC) USING BTREE,
  CONSTRAINT `caja_sesiones_ibfk_1` FOREIGN KEY (`id_cajero`) REFERENCES `usuarios` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of caja_sesiones
-- ----------------------------

-- ----------------------------
-- Table structure for categorias
-- ----------------------------
DROP TABLE IF EXISTS `categorias`;
CREATE TABLE `categorias`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `area` enum('cocina','bar','postres','otros') CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT 'cocina',
  `icono` varchar(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT '',
  `color` varchar(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT '#ff6b35',
  `orden` int NULL DEFAULT 0,
  `activo` tinyint(1) NULL DEFAULT 1,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 9 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of categorias
-- ----------------------------
INSERT INTO `categorias` VALUES (1, 'Entradas', 'cocina', '????', '#4CAF50', 1, 1);
INSERT INTO `categorias` VALUES (2, 'Sopas', 'cocina', '????', '#FF9800', 2, 1);
INSERT INTO `categorias` VALUES (3, 'Platos de Fondo', 'cocina', '?????', '#F44336', 3, 1);
INSERT INTO `categorias` VALUES (4, 'Parrillas', 'cocina', '????', '#E91E63', 4, 1);
INSERT INTO `categorias` VALUES (5, 'Postres', 'postres', '????', '#9C27B0', 5, 1);
INSERT INTO `categorias` VALUES (6, 'Bebidas', 'bar', '????', '#2196F3', 6, 1);
INSERT INTO `categorias` VALUES (7, 'Cervezas', 'bar', '????', '#FFC107', 7, 1);
INSERT INTO `categorias` VALUES (8, 'Menú del Día', 'cocina', '????', '#00BCD4', 8, 1);

-- ----------------------------
-- Table structure for clientes
-- ----------------------------
DROP TABLE IF EXISTS `clientes`;
CREATE TABLE `clientes`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(200) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `apellido` varchar(200) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `telefono` varchar(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `email` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `cumpleanos` date NULL DEFAULT NULL,
  `direccion` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL,
  `dni` varchar(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `ruc` varchar(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `razon_social` varchar(200) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `notas` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of clientes
-- ----------------------------
INSERT INTO `clientes` VALUES (1, 'Miguel', NULL, '972781904', 'miguel@gmail.com', '2001-08-22', 'Santa Anita', NULL, NULL, NULL, 'Cliente VIP', '2026-04-18 13:14:26');

-- ----------------------------
-- Table structure for compra_detalle
-- ----------------------------
DROP TABLE IF EXISTS `compra_detalle`;
CREATE TABLE `compra_detalle`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_compra` int NOT NULL,
  `id_insumo` int NOT NULL,
  `cantidad` decimal(12, 3) NOT NULL,
  `precio_unitario` decimal(10, 4) NOT NULL,
  `subtotal` decimal(12, 2) NOT NULL,
  `lote` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `fecha_vencimiento` date NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `id_compra`(`id_compra` ASC) USING BTREE,
  INDEX `id_insumo`(`id_insumo` ASC) USING BTREE,
  CONSTRAINT `compra_detalle_ibfk_1` FOREIGN KEY (`id_compra`) REFERENCES `compras` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `compra_detalle_ibfk_2` FOREIGN KEY (`id_insumo`) REFERENCES `insumos` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of compra_detalle
-- ----------------------------

-- ----------------------------
-- Table structure for compras
-- ----------------------------
DROP TABLE IF EXISTS `compras`;
CREATE TABLE `compras`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `numero` varchar(30) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `id_proveedor` int NOT NULL,
  `id_usuario` int NOT NULL,
  `fecha` date NOT NULL,
  `numero_factura` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `estado` enum('pendiente','recibida','cancelada') CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT 'pendiente',
  `total` decimal(12, 2) NULL DEFAULT 0.00,
  `observacion` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL,
  `fecha_recepcion` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `numero`(`numero` ASC) USING BTREE,
  INDEX `id_proveedor`(`id_proveedor` ASC) USING BTREE,
  INDEX `id_usuario`(`id_usuario` ASC) USING BTREE,
  CONSTRAINT `compras_ibfk_1` FOREIGN KEY (`id_proveedor`) REFERENCES `proveedores` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `compras_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of compras
-- ----------------------------

-- ----------------------------
-- Table structure for configuracion
-- ----------------------------
DROP TABLE IF EXISTS `configuracion`;
CREATE TABLE `configuracion`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `clave` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `valor` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL,
  `grupo` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT 'general',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `clave`(`clave` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of configuracion
-- ----------------------------

-- ----------------------------
-- Table structure for cuentas_pagar
-- ----------------------------
DROP TABLE IF EXISTS `cuentas_pagar`;
CREATE TABLE `cuentas_pagar`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_compra` int NOT NULL,
  `id_proveedor` int NOT NULL,
  `numero_documento` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `monto_total` decimal(12, 2) NOT NULL,
  `monto_pagado` decimal(12, 2) NULL DEFAULT 0.00,
  `saldo` decimal(12, 2) NOT NULL,
  `fecha_emision` date NOT NULL,
  `fecha_vencimiento` date NULL DEFAULT NULL,
  `estado` enum('pendiente','parcial','pagada','vencida') CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT 'pendiente',
  `notas` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `id_compra`(`id_compra` ASC) USING BTREE,
  INDEX `id_proveedor`(`id_proveedor` ASC) USING BTREE,
  CONSTRAINT `cuentas_pagar_ibfk_1` FOREIGN KEY (`id_compra`) REFERENCES `compras` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `cuentas_pagar_ibfk_2` FOREIGN KEY (`id_proveedor`) REFERENCES `proveedores` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of cuentas_pagar
-- ----------------------------

-- ----------------------------
-- Table structure for delivery
-- ----------------------------
DROP TABLE IF EXISTS `delivery`;
CREATE TABLE `delivery`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `numero` varchar(30) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `id_orden` int NOT NULL,
  `id_cliente` int NULL DEFAULT NULL,
  `nombre_cliente` varchar(200) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `telefono` varchar(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `direccion` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `referencia` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL,
  `metodo_pago` enum('efectivo','yape','plin','tarjeta','otro') CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT 'efectivo',
  `tiempo_estimado` int NULL DEFAULT 30,
  `id_repartidor` int NULL DEFAULT NULL,
  `estado` enum('recibido','en_cocina','listo','en_camino','entregado','cancelado') CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT 'recibido',
  `notas` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `numero`(`numero` ASC) USING BTREE,
  INDEX `id_orden`(`id_orden` ASC) USING BTREE,
  INDEX `id_cliente`(`id_cliente` ASC) USING BTREE,
  INDEX `id_repartidor`(`id_repartidor` ASC) USING BTREE,
  INDEX `idx_delivery_estado`(`estado` ASC) USING BTREE,
  CONSTRAINT `delivery_ibfk_1` FOREIGN KEY (`id_orden`) REFERENCES `ordenes` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `delivery_ibfk_2` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT,
  CONSTRAINT `delivery_ibfk_3` FOREIGN KEY (`id_repartidor`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of delivery
-- ----------------------------

-- ----------------------------
-- Table structure for impresoras
-- ----------------------------
DROP TABLE IF EXISTS `impresoras`;
CREATE TABLE `impresoras`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `tipo` enum('tickets','cocina','caja','etiquetas') CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT 'tickets',
  `cabecera` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL,
  `pie` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL,
  `ancho_papel` int NULL DEFAULT 80,
  `activo` tinyint(1) NULL DEFAULT 1,
  `id_sucursal` int NULL DEFAULT 1,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of impresoras
-- ----------------------------

-- ----------------------------
-- Table structure for insumos
-- ----------------------------
DROP TABLE IF EXISTS `insumos`;
CREATE TABLE `insumos`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(200) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `unidad` varchar(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `stock_actual` decimal(12, 3) NULL DEFAULT 0.000,
  `stock_minimo` decimal(12, 3) NULL DEFAULT 0.000,
  `costo_unitario` decimal(10, 4) NULL DEFAULT 0.0000,
  `categoria` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT 'general',
  `id_proveedor` int NULL DEFAULT NULL,
  `activo` tinyint(1) NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `fk_insumo_proveedor`(`id_proveedor` ASC) USING BTREE,
  CONSTRAINT `fk_insumo_proveedor` FOREIGN KEY (`id_proveedor`) REFERENCES `proveedores` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 16 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of insumos
-- ----------------------------
INSERT INTO `insumos` VALUES (1, 'Pescado fresco', 'kg', 15.000, 5.000, 18.0000, 'carnes', NULL, 1, '2026-04-18 08:42:46');
INSERT INTO `insumos` VALUES (2, 'Carne de res', 'kg', 20.000, 5.000, 25.0000, 'carnes', NULL, 1, '2026-04-18 08:42:46');
INSERT INTO `insumos` VALUES (3, 'Pollo entero', 'kg', 30.000, 10.000, 8.5000, 'carnes', NULL, 1, '2026-04-18 08:42:46');
INSERT INTO `insumos` VALUES (4, 'Papa blanca', 'kg', 50.000, 10.000, 1.2000, 'verduras', NULL, 1, '2026-04-18 08:42:46');
INSERT INTO `insumos` VALUES (5, 'Papa amarilla', 'kg', 25.000, 5.000, 2.0000, 'verduras', NULL, 1, '2026-04-18 08:42:46');
INSERT INTO `insumos` VALUES (6, 'Cebolla roja', 'kg', 15.000, 3.000, 1.5000, 'verduras', NULL, 1, '2026-04-18 08:42:46');
INSERT INTO `insumos` VALUES (7, 'Tomate', 'kg', 10.000, 3.000, 1.8000, 'verduras', NULL, 1, '2026-04-18 08:42:46');
INSERT INTO `insumos` VALUES (8, 'Ají amarillo', 'kg', 3.000, 0.500, 8.0000, 'verduras', NULL, 1, '2026-04-18 08:42:46');
INSERT INTO `insumos` VALUES (9, 'Limón', 'kg', 8.000, 2.000, 2.5000, 'frutas', NULL, 1, '2026-04-18 08:42:46');
INSERT INTO `insumos` VALUES (10, 'Arroz', 'kg', 40.000, 10.000, 2.2000, 'abarrotes', NULL, 1, '2026-04-18 08:42:46');
INSERT INTO `insumos` VALUES (11, 'Aceite vegetal', 'lt', 30.000, 2.000, 4.5000, 'abarrotes', NULL, 1, '2026-04-18 08:42:46');
INSERT INTO `insumos` VALUES (12, 'Soya', 'lt', 3.000, 0.500, 5.0000, 'abarrotes', NULL, 1, '2026-04-18 08:42:46');
INSERT INTO `insumos` VALUES (13, 'Fideos', 'kg', 5.000, 1.000, 2.8000, 'abarrotes', NULL, 1, '2026-04-18 08:42:46');
INSERT INTO `insumos` VALUES (14, 'Chicha morada', 'lt', 20.000, 5.000, 1.5000, 'bebidas', NULL, 1, '2026-04-18 08:42:46');
INSERT INTO `insumos` VALUES (15, 'Gaseosa 625ml', 'unidad', 48.000, 12.000, 1.8000, 'bebidas', NULL, 1, '2026-04-18 08:42:46');

-- ----------------------------
-- Table structure for kardex
-- ----------------------------
DROP TABLE IF EXISTS `kardex`;
CREATE TABLE `kardex`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_insumo` int NOT NULL,
  `tipo` enum('entrada','salida','merma','ajuste','transferencia') CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `cantidad` decimal(12, 3) NOT NULL,
  `stock_resultante` decimal(12, 3) NOT NULL,
  `motivo` varchar(200) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `costo_unitario` decimal(10, 4) NULL DEFAULT 0.0000,
  `id_usuario` int NULL DEFAULT NULL,
  `lote` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `fecha_vencimiento` date NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `id_usuario`(`id_usuario` ASC) USING BTREE,
  INDEX `idx_kardex_insumo`(`id_insumo` ASC) USING BTREE,
  CONSTRAINT `kardex_ibfk_1` FOREIGN KEY (`id_insumo`) REFERENCES `insumos` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `kardex_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of kardex
-- ----------------------------
INSERT INTO `kardex` VALUES (1, 11, 'entrada', 20.000, 30.000, '', 200.0000, 1, NULL, NULL, '2026-04-18 13:40:58');

-- ----------------------------
-- Table structure for menu_dia
-- ----------------------------
DROP TABLE IF EXISTS `menu_dia`;
CREATE TABLE `menu_dia`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `fecha` date NOT NULL,
  `nombre` varchar(200) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT 'Menu del Dia',
  `id_plato_entrada` int NULL DEFAULT NULL,
  `id_plato_fondo` int NULL DEFAULT NULL,
  `id_plato_bebida` int NULL DEFAULT NULL,
  `precio` decimal(10, 2) NOT NULL DEFAULT 0.00,
  `cantidad_limite` int NULL DEFAULT NULL,
  `cantidad_vendida` int NULL DEFAULT 0,
  `activo` tinyint(1) NULL DEFAULT 1,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `id_plato_entrada`(`id_plato_entrada` ASC) USING BTREE,
  INDEX `id_plato_fondo`(`id_plato_fondo` ASC) USING BTREE,
  INDEX `id_plato_bebida`(`id_plato_bebida` ASC) USING BTREE,
  CONSTRAINT `menu_dia_ibfk_1` FOREIGN KEY (`id_plato_entrada`) REFERENCES `platos` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT,
  CONSTRAINT `menu_dia_ibfk_2` FOREIGN KEY (`id_plato_fondo`) REFERENCES `platos` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT,
  CONSTRAINT `menu_dia_ibfk_3` FOREIGN KEY (`id_plato_bebida`) REFERENCES `platos` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of menu_dia
-- ----------------------------

-- ----------------------------
-- Table structure for mesas
-- ----------------------------
DROP TABLE IF EXISTS `mesas`;
CREATE TABLE `mesas`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `numero` varchar(10) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `zona` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT 'salon',
  `capacidad` int NULL DEFAULT 4,
  `estado` enum('libre','ocupada','reservada','por_limpiar','cerrada') CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT 'libre',
  `personas` int NULL DEFAULT NULL,
  `cliente_nombre` varchar(200) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 12 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of mesas
-- ----------------------------
INSERT INTO `mesas` VALUES (1, '01', 'salon', 4, 'libre', NULL, NULL, '2026-04-18 08:42:46');
INSERT INTO `mesas` VALUES (2, '02', 'salon', 4, 'libre', NULL, NULL, '2026-04-18 08:42:46');
INSERT INTO `mesas` VALUES (3, '03', 'salon', 6, 'libre', NULL, NULL, '2026-04-18 08:42:46');
INSERT INTO `mesas` VALUES (4, '04', 'salon', 2, 'libre', NULL, NULL, '2026-04-18 08:42:46');
INSERT INTO `mesas` VALUES (5, '05', 'terraza', 4, 'libre', NULL, NULL, '2026-04-18 08:42:46');
INSERT INTO `mesas` VALUES (6, '06', 'terraza', 4, 'libre', NULL, NULL, '2026-04-18 08:42:46');
INSERT INTO `mesas` VALUES (7, '07', 'vip', 8, 'libre', NULL, NULL, '2026-04-18 08:42:46');
INSERT INTO `mesas` VALUES (8, '08', 'vip', 6, 'libre', NULL, NULL, '2026-04-18 08:42:46');
INSERT INTO `mesas` VALUES (9, '09', 'bar', 2, 'libre', NULL, NULL, '2026-04-18 08:42:46');
INSERT INTO `mesas` VALUES (10, '10', 'bar', 2, 'libre', NULL, NULL, '2026-04-18 08:42:46');
INSERT INTO `mesas` VALUES (11, '08', 'vip', 4, 'libre', NULL, NULL, '2026-04-18 13:12:07');

-- ----------------------------
-- Table structure for notificaciones
-- ----------------------------
DROP TABLE IF EXISTS `notificaciones`;
CREATE TABLE `notificaciones`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `tipo` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `mensaje` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `id_referencia` int NULL DEFAULT NULL,
  `para_rol` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `id_usuario` int NULL DEFAULT NULL,
  `leido` tinyint(1) NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_notif_usuario`(`id_usuario` ASC) USING BTREE,
  INDEX `idx_notif_rol`(`para_rol` ASC) USING BTREE,
  INDEX `idx_notif_leido`(`leido` ASC) USING BTREE,
  CONSTRAINT `notificaciones_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of notificaciones
-- ----------------------------

-- ----------------------------
-- Table structure for orden_detalle
-- ----------------------------
DROP TABLE IF EXISTS `orden_detalle`;
CREATE TABLE `orden_detalle`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_orden` int NOT NULL,
  `id_plato` int NOT NULL,
  `cantidad` int NOT NULL DEFAULT 1,
  `precio_unitario` decimal(10, 2) NOT NULL,
  `opciones_texto` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL,
  `observacion` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL,
  `prioridad` enum('normal','alta','urgente') CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT 'normal',
  `subtotal` decimal(10, 2) NOT NULL,
  `estado` enum('pendiente','preparando','listo','entregado','cancelado') CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT 'pendiente',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `id_plato`(`id_plato` ASC) USING BTREE,
  INDEX `idx_detalle_orden`(`id_orden` ASC) USING BTREE,
  INDEX `idx_detalle_estado`(`estado` ASC) USING BTREE,
  CONSTRAINT `orden_detalle_ibfk_1` FOREIGN KEY (`id_orden`) REFERENCES `ordenes` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `orden_detalle_ibfk_2` FOREIGN KEY (`id_plato`) REFERENCES `platos` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of orden_detalle
-- ----------------------------

-- ----------------------------
-- Table structure for ordenes
-- ----------------------------
DROP TABLE IF EXISTS `ordenes`;
CREATE TABLE `ordenes`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `numero` varchar(30) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `id_mesa` int NULL DEFAULT NULL,
  `id_mozo` int NOT NULL,
  `personas` int NULL DEFAULT 1,
  `tipo` enum('salon','delivery','recojo') CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT 'salon',
  `estado` enum('abierta','en_proceso','lista','pagada','cancelada') CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT 'abierta',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `numero`(`numero` ASC) USING BTREE,
  INDEX `id_mozo`(`id_mozo` ASC) USING BTREE,
  INDEX `idx_ordenes_estado`(`estado` ASC) USING BTREE,
  INDEX `idx_ordenes_mesa`(`id_mesa` ASC) USING BTREE,
  INDEX `idx_ordenes_fecha`(`created_at` ASC) USING BTREE,
  CONSTRAINT `ordenes_ibfk_1` FOREIGN KEY (`id_mesa`) REFERENCES `mesas` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT,
  CONSTRAINT `ordenes_ibfk_2` FOREIGN KEY (`id_mozo`) REFERENCES `usuarios` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of ordenes
-- ----------------------------

-- ----------------------------
-- Table structure for pago_metodos
-- ----------------------------
DROP TABLE IF EXISTS `pago_metodos`;
CREATE TABLE `pago_metodos`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_pago` int NOT NULL,
  `metodo` enum('efectivo','yape','plin','tarjeta_credito','tarjeta_debito','transferencia','otro') CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `monto` decimal(12, 2) NOT NULL,
  `referencia` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `id_pago`(`id_pago` ASC) USING BTREE,
  CONSTRAINT `pago_metodos_ibfk_1` FOREIGN KEY (`id_pago`) REFERENCES `pagos` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of pago_metodos
-- ----------------------------

-- ----------------------------
-- Table structure for pagos
-- ----------------------------
DROP TABLE IF EXISTS `pagos`;
CREATE TABLE `pagos`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `numero` varchar(30) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `id_orden` int NOT NULL,
  `id_cajero` int NOT NULL,
  `subtotal` decimal(12, 2) NOT NULL,
  `descuento` decimal(12, 2) NULL DEFAULT 0.00,
  `igv` decimal(12, 2) NOT NULL,
  `propina` decimal(12, 2) NULL DEFAULT 0.00,
  `total` decimal(12, 2) NOT NULL,
  `tipo_comprobante` enum('ticket','boleta','factura') CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT 'ticket',
  `ruc_cliente` varchar(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `razon_social` varchar(200) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `numero`(`numero` ASC) USING BTREE,
  INDEX `id_orden`(`id_orden` ASC) USING BTREE,
  INDEX `id_cajero`(`id_cajero` ASC) USING BTREE,
  INDEX `idx_pagos_fecha`(`created_at` ASC) USING BTREE,
  CONSTRAINT `pagos_ibfk_1` FOREIGN KEY (`id_orden`) REFERENCES `ordenes` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `pagos_ibfk_2` FOREIGN KEY (`id_cajero`) REFERENCES `usuarios` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of pagos
-- ----------------------------

-- ----------------------------
-- Table structure for pagos_proveedor
-- ----------------------------
DROP TABLE IF EXISTS `pagos_proveedor`;
CREATE TABLE `pagos_proveedor`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_cuenta_pagar` int NOT NULL,
  `monto` decimal(12, 2) NOT NULL,
  `metodo` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT 'transferencia',
  `referencia` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `fecha` date NOT NULL,
  `id_usuario` int NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `id_cuenta_pagar`(`id_cuenta_pagar` ASC) USING BTREE,
  CONSTRAINT `pagos_proveedor_ibfk_1` FOREIGN KEY (`id_cuenta_pagar`) REFERENCES `cuentas_pagar` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of pagos_proveedor
-- ----------------------------

-- ----------------------------
-- Table structure for plato_opciones
-- ----------------------------
DROP TABLE IF EXISTS `plato_opciones`;
CREATE TABLE `plato_opciones`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_plato` int NOT NULL,
  `tipo` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `nombre` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `precio_extra` decimal(8, 2) NULL DEFAULT 0.00,
  `orden` int NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `id_plato`(`id_plato` ASC) USING BTREE,
  CONSTRAINT `plato_opciones_ibfk_1` FOREIGN KEY (`id_plato`) REFERENCES `platos` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 9 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of plato_opciones
-- ----------------------------
INSERT INTO `plato_opciones` VALUES (1, 6, 'termino', 'Término 3/4', 0.00, 1);
INSERT INTO `plato_opciones` VALUES (2, 6, 'termino', 'Bien cocido', 0.00, 2);
INSERT INTO `plato_opciones` VALUES (3, 6, 'extra', 'Extra papas', 3.00, 3);
INSERT INTO `plato_opciones` VALUES (4, 6, 'sin', 'Sin cebolla', 0.00, 4);
INSERT INTO `plato_opciones` VALUES (5, 6, 'sin', 'Sin ají', 0.00, 5);
INSERT INTO `plato_opciones` VALUES (6, 1, 'extra', 'Extra leche de tigre', 2.00, 1);
INSERT INTO `plato_opciones` VALUES (7, 1, 'extra', 'Extra choclo', 2.00, 2);
INSERT INTO `plato_opciones` VALUES (8, 1, 'sin', 'Sin ají', 0.00, 3);

-- ----------------------------
-- Table structure for platos
-- ----------------------------
DROP TABLE IF EXISTS `platos`;
CREATE TABLE `platos`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(200) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `descripcion` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL,
  `precio` decimal(10, 2) NOT NULL,
  `id_categoria` int NULL DEFAULT NULL,
  `imagen` varchar(300) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `disponible` tinyint(1) NULL DEFAULT 1,
  `tiempo_prep` int NULL DEFAULT 15,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `id_categoria`(`id_categoria` ASC) USING BTREE,
  CONSTRAINT `platos_ibfk_1` FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 16 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of platos
-- ----------------------------
INSERT INTO `platos` VALUES (1, 'Ceviche Clásico', 'Pescado fresco marinado en limón con ají amarillo', 28.00, 1, NULL, 1, 10, '2026-04-18 08:42:46');
INSERT INTO `platos` VALUES (2, 'Causa Limeña', 'Causa de papa amarilla rellena de pollo o atún', 18.00, 1, NULL, 1, 12, '2026-04-18 08:42:46');
INSERT INTO `platos` VALUES (3, 'Tequeños', '8 unidades de tequeños con queso', 14.00, 1, NULL, 1, 8, '2026-04-18 08:42:46');
INSERT INTO `platos` VALUES (4, 'Sopa Criolla', 'Tradicional sopa criolla con fideos y carne', 12.00, 2, NULL, 1, 15, '2026-04-18 08:42:46');
INSERT INTO `platos` VALUES (5, 'Aguadito de Pollo', 'Caldo verde con arroz y pollo', 14.00, 2, NULL, 1, 20, '2026-04-18 08:42:46');
INSERT INTO `platos` VALUES (6, 'Lomo Saltado', 'Lomo fino con papas fritas, tomate y cebolla', 38.00, 3, NULL, 1, 18, '2026-04-18 08:42:46');
INSERT INTO `platos` VALUES (7, 'Arroz con Leche', '', 10.00, 5, NULL, 1, 5, '2026-04-18 08:42:46');
INSERT INTO `platos` VALUES (8, 'Pollo a la Brasa ½', 'Medio pollo a la brasa con papas y ensalada', 32.00, 3, NULL, 1, 25, '2026-04-18 08:42:46');
INSERT INTO `platos` VALUES (9, 'Ají de Gallina', 'Pollo desmenuzado en salsa de ají amarillo', 28.00, 3, NULL, 1, 20, '2026-04-18 08:42:46');
INSERT INTO `platos` VALUES (10, 'Parrilla Mixta', 'Costillas, chorizo, pollo y carne', 65.00, 4, NULL, 1, 30, '2026-04-18 08:42:46');
INSERT INTO `platos` VALUES (11, 'Suspiro Limeño', 'Manjar blanco con merengue de oporto', 12.00, 5, NULL, 1, 5, '2026-04-18 08:42:46');
INSERT INTO `platos` VALUES (12, 'Chicha Morada', 'Vaso de chicha morada artesanal', 6.00, 6, NULL, 1, 2, '2026-04-18 08:42:46');
INSERT INTO `platos` VALUES (13, 'Gaseosa', 'Coca-Cola, Inca Kola o Sprite', 5.00, 6, NULL, 1, 1, '2026-04-18 08:42:46');
INSERT INTO `platos` VALUES (14, 'Agua Mineral', 'Agua San Luis 625ml', 4.00, 6, NULL, 1, 1, '2026-04-18 08:42:46');
INSERT INTO `platos` VALUES (15, 'Cusqueña', 'Botella 620ml', 9.00, 7, NULL, 1, 2, '2026-04-18 08:42:46');

-- ----------------------------
-- Table structure for proveedores
-- ----------------------------
DROP TABLE IF EXISTS `proveedores`;
CREATE TABLE `proveedores`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(200) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `ruc` varchar(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `contacto` varchar(200) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `telefono` varchar(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `email` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `direccion` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL,
  `categoria` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT 'general',
  `condicion_pago` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT 'contado',
  `activo` tinyint(1) NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of proveedores
-- ----------------------------

-- ----------------------------
-- Table structure for recetas
-- ----------------------------
DROP TABLE IF EXISTS `recetas`;
CREATE TABLE `recetas`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_plato` int NOT NULL,
  `id_insumo` int NOT NULL,
  `cantidad` decimal(10, 4) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_receta`(`id_plato` ASC, `id_insumo` ASC) USING BTREE,
  INDEX `id_insumo`(`id_insumo` ASC) USING BTREE,
  CONSTRAINT `recetas_ibfk_1` FOREIGN KEY (`id_plato`) REFERENCES `platos` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `recetas_ibfk_2` FOREIGN KEY (`id_insumo`) REFERENCES `insumos` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 16 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of recetas
-- ----------------------------
INSERT INTO `recetas` VALUES (1, 1, 1, 0.2000);
INSERT INTO `recetas` VALUES (2, 1, 6, 0.0500);
INSERT INTO `recetas` VALUES (3, 1, 8, 0.0100);
INSERT INTO `recetas` VALUES (4, 1, 9, 0.0800);
INSERT INTO `recetas` VALUES (5, 6, 2, 0.2500);
INSERT INTO `recetas` VALUES (6, 6, 4, 0.2000);
INSERT INTO `recetas` VALUES (7, 6, 6, 0.0600);
INSERT INTO `recetas` VALUES (8, 6, 7, 0.0600);
INSERT INTO `recetas` VALUES (9, 6, 12, 0.0200);
INSERT INTO `recetas` VALUES (10, 6, 11, 0.0150);
INSERT INTO `recetas` VALUES (11, 8, 3, 0.5000);
INSERT INTO `recetas` VALUES (12, 8, 4, 0.3000);
INSERT INTO `recetas` VALUES (13, 9, 3, 0.2000);
INSERT INTO `recetas` VALUES (14, 9, 8, 0.0300);
INSERT INTO `recetas` VALUES (15, 9, 5, 0.1500);

-- ----------------------------
-- Table structure for reservaciones
-- ----------------------------
DROP TABLE IF EXISTS `reservaciones`;
CREATE TABLE `reservaciones`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre_cliente` varchar(200) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `telefono` varchar(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `email` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `fecha_hora` datetime NOT NULL,
  `personas` int NULL DEFAULT 2,
  `id_mesa` int NULL DEFAULT NULL,
  `observaciones` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL,
  `estado` enum('pendiente','confirmada','cancelada','no_show','completada') CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT 'pendiente',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `id_mesa`(`id_mesa` ASC) USING BTREE,
  CONSTRAINT `reservaciones_ibfk_1` FOREIGN KEY (`id_mesa`) REFERENCES `mesas` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of reservaciones
-- ----------------------------

-- ----------------------------
-- Table structure for roles_permisos
-- ----------------------------
DROP TABLE IF EXISTS `roles_permisos`;
CREATE TABLE `roles_permisos`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `rol` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `modulo` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `puede_ver` tinyint(1) NULL DEFAULT 1,
  `puede_crear` tinyint(1) NULL DEFAULT 0,
  `puede_editar` tinyint(1) NULL DEFAULT 0,
  `puede_eliminar` tinyint(1) NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_rol_modulo`(`rol` ASC, `modulo` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of roles_permisos
-- ----------------------------

-- ----------------------------
-- Table structure for sucursales
-- ----------------------------
DROP TABLE IF EXISTS `sucursales`;
CREATE TABLE `sucursales`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(200) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `direccion` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL,
  `telefono` varchar(30) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `email` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `ruc` varchar(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `activo` tinyint(1) NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of sucursales
-- ----------------------------

-- ----------------------------
-- Table structure for usuarios
-- ----------------------------
DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE `usuarios`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `apellido` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `usuario` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `password` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `rol` enum('administrador','cajero','mozo','cocina','bar','almacen','compras','supervisor') CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `email` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `telefono` varchar(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `activo` tinyint(1) NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `usuario`(`usuario` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of usuarios
-- ----------------------------
INSERT INTO `usuarios` VALUES (1, 'Administrador', 'Sistema', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'administrador', NULL, NULL, 1, '2026-04-18 08:42:46');
INSERT INTO `usuarios` VALUES (2, 'Juan', 'Pérez', 'mozo1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mozo', NULL, NULL, 1, '2026-04-18 08:42:46');
INSERT INTO `usuarios` VALUES (3, 'Carlos', 'López', 'cocina1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cocina', NULL, NULL, 1, '2026-04-18 08:42:46');
INSERT INTO `usuarios` VALUES (4, 'María', 'García', 'cajero1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cajero', NULL, NULL, 1, '2026-04-18 08:42:46');

-- ----------------------------
-- Triggers structure for table orden_detalle
-- ----------------------------
DROP TRIGGER IF EXISTS `trg_descuento_inventario`;
delimiter ;;
CREATE TRIGGER `trg_descuento_inventario` AFTER UPDATE ON `orden_detalle` FOR EACH ROW BEGIN
    IF NEW.estado = 'listo' AND OLD.estado != 'listo' THEN
        
        UPDATE insumos i
        JOIN recetas r ON r.id_insumo = i.id AND r.id_plato = NEW.id_plato
        SET i.stock_actual = i.stock_actual - (r.cantidad * NEW.cantidad)
        WHERE i.activo = 1;

        
        INSERT INTO kardex (id_insumo, tipo, cantidad, stock_resultante, motivo)
        SELECT r.id_insumo, 'salida', r.cantidad * NEW.cantidad,
               i.stock_actual, CONCAT('Venta orden #', NEW.id_orden)
        FROM recetas r
        JOIN insumos i ON i.id = r.id_insumo
        WHERE r.id_plato = NEW.id_plato AND i.activo = 1;
    END IF;
END
;;
delimiter ;

SET FOREIGN_KEY_CHECKS = 1;
