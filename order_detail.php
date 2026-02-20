<?php
require_once 'core.php';
requireLogin();

$user = getCurrentUser();
$db   = loadDB();
$id   = $_GET['id'] ?? '';
$msg  = $_GET['msg'] ?? '';

// Find order
$orderIdx = null;
foreach ($db['orders'] as $i => $o) {
    if ($o['id'] === $id) { $orderIdx = $i; break; }
}

if ($orderIdx === null) {
    header('Location: orders.php');
    exit;
}

$order = &$db['orders'][$orderIdx];

// Handle status change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_status'])) {
    $newStatus = $_POST['new_status'];
    $allowed = [];

    // Determine allowed transitions
    if ($order['status'] === 'ordered' && hasRole(['admin','warehouse'])) {
        $allowed = ['in_process'];
    } elseif ($order['status'] === 'in_process' && hasRole(['admin','warehouse'])) {
        $allowed = ['in_route'];
    } elseif ($order['status'] === 'in_route' && hasRole(['admin','route'])) {
        $allowed = ['delivered'];
    }

    if (in_array($newStatus, $allowed)) {
        $order['status'] = $newStatus;
        saveDB($db);
        header('Location: order_detail.php?id=' . $id . '&msg=status_updated');
        exit;
    }
}

// Handle edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_order'])) {
    if (!hasRole(['admin','sales'])) die('Unauthorized');
    $order['customer_name']    = trim($_POST['customer_name']);
    $order['customer_num']     = trim($_POST['customer_num']);
    $order['rfc']              = trim($_POST['rfc']);
    $order['fiscal_address']   = trim($_POST['fiscal_address']);
    $order['cfdi_use']         = trim($_POST['cfdi_use']);
    $order['order_date']       = trim($_POST['order_date']);
    $order['delivery_address'] = trim($_POST['delivery_address']);
    $order['notes']            = trim($_POST['notes']);
    saveDB($db);
    header('Location: order_detail.php?id=' . $id . '&msg=updated');
    exit;
}

// Handle soft delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_order'])) {
    if (!hasRole(['admin','sales'])) die('Unauthorized');
    $order['deleted'] = true;
    saveDB($db);
    header('Location: orders.php?msg=deleted');
    exit;
}

// Handle photo upload (route)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_route_photo'])) {
    if (!hasRole(['admin','route'])) die('Unauthorized');
    if (!empty($_FILES['route_photo']['tmp_name'])) {
        $ext = strtolower(pathinfo($_FILES['route_photo']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','webp','gif'])) {
            $fname = 'route_' . $id . '_' . time() . '.' . $ext;
            move_uploaded_file($_FILES['route_photo']['tmp_name'], UPLOADS_DIR . 'route/' . $fname);
            $order['photo_route'] = $fname;
            saveDB($db);
        }
    }
    header('Location: order_detail.php?id=' . $id . '&msg=photo_uploaded');
    exit;
}

// Handle photo upload (delivery evidence)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_delivery_photo'])) {
    if (!hasRole(['admin','route'])) die('Unauthorized');
    if (!empty($_FILES['delivery_photo']['tmp_name'])) {
        $ext = strtolower(pathinfo($_FILES['delivery_photo']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','webp','gif'])) {
            $fname = 'delivery_' . $id . '_' . time() . '.' . $ext;
            move_uploaded_file($_FILES['delivery_photo']['tmp_name'], UPLOADS_DIR . 'evidence/' . $fname);
            $order['photo_delivery'] = $fname;
            // Auto set to delivered
            $order['status'] = 'delivered';
            saveDB($db);
        }
    }
    header('Location: order_detail.php?id=' . $id . '&msg=delivered');
    exit;
}

// Reload after saves
$db = loadDB();
foreach ($db['orders'] as $o) {
    if ($o['id'] === $id) { $order = $o; break; }
}

global $STATUS_LABELS, $STATUS_COLORS, $ROLE_LABELS;
$sl = $STATUS_LABELS[$order['status']] ?? $order['status'];
$sc = $STATUS_COLORS[$order['status']] ?? '#fff';

$steps = ['ordered','in_process','in_route','delivered'];
$stepLabels = ['Ordenado','En Proceso','En Ruta','Entregado'];
$currentIdx = array_search($order['status'], $steps);

$editMode = isset($_GET['edit']);
$pageTitle = 'Pedido #' . $order['invoice_num'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Halcón — Pedido #<?= htmlspecialchars($order['invoice_num']) ?></title>
<link rel="stylesheet" href="assets/admin.css">
</head>
<body>
<?php include 'partials/sidebar.php'; ?>
<div class="main-content">
  <?php include 'partials/topbar.php'; ?>
  <div class="page-body">

    <div class="page-header">
      <div>
        <h1>PEDIDO #<?= htmlspecialchars($order['invoice_num']) ?></h1>
        <p><?= htmlspecialchars($order['customer_name']) ?></p>
      </div>
      <div style="display:flex;gap:0.75rem;align-items:center;flex-wrap:wrap">
        <span class="badge" style="color:<?= $sc ?>;border-color:<?= $sc ?>;font-size:0.85rem;padding:0.4rem 1rem"><?= $sl ?></span>
        <?php if (!$editMode && hasRole(['admin','sales'])): ?>
          <a href="order_detail.php?id=<?= $id ?>&edit=1" class="btn-secondary">Editar</a>
        <?php endif; ?>
        <a href="orders.php" class="btn-secondary">← Lista</a>
      </div>
    </div>

    <?php if ($msg === 'created'): ?>
      <div class="alert alert-success">✓ Pedido creado exitosamente. El cliente puede consultarlo con N° cliente <strong><?= $order['customer_num'] ?></strong> y factura <strong>#<?= $order['invoice_num'] ?></strong>.</div>
    <?php elseif ($msg === 'updated'): ?>
      <div class="alert alert-success">✓ Pedido actualizado correctamente.</div>
    <?php elseif ($msg === 'status_updated'): ?>
      <div class="alert alert-success">✓ Estado actualizado.</div>
    <?php elseif ($msg === 'delivered'): ?>
      <div class="alert alert-success">✓ Foto de entrega subida. El pedido ha sido marcado como Entregado.</div>
    <?php elseif ($msg === 'photo_uploaded'): ?>
      <div class="alert alert-success">✓ Foto de unidad cargada subida correctamente.</div>
    <?php endif; ?>

    <?php if ($editMode && hasRole(['admin','sales'])): ?>
    <!-- EDIT FORM -->
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
            <label>Número de Cliente</label>
            <input type="text" name="customer_num" value="<?= htmlspecialchars($order['customer_num']) ?>" required>
          </div>
          <div class="form-group">
            <label>Fecha y Hora del Pedido</label>
            <input type="datetime-local" name="order_date" value="<?= htmlspecialchars(str_replace(' ','T',$order['order_date'])) ?>" required>
          </div>
          <div class="form-group">
            <label>RFC</label>
            <input type="text" name="rfc" value="<?= htmlspecialchars($order['rfc'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label>Uso de CFDI</label>
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
          <a href="order_detail.php?id=<?= $id ?>" class="btn-secondary">Cancelar</a>
          <form method="POST" style="margin:0" onsubmit="return confirm('¿Eliminar este pedido?')">
            <input type="hidden" name="delete_order" value="1">
            <button type="submit" class="btn-danger">Eliminar pedido</button>
          </form>
        </div>
      </form>
    </div>

    <?php else: ?>
    <!-- VIEW MODE -->
    <div class="detail-grid">
      <div>
        <!-- Status timeline -->
        <div class="detail-card">
          <h3>Progreso del Pedido</h3>
          <div class="status-timeline">
            <?php foreach ($steps as $i => $step):
              $isDone = $i < $currentIdx;
              $isActive = $i <= $currentIdx;
            ?>
            <div class="timeline-item">
              <div class="timeline-dot <?= $isActive ? 'active' : '' ?>"><?= $isDone ? '✓' : ($i+1) ?></div>
              <div class="timeline-content">
                <div class="tl-title"><?= $stepLabels[$i] ?></div>
                <div class="tl-sub"><?= $isActive ? ($isDone ? 'Completado' : 'Estado actual') : 'Pendiente' ?></div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>

          <!-- Status change buttons -->
          <?php
          $nextStatus = null;
          $canChange = false;
          if ($order['status'] === 'ordered' && hasRole(['admin','warehouse'])) {
              $nextStatus = 'in_process'; $canChange = true;
          } elseif ($order['status'] === 'in_process' && hasRole(['admin','warehouse'])) {
              $nextStatus = 'in_route'; $canChange = true;
          } elseif ($order['status'] === 'in_route' && hasRole(['admin','route'])) {
              $nextStatus = null; // route uses photo upload
          }
          ?>
          <?php if ($canChange && $nextStatus): ?>
          <form method="POST" style="margin-top:1rem">
            <input type="hidden" name="change_status" value="1">
            <input type="hidden" name="new_status" value="<?= $nextStatus ?>">
            <button type="submit" class="btn-primary" style="width:100%">
              Cambiar estado a "<?= $STATUS_LABELS[$nextStatus] ?>"
            </button>
          </form>
          <?php endif; ?>
        </div>

        <!-- Order info -->
        <div class="detail-card">
          <h3>Información del Pedido</h3>
          <div class="detail-row"><span class="key">Factura</span><span class="val">#<?= htmlspecialchars($order['invoice_num']) ?></span></div>
          <div class="detail-row"><span class="key">Cliente</span><span class="val"><?= htmlspecialchars($order['customer_name']) ?></span></div>
          <div class="detail-row"><span class="key">N° Cliente</span><span class="val"><?= htmlspecialchars($order['customer_num']) ?></span></div>
          <div class="detail-row"><span class="key">Fecha pedido</span><span class="val"><?= htmlspecialchars($order['order_date']) ?></span></div>
          <div class="detail-row"><span class="key">Dirección de entrega</span><span class="val"><?= htmlspecialchars($order['delivery_address']) ?></span></div>
          <?php if (!empty($order['notes'])): ?>
          <div class="detail-row"><span class="key">Notas</span><span class="val"><?= nl2br(htmlspecialchars($order['notes'])) ?></span></div>
          <?php endif; ?>
        </div>

        <!-- Fiscal data -->
        <div class="detail-card">
          <h3>Datos Fiscales</h3>
          <div class="detail-row"><span class="key">RFC</span><span class="val"><?= htmlspecialchars($order['rfc'] ?? '-') ?></span></div>
          <div class="detail-row"><span class="key">Uso CFDI</span><span class="val"><?= htmlspecialchars($order['cfdi_use'] ?? '-') ?></span></div>
          <div class="detail-row"><span class="key">Domicilio Fiscal</span><span class="val"><?= htmlspecialchars($order['fiscal_address'] ?? '-') ?></span></div>
        </div>
      </div>

      <div>
        <!-- Photos section — only route dept sees upload -->
        <div class="detail-card">
          <h3>Evidencias Fotográficas</h3>

          <!-- Route photo -->
          <div style="margin-bottom:1.25rem">
            <div style="font-size:0.8rem;color:var(--muted);margin-bottom:0.75rem;text-transform:uppercase;letter-spacing:0.05em">Unidad Cargada</div>
            <?php if (!empty($order['photo_route'])): ?>
              <div class="evidence-photo">
                <img src="uploads/route/<?= htmlspecialchars($order['photo_route']) ?>" alt="Foto unidad">
              </div>
            <?php elseif (hasRole(['admin','route']) && $order['status'] === 'in_route'): ?>
              <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="upload_route_photo" value="1">
                <div class="upload-zone">
                  <input type="file" name="route_photo" accept="image/*" onchange="this.form.submit()">
                  <div class="upload-icon">📷</div>
                  <p>Subir foto de unidad cargada</p>
                </div>
              </form>
            <?php else: ?>
              <div style="color:var(--muted);font-size:0.85rem;padding:1rem 0;text-align:center;border:1px dashed var(--border);border-radius:6px">Sin foto aún</div>
            <?php endif; ?>
          </div>

          <!-- Delivery photo -->
          <div>
            <div style="font-size:0.8rem;color:var(--muted);margin-bottom:0.75rem;text-transform:uppercase;letter-spacing:0.05em">Evidencia de Entrega</div>
            <?php if (!empty($order['photo_delivery'])): ?>
              <div class="evidence-photo">
                <img src="uploads/evidence/<?= htmlspecialchars($order['photo_delivery']) ?>" alt="Foto entrega">
              </div>
            <?php elseif (hasRole(['admin','route']) && in_array($order['status'], ['in_route','delivered'])): ?>
              <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="upload_delivery_photo" value="1">
                <div class="upload-zone">
                  <input type="file" name="delivery_photo" accept="image/*" onchange="this.form.submit()">
                  <div class="upload-icon">📸</div>
                  <p>Subir foto de entrega<br><small style="font-size:0.75rem;opacity:0.7">(Marcará el pedido como Entregado)</small></p>
                </div>
              </form>
            <?php else: ?>
              <div style="color:var(--muted);font-size:0.85rem;padding:1rem 0;text-align:center;border:1px dashed var(--border);border-radius:6px">Sin foto aún</div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Delete button -->
        <?php if (hasRole(['admin','sales'])): ?>
        <div style="margin-top:0.5rem">
          <form method="POST" onsubmit="return confirm('¿Eliminar este pedido lógicamente?')">
            <input type="hidden" name="delete_order" value="1">
            <button type="submit" class="btn-danger" style="width:100%">🗑 Eliminar Pedido</button>
          </form>
        </div>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>

  </div>
</div>
</body>
</html>
