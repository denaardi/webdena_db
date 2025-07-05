<?php
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <title>Data Skor Siswa</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <style>
    body {
      background: #f4f6f9;
      font-family: 'Segoe UI', sans-serif;
    }
    .header-bar {
      background: #343a40;
      color: white;
      padding: 15px 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .header-bar h4 {
      margin: 0;
    }
    .stat-card {
      background-color: white;
      padding: 15px;
      margin: 10px 10px 20px 0;
      border-left: 5px solid #007bff;
      border-radius: 5px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    .stat-number {
      font-size: 28px;
      font-weight: bold;
    }
    .stat-label {
      font-size: 14px;
      color: #666;
    }
    .sidebar-custom {
    background-color: #3a3f47;
    color: white;
}

    .score-high { color: green; font-weight: bold; }
    .score-medium { color: orange; font-weight: bold; }
    .score-low { color: red; font-weight: bold; }
    .badge-listening { background: #007bff; color: white; padding: 4px 8px; border-radius: 5px; }
    .badge-reading { background: #28a745; color: white; padding: 4px 8px; border-radius: 5px; }
    .badge-writing { background: #ffc107; color: black; padding: 4px 8px; border-radius: 5px; }
    .table-container table { width: 100%; margin-top: 20px; }
    .btn { margin-right: 5px; }
    .no-data { text-align: center; margin: 30px 0; }
    .refresh-info { text-align: center; color: gray; font-size: 13px; margin-top: 20px; }
  </style>
</head>
<body>
<div class="container-fluid">
  <div class="row">
    <!-- Sidebar -->
<div class="col-md-2 sidebar sidebar-custom d-flex flex-column justify-content-between p-3" style="min-height: 100vh;">

  <div>
    <h4 class="mb-3">People Around Me - Learning</h4>
    <p class="small">Admin - Guru Bahasa Inggris Kelas VII</p>
    <hr>
    <nav class="nav flex-column">
      <a class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'fw-bold' : '' ?>" href="index.php">📊 Dashboard</a>
      <a class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) == 'daftar_siswa.php' ? 'fw-bold' : '' ?>" href="daftar_siswa.php">👨‍🏫 Data Siswa</a>
      <a class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) == 'laporan_skor.php' ? 'fw-bold' : '' ?>" href="laporan_skor.php">📄 Data Nilai</a>
      <form id="logoutForm" action="logout.php" method="post" onsubmit="return confirmLogout();">
    <button class="btn btn-danger btn-sm w-100">Logout</button>
</form>

<script>
function confirmLogout() {
    return confirm("Apakah Anda yakin ingin Logout?");
}
</script>

  </div>
</div>
    <!-- Main Content -->
    <div class="col-md-10 p-4">
      <h2>📄 Data Skor Siswa</h2>
      
<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "webdena_db";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Statistik
    $stmt = $pdo->query("SELECT COUNT(DISTINCT nama_siswa) as total FROM skor");
    $total_siswa = $stmt->fetch()['total'];

    $stmt = $pdo->query("SELECT COUNT(*) as total FROM skor");
    $total_attempts = $stmt->fetch()['total'];

    $stmt = $pdo->query("SELECT AVG(CAST(nilai as DECIMAL(5,2))) as avg_score FROM skor");
    $avg_score = round($stmt->fetch()['avg_score'], 1);

    $stmt = $pdo->query("SELECT COUNT(*) as today FROM skor WHERE DATE(created_at) = CURDATE()");
    $today_attempts = $stmt->fetch()['today'];
?>

<div class="row text-center mb-4">
  <div class="col-md-3">
    <div class="stat-card">
      <div class="stat-number"><?= $total_siswa ?></div>
      <div class="stat-label">Total Siswa</div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stat-card">
      <div class="stat-number"><?= $total_attempts ?></div>
      <div class="stat-label">Total Percobaan</div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stat-card">
      <div class="stat-number"><?= $avg_score ?></div>
      <div class="stat-label">Rata-rata Skor</div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stat-card">
      <div class="stat-number"><?= $today_attempts ?></div>
      <div class="stat-label">Hari Ini</div>
    </div>
  </div>
</div>

<!-- Filter -->
<div class="filters mb-3">
  <form method="GET" class="row g-2 align-items-center">
    <div class="col-md-3">
      <select name="tipe_soal" class="form-select">
        <option value="">Semua Tipe Soal</option>
        <option value="listening" <?= (isset($_GET['tipe_soal']) && $_GET['tipe_soal'] == 'listening') ? 'selected' : ''; ?>>Listening</option>
        <option value="reading" <?= (isset($_GET['tipe_soal']) && $_GET['tipe_soal'] == 'reading') ? 'selected' : ''; ?>>Reading</option>
        <option value="writting" <?= (isset($_GET['tipe_soal']) && $_GET['tipe_soal'] == 'writting') ? 'selected' : ''; ?>>Writing</option>
      </select>
    </div>
    <div class="col-md-3">
      <input type="text" name="nama_siswa" class="form-control" placeholder="Cari nama siswa..." value="<?= isset($_GET['nama_siswa']) ? htmlspecialchars($_GET['nama_siswa']) : ''; ?>">
    </div>
    <div class="col-md-3">
      <input type="date" name="tanggal" class="form-control" value="<?= isset($_GET['tanggal']) ? $_GET['tanggal'] : ''; ?>">
    </div>
    <div class="col-md-3">
      <button type="submit" class="btn btn-primary">🔍 Filter</button>
      <a href="?" class="btn btn-secondary">🔄 Reset</a>
    </div>
  </form>
</div>

<!-- Tabel Data Skor -->
<div class="table-container">
<?php
  $where = [];
  $params = [];

  if (!empty($_GET['tipe_soal'])) {
    $where[] = "tipe_soal = ?";
    $params[] = $_GET['tipe_soal'];
  }
  if (!empty($_GET['nama_siswa'])) {
    $where[] = "nama_siswa LIKE ?";
    $params[] = '%' . $_GET['nama_siswa'] . '%';
  }
  if (!empty($_GET['tanggal'])) {
    $where[] = "DATE(created_at) = ?";
    $params[] = $_GET['tanggal'];
  }

  $where_clause = $where ? "WHERE " . implode(" AND ", $where) : "";

  $sql = "SELECT id, nama_siswa, nilai, tipe_soal, created_at FROM skor $where_clause ORDER BY created_at DESC";
  $stmt = $pdo->prepare($sql);
  $stmt->execute($params);
  $results = $stmt->fetchAll();

  if (count($results) > 0):
?>
  <table class="table table-bordered table-striped">
    <thead class="table-light">
      <tr>
        <th>#</th>
        <th>Nama Siswa</th>
        <th>Skor</th>
        <th>Jenis Level</th>
        <th>Tanggal</th>
        <th>Jam</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($results as $i => $row): 
        $scoreClass = $row['nilai'] >= 80 ? 'score-high' : ($row['nilai'] >= 60 ? 'score-medium' : 'score-low');
        $badgeClass = 'badge-' . $row['tipe_soal'];
        if ($row['tipe_soal'] == 'writting') $badgeClass = 'badge-writing';
      ?>
      <tr>
        <td><?= $i + 1 ?></td>
        <td><strong><?= htmlspecialchars($row['nama_siswa']) ?></strong></td>
        <td><span class="score <?= $scoreClass ?>"><?= number_format($row['nilai'], 2) ?></span></td>
        <td><span class="badge <?= $badgeClass ?>"><?= ucfirst($row['tipe_soal']) ?></span></td>
        <td><?= date('d/m/Y', strtotime($row['created_at'])) ?></td>
        <td><?= date('H:i:s', strtotime($row['created_at'])) ?></td>
        <td>
          <form method="POST" action="hapus_skor.php" onsubmit="return confirm('Yakin ingin menghapus data ini?');">
            <input type="hidden" name="id" value="<?= $row['id'] ?>">
            <button type="submit" class="btn btn-danger btn-sm">🗑 Hapus</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php else: ?>
  <div class="no-data">
    <h3>📋 Tidak ada data yang ditemukan</h3>
    <p>Coba ubah filter atau tunggu data baru dari aplikasi Unity.</p>
  </div>
<?php endif; ?>
</div>


<?php
} catch (PDOException $e) {
  echo "<div class='no-data'>";
  echo "<h3>❌ Error Database</h3>";
  echo "<p>" . $e->getMessage() . "</p>";
  echo "</div>";
}
?>

</div>
</div>
</div>
</body>
</html>
