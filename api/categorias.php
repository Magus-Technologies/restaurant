<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $id = $_GET['id'] ?? null;
    if ($id) {
        $cat = DB::fetchOne("SELECT * FROM categorias WHERE id = ?", [$id]);
        jsonResponse($cat ?: ['error' => 'No encontrado'], $cat ? 200 : 404);
    } else {
        $area = $_GET['area'] ?? null;
        if ($area) {
            $cats = DB::fetchAll("SELECT * FROM categorias WHERE area = ? AND activo = 1 ORDER BY orden", [$area]);
        } else {
            $cats = DB::fetchAll("SELECT * FROM categorias WHERE activo = 1 ORDER BY orden");
        }
        jsonResponse($cats);
    }
} elseif ($method === 'POST') {
    requireLogin(['administrador', 'supervisor']);
    $data = json_decode(file_get_contents('php://input'), true);
    $action = $data['action'] ?? 'create';

    if ($action === 'create') {
        DB::query("INSERT INTO categorias (nombre, area, icono, color, orden) VALUES (?,?,?,?,?)",
            [$data['nombre'], $data['area'] ?? 'cocina', $data['icono'] ?? '🍽️', $data['color'] ?? '#ff6b35', $data['orden'] ?? 99]);
        jsonResponse(['success' => true, 'id' => DB::lastInsertId()]);
    } elseif ($action === 'update') {
        DB::query("UPDATE categorias SET nombre=?, area=?, icono=?, color=?, orden=? WHERE id=?",
            [$data['nombre'], $data['area'], $data['icono'], $data['color'], $data['orden'], $data['id']]);
        jsonResponse(['success' => true]);
    } elseif ($action === 'toggle') {
        DB::query("UPDATE categorias SET activo = NOT activo WHERE id=?", [$data['id']]);
        jsonResponse(['success' => true]);
    }
} else {
    jsonResponse(['error' => 'Método no permitido'], 405);
}
