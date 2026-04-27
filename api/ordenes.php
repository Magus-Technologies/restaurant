<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin(['administrador', 'mozo', 'cajero', 'supervisor']);

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $id = $_GET['id'] ?? null;
    $mesa = $_GET['mesa'] ?? null;
    $estado = $_GET['estado'] ?? null;

    if ($id) {
        $orden = DB::fetchOne("SELECT o.*, m.numero as mesa_numero, m.zona,
            u.nombre as mozo_nombre
            FROM ordenes o
            JOIN mesas m ON m.id = o.id_mesa
            JOIN usuarios u ON u.id = o.id_mozo
            WHERE o.id = ?", [$id]);
        if ($orden) {
            $orden['items'] = DB::fetchAll("SELECT od.*, p.nombre as plato_nombre, p.tiempo_prep,
                c.nombre as categoria_nombre, c.area
                FROM orden_detalle od
                JOIN platos p ON p.id = od.id_plato
                JOIN categorias c ON c.id = p.id_categoria
                WHERE od.id_orden = ?
                ORDER BY od.created_at", [$id]);
        }
        jsonResponse($orden ?: ['error' => 'No encontrado'], $orden ? 200 : 404);
    } elseif ($mesa) {
        $orden = DB::fetchOne("SELECT o.*, m.numero as mesa_numero
            FROM ordenes o JOIN mesas m ON m.id = o.id_mesa
            WHERE o.id_mesa = ? AND o.estado NOT IN ('pagada','cancelada')
            ORDER BY o.created_at DESC LIMIT 1", [$mesa]);
        if ($orden) {
            $orden['items'] = DB::fetchAll("SELECT od.*, p.nombre as plato_nombre, c.area
                FROM orden_detalle od
                JOIN platos p ON p.id = od.id_plato
                JOIN categorias c ON c.id = p.id_categoria
                WHERE od.id_orden = ? AND od.estado != 'cancelado'
                ORDER BY od.created_at", [$orden['id']]);
        }
        jsonResponse($orden ?: ['error' => 'Sin orden activa']);
    } elseif ($estado) {
        $ordenes = DB::fetchAll("SELECT o.*, m.numero as mesa_numero, u.nombre as mozo_nombre
            FROM ordenes o JOIN mesas m ON m.id = o.id_mesa JOIN usuarios u ON u.id = o.id_mozo
            WHERE o.estado = ? ORDER BY o.created_at DESC", [$estado]);
        jsonResponse($ordenes);
    } else {
        // Órdenes activas del día
        $ordenes = DB::fetchAll("SELECT o.*, m.numero as mesa_numero, m.zona, u.nombre as mozo_nombre,
            COUNT(od.id) as total_items,
            SUM(od.subtotal) as total
            FROM ordenes o
            JOIN mesas m ON m.id = o.id_mesa
            JOIN usuarios u ON u.id = o.id_mozo
            LEFT JOIN orden_detalle od ON od.id_orden = o.id AND od.estado != 'cancelado'
            WHERE o.estado NOT IN ('pagada','cancelada') AND DATE(o.created_at) = CURDATE()
            GROUP BY o.id
            ORDER BY o.created_at DESC");
        jsonResponse($ordenes);
    }

} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $action = $data['action'] ?? '';

    if ($action === 'agregar_items') {
        $user = currentUser();
        $id_orden = $data['id_orden'];
        
        // Verificar que la orden existe
        $orden = DB::fetchOne("SELECT * FROM ordenes WHERE id = ? AND estado NOT IN ('pagada','cancelada')", [$id_orden]);
        if (!$orden) {
            jsonResponse(['error' => 'Orden no encontrada o cerrada'], 404);
        }

        $items_insertados = 0;
        foreach ($data['items'] as $item) {
            $plato = DB::fetchOne("SELECT precio FROM platos WHERE id = ?", [$item['id_plato']]);
            if (!$plato) continue;

            $precio_unit = $plato['precio'];
            // Calcular extras
            $extras_precio = 0;
            if (!empty($item['opciones'])) {
                foreach ($item['opciones'] as $op_id) {
                    $op = DB::fetchOne("SELECT precio_extra FROM plato_opciones WHERE id = ?", [$op_id]);
                    if ($op) $extras_precio += $op['precio_extra'];
                }
            }
            $subtotal = ($precio_unit + $extras_precio) * $item['cantidad'];

            DB::query("INSERT INTO orden_detalle 
                (id_orden, id_plato, cantidad, precio_unitario, opciones_texto, observacion, prioridad, subtotal, estado)
                VALUES (?,?,?,?,?,?,?,?,'pendiente')",
                [
                    $id_orden,
                    $item['id_plato'],
                    $item['cantidad'],
                    $precio_unit + $extras_precio,
                    $item['opciones_texto'] ?? null,
                    $item['observacion'] ?? null,
                    $item['prioridad'] ?? 'normal',
                    $subtotal
                ]
            );
            $items_insertados++;
        }

        // Actualizar estado orden si estaba en espera
        if ($orden['estado'] === 'abierta') {
            DB::query("UPDATE ordenes SET estado='en_proceso' WHERE id=?", [$id_orden]);
        }

        // Crear notificación para cocina
        DB::query("INSERT INTO notificaciones (tipo, mensaje, id_referencia, para_rol) VALUES ('nuevo_pedido', ?, ?, 'cocina')",
            ["Nueva orden mesa #{$orden['id_mesa']}", $id_orden]);

        jsonResponse(['success' => true, 'items_agregados' => $items_insertados]);

    } elseif ($action === 'cancelar_item') {
        DB::query("UPDATE orden_detalle SET estado='cancelado' WHERE id=? AND estado='pendiente'", [$data['id_detalle']]);
        jsonResponse(['success' => true]);

    } elseif ($action === 'cambiar_mesa') {
        requireLogin(['administrador', 'mozo', 'supervisor']);
        // Verificar mesa destino libre
        $mesa_dest = DB::fetchOne("SELECT * FROM mesas WHERE id=? AND estado='libre'", [$data['id_mesa_destino']]);
        if (!$mesa_dest) {
            jsonResponse(['error' => 'Mesa destino no disponible'], 400);
        }
        $orden = DB::fetchOne("SELECT id_mesa FROM ordenes WHERE id=?", [$data['id_orden']]);
        // Liberar mesa origen
        DB::query("UPDATE mesas SET estado='por_limpiar' WHERE id=?", [$orden['id_mesa']]);
        // Ocupar mesa destino
        DB::query("UPDATE mesas SET estado='ocupada' WHERE id=?", [$data['id_mesa_destino']]);
        // Mover orden
        DB::query("UPDATE ordenes SET id_mesa=? WHERE id=?", [$data['id_mesa_destino'], $data['id_orden']]);
        jsonResponse(['success' => true]);

    } elseif ($action === 'pre_cuenta') {
        $orden = DB::fetchOne("SELECT o.*, m.numero as mesa_numero, u.nombre as mozo_nombre
            FROM ordenes o JOIN mesas m ON m.id=o.id_mesa JOIN usuarios u ON u.id=o.id_mozo
            WHERE o.id=?", [$data['id_orden']]);
        $items = DB::fetchAll("SELECT od.*, p.nombre as plato_nombre
            FROM orden_detalle od JOIN platos p ON p.id=od.id_plato
            WHERE od.id_orden=? AND od.estado!='cancelado'", [$data['id_orden']]);
        $subtotal = array_sum(array_column($items, 'subtotal'));
        $igv = $subtotal * IGV;
        $total = $subtotal + $igv;
        jsonResponse([
            'orden' => $orden,
            'items' => $items,
            'subtotal' => $subtotal,
            'igv' => $igv,
            'total' => $total
        ]);
    }

} else {
    jsonResponse(['error' => 'Método no permitido'], 405);
}
