<?php
require_once __DIR__ . '/includes/functions.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = login(postVar('usuario'), postVar('password'));
    if ($result['ok']) {
        $rol = $result['rol'];
        if ($rol === 'mozo') {
            $redirect = BASE_URL . '/modules/mozos/';
        } elseif ($rol === 'cocina' || $rol === 'bar') {
            $redirect = BASE_URL . '/modules/cocina/';
        } elseif ($rol === 'cajero') {
            $redirect = BASE_URL . '/modules/caja/';
        } else {
            $redirect = BASE_URL . '/modules/admin/';
        }
        header("Location: $redirect");
        exit;
    } else {
        $error = $result['msg'];
    }
}
if (isLoggedIn()) {
    $rol = $_SESSION['user_rol'];
    header('Location: ' . BASE_URL . '/modules/' . ($rol === 'mozo' ? 'mozos' : ($rol === 'cocina' || $rol === 'bar' ? 'cocina' : 'admin')) . '/');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>RestaurantOS — Iniciar Sesión</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
  :root {
    --bg: #0a0a0f;
    --surface: #13131a;
    --surface2: #1e1e2a;
    --border: rgba(255,255,255,0.08);
    --accent: #ff6b35;
    --accent2: #ffaa00;
    --text: #f0f0f5;
    --muted: #6b6b80;
    --green: #22c55e;
    --red: #ef4444;
  }
  * { margin:0; padding:0; box-sizing:border-box; }
  body {
    font-family: 'DM Sans', sans-serif;
    background: var(--bg);
    color: var(--text);
    min-height: 100vh;
    display: flex;
    overflow: hidden;
  }
  .left {
    flex: 1;
    background: linear-gradient(135deg, #1a0a00 0%, #0d0d1a 50%, #001a0d 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
  }
  .left::before {
    content: '';
    position: absolute;
    width: 600px; height: 600px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(255,107,53,0.15) 0%, transparent 70%);
    top: -100px; left: -100px;
    animation: pulse 4s ease-in-out infinite;
  }
  .left::after {
    content: '';
    position: absolute;
    width: 400px; height: 400px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(255,170,0,0.1) 0%, transparent 70%);
    bottom: -100px; right: -50px;
    animation: pulse 4s ease-in-out infinite 2s;
  }
  @keyframes pulse { 0%,100%{transform:scale(1);opacity:.7} 50%{transform:scale(1.1);opacity:1} }
  .brand {
    text-align: center;
    position: relative;
    z-index: 1;
  }
  .brand-icon {
    font-size: 80px;
    display: block;
    margin-bottom: 20px;
    filter: drop-shadow(0 0 30px rgba(255,107,53,0.5));
  }
  .brand h1 {
    font-family: 'Syne', sans-serif;
    font-size: 48px;
    font-weight: 800;
    letter-spacing: -2px;
    background: linear-gradient(135deg, var(--accent), var(--accent2));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
  }
  .brand p {
    color: var(--muted);
    font-size: 16px;
    margin-top: 8px;
    font-weight: 300;
    letter-spacing: 3px;
    text-transform: uppercase;
  }
  .modules-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    margin-top: 40px;
  }
  .mod-chip {
    background: rgba(255,255,255,0.04);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 12px 16px;
    font-size: 13px;
    color: var(--muted);
    display: flex;
    align-items: center;
    gap: 8px;
  }
  .mod-chip span { font-size: 18px; }
  .right {
    width: 460px;
    background: var(--surface);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 60px 50px;
    border-left: 1px solid var(--border);
  }
  .login-box { width: 100%; }
  .login-box h2 {
    font-family: 'Syne', sans-serif;
    font-size: 28px;
    font-weight: 700;
    margin-bottom: 6px;
  }
  .login-box .sub {
    color: var(--muted);
    font-size: 14px;
    margin-bottom: 36px;
  }
  .form-group { margin-bottom: 20px; }
  label {
    display: block;
    font-size: 12px;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--muted);
    margin-bottom: 8px;
  }
  input {
    width: 100%;
    background: var(--surface2);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 14px 16px;
    color: var(--text);
    font-size: 16px;
    font-family: 'DM Sans', sans-serif;
    transition: border-color .2s;
    outline: none;
  }
  input:focus { border-color: var(--accent); }
  .btn-login {
    width: 100%;
    background: linear-gradient(135deg, var(--accent), #e55a28);
    border: none;
    border-radius: 12px;
    padding: 16px;
    color: white;
    font-size: 16px;
    font-weight: 600;
    font-family: 'Syne', sans-serif;
    cursor: pointer;
    transition: all .2s;
    margin-top: 8px;
    letter-spacing: 0.5px;
  }
  .btn-login:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(255,107,53,0.35); }
  .btn-login:active { transform: translateY(0); }
  .error-msg {
    background: rgba(239,68,68,0.1);
    border: 1px solid rgba(239,68,68,0.3);
    border-radius: 10px;
    padding: 12px 16px;
    color: #fc8181;
    font-size: 14px;
    margin-bottom: 20px;
    display: none;
  }
  .quick-access {
    margin-top: 32px;
    padding-top: 24px;
    border-top: 1px solid var(--border);
  }
  .quick-access p {
    color: var(--muted);
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 12px;
  }
  .quick-btns {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
  }
  .quick-btn {
    background: var(--surface2);
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 10px;
    color: var(--text);
    font-size: 12px;
    cursor: pointer;
    text-align: center;
    transition: all .2s;
    font-family: 'DM Sans', sans-serif;
  }
  .quick-btn:hover { border-color: var(--accent); background: rgba(255,107,53,0.08); }
  .quick-btn .emoji { font-size: 20px; display: block; margin-bottom: 4px; }
  .loading { display: none; margin-left: 8px; }
  @media (max-width:768px) { .left { display:none; } .right { width:100%; } }
</style>
</head>
<body>
<div class="left">
  <div class="brand">
    <span class="brand-icon">🍽️</span>
    <h1>RestaurantOS</h1>
    <p>Sistema de Gestión Integral</p>
    <div class="modules-grid">
      <div class="mod-chip"><span>📱</span> Módulo Mozos</div>
      <div class="mod-chip"><span>📺</span> Cocina / KDS</div>
      <div class="mod-chip"><span>💰</span> Caja / Cobros</div>
      <div class="mod-chip"><span>⚙️</span> Administración</div>
      <div class="mod-chip"><span>📦</span> Almacén</div>
      <div class="mod-chip"><span>📊</span> Reportes</div>
    </div>
  </div>
</div>

<div class="right">
  <div class="login-box">
    <h2>Bienvenido 👋</h2>
    <p class="sub">Ingresa tus credenciales para continuar</p>

    <div class="error-msg" id="errorMsg"><?= sanitize($error) ?></div>

    <form id="loginForm" method="POST">
      <div class="form-group">
        <label>Usuario</label>
        <input type="text" name="usuario" id="usuario" placeholder="nombre.usuario" autocomplete="username" required>
      </div>
      <div class="form-group">
        <label>Contraseña</label>
        <input type="password" name="password" id="password" placeholder="••••••••" autocomplete="current-password" required>
      </div>
      <button type="submit" class="btn-login" id="btnLogin">
        Iniciar Sesión
        <span class="loading" id="loading">⏳</span>
      </button>
    </form>

    <div class="quick-access">
      <p>Acceso rápido (demo)</p>
      <div class="quick-btns">
        <button class="quick-btn" onclick="quickLogin('mozo1','password')">
          <span class="emoji">📱</span> Mozo
        </button>
        <button class="quick-btn" onclick="quickLogin('cocina1','password')">
          <span class="emoji">👨‍🍳</span> Cocina
        </button>
        <button class="quick-btn" onclick="quickLogin('cajero1','password')">
          <span class="emoji">💰</span> Caja
        </button>
        <button class="quick-btn" onclick="quickLogin('admin','password')">
          <span class="emoji">⚙️</span> Admin
        </button>
      </div>
    </div>
  </div>
</div>

<script>
const err = <?= json_encode($error) ?>;
if (err) {
  document.getElementById('errorMsg').style.display = 'block';
  document.getElementById('errorMsg').textContent = err;
}

function quickLogin(u, p) {
  document.getElementById('usuario').value = u;
  document.getElementById('password').value = p;
  document.getElementById('loginForm').submit();
}

document.getElementById('loginForm').addEventListener('submit', function() {
  document.getElementById('btnLogin').disabled = true;
  document.getElementById('loading').style.display = 'inline';
});
</script>
</body>
</html>
