<?php
require_once 'core.php';
requireLogin();

$db = loadDB();
$msg = $_GET['msg'] ?? '';
$id = $_GET['id'] ?? '';

// Restore order
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['restore']) && $id) {
    foreach ($db['orders'] as &$o) {
        if ($o['id'] === $id) { $o['deleted'] = false; break; }
    }
    saveDB($db);
    header('Location: deleted_orders.php?msg=restored');
    exit;
}

$db = loadDB();
$orders = array_filter($db['orders'], fn($o) => $o['deleted']);
usort($orders, fn($a,$b) => strtotime($b['created_at']) - strtotime($a['created_at']));

global $STATUS_LABELS, $STATUS_COLORS;
$pageTitle = 'Pedidos Eliminados';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Halcón — Pedidos Eliminados</title>
<link rel="stylesheet" href="admin.css">
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="main-content">
  <?php include 'topbar.php'; ?>
  <div class="page-body">
    <div class="page-header">
      <div>
        <h1>PEDIDOS ELIMINADOS</h1>
        <p><?= count($orders) ?> pedido(s) eliminado(s)</p>
      </div>
    </div>

    <?php if ($msg === 'restored'): ?>
      <div class="alert alert-success">✓ Pedido restaurado correctamente.</div>
    <?php endif; ?>

    <?php if (empty($orders)): ?>
      <div class="empty-state">
        <div class="empty-icon">🗑</div>
        <p>No hay pedidos eliminados.</p>
        <a href="orders.php" class="btn-secondary">← Volver a pedidos</a>
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
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($orders as $order):
            $sl = $STATUS_LABELS[$order['status']] ?? $order['status'];
            $sc = $STATUS_COLORS[$order['status']] ?? '#fff';
          ?>
          <tr>
            <td><strong>#<?= htmlspecialchars($order['invoice_num']) ?></strong></td>
            <td><?= htmlspecialchars($order['customer_name']) ?></td>
            <td><?= htmlspecialchars($order['customer_num']) ?></td>
            <td><?= htmlspecialchars(substr($order['order_date'],0,16)) ?></td>
            <td><span class="badge" style="color:<?= $sc ?>;border-color:<?= $sc ?>;opacity:0.6"><?= $sl ?></span></td>
            <td style="display:flex;gap:0.5rem">
              <a href="deleted_order_detail.php?id=<?= $order['id'] ?>" class="table-action">Ver</a>
              <form method="POST" action="deleted_orders.php?id=<?= $order['id'] ?>" onsubmit="return confirm('¿Restaurar este pedido?')">
                <input type="hidden" name="restore" value="1">
                <button type="submit" style="background:none;border:none;color:#6EE7B7;font-size:0.8rem;cursor:pointer;font-family:inherit">Restaurar</button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div>
</body>
</html>