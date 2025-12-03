<?php
session_start();
include 'db.php';

if (!$conn) {
  die("Koneksi database gagal: " . mysqli_connect_error());
}

if (!isset($_SESSION['alternatif']) || !isset($_SESSION['kriteria'])) {
  header("Location: alternatif.php");
  exit;
}

$alternatif = $_SESSION['alternatif'];
$kriteria   = $_SESSION['kriteria'];

$error = "";

/* -----------------------------------------
   SIMPAN NILAI KE DATABASE (aman + rapi)
----------------------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nilai'])) {

  // ambil id alternatif urut dari DB
  $qAlt = mysqli_query($conn, "SELECT id FROM alternatif ORDER BY id ASC");
  $altIDs = [];
  while ($row = mysqli_fetch_assoc($qAlt)) $altIDs[] = (int)$row['id'];

  // ambil id kriteria urut dari DB
  $qKrit = mysqli_query($conn, "SELECT id FROM kriteria ORDER BY id ASC");
  $kritIDs = [];
  while ($row = mysqli_fetch_assoc($qKrit)) $kritIDs[] = (int)$row['id'];

  // validasi ukuran matrix
  if (count($altIDs) !== count($alternatif) || count($kritIDs) !== count($kriteria)) {
    $error = "Data alternatif/kriteria tidak sinkron. Ulangi dari langkah sebelumnya.";
  } else {
    // validasi nilai: harus numeric
    foreach ((array)$_POST['nilai'] as $i => $row) {
      foreach ((array)$row as $j => $val) {
        if ($val === '' || !is_numeric($val)) {
          $error = "Semua nilai harus diisi angka.";
          break 2;
        }
      }
    }
  }

  if ($error === "") {
    mysqli_query($conn, "DELETE FROM nilai");
    mysqli_query($conn, "ALTER TABLE nilai AUTO_INCREMENT = 1");

    $stmt = mysqli_prepare($conn, "INSERT INTO nilai (id_alternatif, id_kriteria, nilai) VALUES (?, ?, ?)");
    if (!$stmt) {
      $error = "Gagal menyiapkan query: " . mysqli_error($conn);
    } else {
      // simpan matrix
      foreach ($_POST['nilai'] as $i => $row) {
        foreach ($row as $j => $val) {
          $id_alt = $altIDs[$i];
          $id_k   = $kritIDs[$j];
          $nilai  = (float)$val;

          mysqli_stmt_bind_param($stmt, "iid", $id_alt, $id_k, $nilai);
          mysqli_stmt_execute($stmt);
        }
      }
      mysqli_stmt_close($stmt);

      header("Location: proses_topsis.php");
      exit;
    }
  }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Nilai Kriteria - TOPSIS</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>
<div class="page">

  <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
    <div class="container container-narrow">
      <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="index.php">
        <span class="logo-pill"><i class="fa-solid fa-chart-simple"></i></span>
        <span>SPK TOPSIS</span>
      </a>
    </div>
  </nav>

  <main class="container container-narrow" style="padding: 44px 0 70px;">
    <div class="mb-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
      <div>
        <h2 class="fw-bold mb-1">Nilai Kriteria</h2>
        <div class="text-muted">Isi nilai setiap alternatif untuk tiap kriteria.</div>
      </div>

      <div class="stepper">
        <div class="stepper-item done">
          <span class="bubble"><i class="fa-solid fa-check"></i></span><span class="label">Info</span>
        </div>
        <div class="stepper-line"></div>
        <div class="stepper-item done">
          <span class="bubble"><i class="fa-solid fa-check"></i></span><span class="label">Alt</span>
        </div>
        <div class="stepper-line"></div>
        <div class="stepper-item done">
          <span class="bubble"><i class="fa-solid fa-check"></i></span><span class="label">Kriteria</span>
        </div>
        <div class="stepper-line"></div>
        <div class="stepper-item active">
          <span class="bubble">4</span><span class="label">Nilai</span>
        </div>
      </div>
    </div>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger mb-3"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form action="" method="POST">
      <div class="cardx">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
          <div class="text-muted">
            <i class="fa-solid fa-circle-info me-2" style="color: var(--p1);"></i>
            Tip: kamu bisa isi cepat pakai tombol <b>Tab</b> di keyboard.
          </div>
          <div class="small text-muted">
            Total input: <b><?= count($alternatif) ?></b> alternatif × <b><?= count($kriteria) ?></b> kriteria
          </div>
        </div>

        <div class="table-wrap">
          <table class="table table-borderless table-matrix align-middle mb-0">
            <thead>
              <tr>
                <th class="th-sticky-left">Alternatif</th>
                <?php foreach($kriteria as $k): ?>
                  <th class="th-sticky"><?= htmlspecialchars($k) ?></th>
                <?php endforeach; ?>
              </tr>
            </thead>

            <tbody>
              <?php foreach($alternatif as $i => $a): ?>
                <tr>
                  <td class="td-sticky-left fw-semibold"><?= htmlspecialchars($a) ?></td>
                  <?php foreach($kriteria as $j => $k): ?>
                  <td>
                    <input
                      type="number"
                      step="any"
                      name="nilai[<?= $i ?>][<?= $j ?>]"
                      class="form-control form-control-sm matrix-input"
                      required
                    >
                  </td>
                  <?php endforeach; ?>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <div class="d-flex justify-content-between mt-4">
          <a href="kriteria.php" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-2"></i>Kembali
          </a>

          <button class="btn btn-primary px-4">
            <i class="fa-solid fa-calculator me-2"></i>Hitung Hasil
          </button>
        </div>
      </div>
    </form>
  </main>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
