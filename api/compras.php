<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin(['administrador', 'compras', 'almacen', 'supervisor']);

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $id = $_GET['id'] ?? null;
    $estado = $_GET['estado'] ?? null;
    $fecha_ini = $_GET['fecha_ini'] ?? date('Y-m-01');
    $fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');

    if ($id) {
        $compra = DB::fetchOne("SELECT c.*, p.nombre as proveedor_nombre, p.ruc as proveedor_ruc, u.nombre as usuario_nombre
            FROM compras c JOIN proveedores p ON p.id=c.id_proveedor JOIN usuarios u ON u.id=c.id_usuario
            WHERE c.id=?", [$id]);
        if ($compra) {
            $compra['detalle'] = DB::fetchAll("SELECT cd.*, i.nombre as insumo_nombre, i.unidad
                FROM compra_detalle cd JOIN insumos i ON i.id=cd.id_insumo
                WHERE cd.id_compra=?", [$id]);
        }
        jsonResponse($compra ?: ['error' => 'No encontrada'], $compra ? 200 : 404);
    } else {
        $where_estado = $estado ? " AND c.estado=?" : '';
        $params = [$fecha_ini, $fecha_fin];
        if ($estado) $params[] = $estado;
        $compras = DB::fetchAll("SELECT c.*, p.nombre as proveedor_nombre,
            SUM(cd.subtotal) as total_calculado, COUNT(cd.id) as total_items
            FROM compras c JOIN proveedores p ON p.id=c.id_proveedor
            LEFT JOIN compra_detalle cd ON cd.id_compra=c.id
            WHERE DATE(c.fecha) BETWEEN ? AND ? $where_estado
            GROUP BY c.id ORDER BY c.fecha DESC", $params);
        jsonResponse($compras);
    }

} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $action = $data['action'] ?? 'create';
    $user = currentUser();

    if ($action === 'create') {
        $numero = generateNumero('OC');
        DB::query("INSERT INTO compras (numero, id_proveedor, id_usuario, fecha, numero_factura, estado, observacion) VALUES (?,?,?,CURDATE(),?,'pendiente',?)",
            [$numero, $data['id_proveedor'], $user['id'], $data['numero_factura'] ?? null, $data['observacion'] ?? null]);
        $id_compra = DB::lastInsertId();

        $total = 0;
        foreach ($data['items'] as $item) {
            $subtotal = $item['cantidad'] * $item['precio_unitario'];
            $total += $subtotal;
            DB::query("INSERT INTO compra_detalle (id_compra, id_insumo, cantidad, precio_unitario, subtotal, lote, fecha_vencimiento) VALUES (?,?,?,?,?,?,?)",
                [$id_compra, $item['id_insumo'], $item['cantidad'], $item['precio_unitario'], $subtotal, $item['lote'] ?? null, $item['fecha_vencimiento'] ?? null]);
        }
        DB::query("UPDATE compras SET total=? WHERE id=?", [$total, $id_compra]);

        jsonResponse(['success' => true, 'id' => $id_compra, 'numero' => $numero, 'total' => $total]);

    } elseif ($action === 'recibir') {
        // Recepción de compra: ingresa al inventario
        $compra = DB::fetchOne("SELECT * FROM compras WHERE id=? AND estado='pendiente'", [$data['id']]);
        if (!$compra) jsonResponse(['error' => 'Compra no encontrada o ya recibida'], 400);

        $detalle = DB::fetchAll("SELECT * FROM compra_detalle WHERE id_compra=?", [$data['id']]);
        foreach ($detalle as $item) {
            DB::query("UPDATE insumos SET stock_actual = stock_actual + ?, costo_unitario=? WHERE id=?",
                [$item['cantidad'], $item['precio_unitario'], $item['id_insumo']]);
            $insumo = DB::fetchOne("SELECT stock_actual FROM insumos WHERE id=?", [$item['id_insumo']]);
            DB::query("INSERT INTO kardex (id_insumo, tipo, cantidad, stock_resultante, motivo, costo_unitario, id_usuario, lote, fecha_vencimiento) VALUES (?,?,?,?,'Compra #{$compra['numero']}',?,?,?,?)",
                [$item['id_insumo'], 'entrada', $item['cantidad'], $insumo['stock_actual'],
                 $item['precio_unitario'], $user['id'], $item['lote'], $item['fecha_vencimiento']]);
        }

        DB::query("UPDATE compras SET estado='recibida', fecha_recepcion=NOW() WHERE id=?", [$data['id']]);

        // Auto-crear cuenta por pagar si es a crédito
        $proveedor = DB::fetchOne("SELECT condicion_pago FROM proveedores WHERE id=?", [$compra['id_proveedor']]);
        $condicion  = $proveedor['condicion_pago'] ?? 'contado';
        if ($condicion !== 'contado') {
            $dias = (int)filter_var($condicion, FILTER_SANITIZE_NUMBER_INT);
            $vencimiento = $dias > 0 ? date('Y-m-d', strtotime("+{$dias} days")) : null;
            // Create table if not exists
            DB::query("CREATE TABLE IF NOT EXISTS cuentas_pagar (
                id INT AUTO_INCREMENT PRIMARY KEY,
                id_compra INT NOT NULL, id_proveedor INT NOT NULL,
                numero_documento VARCHAR(50), monto_total DECIMAL(12,2) NOT NULL,
                monto_pagado DECIMAL(12,2) DEFAULT 0, saldo DECIMAL(12,2) NOT NULL,
                fecha_emision DATE NOT NULL, fecha_vencimiento DATE,
                estado ENUM('pendiente','parcial','pagada','vencida') DEFAULT 'pendiente',
                notas TEXT, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");
            DB::query("INSERT INTO cuentas_pagar (id_compra, id_proveedor, numero_documento, monto_total, saldo, fecha_emision, fecha_vencimiento)
                VALUES (?,?,?,?,?,CURDATE(),?)",
                [$compra['id'], $compra['id_proveedor'], $compra['numero_factura'], $compra['total'], $compra['total'], $vencimiento]);
        }

        jsonResponse(['success' => true, 'genero_cuenta_pagar' => $condicion !== 'contado']);

    } elseif ($action === 'cancelar') {
        DB::query("UPDATE compras SET estado='cancelada' WHERE id=? AND estado='pendiente'", [$data['id']]);
        jsonResponse(['success' => true]);
    }

} else {
    jsonResponse(['error' => 'Método no permitido'], 405);
}
