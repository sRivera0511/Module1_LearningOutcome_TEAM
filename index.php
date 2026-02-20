<?php
require_once 'core.php';

$result = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_num = trim($_POST['customer_num'] ?? '');
    $invoice_num  = trim($_POST['invoice_num'] ?? '');

    if ($customer_num && $invoice_num) {
        $db = loadDB();
        foreach ($db['orders'] as $order) {
            if ($order['deleted']) continue;
            if ((string)$order['customer_num'] === $customer_num && (string)$order['invoice_num'] === $invoice_num) {
                $result = $order;
                break;
            }
        }
        if (!$result) $error = 'No se encontró ningún pedido con esos datos.';
    } else {
        $error = 'Por favor ingresa el número de cliente y el número de factura.';
    }
}

global $STATUS_LABELS, $STATUS_COLORS;
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Halcón — Estado de Pedido</title>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  :root {
    --bg: #0A0C10;
    --surface: #13161D;
    --border: #1E2330;
    --accent: #E8C84A;
    --accent2: #FF6B35;
    --text: #F0F2F5;
    --muted: #6B7280;
    --ordered: #F59E0B;
    --in_process: #3B82F6;
    --in_route: #8B5CF6;
    --delivered: #10B981;
  }

  body {
    font-family: 'DM Sans', sans-serif;
    background: var(--bg);
    color: var(--text);
    min-height: 100vh;
    display: flex;
    flex-direction: column;
  }

  /* Grain texture overlay */
  body::before {
    content: '';
    position: fixed;
    inset: 0;
    background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)' opacity='0.03'/%3E%3C/svg%3E");
    pointer-events: none;
    z-index: 0;
    opacity: 0.4;
  }

  header {
    position: relative;
    z-index: 1;
    padding: 2rem 3rem;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: space-between;
  }

  .logo {
    font-family: 'Bebas Neue', sans-serif;
    font-size: 2.5rem;
    letter-spacing: 0.1em;
    color: var(--accent);
    text-decoration: none;
  }

  .logo span {
    color: var(--accent2);
  }

  .header-nav a {
    color: var(--muted);
    text-decoration: none;
    font-size: 0.85rem;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    border: 1px solid var(--border);
    padding: 0.5rem 1rem;
    border-radius: 4px;
    transition: all 0.2s;
  }
  .header-nav a:hover { color: var(--text); border-color: var(--accent); }

  main {
    position: relative;
    z-index: 1;
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 4rem 2rem;
  }

  .container {
    width: 100%;
    max-width: 680px;
  }

  .hero-text {
    margin-bottom: 3rem;
  }

  .hero-text h1 {
    font-family: 'Bebas Neue', sans-serif;
    font-size: clamp(3rem, 8vw, 5.5rem);
    line-height: 0.95;
    letter-spacing: 0.02em;
    margin-bottom: 1rem;
  }

  .hero-text h1 em {
    font-style: normal;
    color: var(--accent);
  }

  .hero-text p {
    color: var(--muted);
    font-size: 1rem;
    line-height: 1.6;
  }

  .search-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 2.5rem;
    position: relative;
    overflow: hidden;
  }

  .search-card::before {
    content: '';
    position: absolute;
    top: -1px; left: 10%; right: 10%;
    height: 2px;
    background: linear-gradient(90deg, transparent, var(--accent), transparent);
  }

  .form-group {
    margin-bottom: 1.5rem;
  }

  label {
    display: block;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--muted);
    margin-bottom: 0.5rem;
  }

  input[type="text"], input[type="number"] {
    width: 100%;
    background: var(--bg);
    border: 1px solid var(--border);
    color: var(--text);
    padding: 0.875rem 1rem;
    border-radius: 6px;
    font-family: 'DM Sans', sans-serif;
    font-size: 1rem;
    transition: border-color 0.2s;
    outline: none;
  }

  input:focus { border-color: var(--accent); }

  .form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
  }

  .btn-primary {
    width: 100%;
    padding: 1rem;
    background: var(--accent);
    color: #000;
    border: none;
    border-radius: 6px;
    font-family: 'Bebas Neue', sans-serif;
    font-size: 1.2rem;
    letter-spacing: 0.1em;
    cursor: pointer;
    transition: opacity 0.2s, transform 0.1s;
    margin-top: 0.5rem;
  }

  .btn-primary:hover { opacity: 0.9; transform: translateY(-1px); }
  .btn-primary:active { transform: translateY(0); }

  .error-msg {
    background: rgba(239,68,68,0.1);
    border: 1px solid rgba(239,68,68,0.3);
    color: #FCA5A5;
    padding: 0.875rem 1rem;
    border-radius: 6px;
    margin-bottom: 1.5rem;
    font-size: 0.9rem;
  }

  /* Result card */
  .result-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 12px;
    overflow: hidden;
    margin-top: 2rem;
    animation: slideUp 0.4s ease;
  }

  @keyframes slideUp {
    from { opacity: 0; transform: translateY(20px); }
    to   { opacity: 1; transform: translateY(0); }
  }

  .result-header {
    padding: 1.5rem 2rem;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
  }

  .invoice-label {
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--muted);
    margin-bottom: 0.25rem;
  }

  .invoice-num {
    font-family: 'Bebas Neue', sans-serif;
    font-size: 2rem;
    letter-spacing: 0.05em;
    color: var(--accent);
  }

  .status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1.25rem;
    border-radius: 100px;
    font-size: 0.85rem;
    font-weight: 500;
    background: rgba(255,255,255,0.05);
    border: 1px solid currentColor;
  }

  .status-dot {
    width: 8px; height: 8px;
    border-radius: 50%;
    background: currentColor;
    animation: pulse 2s infinite;
  }

  @keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.4; }
  }

  .result-body {
    padding: 2rem;
  }

  .info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.25rem;
    margin-bottom: 2rem;
  }

  .info-item .label {
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--muted);
    margin-bottom: 0.25rem;
  }

  .info-item .value {
    font-size: 0.95rem;
    color: var(--text);
  }

  .progress-steps {
    display: flex;
    align-items: center;
    margin-bottom: 2rem;
    position: relative;
  }

  .progress-steps::before {
    content: '';
    position: absolute;
    top: 16px;
    left: 16px; right: 16px;
    height: 2px;
    background: var(--border);
    z-index: 0;
  }

  .step {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    position: relative;
    z-index: 1;
  }

  .step-dot {
    width: 32px; height: 32px;
    border-radius: 50%;
    background: var(--border);
    display: flex; align-items: center; justify-content: center;
    font-size: 0.75rem;
    font-weight: 600;
    border: 2px solid var(--border);
    color: var(--muted);
    transition: all 0.3s;
  }

  .step.active .step-dot, .step.done .step-dot {
    background: var(--accent);
    border-color: var(--accent);
    color: #000;
  }

  .step-label {
    font-size: 0.65rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--muted);
    text-align: center;
    white-space: nowrap;
  }

  .step.active .step-label, .step.done .step-label {
    color: var(--text);
  }

  .evidence-section {
    border-top: 1px solid var(--border);
    padding-top: 1.5rem;
  }

  .evidence-section h3 {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--muted);
    margin-bottom: 1rem;
  }

  .evidence-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
  }

  .evidence-img-wrap {
    border-radius: 8px;
    overflow: hidden;
    border: 1px solid var(--border);
    aspect-ratio: 4/3;
    background: var(--bg);
  }

  .evidence-img-wrap img {
    width: 100%; height: 100%;
    object-fit: cover;
  }

  .evidence-img-label {
    font-size: 0.75rem;
    color: var(--muted);
    text-align: center;
    margin-top: 0.4rem;
  }

  footer {
    position: relative; z-index: 1;
    padding: 1.5rem 3rem;
    border-top: 1px solid var(--border);
    text-align: center;
    color: var(--muted);
    font-size: 0.8rem;
  }

  @media (max-width: 580px) {
    header { padding: 1.5rem; }
    .form-row { grid-template-columns: 1fr; }
    .info-grid { grid-template-columns: 1fr; }
    .evidence-grid { grid-template-columns: 1fr; }
    .search-card { padding: 1.5rem; }
  }
</style>
</head>
<body>
<header>
  <a class="logo" href="index.php">HAL<span>C</span>ÓN</a>
  <nav class="header-nav">
    <a href="login.php">Acceso Personal</a>
  </nav>
</header>

<main>
  <div class="container">
    <div class="hero-text">
      <h1>ESTADO DE <em>TU PEDIDO</em></h1>
      <p>Ingresa tu número de cliente y el número de factura para consultar el estado actual de tu pedido.</p>
    </div>

    <div class="search-card">
      <?php if ($error): ?>
        <div class="error-msg">⚠ <?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="POST">
        <div class="form-row">
          <div class="form-group">
            <label>Número de Cliente</label>
            <input type="text" name="customer_num" placeholder="Ej. 5001" value="<?= htmlspecialchars($_POST['customer_num'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label>Número de Factura</label>
            <input type="text" name="invoice_num" placeholder="Ej. 1001" value="<?= htmlspecialchars($_POST['invoice_num'] ?? '') ?>">
          </div>
        </div>
        <button type="submit" class="btn-primary">CONSULTAR PEDIDO →</button>
      </form>
    </div>

    <?php if ($result): 
      global $STATUS_LABELS, $STATUS_COLORS;
      $statusLabel = $STATUS_LABELS[$result['status']] ?? $result['status'];
      $statusColor = $STATUS_COLORS[$result['status']] ?? '#fff';
      $steps = ['ordered','in_process','in_route','delivered'];
      $currentIdx = array_search($result['status'], $steps);
    ?>
    <div class="result-card">
      <div class="result-header">
        <div>
          <div class="invoice-label">Factura</div>
          <div class="invoice-num">#<?= htmlspecialchars($result['invoice_num']) ?></div>
        </div>
        <div class="status-badge" style="color:<?= $statusColor ?>">
          <span class="status-dot"></span>
          <?= $statusLabel ?>
        </div>
      </div>
      <div class="result-body">
        <div class="info-grid">
          <div class="info-item">
            <div class="label">Cliente</div>
            <div class="value"><?= htmlspecialchars($result['customer_name']) ?></div>
          </div>
          <div class="info-item">
            <div class="label">N° Cliente</div>
            <div class="value"><?= htmlspecialchars($result['customer_num']) ?></div>
          </div>
          <div class="info-item">
            <div class="label">Fecha del Pedido</div>
            <div class="value"><?= htmlspecialchars($result['order_date']) ?></div>
          </div>
          <div class="info-item">
            <div class="label">Dirección de Entrega</div>
            <div class="value"><?= htmlspecialchars($result['delivery_address']) ?></div>
          </div>
        </div>

        <div class="progress-steps">
          <?php foreach ($steps as $i => $step): 
            $isDone = $i < $currentIdx;
            $isActive = $i <= $currentIdx;
            $labels = ['Ordenado','En Proceso','En Ruta','Entregado'];
          ?>
          <div class="step <?= $isDone ? 'done' : ($isActive ? 'active' : '') ?>">
            <div class="step-dot"><?= $isDone ? '✓' : ($i+1) ?></div>
            <div class="step-label"><?= $labels[$i] ?></div>
          </div>
          <?php endforeach; ?>
        </div>

        <?php if ($result['status'] === 'delivered'): ?>
        <div class="evidence-section">
          <h3>Evidencia de Entrega</h3>
          <div class="evidence-grid">
            <?php if (!empty($result['photo_route'])): ?>
            <div>
              <div class="evidence-img-wrap">
                <img src="uploads/route/<?= htmlspecialchars($result['photo_route']) ?>" alt="Foto unidad cargada">
              </div>
              <div class="evidence-img-label">Unidad cargada</div>
            </div>
            <?php endif; ?>
            <?php if (!empty($result['photo_delivery'])): ?>
            <div>
              <div class="evidence-img-wrap">
                <img src="uploads/evidence/<?= htmlspecialchars($result['photo_delivery']) ?>" alt="Foto entrega">
              </div>
              <div class="evidence-img-label">Material entregado</div>
            </div>
            <?php endif; ?>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>
</main>

<footer>
  &copy; <?= date('Y') ?> Halcón Materiales de Construcción — Todos los derechos reservados
</footer>
</body>
</html>
