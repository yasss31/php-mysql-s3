<?php 
include 'config.php'; 

// --- CREATE ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file_upload'])) {
    $file = $_FILES['file_upload'];
    $originalName = basename($file['name']);
    $s3Key = time() . '_' . $originalName;

    try {
        // Upload ke S3
        $result = $s3->putObject([
            'Bucket' => $bucket,
            'Key'    => $s3Key,
            'SourceFile' => $file['tmp_name']
        ]);
        $fileUrl = $result['ObjectURL'];

        // Simpan ke DB
        $stmt = $conn->prepare("INSERT INTO assets (file_name, s3_key, file_url) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $originalName, $s3Key, $fileUrl);
        $stmt->execute();

        $msg = "<div class='alert alert-success'>Sukses! Tersimpan di S3 & Database.</div>";
    } catch (Exception $e) {
        $msg = "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
    }
}

// --- DELETE ---
if (isset($_GET['delete']) && isset($_GET['id'])) {
    try {
        // Hapus dari S3
        $s3->deleteObject(['Bucket' => $bucket, 'Key' => $_GET['delete']]);

        // Hapus dari DB
        $stmt = $conn->prepare("DELETE FROM assets WHERE id = ?");
        $stmt->bind_param("i", $_GET['id']);
        $stmt->execute();

        header("Location: index.php");
        exit();
    } catch (Exception $e) {
        $msg = "<div class='alert alert-danger'>Gagal hapus: " . $e->getMessage() . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SMK Cloud CRUD Auto-Setup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <h2 class="text-center mb-4">🚀 Asset Manager (Auto-DB & IAM Role)</h2>
    
    <?php if(isset($msg)) echo $msg; ?>

    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <form action="" method="POST" enctype="multipart/form-data" class="input-group">
                <input type="file" name="file_upload" class="form-control" required>
                <button type="submit" class="btn btn-primary">Upload ke S3</button>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <table class="table table-hover mb-0">
            <thead class="table-dark">
                <tr>
                    <th>File</th>
                    <th>Nama Asli</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // --- READ ---
                $res = $conn->query("SELECT * FROM assets ORDER BY id DESC");
                while ($row = $res->fetch_assoc()): 
                    $ext = strtolower(pathinfo($row['s3_key'], PATHINFO_EXTENSION));
                    $isImg = in_array($ext, ['jpg','jpeg','png','gif','webp']);
                ?>
                <tr>
                    <td>
                        <?php if($isImg): ?>
                            <img src="<?= $row['file_url'] ?>" style="width:50px; height:50px; object-fit:cover;" class="rounded">
                        <?php else: ?>
                            <span class="badge bg-secondary">FILE</span>
                        <?php endif; ?>
                    </td>
                    <td class="align-middle"><?= htmlspecialchars($row['file_name']) ?></td>
                    <td class="align-middle">
                        <a href="<?= $row['file_url'] ?>" target="_blank" class="btn btn-sm btn-info text-white">Buka</a>
                        <a href="index.php?delete=<?= $row['s3_key'] ?>&id=<?= $row['id'] ?>" 
                           class="btn btn-sm btn-danger" onclick="return confirm('Hapus permanent?')">Hapus</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>