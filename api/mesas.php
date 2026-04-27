<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin(['administrador', 'mozo', 'cajero', 'supervisor']);

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $id = $_GET['id'] ?? null;
        $estado = $_GET['estado'] ?? null;

        if ($id) {
            $mesa = DB::fetchOne("SELECT m.*, 
                o.id as orden_id, o.estado as orden_estado,
                (SELECT SUM(od.subtotal) FROM orden_detalle od WHERE od.id_orden = o.id AND od.estado != 'cancelado') as total_consumo
                FROM mesas m
                LEFT JOIN ordenes o ON o.id_mesa = m.id AND o.estado NOT IN ('pagada','cancelada')
                WHERE m.id = ?", [$id]);
            jsonResponse($mesa ?: ['error' => 'Mesa no encontrada'], $mesa ? 200 : 404);
        } elseif ($estado) {
            $mesas = DB::fetchAll("SELECT * FROM mesas WHERE estado = ? ORDER BY numero", [$estado]);
            jsonResponse($mesas);
        } else {
            $mesas = DB::fetchAll("SELECT m.*,
                o.id as orden_id,
                (SELECT COUNT(*) FROM orden_detalle od WHERE od.id_orden = o.id AND od.estado = 'pendiente') as items_pendientes,
                (SELECT SUM(od.subtotal) FROM orden_detalle od WHERE od.id_orden = o.id AND od.estado != 'cancelado') as total_consumo
                FROM mesas m
                LEFT JOIN ordenes o ON o.id_mesa = m.id AND o.estado NOT IN ('pagada','cancelada')
                ORDER BY m.zona, m.numero");
            jsonResponse($mesas);
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $action = $data['action'] ?? 'update';

        if ($action === 'create') {
            requireLogin(['administrador', 'supervisor']);
            $stmt = DB::query(
                "INSERT INTO mesas (numero, zona, capacidad, estado) VALUES (?, ?, ?, 'libre')",
                [$data['numero'], $data['zona'] ?? 'salon', $data['capacidad'] ?? 4]
            );
            jsonResponse(['success' => true, 'id' => DB::lastInsertId()]);

        } elseif ($action === 'update') {
            requireLogin(['administrador', 'supervisor']);
            DB::query("UPDATE mesas SET numero=?, zona=?, capacidad=? WHERE id=?",
                [$data['numero'], $data['zona'], $data['capacidad'], $data['id']]);
            jsonResponse(['success' => true]);

        } elseif ($action === 'delete') {
            requireLogin(['administrador', 'supervisor']);
            DB::query("UPDATE mesas SET estado='cerrada' WHERE id=?", [$data['id']]);
            jsonResponse(['success' => true]);

        } elseif ($action === 'abrir') {
            // Abrir mesa y crear orden
            $user = currentUser();
            DB::query("UPDATE mesas SET estado='ocupada', personas=?, cliente_nombre=? WHERE id=?",
                [$data['personas'] ?? 1, $data['cliente_nombre'] ?? null, $data['id_mesa']]);
            
            // Crear orden nueva
            $numero = generateNumero('ORD');
            DB::query("INSERT INTO ordenes (numero, id_mesa, id_mozo, personas, estado) VALUES (?,?,?,?,'abierta')",
                [$numero, $data['id_mesa'], $user['id'], $data['personas'] ?? 1]);
            $orden_id = DB::lastInsertId();
            jsonResponse(['success' => true, 'orden_id' => $orden_id, 'numero' => $numero]);

        } elseif ($action === 'liberar') {
            DB::query("UPDATE mesas SET estado='por_limpiar', personas=NULL, cliente_nombre=NULL WHERE id=?",
                [$data['id_mesa']]);
            jsonResponse(['success' => true]);

        } elseif ($action === 'limpiar') {
            DB::query("UPDATE mesas SET estado='libre' WHERE id=?", [$data['id_mesa']]);
            jsonResponse(['success' => true]);
        }
        break;

    default:
        jsonResponse(['error' => 'Método no permitido'], 405);
}
