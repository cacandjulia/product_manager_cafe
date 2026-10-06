<?php
require_once 'config.php';

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
$is_edit = false;
$errors = [];

$data = [
    'name' => '',
    'category' => 'Makanan',
    'price' => '',
    'stock' => ''
];

// Syarat Query SELECT BY ID
if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $product = $stmt->fetch();

    if ($product) {
        $is_edit = true;
        $data = $product;
    } else {
        set_flash("Produk tidak ditemukan!", "danger");
        header("Location: index.php");
        exit;
    }
}

// PROSES CREATE & UPDATE (Mencegah data ganda saat refresh via PRG Pattern)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate($_POST['csrf_token'] ?? null)) {
        die("CSRF Token Verification Failed.");
    }

    $data['name'] = trim($_POST['name'] ?? '');
    $data['category'] = trim($_POST['category'] ?? '');
    $data['price'] = trim($_POST['price'] ?? '');
    $data['stock'] = trim($_POST['stock'] ?? '');

    // ATURAN VALIDASI WAJIB
    if (mb_strlen($data['name']) < 3) {
        $errors[] = "Nama produk minimal 3 karakter.";
    }

    if (!is_numeric($data['price']) || floatval($data['price']) <= 0) {
        $errors[] = "Harga harus angka dan lebih besar dari 0 (> 0).";
    }

    if (!is_numeric($data['stock']) || intval($data['stock']) < 0) {
        $errors[] = "Stok harus angka dan tidak boleh negatif (>= 0).";
    }

    // Validasi Nama Unik (Syarat: Nama Unik)
    if (empty($errors)) {
        if ($is_edit) {
            $stmt_check = $pdo->prepare("SELECT id FROM products WHERE name = :name AND id != :id");
            $stmt_check->execute([':name' => $data['name'], ':id' => $id]);
        } else {
            $stmt_check = $pdo->prepare("SELECT id FROM products WHERE name = :name");
            $stmt_check->execute([':name' => $data['name']]);
        }

        if ($stmt_check->fetch()) {
            $errors[] = "Nama produk sudah digunakan! Silakan gunakan nama unik lain.";
        }
    }

    // SIMPAN / UPDATE DATA (JIKA LOLOS VALIDASI)
    if (empty($errors)) {
        if ($is_edit) {
            // Syarat Query UPDATE: $pdo->prepare(...)
            $stmt = $pdo->prepare("UPDATE products SET name = :name, category = :category, price = :price, stock = :stock WHERE id = :id");
            $stmt->execute([
                ':name' => $data['name'],
                ':category' => $data['category'],
                ':price' => $data['price'],
                ':stock' => $data['stock'],
                ':id' => $id
            ]);
            set_flash("Produk berhasil diperbarui!");
        } else {
            // Syarat Query INSERT: $pdo->prepare(...)
            $stmt = $pdo->prepare("INSERT INTO products (name, category, price, stock) VALUES (:name, :category, :price, :stock)");
            $stmt->execute([
                ':name' => $data['name'],
                ':category' => $data['category'],
                ':price' => $data['price'],
                ':stock' => $data['stock']
            ]);
            set_flash("Produk berhasil ditambahkan!");
        }

        // REDIRECT (PRG Pattern) - Menghindari data ganda saat refresh
        header("Location: index.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $is_edit ? 'Edit' : 'Tambah'; ?> Produk - Product Manager</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container" style="max-width: 600px;">
        <header class="header">
            <h1><?= $is_edit ? 'Edit' : 'Tambah'; ?> Produk</h1>
            <a href="index.php" class="btn secondary">&larr; Kembali</a>
        </header>

        <?php if (!empty($errors)): ?>
            <div class="alert danger">
                <ul style="margin:0; padding-left: 20px;">
                    <?php foreach ($errors as $err): ?>
                        <li><?= e($err); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="" class="form-card">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>">

            <div class="form-group">
                <label>Nama Produk (Min. 3 Karakter, Unik):</label>
                <input type="text" name="name" value="<?= e($data['name']); ?>" required>
            </div>

            <div class="form-group">
                <label>Kategori:</label>
                <select name="category" required>
                    <option value="Makanan" <?= $data['category'] === 'Makanan' ? 'selected' : ''; ?>>Makanan</option>
                    <option value="Minuman" <?= $data['category'] === 'Minuman' ? 'selected' : ''; ?>>Minuman</option>
                    <option value="Cemilan" <?= $data['category'] === 'Cemilan' ? 'selected' : ''; ?>>Cemilan</option>
                </select>
            </div>

            <div class="form-group">
                <label>Harga (Rp) (> 0):</label>
                <input type="number" step="0.01" name="price" value="<?= e((string)$data['price']); ?>" required>
            </div>

            <div class="form-group">
                <label>Stok (&ge; 0):</label>
                <input type="number" name="stock" value="<?= e((string)$data['stock']); ?>" required>
            </div>

            <button type="submit" class="btn primary" style="width: 100%;">
                <?= $is_edit ? 'Simpan Perubahan' : 'Simpan Produk Baru'; ?>
            </button>
        </form>
    </div>
</body>
</html>