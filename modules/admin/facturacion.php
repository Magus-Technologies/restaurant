<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/sunat.php';
require_once __DIR__ . '/../../includes/sunat/SunatService.php';

requireLogin(['administrador', 'cajero', 'supervisor']);
$user = currentUser();

$pdo = DB::getPdo();

// Flash mediante sesión
if (!isset($_SESSION['flash'])) $_SESSION['flash'] = null;
function fc_flash(string $tipo, string $msg): void {
    $_SESSION['flash'] = ['tipo' => $tipo, 'mensaje' => $msg];
}
function fc_flash_pop(): ?array {
    $f = $_SESSION['flash'] ?? null;
    $_SESSION['flash'] = null;
    return $f;
}
function fc_redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}
function fc_url(array $extra = []): string {
    $base = BASE_URL . '/modules/admin/facturacion.php';
    return $extra ? $base . '?' . http_build_query($extra) : $base;
}

// ─── Acciones GET (descarga/visor) ───────────────────────────────
$accion = getVar('accion', '');
if ($accion === 'xml' && getVar('id', '')) {
    $row = DB::fetchOne("SELECT serie_doc, num_doc, tipo_comprobante, sunat_xml FROM pagos WHERE id=?", [(int)getVar('id', 0)]);
    if (!$row || empty($row['sunat_xml'])) { http_response_code(404); echo 'Sin XML'; exit; }
    $tipo = $row['tipo_comprobante'] === 'factura' ? '01' : '03';
    $num  = str_pad((string)$row['num_doc'], 8, '0', STR_PAD_LEFT);
    $name = SUNAT_RUC . '-' . $tipo . '-' . $row['serie_doc'] . '-' . $num . '.xml';
    if (getVar('ver', '')) {
        header('Content-Type: application/xml; charset=utf-8');
    } else {
        header('Content-Type: application/xml; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $name . '"');
    }
    echo $row['sunat_xml'];
    exit;
}
if ($accion === 'cdr' && getVar('id', '')) {
    $row = DB::fetchOne("SELECT serie_doc, num_doc, tipo_comprobante, sunat_cdr FROM pagos WHERE id=?", [(int)getVar('id', 0)]);
    if (!$row || empty($row['sunat_cdr'])) { http_response_code(404); echo 'Sin CDR'; exit; }
    $tipo = $row['tipo_comprobante'] === 'factura' ? '01' : '03';
    $num  = str_pad((string)$row['num_doc'], 8, '0', STR_PAD_LEFT);
    $name = 'R-' . SUNAT_RUC . '-' . $tipo . '-' . $row['serie_doc'] . '-' . $num . '.zip';
    $bin  = base64_decode($row['sunat_cdr'], true);
    if ($bin === false) {
        header('Content-Type: text/plain; charset=utf-8');
        echo $row['sunat_cdr'];
        exit;
    }
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $name . '"');
    echo $bin;
    exit;
}

// ─── Acciones POST ──────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = postVar('action', '');
    $id     = (int) postVar('id_pago', 0);

    if ($action === 'asignar_serie' && $id > 0) {
        // Asigna serie/num al pago según tipo_comprobante (lo que ya eligió el cajero).
        $pago = DB::fetchOne("SELECT * FROM pagos WHERE id=?", [$id]);
        if (!$pago) {
            fc_flash('error', "Pago #$id no encontrado.");
        } elseif (!in_array($pago['tipo_comprobante'], ['boleta', 'factura'], true)) {
            fc_flash('error', "Solo se pueden facturar boletas o facturas (este es '{$pago['tipo_comprobante']}').");
        } elseif (!empty($pago['serie_doc'])) {
            fc_flash('error', "Este pago ya tiene serie asignada: {$pago['serie_doc']}-{$pago['num_doc']}.");
        } else {
            $serie = $pago['tipo_comprobante'] === 'factura' ? SUNAT_SERIE_FACTURA : SUNAT_SERIE_BOLETA;
            $num   = SunatService::siguienteNumero($pdo, $serie);
            DB::query("UPDATE pagos SET serie_doc=?, num_doc=? WHERE id=?", [$serie, $num, $id]);

            // Inmediatamente generamos el XML
            $svc = new SunatService($pdo);
            $r   = $svc->generarXml($id);
            if ($r['ok']) {
                fc_flash('success', "Serie $serie-" . str_pad((string)$num, 8, '0', STR_PAD_LEFT) . " asignada. " . $r['mensaje']);
            } else {
                fc_flash('error', "Serie asignada pero error al generar XML: " . $r['mensaje']);
            }
        }
        fc_redirect(fc_url(['v' => $id]));
    }

    if ($action === 'enviar_sunat' && $id > 0) {
        $svc = new SunatService($pdo);
        $r   = $svc->enviarSunat($id);
        fc_flash($r['ok'] ? 'success' : 'error', $r['mensaje']);
        fc_redirect(fc_url(['v' => $id]));
    }

    if ($action === 'regenerar' && $id > 0) {
        $svc = new SunatService($pdo);
        $r   = $svc->generarXml($id);
        fc_flash($r['ok'] ? 'success' : 'error', $r['mensaje']);
        fc_redirect(fc_url(['v' => $id]));
    }

    fc_redirect(fc_url());
}

// ─── Datos para vista ────────────────────────────────────────────
$verId = (int) getVar('v', 0);
$detallePago = null;
$detalleItems = [];
if ($verId > 0) {
    $detallePago = DB::fetchOne("
        SELECT p.*, o.numero AS orden_numero, o.created_at AS orden_fecha,
               u.nombre AS cajero_nombre
        FROM pagos p
        JOIN ordenes o ON o.id = p.id_orden
        LEFT JOIN usuarios u ON u.id = p.id_cajero
        WHERE p.id = ?
    ", [$verId]);
    if ($detallePago) {
        $detalleItems = DB::fetchAll("
            SELECT od.*, pl.nombre AS plato_nombre
            FROM orden_detalle od
            JOIN platos pl ON pl.id = od.id_plato
            WHERE od.id_orden = ? AND od.estado <> 'cancelado'
            ORDER BY od.id
        ", [(int)$detallePago['id_orden']]);
    }
}

// Filtros lista
$fDesde  = getVar('desde', date('Y-m-01'));
$fHasta  = getVar('hasta', date('Y-m-d'));
$fTipo   = getVar('tipo', '');
$fEstado = getVar('estado', '');

$where  = ["DATE(p.created_at) BETWEEN ? AND ?", "p.tipo_comprobante IN ('boleta','factura')"];
$params = [$fDesde, $fHasta];
if ($fTipo !== '' && in_array($fTipo, ['boleta', 'factura'], true)) {
    $where[]  = "p.tipo_comprobante = ?";
    $params[] = $fTipo;
}
if ($fEstado !== '') {
    if ($fEstado === 'sin_emitir') {
        $where[] = "p.serie_doc IS NULL";
    } else {
        $where[]  = "p.sunat_estado = ?";
        $params[] = $fEstado;
    }
}
$sqlWhere = implode(' AND ', $where);

$comprobantes = DB::fetchAll("
    SELECT p.id, p.numero, p.tipo_comprobante, p.serie_doc, p.num_doc,
           p.total, p.ruc_cliente, p.razon_social, p.created_at,
           p.sunat_estado, p.sunat_mensaje,
           o.numero AS orden_numero
    FROM pagos p
    JOIN ordenes o ON o.id = p.id_orden
    WHERE $sqlWhere
    ORDER BY p.id DESC
    LIMIT 300
", $params);

// KPIs
$kpis = DB::fetchOne("
    SELECT
      COUNT(*)                                        AS total,
      SUM(CASE WHEN p.serie_doc IS NULL THEN 1 ELSE 0 END) AS sin_emitir,
      SUM(CASE WHEN p.sunat_estado='aceptado'  THEN 1 ELSE 0 END) AS aceptados,
      SUM(CASE WHEN p.sunat_estado='pendiente' THEN 1 ELSE 0 END) AS pendientes,
      SUM(CASE WHEN p.sunat_estado='rechazado' THEN 1 ELSE 0 END) AS rechazados
    FROM pagos p
    WHERE p.tipo_comprobante IN ('boleta','factura')
      AND DATE(p.created_at) BETWEEN ? AND ?
", [$fDesde, $fHasta]);

$flash = fc_flash_pop();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Facturación SUNAT — RestaurantOS</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<style>
:root{--bg:#08080f;--surface:#111119;--surface2:#1a1a28;--surface3:#222230;--accent:#ff6b35;--green:#22c55e;--yellow:#f59e0b;--red:#ef4444;--blue:#3b82f6;--purple:#a855f7;--text:#f0f0f8;--muted:#5a5a78;--border:rgba(255,255,255,0.07);--radius:16px}
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--text);min-height:100vh}
.wrap{max-width:1280px;margin:0 auto;padding:24px}
.topnav{display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;padding-bottom:16px;border-bottom:1px solid var(--border)}
.topnav h1{font-family:'Syne',sans-serif;font-size:24px;font-weight:800}
.topnav h1 span{color:var(--accent)}
.topnav .right{display:flex;gap:10px;align-items:center}
.btn{background:var(--surface2);border:1px solid var(--border);border-radius:10px;padding:9px 16px;color:var(--text);font-size:13px;font-weight:600;cursor:pointer;transition:all .15s;font-family:'DM Sans',sans-serif;text-decoration:none;display:inline-flex;align-items:center;gap:6px}
.btn:hover{border-color:var(--accent);color:var(--accent)}
.btn-primary{background:var(--accent);border-color:var(--accent);color:white}
.btn-primary:hover{background:#e55a28;color:white}
.btn-success{background:var(--green);border-color:var(--green);color:white}
.btn-success:hover{background:#16a34a}
.btn-danger{background:rgba(239,68,68,0.1);border-color:var(--red);color:var(--red)}
.btn-sm{padding:5px 10px;font-size:12px;border-radius:8px}
.kpi-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:12px;margin-bottom:20px}
.kpi{background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:16px}
.kpi .k-val{font-family:'Syne',sans-serif;font-size:26px;font-weight:800;margin:6px 0 2px}
.kpi .k-lbl{font-size:11px;text-transform:uppercase;letter-spacing:1px;color:var(--muted)}
.kpi.green .k-val{color:var(--green)}
.kpi.yellow .k-val{color:var(--yellow)}
.kpi.red .k-val{color:var(--red)}
.kpi.blue .k-val{color:var(--blue)}
.kpi.muted .k-val{color:var(--muted)}
.box{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;margin-bottom:20px}
.box-header{display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid var(--border)}
.box-header h3{font-family:'Syne',sans-serif;font-size:15px;font-weight:700}
.box-body{padding:18px 20px}
.filters{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:10px;align-items:end}
.field{display:flex;flex-direction:column;gap:5px}
label{font-size:11px;text-transform:uppercase;letter-spacing:1px;color:var(--muted)}
input,select{background:var(--surface2);border:1px solid var(--border);border-radius:10px;padding:9px 12px;color:var(--text);font-size:13px;font-family:'DM Sans',sans-serif;outline:none;width:100%}
input:focus,select:focus{border-color:var(--accent)}
table{width:100%;border-collapse:collapse}
th{text-align:left;padding:10px 16px;font-size:11px;text-transform:uppercase;letter-spacing:1px;color:var(--muted);background:var(--surface2)}
td{padding:11px 16px;border-bottom:1px solid rgba(255,255,255,0.04);font-size:13px;vertical-align:middle}
tr:last-child td{border-bottom:none}
tr:hover td{background:rgba(255,255,255,0.02)}
.badge{padding:3px 10px;border-radius:50px;font-size:11px;font-weight:700;display:inline-block}
.badge-green{background:rgba(34,197,94,0.15);color:var(--green)}
.badge-red{background:rgba(239,68,68,0.15);color:var(--red)}
.badge-yellow{background:rgba(245,158,11,0.15);color:var(--yellow)}
.badge-muted{background:var(--surface2);color:var(--muted)}
.alert{padding:12px 16px;border-radius:10px;margin-bottom:16px;border:1px solid;font-size:14px}
.alert-success{background:rgba(34,197,94,0.08);border-color:rgba(34,197,94,0.25);color:var(--green)}
.alert-error{background:rgba(239,68,68,0.08);border-color:rgba(239,68,68,0.25);color:var(--red)}
.muted{color:var(--muted)}
.right{text-align:right}
.code{font-family:'Courier New',monospace;font-size:12px}
.detail-grid{display:grid;grid-template-columns:1fr 1fr;gap:24px}
@media(max-width:900px){.detail-grid{grid-template-columns:1fr}}
.row-info{display:grid;grid-template-columns:130px 1fr;padding:6px 0;font-size:13px;border-bottom:1px solid rgba(255,255,255,0.03)}
.row-info b{color:var(--muted);font-weight:600}
.actions-row{display:flex;gap:8px;flex-wrap:wrap;margin-top:14px}
.qr-area{background:var(--surface2);border:1px solid var(--border);border-radius:10px;padding:10px;font-family:'Courier New',monospace;font-size:11px;word-break:break-all;max-height:90px;overflow-y:auto;color:var(--muted)}
</style>
</head>
<body>

<div class="wrap">

  <div class="topnav">
    <h1>📄 Facturación <span>SUNAT</span></h1>
    <div class="right">
      <span class="muted" style="font-size:13px"><?= sanitize($user['nombre']) ?> · <?= ucfirst($user['rol']) ?></span>
      <a href="<?= BASE_URL ?>/modules/admin/index.php" class="btn">← Admin</a>
      <a href="<?= BASE_URL ?>/logout.php" class="btn">Salir</a>
    </div>
  </div>

  <?php if ($flash): ?>
    <div class="alert alert-<?= $flash['tipo'] === 'success' ? 'success' : 'error' ?>">
      <?= sanitize($flash['mensaje']) ?>
    </div>
  <?php endif; ?>

  <?php if ($detallePago): ?>
    <!-- ─── VISTA DETALLE ─────────────────────────────── -->
    <a href="<?= fc_url() ?>" class="btn" style="margin-bottom:16px">← Volver a la lista</a>

    <div class="box">
      <div class="box-header">
        <h3>Comprobante #<?= sanitize($detallePago['numero']) ?></h3>
        <?php
          $estado = $detallePago['sunat_estado'];
          $clase  = match ($estado) {
              'aceptado'  => 'badge-green',
              'pendiente' => 'badge-yellow',
              'rechazado' => 'badge-red',
              default     => 'badge-muted'
          };
          $txt = $estado ? strtoupper($estado) : ($detallePago['serie_doc'] ? 'EMITIDO' : 'SIN EMITIR');
        ?>
        <span class="badge <?= $clase ?>"><?= $txt ?></span>
      </div>
      <div class="box-body">
        <div class="detail-grid">
          <div>
            <h4 style="font-family:'Syne',sans-serif;font-size:13px;color:var(--accent);margin-bottom:10px;text-transform:uppercase;letter-spacing:1px">Datos del Pago</h4>
            <div class="row-info"><b>Tipo</b><span><?= ucfirst($detallePago['tipo_comprobante']) ?></span></div>
            <div class="row-info"><b>Serie / Número</b><span class="code"><?= $detallePago['serie_doc'] ? sanitize($detallePago['serie_doc']) . '-' . str_pad((string)$detallePago['num_doc'], 8, '0', STR_PAD_LEFT) : '—' ?></span></div>
            <div class="row-info"><b>Orden</b><span><?= sanitize($detallePago['orden_numero']) ?></span></div>
            <div class="row-info"><b>Cajero</b><span><?= sanitize($detallePago['cajero_nombre'] ?? '—') ?></span></div>
            <div class="row-info"><b>Fecha</b><span><?= date('d/m/Y H:i', strtotime($detallePago['created_at'])) ?></span></div>
            <div class="row-info"><b>Subtotal</b><span><?= formatMoney((float)$detallePago['subtotal']) ?></span></div>
            <div class="row-info"><b>IGV</b><span><?= formatMoney((float)$detallePago['igv']) ?></span></div>
            <div class="row-info"><b>Total</b><span style="font-weight:700;color:var(--accent)"><?= formatMoney((float)$detallePago['total']) ?></span></div>
          </div>
          <div>
            <h4 style="font-family:'Syne',sans-serif;font-size:13px;color:var(--accent);margin-bottom:10px;text-transform:uppercase;letter-spacing:1px">Cliente / SUNAT</h4>
            <div class="row-info"><b>RUC/DNI</b><span class="code"><?= sanitize($detallePago['ruc_cliente'] ?? '—') ?: '—' ?></span></div>
            <div class="row-info"><b>Razón social</b><span><?= sanitize($detallePago['razon_social'] ?? '—') ?: '—' ?></span></div>
            <?php if ($detallePago['sunat_fecha']): ?>
              <div class="row-info"><b>Última gestión</b><span><?= date('d/m/Y H:i', strtotime($detallePago['sunat_fecha'])) ?></span></div>
            <?php endif; ?>
            <?php if ($detallePago['sunat_mensaje']): ?>
              <div class="row-info"><b>Mensaje</b><span><?= sanitize($detallePago['sunat_mensaje']) ?></span></div>
            <?php endif; ?>
            <?php if ($detallePago['sunat_qr']): ?>
              <div style="margin-top:8px">
                <label>QR Info</label>
                <div class="qr-area"><?= sanitize($detallePago['sunat_qr']) ?></div>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <div class="actions-row">
          <?php if (!$detallePago['serie_doc']): ?>
            <form method="post" style="display:inline">
              <input type="hidden" name="action" value="asignar_serie">
              <input type="hidden" name="id_pago" value="<?= (int)$detallePago['id'] ?>">
              <button class="btn btn-primary">⚡ Asignar serie + generar XML</button>
            </form>
          <?php else: ?>
            <?php if ($detallePago['sunat_xml']): ?>
              <a class="btn" href="<?= fc_url(['accion' => 'xml', 'id' => $detallePago['id'], 'ver' => 1]) ?>" target="_blank">👁 Ver XML</a>
              <a class="btn" href="<?= fc_url(['accion' => 'xml', 'id' => $detallePago['id']]) ?>">⬇ Descargar XML</a>
            <?php endif; ?>
            <?php if ($detallePago['sunat_estado'] !== 'aceptado' && $detallePago['sunat_xml']): ?>
              <form method="post" style="display:inline">
                <input type="hidden" name="action" value="enviar_sunat">
                <input type="hidden" name="id_pago" value="<?= (int)$detallePago['id'] ?>">
                <button class="btn btn-success">📤 Enviar a SUNAT</button>
              </form>
            <?php endif; ?>
            <?php if ($detallePago['sunat_estado'] === 'aceptado' && $detallePago['sunat_cdr']): ?>
              <a class="btn" href="<?= fc_url(['accion' => 'cdr', 'id' => $detallePago['id']]) ?>">⬇ Descargar CDR</a>
            <?php endif; ?>
            <?php if ($detallePago['sunat_estado'] !== 'aceptado'): ?>
              <form method="post" style="display:inline" onsubmit="return confirm('¿Regenerar XML? Se reemplaza el existente.')">
                <input type="hidden" name="action" value="regenerar">
                <input type="hidden" name="id_pago" value="<?= (int)$detallePago['id'] ?>">
                <button class="btn btn-danger">🔄 Regenerar XML</button>
              </form>
            <?php endif; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="box">
      <div class="box-header"><h3>Detalle de la orden</h3></div>
      <table>
        <thead>
          <tr>
            <th>Plato</th>
            <th class="right">Cant.</th>
            <th class="right">P. Unit.</th>
            <th class="right">Subtotal</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($detalleItems as $it): ?>
            <tr>
              <td><?= sanitize($it['plato_nombre']) ?></td>
              <td class="right"><?= (int)$it['cantidad'] ?></td>
              <td class="right"><?= formatMoney((float)$it['precio_unitario']) ?></td>
              <td class="right"><?= formatMoney((float)$it['subtotal']) ?></td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($detalleItems)): ?>
            <tr><td colspan="4" class="muted" style="text-align:center;padding:20px">Sin items</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

  <?php else: ?>
    <!-- ─── VISTA LISTA ─────────────────────────────── -->

    <div class="kpi-grid">
      <div class="kpi"><div class="k-lbl">Total</div><div class="k-val"><?= (int)($kpis['total'] ?? 0) ?></div></div>
      <div class="kpi muted"><div class="k-lbl">Sin emitir</div><div class="k-val"><?= (int)($kpis['sin_emitir'] ?? 0) ?></div></div>
      <div class="kpi yellow"><div class="k-lbl">Pendientes</div><div class="k-val"><?= (int)($kpis['pendientes'] ?? 0) ?></div></div>
      <div class="kpi green"><div class="k-lbl">Aceptados</div><div class="k-val"><?= (int)($kpis['aceptados'] ?? 0) ?></div></div>
      <div class="kpi red"><div class="k-lbl">Rechazados</div><div class="k-val"><?= (int)($kpis['rechazados'] ?? 0) ?></div></div>
    </div>

    <div class="box">
      <div class="box-header"><h3>Filtros</h3></div>
      <div class="box-body">
        <form method="get" class="filters">
          <div class="field"><label>Desde</label><input type="date" name="desde" value="<?= sanitize($fDesde) ?>"></div>
          <div class="field"><label>Hasta</label><input type="date" name="hasta" value="<?= sanitize($fHasta) ?>"></div>
          <div class="field">
            <label>Tipo</label>
            <select name="tipo">
              <option value="">Todos</option>
              <option value="boleta"  <?= $fTipo === 'boleta' ? 'selected' : '' ?>>Boleta</option>
              <option value="factura" <?= $fTipo === 'factura' ? 'selected' : '' ?>>Factura</option>
            </select>
          </div>
          <div class="field">
            <label>Estado</label>
            <select name="estado">
              <option value="">Todos</option>
              <option value="sin_emitir" <?= $fEstado === 'sin_emitir' ? 'selected' : '' ?>>Sin emitir</option>
              <option value="pendiente"  <?= $fEstado === 'pendiente'  ? 'selected' : '' ?>>Pendiente</option>
              <option value="aceptado"   <?= $fEstado === 'aceptado'   ? 'selected' : '' ?>>Aceptado</option>
              <option value="rechazado"  <?= $fEstado === 'rechazado'  ? 'selected' : '' ?>>Rechazado</option>
            </select>
          </div>
          <button class="btn btn-primary">Aplicar</button>
        </form>
      </div>
    </div>

    <div class="box">
      <div class="box-header"><h3>Comprobantes (Boleta / Factura)</h3></div>
      <table>
        <thead>
          <tr>
            <th>Pago</th>
            <th>Tipo</th>
            <th>Serie · Número</th>
            <th>Orden</th>
            <th>Cliente</th>
            <th class="right">Total</th>
            <th>Estado SUNAT</th>
            <th>Fecha</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($comprobantes as $c): ?>
            <?php
              $est = $c['sunat_estado'];
              if (!$c['serie_doc']) { $bClass='badge-muted'; $bTxt='SIN EMITIR'; }
              elseif ($est === 'aceptado')  { $bClass='badge-green';  $bTxt='ACEPTADO'; }
              elseif ($est === 'pendiente') { $bClass='badge-yellow'; $bTxt='PENDIENTE'; }
              elseif ($est === 'rechazado') { $bClass='badge-red';    $bTxt='RECHAZADO'; }
              else { $bClass='badge-muted'; $bTxt='—'; }
            ?>
            <tr>
              <td class="code"><?= sanitize($c['numero']) ?></td>
              <td><?= ucfirst($c['tipo_comprobante']) ?></td>
              <td class="code"><?= $c['serie_doc'] ? sanitize($c['serie_doc']) . '-' . str_pad((string)$c['num_doc'], 8, '0', STR_PAD_LEFT) : '—' ?></td>
              <td><?= sanitize($c['orden_numero']) ?></td>
              <td>
                <?= sanitize($c['razon_social'] ?? '') ?: '<span class="muted">—</span>' ?>
                <?php if (!empty($c['ruc_cliente'])): ?>
                  <div class="muted code" style="font-size:11px"><?= sanitize($c['ruc_cliente']) ?></div>
                <?php endif; ?>
              </td>
              <td class="right"><?= formatMoney((float)$c['total']) ?></td>
              <td><span class="badge <?= $bClass ?>"><?= $bTxt ?></span></td>
              <td class="muted" style="font-size:12px"><?= date('d/m H:i', strtotime($c['created_at'])) ?></td>
              <td><a class="btn btn-sm" href="<?= fc_url(['v' => $c['id']]) ?>">Ver</a></td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($comprobantes)): ?>
            <tr><td colspan="9" class="muted" style="text-align:center;padding:30px">Sin comprobantes en el rango.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

  <?php endif; ?>

</div>

</body>
</html>
