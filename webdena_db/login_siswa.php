<?php
// login_siswa.php - API Login untuk Unity
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

include 'config.php';

// Hanya terima POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Ambil data JSON dari Unity
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
    exit;
}

$username = isset($data['username']) ? trim($data['username']) : '';
$password = isset($data['password']) ? trim($data['password']) : '';

// Validasi input
if (empty($username) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Username dan password harus diisi']);
    exit;
}

try {
    // Cari siswa berdasarkan username dan password
    $stmt = $pdo->prepare("SELECT * FROM siswa WHERE username = ? AND password = MD5(?) AND status = 'aktif'");
    $stmt->execute([$username, $password]);
    $siswa = $stmt->fetch();
    
    if ($siswa) {
        // Login berhasil
        $response = [
            'success' => true,
            'message' => 'Login berhasil',
            'data' => [
                'id_siswa' => $siswa['id_siswa'],
                'nama_siswa' => $siswa['nama_siswa'],
                'kelas' => $siswa['kelas'],
                'username' => $siswa['username'],
                'login_time' => date('Y-m-d H:i:s')
            ]
        ];
        
        // Update last login (opsional, bisa ditambah kolom last_login di tabel siswa)
        $stmt_update = $pdo->prepare("UPDATE siswa SET updated_at = NOW() WHERE id_siswa = ?");
        $stmt_update->execute([$siswa['id_siswa']]);
        
    } else {
        // Login gagal
        $response = [
            'success' => false,
            'message' => 'Username atau password salah, atau akun tidak aktif'
        ];
    }
    
} catch (Exception $e) {
    $response = [
        'success' => false,
        'message' => 'Terjadi kesalahan server: ' . $e->getMessage()
    ];
}

echo json_encode($response);
?>