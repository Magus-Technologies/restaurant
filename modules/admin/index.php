<?php
require_once __DIR__ . '/../../includes/functions.php';
requireLogin(['administrador','supervisor']);
$user = currentUser();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin — RestaurantOS</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<style>
:root{--bg:#08080f;--surface:#111119;--surface2:#1a1a28;--surface3:#222230;--accent:#ff6b35;--green:#22c55e;--yellow:#f59e0b;--red:#ef4444;--blue:#3b82f6;--purple:#a855f7;--text:#f0f0f8;--muted:#5a5a78;--border:rgba(255,255,255,0.07);--radius:16px}
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--text);display:flex;height:100vh;overflow:hidden}
.sidebar{width:220px;background:var(--surface);border-right:1px solid var(--border);display:flex;flex-direction:column;flex-shrink:0;padding:20px 0;overflow-y:auto}
.sidebar-brand{padding:0 20px 20px;border-bottom:1px solid var(--border);margin-bottom:16px}
.sidebar-brand h1{font-family:'Syne',sans-serif;font-size:18px;font-weight:800;color:var(--accent)}
.sidebar-brand p{font-size:11px;color:var(--muted);margin-top:2px}
.nav-section{padding:0 10px;margin-bottom:8px}
.nav-label{font-size:10px;text-transform:uppercase;letter-spacing:1.5px;color:var(--muted);padding:0 10px;margin-bottom:6px;margin-top:8px}
.nav-item{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:10px;cursor:pointer;transition:all .15s;color:var(--muted);font-size:14px;font-weight:500;border:1px solid transparent}
.nav-item:hover{background:var(--surface2);color:var(--text)}
.nav-item.active{background:rgba(255,107,53,0.1);border-color:rgba(255,107,53,0.2);color:var(--accent)}
.nav-item .icon{font-size:16px;min-width:20px;text-align:center}
.sidebar-footer{margin-top:auto;padding:16px}
.user-chip{background:var(--surface2);border-radius:10px;padding:10px;font-size:12px}
.user-chip strong{display:block;font-size:13px;margin-bottom:2px}
.user-chip a{color:var(--muted);text-decoration:none;font-size:11px}
.main{flex:1;display:flex;flex-direction:column;overflow:hidden}
.topbar{background:var(--surface);border-bottom:1px solid var(--border);padding:14px 24px;display:flex;align-items:center;gap:12px;flex-shrink:0}
.page-title{font-family:'Syne',sans-serif;font-size:20px;font-weight:700}
.btn{background:var(--surface2);border:1px solid var(--border);border-radius:10px;padding:8px 16px;color:var(--text);font-size:13px;font-weight:600;cursor:pointer;transition:all .15s;font-family:'DM Sans',sans-serif}
.btn:hover{border-color:var(--accent);color:var(--accent)}
.btn-primary{background:var(--accent);border-color:var(--accent);color:white}
.btn-primary:hover{background:#e55a28;color:white}
.btn-danger{background:rgba(239,68,68,0.1);border-color:var(--red);color:var(--red)}
.content{flex:1;overflow-y:auto;padding:24px}
.stats-row{display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:14px;margin-bottom:24px}
.stat-card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:18px}
.stat-card .s-label{font-size:11px;text-transform:uppercase;letter-spacing:1px;color:var(--muted);margin-bottom:8px}
.stat-card .s-value{font-family:'Syne',sans-serif;font-size:28px;font-weight:800}
.table-box{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;margin-bottom:20px}
.table-header{display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid var(--border)}
.table-header h3{font-family:'Syne',sans-serif;font-size:15px;font-weight:700}
table{width:100%;border-collapse:collapse}
th{text-align:left;padding:10px 16px;font-size:11px;text-transform:uppercase;letter-spacing:1px;color:var(--muted);background:var(--surface2)}
td{padding:12px 16px;border-bottom:1px solid rgba(255,255,255,0.04);font-size:14px}
tr:last-child td{border-bottom:none}
tr:hover td{background:rgba(255,255,255,0.02)}
.badge{padding:3px 10px;border-radius:50px;font-size:11px;font-weight:700}
.badge-green{background:rgba(34,197,94,0.15);color:var(--green)}
.badge-red{background:rgba(239,68,68,0.15);color:var(--red)}
.badge-yellow{background:rgba(245,158,11,0.15);color:var(--yellow)}
.badge-blue{background:rgba(59,130,246,0.15);color:var(--blue)}
.badge-purple{background:rgba(168,85,247,0.15);color:var(--purple)}
.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,0.7);z-index:500;display:none;align-items:center;justify-content:center;backdrop-filter:blur(4px)}
.modal-overlay.active{display:flex}
.modal{background:var(--surface);border:1px solid var(--border);border-radius:20px;padding:28px;width:90%;max-width:560px;max-height:90vh;overflow-y:auto}
.modal h3{font-family:'Syne',sans-serif;font-size:18px;font-weight:700;margin-bottom:20px}
.modal-lg{max-width:720px}
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.form-grid-3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px}
.form-group{display:flex;flex-direction:column;gap:6px}
.form-group.full{grid-column:1/-1}
label{font-size:11px;text-transform:uppercase;letter-spacing:1px;color:var(--muted)}
input,select,textarea{background:var(--surface2);border:1px solid var(--border);border-radius:10px;padding:11px 14px;color:var(--text);font-size:14px;font-family:'DM Sans',sans-serif;outline:none;transition:border-color .15s;width:100%}
input:focus,select:focus,textarea:focus{border-color:var(--accent)}
select option{background:var(--surface2)}
textarea{min-height:80px;resize:vertical}
.modal-btns{display:flex;gap:10px;margin-top:20px;flex-wrap:wrap}
.btn-cancel{background:var(--surface2);border:1px solid var(--border);color:var(--text)}
.search-box{display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap;align-items:center}
.search-input{flex:1;min-width:140px;background:var(--surface2);border:1px solid var(--border);border-radius:10px;padding:10px 14px;color:var(--text);font-size:14px;font-family:'DM Sans',sans-serif;outline:none}
.search-input:focus{border-color:var(--accent)}
.action-btns{display:flex;gap:4px;flex-wrap:wrap}
.btn-sm{padding:5px 10px;font-size:12px;border-radius:8px}
.page-section{display:none}
.page-section.active{display:block}
.stock-low{color:var(--red);font-weight:600}
.stock-ok{color:var(--green)}
.toast{position:fixed;bottom:30px;right:30px;transform:translateY(100px);background:var(--surface);border:1px solid var(--border);border-radius:50px;padding:14px 24px;font-size:14px;font-weight:600;z-index:9999;transition:transform .3s cubic-bezier(0.34,1.56,0.64,1);box-shadow:0 10px 40px rgba(0,0,0,0.4)}
.toast.show{transform:translateY(0)}
.toast.error{border-color:var(--red)}
.divider{border:none;border-top:1px solid var(--border);margin:16px 0}
.receta-row{display:grid;grid-template-columns:1fr auto auto;gap:8px;align-items:center;margin-bottom:8px}
.compra-item-row{display:grid;grid-template-columns:1fr 80px 100px auto;gap:8px;align-items:center;margin-bottom:8px}
#listaReceta,#listaCompraItems{max-height:200px;overflow-y:auto;margin-top:8px}
/* TABS */
.tabs{display:flex;gap:4px;margin-bottom:20px;border-bottom:1px solid var(--border);padding-bottom:0}
.tab-btn{background:none;border:none;border-bottom:2px solid transparent;padding:10px 18px;color:var(--muted);font-size:14px;font-weight:600;cursor:pointer;font-family:'DM Sans',sans-serif;transition:all .15s;margin-bottom:-1px}
.tab-btn.active{color:var(--accent);border-bottom-color:var(--accent)}
.tab-panel{display:none}.tab-panel.active{display:block}
/* CHART BARS */
.bar-chart{display:flex;flex-direction:column;gap:8px;padding:4px 0}
.bar-row{display:grid;grid-template-columns:140px 1fr 80px;align-items:center;gap:10px;font-size:13px}
.bar-track{background:var(--surface2);border-radius:4px;height:20px;overflow:hidden}
.bar-fill{height:100%;background:var(--accent);border-radius:4px;transition:width .4s ease;min-width:2px}
.bar-fill.green{background:var(--green)}.bar-fill.blue{background:var(--blue)}.bar-fill.purple{background:var(--purple)}
/* KPI CARDS */
.kpi-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:12px;margin-bottom:20px}
.kpi{background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:16px}
.kpi .k-val{font-family:'Syne',sans-serif;font-size:24px;font-weight:800;margin:6px 0 2px}
.kpi .k-lbl{font-size:11px;text-transform:uppercase;letter-spacing:1px;color:var(--muted)}
.kpi .k-sub{font-size:12px;color:var(--muted)}
/* CONFIG FORM */
.config-section{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:20px;margin-bottom:16px}
.config-section h4{font-family:'Syne',sans-serif;font-size:14px;margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid var(--border);color:var(--accent)}
/* PERMISOS */
.permisos-table th,.permisos-table td{padding:8px 12px;font-size:13px}
.permisos-table input[type=checkbox]{width:16px;height:16px;cursor:pointer;accent-color:var(--accent)}
/* TABLA COLORES */
.red-row td{background:rgba(239,68,68,0.05)!important}
.yellow-row td{background:rgba(245,158,11,0.05)!important}
</style>
</head>
<body>

<div class="sidebar">
  <div class="sidebar-brand">
    <h1>RestaurantOS</h1>
    <p>Panel Admin</p>
  </div>
  <div class="nav-section">
    <div class="nav-label">Principal</div>
    <div class="nav-item active" onclick="showPage('dashboard',this)"><span class="icon">📊</span> Dashboard</div>
    <div class="nav-item" onclick="showPage('mesas',this)"><span class="icon">🪑</span> Mesas</div>
    <div class="nav-item" onclick="showPage('reservas',this)"><span class="icon">📅</span> Reservaciones</div>
  </div>
  <div class="nav-section">
    <div class="nav-label">Menú</div>
    <div class="nav-item" onclick="showPage('categorias',this)"><span class="icon">📁</span> Categorías</div>
    <div class="nav-item" onclick="showPage('platos',this)"><span class="icon">🍽️</span> Platos</div>
    <div class="nav-item" onclick="showPage('menu_dia',this)"><span class="icon">📋</span> Menú del Día</div>
  </div>
  <div class="nav-section">
    <div class="nav-label">Inventario</div>
    <div class="nav-item" onclick="showPage('insumos',this)"><span class="icon">📦</span> Insumos</div>
    <div class="nav-item" onclick="showPage('recetas',this)"><span class="icon">📝</span> Recetas</div>
    <div class="nav-item" onclick="showPage('kardex',this)"><span class="icon">📈</span> Kardex</div>
  </div>
  <div class="nav-section">
    <div class="nav-label">Compras</div>
    <div class="nav-item" onclick="showPage('proveedores',this)"><span class="icon">🏭</span> Proveedores</div>
    <div class="nav-item" onclick="showPage('compras',this)"><span class="icon">🛒</span> Compras</div>
  </div>
  <div class="nav-section">
    <div class="nav-label">Operaciones</div>
    <div class="nav-item" onclick="showPage('delivery',this)"><span class="icon">🛵</span> Delivery</div>
    <div class="nav-item" onclick="showPage('crm',this)"><span class="icon">👥</span> Clientes CRM</div>
    <div class="nav-item" onclick="showPage('reportes',this)"><span class="icon">📊</span> Reportes</div>
    <a href="<?= BASE_URL ?>/modules/admin/facturacion.php" class="nav-item" style="text-decoration:none"><span class="icon">📄</span> Facturación SUNAT</a>
  </div>
  <div class="nav-section">
    <div class="nav-label">Sistema</div>
    <div class="nav-item" onclick="showPage('usuarios',this)"><span class="icon">👤</span> Usuarios</div>
    <div class="nav-item" onclick="showPage('configuracion',this)"><span class="icon">⚙️</span> Configuración</div>
    <div class="nav-item" onclick="showPage('sucursales',this)"><span class="icon">🏢</span> Sucursales</div>
    <div class="nav-item" onclick="showPage('impresoras',this)"><span class="icon">🖨️</span> Impresoras</div>
    <div class="nav-item" onclick="showPage('permisos',this)"><span class="icon">🔐</span> Permisos</div>
  </div>
  <div class="sidebar-footer">
    <div class="user-chip">
      <strong><?= htmlspecialchars($user['nombre']) ?></strong>
      <span style="color:var(--muted);font-size:11px"><?= $user['rol'] ?></span><br>
      <a href="<?= BASE_URL ?>/logout.php">Cerrar sesión</a>
    </div>
  </div>
</div>

<div class="main">
  <div class="topbar">
    <span style="font-size:20px">🍽️</span>
    <span class="page-title" id="pageTitle">Dashboard</span>
    <div style="margin-left:auto;display:flex;gap:8px">
      <a href="<?= BASE_URL ?>/modules/mozos/" target="_blank"><button class="btn">📱 Mozos</button></a>
      <a href="<?= BASE_URL ?>/modules/cocina/" target="_blank"><button class="btn">👨‍🍳 Cocina</button></a>
      <a href="<?= BASE_URL ?>/modules/caja/" target="_blank"><button class="btn">💰 Caja</button></a>
    </div>
  </div>

  <div class="content">

    <!-- DASHBOARD -->
    <div class="page-section active" id="page-dashboard">
      <div class="stats-row" id="dashStats"></div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
        <div class="table-box">
          <div class="table-header"><h3>🕐 Últimas Órdenes</h3></div>
          <table><thead><tr><th>Mesa</th><th>Mozo</th><th>Total</th><th>Estado</th></tr></thead>
          <tbody id="ultimasOrdenes"></tbody></table>
        </div>
        <div class="table-box">
          <div class="table-header"><h3>⚠️ Stock Bajo</h3></div>
          <table><thead><tr><th>Insumo</th><th>Stock</th><th>Mínimo</th></tr></thead>
          <tbody id="stockBajo"></tbody></table>
        </div>
      </div>
    </div>

    <!-- MESAS -->
    <div class="page-section" id="page-mesas">
      <div class="search-box">
        <input class="search-input" placeholder="Buscar mesa..." oninput="filterTable('tableMesas',this.value)">
        <button class="btn btn-primary" onclick="abrirModal('modalMesa')">+ Nueva Mesa</button>
      </div>
      <div class="table-box">
        <table><thead><tr><th>Número</th><th>Zona</th><th>Capacidad</th><th>Estado</th><th>Acciones</th></tr></thead>
        <tbody id="tableMesas"></tbody></table>
      </div>
    </div>

    <!-- CATEGORIAS -->
    <div class="page-section" id="page-categorias">
      <div class="search-box">
        <input class="search-input" placeholder="Buscar categoría..." oninput="filterTable('tableCategorias',this.value)">
        <button class="btn btn-primary" onclick="abrirModal('modalCategoria')">+ Nueva Categoría</button>
      </div>
      <div class="table-box">
        <table><thead><tr><th>Icono</th><th>Nombre</th><th>Área</th><th>Color</th><th>Orden</th><th>Activo</th><th>Acciones</th></tr></thead>
        <tbody id="tableCategorias"></tbody></table>
      </div>
    </div>

    <!-- PLATOS -->
    <div class="page-section" id="page-platos">
      <div class="search-box">
        <input class="search-input" placeholder="Buscar plato..." oninput="filterTable('tablePlatos',this.value)">
        <select id="filterCat" onchange="loadPlatos()" style="width:200px;background:var(--surface2);border:1px solid var(--border);border-radius:10px;padding:10px;color:var(--text);font-family:'DM Sans',sans-serif">
          <option value="">Todas las categorías</option>
        </select>
        <button class="btn btn-primary" onclick="abrirModal('modalPlato')">+ Nuevo Plato</button>
      </div>
      <div class="table-box">
        <table><thead><tr><th>Nombre</th><th>Categoría</th><th>Precio</th><th>Área</th><th>Tiempo</th><th>Disponible</th><th>Acciones</th></tr></thead>
        <tbody id="tablePlatos"></tbody></table>
      </div>
    </div>

    <!-- INSUMOS -->
    <div class="page-section" id="page-insumos">
      <div class="search-box">
        <input class="search-input" placeholder="Buscar insumo..." oninput="filterTable('tableInsumos',this.value)">
        <button class="btn btn-primary" onclick="abrirModal('modalInsumo')">+ Nuevo Insumo</button>
        <button class="btn" onclick="abrirModal('modalKardexEntry')">📥 Ingreso Stock</button>
      </div>
      <div class="table-box">
        <table><thead><tr><th>Nombre</th><th>Unidad</th><th>Stock</th><th>Mínimo</th><th>Costo Unit.</th><th>Categoría</th><th>Acciones</th></tr></thead>
        <tbody id="tableInsumos"></tbody></table>
      </div>
    </div>

    <!-- RECETAS -->
    <div class="page-section" id="page-recetas">
      <div class="search-box">
        <select id="recetaPlatoFiltro" onchange="loadReceta(this.value)" style="width:280px;background:var(--surface2);border:1px solid var(--border);border-radius:10px;padding:10px;color:var(--text);font-family:'DM Sans',sans-serif">
          <option value="">-- Selecciona un plato --</option>
        </select>
        <button class="btn btn-primary" id="btnEditarReceta" style="display:none" onclick="abrirModal('modalReceta')">✏️ Editar Receta</button>
      </div>
      <div class="table-box" id="boxReceta" style="display:none">
        <div class="table-header"><h3 id="recetaTitulo">Receta</h3></div>
        <table><thead><tr><th>Insumo</th><th>Cantidad</th><th>Unidad</th><th>Costo Est.</th></tr></thead>
        <tbody id="tableReceta"></tbody>
        <tfoot><tr><td colspan="3" style="text-align:right;font-weight:700;padding:12px 16px">Costo total receta:</td>
        <td id="costoRecetaTotal" style="font-weight:700;color:var(--accent);padding:12px 16px"></td></tr></tfoot>
        </table>
      </div>
    </div>

    <!-- KARDEX -->
    <div class="page-section" id="page-kardex">
      <div class="search-box">
        <select id="kardexInsumoFiltro" onchange="loadKardex()" style="width:240px;background:var(--surface2);border:1px solid var(--border);border-radius:10px;padding:10px;color:var(--text);font-family:'DM Sans',sans-serif">
          <option value="">Todos los insumos</option>
        </select>
        <button class="btn" onclick="abrirModal('modalKardexEntry')">📥 Nuevo Movimiento</button>
      </div>
      <div class="table-box">
        <table><thead><tr><th>Fecha</th><th>Insumo</th><th>Tipo</th><th>Cantidad</th><th>Stock Result.</th><th>Motivo</th><th>Usuario</th></tr></thead>
        <tbody id="tableKardex"></tbody></table>
      </div>
    </div>

    <!-- PROVEEDORES -->
    <div class="page-section" id="page-proveedores">
      <div class="search-box">
        <input class="search-input" placeholder="Buscar proveedor..." oninput="filterTable('tableProveedores',this.value)">
        <button class="btn btn-primary" onclick="abrirModal('modalProveedor')">+ Nuevo Proveedor</button>
      </div>
      <div class="table-box">
        <table><thead><tr><th>Nombre</th><th>RUC</th><th>Teléfono</th><th>Categoría</th><th>Contacto</th><th>Cond. Pago</th><th>Acciones</th></tr></thead>
        <tbody id="tableProveedores"></tbody></table>
      </div>
    </div>

    <!-- COMPRAS EXPANDIDAS -->
    <div class="page-section" id="page-compras">
      <div class="tabs">
        <button class="tab-btn active" onclick="switchTab('comp','ordenes',this)">📋 Órdenes de Compra</button>
        <button class="tab-btn" onclick="switchTab('comp','cuentas_pagar',this)">💳 Cuentas por Pagar</button>
        <button class="tab-btn" onclick="switchTab('comp','costos_prom',this)">📊 Costos Promedio</button>
      </div>
      <div class="tab-panel active" id="comp-ordenes">
        <div class="search-box">
          <input type="date" id="compraFechaIni" class="search-input" style="max-width:160px">
          <input type="date" id="compraFechaFin" class="search-input" style="max-width:160px">
          <button class="btn" onclick="loadCompras()">🔍 Filtrar</button>
          <button class="btn btn-primary" onclick="abrirModal('modalCompra')">+ Nueva Orden de Compra</button>
        </div>
        <div class="table-box">
          <table><thead><tr><th>Número</th><th>Proveedor</th><th>Fecha</th><th>Factura</th><th>Total</th><th>Estado</th><th>Acciones</th></tr></thead>
          <tbody id="tableCompras"></tbody></table>
        </div>
      </div>
      <div class="tab-panel" id="comp-cuentas_pagar">
        <div class="kpi-grid" id="kpiCuentasPagar"></div>
        <div class="table-box">
          <div class="table-header"><h3>💳 Cuentas por Pagar</h3>
            <select id="filtroCuentasPagar" onchange="loadCuentasPagar()" style="background:var(--surface2);border:1px solid var(--border);border-radius:8px;padding:6px 10px;color:var(--text);font-family:'DM Sans',sans-serif;font-size:13px">
              <option value="">Pendientes/Parciales</option><option value="vencida">Vencidas</option><option value="pagada">Pagadas</option>
            </select>
          </div>
          <table><thead><tr><th>Compra</th><th>Proveedor</th><th>Total</th><th>Pagado</th><th>Saldo</th><th>Vencimiento</th><th>Estado</th><th>Acciones</th></tr></thead>
          <tbody id="tableCuentasPagar"></tbody></table>
        </div>
      </div>
      <div class="tab-panel" id="comp-costos_prom">
        <div class="table-box">
          <div class="table-header"><h3>📊 Costos Promedio de Insumos</h3></div>
          <table><thead><tr><th>Insumo</th><th>Unidad</th><th>Costo Actual</th><th>Costo Promedio Compras</th><th>Variación</th></tr></thead>
          <tbody id="tbCostosProm"></tbody></table>
        </div>
      </div>
    </div>

    <!-- DELIVERY -->
    <div class="page-section" id="page-delivery">
      <div class="search-box">
        <select id="deliveryEstadoFiltro" onchange="loadDelivery()" style="width:200px;background:var(--surface2);border:1px solid var(--border);border-radius:10px;padding:10px;color:var(--text);font-family:'DM Sans',sans-serif">
          <option value="">Todos los estados</option>
          <option value="recibido">Recibido</option>
          <option value="en_cocina">En Cocina</option>
          <option value="listo">Listo</option>
          <option value="en_camino">En Camino</option>
          <option value="entregado">Entregado</option>
        </select>
        <button class="btn btn-primary" onclick="abrirModal('modalDelivery')">+ Nuevo Delivery</button>
      </div>
      <div class="table-box">
        <table><thead><tr><th>N°</th><th>Cliente</th><th>Dirección</th><th>Método Pago</th><th>Estado</th><th>Acciones</th></tr></thead>
        <tbody id="tableDelivery"></tbody></table>
      </div>
    </div>

    <!-- CRM -->
    <div class="page-section" id="page-crm">
      <div class="search-box">
        <input class="search-input" placeholder="Buscar cliente..." oninput="filterTable('tableCRM',this.value)">
        <button class="btn btn-primary" onclick="abrirModal('modalCliente')">+ Nuevo Cliente</button>
      </div>
      <div class="table-box">
        <table><thead><tr><th>Nombre</th><th>Teléfono</th><th>Email</th><th>Cumpleaños</th><th>Notas</th><th>Acciones</th></tr></thead>
        <tbody id="tableCRM"></tbody></table>
      </div>
    </div>

    <!-- MENU DIA -->
    <div class="page-section" id="page-menu_dia">
      <div class="search-box">
        <input class="search-input" type="date" id="fechaMenu" value="<?= date('Y-m-d') ?>" onchange="loadMenuDia()" style="max-width:180px">
        <button class="btn btn-primary" onclick="abrirModal('modalMenuDia')">+ Configurar Menú</button>
      </div>
      <div class="table-box">
        <table><thead><tr><th>Nombre</th><th>Entrada</th><th>Fondo</th><th>Bebida</th><th>Precio</th><th>Vendidos/Límite</th><th>Estado</th><th>Acciones</th></tr></thead>
        <tbody id="tableMenuDia"></tbody></table>
      </div>
    </div>

    <!-- REPORTES EXPANDIDOS -->
    <div class="page-section" id="page-reportes">
      <div class="search-box" style="flex-wrap:wrap">
        <input type="date" id="repFechaIni" class="search-input" style="max-width:160px">
        <span style="color:var(--muted);align-self:center">al</span>
        <input type="date" id="repFechaFin" class="search-input" style="max-width:160px">
        <button class="btn" onclick="loadReporteActivo()">🔍 Actualizar</button>
        <button class="btn" onclick="exportarCSV()">📥 Exportar CSV</button>
      </div>
      <div class="tabs">
        <button class="tab-btn active" onclick="switchTab('rep','ventas_dia',this);loadReporteActivo()">📅 Por Día</button>
        <button class="tab-btn" onclick="switchTab('rep','ventas_hora',this);loadReporteActivo()">🕐 Por Hora</button>
        <button class="tab-btn" onclick="switchTab('rep','ventas_mozo',this);loadReporteActivo()">👤 Por Mozo</button>
        <button class="tab-btn" onclick="switchTab('rep','top_platos',this);loadReporteActivo()">🏆 Top Platos</button>
        <button class="tab-btn" onclick="switchTab('rep','rentabilidad',this);loadReporteActivo()">💰 Rentabilidad</button>
        <button class="tab-btn" onclick="switchTab('rep','consumo',this);loadReporteActivo()">📦 Consumo</button>
        <button class="tab-btn" onclick="switchTab('rep','inventario_val',this);loadReporteActivo()">🏷️ Inv. Valorizado</button>
        <button class="tab-btn" onclick="switchTab('rep','tiempos',this);loadReporteActivo()">⏱️ Tiempos</button>
      </div>
      <div class="tab-panel active" id="rep-ventas_dia">
        <div class="kpi-grid" id="kpiVentasDia"></div>
        <div class="table-box"><div class="table-header"><h3>📅 Ventas por Día</h3></div>
          <table><thead><tr><th>Fecha</th><th>Órdenes</th><th>Total</th><th>Descuentos</th><th>IGV</th><th>Propinas</th><th>Ticket Prom.</th></tr></thead>
          <tbody id="tbVentasDia"></tbody></table></div>
      </div>
      <div class="tab-panel" id="rep-ventas_hora">
        <div class="table-box" style="padding:20px"><div class="table-header"><h3>🕐 Ventas por Hora</h3></div>
          <div id="chartHoras" class="bar-chart" style="padding:16px"></div></div>
      </div>
      <div class="tab-panel" id="rep-ventas_mozo">
        <div class="table-box"><div class="table-header"><h3>👤 Rendimiento por Mozo</h3></div>
          <table><thead><tr><th>Mozo</th><th>Órdenes</th><th>Total Ventas</th><th>Ticket Prom.</th><th>Propinas</th><th>% del total</th></tr></thead>
          <tbody id="tbVentasMozo"></tbody></table></div>
      </div>
      <div class="tab-panel" id="rep-top_platos">
        <div class="table-box" style="padding:20px"><div class="table-header"><h3>🏆 Platos más Vendidos</h3></div>
          <div id="chartPlatos" class="bar-chart" style="padding:16px"></div></div>
        <div class="table-box"><table><thead><tr><th>Plato</th><th>Categoría</th><th>Vendidos</th><th>Ingresos</th><th>En Órdenes</th></tr></thead>
          <tbody id="tbTopPlatos"></tbody></table></div>
      </div>
      <div class="tab-panel" id="rep-rentabilidad">
        <div class="kpi-grid" id="kpiRentabilidad"></div>
        <div class="table-box"><div class="table-header"><h3>💰 Rentabilidad por Plato</h3></div>
          <table><thead><tr><th>Plato</th><th>Precio</th><th>Costo</th><th>Vendidos</th><th>Ingresos</th><th>Costo Total</th><th>Utilidad</th><th>Margen %</th></tr></thead>
          <tbody id="tbRentabilidad"></tbody></table></div>
      </div>
      <div class="tab-panel" id="rep-consumo">
        <div class="table-box"><div class="table-header"><h3>📦 Consumo de Insumos</h3></div>
          <table><thead><tr><th>Insumo</th><th>Unidad</th><th>Categoría</th><th>Entradas</th><th>Consumo</th><th>Merma</th><th>Costo Consumo</th></tr></thead>
          <tbody id="tbConsumo"></tbody></table></div>
      </div>
      <div class="tab-panel" id="rep-inventario_val">
        <div class="kpi-grid" id="kpiInventario"></div>
        <div class="table-box"><div class="table-header"><h3>🏷️ Inventario Valorizado</h3></div>
          <table><thead><tr><th>Insumo</th><th>Unidad</th><th>Categoría</th><th>Stock</th><th>Mínimo</th><th>Costo Unit.</th><th>Valor Total</th><th>Estado</th></tr></thead>
          <tbody id="tbInventarioVal"></tbody></table></div>
      </div>
      <div class="tab-panel" id="rep-tiempos">
        <div class="kpi-grid" id="kpiTiempos"></div>
        <div class="table-box"><div class="table-header"><h3>⏱️ Tiempos de Preparación</h3></div>
          <table><thead><tr><th>Plato</th><th>Categoría</th><th>Total</th><th>Prom. (min)</th><th>Mín.</th><th>Máx.</th></tr></thead>
          <tbody id="tbTiempos"></tbody></table></div>
      </div>
    </div>

    <!-- RESERVAS -->
    <div class="page-section" id="page-reservas">
      <div class="search-box">
        <input class="search-input" type="date" id="fechaReservas" value="<?= date('Y-m-d') ?>" onchange="loadReservas()" style="max-width:180px">
        <button class="btn btn-primary" onclick="abrirModal('modalReserva')">+ Nueva Reserva</button>
      </div>
      <div class="table-box">
        <table><thead><tr><th>Hora</th><th>Cliente</th><th>Personas</th><th>Mesa</th><th>Estado</th><th>Observaciones</th><th>Acciones</th></tr></thead>
        <tbody id="tableReservas"></tbody></table>
      </div>
    </div>

    <!-- USUARIOS -->
    <div class="page-section" id="page-usuarios">
      <div class="search-box">
        <input class="search-input" placeholder="Buscar usuario..." oninput="filterTable('tableUsuarios',this.value)">
        <button class="btn btn-primary" onclick="abrirModal('modalUsuario')">+ Nuevo Usuario</button>
      </div>
      <div class="table-box">
        <table><thead><tr><th>Nombre</th><th>Usuario</th><th>Rol</th><th>Estado</th><th>Acciones</th></tr></thead>
        <tbody id="tableUsuarios"></tbody></table>
      </div>
    </div>


    <!-- CONFIGURACIÓN -->
    <div class="page-section" id="page-configuracion">
      <div class="tabs">
        <button class="tab-btn active" onclick="switchTab('cfg','restaurante',this)">🍽️ Restaurante</button>
        <button class="tab-btn" onclick="switchTab('cfg','fiscal',this)">📄 Datos Fiscales</button>
        <button class="tab-btn" onclick="switchTab('cfg','sistema',this)">⚙️ Sistema</button>
      </div>
      <div class="tab-panel active" id="cfg-restaurante">
        <div class="config-section">
          <h4>🍽️ Datos del Restaurante</h4>
          <div style="display:flex;gap:16px;align-items:flex-start;margin-bottom:16px">
            <div style="text-align:center">
              <img id="logoPreview" src="" alt="" style="width:90px;height:90px;object-fit:contain;background:var(--surface2);border-radius:12px;border:1px solid var(--border);display:block;margin-bottom:8px">
              <input type="file" id="cfgLogoFile" accept="image/*" onchange="previewLogo(this)" style="font-size:11px;color:var(--muted);width:90px">
            </div>
            <div class="form-grid" style="flex:1">
              <div class="form-group"><label>Nombre del Restaurante</label><input id="cfgNombre" placeholder="Mi Restaurante"></div>
              <div class="form-group"><label>Slogan</label><input id="cfgSlogan" placeholder="El mejor sabor"></div>
              <div class="form-group"><label>Teléfono</label><input id="cfgTelefono" placeholder="01-234-5678"></div>
              <div class="form-group"><label>WhatsApp</label><input id="cfgCelular" placeholder="987-654-321"></div>
              <div class="form-group full"><label>Dirección</label><input id="cfgDireccion" placeholder="Av. Principal 123, Lima"></div>
              <div class="form-group"><label>Email</label><input id="cfgEmail" type="email"></div>
              <div class="form-group"><label>Web</label><input id="cfgWeb" placeholder="www.mirestaurante.com"></div>
            </div>
          </div>
          <div class="modal-btns"><button class="btn btn-primary" onclick="saveConfigRestaurante()">💾 Guardar</button></div>
        </div>
      </div>
      <div class="tab-panel" id="cfg-fiscal">
        <div class="config-section">
          <h4>📄 Datos Fiscales</h4>
          <div class="form-grid">
            <div class="form-group"><label>RUC</label><input id="cfgRuc" placeholder="20123456789" maxlength="11"></div>
            <div class="form-group"><label>Razón Social</label><input id="cfgRazonSocial"></div>
            <div class="form-group"><label>% IGV</label><input id="cfgIgv" type="number" step="0.01" value="18"></div>
            <div class="form-group"><label>Moneda</label><select id="cfgMoneda"><option value="S/">S/ Soles</option><option value="$">$ Dólares</option></select></div>
            <div class="form-group full"><label>Cuenta Bancaria</label><input id="cfgCuenta" placeholder="BCP: 123-456789-0-12"></div>
            <div class="form-group full"><label>CCI</label><input id="cfgCci" placeholder="00212300456789012345"></div>
          </div>
          <div class="modal-btns"><button class="btn btn-primary" onclick="saveConfigFiscal()">💾 Guardar</button></div>
        </div>
      </div>
      <div class="tab-panel" id="cfg-sistema">
        <div class="config-section">
          <h4>⚙️ Parámetros</h4>
          <div class="form-grid">
            <div class="form-group"><label>% Propina por defecto</label><input id="cfgPropina" type="number" step="0.5" value="0" min="0"></div>
            <div class="form-group"><label>Tiempo alerta cocina (min)</label><input id="cfgAlertaCocina" type="number" value="15" min="5"></div>
            <div class="form-group"><label>Alertas de Stock</label><select id="cfgAlertaStock"><option value="1">Activadas</option><option value="0">Desactivadas</option></select></div>
            <div class="form-group"><label>Descuento inventario</label><select id="cfgDescInventario"><option value="1">Al marcar LISTO (auto)</option><option value="0">Manual</option></select></div>
          </div>
          <div class="modal-btns"><button class="btn btn-primary" onclick="saveConfigSistema()">💾 Guardar</button></div>
        </div>
      </div>
    </div>

    <!-- SUCURSALES -->
    <div class="page-section" id="page-sucursales">
      <div class="search-box">
        <button class="btn btn-primary" onclick="abrirModal('modalSucursal')">+ Nueva Sucursal</button>
      </div>
      <div class="table-box">
        <table><thead><tr><th>Nombre</th><th>Dirección</th><th>Teléfono</th><th>RUC</th><th>Email</th><th>Estado</th><th>Acciones</th></tr></thead>
        <tbody id="tableSucursales"></tbody></table>
      </div>
    </div>

    <!-- IMPRESORAS -->
    <div class="page-section" id="page-impresoras">
      <div class="search-box">
        <button class="btn btn-primary" onclick="abrirModal('modalImpresora')">+ Nueva Impresora</button>
      </div>
      <div class="table-box">
        <table><thead><tr><th>Nombre</th><th>Tipo</th><th>Ancho Papel</th><th>Estado</th><th>Acciones</th></tr></thead>
        <tbody id="tableImpresoras"></tbody></table>
      </div>
      <div style="margin-top:12px;color:var(--muted);font-size:13px;background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:14px">
        <strong style="color:var(--yellow)">ℹ️</strong> La cabecera y pie se imprimen en todos los tickets. Para térmicas de 80mm recomendamos ancho 48 caracteres.
      </div>
    </div>

    <!-- PERMISOS -->
    <div class="page-section" id="page-permisos">
      <div class="search-box">
        <select id="permisoRolFiltro" onchange="loadPermisos()" style="width:200px;background:var(--surface2);border:1px solid var(--border);border-radius:10px;padding:10px;color:var(--text);font-family:'DM Sans',sans-serif">
          <option value="mozo">Mozo</option><option value="cocina">Cocina</option><option value="bar">Bar</option>
          <option value="cajero">Cajero</option><option value="almacen">Almacén</option>
          <option value="compras">Compras</option><option value="supervisor">Supervisor</option>
        </select>
        <button class="btn btn-primary" onclick="saveAllPermisos()">💾 Guardar Permisos</button>
      </div>
      <div class="table-box">
        <table class="permisos-table">
          <thead><tr><th>Módulo</th><th style="text-align:center">Ver</th><th style="text-align:center">Crear</th><th style="text-align:center">Editar</th><th style="text-align:center">Eliminar</th></tr></thead>
          <tbody id="tablePermisos"></tbody>
        </table>
      </div>
    </div>

  </div><!-- /content -->
</div><!-- /main -->

<!-- ========== MODALES ========== -->

<!-- Mesa -->
<div class="modal-overlay" id="modalMesa">
  <div class="modal">
    <h3 id="tituloModalMesa">Nueva Mesa</h3>
    <div class="form-grid">
      <div class="form-group"><label>Número</label><input id="mesaNumero" placeholder="01"></div>
      <div class="form-group"><label>Zona</label>
        <select id="mesaZona"><option value="salon">Salón</option><option value="terraza">Terraza</option><option value="vip">VIP</option><option value="bar">Bar</option></select>
      </div>
      <div class="form-group"><label>Capacidad</label><input id="mesaCapacidad" type="number" value="4" min="1"></div>
    </div>
    <div class="modal-btns">
      <button class="btn btn-cancel" onclick="cerrarModal('modalMesa')">Cancelar</button>
      <button class="btn btn-primary" onclick="saveMesa()">Guardar</button>
    </div>
  </div>
</div>

<!-- Categoría -->
<div class="modal-overlay" id="modalCategoria">
  <div class="modal">
    <h3 id="tituloModalCategoria">Nueva Categoría</h3>
    <div class="form-grid">
      <div class="form-group"><label>Nombre</label><input id="catNombre" placeholder="Entradas"></div>
      <div class="form-group"><label>Área</label>
        <select id="catArea"><option value="cocina">Cocina</option><option value="bar">Bar</option><option value="postres">Postres</option><option value="otros">Otros</option></select>
      </div>
      <div class="form-group"><label>Icono (emoji)</label><input id="catIcono" placeholder="🍽️" maxlength="10"></div>
      <div class="form-group"><label>Color</label><input id="catColor" type="color" value="#ff6b35" style="padding:6px;height:44px"></div>
      <div class="form-group"><label>Orden</label><input id="catOrden" type="number" value="0" min="0"></div>
    </div>
    <div class="modal-btns">
      <button class="btn btn-cancel" onclick="cerrarModal('modalCategoria')">Cancelar</button>
      <button class="btn btn-primary" onclick="saveCategoria()">Guardar</button>
    </div>
  </div>
</div>

<!-- Plato -->
<div class="modal-overlay" id="modalPlato">
  <div class="modal">
    <h3 id="tituloModalPlato">Nuevo Plato</h3>
    <div class="form-grid">
      <div class="form-group full"><label>Nombre</label><input id="platoNombre" placeholder="Lomo Saltado"></div>
      <div class="form-group"><label>Categoría</label><select id="platoCatModal"></select></div>
      <div class="form-group"><label>Precio (S/)</label><input id="platoPrecio" type="number" step="0.10" min="0"></div>
      <div class="form-group"><label>Tiempo prep. (min)</label><input id="platoTiempo" type="number" value="15" min="1"></div>
      <div class="form-group full"><label>Descripción</label><textarea id="platoDesc" placeholder="Descripción opcional..."></textarea></div>
    </div>
    <div class="modal-btns">
      <button class="btn btn-cancel" onclick="cerrarModal('modalPlato')">Cancelar</button>
      <button class="btn btn-primary" onclick="savePlato()">Guardar</button>
    </div>
  </div>
</div>

<!-- Insumo -->
<div class="modal-overlay" id="modalInsumo">
  <div class="modal">
    <h3 id="tituloModalInsumo">Nuevo Insumo</h3>
    <div class="form-grid">
      <div class="form-group full"><label>Nombre</label><input id="insumoNombre" placeholder="Carne de res"></div>
      <div class="form-group"><label>Unidad</label>
        <select id="insumoUnidad"><option value="kg">kg</option><option value="g">g</option><option value="lt">lt</option><option value="ml">ml</option><option value="unidad">unidad</option><option value="botella">botella</option><option value="caja">caja</option></select>
      </div>
      <div class="form-group"><label>Categoría</label>
        <select id="insumoCat"><option value="carnes">Carnes</option><option value="verduras">Verduras</option><option value="abarrotes">Abarrotes</option><option value="bebidas">Bebidas</option><option value="lacteos">Lácteos</option><option value="frutas">Frutas</option><option value="otros">Otros</option></select>
      </div>
      <div class="form-group"><label>Stock inicial</label><input id="insumoStock" type="number" step="0.001" value="0" min="0"></div>
      <div class="form-group"><label>Stock mínimo</label><input id="insumoMin" type="number" step="0.001" value="0" min="0"></div>
      <div class="form-group"><label>Costo unitario (S/)</label><input id="insumoCosto" type="number" step="0.0001" value="0" min="0"></div>
    </div>
    <div class="modal-btns">
      <button class="btn btn-cancel" onclick="cerrarModal('modalInsumo')">Cancelar</button>
      <button class="btn btn-primary" onclick="saveInsumo()">Guardar</button>
    </div>
  </div>
</div>

<!-- Receta -->
<div class="modal-overlay" id="modalReceta">
  <div class="modal modal-lg">
    <h3>Editar Receta: <span id="recetaNombrePlato"></span></h3>
    <div style="margin-bottom:12px">
      <div style="display:grid;grid-template-columns:1fr 100px auto;gap:8px;margin-bottom:8px">
        <select id="recetaInsumoSel" style="background:var(--surface2);border:1px solid var(--border);border-radius:10px;padding:10px;color:var(--text);font-family:'DM Sans',sans-serif"></select>
        <input id="recetaCantidad" type="number" step="0.001" min="0.001" placeholder="Cant." style="text-align:center">
        <button class="btn btn-primary" onclick="addIngrediente()">+ Agregar</button>
      </div>
      <div id="listaReceta"></div>
    </div>
    <div class="modal-btns">
      <button class="btn btn-cancel" onclick="cerrarModal('modalReceta')">Cancelar</button>
      <button class="btn btn-primary" onclick="saveReceta()">💾 Guardar Receta</button>
    </div>
  </div>
</div>

<!-- Kardex Entry -->
<div class="modal-overlay" id="modalKardexEntry">
  <div class="modal">
    <h3>Movimiento de Stock</h3>
    <div class="form-grid">
      <div class="form-group full"><label>Insumo</label><select id="kardexInsumo"></select></div>
      <div class="form-group"><label>Tipo</label>
        <select id="kardexTipo"><option value="entrada">Entrada</option><option value="merma">Merma</option><option value="ajuste">Ajuste (valor absoluto)</option><option value="salida">Salida manual</option></select>
      </div>
      <div class="form-group"><label>Cantidad</label><input id="kardexCantidad" type="number" step="0.001" min="0" value="0"></div>
      <div class="form-group"><label>Costo unitario (S/)</label><input id="kardexCosto" type="number" step="0.0001" value="0"></div>
      <div class="form-group full"><label>Motivo / Observación</label><input id="kardexObs" placeholder="Compra, ajuste de inventario..."></div>
    </div>
    <div class="modal-btns">
      <button class="btn btn-cancel" onclick="cerrarModal('modalKardexEntry')">Cancelar</button>
      <button class="btn btn-primary" onclick="saveKardexEntry()">Registrar</button>
    </div>
  </div>
</div>

<!-- Proveedor -->
<div class="modal-overlay" id="modalProveedor">
  <div class="modal">
    <h3 id="tituloModalProveedor">Nuevo Proveedor</h3>
    <div class="form-grid">
      <div class="form-group full"><label>Nombre</label><input id="provNombre" placeholder="Distribuidora Lima SAC"></div>
      <div class="form-group"><label>RUC</label><input id="provRuc" placeholder="20123456789" maxlength="11"></div>
      <div class="form-group"><label>Teléfono</label><input id="provTel" placeholder="987654321"></div>
      <div class="form-group"><label>Contacto</label><input id="provContacto" placeholder="Juan Pérez"></div>
      <div class="form-group"><label>Email</label><input id="provEmail" type="email" placeholder="ventas@dist.com"></div>
      <div class="form-group"><label>Categoría</label>
        <select id="provCat"><option value="carnes">Carnes</option><option value="verduras">Verduras</option><option value="bebidas">Bebidas</option><option value="abarrotes">Abarrotes</option><option value="descartables">Descartables</option><option value="general">General</option></select>
      </div>
      <div class="form-group"><label>Condición de pago</label>
        <select id="provCondPago"><option value="contado">Contado</option><option value="credito_7">Crédito 7 días</option><option value="credito_15">Crédito 15 días</option><option value="credito_30">Crédito 30 días</option></select>
      </div>
      <div class="form-group full"><label>Dirección</label><input id="provDir" placeholder="Av. Los Olivos 123, Lima"></div>
    </div>
    <div class="modal-btns">
      <button class="btn btn-cancel" onclick="cerrarModal('modalProveedor')">Cancelar</button>
      <button class="btn btn-primary" onclick="saveProveedor()">Guardar</button>
    </div>
  </div>
</div>

<!-- Compra -->
<div class="modal-overlay" id="modalCompra">
  <div class="modal modal-lg">
    <h3 id="tituloModalCompra">Nueva Orden de Compra</h3>
    <div class="form-grid">
      <div class="form-group"><label>Proveedor</label><select id="compraProveedor"></select></div>
      <div class="form-group"><label>N° Factura / Guía</label><input id="compraFactura" placeholder="F001-00123"></div>
      <div class="form-group full"><label>Observación</label><input id="compraObs" placeholder="Observación opcional"></div>
    </div>
    <hr class="divider">
    <div style="font-size:13px;font-weight:600;margin-bottom:8px;color:var(--muted)">ÍTEMS DE COMPRA</div>
    <div style="display:grid;grid-template-columns:1fr 90px 110px auto;gap:8px;margin-bottom:8px">
      <select id="compraItemInsumo" style="background:var(--surface2);border:1px solid var(--border);border-radius:10px;padding:10px;color:var(--text);font-family:'DM Sans',sans-serif"></select>
      <input id="compraItemCantidad" type="number" step="0.001" min="0" placeholder="Cant." style="text-align:center">
      <input id="compraItemPrecio" type="number" step="0.0001" min="0" placeholder="S/ Unit." style="text-align:center">
      <button class="btn btn-primary btn-sm" onclick="addCompraItem()">+ Agregar</button>
    </div>
    <div id="listaCompraItems" style="max-height:220px;overflow-y:auto"></div>
    <div style="text-align:right;margin-top:8px;font-weight:700;font-size:16px">Total: S/ <span id="compraTotal">0.00</span></div>
    <div class="modal-btns">
      <button class="btn btn-cancel" onclick="cerrarModal('modalCompra')">Cancelar</button>
      <button class="btn btn-primary" onclick="saveCompra()">Guardar Compra</button>
    </div>
  </div>
</div>

<!-- Delivery -->
<div class="modal-overlay" id="modalDelivery">
  <div class="modal modal-lg">
    <h3>Nuevo Pedido Delivery</h3>
    <div class="form-grid">
      <div class="form-group"><label>Nombre Cliente</label><input id="dlvNombre" placeholder="María García"></div>
      <div class="form-group"><label>Teléfono</label><input id="dlvTelefono" placeholder="987654321"></div>
      <div class="form-group full"><label>Dirección</label><input id="dlvDireccion" placeholder="Av. Los Olivos 123"></div>
      <div class="form-group full"><label>Referencia</label><input id="dlvReferencia" placeholder="Frente al parque, color azul"></div>
      <div class="form-group"><label>Método de pago</label>
        <select id="dlvMetodoPago"><option value="efectivo">Efectivo</option><option value="yape">Yape</option><option value="plin">Plin</option><option value="tarjeta">Tarjeta</option></select>
      </div>
      <div class="form-group"><label>Tiempo estimado (min)</label><input id="dlvTiempo" type="number" value="30" min="5"></div>
      <div class="form-group full"><label>Notas</label><textarea id="dlvNotas" placeholder="Instrucciones especiales..."></textarea></div>
    </div>
    <hr class="divider">
    <div style="font-size:13px;font-weight:600;margin-bottom:8px;color:var(--muted)">ÍTEMS DEL PEDIDO</div>
    <div style="display:grid;grid-template-columns:1fr 80px auto;gap:8px;margin-bottom:8px">
      <select id="dlvPlatoSel" style="background:var(--surface2);border:1px solid var(--border);border-radius:10px;padding:10px;color:var(--text);font-family:'DM Sans',sans-serif"></select>
      <input id="dlvCantidad" type="number" value="1" min="1" style="text-align:center">
      <button class="btn btn-primary btn-sm" onclick="addDlvItem()">+ Agregar</button>
    </div>
    <div id="listaDlvItems" style="max-height:180px;overflow-y:auto"></div>
    <div style="text-align:right;margin-top:8px;font-weight:700">Total: S/ <span id="dlvTotal">0.00</span></div>
    <div class="modal-btns">
      <button class="btn btn-cancel" onclick="cerrarModal('modalDelivery')">Cancelar</button>
      <button class="btn btn-primary" onclick="saveDelivery()">Crear Pedido</button>
    </div>
  </div>
</div>

<!-- Cliente CRM -->
<div class="modal-overlay" id="modalCliente">
  <div class="modal">
    <h3 id="tituloModalCliente">Nuevo Cliente</h3>
    <div class="form-grid">
      <div class="form-group"><label>Nombre</label><input id="cliNombre" placeholder="María"></div>
      <div class="form-group"><label>Apellido</label><input id="cliApellido" placeholder="García"></div>
      <div class="form-group"><label>Teléfono</label><input id="cliTelefono" placeholder="987654321"></div>
      <div class="form-group"><label>Email</label><input id="cliEmail" type="email" placeholder="maria@email.com"></div>
      <div class="form-group"><label>Cumpleaños</label><input id="cliCumple" type="date"></div>
      <div class="form-group"><label>Dirección</label><input id="cliDireccion" placeholder="Av. Lima 123"></div>
      <div class="form-group full"><label>Notas</label><textarea id="cliNotas" placeholder="Cliente VIP, prefiere mesa exterior..."></textarea></div>
    </div>
    <div class="modal-btns">
      <button class="btn btn-cancel" onclick="cerrarModal('modalCliente')">Cancelar</button>
      <button class="btn btn-primary" onclick="saveCliente()">Guardar</button>
    </div>
  </div>
</div>

<!-- Menú del Día -->
<div class="modal-overlay" id="modalMenuDia">
  <div class="modal">
    <h3 id="tituloModalMenuDia">Configurar Menú del Día</h3>
    <div class="form-grid">
      <div class="form-group"><label>Fecha</label><input id="menuFecha" type="date" value="<?= date('Y-m-d') ?>"></div>
      <div class="form-group"><label>Nombre</label><input id="menuNombre" value="Menu del Dia" placeholder="Menu del Dia"></div>
      <div class="form-group full"><label>Entrada</label><select id="menuEntrada"><option value="">-- Sin entrada --</option></select></div>
      <div class="form-group full"><label>Plato de Fondo</label><select id="menuFondo"><option value="">-- Sin fondo --</option></select></div>
      <div class="form-group full"><label>Bebida</label><select id="menuBebida"><option value="">-- Sin bebida --</option></select></div>
      <div class="form-group"><label>Precio (S/)</label><input id="menuPrecio" type="number" step="0.50" min="0" value="15"></div>
      <div class="form-group"><label>Límite cantidad (0=sin límite)</label><input id="menuLimite" type="number" min="0" value="0"></div>
    </div>
    <div class="modal-btns">
      <button class="btn btn-cancel" onclick="cerrarModal('modalMenuDia')">Cancelar</button>
      <button class="btn btn-primary" onclick="saveMenuDia()">Guardar</button>
    </div>
  </div>
</div>

<!-- Reserva -->
<div class="modal-overlay" id="modalReserva">
  <div class="modal">
    <h3 id="tituloModalReserva">Nueva Reserva</h3>
    <div class="form-grid">
      <div class="form-group"><label>Cliente</label><input id="resNombre" placeholder="Ana Torres"></div>
      <div class="form-group"><label>Teléfono</label><input id="resTel" placeholder="987654321"></div>
      <div class="form-group"><label>Email</label><input id="resEmail" type="email" placeholder="ana@email.com"></div>
      <div class="form-group"><label>Personas</label><input id="resPersonas" type="number" value="2" min="1"></div>
      <div class="form-group"><label>Fecha</label><input id="resFecha" type="date" value="<?= date('Y-m-d') ?>"></div>
      <div class="form-group"><label>Hora</label><input id="resHora" type="time" value="12:00"></div>
      <div class="form-group"><label>Mesa (opcional)</label><select id="resMesa"><option value="">-- Sin asignar --</option></select></div>
      <div class="form-group full"><label>Observaciones</label><textarea id="resObs" placeholder="Cumpleaños, mesa decorada..."></textarea></div>
    </div>
    <div class="modal-btns">
      <button class="btn btn-cancel" onclick="cerrarModal('modalReserva')">Cancelar</button>
      <button class="btn btn-primary" onclick="saveReserva()">Guardar</button>
    </div>
  </div>
</div>

<!-- Usuario -->
<div class="modal-overlay" id="modalUsuario">
  <div class="modal">
    <h3 id="tituloModalUsuario">Nuevo Usuario</h3>
    <div class="form-grid">
      <div class="form-group"><label>Nombre</label><input id="usuNombre" placeholder="Juan"></div>
      <div class="form-group"><label>Apellido</label><input id="usuApellido" placeholder="Pérez"></div>
      <div class="form-group"><label>Usuario (login)</label><input id="usuUsuario" placeholder="jperez"></div>
      <div class="form-group"><label>Password</label><input id="usuPassword" type="password" placeholder="Min. 6 caracteres"></div>
      <div class="form-group"><label>Rol</label>
        <select id="usuRol"><option value="mozo">Mozo</option><option value="cocina">Cocina</option><option value="bar">Bar</option><option value="cajero">Cajero</option><option value="almacen">Almacén</option><option value="compras">Compras</option><option value="supervisor">Supervisor</option><option value="administrador">Administrador</option></select>
      </div>
      <div class="form-group"><label>Teléfono</label><input id="usuTel" placeholder="987654321"></div>
    </div>
    <div class="modal-btns">
      <button class="btn btn-cancel" onclick="cerrarModal('modalUsuario')">Cancelar</button>
      <button class="btn btn-primary" onclick="saveUsuario()">Guardar</button>
    </div>
  </div>
</div>

<!-- Modal Sucursal -->
<div class="modal-overlay" id="modalSucursal">
  <div class="modal">
    <h3 id="tituloModalSucursal">Nueva Sucursal</h3>
    <div class="form-grid">
      <div class="form-group full"><label>Nombre</label><input id="sucNombre" placeholder="Sucursal Centro"></div>
      <div class="form-group full"><label>Dirección</label><input id="sucDireccion" placeholder="Av. Lima 456"></div>
      <div class="form-group"><label>Teléfono</label><input id="sucTelefono" placeholder="01-234-5678"></div>
      <div class="form-group"><label>Email</label><input id="sucEmail" type="email"></div>
      <div class="form-group"><label>RUC</label><input id="sucRuc" placeholder="20123456789" maxlength="11"></div>
    </div>
    <div class="modal-btns">
      <button class="btn btn-cancel" onclick="cerrarModal('modalSucursal')">Cancelar</button>
      <button class="btn btn-primary" onclick="saveSucursal()">Guardar</button>
    </div>
  </div>
</div>

<!-- Modal Impresora -->
<div class="modal-overlay" id="modalImpresora">
  <div class="modal" style="max-width:600px">
    <h3 id="tituloModalImpresora">Nueva Impresora</h3>
    <div class="form-grid">
      <div class="form-group"><label>Nombre</label><input id="impNombre" placeholder="Impresora Caja"></div>
      <div class="form-group"><label>Tipo</label>
        <select id="impTipo"><option value="tickets">Tickets</option><option value="cocina">Cocina</option><option value="caja">Caja</option><option value="etiquetas">Etiquetas</option></select>
      </div>
      <div class="form-group"><label>Ancho papel (chars)</label><input id="impAncho" type="number" value="48" min="32" max="80"></div>
    </div>
    <div class="form-group full" style="margin-top:12px"><label>Cabecera del ticket (se imprime en cada documento)</label>
      <textarea id="impCabecera" style="min-height:120px;font-family:monospace;font-size:13px" placeholder="RESTAURANTE EL SABOR
Av. Principal 123 - Lima
Tel: 01-234-5678
RUC: 20123456789
------------------------------"></textarea>
    </div>
    <div class="form-group full" style="margin-top:8px"><label>Pie del ticket</label>
      <textarea id="impPie" style="min-height:80px;font-family:monospace;font-size:13px" placeholder="Gracias por su visita
Cuenta BCP: 123-456789-0-12
www.mirestaurante.com"></textarea>
    </div>
    <div class="modal-btns">
      <button class="btn btn-cancel" onclick="cerrarModal('modalImpresora')">Cancelar</button>
      <button class="btn btn-primary" onclick="saveImpresora()">Guardar</button>
    </div>
  </div>
</div>

<!-- Modal Pago Proveedor -->
<div class="modal-overlay" id="modalPagoProveedor">
  <div class="modal">
    <h3>Registrar Pago a Proveedor</h3>
    <input type="hidden" id="pagoProvCuentaId">
    <div id="pagoProvInfo" style="background:var(--surface2);border-radius:10px;padding:12px;margin-bottom:16px;font-size:14px"></div>
    <div class="form-grid">
      <div class="form-group"><label>Monto a Pagar (S/)</label><input id="pagoProvMonto" type="number" step="0.01" min="0.01"></div>
      <div class="form-group"><label>Fecha de Pago</label><input id="pagoProvFecha" type="date"></div>
      <div class="form-group"><label>Método</label>
        <select id="pagoProvMetodo"><option value="transferencia">Transferencia</option><option value="cheque">Cheque</option><option value="efectivo">Efectivo</option><option value="tarjeta">Tarjeta</option></select>
      </div>
      <div class="form-group"><label>Referencia / N° operación</label><input id="pagoProvRef" placeholder="OP-123456"></div>
    </div>
    <div class="modal-btns">
      <button class="btn btn-cancel" onclick="cerrarModal('modalPagoProveedor')">Cancelar</button>
      <button class="btn btn-primary" onclick="registrarPagoProveedor()">💳 Registrar Pago</button>
    </div>
  </div>
</div>

<div class="toast" id="toast"></div>

<script>
const BASE = '<?= BASE_URL ?>';
let editId = null;
let recetaItems = [];
let compraItems = [];
let dlvItems    = [];

// ============================================================
// NAVIGATION
// ============================================================
function showPage(page, navEl) {
  document.querySelectorAll('.page-section').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
  const el = document.getElementById('page-' + page);
  if (el) el.classList.add('active');
  navEl.classList.add('active');
  document.getElementById('pageTitle').textContent = navEl.textContent.trim();
  loadPageData(page);
}
function loadPageData(page) {
  const map = {
    dashboard: loadDashboard, mesas: loadMesas, categorias: loadCategorias,
    platos: loadPlatos, insumos: loadInsumos, recetas: loadRecetaSelects,
    kardex: loadKardex, proveedores: loadProveedores, compras: loadCompras,
    delivery: loadDelivery, crm: loadCRM, menu_dia: loadMenuDia,
    reportes: loadReportes, reservas: loadReservas, usuarios: loadUsuarios,
    configuracion: loadConfiguracion, sucursales: loadSucursales,
    impresoras: loadImpresoras, permisos: loadPermisos,
  };
  if (map[page]) map[page]();
}

// ============================================================
// DASHBOARD
// ============================================================
async function loadDashboard() {
  try {
    const d = await get('/api/reportes.php?action=dashboard');
    document.getElementById('dashStats').innerHTML = `
      <div class="stat-card"><div class="s-label">Ventas Hoy</div><div class="s-value" style="color:var(--green)">S/ ${num(d.ventas_hoy?.total||0)}</div></div>
      <div class="stat-card"><div class="s-label">Órdenes Hoy</div><div class="s-value" style="color:var(--blue)">${d.ventas_hoy?.ordenes||0}</div></div>
      <div class="stat-card"><div class="s-label">Mesas Ocupadas</div><div class="s-value" style="color:var(--orange)">${d.mesas_activas||0}</div></div>
      <div class="stat-card"><div class="s-label">En Cocina</div><div class="s-value" style="color:var(--yellow)">${d.items_cocina||0}</div></div>
      <div class="stat-card"><div class="s-label">Stock Bajo</div><div class="s-value" style="color:var(--red)">${d.stock_bajo||0}</div></div>`;
    document.getElementById('ultimasOrdenes').innerHTML = (d.ultimas_ordenes||[]).map(o =>
      `<tr><td>Mesa ${o.mesa||'—'}</td><td>${o.mozo||'—'}</td><td>S/ ${num(o.total||0)}</td>
       <td><span class="badge badge-${badgeColor(o.estado)}">${o.estado}</span></td></tr>`).join('');
    document.getElementById('stockBajo').innerHTML = '(Ver módulo Insumos)';
  } catch(e) { console.error(e); }
}

// ============================================================
// MESAS
// ============================================================
async function loadMesas() {
  const d = await get('/api/mesas.php');
  document.getElementById('tableMesas').innerHTML = d.map(m => `
    <tr><td><strong>${m.numero}</strong></td><td>${m.zona||''}</td><td>${m.capacidad}</td>
    <td><span class="badge badge-${badgeColor(m.estado)}">${m.estado}</span></td>
    <td class="action-btns">
      <button class="btn btn-sm" onclick="editMesa(${m.id})">✏️</button>
      <button class="btn btn-sm btn-danger" onclick="deleteMesa(${m.id})">🗑</button>
    </td></tr>`).join('');
}
async function saveMesa() {
  const body = {numero:v('mesaNumero'), zona:v('mesaZona'), capacidad:v('mesaCapacidad'), action: editId ? 'update':'create'};
  if (editId) body.id = editId;
  const r = await post('/api/mesas.php', body);
  if (r.success) { cerrarModal('modalMesa'); loadMesas(); toast('✅ Mesa guardada'); editId=null; }
  else toast('❌ '+(r.error||'Error'), 'error');
}
async function editMesa(id) {
  const d = await get('/api/mesas.php?id='+id);
  editId=id; set('mesaNumero',d.numero); set('mesaZona',d.zona||'salon'); set('mesaCapacidad',d.capacidad);
  document.getElementById('tituloModalMesa').textContent='Editar Mesa';
  abrirModal('modalMesa');
}
async function deleteMesa(id) {
  if (!confirm('¿Eliminar esta mesa?')) return;
  await post('/api/mesas.php', {action:'delete', id});
  loadMesas(); toast('🗑 Mesa eliminada');
}

// ============================================================
// CATEGORÍAS
// ============================================================
async function loadCategorias() {
  const d = await get('/api/categorias.php');
  document.getElementById('tableCategorias').innerHTML = d.map(c => `
    <tr><td style="font-size:22px">${c.icono||''}</td><td><strong>${c.nombre}</strong></td>
    <td>${c.area}</td>
    <td><span style="display:inline-block;width:18px;height:18px;border-radius:4px;background:${c.color||'#888'};vertical-align:middle"></span> ${c.color||''}</td>
    <td>${c.orden}</td>
    <td><span class="badge ${c.activo?'badge-green':'badge-red'}">${c.activo?'Activo':'Inactivo'}</span></td>
    <td class="action-btns">
      <button class="btn btn-sm" onclick="editCategoria(${c.id})">✏️</button>
      <button class="btn btn-sm" onclick="toggleCategoria(${c.id})">🔄</button>
    </td></tr>`).join('');
}
async function saveCategoria() {
  const body = {nombre:v('catNombre'), area:v('catArea'), icono:v('catIcono'), color:v('catColor'), orden:v('catOrden'), action: editId?'update':'create'};
  if (editId) body.id = editId;
  const r = await post('/api/categorias.php', body);
  if (r.success) { cerrarModal('modalCategoria'); loadCategorias(); toast('✅ Categoría guardada'); editId=null; }
  else toast('❌ '+(r.error||'Error'), 'error');
}
async function editCategoria(id) {
  const d = await get('/api/categorias.php?id='+id);
  editId=id; set('catNombre',d.nombre); set('catArea',d.area); set('catIcono',d.icono||''); set('catColor',d.color||'#ff6b35'); set('catOrden',d.orden);
  document.getElementById('tituloModalCategoria').textContent='Editar Categoría';
  abrirModal('modalCategoria');
}
async function toggleCategoria(id) {
  await post('/api/categorias.php', {action:'toggle', id});
  loadCategorias(); toast('🔄 Estado actualizado');
}

// ============================================================
// PLATOS
// ============================================================
async function loadPlatos() {
  const cat = document.getElementById('filterCat')?.value || '';
  const url = '/api/platos.php' + (cat ? '?categoria='+cat : '');
  const [d, cats] = await Promise.all([get(url), get('/api/categorias.php')]);
  const catMap = {};
  cats.forEach(c => catMap[c.id] = c.nombre);
  const sel = document.getElementById('filterCat');
  if (sel && sel.options.length <= 1)
    sel.innerHTML = '<option value="">Todas las categorías</option>' + cats.map(c=>`<option value="${c.id}">${c.nombre}</option>`).join('');
  const sel2 = document.getElementById('platoCatModal');
  if (sel2) sel2.innerHTML = cats.map(c=>`<option value="${c.id}">${c.nombre}</option>`).join('');
  document.getElementById('tablePlatos').innerHTML = d.map(p => `
    <tr><td><strong>${p.nombre}</strong></td><td>${catMap[p.id_categoria]||''}</td>
    <td>S/ ${num(p.precio)}</td><td>${p.categoria_nombre||''}</td><td>${p.tiempo_prep||'—'} min</td>
    <td><span class="badge ${p.disponible?'badge-green':'badge-red'}">${p.disponible?'Sí':'No'}</span></td>
    <td class="action-btns">
      <button class="btn btn-sm" onclick="toggleDisponible(${p.id},${p.disponible})">🔄</button>
      <button class="btn btn-sm" onclick="editPlato(${p.id})">✏️</button>
    </td></tr>`).join('');
}
async function savePlato() {
  const body = {nombre:v('platoNombre'), id_categoria:v('platoCatModal'), precio:v('platoPrecio'), tiempo_prep:v('platoTiempo'), descripcion:v('platoDesc'), action: editId?'update':'create'};
  if (editId) body.id = editId;
  const r = await post('/api/platos.php', body);
  if (r.success) { cerrarModal('modalPlato'); loadPlatos(); toast('✅ Plato guardado'); editId=null; }
  else toast('❌ '+(r.error||'Error'), 'error');
}
async function editPlato(id) {
  const d = await get('/api/platos.php?id='+id);
  editId=id; set('platoNombre',d.nombre); set('platoCatModal',d.id_categoria); set('platoPrecio',d.precio); set('platoTiempo',d.tiempo_prep||15); set('platoDesc',d.descripcion||'');
  document.getElementById('tituloModalPlato').textContent='Editar Plato';
  abrirModal('modalPlato');
}
async function toggleDisponible(id) {
  await post('/api/platos.php', {action:'toggle_disponible', id});
  loadPlatos();
}

// ============================================================
// INSUMOS
// ============================================================
async function loadInsumos() {
  const d = await get('/api/insumos.php');
  document.getElementById('tableInsumos').innerHTML = d.map(i => `
    <tr><td><strong>${i.nombre}</strong></td><td>${i.unidad}</td>
    <td class="${parseFloat(i.stock_actual)<=parseFloat(i.stock_minimo)?'stock-low':'stock-ok'}">${i.stock_actual}</td>
    <td>${i.stock_minimo}</td><td>S/ ${parseFloat(i.costo_unitario).toFixed(4)}</td><td>${i.categoria||''}</td>
    <td class="action-btns"><button class="btn btn-sm" onclick="editInsumo(${i.id})">✏️</button></td></tr>`).join('');
  const sel = document.getElementById('kardexInsumo');
  if (sel) sel.innerHTML = d.map(i=>`<option value="${i.id}">${i.nombre} (${i.unidad})</option>`).join('');
  const sel2 = document.getElementById('compraItemInsumo');
  if (sel2) sel2.innerHTML = d.map(i=>`<option value="${i.id}" data-unidad="${i.unidad}" data-costo="${i.costo_unitario}">${i.nombre} (${i.unidad})</option>`).join('');
  populateRecetaInsumos(d);
  populateKardexFiltro(d);
}
async function saveInsumo() {
  const body = {nombre:v('insumoNombre'), unidad:v('insumoUnidad'), categoria:v('insumoCat'), stock_inicial:v('insumoStock'), stock_minimo:v('insumoMin'), costo_unitario:v('insumoCosto'), action: editId?'update':'create'};
  if (editId) body.id = editId;
  const r = await post('/api/insumos.php', body);
  if (r.success) { cerrarModal('modalInsumo'); loadInsumos(); toast('✅ Insumo guardado'); editId=null; }
  else toast('❌ '+(r.error||'Error'), 'error');
}
async function editInsumo(id) {
  const d = await get('/api/insumos.php?id='+id);
  editId=id; set('insumoNombre',d.nombre); set('insumoUnidad',d.unidad); set('insumoCat',d.categoria||'otros'); set('insumoStock',d.stock_actual); set('insumoMin',d.stock_minimo); set('insumoCosto',d.costo_unitario);
  document.getElementById('tituloModalInsumo').textContent='Editar Insumo';
  abrirModal('modalInsumo');
}
async function saveKardexEntry() {
  const r = await post('/api/insumos.php', {action:'kardex_entry', id_insumo:v('kardexInsumo'), tipo:v('kardexTipo'), cantidad:v('kardexCantidad'), costo_unitario:v('kardexCosto'), motivo:v('kardexObs')});
  if (r.success) { cerrarModal('modalKardexEntry'); loadInsumos(); toast('✅ Stock actualizado'); }
  else toast('❌ '+(r.error||'Error'), 'error');
}

// ============================================================
// RECETAS
// ============================================================
let insumosCache = [];
async function loadRecetaSelects() {
  const [platos, insumos] = await Promise.all([get('/api/platos.php'), get('/api/insumos.php')]);
  insumosCache = insumos;
  const sel = document.getElementById('recetaPlatoFiltro');
  sel.innerHTML = '<option value="">-- Selecciona un plato --</option>' + platos.map(p=>`<option value="${p.id}">${p.nombre}</option>`).join('');
  populateRecetaInsumos(insumos);
}
function populateRecetaInsumos(insumos) {
  const sel = document.getElementById('recetaInsumoSel');
  if (sel) sel.innerHTML = insumos.map(i=>`<option value="${i.id}" data-unidad="${i.unidad}" data-costo="${i.costo_unitario}">${i.nombre} (${i.unidad})</option>`).join('');
}
async function loadReceta(idPlato) {
  if (!idPlato) { document.getElementById('boxReceta').style.display='none'; document.getElementById('btnEditarReceta').style.display='none'; return; }
  const d = await get('/api/platos.php?id='+idPlato);
  document.getElementById('boxReceta').style.display='block';
  document.getElementById('btnEditarReceta').style.display='';
  document.getElementById('recetaTitulo').textContent = 'Receta: ' + d.nombre;
  renderRecetaTable(d.receta||[]);
}
function renderRecetaTable(items) {
  let costo = 0;
  document.getElementById('tableReceta').innerHTML = items.map(r => {
    const c = parseFloat(r.cantidad) * parseFloat(r.costo_unitario||0);
    costo += c;
    return `<tr><td>${r.insumo_nombre}</td><td>${r.cantidad}</td><td>${r.unidad}</td><td>S/ ${c.toFixed(4)}</td></tr>`;
  }).join('') || '<tr><td colspan="4" style="text-align:center;color:var(--muted);padding:16px">Sin ingredientes</td></tr>';
  document.getElementById('costoRecetaTotal').textContent = 'S/ ' + costo.toFixed(4);
}
function abrirModalReceta() {
  const idPlato = document.getElementById('recetaPlatoFiltro').value;
  if (!idPlato) return toast('Selecciona un plato primero', 'error');
  document.getElementById('recetaNombrePlato').textContent = document.getElementById('recetaPlatoFiltro').selectedOptions[0].text;
  // Load current recipe into editor
  get('/api/platos.php?id='+idPlato).then(d => {
    recetaItems = (d.receta||[]).map(r => ({id_insumo:r.id_insumo, nombre:r.insumo_nombre, unidad:r.unidad, cantidad:r.cantidad, costo:r.costo_unitario||0}));
    renderRecetaEditor();
    abrirModal('modalReceta');
  });
}
// Override the button
document.getElementById('btnEditarReceta').onclick = abrirModalReceta;

function renderRecetaEditor() {
  document.getElementById('listaReceta').innerHTML = recetaItems.length ? recetaItems.map((r,i) => `
    <div class="receta-row" style="background:var(--surface2);border-radius:8px;padding:8px 12px;margin-bottom:6px">
      <span>${r.nombre} (${r.unidad})</span>
      <input type="number" step="0.001" min="0" value="${r.cantidad}" onchange="recetaItems[${i}].cantidad=this.value" style="width:90px;background:var(--surface3);border:1px solid var(--border);border-radius:8px;padding:6px;color:var(--text);text-align:center">
      <button class="btn btn-sm btn-danger" onclick="recetaItems.splice(${i},1);renderRecetaEditor()">✕</button>
    </div>`).join('') : '<div style="color:var(--muted);text-align:center;padding:12px">Sin ingredientes aún</div>';
}
function addIngrediente() {
  const sel = document.getElementById('recetaInsumoSel');
  const opt = sel.selectedOptions[0];
  const cant = parseFloat(document.getElementById('recetaCantidad').value);
  if (!cant || cant <= 0) return toast('Ingresa una cantidad válida','error');
  const existe = recetaItems.findIndex(r => r.id_insumo == sel.value);
  if (existe >= 0) { recetaItems[existe].cantidad = cant; }
  else { recetaItems.push({id_insumo:sel.value, nombre:opt.text, unidad:opt.dataset.unidad, cantidad:cant, costo:parseFloat(opt.dataset.costo||0)}); }
  document.getElementById('recetaCantidad').value='';
  renderRecetaEditor();
}
async function saveReceta() {
  const idPlato = document.getElementById('recetaPlatoFiltro').value;
  const r = await post('/api/platos.php', {action:'save_receta', id_plato:idPlato, ingredientes:recetaItems.map(r=>({id_insumo:r.id_insumo,cantidad:r.cantidad}))});
  if (r.success) { cerrarModal('modalReceta'); loadReceta(idPlato); toast('✅ Receta guardada'); }
  else toast('❌ '+(r.error||'Error'), 'error');
}

// ============================================================
// KARDEX
// ============================================================
function populateKardexFiltro(insumos) {
  const sel = document.getElementById('kardexInsumoFiltro');
  if (sel) {
    sel.innerHTML = '<option value="">Todos los insumos</option>' + insumos.map(i=>`<option value="${i.id}">${i.nombre}</option>`).join('');
  }
}
async function loadKardex() {
  const id = document.getElementById('kardexInsumoFiltro')?.value || '';
  const url = id ? '/api/insumos.php?action=kardex&id='+id : '/api/insumos.php?action=kardex_all';
  try {
    const d = await get(url);
    const items = Array.isArray(d) ? d : (d.kardex || []);
    document.getElementById('tableKardex').innerHTML = items.map(k => `
      <tr><td>${k.created_at||''}</td><td>${k.insumo_nombre||''}</td>
      <td><span class="badge badge-${k.tipo==='entrada'?'green':k.tipo==='merma'?'red':'blue'}">${k.tipo}</span></td>
      <td>${k.cantidad}</td><td>${k.stock_resultante}</td><td>${k.motivo||''}</td><td>${k.usuario_nombre||''}</td></tr>`).join('')
      || '<tr><td colspan="7" style="text-align:center;color:var(--muted);padding:20px">Sin movimientos</td></tr>';
  } catch(e) { console.error(e); }
}

// ============================================================
// PROVEEDORES
// ============================================================
async function loadProveedores() {
  const d = await get('/api/proveedores.php');
  document.getElementById('tableProveedores').innerHTML = d.map(p => `
    <tr><td><strong>${p.nombre}</strong></td><td>${p.ruc||''}</td><td>${p.telefono||''}</td>
    <td>${p.categoria}</td><td>${p.contacto||''}</td><td>${p.condicion_pago||''}</td>
    <td class="action-btns"><button class="btn btn-sm" onclick="editProveedor(${p.id})">✏️</button></td></tr>`).join('');
  // Populate compra proveedor select
  const sel = document.getElementById('compraProveedor');
  if (sel) sel.innerHTML = d.map(p=>`<option value="${p.id}">${p.nombre}</option>`).join('');
}
async function saveProveedor() {
  const body = {nombre:v('provNombre'), ruc:v('provRuc'), telefono:v('provTel'), contacto:v('provContacto'), email:v('provEmail'), categoria:v('provCat'), condicion_pago:v('provCondPago'), direccion:v('provDir'), action: editId?'update':'create'};
  if (editId) body.id = editId;
  const r = await post('/api/proveedores.php', body);
  if (r.success) { cerrarModal('modalProveedor'); loadProveedores(); toast('✅ Proveedor guardado'); editId=null; }
  else toast('❌ '+(r.error||'Error'), 'error');
}
async function editProveedor(id) {
  const d = await get('/api/proveedores.php?id='+id);
  editId=id; set('provNombre',d.nombre); set('provRuc',d.ruc||''); set('provTel',d.telefono||''); set('provContacto',d.contacto||''); set('provEmail',d.email||''); set('provCat',d.categoria||'general'); set('provCondPago',d.condicion_pago||'contado'); set('provDir',d.direccion||'');
  document.getElementById('tituloModalProveedor').textContent='Editar Proveedor';
  abrirModal('modalProveedor');
}

// ============================================================
// COMPRAS
// ============================================================
async function loadCompras() {
  const fi = document.getElementById('compraFechaIni')?.value || '';
  const ff = document.getElementById('compraFechaFin')?.value || '';
  const url = '/api/compras.php' + (fi&&ff ? '?fecha_ini='+fi+'&fecha_fin='+ff : '');
  const d = await get(url);
  document.getElementById('tableCompras').innerHTML = d.map(c => `
    <tr><td><strong>${c.numero}</strong></td><td>${c.proveedor_nombre}</td><td>${c.fecha}</td>
    <td>${c.numero_factura||'—'}</td><td>S/ ${num(c.total||0)}</td>
    <td><span class="badge badge-${c.estado==='recibida'?'green':c.estado==='cancelada'?'red':'yellow'}">${c.estado}</span></td>
    <td class="action-btns">
      ${c.estado==='pendiente'?`<button class="btn btn-sm" onclick="recibirCompra(${c.id})" style="color:var(--green)">✅ Recibir</button>
      <button class="btn btn-sm btn-danger" onclick="cancelarCompra(${c.id})">❌</button>`:''}
    </td></tr>`).join('') || '<tr><td colspan="7" style="text-align:center;color:var(--muted);padding:20px">Sin compras</td></tr>';
}
function addCompraItem() {
  const sel = document.getElementById('compraItemInsumo');
  const opt = sel.selectedOptions[0];
  const cant = parseFloat(document.getElementById('compraItemCantidad').value)||0;
  const precio = parseFloat(document.getElementById('compraItemPrecio').value)||0;
  if (!cant || !precio) return toast('Ingresa cantidad y precio','error');
  compraItems.push({id_insumo:sel.value, nombre:opt.text, cantidad:cant, precio_unitario:precio});
  document.getElementById('compraItemCantidad').value='';
  document.getElementById('compraItemPrecio').value='';
  renderCompraItems();
}
function renderCompraItems() {
  let total = 0;
  document.getElementById('listaCompraItems').innerHTML = compraItems.map((c,i) => {
    const sub = c.cantidad * c.precio_unitario; total += sub;
    return `<div style="display:grid;grid-template-columns:1fr auto auto auto;gap:8px;align-items:center;background:var(--surface2);border-radius:8px;padding:8px 12px;margin-bottom:6px">
      <span>${c.nombre}</span><span>${c.cantidad}</span><span>S/ ${c.precio_unitario}</span>
      <button class="btn btn-sm btn-danger" onclick="compraItems.splice(${i},1);renderCompraItems()">✕</button>
    </div>`;
  }).join('');
  document.getElementById('compraTotal').textContent = total.toFixed(2);
}
async function saveCompra() {
  if (!compraItems.length) return toast('Agrega al menos un ítem','error');
  const r = await post('/api/compras.php', {action:'create', id_proveedor:v('compraProveedor'), numero_factura:v('compraFactura'), observacion:v('compraObs'), items:compraItems});
  if (r.success) { cerrarModal('modalCompra'); compraItems=[]; renderCompraItems(); loadCompras(); toast('✅ Compra creada'); }
  else toast('❌ '+(r.error||'Error'), 'error');
}
async function recibirCompra(id) {
  if (!confirm('¿Confirmar recepción? Esto ingresará el stock al inventario.')) return;
  const r = await post('/api/compras.php', {action:'recibir', id});
  if (r.success) {
    loadCompras();
    const msg = r.genero_cuenta_pagar ? '✅ Stock ingresado. Se generó cuenta por pagar automáticamente.' : '✅ Stock ingresado al inventario.';
    toast(msg);
  } else toast('❌ '+(r.error||'Error'), 'error');
}
async function cancelarCompra(id) {
  if (!confirm('¿Cancelar esta compra?')) return;
  await post('/api/compras.php', {action:'cancelar', id});
  loadCompras(); toast('🗑 Compra cancelada');
}
// Populate compra selects when modal opens
document.getElementById('modalCompra').addEventListener('click', () => {});

// ============================================================
// DELIVERY
// ============================================================
let platosCache = [];
async function loadDelivery() {
  const estado = document.getElementById('deliveryEstadoFiltro')?.value || '';
  const url = '/api/delivery.php' + (estado ? '?estado='+estado : '');
  const d = await get(url);
  const estadoNext = {recibido:'en_cocina', en_cocina:'listo', listo:'en_camino', en_camino:'entregado'};
  document.getElementById('tableDelivery').innerHTML = (Array.isArray(d)?d:[]).map(dl => `
    <tr><td><strong>${dl.numero||''}</strong></td><td>${dl.nombre_cliente}</td>
    <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${dl.direccion}</td>
    <td>${dl.metodo_pago||''}</td>
    <td><span class="badge badge-${dl.estado==='entregado'?'green':dl.estado==='cancelado'?'red':'blue'}">${dl.estado}</span></td>
    <td class="action-btns">
      ${estadoNext[dl.estado] ? `<button class="btn btn-sm" onclick="avanzarDelivery(${dl.id},'${estadoNext[dl.estado]}')">▶️ ${estadoNext[dl.estado]}</button>` : ''}
      ${dl.estado!=='entregado'&&dl.estado!=='cancelado' ? `<button class="btn btn-sm btn-danger" onclick="avanzarDelivery(${dl.id},'cancelado')">❌</button>` : ''}
    </td></tr>`).join('')
    || '<tr><td colspan="6" style="text-align:center;color:var(--muted);padding:20px">Sin pedidos delivery</td></tr>';
}
async function avanzarDelivery(id, estado) {
  const r = await post('/api/delivery.php', {action:'update_estado', id, estado});
  if (r.success) { loadDelivery(); toast('✅ Estado actualizado'); }
  else toast('❌ '+(r.error||'Error'), 'error');
}
async function prepareDeliveryModal() {
  if (!platosCache.length) platosCache = await get('/api/platos.php');
  const sel = document.getElementById('dlvPlatoSel');
  sel.innerHTML = platosCache.map(p=>`<option value="${p.id}" data-precio="${p.precio}">${p.nombre} - S/ ${num(p.precio)}</option>`).join('');
}
function addDlvItem() {
  const sel = document.getElementById('dlvPlatoSel');
  const opt = sel.selectedOptions[0];
  const cant = parseInt(document.getElementById('dlvCantidad').value)||1;
  const precio = parseFloat(opt.dataset.precio)||0;
  dlvItems.push({id_plato:sel.value, nombre:opt.text, cantidad:cant, precio});
  renderDlvItems();
}
function renderDlvItems() {
  let total = 0;
  document.getElementById('listaDlvItems').innerHTML = dlvItems.map((d,i) => {
    const sub = d.cantidad * d.precio; total += sub;
    return `<div style="display:grid;grid-template-columns:1fr auto auto auto;gap:8px;align-items:center;background:var(--surface2);border-radius:8px;padding:8px 12px;margin-bottom:6px">
      <span>${d.nombre}</span><span>x${d.cantidad}</span><span>S/ ${num(sub)}</span>
      <button class="btn btn-sm btn-danger" onclick="dlvItems.splice(${i},1);renderDlvItems()">✕</button>
    </div>`;
  }).join('');
  document.getElementById('dlvTotal').textContent = total.toFixed(2);
}
async function saveDelivery() {
  if (!dlvItems.length) return toast('Agrega al menos un ítem','error');
  if (!v('dlvNombre')) return toast('Ingresa nombre del cliente','error');
  if (!v('dlvDireccion')) return toast('Ingresa la dirección','error');
  const r = await post('/api/delivery.php', {action:'create', nombre_cliente:v('dlvNombre'), telefono:v('dlvTelefono'), direccion:v('dlvDireccion'), referencia:v('dlvReferencia'), metodo_pago:v('dlvMetodoPago'), tiempo_estimado:v('dlvTiempo'), notas:v('dlvNotas'), items:dlvItems});
  if (r.success) { cerrarModal('modalDelivery'); dlvItems=[]; renderDlvItems(); loadDelivery(); toast('✅ Pedido delivery creado'); }
  else toast('❌ '+(r.error||'Error'), 'error');
}

// ============================================================
// CRM
// ============================================================
async function loadCRM() {
  const d = await get('/api/clientes.php');
  document.getElementById('tableCRM').innerHTML = (Array.isArray(d)?d:[]).map(c => `
    <tr><td><strong>${c.nombre} ${c.apellido||''}</strong></td><td>${c.telefono||''}</td>
    <td>${c.email||''}</td><td>${c.cumpleanos||''}</td><td style="max-width:150px">${c.notas||''}</td>
    <td class="action-btns"><button class="btn btn-sm" onclick="editCliente(${c.id})">✏️</button></td></tr>`).join('')
    || '<tr><td colspan="6" style="text-align:center;color:var(--muted);padding:20px">Sin clientes registrados</td></tr>';
}
async function saveCliente() {
  const body = {nombre:v('cliNombre'), apellido:v('cliApellido'), telefono:v('cliTelefono'), email:v('cliEmail'), cumpleanos:v('cliCumple'), direccion:v('cliDireccion'), notas:v('cliNotas'), action: editId?'update':'create'};
  if (editId) body.id = editId;
  const r = await post('/api/clientes.php', body);
  if (r.success) { cerrarModal('modalCliente'); loadCRM(); toast('✅ Cliente guardado'); editId=null; }
  else toast('❌ '+(r.error||'Error'), 'error');
}
async function editCliente(id) {
  const d = await get('/api/clientes.php?id='+id);
  editId=id; set('cliNombre',d.nombre); set('cliApellido',d.apellido||''); set('cliTelefono',d.telefono||''); set('cliEmail',d.email||''); set('cliCumple',d.cumpleanos||''); set('cliDireccion',d.direccion||''); set('cliNotas',d.notas||'');
  document.getElementById('tituloModalCliente').textContent='Editar Cliente';
  abrirModal('modalCliente');
}

// ============================================================
// MENÚ DEL DÍA
// ============================================================
async function loadMenuDia() {
  const fecha = v('fechaMenu') || '<?= date('Y-m-d') ?>';
  const d = await get('/api/menu_dia.php?fecha='+fecha);
  document.getElementById('tableMenuDia').innerHTML = (Array.isArray(d)?d:[]).map(m => `
    <tr><td><strong>${m.nombre||'Menu del Dia'}</strong></td>
    <td>${m.entrada_nombre||'—'}</td><td>${m.fondo_nombre||'—'}</td><td>${m.bebida_nombre||'—'}</td>
    <td>S/ ${num(m.precio)}</td><td>${m.cantidad_vendida||0}/${m.cantidad_limite||'∞'}</td>
    <td><span class="badge ${m.activo?'badge-green':'badge-red'}">${m.activo?'Activo':'Inactivo'}</span></td>
    <td class="action-btns">
      <button class="btn btn-sm" onclick="toggleMenuDia(${m.id})">🔄</button>
      <button class="btn btn-sm btn-danger" onclick="deleteMenuDia(${m.id})">🗑</button>
    </td></tr>`).join('')
    || '<tr><td colspan="8" style="text-align:center;color:var(--muted);padding:20px">Sin menú configurado para esta fecha</td></tr>';
}
async function prepareMenuDiaModal() {
  const platos = await get('/api/platos.php');
  const opts = '<option value="">-- Sin asignar --</option>' + platos.map(p=>`<option value="${p.id}">${p.nombre}</option>`).join('');
  document.getElementById('menuEntrada').innerHTML = opts;
  document.getElementById('menuFondo').innerHTML = opts;
  document.getElementById('menuBebida').innerHTML = opts;
}
async function saveMenuDia() {
  const limite = parseInt(v('menuLimite'))||0;
  const r = await post('/api/menu_dia.php', {action:'save', fecha:v('menuFecha'), nombre:v('menuNombre')||'Menu del Dia', id_plato_entrada:v('menuEntrada')||null, id_plato_fondo:v('menuFondo')||null, id_plato_bebida:v('menuBebida')||null, precio:v('menuPrecio'), cantidad_limite: limite>0 ? limite : null, activo:1});
  if (r.success) { cerrarModal('modalMenuDia'); loadMenuDia(); toast('✅ Menú guardado'); }
  else toast('❌ '+(r.error||'Error'), 'error');
}
async function toggleMenuDia(id) {
  await post('/api/menu_dia.php', {action:'toggle', id});
  loadMenuDia();
}
async function deleteMenuDia(id) {
  if (!confirm('¿Eliminar este menú?')) return;
  await post('/api/menu_dia.php', {action:'delete', id});
  loadMenuDia(); toast('🗑 Menú eliminado');
}

// ============================================================
// REPORTES
// ============================================================
async function loadReportes() {
  const fi = v('repFechaIni') || '<?= date('Y-m-d') ?>';
  const ff = v('repFechaFin') || '<?= date('Y-m-d') ?>';
  const d = await get('/api/reportes.php?action=ventas&fecha_ini='+fi+'&fecha_fin='+ff);
  const total = (d.ventas_dia||[]).reduce((a,b)=>a+parseFloat(b.total||0),0);
  const ords  = (d.ventas_dia||[]).reduce((a,b)=>a+parseInt(b.ordenes||0),0);
  document.getElementById('repStats').innerHTML = `
    <div class="stat-card"><div class="s-label">Total Ventas</div><div class="s-value" style="color:var(--green)">S/ ${total.toFixed(2)}</div></div>
    <div class="stat-card"><div class="s-label">Órdenes</div><div class="s-value" style="color:var(--blue)">${ords}</div></div>
    <div class="stat-card"><div class="s-label">Ticket Prom.</div><div class="s-value" style="color:var(--yellow)">S/ ${ords>0?(total/ords).toFixed(2):'0.00'}</div></div>`;
  document.getElementById('rTopPlatos').innerHTML = (d.top_platos||[]).map(p =>
    `<tr><td>${p.nombre}</td><td>${p.vendidos}</td><td>S/ ${num(p.total)}</td></tr>`).join('');
  document.getElementById('rPorMozo').innerHTML = (d.por_mozo||[]).map(m =>
    `<tr><td>${m.nombre}</td><td>${m.ordenes}</td><td>S/ ${num(m.total)}</td></tr>`).join('');
}

// ============================================================
// RESERVAS
// ============================================================
async function loadReservas() {
  const fecha = v('fechaReservas') || '<?= date('Y-m-d') ?>';
  const d = await get('/api/reservas.php?fecha='+fecha);
  document.getElementById('tableReservas').innerHTML = (Array.isArray(d)?d:[]).map(r => `
    <tr><td>${r.fecha_hora ? r.fecha_hora.substring(11,16) : ''}</td><td>${r.nombre_cliente}</td>
    <td>${r.personas}</td><td>${r.mesa_numero||'—'}</td>
    <td><span class="badge badge-${r.estado==='confirmada'?'green':r.estado==='cancelada'?'red':'yellow'}">${r.estado}</span></td>
    <td>${r.observaciones||''}</td>
    <td class="action-btns">
      ${r.estado==='pendiente'?`<button class="btn btn-sm" onclick="confirmarReserva(${r.id})">✅</button>`:''}
      ${r.estado!=='cancelada'&&r.estado!=='completada'?`<button class="btn btn-sm btn-danger" onclick="cancelarReserva(${r.id})">❌</button>`:''}
    </td></tr>`).join('')
    || '<tr><td colspan="7" style="text-align:center;color:var(--muted);padding:20px">Sin reservas para esta fecha</td></tr>';
}
async function saveReserva() {
  const fecha = v('resFecha');
  const hora  = v('resHora');
  if (!fecha||!hora) return toast('Selecciona fecha y hora','error');
  const r = await post('/api/reservas.php', {action:'create', nombre_cliente:v('resNombre'), telefono:v('resTel'), email:v('resEmail'), fecha_hora:fecha+' '+hora+':00', personas:v('resPersonas'), id_mesa:v('resMesa')||null, observaciones:v('resObs')});
  if (r.success) { cerrarModal('modalReserva'); loadReservas(); toast('✅ Reserva creada'); }
  else toast('❌ '+(r.error||'Error'), 'error');
}
async function confirmarReserva(id) {
  await post('/api/reservas.php', {action:'update_estado', id, estado:'confirmada'});
  loadReservas(); toast('✅ Reserva confirmada');
}
async function cancelarReserva(id) {
  if (!confirm('¿Cancelar esta reserva?')) return;
  await post('/api/reservas.php', {action:'update_estado', id, estado:'cancelada'});
  loadReservas(); toast('🗑 Reserva cancelada');
}

// ============================================================
// USUARIOS
// ============================================================
async function loadUsuarios() {
  const d = await get('/api/usuarios.php');
  document.getElementById('tableUsuarios').innerHTML = d.map(u => `
    <tr><td><strong>${u.nombre} ${u.apellido||''}</strong></td><td>${u.usuario}</td>
    <td><span class="badge badge-blue">${u.rol}</span></td>
    <td><span class="badge ${u.activo?'badge-green':'badge-red'}">${u.activo?'Activo':'Inactivo'}</span></td>
    <td class="action-btns">
      <button class="btn btn-sm" onclick="editUsuario(${u.id})">✏️</button>
      <button class="btn btn-sm" onclick="toggleUsuario(${u.id})">🔄</button>
    </td></tr>`).join('');
  // Populate reserva mesa select
  const mesas = await get('/api/mesas.php');
  const resMesa = document.getElementById('resMesa');
  if (resMesa) resMesa.innerHTML = '<option value="">-- Sin asignar --</option>' + mesas.filter(m=>m.estado==='libre'||m.estado==='reservada').map(m=>`<option value="${m.id}">Mesa ${m.numero} (${m.zona})</option>`).join('');
}
async function saveUsuario() {
  const body = {nombre:v('usuNombre'), apellido:v('usuApellido'), usuario:v('usuUsuario'), password:v('usuPassword'), rol:v('usuRol'), telefono:v('usuTel'), action: editId?'update':'create'};
  if (editId) body.id = editId;
  const r = await post('/api/usuarios.php', body);
  if (r.success) { cerrarModal('modalUsuario'); loadUsuarios(); toast('✅ Usuario guardado'); editId=null; }
  else toast('❌ '+(r.error||'Error'), 'error');
}
async function editUsuario(id) {
  const d = await get('/api/usuarios.php?id='+id);
  editId=id; set('usuNombre',d.nombre); set('usuApellido',d.apellido||''); set('usuUsuario',d.usuario); set('usuRol',d.rol); set('usuTel',d.telefono||''); set('usuPassword','');
  document.getElementById('tituloModalUsuario').textContent='Editar Usuario';
  abrirModal('modalUsuario');
}
async function toggleUsuario(id) {
  const r = await post('/api/usuarios.php', {action:'toggle', id});
  if (r.success) { loadUsuarios(); toast('🔄 Estado actualizado'); }
  else toast('❌ '+(r.error||'Error'), 'error');
}

// ============================================================
// HELPERS
// ============================================================
async function get(path) {
  const r = await fetch(BASE + path, {credentials:'same-origin'});
  const text = await r.text();
  try {
    const json = JSON.parse(text);
    if (json && json.error === 'No autenticado') { window.location = BASE + '/index.php'; return []; }
    return json;
  } catch(e) {
    console.error('GET '+path+' returned non-JSON:', text.substring(0,200));
    return [];
  }
}
async function post(path, body) {
  const r = await fetch(BASE + path, {method:'POST', credentials:'same-origin', headers:{'Content-Type':'application/json'}, body:JSON.stringify(body)});
  const text = await r.text();
  try {
    const json = JSON.parse(text);
    if (json && json.error === 'No autenticado') { window.location = BASE + '/index.php'; return {success:false}; }
    return json;
  } catch(e) {
    console.error('POST '+path+' returned non-JSON:', text.substring(0,200));
    return {success:false, error:'Error del servidor'};
  }
}
function v(id) { const el=document.getElementById(id); return el?el.value:''; }
function set(id, val) { const el=document.getElementById(id); if(el) el.value=val; }
function num(n) { return parseFloat(n||0).toFixed(2); }
function badgeColor(s) { return {libre:'green',ocupada:'blue',reservada:'yellow',por_limpiar:'purple',abierta:'yellow',en_proceso:'blue',lista:'green',pagada:'purple',cancelada:'red',confirmada:'green',completada:'green',pendiente:'yellow',recibido:'yellow',en_cocina:'blue',listo:'green',en_camino:'purple',entregado:'green'}[s]||'yellow'; }

function abrirModal(id) {
  editId = null;
  document.getElementById(id).style.display='flex';
  // Pre-load selects for certain modals
  if (id==='modalDelivery') { prepareDeliveryModal(); dlvItems=[]; renderDlvItems(); }
  if (id==='modalMenuDia')  { prepareMenuDiaModal(); }
  if (id==='modalCompra')   { loadProveedores(); loadInsumos(); compraItems=[]; renderCompraItems(); }
}
function cerrarModal(id) {
  document.getElementById(id).style.display='none';
  editId=null;
}
document.querySelectorAll('.modal-overlay').forEach(m => {
  m.addEventListener('click', e => { if (e.target===m) cerrarModal(m.id); });
});

function filterTable(tbodyId, q) {
  document.getElementById(tbodyId)?.querySelectorAll('tr').forEach(r =>
    r.style.display = r.textContent.toLowerCase().includes(q.toLowerCase()) ? '' : 'none');
}

function toast(msg, type='success') {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.className = 'toast ' + type + ' show';
  setTimeout(()=>t.classList.remove('show'), 3000);
}

// INIT
const hoy2 = new Date().toISOString().split('T')[0];
if (document.getElementById('compraFechaIni')) { set('compraFechaIni', new Date(new Date().setDate(1)).toISOString().split('T')[0]); set('compraFechaFin', hoy2); }
if (document.getElementById('repFechaIni')) { set('repFechaIni', hoy2); set('repFechaFin', hoy2); }

// ============================================================
// TABS
// ============================================================
let repTabAtivo = 'ventas_dia';

function switchTab(group, tab, btn) {
  document.querySelectorAll(`[id^="${group}-"]`).forEach(p => p.classList.remove('active'));
  const panel = document.getElementById(`${group}-${tab}`);
  if (panel) panel.classList.add('active');
  btn.closest('.tabs').querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  repTabAtivo = tab;
  // Auto-load sub-tabs
  if (tab === 'cuentas_pagar') loadCuentasPagar();
  if (tab === 'costos_prom') loadCostosPromedio();
}

// ============================================================
// REPORTES EXPANDIDOS
// ============================================================
async function loadReporteActivo() { await loadReportes(); }

async function loadReportes() {
  const fi = v('repFechaIni') || hoy2;
  const ff = v('repFechaFin') || hoy2;

  if (repTabAtivo === 'ventas_dia') {
    const d = await get(`/api/reportes.php?action=ventas_dia&fecha_ini=${fi}&fecha_fin=${ff}`);
    const tot = d.totales || {};
    document.getElementById('kpiVentasDia').innerHTML = `
      <div class="kpi"><div class="k-lbl">Total Ventas</div><div class="k-val" style="color:var(--green)">S/ ${num(tot.total_ventas)}</div></div>
      <div class="kpi"><div class="k-lbl">Órdenes</div><div class="k-val" style="color:var(--blue)">${tot.total_ordenes||0}</div></div>
      <div class="kpi"><div class="k-lbl">Ticket Promedio</div><div class="k-val" style="color:var(--yellow)">S/ ${num(tot.ticket_prom)}</div></div>`;
    document.getElementById('tbVentasDia').innerHTML = (d.detalle||[]).map(r => `
      <tr><td>${r.fecha}</td><td>${r.ordenes}</td><td><strong>S/ ${num(r.total)}</strong></td>
      <td>S/ ${num(r.descuentos)}</td><td>S/ ${num(r.igv)}</td><td>S/ ${num(r.propinas)}</td>
      <td>S/ ${num(r.ticket_promedio)}</td></tr>`).join('') || '<tr><td colspan="7" style="text-align:center;color:var(--muted);padding:20px">Sin datos en el rango seleccionado</td></tr>';

  } else if (repTabAtivo === 'ventas_hora') {
    const d = await get(`/api/reportes.php?action=ventas_hora&fecha_ini=${fi}&fecha_fin=${ff}`);
    const maxVal = Math.max(...(d||[]).map(r => parseFloat(r.total||0)), 1);
    document.getElementById('chartHoras').innerHTML = Array.from({length:24},(_,h) => {
      const row = (d||[]).find(r => parseInt(r.hora) === h);
      const total = parseFloat(row?.total||0);
      const pct = (total/maxVal*100).toFixed(1);
      return `<div class="bar-row">
        <span style="text-align:right;color:var(--muted)">${String(h).padStart(2,'0')}:00</span>
        <div class="bar-track"><div class="bar-fill blue" style="width:${pct}%"></div></div>
        <span>S/ ${num(total)}</span></div>`;
    }).join('');

  } else if (repTabAtivo === 'ventas_mozo') {
    const d = await get(`/api/reportes.php?action=ventas_mozo&fecha_ini=${fi}&fecha_fin=${ff}`);
    const totalG = (d||[]).reduce((a,r)=>a+parseFloat(r.total_ventas||0),0);
    document.getElementById('tbVentasMozo').innerHTML = (d||[]).map(r => {
      const pct = totalG > 0 ? (parseFloat(r.total_ventas)/totalG*100).toFixed(1) : 0;
      return `<tr><td><strong>${r.nombre} ${r.apellido||''}</strong></td><td>${r.ordenes}</td>
        <td>S/ ${num(r.total_ventas)}</td><td>S/ ${num(r.ticket_promedio)}</td>
        <td>S/ ${num(r.propinas)}</td>
        <td><div style="display:flex;align-items:center;gap:8px"><div class="bar-track" style="flex:1"><div class="bar-fill" style="width:${pct}%"></div></div><span>${pct}%</span></div></td></tr>`;
    }).join('') || '<tr><td colspan="6" style="text-align:center;color:var(--muted);padding:20px">Sin datos</td></tr>';

  } else if (repTabAtivo === 'top_platos') {
    const d = await get(`/api/reportes.php?action=top_platos&fecha_ini=${fi}&fecha_fin=${ff}`);
    const maxV = Math.max(...(d||[]).map(r=>parseInt(r.vendidos||0)),1);
    document.getElementById('chartPlatos').innerHTML = (d||[]).slice(0,10).map(r => {
      const pct = (parseInt(r.vendidos)/maxV*100).toFixed(1);
      return `<div class="bar-row"><span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${r.nombre}</span>
        <div class="bar-track"><div class="bar-fill green" style="width:${pct}%"></div></div>
        <span>${r.vendidos} uds</span></div>`;
    }).join('');
    document.getElementById('tbTopPlatos').innerHTML = (d||[]).map((r,i) => `
      <tr><td><strong>#${i+1} ${r.nombre}</strong></td><td>${r.categoria}</td>
      <td>${r.vendidos}</td><td>S/ ${num(r.ingresos)}</td><td>${r.en_ordenes}</td></tr>`).join('');

  } else if (repTabAtivo === 'rentabilidad') {
    const d = await get(`/api/reportes.php?action=rentabilidad&fecha_ini=${fi}&fecha_fin=${ff}`);
    const tot = d.totales || {};
    document.getElementById('kpiRentabilidad').innerHTML = `
      <div class="kpi"><div class="k-lbl">Ingresos</div><div class="k-val" style="color:var(--green)">S/ ${num(tot.ingresos)}</div></div>
      <div class="kpi"><div class="k-lbl">Costo Total</div><div class="k-val" style="color:var(--red)">S/ ${num(tot.costo)}</div></div>
      <div class="kpi"><div class="k-lbl">Utilidad</div><div class="k-val" style="color:var(--blue)">S/ ${num(tot.utilidad)}</div></div>
      <div class="kpi"><div class="k-lbl">Margen Global</div><div class="k-val" style="color:var(--yellow)">${tot.margen_global||0}%</div></div>`;
    document.getElementById('tbRentabilidad').innerHTML = (d.platos||[]).map(r => {
      const margenColor = r.margen_pct > 60 ? 'var(--green)' : r.margen_pct > 30 ? 'var(--yellow)' : 'var(--red)';
      return `<tr><td><strong>${r.nombre}</strong><br><small style="color:var(--muted)">${r.categoria}</small></td>
        <td>S/ ${num(r.precio_venta)}</td><td>S/ ${num(r.costo_receta)}</td>
        <td>${r.vendidos}</td><td>S/ ${num(r.ingresos)}</td>
        <td>S/ ${num(r.costo_total)}</td><td>S/ ${num(r.utilidad)}</td>
        <td><strong style="color:${margenColor}">${r.margen_pct}%</strong></td></tr>`;
    }).join('') || '<tr><td colspan="8" style="text-align:center;color:var(--muted);padding:20px">Sin datos (requiere recetas configuradas)</td></tr>';

  } else if (repTabAtivo === 'consumo') {
    const d = await get(`/api/reportes.php?action=consumo_insumos&fecha_ini=${fi}&fecha_fin=${ff}`);
    document.getElementById('tbConsumo').innerHTML = (d||[]).map(r => `
      <tr><td><strong>${r.nombre}</strong></td><td>${r.unidad}</td><td>${r.categoria}</td>
      <td style="color:var(--green)">${num(r.entradas)}</td>
      <td>${num(r.consumo)}</td>
      <td style="color:var(--red)">${num(r.merma)}</td>
      <td>S/ ${num(r.costo_consumo)}</td></tr>`).join('') || '<tr><td colspan="7" style="text-align:center;color:var(--muted);padding:20px">Sin movimientos en este período</td></tr>';

  } else if (repTabAtivo === 'inventario_val') {
    const d = await get('/api/reportes.php?action=inventario_valorizado');
    const tot = d.totales || {};
    document.getElementById('kpiInventario').innerHTML = `
      <div class="kpi"><div class="k-lbl">Valor Total Inventario</div><div class="k-val" style="color:var(--accent)">S/ ${num(tot.valor_total)}</div></div>
      <div class="kpi"><div class="k-lbl">Ítems</div><div class="k-val">${tot.total_items||0}</div></div>
      <div class="kpi"><div class="k-lbl">Sin Stock</div><div class="k-val" style="color:var(--red)">${tot.sin_stock||0}</div></div>
      <div class="kpi"><div class="k-lbl">Stock Bajo</div><div class="k-val" style="color:var(--yellow)">${tot.stock_bajo||0}</div></div>`;
    document.getElementById('tbInventarioVal').innerHTML = (d.items||[]).map(r => {
      const cls = r.estado_stock==='sin_stock'?'red-row':r.estado_stock==='stock_bajo'?'yellow-row':'';
      const badge = r.estado_stock==='sin_stock'?'<span class="badge badge-red">Sin stock</span>':r.estado_stock==='stock_bajo'?'<span class="badge badge-yellow">Stock bajo</span>':'<span class="badge badge-green">OK</span>';
      return `<tr class="${cls}"><td><strong>${r.nombre}</strong></td><td>${r.unidad}</td><td>${r.categoria}</td>
        <td>${r.stock_actual}</td><td>${r.stock_minimo}</td>
        <td>S/ ${parseFloat(r.costo_unitario).toFixed(4)}</td>
        <td><strong>S/ ${num(r.valor_total)}</strong></td><td>${badge}</td></tr>`;
    }).join('');

  } else if (repTabAtivo === 'tiempos') {
    const d = await get(`/api/reportes.php?action=tiempos_atencion&fecha_ini=${fi}&fecha_fin=${ff}`);
    const mesa = d.mesa || {};
    document.getElementById('kpiTiempos').innerHTML = `
      <div class="kpi"><div class="k-lbl">Tiempo Prom. por Mesa</div><div class="k-val" style="color:var(--blue)">${Math.round(mesa.tiempo_promedio_mesa||0)} min</div></div>
      <div class="kpi"><div class="k-lbl">Tiempo Mín.</div><div class="k-val" style="color:var(--green)">${Math.round(mesa.tiempo_min||0)} min</div></div>
      <div class="kpi"><div class="k-lbl">Tiempo Máx.</div><div class="k-val" style="color:var(--red)">${Math.round(mesa.tiempo_max||0)} min</div></div>`;
    document.getElementById('tbTiempos').innerHTML = (d.por_plato||[]).map(r => `
      <tr><td><strong>${r.nombre}</strong></td><td>${r.categoria}</td><td>${r.total}</td>
      <td><strong>${Math.round(r.tiempo_promedio||0)}</strong></td>
      <td style="color:var(--green)">${Math.round(r.tiempo_min||0)}</td>
      <td style="color:var(--red)">${Math.round(r.tiempo_max||0)}</td></tr>`).join('') || '<tr><td colspan="6" style="text-align:center;color:var(--muted);padding:20px">Sin datos</td></tr>';
  }
}

function exportarCSV() {
  const tabla = document.querySelector('.tab-panel.active table');
  if (!tabla) return toast('No hay tabla para exportar','error');
  const rows = [...tabla.querySelectorAll('tr')].map(r =>
    [...r.querySelectorAll('th,td')].map(c => '"'+c.textContent.replace(/"/g,'""')+'"').join(','));
  const csv = rows.join('\n');
  const a = document.createElement('a');
  a.href = 'data:text/csv;charset=utf-8,' + encodeURIComponent('\uFEFF'+csv);
  a.download = 'reporte_'+repTabAtivo+'_'+v('repFechaIni')+'.csv';
  a.click();
}

// ============================================================
// CUENTAS POR PAGAR
// ============================================================
async function loadCuentasPagar() {
  const estado = v('filtroCuentasPagar');
  const url = '/api/cuentas_pagar.php?action=list' + (estado ? '&estado='+estado : '');
  const [d, stats] = await Promise.all([get(url), get('/api/cuentas_pagar.php?action=resumen')]);
  document.getElementById('kpiCuentasPagar').innerHTML = `
    <div class="kpi"><div class="k-lbl">Saldo Pendiente</div><div class="k-val" style="color:var(--accent)">S/ ${num(stats?.total_saldo)}</div></div>
    <div class="kpi"><div class="k-lbl">Vencido</div><div class="k-val" style="color:var(--red)">S/ ${num(stats?.saldo_vencido)}</div></div>
    <div class="kpi"><div class="k-lbl">Vence en 7 días</div><div class="k-val" style="color:var(--yellow)">S/ ${num(stats?.vence_pronto)}</div></div>
    <div class="kpi"><div class="k-lbl">Cuentas activas</div><div class="k-val">${stats?.total_cuentas||0}</div></div>`;
  const estadoBadgeC = {pendiente:'yellow',parcial:'blue',pagada:'green',vencida:'red'};
  document.getElementById('tableCuentasPagar').innerHTML = (Array.isArray(d)?d:[]).map(c => `
    <tr class="${c.estado==='vencida'?'red-row':''}">
      <td><strong>${c.compra_numero||''}</strong></td><td>${c.proveedor_nombre}</td>
      <td>S/ ${num(c.monto_total)}</td><td>S/ ${num(c.monto_pagado)}</td>
      <td><strong>S/ ${num(c.saldo)}</strong></td>
      <td>${c.fecha_vencimiento||'—'}</td>
      <td><span class="badge badge-${estadoBadgeC[c.estado]||'yellow'}">${c.estado}</span></td>
      <td class="action-btns">
        ${c.estado!=='pagada'?`<button class="btn btn-sm btn-primary" onclick="abrirPagoProveedor(${c.id},${c.saldo},'${c.proveedor_nombre}')">💳 Pagar</button>`:''}
      </td></tr>`).join('') || '<tr><td colspan="8" style="text-align:center;color:var(--muted);padding:20px">Sin cuentas pendientes</td></tr>';
}

function abrirPagoProveedor(id, saldo, proveedor) {
  set('pagoProvCuentaId', id);
  set('pagoProvMonto', saldo);
  set('pagoProvFecha', new Date().toISOString().split('T')[0]);
  document.getElementById('pagoProvInfo').innerHTML = `<strong>${proveedor}</strong> — Saldo: <span style="color:var(--red);font-weight:700">S/ ${num(saldo)}</span>`;
  abrirModal('modalPagoProveedor');
}
async function registrarPagoProveedor() {
  const r = await post('/api/cuentas_pagar.php', {action:'registrar_pago', id_cuenta:v('pagoProvCuentaId'), monto:v('pagoProvMonto'), fecha:v('pagoProvFecha'), metodo:v('pagoProvMetodo'), referencia:v('pagoProvRef')});
  if (r.success) { cerrarModal('modalPagoProveedor'); loadCuentasPagar(); toast(`✅ Pago registrado. Saldo restante: S/ ${num(r.saldo_restante)}`); }
  else toast('❌ '+(r.error||'Error'), 'error');
}

// Costos promedio
async function loadCostosPromedio() {
  const insumos = await get('/api/insumos.php');
  document.getElementById('tbCostosProm').innerHTML = insumos.map(i => {
    const costo = parseFloat(i.costo_unitario||0);
    return `<tr><td><strong>${i.nombre}</strong></td><td>${i.unidad}</td>
      <td>S/ ${costo.toFixed(4)}</td>
      <td style="color:var(--muted);font-size:12px">Ver kardex</td>
      <td><span class="badge badge-blue">—</span></td></tr>`;
  }).join('');
}

// ============================================================
// CONFIGURACIÓN
// ============================================================
async function loadConfiguracion() {
  const d = await get('/api/configuracion.php?action=all');
  set('cfgNombre', d.nombre_restaurante||'');
  set('cfgSlogan', d.slogan||'');
  set('cfgTelefono', d.telefono||'');
  set('cfgCelular', d.celular||'');
  set('cfgDireccion', d.direccion||'');
  set('cfgEmail', d.email||'');
  set('cfgWeb', d.web||'');
  set('cfgRuc', d.ruc||'');
  set('cfgRazonSocial', d.razon_social||'');
  set('cfgIgv', d.igv||'18');
  set('cfgMoneda', d.moneda||'S/');
  set('cfgCuenta', d.cuenta_bancaria||'');
  set('cfgCci', d.cci||'');
  set('cfgPropina', d.propina_default||'0');
  set('cfgAlertaCocina', d.alerta_cocina_min||'15');
  set('cfgAlertaStock', d.alerta_stock||'1');
  set('cfgDescInventario', d.descuento_inventario_auto||'1');
  if (d.logo_base64) { const img=document.getElementById('logoPreview'); img.src=d.logo_base64; img.style.display='block'; }
}
function previewLogo(input) {
  const file = input.files[0]; if (!file) return;
  const reader = new FileReader();
  reader.onload = e => {
    const img = document.getElementById('logoPreview');
    img.src = e.target.result; img.style.display='block';
    post('/api/configuracion.php', {action:'save_logo', logo:e.target.result}).then(()=>toast('✅ Logo guardado'));
  };
  reader.readAsDataURL(file);
}
async function saveConfigRestaurante() {
  const r = await post('/api/configuracion.php', {action:'save_config', grupo:'restaurante', config:{nombre_restaurante:v('cfgNombre'),slogan:v('cfgSlogan'),telefono:v('cfgTelefono'),celular:v('cfgCelular'),direccion:v('cfgDireccion'),email:v('cfgEmail'),web:v('cfgWeb')}});
  if (r.success) toast('✅ Configuración guardada'); else toast('❌ Error','error');
}
async function saveConfigFiscal() {
  const r = await post('/api/configuracion.php', {action:'save_config', grupo:'fiscal', config:{ruc:v('cfgRuc'),razon_social:v('cfgRazonSocial'),igv:v('cfgIgv'),moneda:v('cfgMoneda'),cuenta_bancaria:v('cfgCuenta'),cci:v('cfgCci')}});
  if (r.success) toast('✅ Datos fiscales guardados'); else toast('❌ Error','error');
}
async function saveConfigSistema() {
  const r = await post('/api/configuracion.php', {action:'save_config', grupo:'sistema', config:{propina_default:v('cfgPropina'),alerta_cocina_min:v('cfgAlertaCocina'),alerta_stock:v('cfgAlertaStock'),descuento_inventario_auto:v('cfgDescInventario')}});
  if (r.success) toast('✅ Parámetros guardados'); else toast('❌ Error','error');
}

// ============================================================
// SUCURSALES
// ============================================================
async function loadSucursales() {
  const d = await get('/api/configuracion.php?action=sucursales');
  document.getElementById('tableSucursales').innerHTML = (Array.isArray(d)?d:[]).map(s => `
    <tr><td><strong>${s.nombre}</strong></td><td>${s.direccion||''}</td><td>${s.telefono||''}</td>
    <td>${s.ruc||''}</td><td>${s.email||''}</td>
    <td><span class="badge ${s.activo?'badge-green':'badge-red'}">${s.activo?'Activa':'Inactiva'}</span></td>
    <td class="action-btns">
      <button class="btn btn-sm" onclick="editSucursal(${s.id})">✏️</button>
      <button class="btn btn-sm" onclick="toggleSucursal(${s.id})">🔄</button>
    </td></tr>`).join('') || '<tr><td colspan="7" style="text-align:center;color:var(--muted);padding:20px">Sin sucursales registradas</td></tr>';
}
async function saveSucursal() {
  const body = {nombre:v('sucNombre'),direccion:v('sucDireccion'),telefono:v('sucTelefono'),email:v('sucEmail'),ruc:v('sucRuc'),action:'save_sucursal'};
  if (editId) body.id = editId;
  const r = await post('/api/configuracion.php', body);
  if (r.success) { cerrarModal('modalSucursal'); loadSucursales(); toast('✅ Sucursal guardada'); editId=null; }
  else toast('❌ '+(r.error||'Error'),'error');
}
async function editSucursal(id) {
  const d = (await get('/api/configuracion.php?action=sucursales')).find(s=>s.id==id);
  if (!d) return; editId=id;
  set('sucNombre',d.nombre); set('sucDireccion',d.direccion||''); set('sucTelefono',d.telefono||''); set('sucEmail',d.email||''); set('sucRuc',d.ruc||'');
  document.getElementById('tituloModalSucursal').textContent='Editar Sucursal';
  abrirModal('modalSucursal');
}
async function toggleSucursal(id) {
  await post('/api/configuracion.php', {action:'toggle_sucursal',id}); loadSucursales();
}

// ============================================================
// IMPRESORAS
// ============================================================
async function loadImpresoras() {
  const d = await get('/api/configuracion.php?action=impresoras');
  document.getElementById('tableImpresoras').innerHTML = (Array.isArray(d)?d:[]).map(imp => `
    <tr><td><strong>${imp.nombre}</strong></td><td><span class="badge badge-blue">${imp.tipo}</span></td>
    <td>${imp.ancho_papel} chars</td>
    <td><span class="badge badge-green">Configurada</span></td>
    <td class="action-btns">
      <button class="btn btn-sm" onclick="editImpresora(${imp.id})">✏️</button>
      <button class="btn btn-sm btn-danger" onclick="deleteImpresora(${imp.id})">🗑</button>
    </td></tr>`).join('') || '<tr><td colspan="5" style="text-align:center;color:var(--muted);padding:20px">Sin impresoras configuradas</td></tr>';
}
async function saveImpresora() {
  const body = {nombre:v('impNombre'),tipo:v('impTipo'),cabecera:v('impCabecera'),pie:v('impPie'),ancho_papel:v('impAncho'),action:'save_impresora'};
  if (editId) body.id = editId;
  const r = await post('/api/configuracion.php', body);
  if (r.success) { cerrarModal('modalImpresora'); loadImpresoras(); toast('✅ Impresora guardada'); editId=null; }
  else toast('❌ '+(r.error||'Error'),'error');
}
async function editImpresora(id) {
  const d = (await get('/api/configuracion.php?action=impresoras')).find(i=>i.id==id);
  if (!d) return; editId=id;
  set('impNombre',d.nombre); set('impTipo',d.tipo); set('impCabecera',d.cabecera||''); set('impPie',d.pie||''); set('impAncho',d.ancho_papel||48);
  document.getElementById('tituloModalImpresora').textContent='Editar Impresora';
  abrirModal('modalImpresora');
}
async function deleteImpresora(id) {
  if (!confirm('¿Eliminar esta impresora?')) return;
  await post('/api/configuracion.php', {action:'delete_impresora',id}); loadImpresoras(); toast('🗑 Eliminada');
}

// ============================================================
// PERMISOS
// ============================================================
const MODULOS = ['mozos','cocina','caja','mesas','platos','categorias','insumos','kardex','proveedores','compras','reservas','delivery','crm','reportes','usuarios'];
let permisosCache = {};

async function loadPermisos() {
  const rol = v('permisoRolFiltro');
  const d = await get('/api/configuracion.php?action=permisos&rol='+rol);
  permisosCache = {};
  (d||[]).forEach(p => permisosCache[p.modulo] = p);
  document.getElementById('tablePermisos').innerHTML = MODULOS.map(mod => {
    const p = permisosCache[mod] || {puede_ver:1,puede_crear:0,puede_editar:0,puede_eliminar:0};
    return `<tr>
      <td><strong>${mod}</strong></td>
      <td style="text-align:center"><input type="checkbox" data-mod="${mod}" data-perm="ver" ${p.puede_ver?'checked':''}></td>
      <td style="text-align:center"><input type="checkbox" data-mod="${mod}" data-perm="crear" ${p.puede_crear?'checked':''}></td>
      <td style="text-align:center"><input type="checkbox" data-mod="${mod}" data-perm="editar" ${p.puede_editar?'checked':''}></td>
      <td style="text-align:center"><input type="checkbox" data-mod="${mod}" data-perm="eliminar" ${p.puede_eliminar?'checked':''}></td>
    </tr>`;
  }).join('');
}
async function saveAllPermisos() {
  const rol = v('permisoRolFiltro');
  const checkboxes = document.querySelectorAll('#tablePermisos input[type=checkbox]');
  const permisosPorMod = {};
  checkboxes.forEach(cb => {
    const mod = cb.dataset.mod; const perm = cb.dataset.perm;
    if (!permisosPorMod[mod]) permisosPorMod[mod] = {ver:0,crear:0,editar:0,eliminar:0};
    permisosPorMod[mod][perm] = cb.checked ? 1 : 0;
  });
  let ok = 0;
  for (const [mod, perms] of Object.entries(permisosPorMod)) {
    const r = await post('/api/configuracion.php', {action:'save_permiso',rol,modulo:mod,puede_ver:perms.ver,puede_crear:perms.crear,puede_editar:perms.editar,puede_eliminar:perms.eliminar});
    if (r.success) ok++;
  }
  toast(`✅ ${ok} permisos guardados para rol "${rol}"`);
}

// loadPageData extended inline above

// ============================================================
// INIT
// ============================================================
loadDashboard();
setInterval(loadDashboard, 30000);
</script>
</body>
</html>
