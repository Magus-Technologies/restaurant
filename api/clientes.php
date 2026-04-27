<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin(['administrador', 'cajero', 'supervisor', 'mozo']);

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $id = $_GET['id'] ?? null;
    $search = $_GET['search'] ?? null;

    if ($id) {
        $cliente = DB::fetchOne("SELECT * FROM clientes WHERE id=?", [$id]);
        if ($cliente) {
            // Historial de visitas
            $cliente['historial'] = DB::fetchAll("SELECT o.numero, o.created_at, p.total, m.numero as mesa
                FROM ordenes o LEFT JOIN pagos p ON p.id_orden=o.id LEFT JOIN mesas m ON m.id=o.id_mesa
                WHERE o.id IN (SELECT id_orden FROM pagos WHERE id_cliente=? OR EXISTS(SELECT 1 FROM delivery d WHERE d.id_cliente=? AND d.id_orden=o.id))
                ORDER BY o.created_at DESC LIMIT 20", [$id, $id]);
            // Platos favoritos
            $cliente['platos_favoritos'] = DB::fetchAll("SELECT p.nombre, SUM(od.cantidad) as veces
                FROM orden_detalle od JOIN platos p ON p.id=od.id_plato
                JOIN ordenes o ON o.id=od.id_orden
                JOIN delivery d ON d.id_orden=o.id AND d.id_cliente=?
                GROUP BY p.id ORDER BY veces DESC LIMIT 5", [$id]);
        }
        jsonResponse($cliente ?: ['error' => 'No encontrado'], $cliente ? 200 : 404);
    } elseif ($search) {
        $like = "%$search%";
        $clientes = DB::fetchAll("SELECT * FROM clientes WHERE nombre LIKE ? OR telefono LIKE ? OR email LIKE ? ORDER BY nombre LIMIT 20",
            [$like, $like, $like]);
        jsonResponse($clientes);
    } else {
        $clientes = DB::fetchAll("SELECT c.*,
            COUNT(DISTINCT d.id) as total_pedidos,
            COALESCE(SUM(p_totales.total_gastado), 0) as total_gastado
            FROM clientes c
            LEFT JOIN delivery d ON d.id_cliente=c.id
            LEFT JOIN (SELECT d2.id_cliente, SUM(p.total) as total_gastado FROM delivery d2 JOIN pagos p ON p.id_orden=d2.id_orden GROUP BY d2.id_cliente) p_totales ON p_totales.id_cliente=c.id
            GROUP BY c.id ORDER BY total_gastado DESC");
        jsonResponse($clientes);
    }

} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $action = $data['action'] ?? 'create';

    if ($action === 'create') {
        $existe = DB::fetchOne("SELECT id FROM clientes WHERE telefono=?", [$data['telefono'] ?? '']);
        if ($existe) jsonResponse(['error' => 'Ya existe cliente con ese teléfono', 'id' => $existe['id']], 409);

        DB::query("INSERT INTO clientes (nombre, telefono, email, cumpleanos, direccion, notas) VALUES (?,?,?,?,?,?)",
            [$data['nombre'], $data['telefono'] ?? null, $data['email'] ?? null,
             $data['cumpleanos'] ?? null, $data['direccion'] ?? null, $data['notas'] ?? null]);
        jsonResponse(['success' => true, 'id' => DB::lastInsertId()]);

    } elseif ($action === 'update') {
        DB::query("UPDATE clientes SET nombre=?, telefono=?, email=?, cumpleanos=?, direccion=?, notas=? WHERE id=?",
            [$data['nombre'], $data['telefono'], $data['email'], $data['cumpleanos'] ?? null, $data['direccion'] ?? null, $data['notas'] ?? null, $data['id']]);
        jsonResponse(['success' => true]);
    }
} else {
    jsonResponse(['error' => 'Método no permitido'], 405);
}
