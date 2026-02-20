<?php
require_once __DIR__ . '/core.php';
$user = getCurrentUser();
$currentPage = basename($_SERVER['PHP_SELF']);
global $ROLE_LABELS;
?>
<nav class="sidebar">
  <div class="sidebar-brand">
    <a href="dashboard.php">HAL<span>C</span>ÓN</a>
    <div class="sidebar-role"><?= htmlspecialchars($ROLE_LABELS[$user['role']] ?? $user['role']) ?></div>
  </div>

  <div class="nav-section">
    <div class="nav-label">Principal</div>
    <a href="dashboard.php" class="nav-item <?= $currentPage === 'dashboard.php' ? 'active' : '' ?>">
      <span class="nav-icon">⬜</span> Dashboard
    </a>
    <a href="orders.php" class="nav-item <?= in_array($currentPage, ['orders.php','order_detail.php']) ? 'active' : '' ?>">
      <span class="nav-icon">📦</span> Pedidos
    </a>
    <?php if (hasRole(['admin','sales'])): ?>
    <a href="orders.php?action=new" class="nav-item <?= ($currentPage === 'orders.php' && ($_GET['action'] ?? '') === 'new') ? 'active' : '' ?>">
      <span class="nav-icon">➕</span> Nuevo Pedido
    </a>
    <?php endif; ?>
    <a href="deleted_orders.php" class="nav-item <?= $currentPage === 'deleted_orders.php' ? 'active' : '' ?>">
      <span class="nav-icon">🗑</span> Pedidos Eliminados
    </a>
  </div>

  <?php if (hasRole(['admin'])): ?>
  <div class="nav-section">
    <div class="nav-label">Administración</div>
    <a href="users.php" class="nav-item <?= $currentPage === 'users.php' ? 'active' : '' ?>">
      <span class="nav-icon">👥</span> Usuarios
    </a>
  </div>
  <?php endif; ?>

  <div class="sidebar-footer">
    <div class="sidebar-user">
      <div class="sidebar-avatar"><?= strtoupper(substr($user['name'],0,1)) ?></div>
      <div>
        <div style="font-weight:500;font-size:0.85rem"><?= htmlspecialchars($user['name']) ?></div>
        <div style="color:var(--muted);font-size:0.75rem">@<?= htmlspecialchars($user['username']) ?></div>
      </div>
    </div>
    <a href="logout.php" class="nav-item logout">
      <span class="nav-icon">🚪</span> Cerrar Sesión
    </a>
  </div>
</nav>
