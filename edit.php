<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Kategori - UTS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php
    require_once 'config/database.php';
    
    $errors = [];
    $id = $_GET['id'] ?? null;

    // Jika ID tidak ada atau tidak valid
    if (!$id || !is_numeric($id)) {
        $_SESSION['error'] = "ID Kategori tidak valid.";
        header("Location: index.php");
        exit();
    }

    // Retrieve data berdasarkan ID
    $stmt = $conn->prepare("SELECT * FROM kategori WHERE id_kategori = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $_SESSION['error'] = "Data kategori tidak ditemukan.";
        header("Location: index.php");
        exit();
    }

    $data = $result->fetch_assoc();
    $kode = $data['kode_kategori'];
    $nama = $data['nama_kategori'];
    $deskripsi = $data['deskripsi'];
    $status = $data['status'];
    $stmt->close();
    
    // Jika POST, proses update
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $kode = escape($conn, $_POST['kode_kategori'] ?? '');
        $nama = escape($conn, $_POST['nama_kategori'] ?? '');
        $deskripsi = escape($conn, $_POST['deskripsi'] ?? '');
        $status = escape($conn, $_POST['status'] ?? 'Aktif');
        
        // Validasi sama dengan Create
        if (empty($kode)) $errors[] = "Kode Kategori wajib diisi.";
        elseif (strlen($kode) < 4 || strlen($kode) > 10) $errors[] = "Kode Kategori harus 4-10 karakter.";
        elseif (!preg_match('/^KAT-/', $kode)) $errors[] = "Kode Kategori harus diawali dengan 'KAT-'.";
        else {
            // Validasi duplikasi (exclude record saat ini)
            $stmtCheck = $conn->prepare("SELECT id_kategori FROM kategori WHERE kode_kategori = ? AND id_kategori != ?");
            $stmtCheck->bind_param("si", $kode, $id);
            $stmtCheck->execute();
            if ($stmtCheck->get_result()->num_rows > 0) {
                $errors[] = "Kode Kategori sudah digunakan oleh kategori lain.";
            }
            $stmtCheck->close();
        }
        
        if (empty($nama)) $errors[] = "Nama Kategori wajib diisi.";
        elseif (strlen($nama) < 3 || strlen($nama) > 50) $errors[] = "Nama Kategori harus 3-50 karakter.";
        
        if (!empty($deskripsi) && strlen($deskripsi) > 200) $errors[] = "Deskripsi maksimal 200 karakter.";
        if (!in_array($status, ['Aktif', 'Nonaktif'])) $errors[] = "Status tidak valid.";

        // Update data jika tidak ada error
        if (empty($errors)) {
            $sql = "UPDATE kategori SET kode_kategori = ?, nama_kategori = ?, deskripsi = ?, status = ? WHERE id_kategori = ?";
            $stmtUpdate = $conn->prepare($sql);
            $stmtUpdate->bind_param("ssssi", $kode, $nama, $deskripsi, $status, $id);
            
            if ($stmtUpdate->execute()) {
                $_SESSION['success'] = "Kategori berhasil diperbarui.";
                header("Location: index.php");
                exit();
            } else {
                $errors[] = "Gagal memperbarui data: " . $conn->error;
            }
            $stmtUpdate->close();
        }
    }
    ?>
    
    <div class="container mt-5 mb-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-warning">
                        <h4 class="mb-0 text-dark">Edit Kategori</h4>
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
                                <input type="text" class="form-control" id="kode_kategori" name="kode_kategori" value="<?= htmlspecialchars($kode) ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="nama_kategori" class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nama_kategori" name="nama_kategori" value="<?= htmlspecialchars($nama) ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="deskripsi" class="form-label">Deskripsi</label>
                                <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"><?= htmlspecialchars($deskripsi) ?></textarea>
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
                                <button type="submit" class="btn btn-warning">Update</button>
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