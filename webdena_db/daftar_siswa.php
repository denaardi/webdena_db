<?php
// daftar_siswa.php - Daftar semua siswa
include 'config.php';

// Handle penghapusan siswa via AJAX
if ($_POST && isset($_POST['action']) && $_POST['action'] == 'hapus_siswa') {
    $id_siswa = $_POST['id_siswa'];
    
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
        
        echo json_encode(['success' => true, 'message' => 'Siswa berhasil dihapus']);
        exit();
        
    } catch (Exception $e) {
        // Rollback jika ada error
        $pdo->rollback();
        echo json_encode(['success' => false, 'message' => 'Gagal menghapus siswa: ' . $e->getMessage()]);
        exit();
    }
}

// Filter berdasarkan kelas jika ada
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

// Get unique kelas untuk filter
$stmt_kelas = $pdo->query("SELECT DISTINCT kelas FROM siswa ORDER BY kelas");
$kelas_list = $stmt_kelas->fetchAll();

// Statistik untuk info boxes
$total = $pdo->query("SELECT COUNT(*) as total FROM siswa")->fetch()['total'];
$aktif = $pdo->query("SELECT COUNT(*) as aktif FROM siswa WHERE status = 'aktif'")->fetch()['aktif'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Daftar Siswa</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: #f4f6f9;
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
        .card-header {
            background-color: #343a40;
            color: white;
        }
        .table-responsive {
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .modal-header.bg-danger {
            background-color: #dc3545 !important;
            color: white;
        }
        .detail-table {
            background: #f8f9fa;
        }
        .detail-table th {
            background: #e9ecef;
            width: 30%;
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-2 sidebar">
            <h4>People Around Me - Learning</h4>
            <p>Admin - Guru Bahasa Inggris Kelas VII</p>
            <hr>
            <a href="index.php">📊 Dasbor</a>
            <a href="daftar_siswa.php" class="active">👨‍🏫 Data Siswa</a>
            <a href="laporan_skor.php">📄 Laporan Nilai</a>
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
        <div class="col-md-10 p-4">
            <h2>Data Siswa</h2>
            
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
                        <div class="row">
                            <div class="col-md-3">
                                <label for="kelas" class="form-label">Kelas:</label>
                                <select name="kelas" id="kelas" class="form-select">
                                    <option value="">Semua Kelas</option>
                                    <?php foreach($kelas_list as $kelas): ?>
                                        <option value="<?php echo $kelas['kelas']; ?>" <?php echo ($kelas_filter == $kelas['kelas']) ? 'selected' : ''; ?>>
                                            <?php echo $kelas['kelas']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="search" class="form-label">Cari:</label>
                                <input type="text" name="search" id="search" class="form-control" value="<?php echo $search; ?>" placeholder="Nama atau Username">
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2">Filter</button>
                                <a href="daftar_siswa.php" class="btn btn-secondary">Reset</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Data Table -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Daftar Siswa (<?= count($siswa_list) ?> siswa)</h5>
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

        </div> <!-- /main -->
    </div> <!-- /row -->
</div> <!-- /container -->

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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
let deleteId = null;

function confirmDelete(id, nama, kelas, username) {
    deleteId = id;
    
    // Set data ke modal
    document.getElementById('delete-id').textContent = id;
    document.getElementById('delete-nama').textContent = nama;
    document.getElementById('delete-kelas').textContent = kelas;
    document.getElementById('delete-username').textContent = username;
    
    // Cek apakah siswa punya data skor (optional - bisa dihapus jika tidak diperlukan)
    // document.getElementById('skorInfo').style.display = 'block';
    
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
        fetch(window.location.href, {
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
</body>
</html>