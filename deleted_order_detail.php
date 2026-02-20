<?php
require_once 'core.php';
requireLogin();

$db = loadDB();
$id = $_GET['id'] ?? '';
$order = null;
foreach ($db['orders'] as $o) {
    if ($o['id'] === $id && $o['deleted']) { $order = $o; break; }
}
if (!$order) { header('Location: deleted_orders.php'); exit; }

// Handle edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_order'])) {
    if (!hasRole(['admin','sales'])) die('Unauthorized');
    foreach ($db['orders'] as &$o) {
        if ($o['id'] === $id) {
            $o['customer_name']    = trim($_POST['customer_name']);
            $o['customer_num']     = trim($_POST['customer_num']);
            $o['rfc']              = trim($_POST['rfc']);
            $o['fiscal_address']   = trim($_POST['fiscal_address']);
            $o['cfdi_use']         = trim($_POST['cfdi_use']);
            $o['order_date']       = trim($_POST['order_date']);
            $o['delivery_address'] = trim($_POST['delivery_address']);
            $o['notes']            = trim($_POST['notes']);
            break;
        }
    }
    saveDB($db);
    header('Location: deleted_order_detail.php?id=' . $id . '&msg=updated');
    exit;
}

// Handle restore
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['restore'])) {
    foreach ($db['orders'] as &$o) {
        if ($o['id'] === $id) { $o['deleted'] = false; break; }
    }
    saveDB($db);
    header('Location: order_detail.php?id=' . $id . '&msg=restored');
    exit;
}

global $STATUS_LABELS, $STATUS_COLORS;
$sl = $STATUS_LABELS[$order['status']] ?? $order['status'];
$sc = $STATUS_COLORS[$order['status']] ?? '#fff';
$msg = $_GET['msg'] ?? '';
$editMode = isset($_GET['edit']);
$pageTitle = 'Pedido Eliminado #' . $order['invoice_num'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Halcón — Pedido Eliminado #<?= $order['invoice_num'] ?></title>
<link rel="stylesheet" href="assets/admin.css">
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="main-content">
  <?php include 'topbar.php'; ?>
  <div class="page-body">
    <div class="page-header">
      <div>
        <h1>PEDIDO ELIMINADO #<?= htmlspecialchars($order['invoice_num']) ?></h1>
        <p><?= htmlspecialchars($order['customer_name']) ?></p>
      </div>
      <div style="display:flex;gap:0.75rem;flex-wrap:wrap">
        <?php if (!$editMode && hasRole(['admin','sales'])): ?>
          <a href="?id=<?= $id ?>&edit=1" class="btn-secondary">Editar</a>
        <?php endif; ?>
        <form method="POST" onsubmit="return confirm('¿Restaurar este pedido?')">
          <input type="hidden" name="restore" value="1">
          <button type="submit" class="btn-primary">↩ Restaurar Pedido</button>
        </form>
        <a href="deleted_orders.php" class="btn-secondary">← Lista</a>
      </div>
    </div>

    <?php if ($msg === 'updated'): ?>
      <div class="alert alert-success">✓ Pedido actualizado.</div>
    <?php endif; ?>

    <div style="display:inline-block;padding:0.4rem 1rem;background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);border-radius:6px;color:#FCA5A5;font-size:0.8rem;margin-bottom:1.5rem">
      ⚠ Este pedido ha sido eliminado y no es visible en la lista principal.
    </div>

    <?php if ($editMode && hasRole(['admin','sales'])): ?>
    <div class="form-card">
      <form method="POST">
        <input type="hidden" name="edit_order" value="1">
        <div class="form-section-title">Datos del Pedido</div>
        <div class="form-grid">
          <div class="form-group">
            <label>Nombre / Razón Social</label>
            <input type="text" name="customer_name" value="<?= htmlspecialchars($order['customer_name']) ?>" required>
          </div>
          <div class="form-group">
            <label>N° Cliente</label>
            <input type="text" name="customer_num" value="<?= htmlspecialchars($order['customer_num']) ?>" required>
          </div>
          <div class="form-group">
            <label>Fecha del Pedido</label>
            <input type="datetime-local" name="order_date" value="<?= htmlspecialchars(str_replace(' ','T',$order['order_date'])) ?>" required>
          </div>
          <div class="form-group">
            <label>RFC</label>
            <input type="text" name="rfc" value="<?= htmlspecialchars($order['rfc'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label>Uso CFDI</label>
            <select name="cfdi_use">
              <?php foreach (['G01'=>'G01 - Adquisición de mercancias','G03'=>'G03 - Gastos en general','P01'=>'P01 - Por definir','S01'=>'S01 - Sin efectos fiscales'] as $v=>$l): ?>
              <option value="<?= $v ?>" <?= ($order['cfdi_use'] ?? '') === $v ? 'selected' : '' ?>><?= $l ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group full-col">
            <label>Domicilio Fiscal</label>
            <input type="text" name="fiscal_address" value="<?= htmlspecialchars($order['fiscal_address'] ?? '') ?>">
          </div>
          <div class="form-group full-col">
            <label>Dirección de Entrega</label>
            <input type="text" name="delivery_address" value="<?= htmlspecialchars($order['delivery_address']) ?>" required>
          </div>
        </div>
        <div class="form-section-title">Notas</div>
        <div class="form-group">
          <textarea name="notes"><?= htmlspecialchars($order['notes'] ?? '') ?></textarea>
        </div>
        <div class="form-actions">
          <button type="submit" class="btn-primary">GUARDAR CAMBIOS</button>
          <a href="?id=<?= $id ?>" class="btn-secondary">Cancelar</a>
        </div>
      </form>
    </div>

    <?php else: ?>
    <div class="detail-card" style="max-width:600px">
      <h3>Información del Pedido</h3>
      <div class="detail-row"><span class="key">Factura</span><span class="val">#<?= htmlspecialchars($order['invoice_num']) ?></span></div>
      <div class="detail-row"><span class="key">Cliente</span><span class="val"><?= htmlspecialchars($order['customer_name']) ?></span></div>
      <div class="detail-row"><span class="key">N° Cliente</span><span class="val"><?= htmlspecialchars($order['customer_num']) ?></span></div>
      <div class="detail-row"><span class="key">RFC</span><span class="val"><?= htmlspecialchars($order['rfc'] ?? '-') ?></span></div>
      <div class="detail-row"><span class="key">Uso CFDI</span><span class="val"><?= htmlspecialchars($order['cfdi_use'] ?? '-') ?></span></div>
      <div class="detail-row"><span class="key">Domicilio fiscal</span><span class="val"><?= htmlspecialchars($order['fiscal_address'] ?? '-') ?></span></div>
      <div class="detail-row"><span class="key">Fecha pedido</span><span class="val"><?= htmlspecialchars($order['order_date']) ?></span></div>
      <div class="detail-row"><span class="key">Dirección de entrega</span><span class="val"><?= htmlspecialchars($order['delivery_address']) ?></span></div>
      <div class="detail-row"><span class="key">Estado</span><span class="val"><span class="badge" style="color:<?= $sc ?>;border-color:<?= $sc ?>"><?= $sl ?></span></span></div>
      <?php if (!empty($order['notes'])): ?>
      <div class="detail-row"><span class="key">Notas</span><span class="val"><?= nl2br(htmlspecialchars($order['notes'])) ?></span></div>
      <?php endif; ?>
    </div>
    <?php endif; ?>

  </div>
</div>
</body>
</html>
