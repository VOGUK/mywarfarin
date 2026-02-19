<?php
session_start();
header('Content-Type: application/json');

$db_file = __DIR__ . '/mywarfarin.sqlite';
$db = new PDO('sqlite:' . $db_file);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Auto-install: Create tables
$db->exec("
    CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT, username TEXT UNIQUE, password TEXT, role TEXT, 
        name TEXT, dob TEXT, share_code TEXT UNIQUE, saved_share_code TEXT,
        target_min REAL DEFAULT 2.5, target_max REAL DEFAULT 3.5
    );
    CREATE TABLE IF NOT EXISTS inr_results (
        id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER, date TEXT, result REAL
    );
    CREATE TABLE IF NOT EXISTS dosages (
        id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER, date TEXT, dose REAL
    );
");

// Seed default admin
$stmt = $db->query("SELECT COUNT(*) FROM users");
if ($stmt->fetchColumn() == 0) {
    $hash = password_hash('admin', PASSWORD_DEFAULT);
    $share = substr(md5(uniqid()), 0, 10);
    $db->exec("INSERT INTO users (username, password, role, share_code) VALUES ('admin', '$hash', 'admin', '$share')");
}

$action = $_GET['action'] ?? '';

// Authentication
if ($action === 'login') {
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$data['username'] ?? '']);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($data['password'] ?? '', $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        echo json_encode(['success' => true, 'role' => $user['role']]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Incorrect username or password.']);
    }
    exit;
}

if ($action === 'logout') { session_destroy(); echo json_encode(['success' => true]); exit; }

if (!isset($_SESSION['user_id'])) { echo json_encode(['success' => false, 'error' => 'Unauthorized']); exit; }

$user_id = $_SESSION['user_id'];
$is_admin = $_SESSION['role'] === 'admin';
$is_viewer = $_SESSION['role'] === 'viewer';

// Determine which user's data to view
$stmt = $db->prepare("SELECT saved_share_code FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$saved_share = $stmt->fetchColumn();

$target_user_id = $user_id;
$view_only = $is_viewer;
if (!empty($saved_share)) {
    $stmt = $db->prepare("SELECT id FROM users WHERE share_code = ?");
    $stmt->execute([$saved_share]);
    $shared_id = $stmt->fetchColumn();
    if ($shared_id) {
        $target_user_id = $shared_id;
        $view_only = true;
    }
}

// Data Endpoints
if ($action === 'get_data') {
    $stmt = $db->prepare("SELECT name, dob, target_min, target_max, share_code, saved_share_code FROM users WHERE id = ?");
    $stmt->execute([$target_user_id]);
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $db->prepare("SELECT date, result FROM inr_results WHERE user_id = ? ORDER BY date DESC");
    $stmt->execute([$target_user_id]);
    $inr = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $db->prepare("SELECT date, dose FROM dosages WHERE user_id = ? ORDER BY date DESC");
    $stmt->execute([$target_user_id]);
    $dosages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'settings' => $settings, 'inr' => $inr, 'dosages' => $dosages, 'view_only' => $view_only]);
    exit;
}

if ($action === 'save_inr' && !$view_only) {
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $db->prepare("INSERT INTO inr_results (user_id, date, result) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $data['date'], $data['result']]);
    echo json_encode(['success' => true]); exit;
}

if ($action === 'save_dose' && !$view_only) {
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $db->prepare("DELETE FROM dosages WHERE user_id = ? AND date = ?");
    $stmt->execute([$user_id, $data['date']]);
    $stmt = $db->prepare("INSERT INTO dosages (user_id, date, dose) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $data['date'], $data['dose']]);
    echo json_encode(['success' => true]); exit;
}

// New Delete Dose Endpoint
if ($action === 'delete_dose' && !$view_only) {
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $db->prepare("DELETE FROM dosages WHERE user_id = ? AND date = ?");
    $stmt->execute([$user_id, $data['date']]);
    echo json_encode(['success' => true]); exit;
}

if ($action === 'save_settings' && !$view_only) {
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $db->prepare("UPDATE users SET name=?, dob=?, target_min=?, target_max=?, saved_share_code=? WHERE id=?");
    $stmt->execute([$data['name'], $data['dob'], $data['target_min'], $data['target_max'], $data['saved_share_code'], $user_id]);
    echo json_encode(['success' => true]); exit;
}

// Admin Endpoints
if ($action === 'admin_users' && $is_admin) {
    $stmt = $db->query("SELECT id, username, role FROM users");
    echo json_encode(['success' => true, 'users' => $stmt->fetchAll(PDO::FETCH_ASSOC)]); exit;
}

if ($action === 'admin_save_user' && $is_admin) {
    $data = json_decode(file_get_contents('php://input'), true);
    $share = substr(md5(uniqid()), 0, 10);
    $hash = password_hash($data['password'], PASSWORD_DEFAULT);
    
    if (empty($data['id'])) {
        $stmt = $db->prepare("INSERT INTO users (username, password, role, share_code) VALUES (?, ?, ?, ?)");
        $stmt->execute([$data['username'], $hash, $data['role'], $share]);
    } else {
        $stmt = $db->prepare("UPDATE users SET username=?, password=?, role=? WHERE id=?");
        $stmt->execute([$data['username'], $hash, $data['role'], $data['id']]);
    }
    echo json_encode(['success' => true]); exit;
}

if ($action === 'admin_delete_user' && $is_admin) {
    $data = json_decode(file_get_contents('php://input'), true);
    if ($data['id'] != $user_id) { 
        $db->prepare("DELETE FROM users WHERE id=?")->execute([$data['id']]);
        $db->prepare("DELETE FROM inr_results WHERE user_id=?")->execute([$data['id']]);
        $db->prepare("DELETE FROM dosages WHERE user_id=?")->execute([$data['id']]);
    }
    echo json_encode(['success' => true]); exit;
}

// Backup & Restore
if ($action === 'backup' && !$view_only) {
    $stmt = $db->prepare("SELECT date, result FROM inr_results WHERE user_id = ?"); $stmt->execute([$user_id]); $inr = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt = $db->prepare("SELECT date, dose FROM dosages WHERE user_id = ?"); $stmt->execute([$user_id]); $dose = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'backup' => ['inr' => $inr, 'dosages' => $dose]]); exit;
}

if ($action === 'restore' && !$view_only) {
    $data = json_decode(file_get_contents('php://input'), true);
    $db->prepare("DELETE FROM inr_results WHERE user_id=?")->execute([$user_id]);
    $db->prepare("DELETE FROM dosages WHERE user_id=?")->execute([$user_id]);
    
    $stmt_inr = $db->prepare("INSERT INTO inr_results (user_id, date, result) VALUES (?, ?, ?)");
    foreach ($data['inr'] as $row) $stmt_inr->execute([$user_id, $row['date'], $row['result']]);
    
    $stmt_dose = $db->prepare("INSERT INTO dosages (user_id, date, dose) VALUES (?, ?, ?)");
    foreach ($data['dosages'] as $row) $stmt_dose->execute([$user_id, $row['date'], $row['dose']]);
    
    echo json_encode(['success' => true]); exit;
}

echo json_encode(['success' => false, 'error' => 'Invalid endpoint']);
?>