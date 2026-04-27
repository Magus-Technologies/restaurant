<?php
// Auth guard — debe estar ANTES de cualquier output HTML
require_once __DIR__ . '/../../includes/functions.php';
requireLogin(['cajero','administrador','supervisor']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Caja — RestaurantOS</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<style>
:root {
  --bg: #0a0e14;
  --surface: #131820;
  --surface2: #1c2330;
  --surface3: #242e3d;
  --accent: #22c55e;
  --orange: #ff6b35;
  --yellow: #f59e0b;
  --red: #ef4444;
  --blue: #3b82f6;
  --text: #e8edf5;
  --muted: #5e6d82;
  --border: rgba(255,255,255,0.07);
  --radius: 16px;
}
* { margin:0; padding:0; box-sizing:border-box; }
html,body { height:100%; }
body { font-family:'DM Sans',sans-serif; background:var(--bg); color:var(--text); display:flex; flex-direction:column; }

/* HEADER */
.header { background:var(--surface); border-bottom:1px solid var(--border); padding:12px 24px; display:flex; align-items:center; gap:16px; flex-shrink:0; }
.header-brand { font-family:'Syne',sans-serif; font-size:20px; font-weight:800; color:var(--accent); }
.caja-status { background:rgba(34,197,94,0.1); border:1px solid rgba(34,197,94,0.2); border-radius:50px; padding:6px 14px; font-size:13px; color:var(--accent); font-weight:600; }
.header-right { margin-left:auto; display:flex; align-items:center; gap:10px; }

/* MAIN LAYOUT */
.main { flex:1; display:flex; overflow:hidden; }

/* MESAS LIST */
.mesas-list { width:240px; background:var(--surface); border-right:1px solid var(--border); overflow-y:auto; flex-shrink:0; padding:16px 12px; display:flex; flex-direction:column; gap:6px; }
.mesas-list h3 { font-family:'Syne',sans-serif; font-size:14px; font-weight:700; color:var(--muted); text-transform:uppercase; letter-spacing:1px; margin-bottom:8px; padding:0 4px; }
.mesa-item { background:var(--surface2); border:1px solid var(--border); border-radius:12px; padding:12px 14px; cursor:pointer; transition:all .2s; }
.mesa-item:hover, .mesa-item.active { border-color:var(--accent); background:rgba(34,197,94,0.08); }
.mesa-item-num { font-family:'Syne',sans-serif; font-size:18px; font-weight:700; }
.mesa-item-info { font-size:12px; color:var(--muted); margin-top:2px; }
.mesa-item-total { font-size:13px; font-weight:700; color:var(--accent); margin-top:4px; }

/* CUENTA PANEL */
.cuenta-panel { flex:1; overflow-y:auto; padding:24px; }
.cuenta-placeholder { display:flex; flex-direction:column; align-items:center; justify-content:center; height:100%; color:var(--muted); gap:16px; }
.cuenta-placeholder .emoji { font-size:64px; }
.cuenta-placeholder p { font-size:18px; font-family:'Syne',sans-serif; }

.cuenta-header { display:flex; align-items:center; gap:16px; margin-bottom:20px; }
.cuenta-title { font-family:'Syne',sans-serif; font-size:24px; font-weight:800; }
.btn-action { background:var(--surface2); border:1px solid var(--border); border-radius:10px; padding:9px 16px; color:var(--text); font-size:13px; font-weight:600; cursor:pointer; transition:all .2s; font-family:'DM Sans',sans-serif; }
.btn-action:hover { border-color:var(--accent); }

/* ITEMS TABLE */
.items-table { width:100%; border-collapse:collapse; margin-bottom:20px; }
.items-table th { text-align:left; padding:10px 14px; font-size:12px; text-transform:uppercase; letter-spacing:1px; color:var(--muted); border-bottom:1px solid var(--border); }
.items-table td { padding:12px 14px; border-bottom:1px solid rgba(255,255,255,0.04); font-size:15px; }
.items-table tr:last-child td { border-bottom:none; }
.item-estado-badge { padding:3px 10px; border-radius:50px; font-size:11px; font-weight:700; }
.badge-pendiente { background:rgba(245,158,11,0.15); color:var(--yellow); }
.badge-listo { background:rgba(34,197,94,0.15); color:var(--accent); }
.badge-entregado { background:rgba(148,163,184,0.15); color:#94a3b8; }

/* TOTALES */
.totales-box { background:var(--surface); border:1px solid var(--border); border-radius:var(--radius); padding:20px; margin-bottom:20px; }
.total-row { display:flex; justify-content:space-between; padding:8px 0; font-size:15px; border-bottom:1px solid var(--border); }
.total-row:last-child { border-bottom:none; font-family:'Syne',sans-serif; font-size:20px; font-weight:800; color:var(--accent); padding-top:14px; }
.total-row .label { color:var(--muted); }

/* PAGO SECTION */
.pago-section { background:var(--surface); border:1px solid var(--border); border-radius:var(--radius); padding:20px; }
.pago-title { font-family:'Syne',sans-serif; font-size:16px; font-weight:700; margin-bottom:16px; }
.metodos-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:8px; margin-bottom:16px; }
.metodo-btn { background:var(--surface2); border:1px solid var(--border); border-radius:12px; padding:14px 10px; text-align:center; cursor:pointer; transition:all .2s; font-family:'DM Sans',sans-serif; }
.metodo-btn:hover, .metodo-btn.active { border-color:var(--accent); background:rgba(34,197,94,0.08); }
.metodo-btn .emoji { font-size:24px; display:block; margin-bottom:6px; }
.metodo-btn .label { font-size:12px; font-weight:600; color:var(--muted); }
.metodo-btn.active .label { color:var(--text); }
.metodos-inputs { display:flex; flex-direction:column; gap:8px; margin-bottom:16px; }
.metodo-input-row { display:flex; align-items:center; gap:10px; background:var(--surface2); border-radius:10px; padding:10px 14px; }
.metodo-input-row label { font-size:13px; font-weight:600; min-width:100px; }
.metodo-input-row input { background:transparent; border:none; color:var(--text); font-size:16px; font-weight:700; font-family:'Syne',sans-serif; outline:none; flex:1; text-align:right; }
.comprobante-row { display:flex; gap:8px; margin-bottom:16px; }
.comp-btn { flex:1; background:var(--surface2); border:1px solid var(--border); border-radius:10px; padding:12px; text-align:center; cursor:pointer; transition:all .2s; font-size:13px; font-weight:600; font-family:'DM Sans',sans-serif; color:var(--muted); }
.comp-btn.active, .comp-btn:hover { border-color:var(--blue); background:rgba(59,130,246,0.08); color:var(--text); }
.btn-cobrar { width:100%; background:linear-gradient(135deg, var(--accent), #16a34a); border:none; border-radius:14px; padding:18px; color:white; font-family:'Syne',sans-serif; font-size:18px; font-weight:800; cursor:pointer; transition:all .2s; letter-spacing:0.5px; }
.btn-cobrar:hover { transform:translateY(-2px); box-shadow:0 8px 25px rgba(34,197,94,0.3); }
.btn-cobrar:disabled { opacity:0.4; transform:none; cursor:not-allowed; }

/* DESCUENTO/PROPINA */
.extras-row { display:flex; gap:10px; margin-bottom:16px; }
.extra-field { flex:1; background:var(--surface2); border:1px solid var(--border); border-radius:10px; padding:10px 14px; display:flex; align-items:center; gap:8px; }
.extra-field label { font-size:12px; color:var(--muted); font-weight:500; }
.extra-field input { background:transparent; border:none; color:var(--text); font-size:15px; font-weight:700; font-family:'Syne',sans-serif; outline:none; width:60px; text-align:right; }

/* SIDE PANEL */
.side-panel { width:320px; background:var(--surface); border-left:1px solid var(--border); overflow-y:auto; flex-shrink:0; padding:16px; display:flex; flex-direction:column; gap:12px; }

.caja-stat { background:var(--surface2); border-radius:var(--radius); padding:16px; }
.caja-stat h4 { font-size:12px; color:var(--muted); text-transform:uppercase; letter-spacing:1px; margin-bottom:8px; }
.caja-stat .value { font-family:'Syne',sans-serif; font-size:26px; font-weight:800; color:var(--accent); }

/* TOAST */
.toast { position:fixed; bottom:30px; left:50%; transform:translateX(-50%) translateY(100px); background:var(--surface); border:1px solid var(--border); border-radius:50px; padding:14px 24px; font-size:15px; font-weight:600; z-index:9999; transition:transform .3s cubic-bezier(0.34,1.56,0.64,1); box-shadow:0 10px 40px rgba(0,0,0,0.4); white-space:nowrap; }
.toast.show { transform:translateX(-50%) translateY(0); }
.toast.success { border-color:var(--accent); }
.toast.error { border-color:var(--red); }

/* MODAL TICKET */
.modal-overlay { position:fixed; inset:0; background:rgba(0,0,0,0.7); z-index:500; display:none; align-items:center; justify-content:center; backdrop-filter:blur(4px); }
.modal-overlay.active { display:flex; }
.ticket { background:white; color:#333; border-radius:12px; padding:24px; width:300px; font-family:monospace; font-size:13px; }
.ticket-header { text-align:center; margin-bottom:16px; }
.ticket-header h2 { font-size:18px; font-weight:bold; }
.ticket-divider { border-top:1px dashed #999; margin:12px 0; }
.ticket-row { display:flex; justify-content:space-between; margin:4px 0; }
.ticket-total { font-size:16px; font-weight:bold; margin-top:8px; }
.ticket-actions { display:flex; gap:8px; margin-top:16px; }
.ticket-actions button { flex:1; padding:10px; border:none; border-radius:8px; cursor:pointer; font-weight:600; font-family:'DM Sans',sans-serif; }
.btn-imprimir { background:#333; color:white; }
.btn-cerrar-ticket { background:#eee; color:#333; }
</style>
</head>
<body>
<?php
$user = currentUser();
?>

<div class="header">
  <div class="header-brand">💰 Caja</div>
  <div class="caja-status" id="cajaStatus">⏳ Verificando caja...</div>
  <div class="header-right">
    <button class="btn-action" onclick="abrirCaja()">📂 Abrir Caja</button>
    <button class="btn-action" onclick="cerrarCaja()" style="color:var(--yellow)">🔒 Cerrar Caja</button>
    <div style="background:var(--surface2);border-radius:50px;padding:8px 16px;font-size:13px;">
      👤 <?= sanitize($user['nombre']) ?>
      &nbsp;|&nbsp;
      <a href="<?= BASE_URL ?>/logout.php" style="color:var(--muted);text-decoration:none">Salir</a>
    </div>
  </div>
</div>

<div class="main">
  <!-- LISTA MESAS ACTIVAS -->
  <div class="mesas-list">
    <h3>Mesas Activas</h3>
    <div id="mesasActivas"></div>
  </div>

  <!-- CUENTA -->
  <div class="cuenta-panel" id="cuentaPanel">
    <div class="cuenta-placeholder">
      <span class="emoji">🪑</span>
      <p>Selecciona una mesa</p>
    </div>
  </div>

  <!-- STATS CAJA -->
  <div class="side-panel">
    <h3 style="font-family:'Syne',sans-serif;font-size:16px;font-weight:700;padding:4px 0">📊 Resumen del Día</h3>
    <div class="caja-stat">
      <h4>Ventas del Día</h4>
      <div class="value" id="ventasDia">S/ 0.00</div>
    </div>
    <div class="caja-stat">
      <h4>Órdenes Cobradas</h4>
      <div class="value" id="ordenesCobradas">0</div>
    </div>
    <div class="caja-stat">
      <h4>Efectivo</h4>
      <div class="value" id="totalEfectivo" style="color:var(--yellow)">S/ 0.00</div>
    </div>
    <div class="caja-stat">
      <h4>Digital (Yape/POS)</h4>
      <div class="value" id="totalDigital" style="color:var(--blue)">S/ 0.00</div>
    </div>
    <div class="caja-stat">
      <h4>Mesas en Servicio</h4>
      <div class="value" id="mesasServicio" style="color:var(--orange)">0</div>
    </div>
  </div>
</div>

<!-- MODAL TICKET -->
<div class="modal-overlay" id="modalTicket">
  <div class="modal">
    <div class="ticket" id="ticketContent"></div>
    <div style="display:flex;gap:10px;margin-top:14px;justify-content:center">
      <button onclick="imprimirTicket()" style="background:var(--surface2);border:1px solid var(--border);border-radius:10px;padding:12px 24px;color:var(--text);font-weight:600;cursor:pointer;font-family:'DM Sans',sans-serif">🖨️ Imprimir</button>
      <button onclick="document.getElementById('modalTicket').classList.remove('active')" style="background:var(--accent);border:none;border-radius:10px;padding:12px 24px;color:white;font-weight:600;cursor:pointer;font-family:'DM Sans',sans-serif">✅ Cerrar</button>
    </div>
  </div>
</div>

<!-- TOAST -->
<div class="toast" id="toast"></div>

<script>
const BASE = '<?= BASE_URL ?>';
let ordenActual = null;
let metodosPago = [];
let tipoComprobante = 'ticket';

// ============================================================
// CARGA INICIAL
// ============================================================
async function loadMesasActivas() {
  const res = await fetch(BASE + '/api/mesas.php?estado=ocupada', {credentials:'same-origin'});
  const data = await res.json();
  document.getElementById('mesasServicio').textContent = data.length;
  const el = document.getElementById('mesasActivas');
  el.innerHTML = data.map(m => `
    <div class="mesa-item" onclick="seleccionarMesa(${m.id}, ${m.id_orden || 0})">
      <div class="mesa-item-num">Mesa ${m.numero}</div>
      <div class="mesa-item-info">👥 ${m.personas || '?'} · ${m.mozo_nombre || 'Sin mozo'}</div>
      <div class="mesa-item-total">${m.total ? 'S/ ' + parseFloat(m.total).toFixed(2) : '...'}</div>
    </div>
  `).join('') || '<div style="text-align:center;color:var(--muted);font-size:13px;padding:20px">Sin mesas ocupadas</div>';
}

async function loadResumenCaja() {
  try {
    const res = await fetch(BASE + '/api/caja.php?action=resumen', {credentials:'same-origin'});
    const data = await res.json();
    document.getElementById('ventasDia').textContent = 'S/ ' + (data.total_ventas || 0).toFixed(2);
    document.getElementById('ordenesCobradas').textContent = data.ordenes_cobradas || 0;
    document.getElementById('totalEfectivo').textContent = 'S/ ' + (data.total_efectivo || 0).toFixed(2);
    document.getElementById('totalDigital').textContent = 'S/ ' + (data.total_digital || 0).toFixed(2);
    document.getElementById('cajaStatus').textContent = data.caja_abierta ? '✅ Caja Abierta' : '❌ Caja Cerrada';
  } catch(e) {}
}

// ============================================================
// SELECCIONAR MESA
// ============================================================
async function seleccionarMesa(id_mesa, id_orden) {
  document.querySelectorAll('.mesa-item').forEach(m => m.classList.remove('active'));
  event.currentTarget.classList.add('active');

  if (!id_orden) {
    showToast('Esta mesa no tiene orden activa', 'error');
    return;
  }

  const res = await fetch(BASE + '/api/ordenes.php?id=' + id_orden);
  ordenActual = await res.json();
  renderCuenta(ordenActual);
}

function renderCuenta(orden) {
  metodosPago = [{metodo:'efectivo', monto: 0}];
  const panel = document.getElementById('cuentaPanel');
  const subtotal = parseFloat(orden.subtotal) || orden.items.reduce((s,i) => s + parseFloat(i.precio_total), 0);
  const igv = subtotal * 0.18;
  const total = subtotal + igv;

  panel.innerHTML = `
    <div class="cuenta-header">
      <div class="cuenta-title">Mesa ${orden.mesa_numero} — Cuenta</div>
      <button class="btn-action" onclick="dividirCuenta()">✂️ Dividir</button>
      <button class="btn-action" onclick="imprimirPreCuenta()" style="color:var(--orange)">🧾 Pre-cuenta</button>
    </div>

    <table class="items-table">
      <thead>
        <tr>
          <th>Cant.</th>
          <th>Producto</th>
          <th>P. Unit.</th>
          <th>Total</th>
          <th>Estado</th>
        </tr>
      </thead>
      <tbody>
        ${orden.items.map(i => `
          <tr>
            <td><strong>${i.cantidad}</strong></td>
            <td>${i.nombre_plato}</td>
            <td>S/ ${parseFloat(i.precio_unitario).toFixed(2)}</td>
            <td>S/ ${parseFloat(i.precio_total).toFixed(2)}</td>
            <td><span class="item-estado-badge badge-${i.estado}">${i.estado}</span></td>
          </tr>
        `).join('')}
      </tbody>
    </table>

    <div class="totales-box">
      <div class="total-row"><span class="label">Subtotal</span><span id="showSubtotal">S/ ${subtotal.toFixed(2)}</span></div>
      <div class="extras-row">
        <div class="extra-field">
          <label>Descuento S/</label>
          <input type="number" id="descuento" value="0" min="0" oninput="recalcular(${subtotal})" placeholder="0">
        </div>
        <div class="extra-field">
          <label>Propina S/</label>
          <input type="number" id="propina" value="0" min="0" oninput="recalcular(${subtotal})" placeholder="0">
        </div>
      </div>
      <div class="total-row"><span class="label">IGV (18%)</span><span id="showIGV">S/ ${igv.toFixed(2)}</span></div>
      <div class="total-row"><span class="label">TOTAL</span><span id="showTotal">S/ ${total.toFixed(2)}</span></div>
    </div>

    <div class="pago-section">
      <div class="pago-title">Método de Pago</div>
      <div class="metodos-grid">
        <div class="metodo-btn active" onclick="selectMetodo('efectivo',this)">
          <span class="emoji">💵</span><span class="label">Efectivo</span>
        </div>
        <div class="metodo-btn" onclick="selectMetodo('yape',this)">
          <span class="emoji">📱</span><span class="label">Yape</span>
        </div>
        <div class="metodo-btn" onclick="selectMetodo('plin',this)">
          <span class="emoji">💙</span><span class="label">Plin</span>
        </div>
        <div class="metodo-btn" onclick="selectMetodo('tarjeta_credito',this)">
          <span class="emoji">💳</span><span class="label">T. Crédito</span>
        </div>
        <div class="metodo-btn" onclick="selectMetodo('tarjeta_debito',this)">
          <span class="emoji">💳</span><span class="label">T. Débito</span>
        </div>
        <div class="metodo-btn" onclick="selectMetodo('mixto',this)">
          <span class="emoji">🔀</span><span class="label">Mixto</span>
        </div>
      </div>
      <div id="metodoInputs"></div>
      <div class="comprobante-row">
        <button class="comp-btn active" onclick="setComp('ticket',this)">🎫 Ticket</button>
        <button class="comp-btn" onclick="setComp('boleta',this)">📄 Boleta</button>
        <button class="comp-btn" onclick="setComp('factura',this)">📋 Factura</button>
      </div>
      <button class="btn-cobrar" id="btnCobrar" onclick="cobrar()">
        💰 COBRAR S/ ${total.toFixed(2)}
      </button>
    </div>
  `;

  updateMetodoInput('efectivo', total);
}

let metodoSeleccionado = 'efectivo';
let totalActual = 0;

function selectMetodo(m, btn) {
  metodoSeleccionado = m;
  document.querySelectorAll('.metodo-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  updateMetodoInput(m, totalActual);
}

function updateMetodoInput(metodo, total) {
  const el = document.getElementById('metodoInputs');
  if (metodo === 'mixto') {
    el.innerHTML = `
      <div class="metodos-inputs">
        <div class="metodo-input-row">
          <label>💵 Efectivo</label>
          <input type="number" id="montoEfectivo" placeholder="0.00" value="0" oninput="calcVuelto()">
        </div>
        <div class="metodo-input-row">
          <label>📱 Yape/Plin</label>
          <input type="number" id="montoDigital" placeholder="0.00" value="${total.toFixed(2)}" oninput="calcVuelto()">
        </div>
        <div id="vueltoInfo" style="padding:10px;font-size:14px;color:var(--muted)"></div>
      </div>`;
  } else {
    el.innerHTML = `
      <div class="metodos-inputs">
        <div class="metodo-input-row">
          <label>Monto recibido</label>
          <input type="number" id="montoRecibido" placeholder="${total.toFixed(2)}" value="${total.toFixed(2)}" oninput="calcVuelto()">
        </div>
        <div id="vueltoInfo" style="padding:10px;font-size:14px;color:var(--accent)"></div>
      </div>`;
  }
  totalActual = total;
  calcVuelto();
}

function calcVuelto() {
  const el = document.getElementById('vueltoInfo');
  if (!el) return;
  if (metodoSeleccionado === 'mixto') {
    const ef = parseFloat(document.getElementById('montoEfectivo')?.value) || 0;
    const dig = parseFloat(document.getElementById('montoDigital')?.value) || 0;
    const vuelto = ef + dig - totalActual;
    el.innerHTML = vuelto >= 0 ? `✅ Vuelto: <strong>S/ ${vuelto.toFixed(2)}</strong>` : `⚠️ Falta: <strong style="color:var(--red)">S/ ${Math.abs(vuelto).toFixed(2)}</strong>`;
  } else {
    const recibido = parseFloat(document.getElementById('montoRecibido')?.value) || 0;
    const vuelto = recibido - totalActual;
    if (recibido > 0) el.innerHTML = vuelto >= 0 ? `✅ Vuelto: <strong>S/ ${vuelto.toFixed(2)}</strong>` : `⚠️ Falta: <strong style="color:var(--red)">S/ ${Math.abs(vuelto).toFixed(2)}</strong>`;
  }
}

function recalcular(baseSubtotal) {
  const desc = parseFloat(document.getElementById('descuento')?.value) || 0;
  const prop = parseFloat(document.getElementById('propina')?.value) || 0;
  const sub2 = Math.max(0, baseSubtotal - desc);
  const igv = sub2 * 0.18;
  const total = sub2 + igv + prop;
  document.getElementById('showSubtotal').textContent = 'S/ ' + sub2.toFixed(2);
  document.getElementById('showIGV').textContent = 'S/ ' + igv.toFixed(2);
  document.getElementById('showTotal').textContent = 'S/ ' + total.toFixed(2);
  document.getElementById('btnCobrar').textContent = '💰 COBRAR S/ ' + total.toFixed(2);
  updateMetodoInput(metodoSeleccionado, total);
}

function setComp(tipo, btn) {
  tipoComprobante = tipo;
  document.querySelectorAll('.comp-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
}

// ============================================================
// COBRAR
// ============================================================
async function cobrar() {
  const btn = document.getElementById('btnCobrar');
  btn.disabled = true;
  btn.textContent = '⏳ Procesando...';

  const desc = parseFloat(document.getElementById('descuento')?.value) || 0;
  const prop = parseFloat(document.getElementById('propina')?.value) || 0;
  const sub = parseFloat(document.getElementById('showSubtotal')?.textContent.replace('S/ ','')) || 0;
  const igv = parseFloat(document.getElementById('showIGV')?.textContent.replace('S/ ','')) || 0;
  const total = sub + igv + prop;

  const pagos = buildPagos(total);

  const res = await fetch(BASE + '/api/caja.php', {
    method: 'POST',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify({
      action: 'cobrar',
      id_orden: ordenActual.id,
      tipo_comprobante: tipoComprobante,
      subtotal: sub,
      descuento: desc,
      igv,
      propina: prop,
      total,
      pagos,
      id_cajero: <?= $user['id'] ?>
    })
  });

  const data = await res.json();
  if (data.ok) {
    showToast('✅ Cobro exitoso', 'success');
    mostrarTicket(ordenActual, {subtotal:sub, igv, propina:prop, descuento:desc, total, comprobante:tipoComprobante, numero:data.numero});
    loadMesasActivas();
    loadResumenCaja();
    document.getElementById('cuentaPanel').innerHTML = '<div class="cuenta-placeholder"><span class="emoji">✅</span><p>Cobro completado</p></div>';
  } else {
    showToast('❌ ' + (data.msg || 'Error'), 'error');
    btn.disabled = false;
    btn.textContent = '💰 COBRAR S/ ' + total.toFixed(2);
  }
}

function buildPagos(total) {
  if (metodoSeleccionado === 'mixto') {
    return [
      {metodo:'efectivo', monto: parseFloat(document.getElementById('montoEfectivo')?.value) || 0},
      {metodo:'yape', monto: parseFloat(document.getElementById('montoDigital')?.value) || 0},
    ].filter(p => p.monto > 0);
  }
  return [{metodo: metodoSeleccionado, monto: total}];
}

// ============================================================
// TICKET
// ============================================================
function mostrarTicket(orden, totales) {
  const tc = document.getElementById('ticketContent');
  const now = new Date();
  tc.innerHTML = `
    <div class="ticket-header">
      <h2>🍽️ RESTAURANTOS</h2>
      <div>RUC: 20XXXXXXXXX</div>
      <div>${totales.comprobante.toUpperCase()} N° ${totales.numero || 'T-001'}</div>
      <div>${now.toLocaleDateString('es-PE')} ${now.toLocaleTimeString('es-PE')}</div>
    </div>
    <div class="ticket-divider"></div>
    <div><strong>Mesa:</strong> ${orden.mesa_numero} | <strong>Mozo:</strong> ${orden.mozo_nombre || '-'}</div>
    <div class="ticket-divider"></div>
    ${orden.items.map(i => `
      <div class="ticket-row">
        <span>${i.cantidad}x ${i.nombre_plato}</span>
        <span>S/ ${parseFloat(i.precio_total).toFixed(2)}</span>
      </div>
    `).join('')}
    <div class="ticket-divider"></div>
    <div class="ticket-row"><span>Subtotal</span><span>S/ ${totales.subtotal.toFixed(2)}</span></div>
    ${totales.descuento > 0 ? `<div class="ticket-row"><span>Descuento</span><span>-S/ ${totales.descuento.toFixed(2)}</span></div>` : ''}
    ${totales.propina > 0 ? `<div class="ticket-row"><span>Propina</span><span>S/ ${totales.propina.toFixed(2)}</span></div>` : ''}
    <div class="ticket-row"><span>IGV (18%)</span><span>S/ ${totales.igv.toFixed(2)}</span></div>
    <div class="ticket-row ticket-total"><span><strong>TOTAL</strong></span><span><strong>S/ ${totales.total.toFixed(2)}</strong></span></div>
    <div class="ticket-divider"></div>
    <div style="text-align:center;font-size:12px;color:#666">¡Gracias por su visita!<br>Vuelva pronto 😊</div>
  `;
  document.getElementById('modalTicket').classList.add('active');
}

function imprimirTicket() {
  const content = document.getElementById('ticketContent').innerHTML;
  const win = window.open('','_blank','width=320,height=600');
  win.document.write('<html><head><title>Ticket</title></head><body>' + content + '<script>window.print();window.close()<\/script></body></html>');
  win.document.close();
}

function imprimirPreCuenta() {
  if (!ordenActual) return;
  mostrarTicket(ordenActual, {subtotal: ordenActual.subtotal || 0, igv: ordenActual.igv || 0, propina: 0, descuento: 0, total: ordenActual.total || 0, comprobante:'PRE-CUENTA', numero:'—'});
}

function dividirCuenta() {
  showToast('Función de dividir cuenta en desarrollo', '');
}

async function abrirCaja() {
  const monto = prompt('Monto inicial de caja:');
  if (!monto) return;
  const res = await fetch(BASE + '/api/caja.php', {
    method: 'POST',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify({action:'abrir', monto_inicial: parseFloat(monto), id_cajero: <?= $user['id'] ?>})
  });
  const data = await res.json();
  if (data.ok) { showToast('✅ Caja abierta', 'success'); loadResumenCaja(); }
}

async function cerrarCaja() {
  if (!confirm('¿Cerrar caja? Se registrará el arqueo.')) return;
  const res = await fetch(BASE + '/api/caja.php', {
    method: 'POST',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify({action:'cerrar', id_cajero: <?= $user['id'] ?>})
  });
  const data = await res.json();
  if (data.ok) { showToast('✅ Caja cerrada', 'success'); loadResumenCaja(); }
}

function showToast(msg, type='') {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.className = 'toast ' + type;
  t.classList.add('show');
  setTimeout(() => t.classList.remove('show'), 3000);
}

// INIT
loadMesasActivas();
loadResumenCaja();
setInterval(() => { loadMesasActivas(); loadResumenCaja(); }, 15000);
</script>
</body>
</html>
