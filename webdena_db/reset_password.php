<?php
include 'config.php';

$message = '';
$message_type = '';
$id_siswa = isset($_GET['id']) ? $_GET['id'] : 0;

// Ambil data siswa berdasarkan ID
$stmt = $pdo->prepare("SELECT * FROM siswa WHERE id_siswa = ?");
$stmt->execute([$id_siswa]);
$siswa = $stmt->fetch();

if (!$siswa) {
    die("Siswa tidak ditemukan!");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nis_baru = $_POST['nis_baru'];
    $nama = $_POST['nama_siswa'];
    $kelas = $_POST['kelas'];
    $username = $_POST['username'];
    $status = $_POST['status'];
    $password_baru = $_POST['password_baru'];
    $konfirmasi_password = $_POST['konfirmasi_password'];

    if (empty($nis_baru) || empty($nama) || empty($kelas) || empty($username)) {
        $message = "NIS, nama, kelas, dan username harus diisi!";
        $message_type = "danger";
    } else {
        // Validasi NIS hanya angka
        if (!is_numeric($nis_baru)) {
            $message = "NIS harus berupa angka!";
            $message_type = "danger";
        } else if ($nis_baru > 2147483647) {
            $message = "NIS terlalu besar! Maksimal 10 digit.";
            $message_type = "danger";
        } else if ($nis_baru <= 0) {
            $message = "NIS harus lebih dari 0!";
            $message_type = "danger";
        } else {
            // Cek NIS sudah digunakan siswa lain (kecuali siswa yang sedang diedit)
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM siswa WHERE id_siswa = ? AND id_siswa != ?");
            $stmt->execute([$nis_baru, $id_siswa]);

            if ($stmt->fetchColumn() > 0) {
                $message = "NIS sudah digunakan oleh siswa lain!";
                $message_type = "danger";
            } else {
                // Cek username sudah digunakan siswa lain
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM siswa WHERE username = ? AND id_siswa != ?");
                $stmt->execute([$username, $id_siswa]);

                if ($stmt->fetchColumn() > 0) {
                    $message = "Username sudah digunakan oleh siswa lain!";
                    $message_type = "danger";
                } elseif (!empty($password_baru)) {
                    // Validasi password
                    if ($password_baru !== $konfirmasi_password) {
                        $message = "Password dan konfirmasi tidak sama!";
                        $message_type = "danger";
                    } elseif (strlen($password_baru) < 6) {
                        $message = "Password minimal 6 karakter!";
                        $message_type = "danger";
                    } else {
                        // Update dengan password baru (termasuk NIS)
                        $stmt = $pdo->prepare("UPDATE siswa SET id_siswa = ?, nama_siswa = ?, kelas = ?, username = ?, status = ?, password = MD5(?), password_hint = ?, updated_at = NOW() WHERE id_siswa = ?");
                        $success = $stmt->execute([$nis_baru, $nama, $kelas, $username, $status, $password_baru, $password_baru, $id_siswa]);
                        
                        if ($success) {
                            $message = "Data dan password berhasil diupdate!";
                            $message_type = "success";
                            // Update ID untuk fetch data yang baru
                            $id_siswa = $nis_baru;
                        } else {
                            $message = "Gagal mengupdate data siswa!";
                            $message_type = "danger";
                        }
                    }
                } else {
                    // Update tanpa password (termasuk NIS)
                    $stmt = $pdo->prepare("UPDATE siswa SET id_siswa = ?, nama_siswa = ?, kelas = ?, username = ?, status = ?, updated_at = NOW() WHERE id_siswa = ?");
                    $success = $stmt->execute([$nis_baru, $nama, $kelas, $username, $status, $id_siswa]);
                    
                    if ($success) {
                        $message = "Data siswa berhasil diupdate!";
                        $message_type = "success";
                        // Update ID untuk fetch data yang baru
                        $id_siswa = $nis_baru;
                    } else {
                        $message = "Gagal mengupdate data siswa!";
                        $message_type = "danger";
                    }
                }
            }
        }

        // Refresh data siswa dengan ID yang mungkin sudah berubah
        $stmt = $pdo->prepare("SELECT * FROM siswa WHERE id_siswa = ?");
        $stmt->execute([$id_siswa]);
        $siswa = $stmt->fetch();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit & Reset Password Siswa - SPT-Learning</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background: #f4f6f9;
            margin: 0;
            padding: 0;
        }
        
        .container-fluid {
            padding: 0;
        }
        
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            min-height: 100vh;
            background: #343a40;
            color: white;
            padding: 20px;
            overflow-y: auto;
            z-index: 1000;
        }
        
        .sidebar a {
            display: block;
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 5px;
            transition: background-color 0.3s;
        }
        
        .sidebar a:hover {
            background: #495057;
            text-decoration: none;
            color: white;
        }
        
        .sidebar a.active {
            background: #495057;
            font-weight: bold;
        }
        
        .main-content {
            margin-left: 250px;
            min-height: 100vh;
            padding: 20px;
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
        
        .required {
            color: red;
        }
        
        .student-info {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .default-password {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 10px;
            font-family: monospace;
        }
        
        /* Responsive design */
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                position: relative;
                min-height: auto;
            }
            
            .main-content {
                margin-left: 0;
            }
        }

        .form-label {
    font-weight: 600;
}

.form-control, .form-select {
    border: 2px solid #ced4da;
    font-weight: 500;
    padding: 10px 14px;
    box-shadow: none;
}

.form-control:focus, .form-select:focus {
    border-color: #495057;
    box-shadow: 0 0 5px rgba(0,0,0,0.1);
}

    </style>
</head>
<body>
    <div class="container-fluid">
        <!-- Sidebar -->
        <div class="sidebar">
            <h4>People Around Me - Learning</h4>
            <p>Admin - Guru Bahasa Inggris Kelas VII</p>
            <hr>
            <a href="index.php">📊 Dashboard</a>
            <a href="daftar_siswa.php" class="active">👨‍🏫 Data Siswa</a>
            <a href="laporan_skor.php">📄 Data Nilai</a>
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
        <div class="main-content">
            <h2>Edit Data Siswa</h2>
            
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="daftar_siswa.php">Data Siswa</a></li>
                    <li class="breadcrumb-item active">Edit Siswa</li>
                </ol>
            </nav>

            <!-- Message Alert -->
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                    <strong><?php echo $message; ?></strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Student Info Box -->
            <div class="student-info">
                <div class="row">
                    <div class="col-md-8">
                        <h4>📋 Informasi Siswa</h4>
                        <p class="mb-0"><strong>NIS:</strong> <?php echo $siswa['id_siswa']; ?></p>
                        <p class="mb-0"><strong>Nama:</strong> <?php echo $siswa['nama_siswa']; ?></p>
                        <p class="mb-0"><strong>Kelas:</strong> <?php echo $siswa['kelas']; ?></p>
                        <p class="mb-0"><strong>Username:</strong> <?php echo $siswa['username']; ?></p>
                        <p class="mb-0"><strong>Password Lama:</strong> <?php echo $siswa['password_hint']; ?></p>

                    </div>
                    <div class="col-md-4 text-end">
                        <h5>Status Siswa</h5>
                        <?php if($siswa['status'] == 'aktif'): ?>
                            <span class="badge bg-success fs-6">✅ Aktif</span>
                        <?php else: ?>
                            <span class="badge bg-secondary fs-6">❌ Tidak Aktif</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Form Card -->
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">✏️ Form Edit Data Siswa</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="mb-3">
                                    <label for="nis_baru" class="form-label">NIS (Nomor Induk Siswa) <span class="required">*</span></label>
                                    <input type="text" class="form-control" id="nis_baru" name="nis_baru" 
                                           value="<?php echo $siswa['id_siswa']; ?>" 
                                           placeholder="Contoh: 12345678 (maksimal 10 digit)" 
                                           maxlength="10" required>
                                    <div class="form-text">NIS harus berupa angka (maksimal 10 digit) dan tidak boleh sama dengan siswa lain.</div>
                                </div>

                                <div class="mb-3">
                                    <label for="nama_siswa" class="form-label">Nama Siswa <span class="required">*</span></label>
                                    <input type="text" class="form-control" id="nama_siswa" name="nama_siswa" 
                                           value="<?php echo $siswa['nama_siswa']; ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="kelas" class="form-label">Kelas <span class="required">*</span></label>
                                    <select class="form-select" id="kelas" name="kelas" required>
                                        <option value="">Pilih Kelas</option>
                                        <?php
                                        $kelasList = ['VII-A', 'VII-B', 'VII-C', 'VII-D', 'VII-E'];
                                        foreach ($kelasList as $kelas) {
                                            echo "<option value='$kelas'" . ($siswa['kelas'] == $kelas ? ' selected' : '') . ">$kelas</option>";
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="username" class="form-label">Username <span class="required">*</span></label>
                                    <input type="text" class="form-control" id="username" name="username" 
                                           value="<?php echo $siswa['username']; ?>" required>
                                    <div class="form-text">Username harus unik dan tidak boleh sama dengan siswa lain.</div>
                                </div>

                                <div class="mb-3">
                                    <label for="status" class="form-label">Status <span class="required">*</span></label>
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="aktif" <?php echo ($siswa['status'] == 'aktif') ? 'selected' : ''; ?>>Aktif</option>
                                        <option value="nonaktif" <?php echo ($siswa['status'] == 'nonaktif') ? 'selected' : ''; ?>>Nonaktif</option>
                                    </select>
                                </div>

                                <hr>
                                                                
                                <div class="mb-3">
                                    <label for="password_baru" class="form-label">Password Baru</label>
                                    <input type="password" class="form-control" id="password_baru" name="password_baru" 
                                           placeholder="Kosongkan jika tidak ingin mengubah password">
                                    <div class="form-text">Password minimal 6 karakter.</div>
                                </div>

                                <div class="mb-3">
                                    <label for="konfirmasi_password" class="form-label">Konfirmasi Password</label>
                                    <input type="password" class="form-control" id="konfirmasi_password" name="konfirmasi_password" 
                                           placeholder="Ulangi password baru">
                                </div>

                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <button type="button" class="btn btn-secondary me-md-2" onclick="history.back()">
                                        ← Kembali
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        💾 Simpan Perubahan
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Validate NIS input (only numbers and max length)
document.getElementById('nis_baru').addEventListener('input', function() {
    // Remove non-numeric characters
    this.value = this.value.replace(/[^0-9]/g, '');
    
    // Limit to 10 digits
    if (this.value.length > 10) {
        this.value = this.value.substring(0, 10);
    }
    
    // Check if value exceeds maximum integer
    if (parseInt(this.value) > 2147483647) {
        this.value = '2147483647';
    }
});

// Validate password confirmation
document.getElementById('konfirmasi_password').addEventListener('input', function() {
    const password = document.getElementById('password_baru').value;
    const confirm = this.value;
    
    if (password && confirm && password !== confirm) {
        this.setCustomValidity('Password tidak sama');
    } else {
        this.setCustomValidity('');
    }
});

// Clear confirmation when password is changed
document.getElementById('password_baru').addEventListener('input', function() {
    const confirm = document.getElementById('konfirmasi_password');
    if (this.value === '') {
        confirm.value = '';
    }
    confirm.dispatchEvent(new Event('input'));
});
</script>
</body>
</html>