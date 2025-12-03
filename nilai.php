<?php
session_start();
include 'db.php';

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

if (
    !isset($_SESSION['alternatif']) ||
    !isset($_SESSION['kriteria'])
) {
    header("Location: alternatif.php");
    exit;
}

$alternatif = $_SESSION['alternatif'];
$kriteria   = $_SESSION['kriteria'];

/* -----------------------------------------
   SIMPAN NILAI KE DATABASE
----------------------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nilai'])) {

    // Hapus nilai lama
    mysqli_query($conn, "DELETE FROM nilai");
    mysqli_query($conn, "ALTER TABLE nilai AUTO_INCREMENT = 1");

    // ambil id alternatif urut dari DB
    $qAlt = mysqli_query($conn, "SELECT id FROM alternatif ORDER BY id ASC");
    $altIDs = [];
    while ($row = mysqli_fetch_assoc($qAlt)) {
        $altIDs[] = $row['id'];
    }

    // ambil id kriteria urut dari DB
    $qKrit = mysqli_query($conn, "SELECT id FROM kriteria ORDER BY id ASC");
    $kritIDs = [];
    while ($row = mysqli_fetch_assoc($qKrit)) {
        $kritIDs[] = $row['id'];
    }

    // simpan matrix nilai ke database
    foreach ($_POST['nilai'] as $i => $row) {
        foreach ($row as $j => $val) {
            $id_alt = $altIDs[$i];
            $id_k   = $kritIDs[$j];
            mysqli_query($conn, "
                INSERT INTO nilai(id_alternatif, id_kriteria, nilai)
                VALUES('$id_alt', '$id_k', '".floatval($val)."')
            ");
        }
    }

    // lanjut otomatis ke proses TOPSIS
    header("Location: proses_topsis.php");
    exit;
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Nilai Kriteria</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

</head>
<body>

<div class="site-overlay">

<!-- NAVBAR PUTIH -->
<nav class="navbar navbar-expand-lg">
  <div class="container container-narrow">
    <a class="navbar-brand" href="index.php">
      <i class="fa-solid fa-chart-simple icon"></i>SPK TOPSIS
    </a>
  </div>
</nav>

<div class="container container-narrow" style="margin-top:110px">

  <div class="card-clean">

    <!-- STEPPER -->
    <div class="text-center mb-3 d-flex justify-content-center gap-4">
      <div class="text-center">
        <span class="step done">1</span>
        <div>Info Dasar</div>
      </div>
      <div class="text-center">
        <span class="step done">2</span>
        <div>Alternatif</div>
      </div>
      <div class="text-center">
        <span class="step done">3</span>
        <div>Kriteria</div>
      </div>
      <div class="text-center">
        <span class="step active">4</span>
        <div>Nilai</div>
      </div>
    </div>

    <h3 class="fw-bold"><i class="fa-solid fa-table-list icon"></i>Nilai Kriteria</h3>
    <p class="text-muted">Isi nilai setiap alternatif pada setiap kriteria</p>

    <form action="" method="POST">

      <div class="table-responsive box">
        <table class="table table-bordered align-middle">
          <thead class="table-light">
            <tr>
              <th>Alternatif</th>
              <?php foreach($kriteria as $k): ?>
              <th><?= htmlspecialchars($k) ?></th>
              <?php endforeach; ?>
            </tr>
          </thead>

          <tbody>
          <?php foreach($alternatif as $i => $a): ?>
            <tr>
              <td class="fw-semibold"><?= htmlspecialchars($a) ?></td>
              <?php foreach($kriteria as $j => $k): ?>
              <td>
                <input type="number" step="any" name="nilai[<?= $i ?>][<?= $j ?>]"
                       class="form-control" required>
              </td>
              <?php endforeach; ?>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <div class="d-flex justify-content-between mt-4">
        <a href="kriteria.php" class="btn btn-secondary">
          <i class="fa-solid fa-arrow-left me-2"></i>Kembali
        </a>

        <button class="btn btn-primary">
          <i class="fa-solid fa-calculator me-2"></i>Hitung Hasil
        </button>
      </div>

    </form>

  </div>
</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
