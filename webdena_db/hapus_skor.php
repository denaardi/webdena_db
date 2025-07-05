<?php
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    http_response_code(401);
    echo "Unauthorized";
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "webdena_db";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
        $id = $_POST['id'];
        
        // Validasi ID
        if (!is_numeric($id)) {
            http_response_code(400);
            echo "Invalid ID";
            exit();
        }
        
        // Hapus data
        $stmt = $pdo->prepare("DELETE FROM skor WHERE id = ?");
        $result = $stmt->execute([$id]);
        
        if ($result) {
            echo "Success";
        } else {
            http_response_code(500);
            echo "Failed to delete";
        }
    } else {
        http_response_code(400);
        echo "Invalid request";
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    echo "Database error: " . $e->getMessage();
}
?>