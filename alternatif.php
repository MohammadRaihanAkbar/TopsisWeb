<?php
session_start();
include 'db.php';

// simpan judul bila ada
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['judul']) && !isset($_POST['alternatif'])) {
    $_SESSION['judul'] = trim($_POST['judul']);
}

// proses ketika klik LANJUT
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['alternatif'])) 

    $alts = array_values(
        array_filter(
            array_map('trim', $_POST['alternatif']),
            fn($v) => $v !== ''
        )
    );

    if (empty($alts)) {
        $error = "Masukkan minimal 1 alternatif.";
    } else {

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['alternatif'])) {

    mysqli_query($conn, "DELETE FROM hasil");
    mysqli_query($conn, "DELETE FROM nilai");
    mysqli_query($conn, "DELETE FROM alternatif");
    mysqli_query($conn, "ALTER TABLE alternatif AUTO_INCREMENT = 1");

    foreach ($_POST['alternatif'] as $alt) {
        $nama = trim($alt);
        if ($nama !== "") {
            mysqli_query($conn, "INSERT INTO alternatif (nama) VALUES ('$nama')");
        }
    }

    $_SESSION['alternatif'] = $alts;
    header("Location: kriteria.php");
    exit;
}

}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Alternatif - TOPSIS</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>
<div class="site-overlay">

<nav class="navbar navbar-expand-lg">
  <div class="container container-narrow">
    <a class="navbar-brand" href="index.php">
      <i class="fa-solid fa-chart-simple icon"></i>SPK TOPSIS
    </a>
  </div>
</nav>

<div class="container container-narrow" style="margin-top:110px">
  <div class="card-clean">

    <div class="text-center mb-3">
      <span class="step active">2</span>
      <span class="ms-3">Alternatif</span>
    </div>

    <h3 class="fw-bold">
      <i class="fa-solid fa-layer-group icon"></i>
      Alternatif
    </h3>
    <p class="text-muted">Masukkan alternatif yang akan dianalisis.</p>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form action="alternatif.php" method="POST" id="formAlternatif">
      <div id="containerAlternatif">
        <div class="mb-3">
          <label class="form-label">Alternatif 1</label>
          <input type="text" name="alternatif[]" class="form-control" placeholder="Contoh: Alternatif A" required>
        </div>
      </div>

      <button type="button" class="btn btn-outline-secondary btn-sm mb-3" onclick="tambahAlternatif()">
        <i class="fa-solid fa-plus me-1"></i>Tambah Alternatif
      </button>

      <div class="d-flex justify-content-between mt-3">
        <a href="mulai.php" class="btn btn-secondary">
          <i class="fa-solid fa-arrow-left me-2"></i>Kembali
        </a>
        <button type="submit" class="btn btn-primary">
          <i class="fa-solid fa-arrow-right me-2"></i>Lanjut
        </button>
      </div>
    </form>

  </div>
</div>

</div>

<script>
let countAlt = 1;
function tambahAlternatif(){
  countAlt++;
  const div = document.createElement('div');
  div.className = 'mb-3';
  div.innerHTML = `
    <label class="form-label">Alternatif ${countAlt}</label>
    <input type="text" name="alternatif[]" class="form-control" placeholder="Contoh: Alternatif ${countAlt}" required>
  `;
  document.getElementById('containerAlternatif').appendChild(div);
}
</script>

</body>
</html>
