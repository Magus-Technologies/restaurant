<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $fecha = $_GET['fecha'] ?? date('Y-m-d');

    $menu = DB::fetchAll("SELECT md.*, 
        p_entrada.nombre as entrada_nombre,
        p_fondo.nombre as fondo_nombre,
        p_bebida.nombre as bebida_nombre
        FROM menu_dia md
        LEFT JOIN platos p_entrada ON p_entrada.id = md.id_plato_entrada
        LEFT JOIN platos p_fondo ON p_fondo.id = md.id_plato_fondo
        LEFT JOIN platos p_bebida ON p_bebida.id = md.id_plato_bebida
        WHERE md.fecha = ?
        ORDER BY md.id", [$fecha]);
    
    jsonResponse($menu);

} elseif ($method === 'POST') {
    requireLogin(['administrador', 'supervisor']);
    $data = json_decode(file_get_contents('php://input'), true);
    $action = $data['action'] ?? 'save';

    if ($action === 'save') {
        // Upsert menú del día
        $existe = DB::fetchOne("SELECT id FROM menu_dia WHERE fecha=? AND nombre=?", [$data['fecha'], $data['nombre'] ?? 'Menu del Día']);
        if ($existe) {
            DB::query("UPDATE menu_dia SET id_plato_entrada=?, id_plato_fondo=?, id_plato_bebida=?, precio=?, cantidad_limite=?, activo=? WHERE id=?",
                [$data['id_plato_entrada'] ?? null, $data['id_plato_fondo'] ?? null, $data['id_plato_bebida'] ?? null,
                 $data['precio'] ?? 0, $data['cantidad_limite'] ?? null, $data['activo'] ?? 1, $existe['id']]);
            jsonResponse(['success' => true, 'id' => $existe['id']]);
        } else {
            DB::query("INSERT INTO menu_dia (fecha, nombre, id_plato_entrada, id_plato_fondo, id_plato_bebida, precio, cantidad_limite, activo) VALUES (?,?,?,?,?,?,?,?)",
                [$data['fecha'], $data['nombre'] ?? 'Menu del Día', $data['id_plato_entrada'] ?? null,
                 $data['id_plato_fondo'] ?? null, $data['id_plato_bebida'] ?? null,
                 $data['precio'] ?? 0, $data['cantidad_limite'] ?? null, $data['activo'] ?? 1]);
            jsonResponse(['success' => true, 'id' => DB::lastInsertId()]);
        }
    } elseif ($action === 'toggle') {
        DB::query("UPDATE menu_dia SET activo = NOT activo WHERE id=?", [$data['id']]);
        jsonResponse(['success' => true]);
    } elseif ($action === 'delete') {
        DB::query("DELETE FROM menu_dia WHERE id=?", [$data['id']]);
        jsonResponse(['success' => true]);
    }
} else {
    jsonResponse(['error' => 'Método no permitido'], 405);
}
