<?php
require_once 'core.php';

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username && $password) {
        $db = loadDB();
        foreach ($db['users'] as $user) {
            if ($user['username'] === $username && $user['password'] === hashPassword($password) && $user['active']) {
                $_SESSION['user_id'] = $user['id'];
                header('Location: dashboard.php');
                exit;
            }
        }
        $error = 'Usuario o contraseña incorrectos.';
    } else {
        $error = 'Por favor completa todos los campos.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Halcón — Acceso Personal</title>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  :root {
    --bg: #0A0C10; --surface: #13161D; --border: #1E2330;
    --accent: #E8C84A; --accent2: #FF6B35; --text: #F0F2F5; --muted: #6B7280;
  }
  body {
    font-family: 'DM Sans', sans-serif;
    background: var(--bg); color: var(--text);
    min-height: 100vh;
    display: grid; place-items: center;
    background-image: radial-gradient(ellipse 80% 50% at 50% -20%, rgba(232,200,74,0.07), transparent);
  }
  .login-wrap {
    width: 100%; max-width: 420px; padding: 2rem;
  }
  .brand {
    text-align: center; margin-bottom: 3rem;
  }
  .brand a {
    font-family: 'Bebas Neue', sans-serif;
    font-size: 3rem; letter-spacing: 0.1em;
    color: var(--accent); text-decoration: none;
  }
  .brand a span { color: var(--accent2); }
  .brand p { color: var(--muted); font-size: 0.85rem; margin-top: 0.25rem; letter-spacing: 0.05em; text-transform: uppercase; }
  .card {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: 12px; padding: 2.5rem;
    position: relative; overflow: hidden;
  }
  .card::before {
    content: '';
    position: absolute; top: -1px; left: 20%; right: 20%;
    height: 2px;
    background: linear-gradient(90deg, transparent, var(--accent), transparent);
  }
  h2 { font-size: 1.3rem; font-weight: 500; margin-bottom: 0.25rem; }
  .subtitle { color: var(--muted); font-size: 0.85rem; margin-bottom: 2rem; }
  .form-group { margin-bottom: 1.25rem; }
  label { display: block; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--muted); margin-bottom: 0.5rem; }
  input[type="text"], input[type="password"] {
    width: 100%; background: var(--bg); border: 1px solid var(--border);
    color: var(--text); padding: 0.875rem 1rem; border-radius: 6px;
    font-family: 'DM Sans', sans-serif; font-size: 1rem;
    transition: border-color 0.2s; outline: none;
  }
  input:focus { border-color: var(--accent); }
  .btn {
    width: 100%; padding: 1rem; background: var(--accent); color: #000;
    border: none; border-radius: 6px; font-family: 'Bebas Neue', sans-serif;
    font-size: 1.2rem; letter-spacing: 0.1em; cursor: pointer;
    transition: opacity 0.2s; margin-top: 0.5rem;
  }
  .btn:hover { opacity: 0.9; }
  .error-msg {
    background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.3);
    color: #FCA5A5; padding: 0.875rem 1rem; border-radius: 6px;
    margin-bottom: 1.5rem; font-size: 0.9rem;
  }
  .back-link { text-align: center; margin-top: 1.5rem; }
  .back-link a { color: var(--muted); font-size: 0.85rem; text-decoration: none; }
  .back-link a:hover { color: var(--text); }
</style>
</head>
<body>
<div class="login-wrap">
  <div class="brand">
    <a href="index.php">HAL<span>C</span>ÓN</a>
    <p>Sistema Interno de Gestión</p>
  </div>
  <div class="card">
    <h2>Iniciar Sesión</h2>
    <p class="subtitle">Acceso exclusivo para personal de Halcón</p>
    <?php if ($error): ?>
      <div class="error-msg">⚠ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST">
      <div class="form-group">
        <label>Usuario</label>
        <input type="text" name="username" autocomplete="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>Contraseña</label>
        <input type="password" name="password" autocomplete="current-password">
      </div>
      <button type="submit" class="btn">ENTRAR →</button>
    </form>
  </div>
  <div class="back-link"><a href="index.php">← Consulta de pedidos</a></div>
</div>
</body>
</html>
