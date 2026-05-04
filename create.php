<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Kategori - UTS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php
    require_once 'config/database.php';
    
    $errors = [];
    $kode = '';
    $nama = '';
    $deskripsi = '';
    $status = 'Aktif';
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Ambil dan sanitasi data
        $kode = escape($conn, $_POST['kode_kategori'] ?? '');
        $nama = escape($conn, $_POST['nama_kategori'] ?? '');
        $deskripsi = escape($conn, $_POST['deskripsi'] ?? '');
        $status = escape($conn, $_POST['status'] ?? 'Aktif');
        
        // Validasi kode kategori
        if (empty($kode)) {
            $errors[] = "Kode Kategori wajib diisi.";
        } elseif (strlen($kode) < 4 || strlen($kode) > 10) {
            $errors[] = "Kode Kategori harus antara 4-10 karakter.";
        } elseif (!preg_match('/^KAT-/', $kode)) {
            $errors[] = "Kode Kategori harus diawali dengan 'KAT-'.";
        } else {
            // Cek duplikasi kode
            $stmtCheck = $conn->prepare("SELECT id_kategori FROM kategori WHERE kode_kategori = ?");
            $stmtCheck->bind_param("s", $kode);
            $stmtCheck->execute();
            if ($stmtCheck->get_result()->num_rows > 0) {
                $errors[] = "Kode Kategori sudah digunakan. Silakan gunakan kode lain.";
            }
            $stmtCheck->close();
        }
        
        // Validasi nama kategori
        if (empty($nama)) {
            $errors[] = "Nama Kategori wajib diisi.";
        } elseif (strlen($nama) < 3 || strlen($nama) > 50) {
            $errors[] = "Nama Kategori harus antara 3-50 karakter.";
        }
        
        // Validasi deskripsi
        if (!empty($deskripsi) && strlen($deskripsi) > 200) {
            $errors[] = "Deskripsi maksimal 200 karakter.";
        }
        
        // Validasi status
        if (!in_array($status, ['Aktif', 'Nonaktif'])) {
            $errors[] = "Status tidak valid.";
        }
        
        // Jika tidak ada error, insert data
        if (empty($errors)) {
            $sql = "INSERT INTO kategori (kode_kategori, nama_kategori, deskripsi, status) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $kode, $nama, $deskripsi, $status);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = "Kategori berhasil ditambahkan.";
                header("Location: index.php");
                exit();
            } else {
                $errors[] = "Gagal menyimpan data: " . $conn->error;
            }
            $stmt->close();
        }
    }
    ?>
    
    <div class="container mt-5 mb-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Tambah Kategori Baru</h4>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?= $error ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="kode_kategori" class="form-label">Kode Kategori <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="kode_kategori" name="kode_kategori" value="<?= $kode ?>" placeholder="Contoh: KAT-004" required>
                                <small class="text-muted">Awali dengan KAT- (Max 10 Karakter).</small>
                            </div>

                            <div class="mb-3">
                                <label for="nama_kategori" class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nama_kategori" name="nama_kategori" value="<?= $nama ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="deskripsi" class="form-label">Deskripsi</label>
                                <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"><?= $deskripsi ?></textarea>
                            </div>

                            <div class="mb-4">
                                <label class="form-label d-block">Status <span class="text-danger">*</span></label>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="status" id="statusAktif" value="Aktif" <?= ($status == 'Aktif') ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="statusAktif">Aktif</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="status" id="statusNonaktif" value="Nonaktif" <?= ($status == 'Nonaktif') ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="statusNonaktif">Nonaktif</label>
                                </div>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">Simpan</button>
                                <a href="index.php" class="btn btn-secondary">Kembali</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>