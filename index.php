<?php
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

include 'config.php';

// Ambil data siswa dan statistik
$stmt = $pdo->query("SELECT * FROM siswa ORDER BY created_at DESC");
$siswa_list = $stmt->fetchAll();

$total = $pdo->query("SELECT COUNT(*) as total FROM siswa")->fetch()['total'];
$aktif = $pdo->query("SELECT COUNT(*) as aktif FROM siswa WHERE status = 'aktif'")->fetch()['aktif'];

// Laporan skor data
try {
    $stmt = $pdo->query("SELECT COUNT(DISTINCT nama_siswa) as total FROM skor");
    $total_siswa = $stmt->fetch()['total'];

    $stmt = $pdo->query("SELECT COUNT(*) as total FROM skor");
    $total_attempts = $stmt->fetch()['total'];

    $stmt = $pdo->query("SELECT AVG(CAST(nilai as DECIMAL(5,2))) as avg_score FROM skor");
    $avg_score = round($stmt->fetch()['avg_score'], 1);

    $stmt = $pdo->query("SELECT COUNT(*) as today FROM skor WHERE DATE(created_at) = CURDATE()");
    $today_attempts = $stmt->fetch()['today'];

    $stmt = $pdo->query("SELECT COUNT(nilai) as total_nilai FROM skor");
    $total_nilai = $stmt->fetch()['total_nilai'] ?? 0;
} catch (PDOException $e) {
    $error_message = $e->getMessage();
}

// Tentukan halaman yang akan ditampilkan
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
?>

<!DOCTYPE html>
<html>
<head>
  <title>Dashboard Guru</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    body {
      background-color: #f4f6f9;
    }

    .sidebar {
      height: 100vh;
      background: #343a40;
      color: white;
      padding: 20px;
    }

    .sidebar a {
      display: block;
      color: white;
      text-decoration: none;
      padding: 10px 15px;
    }

    .sidebar a:hover {
      background: #495057;
    }

    .sidebar a.active {
      background: #495057;
      font-weight: bold;
    }

    .info-box {
      color: white;
      padding: 20px;
      border-radius: 5px;
      margin-bottom: 20px;
    }

    .main-content {
      padding: 25px;
      border-radius: 12px;
      margin-top: 20px;
      background-color: rgba(255, 255, 255, 0.9);
    }

    .main-content.dashboard {
      background-image: url('wallpaper.png');
      background-size: cover;
      background-repeat: no-repeat;
      background-position: center;
    }

    .card-header {
      background-color: #343a40;
      color: white;
    }

    /* Styles untuk halaman laporan skor */
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
    
    /* Styles untuk halaman daftar siswa */
    .detail-table {
      background: #f8f9fa;
    }
    .detail-table th {
      background: #e9ecef;
      width: 30%;
    }
    .modal-header.bg-danger {
      background-color: #dc3545 !important;
      color: white;
    }
    .table-responsive {
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
  </style>
</head>
<body>
<div class="container-fluid">
  <div class="row">

    <!-- Sidebar -->
    <div class="col-md-2 sidebar">
      <h4>People Around Me-Learning</h4>
      <p>Admin - Guru Bahasa Inggris Kelas VII</p>
      <hr>
      <a href="?page=dashboard" class="<?= ($page == 'dashboard') ? 'active' : '' ?>">📊 Dashboard</a>
      <a href="?page=daftar_siswa" class="<?= ($page == 'daftar_siswa') ? 'active' : '' ?>">👨‍🏫 Data Siswa</a>
      <a href="?page=laporan_skor" class="<?= ($page == 'laporan_skor') ? 'active' : '' ?>">📄 Data Nilai</a>
      <form id="logoutForm" action="logout.php" method="post" onsubmit="return confirmLogout();">
        <button class="btn btn-danger btn-sm w-100">Logout</button>
    </form>

<script>
function confirmLogout() {
    return confirm("Apakah Anda yakin ingin Logout?");
}
</script>

    </div>

    <!-- Main Content -->
    <div class="col-md-10 main-content <?= ($page == 'dashboard') ? 'dashboard' : '' ?>">
      
      <?php if ($page == 'dashboard'): ?>
        <!-- Konten Dashboard -->
        <div class="row mt-4">
          <center>  
            <h2 class="text-dark">Dashboard Guru</h2>
          </center>
        </div>

        <div class="row mt-4">
          <div class="col-md-4">
            <div class="info-box bg-primary">
              <h5>Jumlah Siswa</h5>
              <h2><?= $total ?></h2>
            </div>
          </div>

          <div class="col-md-4">
            <div class="info-box bg-success">
              <h5>Permainan</h5>
              <h2>3 Jenis Level</h2>
            </div>
          </div>

          <div class="col-md-4">
            <div class="info-box bg-warning">
              <h5>Nilai Masuk</h5>
              <h2><?= $total_nilai ?></h2>
            </div>
          </div>
        </div>

      <?php elseif ($page == 'laporan_skor'): ?>
        <!-- Konten Laporan Skor -->
        <div class="row mt-4">
          <center>  
            <h2 class="text-dark">📊 Data Skor Siswa</h2>
          </center>
        </div>

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
            <input type="hidden" name="page" value="laporan_skor">
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
              <a href="?page=laporan_skor" class="btn btn-secondary">🔄 Reset</a>
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

        <script>
          setTimeout(() => location.reload(), 30000);
          setInterval(() => {
            document.title = '📊 Data Skor - ' + new Date().toLocaleTimeString('id-ID');
          }, 1000);
        </script>

      <?php elseif ($page == 'daftar_siswa'): ?>
        <!-- Konten Daftar Siswa -->
        <div class="row mt-4">
          <div class="col-md-12">  
            <h2 class="text-dark">👨‍🏫 Data Siswa</h2>
          </div>
        </div>

        <!-- Alert untuk pesan -->
        <div id="alertMessage" class="alert alert-dismissible fade" role="alert" style="display: none;">
            <span id="alertText"></span>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        
        <!-- Action Buttons -->
        <div class="row mb-3">
            <div class="col-md-12">
                <a href="tambah_siswa.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Tambah Siswa Baru
                </a>
            </div>
        </div>

        <!-- Filter & Search Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Filter & Pencarian</h5>
            </div>
            <div class="card-body">
                <form method="GET">
                    <input type="hidden" name="page" value="daftar_siswa">
                    <div class="row">
                        <div class="col-md-3">
                            <label for="kelas" class="form-label">Kelas:</label>
                            <select name="kelas" id="kelas" class="form-select">
                                <option value="">Semua Kelas</option>
                                <?php 
                                $stmt_kelas = $pdo->query("SELECT DISTINCT kelas FROM siswa ORDER BY kelas");
                                $kelas_list = $stmt_kelas->fetchAll();
                                foreach($kelas_list as $kelas): 
                                ?>
                                    <option value="<?php echo $kelas['kelas']; ?>" <?php echo (isset($_GET['kelas']) && $_GET['kelas'] == $kelas['kelas']) ? 'selected' : ''; ?>>
                                        <?php echo $kelas['kelas']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="search" class="form-label">Cari:</label>
                            <input type="text" name="search" id="search" class="form-control" value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>" placeholder="Nama atau Username">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">Filter</button>
                            <a href="?page=daftar_siswa" class="btn btn-secondary">Reset</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Data Table -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Daftar Siswa
                <?php 
                // Query untuk daftar siswa dengan filter
                $kelas_filter = isset($_GET['kelas']) ? $_GET['kelas'] : '';
                $search = isset($_GET['search']) ? $_GET['search'] : '';

                $query = "SELECT * FROM siswa WHERE 1=1";
                $params = [];

                if ($kelas_filter) {
                    $query .= " AND kelas = ?";
                    $params[] = $kelas_filter;
                }

                if ($search) {
                    $query .= " AND (nama_siswa LIKE ? OR username LIKE ?)";
                    $params[] = "%$search%";
                    $params[] = "%$search%";
                }

                $query .= " ORDER BY kelas, nama_siswa";

                $stmt = $pdo->prepare($query);
                $stmt->execute($params);
                $siswa_list = $stmt->fetchAll();
                ?>
                (<?= count($siswa_list) ?> siswa)</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>No</th>
                                <th>NIS</th>
                                <th>Nama Siswa</th>
                                <th>Kelas</th>
                                <th>Username</th>
                                <th>Password</th>
                                <th>Status</th>
                                <th>Tanggal Daftar</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            foreach($siswa_list as $siswa): 
                            ?>
                            <tr id="row-<?php echo $siswa['id_siswa']; ?>">
                                <td><?php echo $no++; ?></td>
                                <td><?php echo $siswa['id_siswa']; ?></td>
                                <td><?php echo $siswa['nama_siswa']; ?></td>
                                <td><?php echo $siswa['kelas']; ?></td>
                                <td><?php echo $siswa['username']; ?></td>
                                <td><?php echo $siswa['password_hint'] ?? '-'; ?></td>
                                <td>
                                    <?php if($siswa['status'] == 'aktif'): ?>
                                        <span class="badge bg-success">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Tidak Aktif</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($siswa['created_at'])); ?></td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="reset_password.php?id=<?php echo $siswa['id_siswa']; ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <button class="btn btn-sm btn-danger" onclick="confirmDelete(<?php echo $siswa['id_siswa']; ?>, '<?php echo addslashes($siswa['nama_siswa']); ?>', '<?php echo $siswa['kelas']; ?>', '<?php echo $siswa['username']; ?>')">
                                            <i class="fas fa-trash"></i> Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <?php if(count($siswa_list) == 0): ?>
        <div class="alert alert-info mt-3">
            <h5>Tidak ada data siswa yang ditemukan</h5>
            <p>Silakan ubah filter atau tambah siswa baru.</p>
        </div>
        <?php endif; ?>

        <!-- Modal Konfirmasi Hapus -->
        <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-danger">
                        <h5 class="modal-title" id="deleteModalLabel">
                            <i class="fas fa-exclamation-triangle"></i> Konfirmasi Penghapusan Data
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <i class="fas fa-warning"></i>
                            <strong>PERINGATAN:</strong> Data yang sudah dihapus tidak dapat dikembalikan!
                        </div>
                        
                        <h6>Detail Siswa yang akan dihapus:</h6>
                        <table class="table table-bordered detail-table">
                            <tr>
                                <th>ID Siswa</th>
                                <td id="delete-id"></td>
                            </tr>
                            <tr>
                                <th>Nama Siswa</th>
                                <td id="delete-nama"></td>
                            </tr>
                            <tr>
                                <th>Kelas</th>
                                <td id="delete-kelas"></td>
                            </tr>
                            <tr>
                                <th>Username</th>
                                <td id="delete-username"></td>
                            </tr>
                        </table>
                        
                        <div id="skorInfo" class="alert alert-info" style="display: none;">
                            <i class="fas fa-info-circle"></i>
                            <strong>Informasi:</strong> Siswa ini memiliki data skor game yang akan dipisahkan dari akun siswa.
                        </div>
                        
                        <p class="text-danger fw-bold">
                            <i class="fas fa-question-circle"></i>
                            Apakah Anda yakin ingin menghapus data siswa ini?
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times"></i> Batal
                        </button>
                        <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                            <i class="fas fa-trash"></i> Ya, Hapus Data
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <script>
        let deleteId = null;

        function confirmDelete(id, nama, kelas, username) {
            deleteId = id;
            
            // Set data ke modal
            document.getElementById('delete-id').textContent = id;
            document.getElementById('delete-nama').textContent = nama;
            document.getElementById('delete-kelas').textContent = kelas;
            document.getElementById('delete-username').textContent = username;
            
            // Tampilkan modal
            const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
            modal.show();
        }

        // Handle konfirmasi hapus
        document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
            if (deleteId) {
                // Disable tombol untuk mencegah double click
                this.disabled = true;
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menghapus...';
                
                // Kirim request hapus via fetch
                fetch('daftar_siswa.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=hapus_siswa&id_siswa=' + deleteId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Hapus baris dari tabel
                        document.getElementById('row-' + deleteId).remove();
                        
                        // Tampilkan pesan sukses
                        showAlert('success', data.message);
                        
                        // Tutup modal
                        bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();
                        
                        // Update counter (optional)
                        location.reload(); // Atau update counter secara manual
                    } else {
                        showAlert('danger', data.message);
                    }
                })
                .catch(error => {
                    showAlert('danger', 'Terjadi kesalahan: ' + error.message);
                })
                .finally(() => {
                    // Re-enable tombol
                    this.disabled = false;
                    this.innerHTML = '<i class="fas fa-trash"></i> Ya, Hapus Data';
                });
            }
        });

        function showAlert(type, message) {
            const alertDiv = document.getElementById('alertMessage');
            const alertText = document.getElementById('alertText');
            
            alertDiv.className = 'alert alert-' + type + ' alert-dismissible fade show';
            alertText.textContent = message;
            alertDiv.style.display = 'block';
            
            // Auto hide after 5 seconds
            setTimeout(() => {
                alertDiv.style.display = 'none';
            }, 5000);
        }
        </script>

      <?php endif; ?>

    </div> <!-- /main content -->
  </div> <!-- /row -->
</div> <!-- /container -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>