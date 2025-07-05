<?php
// hapus_siswa.php - Hapus data siswa
include 'config.php';

$id_siswa = isset($_GET['id']) ? $_GET['id'] : 0;

// Ambil data siswa berdasarkan ID
$stmt = $pdo->prepare("SELECT * FROM siswa WHERE id_siswa = ?");
$stmt->execute([$id_siswa]);
$siswa = $stmt->fetch();

if (!$siswa) {
    die("Siswa tidak ditemukan!");
}

$message = '';

if ($_POST && isset($_POST['konfirmasi_hapus'])) {
    try {
        // Mulai transaction
        $pdo->beginTransaction();
        
        // Hapus relasi skor siswa terlebih dahulu (set id_siswa = NULL)
        $stmt = $pdo->prepare("UPDATE skor SET id_siswa = NULL WHERE id_siswa = ?");
        $stmt->execute([$id_siswa]);
        
        // Hapus data siswa
        $stmt = $pdo->prepare("DELETE FROM siswa WHERE id_siswa = ?");
        $stmt->execute([$id_siswa]);
        
        // Commit transaction
        $pdo->commit();
        
        // Redirect ke daftar siswa dengan pesan sukses
        header("Location: daftar_siswa.php?message=Siswa berhasil dihapus");
        exit();
        
    } catch (Exception $e) {
        // Rollback jika ada error
        $pdo->rollback();
        $message = "Gagal menghapus siswa: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Hapus Siswa</title>
</head>
<body>
    <h1>Hapus Data Siswa</h1>
    
    <a href="daftar_siswa.php">← Kembali ke Daftar Siswa</a>
    
    <?php if ($message): ?>
        <p><strong style="color: red;"><?php echo $message; ?></strong></p>
    <?php endif; ?>
    
    <h3>Konfirmasi Penghapusan</h3>
    <p><strong style="color: red;">PERINGATAN: Data yang sudah dihapus tidak dapat dikembalikan!</strong></p>
    
    <table border="1" cellpadding="5" cellspacing="0">
        <tr>
            <td>ID Siswa:</td>
            <td><strong><?php echo $siswa['id_siswa']; ?></strong></td>
        </tr>
        <tr>
            <td>Nama:</td>
            <td><strong><?php echo $siswa['nama_siswa']; ?></strong></td>
        </tr>
        <tr>
            <td>Kelas:</td>
            <td><strong><?php echo $siswa['kelas']; ?></strong></td>
        </tr>
        <tr>
            <td>Username:</td>
            <td><strong><?php echo $siswa['username']; ?></strong></td>
        </tr>
        <tr>
            <td>Status:</td>
            <td><strong><?php echo $siswa['status']; ?></strong></td>
        </tr>
        <tr>
            <td>Tanggal Daftar:</td>
            <td><strong><?php echo date('d/m/Y H:i', strtotime($siswa['created_at'])); ?></strong></td>
        </tr>
    </table>
    
    <?php
    // Cek apakah siswa ini punya data skor
    $stmt_skor = $pdo->prepare("SELECT COUNT(*) as total FROM skor WHERE id_siswa = ?");
    $stmt_skor->execute([$id_siswa]);
    $total_skor = $stmt_skor->fetch()['total'];
    ?>
    
    <?php if ($total_skor > 0): ?>
    <h3>Data Terkait</h3>
    <p>Siswa ini memiliki <strong><?php echo $total_skor; ?></strong> record skor game.</p>
    <p><em>Catatan: Data skor akan tetap ada di database, tapi tidak akan terhubung dengan siswa ini lagi.</em></p>
    <?php endif; ?>
    
    <h3>Apakah Anda yakin ingin menghapus siswa ini?</h3>
    <form method="POST">
        <input type="hidden" name="konfirmasi_hapus" value="1">
        <input type="submit" value="YA, HAPUS SISWA INI" style="background-color: red; color: white; padding: 10px;">
        <input type="button" value="BATAL" onclick="history.back()" style="padding: 10px;">
    </form>
</body>
</html>