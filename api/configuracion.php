<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin(['administrador', 'supervisor']);

$method = $_SERVER['REQUEST_METHOD'];

// Ensure config table exists
DB::query("CREATE TABLE IF NOT EXISTS configuracion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    clave VARCHAR(100) UNIQUE NOT NULL,
    valor TEXT,
    grupo VARCHAR(50) DEFAULT 'general',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");

DB::query("CREATE TABLE IF NOT EXISTS sucursales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(200) NOT NULL,
    direccion TEXT,
    telefono VARCHAR(30),
    email VARCHAR(100),
    ruc VARCHAR(20),
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

DB::query("CREATE TABLE IF NOT EXISTS impresoras (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    tipo ENUM('tickets','cocina','caja','etiquetas') DEFAULT 'tickets',
    cabecera TEXT,
    pie TEXT,
    ancho_papel INT DEFAULT 80,
    activo TINYINT(1) DEFAULT 1,
    id_sucursal INT DEFAULT 1
)");

DB::query("CREATE TABLE IF NOT EXISTS roles_permisos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rol VARCHAR(50) NOT NULL,
    modulo VARCHAR(50) NOT NULL,
    puede_ver TINYINT(1) DEFAULT 1,
    puede_crear TINYINT(1) DEFAULT 0,
    puede_editar TINYINT(1) DEFAULT 0,
    puede_eliminar TINYINT(1) DEFAULT 0,
    UNIQUE KEY uk_rol_modulo (rol, modulo)
)");

if ($method === 'GET') {
    $grupo = $_GET['grupo'] ?? null;
    $action = $_GET['action'] ?? 'config';

    if ($action === 'all') {
        $rows = DB::fetchAll("SELECT clave, valor, grupo FROM configuracion ORDER BY grupo, clave");
        $config = [];
        foreach ($rows as $r) $config[$r['clave']] = $r['valor'];
        jsonResponse($config);

    } elseif ($action === 'sucursales') {
        jsonResponse(DB::fetchAll("SELECT * FROM sucursales ORDER BY nombre"));

    } elseif ($action === 'impresoras') {
        jsonResponse(DB::fetchAll("SELECT * FROM impresoras ORDER BY tipo, nombre"));

    } elseif ($action === 'permisos') {
        $rol = $_GET['rol'] ?? null;
        if ($rol) {
            $perms = DB::fetchAll("SELECT * FROM roles_permisos WHERE rol=?", [$rol]);
            jsonResponse($perms);
        } else {
            jsonResponse(DB::fetchAll("SELECT * FROM roles_permisos ORDER BY rol, modulo"));
        }
    } else {
        $rows = DB::fetchAll("SELECT clave, valor FROM configuracion" . ($grupo ? " WHERE grupo=?" : ""), $grupo ? [$grupo] : []);
        $config = [];
        foreach ($rows as $r) $config[$r['clave']] = $r['valor'];
        jsonResponse($config);
    }

} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $action = $data['action'] ?? '';

    if ($action === 'save_config') {
        foreach ($data['config'] as $clave => $valor) {
            DB::query("INSERT INTO configuracion (clave, valor, grupo) VALUES (?,?,?)
                ON DUPLICATE KEY UPDATE valor=?, updated_at=NOW()",
                [$clave, $valor, $data['grupo'] ?? 'general', $valor]);
        }
        jsonResponse(['success' => true]);

    } elseif ($action === 'save_sucursal') {
        if (!empty($data['id'])) {
            DB::query("UPDATE sucursales SET nombre=?, direccion=?, telefono=?, email=?, ruc=? WHERE id=?",
                [$data['nombre'], $data['direccion'], $data['telefono'], $data['email'], $data['ruc'], $data['id']]);
        } else {
            DB::query("INSERT INTO sucursales (nombre, direccion, telefono, email, ruc) VALUES (?,?,?,?,?)",
                [$data['nombre'], $data['direccion'] ?? '', $data['telefono'] ?? '', $data['email'] ?? '', $data['ruc'] ?? '']);
        }
        jsonResponse(['success' => true, 'id' => DB::lastInsertId()]);

    } elseif ($action === 'toggle_sucursal') {
        DB::query("UPDATE sucursales SET activo = NOT activo WHERE id=?", [$data['id']]);
        jsonResponse(['success' => true]);

    } elseif ($action === 'save_impresora') {
        if (!empty($data['id'])) {
            DB::query("UPDATE impresoras SET nombre=?, tipo=?, cabecera=?, pie=?, ancho_papel=? WHERE id=?",
                [$data['nombre'], $data['tipo'], $data['cabecera'] ?? '', $data['pie'] ?? '', $data['ancho_papel'] ?? 80, $data['id']]);
        } else {
            DB::query("INSERT INTO impresoras (nombre, tipo, cabecera, pie, ancho_papel) VALUES (?,?,?,?,?)",
                [$data['nombre'], $data['tipo'], $data['cabecera'] ?? '', $data['pie'] ?? '', $data['ancho_papel'] ?? 80]);
        }
        jsonResponse(['success' => true, 'id' => DB::lastInsertId()]);

    } elseif ($action === 'delete_impresora') {
        DB::query("DELETE FROM impresoras WHERE id=?", [$data['id']]);
        jsonResponse(['success' => true]);

    } elseif ($action === 'save_permiso') {
        DB::query("INSERT INTO roles_permisos (rol, modulo, puede_ver, puede_crear, puede_editar, puede_eliminar)
            VALUES (?,?,?,?,?,?)
            ON DUPLICATE KEY UPDATE puede_ver=?, puede_crear=?, puede_editar=?, puede_eliminar=?",
            [$data['rol'], $data['modulo'], $data['puede_ver']??1, $data['puede_crear']??0, $data['puede_editar']??0, $data['puede_eliminar']??0,
             $data['puede_ver']??1, $data['puede_crear']??0, $data['puede_editar']??0, $data['puede_eliminar']??0]);
        jsonResponse(['success' => true]);

    } elseif ($action === 'save_logo') {
        // Base64 logo save
        $logo = $data['logo'] ?? '';
        DB::query("INSERT INTO configuracion (clave, valor, grupo) VALUES ('logo_base64', ?, 'branding')
            ON DUPLICATE KEY UPDATE valor=?", [$logo, $logo]);
        jsonResponse(['success' => true]);
    }
}
