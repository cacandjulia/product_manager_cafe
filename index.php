<?php
require_once 'config.php';

// Proteksi Auth
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// PROSES DELETE: wajib via POST + CSRF + Syarat Query DELETE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    if (!csrf_validate($_POST['csrf_token'] ?? null)) {
        set_flash("Token CSRF tidak valid!", "danger");
        header("Location: index.php");
        exit;
    }

    $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
    if ($id) {
        // Syarat Query DELETE: $pdo->prepare(...)
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = :id");
        $stmt->execute([':id' => $id]);
        set_flash("Produk berhasil dihapus!");
    } else {
        set_flash("ID Produk tidak valid!", "danger");
    }
    header("Location: index.php");
    exit;
}

// FETCH DATA PRODUK
$stmt = $pdo->prepare("SELECT * FROM products ORDER BY id DESC");
$stmt->execute();
$products = $stmt->fetchAll();

$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Manager</title>
    <link rel="stylesheet" href="style.css?v=500">
</head>
<body>
    <div class="container">
        <header class="header">
            <div>
                <h1>Product Manager</h1>
                <p>Kelola katalog produk kafe dengan aman dan nyaman.</p>
            </div>
            <div>
                <a href="form.php" class="btn primary">+ Tambah Produk</a>
                <a href="logout.php" class="btn danger" onclick="return confirm('Yakin ingin keluar?')">Logout</a>
            </div>
        </header>

        <?php if ($flash): ?>
            <div class="alert <?= e($flash['type']); ?>"><?= e($flash['msg']); ?></div>
        <?php endif; ?>

        <!-- FITUR WAJIB READ: Card Responsif -->
        <div class="product-grid">
            <?php if (empty($products)): ?>
                <p class="empty-text">Belum ada data produk tersimpan.</p>
            <?php else: ?>
                <?php foreach ($products as $p): ?>
                    <div class="card">
                        <span class="badge"><?= e($p['category']); ?></span>
                        <!-- Syarat Output: htmlspecialchars($product["name"], ENT_QUOTES, "UTF-8") -->
                        <h3><?= e($p['name']); ?></h3>
                        <p class="price">Rp <?= number_format((float)$p['price'], 0, ',', '.'); ?></p>
                        <p class="stock">Stok: <strong><?= e((string)$p['stock']); ?></strong></p>
                        
                        <div class="card-actions">
                            <!-- UPDATE berdasarkan ID -->
                            <a href="form.php?id=<?= e((string)$p['id']); ?>" class="btn secondary">Edit</a>
                            
                            <!-- FITUR WAJIB DELETE: POST + CSRF -->
                            <form method="POST" action="index.php" style="display:inline;" onsubmit="return confirm('Hapus produk ini?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>">
                                <input type="hidden" name="id" value="<?= e((string)$p['id']); ?>">
                                <button type="submit" class="btn danger">Hapus</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <footer class="footer">
            <p>Dibuat oleh <strong>Caca Nurul Dwi Julia</strong> &bull; Pemrograman Web</p>
        </footer>
    </div>
</body>
</html>