<?php
session_start();

define('DB_FILE', __DIR__ . '/data/db.json');
define('UPLOADS_DIR', __DIR__ . '/uploads/');

// Auto-create required directories and default DB if missing
if (!is_dir(__DIR__ . '/data')) {
    mkdir(__DIR__ . '/data', 0775, true);
}
if (!is_dir(__DIR__ . '/uploads/route')) {
    mkdir(__DIR__ . '/uploads/route', 0775, true);
}
if (!is_dir(__DIR__ . '/uploads/evidence')) {
    mkdir(__DIR__ . '/uploads/evidence', 0775, true);
}
if (!file_exists(DB_FILE)) {
    $defaultDB = [
        "users" => [
            [
                "id"       => 1,
                "username" => "admin",
                "password" => hash('sha256', 'admin'),
                "name"     => "Administrador",
                "role"     => "admin",
                "active"   => true
            ]
        ],
        "orders"          => [],
        "next_invoice"    => 1001,
        "next_customer"   => 5001
    ];
    file_put_contents(DB_FILE, json_encode($defaultDB, JSON_PRETTY_PRINT));
}

function loadDB() {
    return json_decode(file_get_contents(DB_FILE), true);
}

function saveDB($data) {
    file_put_contents(DB_FILE, json_encode($data, JSON_PRETTY_PRINT));
}

function hashPassword($pass) {
    return hash('sha256', $pass);
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getCurrentUser() {
    if (!isLoggedIn()) return null;
    $db = loadDB();
    foreach ($db['users'] as $u) {
        if ($u['id'] == $_SESSION['user_id']) return $u;
    }
    return null;
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function hasRole($roles) {
    $user = getCurrentUser();
    if (!$user) return false;
    if (!is_array($roles)) $roles = [$roles];
    return in_array($user['role'], $roles);
}

function requireRole($roles) {
    requireLogin();
    if (!hasRole($roles)) {
        header('Location: dashboard.php?error=unauthorized');
        exit;
    }
}

$STATUS_LABELS = [
    'ordered'    => 'Ordenado',
    'in_process' => 'En proceso',
    'in_route'   => 'En ruta',
    'delivered'  => 'Entregado',
];

$STATUS_COLORS = [
    'ordered'    => '#F59E0B',
    'in_process' => '#3B82F6',
    'in_route'   => '#8B5CF6',
    'delivered'  => '#10B981',
];

$ROLE_LABELS = [
    'admin'     => 'Administrador',
    'sales'     => 'Ventas',
    'purchasing'=> 'Compras',
    'warehouse' => 'Almacén',
    'route'     => 'Ruta',
];