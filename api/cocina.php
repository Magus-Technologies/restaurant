<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin(['administrador', 'cocina', 'bar', 'supervisor']);

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $area = $_GET['area'] ?? null;
    $desde = $_GET['desde'] ?? null; // Para polling incremental

    // Mapear área a categorías
    $area_filter = '';
    $params = [];
    if ($area && $area !== 'todas') {
        $area_filter = ' AND c.area = ?';
        $params[] = $area;
    }

    // Solo estados activos (no entregado)
    $sql = "SELECT o.id as orden_id, o.numero as orden_numero,
            m.numero as mesa_numero, m.zona,
            u.nombre as mozo_nombre,
            od.id as item_id, od.cantidad, od.precio_unitario, od.opciones_texto, od.observacion, od.prioridad,
            od.estado as item_estado, od.created_at as item_hora,
            TIMESTAMPDIFF(MINUTE, od.created_at, NOW()) as minutos_espera,
            p.nombre as plato_nombre, p.tiempo_prep,
            c.nombre as categoria_nombre, c.area
            FROM orden_detalle od
            JOIN ordenes o ON o.id = od.id_orden
            JOIN mesas m ON m.id = o.id_mesa
            JOIN usuarios u ON u.id = o.id_mozo
            JOIN platos p ON p.id = od.id_plato
            JOIN categorias c ON c.id = p.id_categoria
            WHERE od.estado IN ('pendiente','preparando','listo')
            AND o.estado NOT IN ('pagada','cancelada')
            $area_filter
            ORDER BY od.prioridad DESC, od.created_at ASC";

    $items = DB::fetchAll($sql, $params);

    // Agrupar por orden
    $ordenes = [];
    foreach ($items as $item) {
        $oid = $item['orden_id'];
        if (!isset($ordenes[$oid])) {
            $ordenes[$oid] = [
                'orden_id'      => $item['orden_id'],
                'orden_numero'  => $item['orden_numero'],
                'mesa_numero'   => $item['mesa_numero'],
                'zona'          => $item['zona'],
                'mozo_nombre'   => $item['mozo_nombre'],
                'items'         => [],
                'max_espera'    => 0
            ];
        }
        $ordenes[$oid]['items'][] = [
            'id'             => $item['item_id'],
            'cantidad'       => $item['cantidad'],
            'plato_nombre'   => $item['plato_nombre'],
            'categoria'      => $item['categoria_nombre'],
            'area'           => $item['area'],
            'opciones_texto' => $item['opciones_texto'],
            'observacion'    => $item['observacion'],
            'prioridad'      => $item['prioridad'],
            'estado'         => $item['item_estado'],
            'hora'           => $item['item_hora'],
            'minutos_espera' => (int)$item['minutos_espera'],
            'tiempo_prep'    => $item['tiempo_prep'],
            'alerta'         => alertColor((int)$item['minutos_espera'])
        ];
        if ($item['minutos_espera'] > $ordenes[$oid]['max_espera']) {
            $ordenes[$oid]['max_espera'] = (int)$item['minutos_espera'];
        }
    }

    // Stats
    $stats = DB::fetchOne("SELECT
        COUNT(CASE WHEN od.estado='pendiente' THEN 1 END) as pendientes,
        COUNT(CASE WHEN od.estado='preparando' THEN 1 END) as preparando,
        COUNT(CASE WHEN od.estado='listo' THEN 1 END) as listos,
        COUNT(CASE WHEN od.estado='entregado' AND DATE(od.updated_at)=CURDATE() THEN 1 END) as entregados_hoy
        FROM orden_detalle od
        JOIN ordenes o ON o.id = od.id_orden
        WHERE o.estado NOT IN ('pagada','cancelada')");

    jsonResponse([
        'ordenes' => array_values($ordenes),
        'stats'   => $stats,
        'timestamp' => time()
    ]);

} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $action = $data['action'] ?? '';

    if ($action === 'cambiar_estado') {
        // Cambiar estado de toda la orden (todos los items del área)
        $id_orden = $data['id_orden'];
        $nuevo_estado = $data['estado'];
        $area = $data['area'] ?? null;

        $estados_validos = ['preparando', 'listo', 'entregado'];
        if (!in_array($nuevo_estado, $estados_validos)) {
            jsonResponse(['error' => 'Estado inválido'], 400);
        }

        if ($area && $area !== 'todas') {
            DB::query("UPDATE orden_detalle od
                JOIN platos p ON p.id = od.id_plato
                JOIN categorias c ON c.id = p.id_categoria
                SET od.estado = ?, od.updated_at = NOW()
                WHERE od.id_orden = ? AND c.area = ? AND od.estado != 'cancelado'",
                [$nuevo_estado, $id_orden, $area]);
        } else {
            DB::query("UPDATE orden_detalle SET estado=?, updated_at=NOW() 
                WHERE id_orden=? AND estado != 'cancelado'",
                [$nuevo_estado, $id_orden]);
        }

        // Si todo está listo, notificar al mozo
        if ($nuevo_estado === 'listo') {
            $orden = DB::fetchOne("SELECT o.id_mozo, m.numero as mesa FROM ordenes o JOIN mesas m ON m.id=o.id_mesa WHERE o.id=?", [$id_orden]);
            DB::query("INSERT INTO notificaciones (tipo, mensaje, id_referencia, para_rol, id_usuario) VALUES ('pedido_listo',?,?,'mozo',?)",
                ["Mesa {$orden['mesa']} lista para servir", $id_orden, $orden['id_mozo']]);
        }

        jsonResponse(['success' => true]);

    } elseif ($action === 'cambiar_estado_item') {
        $item_id = $data['id_item'];
        $nuevo_estado = $data['estado'];

        $estados_validos = ['preparando', 'listo', 'entregado'];
        if (!in_array($nuevo_estado, $estados_validos)) {
            jsonResponse(['error' => 'Estado inválido'], 400);
        }

        DB::query("UPDATE orden_detalle SET estado=?, updated_at=NOW() WHERE id=?",
            [$nuevo_estado, $item_id]);

        // Verificar si toda la orden está lista
        if ($nuevo_estado === 'listo') {
            $item = DB::fetchOne("SELECT id_orden FROM orden_detalle WHERE id=?", [$item_id]);
            $pendientes = DB::fetchOne("SELECT COUNT(*) as cnt FROM orden_detalle 
                WHERE id_orden=? AND estado IN ('pendiente','preparando')", [$item['id_orden']]);
            
            if ($pendientes['cnt'] == 0) {
                $orden = DB::fetchOne("SELECT o.id_mozo, m.numero as mesa FROM ordenes o JOIN mesas m ON m.id=o.id_mesa WHERE o.id=?", [$item['id_orden']]);
                DB::query("INSERT INTO notificaciones (tipo, mensaje, id_referencia, para_rol, id_usuario) VALUES ('pedido_listo',?,?,'mozo',?)",
                    ["Mesa {$orden['mesa']} lista para servir", $item['id_orden'], $orden['id_mozo']]);
            }
        }

        jsonResponse(['success' => true]);
    }

} else {
    jsonResponse(['error' => 'Método no permitido'], 405);
}
