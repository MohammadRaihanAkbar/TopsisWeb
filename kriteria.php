<?php
session_start();
include 'db.php';

if (!isset($_SESSION['alternatif']) || count($_SESSION['alternatif']) == 0) {
    header("Location: alternatif.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['kriteria'])) {
    $kriteria = array_values(array_filter(array_map('trim', $_POST['kriteria']), fn($v)=>$v!==''));    
    $bobot = array_map('floatval', $_POST['bobot']);
    $tipe  = $_POST['tipe'];

    if (count($kriteria) == 0) {
        $error = "Masukkan minimal 1 kriteria.";
    } else {

        $_SESSION['kriteria'] = $kriteria;
        $_SESSION['bobot'] = $bobot;
        $_SESSION['tipe'] = $tipe;

        mysqli_query($conn, "DELETE FROM kriteria");
        mysqli_query($conn, "ALTER TABLE kriteria AUTO_INCREMENT = 1");

        foreach ($kriteria as $i => $nama) {
            $b = $bobot[$i];
            $t = $tipe[$i];

            mysqli_query($conn, "
                INSERT INTO kriteria(nama, bobot, sifat)
                VALUES(
                    '".mysqli_real_escape_string($conn, $nama)."',
                    '$b',
                    '$t'
                )
            ");
        }

        header("Location: nilai.php");
        exit;
    }
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Kriteria - TOPSIS</title>
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
      <span class="step active">3</span>
      <span class="ms-3">Kriteria</span>
    </div>

    <h3 class="fw-bold">
      <i class="fa-solid fa-sliders icon"></i>
      Kriteria dan Bobot
    </h3>
    <p class="text-muted">Masukkan kriteria penilaian.</p>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">

      <div id="containerKriteria">
        <div class="border p-3 rounded mb-3 bg-white">
          <label class="form-label">Kriteria 1</label>
          <input type="text" name="kriteria[]" class="form-control mb-2" placeholder="Contoh: Harga" required>

          <label class="form-label">Bobot</label>
          <input type="number" step="any" name="bobot[]" class="form-control mb-2" value="1" required>

          <label class="form-label">Tipe Kriteria</label>
          <div class="d-flex gap-3 mb-2">
            <div class="form-check">
              <input class="form-check-input" type="radio" name="tipe[0]" value="benefit" checked>
              <label class="form-check-label">Benefit</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="tipe[0]" value="cost">
              <label class="form-check-label">Cost</label>
            </div>
          </div>
        </div>
      </div>

      <button type="button" class="btn btn-outline-primary btn-sm mb-3" onclick="tambahKriteria()">
        <i class="fa-solid fa-plus me-1"></i>Tambah Kriteria
      </button>

      <div class="d-flex justify-content-between">
        <a href="alternatif.php" class="btn btn-secondary">
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
let kriCount = 1;
function tambahKriteria(){
  const i = kriCount;
  const div = document.createElement('div');
  div.className = 'border p-3 rounded mb-3 bg-white';
  div.innerHTML = `
    <label class="form-label">Kriteria ${i+1}</label>
    <input type="text" name="kriteria[]" class="form-control mb-2" required>
    <label class="form-label">Bobot</label>
    <input type="number" step="any" name="bobot[]" class="form-control mb-2" value="1" required>
    <label class="form-label">Tipe Kriteria</label>
    <div class="d-flex gap-3 mb-2">
      <div class="form-check"><input class="form-check-input" type="radio" name="tipe[${i}]" value="benefit" checked><label class="form-check-label">Benefit</label></div>
      <div class="form-check"><input class="form-check-input" type="radio" name="tipe[${i}]" value="cost"><label class="form-check-label">Cost</label></div>
    </div>
  `;
  document.getElementById('containerKriteria').appendChild(div);
  kriCount++;
}
</script>

</body>
</html>
