<?php
// ============================================================
// CONFIGURACION DE BASE DE DATOS - RestaurantOS
// Auto-detecta entorno (LOCAL vs PRODUCCION) por SO/hostname.
// ============================================================
$__host      = $_SERVER['HTTP_HOST'] ?? gethostname();
$__isWindows = DIRECTORY_SEPARATOR === '\\';
$__isLocal   = (
    $__isWindows ||
    str_contains($__host, 'localhost') ||
    str_contains($__host, '127.0.0.1') ||
    str_contains($__host, '.test')     ||
    str_contains($__host, '.local')
);

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', $__isLocal ? '' : 'c4p1cu4$$');
define('DB_NAME', 'restaurant_db');
define('DB_PORT', 3306);
define('APP_ENV', $__isLocal ? 'development' : 'production');

date_default_timezone_set('America/Lima');

define('RESTAURANT_NAME', 'Mi Restaurante');
define('RESTAURANT_IGV', 0.18);
define('IGV', 0.18);
define('CURRENCY', 'S/');
define('SESSION_TIMEOUT', 3600);

// Auto-detectar BASE_URL
if (!defined('BASE_URL')) {
    $root    = dirname(__DIR__);
    $docroot = isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : '';
    $root    = rtrim(str_replace(DIRECTORY_SEPARATOR, '/', $root), '/');
    $docroot = rtrim(str_replace(DIRECTORY_SEPARATOR, '/', $docroot), '/');
    if ($docroot !== '' && strpos($root, $docroot) === 0) {
        $rel = substr($root, strlen($docroot));
        $rel = '/' . trim($rel, '/');
        define('BASE_URL', ($rel === '/') ? '' : $rel);
    } else {
        define('BASE_URL', '/restaurant');
    }
}
define('BASE_PATH', dirname(__DIR__));

// ============================================================
// Clase DB - wrapper PDO
// ============================================================
class DB {
    private static $pdo = null;
    private static $initialized = false;

    public static function init() {
        if (self::$initialized) return;
        $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $options = array(
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        );
        try {
            self::$pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            self::$pdo->exec("SET time_zone = '-05:00'");
        } catch (PDOException $e) {
            http_response_code(500);
            header('Content-Type: application/json');
            die(json_encode(array('error' => 'Error BD: ' . $e->getMessage())));
        }
        self::$initialized = true;
    }

    public static function getPdo() {
        self::init();
        return self::$pdo;
    }

    public static function query($sql, $params = array()) {
        self::init();
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public static function fetchAll($sql, $params = array()) {
        return self::query($sql, $params)->fetchAll();
    }

    public static function fetchOne($sql, $params = array()) {
        $row = self::query($sql, $params)->fetch();
        return $row ? $row : null;
    }

    public static function lastInsertId() {
        self::init();
        return self::$pdo->lastInsertId();
    }

    public static function escape($str) {
        self::init();
        return substr(self::$pdo->quote($str), 1, -1);
    }
}

DB::init();
