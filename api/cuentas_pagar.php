<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin(['administrador', 'compras', 'supervisor', 'cajero']);

// Ensure table exists
DB::query("CREATE TABLE IF NOT EXISTS cuentas_pagar (
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
)");

DB::query("CREATE TABLE IF NOT EXISTS pagos_proveedor (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_cuenta_pagar INT NOT NULL,
    monto DECIMAL(12,2) NOT NULL,
    metodo VARCHAR(50) DEFAULT 'transferencia',
    referencia VARCHAR(100),
    fecha DATE NOT NULL,
    id_usuario INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_cuenta_pagar) REFERENCES cuentas_pagar(id)
)");

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $action = $_GET['action'] ?? 'list';

    if ($action === 'list') {
        $estado = $_GET['estado'] ?? null;
        $where = $estado ? "WHERE cp.estado=?" : "WHERE cp.estado != 'pagada'";
        $params = $estado ? [$estado] : [];
        $cuentas = DB::fetchAll("SELECT cp.*, p.nombre as proveedor_nombre, c.numero as compra_numero
            FROM cuentas_pagar cp
            JOIN proveedores p ON p.id = cp.id_proveedor
            JOIN compras c ON c.id = cp.id_compra
            $where ORDER BY cp.fecha_vencimiento ASC", $params);

        // Mark overdue
        foreach ($cuentas as &$c) {
            if ($c['estado'] === 'pendiente' && $c['fecha_vencimiento'] && $c['fecha_vencimiento'] < date('Y-m-d')) {
                DB::query("UPDATE cuentas_pagar SET estado='vencida' WHERE id=?", [$c['id']]);
                $c['estado'] = 'vencida';
            }
        }
        jsonResponse($cuentas);

    } elseif ($action === 'resumen') {
        $stats = DB::fetchOne("SELECT
            COUNT(*) as total_cuentas,
            SUM(saldo) as total_saldo,
            SUM(CASE WHEN estado='vencida' THEN saldo ELSE 0 END) as saldo_vencido,
            SUM(CASE WHEN fecha_vencimiento <= DATE_ADD(CURDATE(), INTERVAL 7 DAY) AND estado != 'pagada' THEN saldo ELSE 0 END) as vence_pronto
            FROM cuentas_pagar WHERE estado != 'pagada'");
        jsonResponse($stats);

    } elseif ($action === 'pagos') {
        $id = $_GET['id'] ?? null;
        if ($id) jsonResponse(DB::fetchAll("SELECT pp.*, u.nombre as usuario_nombre FROM pagos_proveedor pp LEFT JOIN usuarios u ON u.id=pp.id_usuario WHERE pp.id_cuenta_pagar=? ORDER BY pp.fecha DESC", [$id]));
    }

} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $action = $data['action'] ?? '';
    $user = currentUser();

    if ($action === 'registrar_pago') {
        $cuenta = DB::fetchOne("SELECT * FROM cuentas_pagar WHERE id=?", [$data['id_cuenta']]);
        if (!$cuenta) jsonResponse(['error' => 'Cuenta no encontrada'], 404);

        $monto = (float)$data['monto'];
        if ($monto <= 0 || $monto > $cuenta['saldo']) jsonResponse(['error' => 'Monto inválido'], 400);

        DB::query("INSERT INTO pagos_proveedor (id_cuenta_pagar, monto, metodo, referencia, fecha, id_usuario) VALUES (?,?,?,?,?,?)",
            [$data['id_cuenta'], $monto, $data['metodo'] ?? 'transferencia', $data['referencia'] ?? null, $data['fecha'] ?? date('Y-m-d'), $user['id']]);

        $nuevo_pagado = $cuenta['monto_pagado'] + $monto;
        $nuevo_saldo  = $cuenta['monto_total'] - $nuevo_pagado;
        $nuevo_estado = $nuevo_saldo <= 0 ? 'pagada' : 'parcial';

        DB::query("UPDATE cuentas_pagar SET monto_pagado=?, saldo=?, estado=? WHERE id=?",
            [$nuevo_pagado, max(0, $nuevo_saldo), $nuevo_estado, $data['id_cuenta']]);

        jsonResponse(['success' => true, 'saldo_restante' => max(0, $nuevo_saldo)]);

    } elseif ($action === 'crear') {
        DB::query("INSERT INTO cuentas_pagar (id_compra, id_proveedor, numero_documento, monto_total, saldo, fecha_emision, fecha_vencimiento, notas) VALUES (?,?,?,?,?,?,?,?)",
            [$data['id_compra'], $data['id_proveedor'], $data['numero_documento'] ?? null,
             $data['monto_total'], $data['monto_total'], $data['fecha_emision'] ?? date('Y-m-d'),
             $data['fecha_vencimiento'] ?? null, $data['notas'] ?? null]);
        jsonResponse(['success' => true, 'id' => DB::lastInsertId()]);
    }
}
