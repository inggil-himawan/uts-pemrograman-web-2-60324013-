<?php
require_once 'config/database.php';

// Validasi ID dari GET
$id = $_GET['id'] ?? null;

if ($id && is_numeric($id)) {
    // Cek keberadaan data
    $stmtCheck = $conn->prepare("SELECT id_kategori FROM kategori WHERE id_kategori = ?");
    $stmtCheck->bind_param("i", $id);
    $stmtCheck->execute();
    $result = $stmtCheck->get_result();

    if ($result->num_rows > 0) {
        // Data ada, lakukan proses delete
        $stmtDelete = $conn->prepare("DELETE FROM kategori WHERE id_kategori = ?");
        $stmtDelete->bind_param("i", $id);
        
        if ($stmtDelete->execute() && $stmtDelete->affected_rows > 0) {
            $_SESSION['success'] = "Kategori berhasil dihapus.";
        } else {
            $_SESSION['error'] = "Gagal menghapus kategori: " . $conn->error;
        }
        $stmtDelete->close();
    } else {
        $_SESSION['error'] = "Kategori tidak ditemukan.";
    }
    $stmtCheck->close();
} else {
    $_SESSION['error'] = "ID Kategori tidak valid.";
}

// Redirect dengan pesan
header("Location: index.php");
exit();
?>