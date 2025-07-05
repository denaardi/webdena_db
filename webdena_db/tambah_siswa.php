<?php
// tambah_siswa.php - Form tambah siswa baru
include 'config.php';

$message = '';
$message_type = '';

if ($_POST) {
    $nis = $_POST['nis']; // NIS sebagai ID siswa
    $nama = $_POST['nama_siswa'];
    $kelas = $_POST['kelas'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // Validasi input
    if (empty($nis) || empty($nama) || empty($kelas) || empty($username) || empty($password)) {
        $message = "Semua field harus diisi!";
        $message_type = "danger";
    } else {
        // Validasi NIS hanya angka
        if (!is_numeric($nis)) {
            $message = "NIS harus berupa angka!";
            $message_type = "danger";
        } else if ($nis > 2147483647) {
            $message = "NIS terlalu besar! Maksimal 10 digit.";
            $message_type = "danger";
        } else if ($nis <= 0) {
            $message = "NIS harus lebih dari 0!";
            $message_type = "danger";
        } else {
            // Cek NIS sudah ada atau belum
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM siswa WHERE id_siswa = ?");
            $stmt->execute([$nis]);
            
            if ($stmt->fetchColumn() > 0) {
                $message = "NIS sudah digunakan!";
                $message_type = "danger";
            } else {
                // Cek username sudah ada atau belum
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM siswa WHERE username = ?");
                $stmt->execute([$username]);
                
                if ($stmt->fetchColumn() > 0) {
                    $message = "Username sudah digunakan!";
                    $message_type = "danger";
                } else {
                    // Insert dengan NIS sebagai id_siswa
                    $stmt = $pdo->prepare("INSERT INTO siswa (id_siswa, nama_siswa, kelas, username, password, password_hint) VALUES (?, ?, ?, ?, MD5(?), ?)");
                    $success = $stmt->execute([$nis, $nama, $kelas, $username, $password, $password]);

                    if ($success) {
                        $message = "Siswa berhasil ditambahkan!";
                        $message_type = "success";
                        $_POST = array(); // reset form
                    } else {
                        $message = "Gagal menambahkan siswa!";
                        $message_type = "danger";
                    }
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tambah Siswa Baru</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
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
        .required {
            color: red;
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
        <div class="col-md-10 p-4">
            <h2>Tambah Siswa Baru</h2>
            
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="daftar_siswa.php">Data Siswa</a></li>
                    <li class="breadcrumb-item active">Tambah Siswa</li>
                </ol>
            </nav>

            <!-- Message Alert -->
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                    <strong><?php echo $message; ?></strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <!-- Form Card -->
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Form Tambah Siswa</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="mb-3">
                                    <label for="nis" class="form-label">NIS (Nomor Induk Siswa) <span class="required">*</span></label>
                                    <input type="text" class="form-control" id="nis" name="nis" 
                                           value="<?php echo isset($_POST['nis']) ? $_POST['nis'] : ''; ?>" 
                                           
                                           maxlength="10" required>
                                    <div class="form-text">NIS harus berupa angka (maksimal 10 digit) dan tidak boleh sama dengan siswa lain.</div>
                                </div>

                                <div class="mb-3">
                                    <label for="nama_siswa" class="form-label">Nama Siswa <span class="required">*</span></label>
                                    <input type="text" class="form-control" id="nama_siswa" name="nama_siswa" 
                                           value="<?php echo isset($_POST['nama_siswa']) ? $_POST['nama_siswa'] : ''; ?>" 
                                           
                                </div>

                                <div class="mb-3">
                                    <label for="kelas" class="form-label">Kelas <span class="required">*</span></label>
                                    <select class="form-select" id="kelas" name="kelas" required>
                                        <option value="">Pilih Kelas</option>
                                        <option value="VII-A" <?php echo (isset($_POST['kelas']) && $_POST['kelas'] == 'VII-A') ? 'selected' : ''; ?>>VII-A</option>
                                        <option value="VII-B" <?php echo (isset($_POST['kelas']) && $_POST['kelas'] == 'VII-B') ? 'selected' : ''; ?>>VII-B</option>
                                        <option value="VII-C" <?php echo (isset($_POST['kelas']) && $_POST['kelas'] == 'VII-C') ? 'selected' : ''; ?>>VII-C</option>
                                        <option value="VII-D" <?php echo (isset($_POST['kelas']) && $_POST['kelas'] == 'VII-D') ? 'selected' : ''; ?>>VII-D</option>
                                        <option value="VII-E" <?php echo (isset($_POST['kelas']) && $_POST['kelas'] == 'VII-E') ? 'selected' : ''; ?>>VII-E</option>

                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="username" class="form-label">Username <span class="required">*</span></label>
                                    <input type="text" class="form-control" id="username" name="username" 
                                           value="<?php echo isset($_POST['username']) ? $_POST['username'] : ''; ?>" 
                               
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password <span class="required">*</span></label>
                                    <input type="password" class="form-control" id="password" name="password" 
                                           
                                    
                                </div>

                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <button type="reset" class="btn btn-secondary me-md-2">Reset Form</button>
                                    <button type="submit" class="btn btn-primary">Tambah Siswa</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                
                </div>
            </div>

        </div> <!-- /main -->
    </div> <!-- /row -->
</div> <!-- /container -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Auto-generate username based on name (optional)
document.getElementById('nama_siswa').addEventListener('input', function() {
    const nama = this.value.toLowerCase().replace(/\s+/g, '');
    if (nama && !document.getElementById('username').value) {
        document.getElementById('username').value = nama;
    }
});

// Validate NIS input (only numbers and max length)
document.getElementById('nis').addEventListener('input', function() {
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
</script>
</body>
</html>