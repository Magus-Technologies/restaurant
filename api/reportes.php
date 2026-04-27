<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin(['administrador', 'supervisor', 'cajero']);

$method = $_SERVER['REQUEST_METHOD'];
if ($method !== 'GET') jsonResponse(['error' => 'Metodo no permitido'], 405);

$action    = $_GET['action']    ?? 'dashboard';
$fecha_ini = $_GET['fecha_ini'] ?? date('Y-m-d');
$fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');

if ($action === 'dashboard') {
    $hoy = date('Y-m-d');
    $ventas_hoy  = DB::fetchOne("SELECT COUNT(*) as ordenes, COALESCE(SUM(total),0) as total FROM pagos WHERE DATE(created_at)=?", [$hoy]);
    $mesas_act   = DB::fetchOne("SELECT COUNT(*) as cnt FROM mesas WHERE estado='ocupada'");
    $items_coc   = DB::fetchOne("SELECT COUNT(*) as cnt FROM orden_detalle WHERE estado IN ('pendiente','preparando')");
    $stock_bajo  = DB::fetchOne("SELECT COUNT(*) as cnt FROM insumos WHERE stock_actual <= stock_minimo AND activo=1");
    $ult_ordenes = DB::fetchAll("SELECT o.numero, m.numero as mesa, u.nombre as mozo, o.estado, p.total, o.created_at FROM ordenes o JOIN mesas m ON m.id=o.id_mesa JOIN usuarios u ON u.id=o.id_mozo LEFT JOIN pagos p ON p.id_orden=o.id WHERE DATE(o.created_at)=? ORDER BY o.created_at DESC LIMIT 10", [$hoy]);
    jsonResponse(['ventas_hoy'=>$ventas_hoy,'mesas_activas'=>$mesas_act['cnt'],'items_cocina'=>$items_coc['cnt'],'stock_bajo'=>$stock_bajo['cnt'],'ultimas_ordenes'=>$ult_ordenes]);

} elseif ($action === 'ventas_dia') {
    $rows = DB::fetchAll("SELECT DATE(created_at) as fecha, COUNT(*) as ordenes, SUM(total) as total, SUM(descuento) as descuentos, SUM(igv) as igv, SUM(propina) as propinas, AVG(total) as ticket_promedio FROM pagos WHERE DATE(created_at) BETWEEN ? AND ? GROUP BY DATE(created_at) ORDER BY fecha", [$fecha_ini,$fecha_fin]);
    $tot  = DB::fetchOne("SELECT COUNT(*) as total_ordenes, COALESCE(SUM(total),0) as total_ventas, COALESCE(AVG(total),0) as ticket_prom FROM pagos WHERE DATE(created_at) BETWEEN ? AND ?", [$fecha_ini,$fecha_fin]);
    jsonResponse(['detalle'=>$rows,'totales'=>$tot]);

} elseif ($action === 'ventas_hora') {
    $rows = DB::fetchAll("SELECT HOUR(created_at) as hora, COUNT(*) as ordenes, SUM(total) as total, AVG(total) as ticket_promedio FROM pagos WHERE DATE(created_at) BETWEEN ? AND ? GROUP BY HOUR(created_at) ORDER BY hora", [$fecha_ini,$fecha_fin]);
    jsonResponse($rows);

} elseif ($action === 'ventas_mozo') {
    $rows = DB::fetchAll("SELECT u.nombre, u.apellido, COUNT(DISTINCT o.id) as ordenes, COALESCE(SUM(p.total),0) as total_ventas, COALESCE(AVG(p.total),0) as ticket_promedio, COALESCE(SUM(p.propina),0) as propinas FROM ordenes o JOIN usuarios u ON u.id=o.id_mozo JOIN pagos p ON p.id_orden=o.id WHERE DATE(p.created_at) BETWEEN ? AND ? GROUP BY u.id ORDER BY total_ventas DESC", [$fecha_ini,$fecha_fin]);
    jsonResponse($rows);

} elseif ($action === 'top_platos') {
    $rows = DB::fetchAll("SELECT p.nombre, c.nombre as categoria, SUM(od.cantidad) as vendidos, SUM(od.subtotal) as ingresos, COUNT(DISTINCT od.id_orden) as en_ordenes FROM orden_detalle od JOIN platos p ON p.id=od.id_plato JOIN categorias c ON c.id=p.id_categoria JOIN ordenes o ON o.id=od.id_orden WHERE DATE(o.created_at) BETWEEN ? AND ? AND od.estado!='cancelado' GROUP BY p.id ORDER BY vendidos DESC LIMIT 20", [$fecha_ini,$fecha_fin]);
    jsonResponse($rows);

} elseif ($action === 'rentabilidad') {
    $rows = DB::fetchAll("SELECT p.nombre, c.nombre as categoria, p.precio as precio_venta, SUM(od.cantidad) as vendidos, SUM(od.subtotal) as ingresos, (SELECT COALESCE(SUM(r.cantidad*i.costo_unitario),0) FROM recetas r JOIN insumos i ON i.id=r.id_insumo WHERE r.id_plato=p.id) as costo_receta, SUM(od.cantidad)*(SELECT COALESCE(SUM(r.cantidad*i.costo_unitario),0) FROM recetas r JOIN insumos i ON i.id=r.id_insumo WHERE r.id_plato=p.id) as costo_total FROM orden_detalle od JOIN platos p ON p.id=od.id_plato JOIN categorias c ON c.id=p.id_categoria JOIN ordenes o ON o.id=od.id_orden WHERE DATE(o.created_at) BETWEEN ? AND ? AND od.estado!='cancelado' GROUP BY p.id ORDER BY ingresos DESC", [$fecha_ini,$fecha_fin]);
    foreach ($rows as &$r) {
        $r['utilidad']   = (float)$r['ingresos'] - (float)$r['costo_total'];
        $r['margen_pct'] = (float)$r['ingresos']>0 ? round(($r['utilidad']/(float)$r['ingresos'])*100,1) : 0;
    }
    $tot = ['ingresos'=>array_sum(array_column($rows,'ingresos')),'costo'=>array_sum(array_column($rows,'costo_total')),'utilidad'=>array_sum(array_column($rows,'utilidad'))];
    $tot['margen_global'] = $tot['ingresos']>0 ? round(($tot['utilidad']/$tot['ingresos'])*100,1) : 0;
    jsonResponse(['platos'=>$rows,'totales'=>$tot]);

} elseif ($action === 'consumo_insumos') {
    $rows = DB::fetchAll("SELECT i.nombre, i.unidad, i.categoria, COALESCE(SUM(CASE WHEN k.tipo='salida' THEN k.cantidad ELSE 0 END),0) as consumo, COALESCE(SUM(CASE WHEN k.tipo='merma' THEN k.cantidad ELSE 0 END),0) as merma, COALESCE(SUM(CASE WHEN k.tipo='entrada' THEN k.cantidad ELSE 0 END),0) as entradas, COALESCE(SUM(CASE WHEN k.tipo='salida' THEN k.cantidad*i.costo_unitario ELSE 0 END),0) as costo_consumo, i.stock_actual, i.stock_minimo FROM insumos i LEFT JOIN kardex k ON k.id_insumo=i.id AND DATE(k.created_at) BETWEEN ? AND ? WHERE i.activo=1 GROUP BY i.id ORDER BY costo_consumo DESC", [$fecha_ini,$fecha_fin]);
    jsonResponse($rows);

} elseif ($action === 'inventario_valorizado') {
    $rows = DB::fetchAll("SELECT i.nombre, i.unidad, i.categoria, i.stock_actual, i.stock_minimo, i.costo_unitario, (i.stock_actual*i.costo_unitario) as valor_total, p.nombre as proveedor_nombre, CASE WHEN i.stock_actual<=0 THEN 'sin_stock' WHEN i.stock_actual<=i.stock_minimo THEN 'stock_bajo' ELSE 'ok' END as estado_stock FROM insumos i LEFT JOIN proveedores p ON p.id=i.id_proveedor WHERE i.activo=1 ORDER BY valor_total DESC");
    $tot = DB::fetchOne("SELECT COUNT(*) as total_items, COALESCE(SUM(stock_actual*costo_unitario),0) as valor_total, SUM(CASE WHEN stock_actual<=0 THEN 1 ELSE 0 END) as sin_stock, SUM(CASE WHEN stock_actual<=stock_minimo AND stock_actual>0 THEN 1 ELSE 0 END) as stock_bajo FROM insumos WHERE activo=1");
    jsonResponse(['items'=>$rows,'totales'=>$tot]);

} elseif ($action === 'tiempos_atencion') {
    $por_plato = DB::fetchAll("SELECT p.nombre, c.nombre as categoria, COUNT(od.id) as total, AVG(TIMESTAMPDIFF(MINUTE,od.created_at,od.updated_at)) as tiempo_promedio, MIN(TIMESTAMPDIFF(MINUTE,od.created_at,od.updated_at)) as tiempo_min, MAX(TIMESTAMPDIFF(MINUTE,od.created_at,od.updated_at)) as tiempo_max FROM orden_detalle od JOIN platos p ON p.id=od.id_plato JOIN categorias c ON c.id=p.id_categoria WHERE od.estado='entregado' AND DATE(od.created_at) BETWEEN ? AND ? GROUP BY p.id ORDER BY tiempo_promedio DESC LIMIT 20", [$fecha_ini,$fecha_fin]);
    $mesa = DB::fetchOne("SELECT AVG(TIMESTAMPDIFF(MINUTE,o.created_at,p.created_at)) as tiempo_promedio_mesa, MIN(TIMESTAMPDIFF(MINUTE,o.created_at,p.created_at)) as tiempo_min, MAX(TIMESTAMPDIFF(MINUTE,o.created_at,p.created_at)) as tiempo_max FROM ordenes o JOIN pagos p ON p.id_orden=o.id WHERE DATE(o.created_at) BETWEEN ? AND ?", [$fecha_ini,$fecha_fin]);
    jsonResponse(['por_plato'=>$por_plato,'mesa'=>$mesa]);

} elseif ($action === 'ventas') {
    $ventas_dia = DB::fetchAll("SELECT DATE(created_at) as fecha, COUNT(*) as ordenes, SUM(total) as total FROM pagos WHERE DATE(created_at) BETWEEN ? AND ? GROUP BY DATE(created_at) ORDER BY fecha", [$fecha_ini,$fecha_fin]);
    $top_platos = DB::fetchAll("SELECT p.nombre, SUM(od.cantidad) as vendidos, SUM(od.subtotal) as total FROM orden_detalle od JOIN platos p ON p.id=od.id_plato JOIN ordenes o ON o.id=od.id_orden WHERE DATE(o.created_at) BETWEEN ? AND ? AND od.estado!='cancelado' GROUP BY p.id ORDER BY vendidos DESC LIMIT 10", [$fecha_ini,$fecha_fin]);
    $por_mozo   = DB::fetchAll("SELECT u.nombre, COUNT(o.id) as ordenes, SUM(p.total) as total FROM ordenes o JOIN usuarios u ON u.id=o.id_mozo JOIN pagos p ON p.id_orden=o.id WHERE DATE(p.created_at) BETWEEN ? AND ? GROUP BY u.id ORDER BY total DESC", [$fecha_ini,$fecha_fin]);
    jsonResponse(['ventas_dia'=>$ventas_dia,'top_platos'=>$top_platos,'por_mozo'=>$por_mozo]);

} else {
    jsonResponse(['error'=>'Accion no valida'],400);
}
