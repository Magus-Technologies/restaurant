<?php
// Auth guard — debe estar ANTES de cualquier output HTML
require_once __DIR__ . '/../../includes/functions.php';
requireLogin(['mozo','administrador','supervisor']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
<title>Mozos — RestaurantOS</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<style>
/* ===== VARIABLES ===== */
:root {
  --bg: #0f0f14;
  --surface: #1a1a24;
  --surface2: #252535;
  --surface3: #2f2f42;
  --accent: #ff6b35;
  --accent2: #ffaa00;
  --green: #22c55e;
  --yellow: #f59e0b;
  --red: #ef4444;
  --blue: #3b82f6;
  --purple: #a855f7;
  --text: #f0f0f8;
  --muted: #6b6b88;
  --border: rgba(255,255,255,0.08);
  --radius: 18px;
  --radius-sm: 12px;
}
* { margin:0; padding:0; box-sizing:border-box; -webkit-tap-highlight-color: transparent; }
html,body { height:100%; overflow:hidden; }
body {
  font-family: 'DM Sans', sans-serif;
  background: var(--bg);
  color: var(--text);
  font-size: 16px;
}

/* ===== LAYOUT ===== */
.app { display:flex; flex-direction:column; height:100vh; }

/* ===== HEADER ===== */
.header {
  background: var(--surface);
  border-bottom: 1px solid var(--border);
  padding: 12px 20px;
  display: flex;
  align-items: center;
  gap: 16px;
  flex-shrink: 0;
  z-index: 100;
}
.header-brand {
  font-family: 'Syne', sans-serif;
  font-size: 20px;
  font-weight: 800;
  color: var(--accent);
}
.header-user {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-left: auto;
  background: var(--surface2);
  border-radius: 50px;
  padding: 8px 16px;
  font-size: 14px;
  font-weight: 500;
}
.notification-bell {
  background: var(--surface2);
  border-radius: 50%;
  width: 44px; height: 44px;
  display: flex; align-items: center; justify-content: center;
  cursor: pointer;
  font-size: 20px;
  position: relative;
  border: 1px solid var(--border);
}
.notif-badge {
  position: absolute;
  top: 4px; right: 4px;
  background: var(--red);
  border-radius: 50%;
  width: 16px; height: 16px;
  font-size: 10px;
  font-weight: 700;
  display: flex; align-items: center; justify-content: center;
  display: none;
}

/* ===== MAIN CONTENT ===== */
.main { flex:1; display:flex; overflow:hidden; }

/* ===== MESAS PANEL ===== */
.mesas-panel {
  width: 100%;
  padding: 20px;
  overflow-y: auto;
  transition: all .3s;
}
.panel-title {
  font-family: 'Syne', sans-serif;
  font-size: 22px;
  font-weight: 700;
  margin-bottom: 16px;
  display: flex;
  align-items: center;
  gap: 10px;
}
.zona-filter {
  display: flex;
  gap: 8px;
  margin-bottom: 20px;
  flex-wrap: wrap;
}
.zona-btn {
  background: var(--surface2);
  border: 1px solid var(--border);
  border-radius: 50px;
  padding: 8px 18px;
  color: var(--muted);
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: all .2s;
  font-family: 'DM Sans', sans-serif;
}
.zona-btn.active, .zona-btn:hover {
  background: var(--accent);
  border-color: var(--accent);
  color: white;
}
.mesas-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
  gap: 14px;
}
.mesa-card {
  background: var(--surface);
  border: 2px solid var(--border);
  border-radius: var(--radius);
  padding: 20px 16px;
  cursor: pointer;
  transition: all .2s;
  text-align: center;
  position: relative;
  overflow: hidden;
  min-height: 140px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 8px;
  user-select: none;
  -webkit-user-select: none;
}
.mesa-card:active { transform: scale(0.96); }
.mesa-card.libre { border-color: rgba(34,197,94,0.3); }
.mesa-card.libre:hover { border-color: var(--green); background: rgba(34,197,94,0.08); }
.mesa-card.ocupada { border-color: rgba(239,68,68,0.4); background: rgba(239,68,68,0.06); }
.mesa-card.reservada { border-color: rgba(245,158,11,0.4); background: rgba(245,158,11,0.06); }
.mesa-card.por_limpiar { border-color: rgba(168,85,247,0.4); background: rgba(168,85,247,0.06); }
.mesa-numero {
  font-family: 'Syne', sans-serif;
  font-size: 32px;
  font-weight: 800;
  line-height: 1;
}
.mesa-estado {
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 1px;
  padding: 4px 10px;
  border-radius: 50px;
}
.libre .mesa-estado { background: rgba(34,197,94,0.15); color: var(--green); }
.ocupada .mesa-estado { background: rgba(239,68,68,0.15); color: var(--red); }
.reservada .mesa-estado { background: rgba(245,158,11,0.15); color: var(--yellow); }
.por_limpiar .mesa-estado { background: rgba(168,85,247,0.15); color: var(--purple); }
.mesa-info { font-size: 12px; color: var(--muted); }
.mesa-mozo { font-size: 11px; color: var(--muted); }
.mesa-badge {
  position: absolute;
  top: 8px; right: 8px;
  background: var(--accent);
  color: white;
  border-radius: 50%;
  width: 22px; height: 22px;
  font-size: 11px;
  font-weight: 700;
  display: flex; align-items: center; justify-content: center;
}

/* ===== PEDIDO PANEL ===== */
.pedido-panel {
  position: fixed;
  top: 0; right: 0;
  width: 100%; height: 100%;
  background: var(--bg);
  z-index: 200;
  display: none;
  flex-direction: column;
}
.pedido-panel.active { display: flex; }
.pedido-header {
  background: var(--surface);
  border-bottom: 1px solid var(--border);
  padding: 14px 20px;
  display: flex;
  align-items: center;
  gap: 14px;
  flex-shrink: 0;
}
.btn-back {
  background: var(--surface2);
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 10px 18px;
  color: var(--text);
  font-size: 16px;
  cursor: pointer;
  font-family: 'DM Sans', sans-serif;
  font-weight: 600;
  display: flex; align-items: center; gap: 6px;
  transition: all .2s;
}
.btn-back:hover { background: var(--surface3); }
.pedido-title {
  font-family: 'Syne', sans-serif;
  font-size: 20px;
  font-weight: 700;
}
.pedido-body {
  flex: 1;
  display: flex;
  overflow: hidden;
}
.categorias-col {
  width: 130px;
  background: var(--surface);
  border-right: 1px solid var(--border);
  overflow-y: auto;
  flex-shrink: 0;
  padding: 10px 8px;
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.cat-btn {
  background: transparent;
  border: 1px solid transparent;
  border-radius: var(--radius-sm);
  padding: 12px 8px;
  color: var(--muted);
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  text-align: center;
  transition: all .2s;
  font-family: 'DM Sans', sans-serif;
  line-height: 1.3;
}
.cat-btn .cat-icon { font-size: 22px; display: block; margin-bottom: 4px; }
.cat-btn:hover, .cat-btn.active {
  background: var(--surface2);
  border-color: var(--accent);
  color: var(--text);
}
.platos-col {
  flex: 1;
  overflow-y: auto;
  padding: 14px;
}
.platos-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
  gap: 10px;
}
.plato-btn {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  padding: 16px 12px;
  cursor: pointer;
  text-align: center;
  transition: all .2s;
  font-family: 'DM Sans', sans-serif;
  position: relative;
  min-height: 100px;
  display: flex; flex-direction: column;
  align-items: center; justify-content: center;
  gap: 6px;
}
.plato-btn:active { transform: scale(0.95); }
.plato-btn:hover { border-color: var(--accent); background: rgba(255,107,53,0.08); }
.plato-btn.no-disponible { opacity: 0.4; pointer-events: none; }
.plato-nombre {
  font-size: 13px;
  font-weight: 600;
  color: var(--text);
  line-height: 1.3;
}
.plato-precio {
  font-size: 14px;
  font-weight: 700;
  color: var(--accent);
}
.plato-area {
  font-size: 10px;
  color: var(--muted);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

/* ===== ORDEN LATERAL ===== */
.orden-col {
  width: 300px;
  background: var(--surface);
  border-left: 1px solid var(--border);
  display: flex;
  flex-direction: column;
  flex-shrink: 0;
}
.orden-header {
  padding: 14px 16px;
  border-bottom: 1px solid var(--border);
  font-family: 'Syne', sans-serif;
  font-size: 16px;
  font-weight: 700;
}
.orden-items {
  flex: 1;
  overflow-y: auto;
  padding: 10px;
}
.orden-item {
  background: var(--surface2);
  border-radius: var(--radius-sm);
  padding: 12px;
  margin-bottom: 8px;
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.orden-item-top {
  display: flex;
  align-items: center;
  gap: 8px;
}
.item-qty-ctrl {
  display: flex;
  align-items: center;
  gap: 6px;
}
.qty-btn {
  background: var(--surface3);
  border: none;
  border-radius: 8px;
  width: 30px; height: 30px;
  color: var(--text);
  font-size: 18px;
  cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  transition: background .15s;
  font-family: 'DM Sans', sans-serif;
}
.qty-btn:hover { background: var(--accent); }
.qty-val {
  font-weight: 700;
  font-size: 16px;
  min-width: 24px;
  text-align: center;
}
.item-nombre { flex: 1; font-size: 14px; font-weight: 500; }
.item-price { font-size: 14px; font-weight: 700; color: var(--accent); white-space: nowrap; }
.item-obs {
  font-size: 12px;
  color: var(--muted);
  background: var(--surface);
  border-radius: 6px;
  padding: 4px 8px;
  cursor: pointer;
}
.item-del {
  background: rgba(239,68,68,0.15);
  border: none;
  border-radius: 8px;
  width: 30px; height: 30px;
  color: var(--red);
  cursor: pointer;
  font-size: 14px;
  display: flex; align-items: center; justify-content: center;
}

.orden-footer {
  padding: 14px;
  border-top: 1px solid var(--border);
}
.orden-total {
  display: flex;
  justify-content: space-between;
  font-family: 'Syne', sans-serif;
  font-size: 18px;
  font-weight: 700;
  margin-bottom: 12px;
}
.btn-enviar {
  width: 100%;
  background: linear-gradient(135deg, var(--green), #16a34a);
  border: none;
  border-radius: var(--radius-sm);
  padding: 16px;
  color: white;
  font-family: 'Syne', sans-serif;
  font-size: 16px;
  font-weight: 700;
  cursor: pointer;
  transition: all .2s;
  letter-spacing: 0.5px;
}
.btn-enviar:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(34,197,94,0.3); }
.btn-enviar:disabled { opacity: 0.5; transform: none; cursor: not-allowed; }

/* ===== MODAL ===== */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.7);
  z-index: 500;
  display: none;
  align-items: center;
  justify-content: center;
  backdrop-filter: blur(4px);
}
.modal-overlay.active { display: flex; }
.modal {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 24px;
  padding: 30px;
  width: 90%;
  max-width: 460px;
  max-height: 90vh;
  overflow-y: auto;
}
.modal h3 {
  font-family: 'Syne', sans-serif;
  font-size: 20px;
  font-weight: 700;
  margin-bottom: 20px;
}
.modal-input {
  width: 100%;
  background: var(--surface2);
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 14px;
  color: var(--text);
  font-size: 16px;
  font-family: 'DM Sans', sans-serif;
  outline: none;
  margin-bottom: 12px;
}
.modal-input:focus { border-color: var(--accent); }
.obs-chips { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 12px; }
.obs-chip {
  background: var(--surface2);
  border: 1px solid var(--border);
  border-radius: 50px;
  padding: 6px 14px;
  font-size: 13px;
  cursor: pointer;
  transition: all .2s;
  font-family: 'DM Sans', sans-serif;
  color: var(--text);
}
.obs-chip.selected, .obs-chip:hover { background: var(--accent); border-color: var(--accent); color: white; }
.modal-btns { display: flex; gap: 10px; margin-top: 16px; }
.btn-modal {
  flex: 1;
  border-radius: 12px;
  padding: 14px;
  font-size: 15px;
  font-weight: 600;
  cursor: pointer;
  transition: all .2s;
  font-family: 'Syne', sans-serif;
  border: none;
}
.btn-cancel { background: var(--surface2); color: var(--text); border: 1px solid var(--border); }
.btn-ok { background: var(--accent); color: white; }
.btn-ok:hover { background: #e55a28; }

/* ===== TOAST ===== */
.toast {
  position: fixed;
  bottom: 30px;
  left: 50%;
  transform: translateX(-50%) translateY(100px);
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 50px;
  padding: 14px 24px;
  font-size: 15px;
  font-weight: 600;
  z-index: 9999;
  transition: transform .3s cubic-bezier(0.34, 1.56, 0.64, 1);
  display: flex; align-items: center; gap: 10px;
  box-shadow: 0 10px 40px rgba(0,0,0,0.4);
  white-space: nowrap;
}
.toast.show { transform: translateX(-50%) translateY(0); }
.toast.success { border-color: var(--green); }
.toast.error { border-color: var(--red); }

/* ===== NOTIFICATIONS PANEL ===== */
.notif-panel {
  position: fixed;
  top: 70px; right: 16px;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  width: 300px;
  max-height: 400px;
  overflow-y: auto;
  z-index: 300;
  display: none;
  box-shadow: 0 20px 60px rgba(0,0,0,0.5);
}
.notif-panel.active { display: block; }
.notif-item {
  padding: 14px 16px;
  border-bottom: 1px solid var(--border);
  font-size: 14px;
}
.notif-item:last-child { border-bottom: none; }
.notif-item .notif-time { font-size: 11px; color: var(--muted); margin-top: 4px; }

/* ===== RESPONSIVE ===== */
@media (max-width: 600px) {
  .orden-col { width: 100%; position: fixed; bottom: 0; left: 0; right: 0; height: 50vh; z-index: 150; border-left: none; border-top: 1px solid var(--border); }
  .pedido-body { flex-direction: column; }
  .platos-col { padding-bottom: 56vh; }
  .categorias-col { width: 100%; flex-direction: row; overflow-x: auto; border-right: none; border-bottom: 1px solid var(--border); padding: 8px; flex-shrink: 0; height: auto; }
  .cat-btn { flex-shrink: 0; }
}
</style>
</head>
<body>
<?php
$user = currentUser();
?>

<div class="app">
  <!-- HEADER -->
  <div class="header">
    <span class="header-brand">🍽️ Mozos</span>
    <div class="notification-bell" onclick="toggleNotifPanel()" id="notifBtn">
      🔔
      <div class="notif-badge" id="notifBadge"></div>
    </div>
    <div class="header-user">
      👤 <?= sanitize($user['nombre']) ?>
      &nbsp;|&nbsp;
      <a href="<?= BASE_URL ?>/logout.php" style="color:var(--muted);text-decoration:none;font-size:13px;">Salir</a>
    </div>
  </div>

  <!-- MESAS -->
  <div class="main">
    <div class="mesas-panel" id="mesasPanel">
      <div class="panel-title">🪑 Mesas</div>
      <div class="zona-filter" id="zonaFilter">
        <button class="zona-btn active" onclick="filterZona('todas',this)">Todas</button>
      </div>
      <div class="mesas-grid" id="mesasGrid">
        <!-- Se carga via JS -->
      </div>
    </div>
  </div>

  <!-- PANEL PEDIDO -->
  <div class="pedido-panel" id="pedidoPanel">
    <div class="pedido-header">
      <button class="btn-back" onclick="closePedido()">← Volver</button>
      <div class="pedido-title" id="pedidoTitle">Mesa 01</div>
      <div style="margin-left:auto;display:flex;gap:8px">
        <button class="btn-back" onclick="openObs()" style="font-size:13px">📝 Obs. Mesa</button>
        <button class="btn-back" onclick="abrirPreCuenta()" style="font-size:13px;color:var(--accent)">🧾 Pre-cuenta</button>
      </div>
    </div>
    <div class="pedido-body">
      <!-- Categorías -->
      <div class="categorias-col" id="categoriasCol"></div>

      <!-- Platos -->
      <div class="platos-col">
        <div class="platos-grid" id="platosGrid"></div>
      </div>

      <!-- Orden -->
      <div class="orden-col">
        <div class="orden-header">🛒 Pedido</div>
        <div class="orden-items" id="ordenItems">
          <div style="text-align:center;color:var(--muted);padding:30px;font-size:14px">
            Sin productos aún
          </div>
        </div>
        <div class="orden-footer">
          <div class="orden-total">
            <span>Total</span>
            <span id="ordenTotal">S/ 0.00</span>
          </div>
          <button class="btn-enviar" id="btnEnviar" onclick="enviarPedido()">
            🚀 Enviar a Cocina
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- MODAL OBS ITEM -->
<div class="modal-overlay" id="modalObs">
  <div class="modal">
    <h3>✏️ Personalizar plato</h3>
    <div class="obs-chips" id="obsChips">
      <button class="obs-chip" onclick="toggleChip(this)">Sin cebolla</button>
      <button class="obs-chip" onclick="toggleChip(this)">Sin ají</button>
      <button class="obs-chip" onclick="toggleChip(this)">Extra papas</button>
      <button class="obs-chip" onclick="toggleChip(this)">Poco picante</button>
      <button class="obs-chip" onclick="toggleChip(this)">Muy picante</button>
      <button class="obs-chip" onclick="toggleChip(this)">Sin sal</button>
      <button class="obs-chip" onclick="toggleChip(this)">Para diabético</button>
      <button class="obs-chip" onclick="toggleChip(this)">Extra arroz</button>
    </div>
    <input class="modal-input" id="obsInput" placeholder="Observación personalizada..." />
    <div style="margin-bottom:12px">
      <label style="color:var(--muted);font-size:12px;text-transform:uppercase;letter-spacing:1px;display:block;margin-bottom:8px">Prioridad</label>
      <div style="display:flex;gap:8px">
        <button class="obs-chip" id="priNormal" onclick="setPri('normal')" style="background:var(--surface3)">Normal</button>
        <button class="obs-chip" id="priAlta" onclick="setPri('alta')">⚡ Alta</button>
        <button class="obs-chip" id="priUrgente" onclick="setPri('urgente')">🔴 Urgente</button>
      </div>
    </div>
    <div class="modal-btns">
      <button class="btn-modal btn-cancel" onclick="closeObs()">Cancelar</button>
      <button class="btn-modal btn-ok" onclick="saveObs()">Guardar</button>
    </div>
  </div>
</div>

<!-- MODAL ABRIR MESA -->
<div class="modal-overlay" id="modalAbrirMesa">
  <div class="modal">
    <h3>🪑 Abrir Mesa</h3>
    <input class="modal-input" id="personasInput" type="number" min="1" max="20" placeholder="Número de personas" />
    <input class="modal-input" id="clienteNombre" placeholder="Nombre del cliente (opcional)" />
    <div class="modal-btns">
      <button class="btn-modal btn-cancel" onclick="document.getElementById('modalAbrirMesa').classList.remove('active')">Cancelar</button>
      <button class="btn-modal btn-ok" onclick="confirmarAbrirMesa()">Abrir Mesa</button>
    </div>
  </div>
</div>

<!-- NOTIFICATIONS PANEL -->
<div class="notif-panel" id="notifPanel"></div>

<!-- TOAST -->
<div class="toast" id="toast">✅ Acción completada</div>

<script>
const BASE = '<?= BASE_URL ?>';
let mesaActual = null;
let ordenActual = [];
let currentCatId = null;
let categorias = [];
let platos = [];
let obsItemIdx = null;
let prioridad = 'normal';
let zonas = new Set();

// ============================================================
// CARGA DE MESAS
// ============================================================
async function loadMesas() {
  const res = await fetch(BASE + '/api/mesas.php', {credentials:'same-origin'});
  const data = await res.json();
  zonas = new Set(['todas', ...data.map(m => m.zona)]);
  renderZonas();
  renderMesas(data, 'todas');
}

function renderZonas() {
  const el = document.getElementById('zonaFilter');
  el.innerHTML = '';
  zonas.forEach(z => {
    const btn = document.createElement('button');
    btn.className = 'zona-btn' + (z === 'todas' ? ' active' : '');
    btn.textContent = z === 'todas' ? 'Todas' : z;
    btn.onclick = () => filterZona(z, btn);
    el.appendChild(btn);
  });
}

let allMesas = [];
function renderMesas(mesas, filtro) {
  allMesas = mesas;
  const grid = document.getElementById('mesasGrid');
  const filtered = filtro === 'todas' ? mesas : mesas.filter(m => m.zona === filtro);
  grid.innerHTML = filtered.map(m => `
    <div class="mesa-card ${m.estado}" onclick="clickMesa(${m.id}, '${m.estado}')">
      <div class="mesa-numero">${m.numero}</div>
      <div class="mesa-estado">${estadoLabel(m.estado)}</div>
      <div class="mesa-info">👥 ${m.capacidad} personas</div>
      ${m.id_mozo ? `<div class="mesa-mozo">👤 ${m.mozo_nombre || ''}</div>` : ''}
      ${m.estado === 'ocupada' && m.orden_items > 0 ? `<div class="mesa-badge">${m.orden_items}</div>` : ''}
    </div>
  `).join('');
}

function estadoLabel(e) {
  return {libre:'Libre',ocupada:'Ocupada',reservada:'Reservada',por_limpiar:'Por Limpiar',cerrada:'Cerrada'}[e] || e;
}

function filterZona(zona, btn) {
  document.querySelectorAll('.zona-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  renderMesas(allMesas, zona);
}

function clickMesa(id, estado) {
  const mesa = allMesas.find(m => m.id == id);
  mesaActual = mesa;
  if (estado === 'libre') {
    document.getElementById('personasInput').value = '';
    document.getElementById('clienteNombre').value = '';
    document.getElementById('modalAbrirMesa').classList.add('active');
  } else if (estado === 'ocupada') {
    openPedido(mesa);
  } else if (estado === 'por_limpiar') {
    if (confirm('¿Marcar mesa como libre?')) liberarMesa(id);
  }
}

async function confirmarAbrirMesa() {
  const personas = document.getElementById('personasInput').value || 1;
  const cliente = document.getElementById('clienteNombre').value;
  const res = await fetch(BASE + '/api/ordenes.php', {
    method: 'POST',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify({
      action: 'abrir',
      id_mesa: mesaActual.id,
      personas,
      nombre_cliente: cliente,
      id_mozo: <?= $user['id'] ?>
    })
  });
  const data = await res.json();
  if (data.ok) {
    document.getElementById('modalAbrirMesa').classList.remove('active');
    mesaActual.estado = 'ocupada';
    mesaActual.id_orden = data.id_orden;
    showToast('✅ Mesa abierta', 'success');
    openPedido(mesaActual);
    loadMesas();
  } else {
    showToast('❌ ' + data.msg, 'error');
  }
}

async function liberarMesa(id) {
  await fetch(BASE + '/api/mesas.php', {
    method: 'POST',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify({action:'liberar', id})
  });
  loadMesas();
  showToast('✅ Mesa liberada', 'success');
}

// ============================================================
// PANEL PEDIDO
// ============================================================
async function openPedido(mesa) {
  document.getElementById('pedidoTitle').textContent = `Mesa ${mesa.numero}`;
  document.getElementById('pedidoPanel').classList.add('active');
  ordenActual = [];
  await loadCategorias();
  // Si hay orden activa, cargar items existentes
  if (mesa.id_orden) await loadOrdenExistente(mesa.id_orden);
}

function closePedido() {
  document.getElementById('pedidoPanel').classList.remove('active');
  mesaActual = null;
  ordenActual = [];
  loadMesas();
}

async function loadCategorias() {
  const res = await fetch(BASE + '/api/categorias.php', {credentials:'same-origin'});
  categorias = await res.json();
  const col = document.getElementById('categoriasCol');
  col.innerHTML = categorias.map(c => `
    <button class="cat-btn" onclick="selectCat(${c.id}, this)">
      <span class="cat-icon">${c.icono || '🍽️'}</span>
      ${c.nombre}
    </button>
  `).join('');
  if (categorias.length > 0) {
    const firstBtn = col.querySelector('.cat-btn');
    selectCat(categorias[0].id, firstBtn);
  }
}

async function selectCat(id, btn) {
  currentCatId = id;
  document.querySelectorAll('.cat-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  const res = await fetch(BASE + '/api/platos.php?categoria=' + id);
  platos = await res.json();
  renderPlatos(platos);
}

function renderPlatos(platos) {
  document.getElementById('platosGrid').innerHTML = platos.map(p => `
    <div class="plato-btn ${p.disponible ? '' : 'no-disponible'}" onclick="addPlato(${p.id})">
      <div class="plato-nombre">${p.nombre}</div>
      <div class="plato-precio">S/ ${parseFloat(p.precio).toFixed(2)}</div>
      <div class="plato-area">${areaLabel(p.area)}</div>
    </div>
  `).join('');
}

function areaLabel(a) {
  return {cocina_caliente:'🔥 Cocina',cocina_fria:'❄️ Frío',bar:'🍹 Bar',postres:'🍰 Postre',general:'🍽️'}[a] || a;
}

function addPlato(id) {
  const plato = platos.find(p => p.id == id);
  if (!plato) return;
  const existing = ordenActual.findIndex(i => i.id_plato == id && !i.obs);
  if (existing >= 0) {
    ordenActual[existing].cantidad++;
    ordenActual[existing].total = ordenActual[existing].cantidad * parseFloat(plato.precio);
  } else {
    ordenActual.push({
      id_plato: id,
      nombre: plato.nombre,
      precio: parseFloat(plato.precio),
      cantidad: 1,
      total: parseFloat(plato.precio),
      obs: '',
      prioridad: 'normal',
      area: plato.area
    });
  }
  renderOrden();
  // Feedback háptico
  if (navigator.vibrate) navigator.vibrate(30);
}

function renderOrden() {
  const el = document.getElementById('ordenItems');
  if (ordenActual.length === 0) {
    el.innerHTML = '<div style="text-align:center;color:var(--muted);padding:30px;font-size:14px">Sin productos aún</div>';
  } else {
    el.innerHTML = ordenActual.map((item, i) => `
      <div class="orden-item">
        <div class="orden-item-top">
          <div class="item-qty-ctrl">
            <button class="qty-btn" onclick="changeQty(${i},-1)">−</button>
            <div class="qty-val">${item.cantidad}</div>
            <button class="qty-btn" onclick="changeQty(${i},1)">+</button>
          </div>
          <div class="item-nombre">${item.nombre}</div>
          <div class="item-price">S/${item.total.toFixed(2)}</div>
          <button class="item-del" onclick="removeItem(${i})">🗑</button>
        </div>
        ${item.obs ? `<div class="item-obs" onclick="openItemObs(${i})">📝 ${item.obs}</div>` : 
          `<div class="item-obs" onclick="openItemObs(${i})" style="opacity:0.4">+ Agregar observación</div>`}
        ${item.prioridad !== 'normal' ? `<span style="font-size:11px;color:var(--yellow)">⚡ ${item.prioridad.toUpperCase()}</span>` : ''}
      </div>
    `).join('');
  }
  const total = ordenActual.reduce((s, i) => s + i.total, 0);
  document.getElementById('ordenTotal').textContent = 'S/ ' + total.toFixed(2);
  document.getElementById('btnEnviar').disabled = ordenActual.length === 0;
}

function changeQty(idx, delta) {
  ordenActual[idx].cantidad = Math.max(1, ordenActual[idx].cantidad + delta);
  ordenActual[idx].total = ordenActual[idx].cantidad * ordenActual[idx].precio;
  renderOrden();
}

function removeItem(idx) {
  ordenActual.splice(idx, 1);
  renderOrden();
}

// ============================================================
// OBSERVACIONES
// ============================================================
function openItemObs(idx) {
  obsItemIdx = idx;
  const item = ordenActual[idx];
  document.getElementById('obsInput').value = item.obs || '';
  prioridad = item.prioridad || 'normal';
  document.querySelectorAll('.obs-chip').forEach(c => c.classList.remove('selected'));
  document.getElementById('modalObs').classList.add('active');
}

function closeObs() {
  document.getElementById('modalObs').classList.remove('active');
  obsItemIdx = null;
}

function toggleChip(btn) { btn.classList.toggle('selected'); }

function setPri(p) {
  prioridad = p;
  ['priNormal','priAlta','priUrgente'].forEach(id => document.getElementById(id).classList.remove('selected'));
  document.getElementById('pri' + p.charAt(0).toUpperCase() + p.slice(1)).classList.add('selected');
}

function saveObs() {
  if (obsItemIdx === null) return;
  const chips = [...document.querySelectorAll('#obsChips .obs-chip.selected')].map(c => c.textContent).join(', ');
  const custom = document.getElementById('obsInput').value.trim();
  ordenActual[obsItemIdx].obs = [chips, custom].filter(Boolean).join('. ');
  ordenActual[obsItemIdx].prioridad = prioridad;
  closeObs();
  renderOrden();
}

function openObs() {
  obsItemIdx = null;
}

// ============================================================
// ENVIAR PEDIDO
// ============================================================
async function enviarPedido() {
  if (ordenActual.length === 0) return;
  document.getElementById('btnEnviar').disabled = true;
  document.getElementById('btnEnviar').textContent = '⏳ Enviando...';
  try {
    const res = await fetch(BASE + '/api/ordenes.php', {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({
        action: 'agregar_items',
        id_mesa: mesaActual.id,
        id_orden: mesaActual.id_orden,
        id_mozo: <?= $user['id'] ?>,
        items: ordenActual
      })
    });
    const data = await res.json();
    if (data.ok) {
      showToast('✅ Pedido enviado a cocina', 'success');
      mesaActual.id_orden = data.id_orden;
      ordenActual = [];
      renderOrden();
    } else {
      showToast('❌ ' + (data.msg || 'Error'), 'error');
    }
  } catch(e) {
    showToast('❌ Error de conexión', 'error');
  }
  document.getElementById('btnEnviar').disabled = false;
  document.getElementById('btnEnviar').textContent = '🚀 Enviar a Cocina';
}

async function loadOrdenExistente(id_orden) {
  // Solo cargar items ya en preparación para mostrar referencia
}

async function abrirPreCuenta() {
  if (!mesaActual || !mesaActual.id_orden) return showToast('No hay orden activa', 'error');
  window.open(BASE + '/modules/caja/?precuenta=1&id_orden=' + mesaActual.id_orden, '_blank');
}

// ============================================================
// NOTIFICACIONES
// ============================================================
let notificaciones = [];

async function loadNotificaciones() {
  try {
    const res = await fetch(BASE + '/api/notificaciones.php?id_mozo=<?= $user['id'] ?>');
    const data = await res.json();
    notificaciones = data;
    const badge = document.getElementById('notifBadge');
    const nuevas = data.filter(n => !n.leida).length;
    if (nuevas > 0) {
      badge.textContent = nuevas;
      badge.style.display = 'flex';
      // Alerta de plato listo
      data.filter(n => !n.leida && n.tipo === 'plato_listo').forEach(n => {
        showToast('🍽️ ' + n.mensaje, 'success');
      });
    } else {
      badge.style.display = 'none';
    }
    renderNotifPanel(data);
  } catch(e) {}
}

function toggleNotifPanel() {
  document.getElementById('notifPanel').classList.toggle('active');
}

function renderNotifPanel(data) {
  const el = document.getElementById('notifPanel');
  if (data.length === 0) {
    el.innerHTML = '<div style="padding:16px;text-align:center;color:var(--muted)">Sin notificaciones</div>';
    return;
  }
  el.innerHTML = data.map(n => `
    <div class="notif-item" style="${!n.leida ? 'border-left:3px solid var(--accent)' : ''}">
      <strong>${n.titulo}</strong><br>${n.mensaje}
      <div class="notif-time">${n.created_at}</div>
    </div>
  `).join('');
}

// ============================================================
// TOAST
// ============================================================
function showToast(msg, type = '') {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.className = 'toast ' + type;
  t.classList.add('show');
  setTimeout(() => t.classList.remove('show'), 3000);
}

// ============================================================
// INIT
// ============================================================
loadMesas();
loadNotificaciones();
// Polling cada 10 seg para notificaciones y actualización de mesas
setInterval(() => {
  loadNotificaciones();
  if (!document.getElementById('pedidoPanel').classList.contains('active')) {
    loadMesas();
  }
}, 10000);

// Cerrar notif panel al click fuera
document.addEventListener('click', e => {
  if (!document.getElementById('notifBtn').contains(e.target) &&
      !document.getElementById('notifPanel').contains(e.target)) {
    document.getElementById('notifPanel').classList.remove('active');
  }
});
</script>
</body>
</html>
