<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

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
    echo json_encode(["error" => "Connection failed: " . $e->getMessage()]);
    exit();
}

// Handle different HTTP methods
$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'GET') {
    // GET request - untuk mengambil data (APIFetcher.cs)
    try {
        $stmt = $pdo->prepare("SELECT id, nama_siswa, nilai, tipe_soal, created_at FROM skor ORDER BY created_at DESC");
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Format tanggal untuk setiap record
        foreach ($result as &$record) {
            $record['tanggal_pengerjaan'] = date('Y-m-d H:i:s', strtotime($record['created_at']));
            $record['tanggal_format'] = date('d/m/Y H:i', strtotime($record['created_at']));
        }
        
        echo json_encode($result);
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => "Query failed: " . $e->getMessage()]);
    }
    
} elseif ($method == 'POST') {
    // POST request - untuk mengirim data baru (FinalScore.cs)
    
    // Validasi input
    if (!isset($_POST['nama_siswa']) || !isset($_POST['nilai']) || !isset($_POST['tipe_soal'])) {
        http_response_code(400);
        echo json_encode(["error" => "Missing required fields: nama_siswa, nilai, tipe_soal"]);
        exit();
    }
    
    $nama_siswa = trim($_POST['nama_siswa']);
    $nilai = trim($_POST['nilai']);
    $tipe_soal = trim($_POST['tipe_soal']);
    
    // Validasi data
    if (empty($nama_siswa) || empty($nilai) || empty($tipe_soal)) {
        http_response_code(400);
        echo json_encode(["error" => "Fields cannot be empty"]);
        exit();
    }
    
    // Validasi tipe_soal
    $allowed_types = ['listening', 'reading', 'writting'];
    if (!in_array(strtolower($tipe_soal), $allowed_types)) {
        http_response_code(400);
        echo json_encode(["error" => "Invalid tipe_soal. Allowed: listening, reading, writting"]);
        exit();
    }
    
    // Validasi nilai (harus angka)
    if (!is_numeric($nilai)) {
        http_response_code(400);
        echo json_encode(["error" => "Nilai must be a number"]);
        exit();
    }
    
    try {
        // Insert data ke database (tanpa waktu_pengerjaan)
        // created_at akan otomatis ter-set oleh MySQL
        $stmt = $pdo->prepare("INSERT INTO skor (nama_siswa, nilai, tipe_soal) VALUES (?, ?, ?)");
        $stmt->execute([$nama_siswa, $nilai, strtolower($tipe_soal)]);
        
        // Get ID dari record yang baru diinsert
        $lastId = $pdo->lastInsertId();
        
        // Ambil data yang baru diinsert untuk konfirmasi (termasuk created_at)
        $stmt = $pdo->prepare("SELECT id, nama_siswa, nilai, tipe_soal, created_at FROM skor WHERE id = ?");
        $stmt->execute([$lastId]);
        $insertedData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Response sukses
        echo json_encode([
            "status" => "success",
            "message" => "Data berhasil disimpan",
            "id" => $lastId,
            "data" => [
                "nama_siswa" => $insertedData['nama_siswa'],
                "nilai" => $insertedData['nilai'],
                "tipe_soal" => $insertedData['tipe_soal'],
                "created_at" => $insertedData['created_at'],
                "tanggal_format" => date('d/m/Y H:i:s', strtotime($insertedData['created_at']))
            ]
        ]);
        
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => "Failed to insert data: " . $e->getMessage()]);
    }
    
} else {
    // Method tidak didukung
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
}
?>