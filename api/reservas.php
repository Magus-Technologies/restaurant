<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $id = $_GET['id'] ?? null;
    $fecha = $_GET['fecha'] ?? date('Y-m-d');

    if ($id) {
        $res = DB::fetchOne("SELECT r.*, m.numero as mesa_numero FROM reservaciones r LEFT JOIN mesas m ON m.id=r.id_mesa WHERE r.id=?", [$id]);
        jsonResponse($res ?: ['error' => 'No encontrada'], $res ? 200 : 404);
    } else {
        $estado = $_GET['estado'] ?? null;
        $params = [$fecha];
        $where_estado = '';
        if ($estado) {
            $where_estado = " AND r.estado=?";
            $params[] = $estado;
        }
        $reservas = DB::fetchAll("SELECT r.*, m.numero as mesa_numero
            FROM reservaciones r LEFT JOIN mesas m ON m.id=r.id_mesa
            WHERE DATE(r.fecha_hora) = ? $where_estado
            ORDER BY r.fecha_hora", $params);
        jsonResponse($reservas);
    }

} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $action = $data['action'] ?? 'create';

    if ($action === 'create') {
        requireLogin(['administrador', 'cajero', 'supervisor', 'mozo']);
        // Verificar disponibilidad
        $conflicto = DB::fetchOne("SELECT id FROM reservaciones 
            WHERE id_mesa=? AND estado IN ('pendiente','confirmada')
            AND ABS(TIMESTAMPDIFF(MINUTE, fecha_hora, ?)) < 90",
            [$data['id_mesa'] ?? null, $data['fecha_hora']]);
        
        DB::query("INSERT INTO reservaciones (nombre_cliente, telefono, email, fecha_hora, personas, id_mesa, observaciones, estado) VALUES (?,?,?,?,?,?,?,'pendiente')",
            [$data['nombre_cliente'], $data['telefono'] ?? null, $data['email'] ?? null,
             $data['fecha_hora'], $data['personas'] ?? 2, $data['id_mesa'] ?? null, $data['observaciones'] ?? null]);
        jsonResponse(['success' => true, 'id' => DB::lastInsertId(), 'conflicto' => (bool)$conflicto]);

    } elseif ($action === 'update_estado') {
        requireLogin(['administrador', 'cajero', 'supervisor', 'mozo']);
        DB::query("UPDATE reservaciones SET estado=?, updated_at=NOW() WHERE id=?", [$data['estado'], $data['id']]);
        // Si se confirma, marcar mesa como reservada
        if ($data['estado'] === 'confirmada') {
            $res = DB::fetchOne("SELECT id_mesa FROM reservaciones WHERE id=?", [$data['id']]);
            if ($res['id_mesa']) DB::query("UPDATE mesas SET estado='reservada' WHERE id=?", [$res['id_mesa']]);
        }
        jsonResponse(['success' => true]);

    } elseif ($action === 'update') {
        requireLogin(['administrador', 'cajero', 'supervisor']);
        DB::query("UPDATE reservaciones SET nombre_cliente=?, telefono=?, email=?, fecha_hora=?, personas=?, id_mesa=?, observaciones=? WHERE id=?",
            [$data['nombre_cliente'], $data['telefono'], $data['email'], $data['fecha_hora'], $data['personas'], $data['id_mesa'] ?? null, $data['observaciones'], $data['id']]);
        jsonResponse(['success' => true]);
    }
} else {
    jsonResponse(['error' => 'Método no permitido'], 405);
}
