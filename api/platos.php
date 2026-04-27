<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $id = $_GET['id'] ?? null;
    $categoria = $_GET['categoria'] ?? null;
    $search = $_GET['search'] ?? null;

    if ($id) {
        $plato = DB::fetchOne("SELECT p.*, c.nombre as categoria_nombre, c.area
            FROM platos p LEFT JOIN categorias c ON c.id = p.id_categoria
            WHERE p.id = ?", [$id]);
        if ($plato) {
            // Opciones del plato
            $plato['opciones'] = DB::fetchAll("SELECT * FROM plato_opciones WHERE id_plato = ? ORDER BY tipo, orden", [$id]);
            // Receta
            $plato['receta'] = DB::fetchAll("SELECT r.*, i.nombre as insumo_nombre, i.unidad 
                FROM recetas r JOIN insumos i ON i.id = r.id_insumo 
                WHERE r.id_plato = ?", [$id]);
        }
        jsonResponse($plato ?: ['error' => 'No encontrado'], $plato ? 200 : 404);
    } elseif ($categoria) {
        $platos = DB::fetchAll("SELECT p.*, c.nombre as categoria_nombre 
            FROM platos p LEFT JOIN categorias c ON c.id = p.id_categoria
            WHERE p.id_categoria = ? AND p.disponible = 1
            ORDER BY p.nombre", [$categoria]);
        // Agregar opciones a cada plato
        foreach ($platos as &$plato) {
            $plato['opciones'] = DB::fetchAll("SELECT * FROM plato_opciones WHERE id_plato = ? ORDER BY tipo, orden", [$plato['id']]);
        }
        jsonResponse($platos);
    } elseif ($search) {
        $like = "%$search%";
        $platos = DB::fetchAll("SELECT p.*, c.nombre as categoria_nombre 
            FROM platos p LEFT JOIN categorias c ON c.id = p.id_categoria
            WHERE (p.nombre LIKE ? OR p.descripcion LIKE ?) AND p.disponible = 1
            ORDER BY p.nombre", [$like, $like]);
        jsonResponse($platos);
    } else {
        $platos = DB::fetchAll("SELECT p.*, c.nombre as categoria_nombre, c.area
            FROM platos p LEFT JOIN categorias c ON c.id = p.id_categoria
            ORDER BY c.orden, p.nombre");
        jsonResponse($platos);
    }

} elseif ($method === 'POST') {
    requireLogin(['administrador', 'supervisor']);
    $data = json_decode(file_get_contents('php://input'), true);
    $action = $data['action'] ?? 'create';

    if ($action === 'create') {
        DB::query("INSERT INTO platos (nombre, descripcion, precio, id_categoria, imagen, disponible, tiempo_prep) VALUES (?,?,?,?,?,1,?)",
            [$data['nombre'], $data['descripcion'] ?? '', $data['precio'], $data['id_categoria'], $data['imagen'] ?? null, $data['tiempo_prep'] ?? 15]);
        $id = DB::lastInsertId();
        
        // Guardar opciones si vienen
        if (!empty($data['opciones'])) {
            foreach ($data['opciones'] as $op) {
                DB::query("INSERT INTO plato_opciones (id_plato, tipo, nombre, precio_extra, orden) VALUES (?,?,?,?,?)",
                    [$id, $op['tipo'], $op['nombre'], $op['precio_extra'] ?? 0, $op['orden'] ?? 0]);
            }
        }
        jsonResponse(['success' => true, 'id' => $id]);

    } elseif ($action === 'update') {
        DB::query("UPDATE platos SET nombre=?, descripcion=?, precio=?, id_categoria=?, tiempo_prep=? WHERE id=?",
            [$data['nombre'], $data['descripcion'], $data['precio'], $data['id_categoria'], $data['tiempo_prep'] ?? 15, $data['id']]);
        jsonResponse(['success' => true]);

    } elseif ($action === 'toggle_disponible') {
        DB::query("UPDATE platos SET disponible = NOT disponible WHERE id=?", [$data['id']]);
        $plato = DB::fetchOne("SELECT disponible FROM platos WHERE id=?", [$data['id']]);
        jsonResponse(['success' => true, 'disponible' => (bool)$plato['disponible']]);

    } elseif ($action === 'save_receta') {
        // Guardar ficha técnica
        DB::query("DELETE FROM recetas WHERE id_plato = ?", [$data['id_plato']]);
        foreach ($data['ingredientes'] as $ing) {
            DB::query("INSERT INTO recetas (id_plato, id_insumo, cantidad) VALUES (?,?,?)",
                [$data['id_plato'], $ing['id_insumo'], $ing['cantidad']]);
        }
        jsonResponse(['success' => true]);
    }

} else {
    jsonResponse(['error' => 'Método no permitido'], 405);
}
