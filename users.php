<?php
require_once 'core.php';
requireRole(['admin']);

$db = loadDB();
$msg = $_GET['msg'] ?? '';

// Create user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_user'])) {
    $username = trim($_POST['username']);
    $name     = trim($_POST['name']);
    $role     = trim($_POST['role']);
    $pass     = trim($_POST['password']);

    // Check username unique
    $exists = false;
    foreach ($db['users'] as $u) {
        if ($u['username'] === $username) { $exists = true; break; }
    }

    if (!$exists && $username && $name && $role && $pass) {
        $db['users'][] = [
            'id'       => max(array_column($db['users'], 'id')) + 1,
            'username' => $username,
            'password' => hashPassword($pass),
            'name'     => $name,
            'role'     => $role,
            'active'   => true,
        ];
        saveDB($db);
        header('Location: users.php?msg=created');
        exit;
    } else {
        $error = $exists ? 'El nombre de usuario ya existe.' : 'Completa todos los campos.';
    }
}

// Toggle active
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_active'])) {
    $uid = (int)$_POST['user_id'];
    foreach ($db['users'] as &$u) {
        if ($u['id'] === $uid && $uid !== 1) {
            $u['active'] = !$u['active'];
        }
    }
    saveDB($db);
    header('Location: users.php?msg=updated');
    exit;
}

// Change role
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_role'])) {
    $uid  = (int)$_POST['user_id'];
    $role = trim($_POST['role']);
    foreach ($db['users'] as &$u) {
        if ($u['id'] === $uid) { $u['role'] = $role; }
    }
    saveDB($db);
    header('Location: users.php?msg=updated');
    exit;
}

$db = loadDB();
global $ROLE_LABELS;
$pageTitle = 'Usuarios';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Halcón — Usuarios</title>
<link rel="stylesheet" href="admin.css">
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="main-content">
  <?php include 'topbar.php'; ?>
  <div class="page-body">
    <div class="page-header">
      <div>
        <h1>USUARIOS</h1>
        <p>Gestión de accesos al sistema</p>
      </div>
    </div>

    <?php if ($msg === 'created'): ?>
      <div class="alert alert-success">✓ Usuario creado exitosamente.</div>
    <?php elseif ($msg === 'updated'): ?>
      <div class="alert alert-success">✓ Usuario actualizado.</div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
      <div class="alert alert-error">⚠ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div style="display:grid;grid-template-columns:1fr 320px;gap:1.5rem;align-items:start">
      <!-- User list -->
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Nombre</th>
              <th>Usuario</th>
              <th>Rol</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($db['users'] as $u): ?>
            <tr>
              <td>
                <div style="display:flex;align-items:center;gap:0.75rem">
                  <div style="width:32px;height:32px;border-radius:50%;background:var(--accent);color:#000;display:flex;align-items:center;justify-content:center;font-size:0.75rem;font-weight:600;flex-shrink:0">
                    <?= strtoupper(substr($u['name'],0,1)) ?>
                  </div>
                  <?= htmlspecialchars($u['name']) ?>
                </div>
              </td>
              <td style="color:var(--muted)">@<?= htmlspecialchars($u['username']) ?></td>
              <td>
                <?php if ($u['id'] === 1): ?>
                  <span class="badge" style="color:var(--accent);border-color:var(--accent)"><?= $ROLE_LABELS[$u['role']] ?></span>
                <?php else: ?>
                  <form method="POST" style="display:inline">
                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                    <select name="role" onchange="this.form.submit()" style="background:var(--bg);border:1px solid var(--border);color:var(--text);padding:0.3rem 0.5rem;border-radius:4px;font-size:0.8rem;cursor:pointer">
                      <?php foreach ($ROLE_LABELS as $rk => $rl): if ($rk === 'admin') continue; ?>
                        <option value="<?= $rk ?>" <?= $u['role'] === $rk ? 'selected' : '' ?>><?= $rl ?></option>
                      <?php endforeach; ?>
                    </select>
                    <input type="hidden" name="change_role" value="1">
                  </form>
                <?php endif; ?>
              </td>
              <td>
                <span class="badge" style="color:<?= $u['active'] ? '#10B981' : '#9CA3AF' ?>;border-color:<?= $u['active'] ? '#10B981' : '#9CA3AF' ?>">
                  <?= $u['active'] ? 'Activo' : 'Inactivo' ?>
                </span>
              </td>
              <td>
                <?php if ($u['id'] !== 1): ?>
                <form method="POST" style="display:inline" onsubmit="return confirm('¿Cambiar estado del usuario?')">
                  <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                  <input type="hidden" name="toggle_active" value="1">
                  <button type="submit" style="background:none;border:none;color:var(--muted);font-size:0.8rem;cursor:pointer;font-family:inherit">
                    <?= $u['active'] ? 'Desactivar' : 'Activar' ?>
                  </button>
                </form>
                <?php else: ?>
                  <span style="color:var(--muted);font-size:0.75rem">—</span>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <!-- Create user form -->
      <div class="detail-card">
        <h3>Nuevo Usuario</h3>
        <form method="POST">
          <input type="hidden" name="create_user" value="1">
          <div class="form-group">
            <label>Nombre completo</label>
            <input type="text" name="name" placeholder="Juan Pérez" required>
          </div>
          <div class="form-group">
            <label>Nombre de usuario</label>
            <input type="text" name="username" placeholder="jperez" required autocomplete="off">
          </div>
          <div class="form-group">
            <label>Contraseña</label>
            <input type="password" name="password" placeholder="Mínimo 6 caracteres" required autocomplete="new-password">
          </div>
          <div class="form-group">
            <label>Departamento / Rol</label>
            <select name="role" required>
              <option value="sales">Ventas</option>
              <option value="purchasing">Compras</option>
              <option value="warehouse">Almacén</option>
              <option value="route">Ruta</option>
            </select>
          </div>
          <button type="submit" class="btn-primary" style="width:100%">CREAR USUARIO</button>
        </form>
      </div>
    </div>
  </div>
</div>
</body>
</html>