<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "webdena_db";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "Database connection failed: " . $e->getMessage()
    ]);
    exit();
}

// Terima GET atau POST
$method = $_SERVER['REQUEST_METHOD'];
$id_siswa = '';

if ($method == 'POST') {
    $id_siswa = $_POST['id_siswa'] ?? '';
} elseif ($method == 'GET') {
    $id_siswa = $_GET['id_siswa'] ?? '';
} else {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "error" => "Only POST or GET method allowed"
    ]);
    exit();
}

// Validasi input
if (empty($id_siswa)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "error" => "id_siswa harus diisi"
    ]);
    exit();
}

try {
    // Cek apakah siswa masih aktif
    $stmt = $pdo->prepare("SELECT id_siswa, nama_siswa, kelas, username, status, updated_at 
                           FROM siswa 
                           WHERE id_siswa = ? AND status = 'aktif'");
    $stmt->execute([$id_siswa]);
    $siswa = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$siswa) {
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "error" => "Siswa tidak ditemukan atau akun tidak aktif"
        ]);
        exit();
    }
    
    // Cek apakah session masih valid (contoh: maksimal 2 jam dari last activity)
    $last_activity = strtotime($siswa['updated_at']);
    $current_time = time();
    $session_duration = 2 * 60 * 60; // 2 jam dalam detik
    
    if (($current_time - $last_activity) > $session_duration) {
        http_response_code(401);
        echo json_encode([
            "success" => false,
            "error" => "Session expired, silakan login kembali",
            "expired" => true
        ]);
        exit();
    }
    
    // Update last activity
    $update_stmt = $pdo->prepare("UPDATE siswa SET updated_at = CURRENT_TIMESTAMP WHERE id_siswa = ?");
    $update_stmt->execute([$id_siswa]);
    
    // Response sukses
    echo json_encode([
        "success" => true,
        "message" => "Token valid",
        "data" => [
            "id_siswa" => $siswa['id_siswa'],
            "nama_siswa" => $siswa['nama_siswa'],
            "kelas" => $siswa['kelas'],
            "username" => $siswa['username'],
            "status" => $siswa['status'],
            "last_activity" => $siswa['updated_at'],
            "verified_at" => date('Y-m-d H:i:s')
        ]
    ]);
    
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "Database error: " . $e->getMessage()
    ]);
}
?>