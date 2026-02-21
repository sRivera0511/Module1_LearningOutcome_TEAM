<?php
header('Content-Type: application/json; charset=utf-8');
session_start();

// Ruta del archivo JSON que funciona como base de datos local.
$dbPath = __DIR__ . '/../data/db.json';
if (!file_exists($dbPath)) {
    echo json_encode(['ok' => false, 'message' => 'Base de datos no encontrada']);
    exit;
}

// Lee el body JSON enviado por fetch() y lo convierte a arreglo PHP.
$raw = file_get_contents('php://input');
$input = json_decode($raw, true);

// Captura y limpia los campos.
$username = trim((string)($input['username'] ?? ''));
$password = (string)($input['password'] ?? '');

// Validación mínima: ambos campos son obligatorios.
if ($username === '' || $password === '') {
    echo json_encode(['ok' => false, 'message' => 'Faltan datos']);
    exit;
}

$data = json_decode(file_get_contents($dbPath), true);
$users = $data['users'] ?? [];

foreach ($users as $user) {
    // Solo usuarios activos.
    if (!($user['active'] ?? false)) continue;

    if ($user['username'] === $username && $user['password'] === $password) {
        // Guardar datos básicos en sesión (sin contraseña).
        $_SESSION['user'] = [
            'id'   => $user['id'],
            'name' => $user['name'],
            'role' => $user['role']
        ];

        echo json_encode(['ok' => true]);
        exit;
    }
}

echo json_encode(['ok' => false, 'message' => 'Usuario o contraseña incorrectos']);