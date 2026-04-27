<?php
// Archivo temporal de diagnóstico — ELIMINAR después de usarlo
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Diagnóstico RestaurantOS</h2>";
echo "<pre>";

// PHP Version
echo "PHP Version: " . PHP_VERSION . "\n";

// Extensions
$needed = ['pdo', 'pdo_mysql', 'json', 'session', 'mbstring'];
foreach ($needed as $ext) {
    echo "Extension $ext: " . (extension_loaded($ext) ? "✓ OK" : "✗ FALTA") . "\n";
}

echo "\nDOCUMENT_ROOT: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "__DIR__: " . __DIR__ . "\n";
echo "BASE_URL calculado: ";

$scriptPath = str_replace('\\', '/', dirname(__DIR__ . '/x'));
$docRoot    = str_replace('\\', '/', rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/'));
if ($docRoot && strpos($scriptPath, $docRoot) === 0) {
    $rel = substr($scriptPath, strlen($docRoot));
    $rel = '/' . trim($rel, '/');
    echo ($rel === '/' ? '(raiz)' : $rel);
} else {
    echo "/restaurant (fallback)";
}
echo "\n";

// Test DB connection
echo "\nConexión BD: ";
try {
    require_once __DIR__ . '/config/database.php';
    $test = DB::fetchOne("SELECT 1 as ok");
    echo $test ? "✓ OK" : "✗ FALLO";
} catch (Exception $e) {
    echo "✗ ERROR: " . $e->getMessage();
}

echo "\n\narchivos clave:\n";
$files = [
    'config/database.php',
    'includes/functions.php',
    'index.php',
    'modules/mozos/index.php',
    'modules/cocina/index.php',
    'modules/caja/index.php',
    'modules/admin/index.php',
];
foreach ($files as $f) {
    $path = __DIR__ . '/' . $f;
    echo "$f: " . (file_exists($path) ? "✓ existe" : "✗ NO EXISTE") . "\n";
}

echo "</pre>";
echo "<p style='color:red'><b>IMPORTANTE: Elimina este archivo después de revisar.</b></p>";
