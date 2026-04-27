<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin(['administrador', 'almacen', 'supervisor', 'compras']);

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $id = $_GET['id'] ?? null;
    $action = $_GET['action'] ?? null;
    $alerta = $_GET['alerta'] ?? null;

    if ($action === 'kardex_all') {
        $kardex = DB::fetchAll("SELECT k.*, i.nombre as insumo_nombre, u.nombre as usuario_nombre
            FROM kardex k 
            JOIN insumos i ON i.id = k.id_insumo
            LEFT JOIN usuarios u ON u.id = k.id_usuario
            ORDER BY k.created_at DESC LIMIT 200");
        jsonResponse($kardex);
    } elseif ($action === 'kardex' && $id) {
        $kardex = DB::fetchAll("SELECT k.*, u.nombre as usuario_nombre
            FROM kardex k LEFT JOIN usuarios u ON u.id = k.id_usuario
            WHERE k.id_insumo = ?
            ORDER BY k.created_at DESC
            LIMIT 100", [$id]);
        $insumo = DB::fetchOne("SELECT * FROM insumos WHERE id=?", [$id]);
        jsonResponse(['insumo' => $insumo, 'kardex' => $kardex]);

    } elseif ($id) {
        $insumo = DB::fetchOne("SELECT i.*, p.nombre as proveedor_nombre
            FROM insumos i LEFT JOIN proveedores p ON p.id = i.id_proveedor
            WHERE i.id = ?", [$id]);
        jsonResponse($insumo ?: ['error' => 'No encontrado'], $insumo ? 200 : 404);

    } elseif ($alerta === 'stock_bajo') {
        $insumos = DB::fetchAll("SELECT * FROM insumos WHERE stock_actual <= stock_minimo AND activo=1 ORDER BY (stock_actual/stock_minimo) ASC");
        jsonResponse($insumos);

    } else {
        $categoria = $_GET['categoria'] ?? null;
        if ($categoria) {
            $insumos = DB::fetchAll("SELECT i.*, p.nombre as proveedor_nombre
                FROM insumos i LEFT JOIN proveedores p ON p.id=i.id_proveedor
                WHERE i.categoria = ? AND i.activo=1 ORDER BY i.nombre", [$categoria]);
        } else {
            $insumos = DB::fetchAll("SELECT i.*, p.nombre as proveedor_nombre,
                CASE WHEN i.stock_actual <= i.stock_minimo THEN 1 ELSE 0 END as alerta_stock
                FROM insumos i LEFT JOIN proveedores p ON p.id=i.id_proveedor
                WHERE i.activo=1
                ORDER BY alerta_stock DESC, i.nombre");
        }
        jsonResponse($insumos);
    }

} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $action = $data['action'] ?? '';
    $user = currentUser();

    if ($action === 'create') {
        DB::query("INSERT INTO insumos (nombre, unidad, stock_actual, stock_minimo, costo_unitario, categoria, id_proveedor) VALUES (?,?,?,?,?,?,?)",
            [$data['nombre'], $data['unidad'], $data['stock_inicial'] ?? 0, $data['stock_minimo'] ?? 0,
             $data['costo_unitario'] ?? 0, $data['categoria'] ?? 'general', $data['id_proveedor'] ?? null]);
        $id = DB::lastInsertId();
        // Registro kardex inicial
        if (($data['stock_inicial'] ?? 0) > 0) {
            DB::query("INSERT INTO kardex (id_insumo, tipo, cantidad, stock_resultante, motivo, id_usuario) VALUES (?,?,?,?,'Inventario inicial',?)",
                [$id, 'entrada', $data['stock_inicial'], $data['stock_inicial'], $user['id']]);
        }
        jsonResponse(['success' => true, 'id' => $id]);

    } elseif ($action === 'update') {
        DB::query("UPDATE insumos SET nombre=?, unidad=?, stock_minimo=?, costo_unitario=?, categoria=?, id_proveedor=? WHERE id=?",
            [$data['nombre'], $data['unidad'], $data['stock_minimo'], $data['costo_unitario'], $data['categoria'], $data['id_proveedor'] ?? null, $data['id']]);
        jsonResponse(['success' => true]);

    } elseif ($action === 'kardex_entry') {
        // Ajuste manual de stock
        $insumo = DB::fetchOne("SELECT stock_actual FROM insumos WHERE id=?", [$data['id_insumo']]);
        if (!$insumo) jsonResponse(['error' => 'Insumo no encontrado'], 404);

        $tipo = $data['tipo']; // entrada | merma | ajuste | transferencia
        $cantidad = abs((float)$data['cantidad']);
        
        if (in_array($tipo, ['salida', 'merma'])) {
            $nuevo_stock = $insumo['stock_actual'] - $cantidad;
        } elseif ($tipo === 'ajuste') {
            $nuevo_stock = (float)$data['cantidad']; // Valor absoluto
            $cantidad = abs($nuevo_stock - $insumo['stock_actual']);
        } else {
            $nuevo_stock = $insumo['stock_actual'] + $cantidad;
        }

        if ($nuevo_stock < 0) jsonResponse(['error' => 'Stock insuficiente'], 400);

        DB::query("UPDATE insumos SET stock_actual=? WHERE id=?", [$nuevo_stock, $data['id_insumo']]);
        DB::query("INSERT INTO kardex (id_insumo, tipo, cantidad, stock_resultante, motivo, costo_unitario, id_usuario, lote, fecha_vencimiento) VALUES (?,?,?,?,?,?,?,?,?)",
            [$data['id_insumo'], $tipo, $cantidad, $nuevo_stock, $data['motivo'] ?? $tipo, $data['costo_unitario'] ?? 0, $user['id'], $data['lote'] ?? null, $data['fecha_vencimiento'] ?? null]);

        jsonResponse(['success' => true, 'nuevo_stock' => $nuevo_stock]);
    }

} else {
    jsonResponse(['error' => 'Método no permitido'], 405);
}
