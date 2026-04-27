<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin(['administrador', 'almacen', 'compras', 'supervisor']);

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $id = $_GET['id'] ?? null;
    if ($id) {
        $prov = DB::fetchOne("SELECT * FROM proveedores WHERE id=?", [$id]);
        if ($prov) {
            $prov['compras_recientes'] = DB::fetchAll("SELECT c.*, SUM(cd.subtotal) as total
                FROM compras c LEFT JOIN compra_detalle cd ON cd.id_compra=c.id
                WHERE c.id_proveedor=? GROUP BY c.id ORDER BY c.fecha DESC LIMIT 10", [$id]);
        }
        jsonResponse($prov ?: ['error' => 'No encontrado'], $prov ? 200 : 404);
    } else {
        $categoria = $_GET['categoria'] ?? null;
        if ($categoria) {
            $provs = DB::fetchAll("SELECT * FROM proveedores WHERE categoria=? AND activo=1 ORDER BY nombre", [$categoria]);
        } else {
            $provs = DB::fetchAll("SELECT p.*, COUNT(c.id) as total_compras FROM proveedores p LEFT JOIN compras c ON c.id_proveedor=p.id WHERE p.activo=1 GROUP BY p.id ORDER BY p.nombre");
        }
        jsonResponse($provs);
    }

} elseif ($method === 'POST') {
    requireLogin(['administrador', 'compras', 'supervisor']);
    $data = json_decode(file_get_contents('php://input'), true);
    $action = $data['action'] ?? 'create';

    if ($action === 'create') {
        DB::query("INSERT INTO proveedores (nombre, ruc, contacto, telefono, email, direccion, categoria, condicion_pago) VALUES (?,?,?,?,?,?,?,?)",
            [$data['nombre'], $data['ruc'] ?? null, $data['contacto'] ?? null, $data['telefono'] ?? null,
             $data['email'] ?? null, $data['direccion'] ?? null, $data['categoria'] ?? 'general', $data['condicion_pago'] ?? 'contado']);
        jsonResponse(['success' => true, 'id' => DB::lastInsertId()]);
    } elseif ($action === 'update') {
        DB::query("UPDATE proveedores SET nombre=?, ruc=?, contacto=?, telefono=?, email=?, direccion=?, categoria=?, condicion_pago=? WHERE id=?",
            [$data['nombre'], $data['ruc'], $data['contacto'], $data['telefono'], $data['email'], $data['direccion'], $data['categoria'], $data['condicion_pago'], $data['id']]);
        jsonResponse(['success' => true]);
    } elseif ($action === 'toggle') {
        DB::query("UPDATE proveedores SET activo = NOT activo WHERE id=?", [$data['id']]);
        jsonResponse(['success' => true]);
    }
} else {
    jsonResponse(['error' => 'Método no permitido'], 405);
}
