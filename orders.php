<?php
require_once 'core.php';
requireLogin();

$user = getCurrentUser();
$db = loadDB();
$msg = $_GET['msg'] ?? '';

// Handle new order form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_order'])) {
    if (!hasRole(['admin','sales'])) die('Unauthorized');

    $invoice = $db['next_invoice'];
    $db['next_invoice']++;

    $newOrder = [
        'id'               => uniqid('ord_'),
        'invoice_num'      => (string)$invoice,
        'customer_name'    => trim($_POST['customer_name']),
        'customer_num'     => trim($_POST['customer_num']) ?: (string)$db['next_customer']++,
        'rfc'              => trim($_POST['rfc']),
        'fiscal_address'   => trim($_POST['fiscal_address']),
        'cfdi_use'         => trim($_POST['cfdi_use']),
        'order_date'       => trim($_POST['order_date']),
        'delivery_address' => trim($_POST['delivery_address']),
        'notes'            => trim($_POST['notes']),
        'status'           => 'ordered',
        'deleted'          => false,
        'created_by'       => $user['id'],
        'created_at'       => date('Y-m-d H:i:s'),
        'photo_route'      => null,
        'photo_delivery'   => null,
    ];

    saveDB($db);
    $db = loadDB();
    $db['orders'][] = $newOrder;
    saveDB($db);

    header('Location: order_detail.php?id=' . $newOrder['id'] . '&msg=created');
    exit;
}

// Filters
$search_invoice  = trim($_GET['invoice'] ?? '');
$search_customer = trim($_GET['customer'] ?? '');
$search_date     = trim($_GET['date'] ?? '');
$search_status   = trim($_GET['status'] ?? '');

$orders = array_filter($db['orders'], fn($o) => !$o['deleted']);
$orders = array_values($orders);

if ($search_invoice)  $orders = array_filter($orders, fn($o) => str_contains($o['invoice_num'], $search_invoice));
if ($search_customer) $orders = array_filter($orders, fn($o) => str_contains($o['customer_num'], $search_customer) || stripos($o['customer_name'], $search_customer) !== false);
if ($search_date)     $orders = array_filter($orders, fn($o) => str_starts_with($o['order_date'], $search_date));
if ($search_status)   $orders = array_filter($orders, fn($o) => $o['status'] === $search_status);

usort($orders, fn($a,$b) => strtotime($b['created_at']) - strtotime($a['created_at']));

global $STATUS_LABELS, $STATUS_COLORS;
$isNewForm = ($_GET['action'] ?? '') === 'new';
$pageTitle = 'Pedidos';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Halcón — Pedidos</title>
<link rel="stylesheet" href="admin.css">
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="main-content">
  <?php include 'topbar.php'; ?>
  <div class="page-body">

    <?php if ($isNewForm && hasRole(['admin','sales'])): ?>
    <!-- NEW ORDER FORM -->
    <div class="page-header">
      <div>
        <h1>NUEVO PEDIDO</h1>
        <p>Ingresa los datos del pedido</p>
      </div>
      <a href="orders.php" class="btn-secondary">← Lista de pedidos</a>
    </div>

    <?php $db2 = loadDB(); ?>
    <div class="form-card">
      <form method="POST">
        <input type="hidden" name="new_order" value="1">

        <div class="form-section-title">Datos del Pedido</div>
        <div class="form-grid">
          <div class="form-group">
            <label>Número de Factura (automático)</label>
            <input type="text" value="#<?= $db2['next_invoice'] ?>" disabled style="opacity:0.5">
          </div>
          <div class="form-group">
            <label>Fecha y Hora del Pedido</label>
            <input type="datetime-local" name="order_date" value="<?= date('Y-m-d\TH:i') ?>" required>
          </div>
          <div class="form-group">
            <label>Nombre / Razón Social del Cliente</label>
            <input type="text" name="customer_name" placeholder="Constructora XYZ S.A." required>
          </div>
          <div class="form-group">
            <label>Número de Cliente</label>
            <input type="text" name="customer_num" placeholder="Dejar vacío para asignar automático">
          </div>
          <div class="form-group full-col">
            <label>Dirección de Entrega</label>
            <input type="text" name="delivery_address" placeholder="Calle, número, colonia, ciudad" required>
          </div>
        </div>

        <div class="form-section-title">Datos Fiscales</div>
        <div class="form-grid">
          <div class="form-group">
            <label>RFC</label>
            <input type="text" name="rfc" placeholder="XAXX010101000">
          </div>
          <div class="form-group">
            <label>Uso de CFDI</label>
            <select name="cfdi_use">
              <option value="G01">G01 - Adquisición de mercancias</option>
              <option value="G03">G03 - Gastos en general</option>
              <option value="P01">P01 - Por definir</option>
              <option value="S01">S01 - Sin efectos fiscales</option>
            </select>
          </div>
          <div class="form-group full-col">
            <label>Domicilio Fiscal</label>
            <input type="text" name="fiscal_address" placeholder="Domicilio fiscal del cliente">
          </div>
        </div>

        <div class="form-section-title">Notas Adicionales</div>
        <div class="form-group">
          <textarea name="notes" placeholder="Instrucciones especiales, detalles del material, etc."></textarea>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn-primary">CREAR PEDIDO →</button>
          <a href="orders.php" class="btn-secondary">Cancelar</a>
        </div>
      </form>
    </div>

    <?php else: ?>
    <!-- ORDER LIST -->
    <div class="page-header">
      <div>
        <h1>PEDIDOS</h1>
        <p><?= count($orders) ?> pedido(s) encontrado(s)</p>
      </div>
      <?php if (hasRole(['admin','sales'])): ?>
        <a href="orders.php?action=new" class="btn-primary">+ Nuevo Pedido</a>
      <?php endif; ?>
    </div>

    <?php if ($msg === 'deleted'): ?>
      <div class="alert alert-success">✓ Pedido eliminado correctamente.</div>
    <?php endif; ?>

    <form method="GET" class="filters-bar">
      <div class="form-group">
        <label>N° Factura</label>
        <input type="text" name="invoice" placeholder="Buscar..." value="<?= htmlspecialchars($search_invoice) ?>">
      </div>
      <div class="form-group">
        <label>Cliente / N° Cliente</label>
        <input type="text" name="customer" placeholder="Nombre o número..." value="<?= htmlspecialchars($search_customer) ?>">
      </div>
      <div class="form-group">
        <label>Fecha</label>
        <input type="date" name="date" value="<?= htmlspecialchars($search_date) ?>">
      </div>
      <div class="form-group">
        <label>Estado</label>
        <select name="status">
          <option value="">Todos</option>
          <?php foreach ($STATUS_LABELS as $val => $lbl): ?>
            <option value="<?= $val ?>" <?= $search_status === $val ? 'selected' : '' ?>><?= $lbl ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div style="display:flex;gap:0.5rem;align-items:flex-end">
        <button type="submit" class="btn-primary" style="padding:0.6rem 1.25rem;font-size:0.85rem">Filtrar</button>
        <a href="orders.php" class="btn-secondary" style="font-size:0.85rem">Limpiar</a>
      </div>
    </form>

    <?php if (empty($orders)): ?>
      <div class="empty-state">
        <div class="empty-icon">📦</div>
        <p>No se encontraron pedidos con los filtros aplicados.</p>
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
            <th>Dirección</th>
            <th>Estado</th>
            <th></th>
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
            <td style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= htmlspecialchars($order['delivery_address']) ?></td>
            <td><span class="badge" style="color:<?= $sc ?>;border-color:<?= $sc ?>"><?= $sl ?></span></td>
            <td><a href="order_detail.php?id=<?= $order['id'] ?>" class="table-action">Ver →</a></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
    <?php endif; ?>

  </div>
</div>
</body>
</html>