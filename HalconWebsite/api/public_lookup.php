<?php
header('Content-Type: application/json; charset=utf-8');

// Ruta del archivo JSON que funciona como base de datos local.
$dbPath = __DIR__ . '/../data/db.json';
if (!file_exists($dbPath)) {
    echo json_encode(['ok' => false, 'message' => 'Base de datos no encontrada']);
    exit;
}

// Lee el body JSON enviado por fetch() y lo convierte a arreglo PHP.
$raw = file_get_contents('php://input');
$input = json_decode($raw, true);

// Captura y limpia los campos de búsqueda.
$customer = trim((string)($input['customer_number'] ?? ''));
$invoice = trim((string)($input['invoice_number'] ?? ''));

// Validación mínima: ambos campos son obligatorios.
if ($customer === '' || $invoice === '') {
    echo json_encode(['ok' => false, 'message' => 'Faltan datos']);
    exit;
}

$data = json_decode(file_get_contents($dbPath), true);
$orders = $data['orders'] ?? [];

foreach ($orders as $order) {
    // Ignora pedidos eliminados lógicamente.
    $isDeleted = $order['is_deleted'] ?? false;
    if ($isDeleted) continue;

    // Busca coincidencia exacta por cliente + factura.
    if ((string)$order['customer_number'] === $customer &&
        (string)$order['invoice_number'] === $invoice) {

        $response = [
            'ok' => true,
            'status' => $order['status'] ?? 'Unknown'
        ];

        // Regla del negocio: solo exponer evidencia cuando está Delivered.
        if (($order['status'] ?? '') === 'Delivered') {
            $response['delivery_photo'] = $order['delivery_photo'] ?? null;
        }

        echo json_encode($response);
        exit;
    }
}

// Si termina el loop sin match, no existe pedido con esos datos.
echo json_encode(['ok' => false, 'message' => 'Pedido no encontrado']);
