<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin();

$method = $_SERVER['REQUEST_METHOD'];
$user = currentUser();

if ($method === 'GET') {
    $desde = $_GET['desde'] ?? 0; // timestamp del último poll

    $notifs = DB::fetchAll("SELECT * FROM notificaciones
        WHERE (id_usuario = ? OR para_rol = ?)
        AND leido = 0
        AND UNIX_TIMESTAMP(created_at) > ?
        ORDER BY created_at DESC
        LIMIT 20",
        [$user['id'], $user['rol'], $desde]);

    // Marcar como leídas las que el usuario ve
    if (!empty($notifs)) {
        $ids = array_column($notifs, 'id');
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        DB::query("UPDATE notificaciones SET leido=1 WHERE id IN ($placeholders)", $ids);
    }

    jsonResponse([
        'notificaciones' => $notifs,
        'timestamp' => time(),
        'total' => count($notifs)
    ]);

} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    DB::query("INSERT INTO notificaciones (tipo, mensaje, id_referencia, para_rol, id_usuario) VALUES (?,?,?,?,?)",
        [$data['tipo'], $data['mensaje'], $data['id_referencia'] ?? null, $data['para_rol'] ?? null, $data['id_usuario'] ?? null]);
    jsonResponse(['success' => true]);
}
