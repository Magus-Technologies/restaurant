<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin(['administrador', 'cajero', 'supervisor']);

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $action = $_GET['action'] ?? 'mesas_activas';

    if ($action === 'mesas_activas') {
        // Mesas con consumo listas para cobrar
        $mesas = DB::fetchAll("SELECT m.id, m.numero, m.zona, m.personas, m.cliente_nombre,
            o.id as orden_id, o.numero as orden_numero, o.created_at as hora_apertura,
            SUM(od.subtotal) as subtotal,
            SUM(od.subtotal) * ? as igv,
            SUM(od.subtotal) * (1 + ?) as total,
            COUNT(od.id) as total_items,
            u.nombre as mozo_nombre
            FROM mesas m
            JOIN ordenes o ON o.id_mesa = m.id AND o.estado NOT IN ('pagada','cancelada')
            JOIN orden_detalle od ON od.id_orden = o.id AND od.estado != 'cancelado'
            JOIN usuarios u ON u.id = o.id_mozo
            WHERE m.estado = 'ocupada'
            GROUP BY m.id, o.id
            ORDER BY o.created_at", [IGV, IGV]);
        jsonResponse($mesas);

    } elseif ($action === 'resumen') {
        $id_orden = $_GET['id_orden'];
        $orden = DB::fetchOne("SELECT o.*, m.numero as mesa_numero, m.zona, u.nombre as mozo_nombre
            FROM ordenes o JOIN mesas m ON m.id=o.id_mesa JOIN usuarios u ON u.id=o.id_mozo
            WHERE o.id=?", [$id_orden]);
        if (!$orden) jsonResponse(['error' => 'Orden no encontrada'], 404);

        $items = DB::fetchAll("SELECT od.*, p.nombre as plato_nombre, c.nombre as categoria
            FROM orden_detalle od 
            JOIN platos p ON p.id=od.id_plato 
            JOIN categorias c ON c.id=p.id_categoria
            WHERE od.id_orden=? AND od.estado != 'cancelado'
            ORDER BY od.created_at", [$id_orden]);

        $subtotal = array_sum(array_column($items, 'subtotal'));
        $igv = round($subtotal * IGV, 2);
        $total = $subtotal + $igv;

        jsonResponse([
            'orden'    => $orden,
            'items'    => $items,
            'subtotal' => $subtotal,
            'igv'      => $igv,
            'total'    => $total
        ]);

    } elseif ($action === 'sesion_activa') {
        $user = currentUser();
        $sesion = DB::fetchOne("SELECT * FROM caja_sesiones WHERE id_cajero=? AND estado='abierta' ORDER BY created_at DESC LIMIT 1", [$user['id']]);
        jsonResponse($sesion ?: ['abierta' => false]);

    } elseif ($action === 'resumen_dia') {
        $fecha = $_GET['fecha'] ?? date('Y-m-d');
        $stats = DB::fetchOne("SELECT
            COUNT(p.id) as total_ventas,
            SUM(p.total) as monto_total,
            SUM(CASE WHEN pm.metodo='efectivo' THEN pm.monto ELSE 0 END) as efectivo,
            SUM(CASE WHEN pm.metodo='yape' THEN pm.monto ELSE 0 END) as yape,
            SUM(CASE WHEN pm.metodo='plin' THEN pm.monto ELSE 0 END) as plin,
            SUM(CASE WHEN pm.metodo IN ('tarjeta_credito','tarjeta_debito') THEN pm.monto ELSE 0 END) as tarjeta
            FROM pagos p
            LEFT JOIN pago_metodos pm ON pm.id_pago = p.id
            WHERE DATE(p.created_at) = ?", [$fecha]);
        jsonResponse($stats);
    }

} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $action = $data['action'] ?? '';

    if ($action === 'cobrar') {
        $user = currentUser();
        $id_orden = $data['id_orden'];

        // Calcular totales reales
        $items = DB::fetchAll("SELECT subtotal FROM orden_detalle WHERE id_orden=? AND estado!='cancelado'", [$id_orden]);
        $subtotal = array_sum(array_column($items, 'subtotal'));
        
        $descuento = (float)($data['descuento'] ?? 0);
        $propina = (float)($data['propina'] ?? 0);
        $subtotal_con_desc = $subtotal - $descuento;
        $igv = round($subtotal_con_desc * IGV, 2);
        $total = $subtotal_con_desc + $igv + $propina;

        // Crear pago
        $numero_comprobante = generateNumero($data['tipo_comprobante'] === 'factura' ? 'FAC' : 'BOL');
        DB::query("INSERT INTO pagos (numero, id_orden, id_cajero, subtotal, descuento, igv, propina, total, tipo_comprobante, ruc_cliente, razon_social)
            VALUES (?,?,?,?,?,?,?,?,?,?,?)",
            [
                $numero_comprobante, $id_orden, $user['id'],
                $subtotal, $descuento, $igv, $propina, $total,
                $data['tipo_comprobante'] ?? 'ticket',
                $data['ruc'] ?? null,
                $data['razon_social'] ?? null
            ]);
        $id_pago = DB::lastInsertId();

        // Registrar métodos de pago
        foreach ($data['metodos_pago'] as $mp) {
            DB::query("INSERT INTO pago_metodos (id_pago, metodo, monto, referencia) VALUES (?,?,?,?)",
                [$id_pago, $mp['metodo'], $mp['monto'], $mp['referencia'] ?? null]);
        }

        // Cerrar orden
        DB::query("UPDATE ordenes SET estado='pagada', updated_at=NOW() WHERE id=?", [$id_orden]);
        
        // Marcar ítems como entregados
        DB::query("UPDATE orden_detalle SET estado='entregado', updated_at=NOW() WHERE id_orden=? AND estado!='cancelado'", [$id_orden]);

        // Liberar mesa
        $orden = DB::fetchOne("SELECT id_mesa FROM ordenes WHERE id=?", [$id_orden]);
        DB::query("UPDATE mesas SET estado='por_limpiar', personas=NULL, cliente_nombre=NULL WHERE id=?", [$orden['id_mesa']]);

        // Registrar en kardex (descuento de inventario ya lo hace el trigger de BD)

        jsonResponse([
            'success'   => true,
            'id_pago'   => $id_pago,
            'numero'    => $numero_comprobante,
            'total'     => $total
        ]);

    } elseif ($action === 'abrir_caja') {
        $user = currentUser();
        // Verificar que no haya caja abierta
        $abierta = DB::fetchOne("SELECT id FROM caja_sesiones WHERE id_cajero=? AND estado='abierta'", [$user['id']]);
        if ($abierta) jsonResponse(['error' => 'Ya hay una caja abierta'], 400);

        DB::query("INSERT INTO caja_sesiones (id_cajero, monto_inicial, estado) VALUES (?,?,'abierta')",
            [$user['id'], $data['monto_inicial'] ?? 0]);
        jsonResponse(['success' => true, 'id' => DB::lastInsertId()]);

    } elseif ($action === 'cerrar_caja') {
        $user = currentUser();
        $sesion = DB::fetchOne("SELECT * FROM caja_sesiones WHERE id_cajero=? AND estado='abierta'", [$user['id']]);
        if (!$sesion) jsonResponse(['error' => 'No hay caja abierta'], 400);

        // Calcular totales del día
        $stats = DB::fetchOne("SELECT SUM(p.total) as total_ventas,
            SUM(pm.monto) as total_cobrado
            FROM pagos p
            LEFT JOIN pago_metodos pm ON pm.id_pago=p.id
            WHERE p.id_cajero=? AND DATE(p.created_at)=CURDATE()", [$user['id']]);

        DB::query("UPDATE caja_sesiones SET estado='cerrada', monto_final=?, total_ventas=?, observacion=?, updated_at=NOW() WHERE id=?",
            [$data['monto_final'] ?? 0, $stats['total_ventas'] ?? 0, $data['observacion'] ?? null, $sesion['id']]);

        $diferencia = ($data['monto_final'] ?? 0) - ($sesion['monto_inicial'] + ($stats['total_ventas'] ?? 0));
        jsonResponse(['success' => true, 'diferencia' => $diferencia, 'total_ventas' => $stats['total_ventas']]);
    }

} else {
    jsonResponse(['error' => 'Método no permitido'], 405);
}
