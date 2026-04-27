<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin(['administrador', 'cajero', 'mozo', 'supervisor']);

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $id = $_GET['id'] ?? null;
    $estado = $_GET['estado'] ?? null;

    if ($id) {
        $del = DB::fetchOne("SELECT d.*, o.numero as orden_numero, u.nombre as mozo_nombre
            FROM delivery d JOIN ordenes o ON o.id=d.id_orden LEFT JOIN usuarios u ON u.id=d.id_repartidor
            WHERE d.id=?", [$id]);
        if ($del) {
            $del['items'] = DB::fetchAll("SELECT od.*, p.nombre as plato_nombre
                FROM orden_detalle od JOIN platos p ON p.id=od.id_plato
                WHERE od.id_orden=? AND od.estado!='cancelado'", [$del['id_orden']]);
        }
        jsonResponse($del ?: ['error' => 'No encontrado'], $del ? 200 : 404);
    } else {
        $where = '';
        $params = [];
        if ($estado) {
            $where = " WHERE d.estado=?";
            $params[] = $estado;
        } else {
            $where = " WHERE d.estado NOT IN ('entregado','cancelado') OR DATE(d.created_at)=CURDATE()";
        }
        $deliveries = DB::fetchAll("SELECT d.*, o.numero as orden_numero,
            u.nombre as repartidor_nombre, cl.nombre as cliente_nombre,
            COUNT(od.id) as total_items, SUM(od.subtotal) as subtotal
            FROM delivery d
            JOIN ordenes o ON o.id=d.id_orden
            LEFT JOIN usuarios u ON u.id=d.id_repartidor
            LEFT JOIN clientes cl ON cl.id=d.id_cliente
            LEFT JOIN orden_detalle od ON od.id_orden=o.id AND od.estado!='cancelado'
            $where GROUP BY d.id ORDER BY d.created_at DESC", $params);
        jsonResponse($deliveries);
    }

} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $action = $data['action'] ?? '';
    $user = currentUser();

    if ($action === 'create') {
        // Crear orden de delivery
        $numero = generateNumero('DEL');
        
        // Crear o encontrar cliente
        $id_cliente = null;
        if (!empty($data['telefono'])) {
            $cliente = DB::fetchOne("SELECT id FROM clientes WHERE telefono=?", [$data['telefono']]);
            if (!$cliente) {
                DB::query("INSERT INTO clientes (nombre, telefono, email) VALUES (?,?,?)",
                    [$data['nombre_cliente'], $data['telefono'], $data['email'] ?? null]);
                $id_cliente = DB::lastInsertId();
            } else {
                $id_cliente = $cliente['id'];
                DB::query("UPDATE clientes SET nombre=? WHERE id=?", [$data['nombre_cliente'], $id_cliente]);
            }
        }

        // Crear orden interna
        $orden_numero = generateNumero('ORD');
        DB::query("INSERT INTO ordenes (numero, id_mesa, id_mozo, personas, estado, tipo) VALUES (?,NULL,?,1,'en_proceso','delivery')",
            [$orden_numero, $user['id']]);
        $id_orden = DB::lastInsertId();

        // Insertar items
        foreach ($data['items'] as $item) {
            $plato = DB::fetchOne("SELECT precio FROM platos WHERE id=?", [$item['id_plato']]);
            if (!$plato) continue;
            DB::query("INSERT INTO orden_detalle (id_orden, id_plato, cantidad, precio_unitario, observacion, subtotal, estado) VALUES (?,?,?,?,?,?,'pendiente')",
                [$id_orden, $item['id_plato'], $item['cantidad'], $plato['precio'], $item['observacion'] ?? null, $plato['precio'] * $item['cantidad']]);
        }

        // Crear delivery
        DB::query("INSERT INTO delivery (numero, id_orden, id_cliente, nombre_cliente, telefono, direccion, referencia, metodo_pago, tiempo_estimado, estado, notas) VALUES (?,?,?,?,?,?,?,?,?,?,?)",
            [$numero, $id_orden, $id_cliente, $data['nombre_cliente'], $data['telefono'],
             $data['direccion'], $data['referencia'] ?? null, $data['metodo_pago'] ?? 'efectivo',
             $data['tiempo_estimado'] ?? 30, 'recibido', $data['notas'] ?? null]);

        jsonResponse(['success' => true, 'id' => DB::lastInsertId(), 'numero' => $numero, 'id_orden' => $id_orden]);

    } elseif ($action === 'update_estado') {
        $estados_validos = ['recibido', 'en_cocina', 'listo', 'en_camino', 'entregado', 'cancelado'];
        if (!in_array($data['estado'], $estados_validos)) jsonResponse(['error' => 'Estado inválido'], 400);
        
        DB::query("UPDATE delivery SET estado=?, updated_at=NOW() WHERE id=?", [$data['estado'], $data['id']]);
        
        // Si se asigna repartidor
        if (!empty($data['id_repartidor'])) {
            DB::query("UPDATE delivery SET id_repartidor=? WHERE id=?", [$data['id_repartidor'], $data['id']]);
        }
        jsonResponse(['success' => true]);
    }
} else {
    jsonResponse(['error' => 'Método no permitido'], 405);
}
