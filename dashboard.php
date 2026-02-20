<?php
require_once 'core.php';
requireLogin();

$user = getCurrentUser();
$db = loadDB();

// Count stats
$orders = array_filter($db['orders'], fn($o) => !$o['deleted']);
$total = count($orders);
$ordered = count(array_filter($orders, fn($o) => $o['status'] === 'ordered'));
$in_process = count(array_filter($orders, fn($o) => $o['status'] === 'in_process'));
$in_route = count(array_filter($orders, fn($o) => $o['status'] === 'in_route'));
$delivered = count(array_filter($orders, fn($o) => $o['status'] === 'delivered'));

global $ROLE_LABELS, $STATUS_LABELS;
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Halcón — Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="admin.css">
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="main-content">
  <?php include 'topbar.php'; ?>
  <div class="page-body">
    <div class="page-header">
      <h1>Dashboard</h1>
      <p>Bienvenido, <?= htmlspecialchars($user['name']) ?></p>
    </div>

    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-label">Total Pedidos</div>
        <div class="stat-value"><?= $total ?></div>
        <div class="stat-sub">activos</div>
      </div>
      <div class="stat-card" style="--accent-color: #F59E0B">
        <div class="stat-label">Ordenados</div>
        <div class="stat-value"><?= $ordered ?></div>
        <div class="stat-dot" style="background:#F59E0B"></div>
      </div>
      <div class="stat-card" style="--accent-color: #3B82F6">
        <div class="stat-label">En Proceso</div>
        <div class="stat-value"><?= $in_process ?></div>
        <div class="stat-dot" style="background:#3B82F6"></div>
      </div>
      <div class="stat-card" style="--accent-color: #8B5CF6">
        <div class="stat-label">En Ruta</div>
        <div class="stat-value"><?= $in_route ?></div>
        <div class="stat-dot" style="background:#8B5CF6"></div>
      </div>
      <div class="stat-card" style="--accent-color: #10B981">
        <div class="stat-label">Entregados</div>
        <div class="stat-value"><?= $delivered ?></div>
        <div class="stat-dot" style="background:#10B981"></div>
      </div>
    </div>

    <div class="section-title">Pedidos Recientes</div>
    <?php
    $recent = array_filter($db['orders'], fn($o) => !$o['deleted']);
    usort($recent, fn($a,$b) => strtotime($b['created_at']) - strtotime($a['created_at']));
    $recent = array_slice(array_values($recent), 0, 5);
    ?>
    <?php if (empty($recent)): ?>
      <div class="empty-state">
        <div class="empty-icon">📦</div>
        <p>No hay pedidos registrados aún.</p>
        <?php if (hasRole(['admin','sales'])): ?>
          <a href="orders.php?action=new" class="btn-primary">Crear primer pedido</a>
        <?php endif; ?>
      </div>
    <?php else: ?>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Factura</th>
            <th>Cliente</th>
            <th>N° Cliente</th>
            <th>Fecha</th>
            <th>Estado</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($recent as $order): 
            global $STATUS_LABELS, $STATUS_COLORS;
            $sl = $STATUS_LABELS[$order['status']] ?? $order['status'];
            $sc = $STATUS_COLORS[$order['status']] ?? '#fff';
          ?>
          <tr>
            <td><strong>#<?= htmlspecialchars($order['invoice_num']) ?></strong></td>
            <td><?= htmlspecialchars($order['customer_name']) ?></td>
            <td><?= htmlspecialchars($order['customer_num']) ?></td>
            <td><?= htmlspecialchars(substr($order['order_date'],0,16)) ?></td>
            <td><span class="badge" style="color:<?= $sc ?>;border-color:<?= $sc ?>"><?= $sl ?></span></td>
            <td><a href="order_detail.php?id=<?= $order['id'] ?>" class="table-action">Ver →</a></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <div style="text-align:right;margin-top:1rem">
      <a href="orders.php" class="btn-secondary">Ver todos los pedidos →</a>
    </div>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
