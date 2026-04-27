<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin(['administrador', 'supervisor']);

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $id = $_GET['id'] ?? null;
    if ($id) {
        $u = DB::fetchOne("SELECT id, nombre, usuario, rol, email, telefono, activo, created_at FROM usuarios WHERE id=?", [$id]);
        jsonResponse($u ?: ['error' => 'No encontrado'], $u ? 200 : 404);
    } else {
        $rol = $_GET['rol'] ?? null;
        if ($rol) {
            $users = DB::fetchAll("SELECT id, nombre, usuario, rol, activo FROM usuarios WHERE rol=? ORDER BY nombre", [$rol]);
        } else {
            $users = DB::fetchAll("SELECT id, nombre, usuario, rol, email, activo, created_at FROM usuarios ORDER BY rol, nombre");
        }
        jsonResponse($users);
    }

} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $action = $data['action'] ?? 'create';

    if ($action === 'create') {
        // Verificar usuario único
        $existe = DB::fetchOne("SELECT id FROM usuarios WHERE usuario=?", [$data['usuario']]);
        if ($existe) jsonResponse(['error' => 'El nombre de usuario ya existe'], 400);

        $hash = password_hash($data['password'], PASSWORD_DEFAULT);
        DB::query("INSERT INTO usuarios (nombre, usuario, password, rol, email, telefono) VALUES (?,?,?,?,?,?)",
            [$data['nombre'], $data['usuario'], $hash, $data['rol'], $data['email'] ?? null, $data['telefono'] ?? null]);
        jsonResponse(['success' => true, 'id' => DB::lastInsertId()]);

    } elseif ($action === 'update') {
        if (!empty($data['password'])) {
            $hash = password_hash($data['password'], PASSWORD_DEFAULT);
            DB::query("UPDATE usuarios SET nombre=?, rol=?, email=?, telefono=?, password=? WHERE id=?",
                [$data['nombre'], $data['rol'], $data['email'], $data['telefono'], $hash, $data['id']]);
        } else {
            DB::query("UPDATE usuarios SET nombre=?, rol=?, email=?, telefono=? WHERE id=?",
                [$data['nombre'], $data['rol'], $data['email'], $data['telefono'], $data['id']]);
        }
        jsonResponse(['success' => true]);

    } elseif ($action === 'toggle') {
        $user = currentUser();
        if ($user['id'] == $data['id']) jsonResponse(['error' => 'No puedes desactivar tu propio usuario'], 400);
        DB::query("UPDATE usuarios SET activo = NOT activo WHERE id=?", [$data['id']]);
        jsonResponse(['success' => true]);
    }
} else {
    jsonResponse(['error' => 'Método no permitido'], 405);
}
