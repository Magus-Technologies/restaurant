<?php
// Auth guard — debe estar ANTES de cualquier output HTML
require_once __DIR__ . '/../../includes/functions.php';
requireLogin(['cocina','bar','administrador','supervisor']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Cocina / KDS — RestaurantOS</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<style>
:root {
  --bg: #060609;
  --surface: #0f0f16;
  --surface2: #18181f;
  --border: rgba(255,255,255,0.07);
  --text: #f0f0f8;
  --muted: #5a5a72;
  --green: #22c55e;
  --yellow: #f59e0b;
  --red: #ef4444;
  --orange: #ff6b35;
  --blue: #3b82f6;
  --purple: #a855f7;
  --radius: 20px;
}
* { margin:0; padding:0; box-sizing:border-box; }
body {
  font-family: 'DM Sans', sans-serif;
  background: var(--bg);
  color: var(--text);
  height: 100vh;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

/* ===== HEADER ===== */
.kds-header {
  background: var(--surface);
  border-bottom: 1px solid var(--border);
  padding: 12px 24px;
  display: flex;
  align-items: center;
  gap: 20px;
  flex-shrink: 0;
}
.kds-brand {
  font-family: 'Syne', sans-serif;
  font-size: 22px;
  font-weight: 800;
  color: var(--orange);
}
.kds-clock {
  font-family: 'Syne', sans-serif;
  font-size: 28px;
  font-weight: 700;
  margin-left: auto;
  letter-spacing: 2px;
}
.area-tabs {
  display: flex;
  gap: 8px;
}
.area-tab {
  background: var(--surface2);
  border: 1px solid var(--border);
  border-radius: 50px;
  padding: 8px 20px;
  color: var(--muted);
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all .2s;
  font-family: 'DM Sans', sans-serif;
}
.area-tab.active { background: var(--orange); border-color: var(--orange); color: white; }
.area-tab:hover:not(.active) { border-color: var(--orange); color: var(--text); }

/* ===== STATS BAR ===== */
.stats-bar {
  display: flex;
  gap: 16px;
  padding: 10px 24px;
  background: var(--surface);
  border-bottom: 1px solid var(--border);
  flex-shrink: 0;
}
.stat {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  color: var(--muted);
  font-weight: 500;
}
.stat .num {
  font-family: 'Syne', sans-serif;
  font-size: 20px;
  font-weight: 800;
  color: var(--text);
}
.stat.pending .num { color: var(--yellow); }
.stat.prep .num { color: var(--blue); }
.stat.ready .num { color: var(--green); }

/* ===== PEDIDOS GRID ===== */
.pedidos-container {
  flex: 1;
  overflow-y: auto;
  padding: 20px 24px;
}
.pedidos-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 16px;
}
.pedido-card {
  background: var(--surface);
  border: 2px solid var(--border);
  border-radius: var(--radius);
  overflow: hidden;
  transition: border-color .3s;
  display: flex;
  flex-direction: column;
}
.pedido-card.verde { border-color: rgba(34,197,94,0.3); }
.pedido-card.amarillo { border-color: rgba(245,158,11,0.5); }
.pedido-card.rojo { border-color: rgba(239,68,68,0.6); animation: alertPulse 1.5s infinite; }

@keyframes alertPulse {
  0%, 100% { border-color: rgba(239,68,68,0.6); box-shadow: 0 0 0 0 rgba(239,68,68,0); }
  50% { border-color: rgba(239,68,68,1); box-shadow: 0 0 0 4px rgba(239,68,68,0.15); }
}

.card-header {
  padding: 14px 16px;
  display: flex;
  align-items: center;
  gap: 10px;
}
.card-header.verde { background: rgba(34,197,94,0.08); }
.card-header.amarillo { background: rgba(245,158,11,0.08); }
.card-header.rojo { background: rgba(239,68,68,0.1); }

.mesa-num {
  font-family: 'Syne', sans-serif;
  font-size: 28px;
  font-weight: 800;
  line-height: 1;
}
.card-meta {
  flex: 1;
  font-size: 12px;
  color: var(--muted);
  line-height: 1.6;
}
.card-meta strong { color: var(--text); font-size: 14px; }
.timer {
  font-family: 'Syne', sans-serif;
  font-size: 20px;
  font-weight: 800;
  padding: 6px 12px;
  border-radius: 10px;
}
.timer.verde { background: rgba(34,197,94,0.15); color: var(--green); }
.timer.amarillo { background: rgba(245,158,11,0.15); color: var(--yellow); }
.timer.rojo { background: rgba(239,68,68,0.15); color: var(--red); }

.card-items {
  padding: 12px 16px;
  flex: 1;
  border-top: 1px solid var(--border);
  border-bottom: 1px solid var(--border);
}
.item-row {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  padding: 8px 0;
  border-bottom: 1px solid rgba(255,255,255,0.04);
}
.item-row:last-child { border-bottom: none; }
.item-qty {
  font-family: 'Syne', sans-serif;
  font-size: 22px;
  font-weight: 800;
  color: var(--orange);
  line-height: 1;
  min-width: 32px;
}
.item-info { flex: 1; }
.item-nombre {
  font-size: 15px;
  font-weight: 600;
  line-height: 1.3;
}
.item-obs {
  font-size: 12px;
  color: var(--yellow);
  margin-top: 3px;
}
.item-urgente {
  font-size: 11px;
  color: var(--red);
  font-weight: 700;
  text-transform: uppercase;
}
.item-estado {
  padding: 3px 10px;
  border-radius: 50px;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  cursor: pointer;
}
.item-estado.pendiente { background: rgba(245,158,11,0.15); color: var(--yellow); }
.item-estado.en_preparacion { background: rgba(59,130,246,0.15); color: var(--blue); }
.item-estado.listo { background: rgba(34,197,94,0.15); color: var(--green); }

.card-actions {
  padding: 12px 16px;
  display: flex;
  gap: 8px;
}
.kds-btn {
  flex: 1;
  border: none;
  border-radius: 12px;
  padding: 14px 10px;
  font-family: 'Syne', sans-serif;
  font-size: 14px;
  font-weight: 700;
  cursor: pointer;
  transition: all .2s;
  letter-spacing: 0.5px;
}
.kds-btn:active { transform: scale(0.96); }
.btn-aceptar { background: rgba(59,130,246,0.2); color: var(--blue); border: 1px solid rgba(59,130,246,0.3); }
.btn-aceptar:hover { background: var(--blue); color: white; }
.btn-preparando { background: rgba(245,158,11,0.2); color: var(--yellow); border: 1px solid rgba(245,158,11,0.3); }
.btn-preparando:hover { background: var(--yellow); color: white; }
.btn-listo { background: rgba(34,197,94,0.2); color: var(--green); border: 1px solid rgba(34,197,94,0.3); }
.btn-listo:hover { background: var(--green); color: white; }

/* ===== EMPTY STATE ===== */
.empty-state {
  grid-column: 1 / -1;
  text-align: center;
  padding: 80px;
  color: var(--muted);
}
.empty-state .emoji { font-size: 64px; display: block; margin-bottom: 16px; }
.empty-state h3 { font-family: 'Syne', sans-serif; font-size: 24px; color: var(--text); margin-bottom: 8px; }

/* ===== ALERT SOUND ===== */
.alert-overlay {
  position: fixed;
  inset: 0;
  background: rgba(239,68,68,0.15);
  z-index: 999;
  display: none;
  align-items: center;
  justify-content: center;
  font-family: 'Syne', sans-serif;
  font-size: 48px;
  font-weight: 800;
  color: white;
  animation: flashOverlay .5s;
}
@keyframes flashOverlay { 0% { opacity:0 } 50% { opacity:1 } 100% { opacity:0 } }

/* ===== SCROLLBAR ===== */
::-webkit-scrollbar { width: 6px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: var(--border); border-radius: 3px; }
</style>
</head>
<body>
<?php
$user = currentUser();
?>

<!-- HEADER -->
<div class="kds-header">
  <div class="kds-brand">👨‍🍳 KDS — Cocina</div>

  <div class="area-tabs">
    <button class="area-tab active" onclick="filterArea('todas',this)">🍽️ Todas</button>
    <button class="area-tab" onclick="filterArea('cocina_caliente',this)">🔥 Cocina</button>
    <button class="area-tab" onclick="filterArea('cocina_fria',this)">❄️ Frío</button>
    <button class="area-tab" onclick="filterArea('bar',this)">🍹 Bar</button>
    <button class="area-tab" onclick="filterArea('postres',this)">🍰 Postres</button>
  </div>

  <div class="kds-clock" id="clock">--:--:--</div>
</div>

<!-- STATS -->
<div class="stats-bar">
  <div class="stat pending">
    <div class="num" id="statPending">0</div>
    Pendientes
  </div>
  <div class="stat prep">
    <div class="num" id="statPrep">0</div>
    En Preparación
  </div>
  <div class="stat ready">
    <div class="num" id="statReady">0</div>
    Listos hoy
  </div>
  <div class="stat" style="margin-left:auto;color:var(--muted)">
    <span>Auto-actualiza cada 5s</span>
    <div style="width:8px;height:8px;border-radius:50%;background:var(--green);margin-left:6px;animation:pulse 2s infinite" id="liveIndicator"></div>
  </div>
</div>
<style>@keyframes pulse{0%,100%{opacity:1}50%{opacity:0.4}}</style>

<!-- PEDIDOS -->
<div class="pedidos-container">
  <div class="pedidos-grid" id="pedidosGrid">
    <div class="empty-state">
      <span class="emoji">⏳</span>
      <h3>Cargando pedidos...</h3>
    </div>
  </div>
</div>

<script>
const BASE = '<?= BASE_URL ?>';
let areaActual = 'todas';
let pedidosCache = [];
let timers = {};

// Reloj
function updateClock() {
  const now = new Date();
  const h = String(now.getHours()).padStart(2,'0');
  const m = String(now.getMinutes()).padStart(2,'0');
  const s = String(now.getSeconds()).padStart(2,'0');
  document.getElementById('clock').textContent = `${h}:${m}:${s}`;
}
setInterval(updateClock, 1000);
updateClock();

// ============================================================
// CARGA DE PEDIDOS
// ============================================================
async function loadPedidos() {
  try {
    const url = BASE + '/api/cocina.php' + (areaActual !== 'todas' ? '?area=' + areaActual : '');
    const res = await fetch(url);
    const data = await res.json();

    // Detectar nuevo pedido (sonido/alerta)
    const prevIds = new Set(pedidosCache.map(p => p.id));
    const newOnes = data.filter(p => !prevIds.has(p.id));
    if (newOnes.length > 0 && pedidosCache.length > 0) {
      playAlert();
    }

    pedidosCache = data;
    renderPedidos(data);
    updateStats(data);
  } catch(e) {
    console.error(e);
  }
}

function renderPedidos(pedidos) {
  const grid = document.getElementById('pedidosGrid');

  // Filtrar por área
  const filtered = areaActual === 'todas' ? pedidos :
    pedidos.filter(p => p.items.some(i => i.area === areaActual));

  if (filtered.length === 0) {
    grid.innerHTML = `
      <div class="empty-state">
        <span class="emoji">✅</span>
        <h3>¡Todo al día!</h3>
        <p>No hay pedidos pendientes</p>
      </div>`;
    return;
  }

  grid.innerHTML = filtered.map(p => {
    const color = alertColor(p.created_at);
    const mins = Math.floor((Date.now() - new Date(p.created_at).getTime()) / 60000);
    const items = areaActual === 'todas' ? p.items :
      p.items.filter(i => i.area === areaActual);

    return `
      <div class="pedido-card ${color}" id="card-${p.id}">
        <div class="card-header ${color}">
          <div>
            <div class="mesa-num">Mesa ${p.mesa_numero || '—'}</div>
            <div class="card-meta">
              <strong>${p.mozo_nombre || 'Sin mozo'}</strong><br>
              🕐 ${formatTime(p.created_at)} · ${p.personas || 1} pers.
            </div>
          </div>
          <div class="timer ${color}" id="timer-${p.id}">${mins}m</div>
        </div>

        <div class="card-items">
          ${items.map(item => `
            <div class="item-row">
              <div class="item-qty">${item.cantidad}x</div>
              <div class="item-info">
                <div class="item-nombre">${item.nombre_plato}</div>
                ${item.observacion ? `<div class="item-obs">📝 ${item.observacion}</div>` : ''}
                ${item.prioridad === 'urgente' ? `<div class="item-urgente">🔴 URGENTE</div>` : ''}
                ${item.prioridad === 'alta' ? `<div class="item-urgente" style="color:var(--yellow)">⚡ ALTA PRIORIDAD</div>` : ''}
              </div>
              <div class="item-estado ${item.estado}" onclick="cambiarEstadoItem(${item.id}, '${item.estado}', ${p.id})">
                ${estadoLabel(item.estado)}
              </div>
            </div>
          `).join('')}
        </div>

        <div class="card-actions">
          ${p.estado === 'enviada' ? `
            <button class="kds-btn btn-aceptar" onclick="cambiarEstado(${p.id}, 'preparando')">✋ Aceptar</button>
          ` : ''}
          ${p.estado === 'preparando' ? `
            <button class="kds-btn btn-preparando" onclick="cambiarEstado(${p.id}, 'preparando')">🔄 Preparando</button>
          ` : ''}
          <button class="kds-btn btn-listo" onclick="cambiarEstado(${p.id}, 'lista')">✅ LISTO</button>
        </div>
      </div>
    `;
  }).join('');

  // Actualizar timers en tiempo real
  filtered.forEach(p => {
    clearInterval(timers[p.id]);
    timers[p.id] = setInterval(() => updateTimer(p), 30000);
  });
}

function estadoLabel(e) {
  return {pendiente:'⏳ Pend.',en_preparacion:'🔄 Prep.',listo:'✅ Listo',entregado:'📦 Entr.'}[e] || e;
}

function alertColor(dt) {
  const mins = (Date.now() - new Date(dt).getTime()) / 60000;
  if (mins < 10) return 'verde';
  if (mins < 20) return 'amarillo';
  return 'rojo';
}

function formatTime(dt) {
  const d = new Date(dt);
  return d.getHours().toString().padStart(2,'0') + ':' + d.getMinutes().toString().padStart(2,'0');
}

function updateTimer(p) {
  const el = document.getElementById('timer-' + p.id);
  if (!el) return;
  const mins = Math.floor((Date.now() - new Date(p.created_at).getTime()) / 60000);
  const color = alertColor(p.created_at);
  el.textContent = mins + 'm';
  el.className = 'timer ' + color;
  const card = document.getElementById('card-' + p.id);
  if (card) {
    card.className = 'pedido-card ' + color;
    card.querySelector('.card-header').className = 'card-header ' + color;
  }
}

function updateStats(pedidos) {
  document.getElementById('statPending').textContent = pedidos.filter(p => p.estado === 'enviada').length;
  document.getElementById('statPrep').textContent = pedidos.filter(p => p.estado === 'preparando').length;
  document.getElementById('statReady').textContent = pedidos.filter(p => p.estado === 'lista').length;
}

// ============================================================
// CAMBIAR ESTADO ORDEN
// ============================================================
async function cambiarEstado(id_orden, nuevoEstado) {
  const res = await fetch(BASE + '/api/cocina.php', {
    method: 'POST',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify({ action: 'cambiar_estado', id_orden, estado: nuevoEstado })
  });
  const data = await res.json();
  if (data.ok) {
    loadPedidos();
    if (nuevoEstado === 'lista') playListo();
  }
}

async function cambiarEstadoItem(id_item, estadoActual, id_orden) {
  const siguientes = {pendiente:'en_preparacion', en_preparacion:'listo'};
  const nuevoEstado = siguientes[estadoActual] || 'listo';
  const res = await fetch(BASE + '/api/cocina.php', {
    method: 'POST',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify({ action: 'cambiar_estado_item', id_item, estado: nuevoEstado })
  });
  await res.json();
  loadPedidos();
}

// ============================================================
// FILTROS
// ============================================================
function filterArea(area, btn) {
  areaActual = area;
  document.querySelectorAll('.area-tab').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  renderPedidos(pedidosCache);
}

// ============================================================
// SONIDOS (Web Audio API)
// ============================================================
function playBeep(freq = 880, dur = 200, vol = 0.3) {
  try {
    const ctx = new (window.AudioContext || window.webkitAudioContext)();
    const osc = ctx.createOscillator();
    const gain = ctx.createGain();
    osc.connect(gain);
    gain.connect(ctx.destination);
    osc.frequency.value = freq;
    gain.gain.value = vol;
    osc.start();
    osc.stop(ctx.currentTime + dur / 1000);
  } catch(e) {}
}

function playAlert() {
  playBeep(660, 150, 0.4);
  setTimeout(() => playBeep(880, 150, 0.4), 200);
  setTimeout(() => playBeep(1100, 200, 0.4), 400);
}

function playListo() {
  playBeep(523, 100, 0.3);
  setTimeout(() => playBeep(659, 100, 0.3), 120);
  setTimeout(() => playBeep(784, 300, 0.3), 240);
}

// ============================================================
// INIT
// ============================================================
loadPedidos();
setInterval(loadPedidos, 5000);
</script>
</body>
</html>
