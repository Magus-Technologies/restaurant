<?php
require_once __DIR__ . '/../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============================================================
// AUTENTICACIÓN
// ============================================================

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function isApiRequest(): bool {
    $uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
    return strpos($uri, '/api/') !== false;
}

function requireLogin(array $roles = []): void {
    if (!isLoggedIn()) {
        if (isAjax() || isApiRequest()) {
            jsonResponse(['error' => 'No autenticado', 'redirect' => BASE_URL . '/index.php'], 401);
        }
        header('Location: ' . BASE_URL . '/index.php');
        exit;
    }
    if (!empty($roles) && !in_array($_SESSION['user_rol'], $roles)) {
        if (isAjax() || isApiRequest()) {
            jsonResponse(['error' => 'Sin permisos', 'rol' => $_SESSION['user_rol'], 'roles_requeridos' => $roles], 403);
        }
        header('Location: ' . BASE_URL . '/index.php?error=permisos');
        exit;
    }
}

function currentUser(): ?array {
    if (!isLoggedIn()) return null;
    return [
        'id'     => $_SESSION['user_id'],
        'nombre' => $_SESSION['user_nombre'],
        'rol'    => $_SESSION['user_rol'],
        'usuario'=> $_SESSION['user_usuario'],
    ];
}

function login(string $usuario, string $password): array {
    $row = DB::fetchOne("SELECT * FROM usuarios WHERE usuario = ? AND activo = 1", [$usuario]);

    if (!$row) {
        return ['ok' => false, 'msg' => 'Usuario no encontrado'];
    }
    if (!password_verify($password, $row['password'])) {
        return ['ok' => false, 'msg' => 'Contraseña incorrecta'];
    }

    $nombre_completo = trim(($row['nombre'] ?? '') . ' ' . ($row['apellido'] ?? ''));
    $_SESSION['user_id']      = $row['id'];
    $_SESSION['user_nombre']  = $nombre_completo;
    $_SESSION['user_rol']     = $row['rol'];
    $_SESSION['user_usuario'] = $row['usuario'];
    $_SESSION['login_time']   = time();

    return ['ok' => true, 'rol' => $row['rol']];
}

function logout(): void {
    session_destroy();
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

// ============================================================
// HELPERS
// ============================================================

function isAjax(): bool {
    $xhr = isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    $json = isset($_SERVER['CONTENT_TYPE']) &&
            strpos(isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '', 'application/json') !== false;
    $accept = isset($_SERVER['HTTP_ACCEPT']) &&
              strpos(isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : '', 'application/json') !== false;
    return $xhr || $json || $accept;
}

function jsonResponse(array $data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function generateNumero(string $prefix = 'ORD'): string {
    return $prefix . '-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
}

function formatMoney(float $amount): string {
    return CURRENCY . ' ' . number_format($amount, 2);
}

function timeAgo(string $datetime): string {
    $diff = time() - strtotime($datetime);
    if ($diff < 60)   return $diff . ' seg';
    if ($diff < 3600) return intdiv($diff, 60) . ' min';
    return intdiv($diff, 3600) . ' hr';
}

function alertColor($datetime_or_mins): string {
    if (is_numeric($datetime_or_mins)) {
        $mins = (float)$datetime_or_mins;
    } else {
        $mins = (time() - strtotime($datetime_or_mins)) / 60;
    }
    if ($mins < 10) return 'green';
    if ($mins < 20) return 'yellow';
    return 'red';
}

function sanitize(string $str): string {
    return htmlspecialchars(trim($str), ENT_QUOTES, 'UTF-8');
}

function postVar(string $key, $default = '') {
    return $_POST[$key] ?? $default;
}

function getVar(string $key, $default = '') {
    return $_GET[$key] ?? $default;
}
