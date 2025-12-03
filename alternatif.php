<?php
session_start();
include 'db.php';

$error = "";

/**
 * 1) Simpan judul kalau datang dari mulai.php
 * (mulai.php POST: judul)
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['judul']) && !isset($_POST['alternatif'])) {
    $_SESSION['judul'] = trim($_POST['judul']);
}

/**
 * 2) Proses submit alternatif
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['alternatif'])) {
    // rapihin input alternatif: trim + buang kosong
    $alts = array_values(array_filter(array_map('trim', (array)$_POST['alternatif']), function($v){
        return $v !== '';
    }));

    if (count($alts) < 1) {
        $error = "Masukkan minimal 1 alternatif.";
    } else {
        // bersihin tabel terkait (sesuai skema kamu)
        mysqli_query($conn, "DELETE FROM hasil");
        mysqli_query($conn, "DELETE FROM nilai");
        mysqli_query($conn, "DELETE FROM alternatif");
        mysqli_query($conn, "ALTER TABLE alternatif AUTO_INCREMENT = 1");

        // insert pakai prepared statement biar aman
        $stmt = mysqli_prepare($conn, "INSERT INTO alternatif (nama) VALUES (?)");
        if (!$stmt) {
            $error = "Gagal menyiapkan query: " . mysqli_error($conn);
        } else {
            foreach ($alts as $nama) {
                mysqli_stmt_bind_param($stmt, "s", $nama);
                mysqli_stmt_execute($stmt);
            }
            mysqli_stmt_close($stmt);

            $_SESSION['alternatif'] = $alts;
            header("Location: kriteria.php");
            exit;
        }
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
    <div class="mb-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
      <div>
        <h2 class="fw-bold mb-1">Alternatif</h2>
        <div class="text-muted">Masukkan alternatif yang akan dianalisis.</div>
      </div>

      <!-- Stepper ringkas -->
      <div class="stepper">
        <div class="stepper-item done">
          <span class="bubble"><i class="fa-solid fa-check"></i></span>
          <span class="label">Info</span>
        </div>
        <div class="stepper-line"></div>
        <div class="stepper-item active">
          <span class="bubble">2</span>
          <span class="label">Alternatif</span>
        </div>
        <div class="stepper-line"></div>
        <div class="stepper-item">
          <span class="bubble">3</span>
          <span class="label">Kriteria</span>
        </div>
        <div class="stepper-line"></div>
        <div class="stepper-item">
          <span class="bubble">4</span>
          <span class="label">Hasil</span>
        </div>
      </div>
    </div>

    <div class="row g-4">
      <div class="col-lg-8">
        <div class="cardx">
          <?php if (!empty($error)): ?>
            <div class="alert alert-danger mb-3"><?= htmlspecialchars($error) ?></div>
          <?php endif; ?>

          <form action="alternatif.php" method="POST" id="formAlternatif">
            <div id="containerAlternatif">
              <div class="mb-3">
                <label class="form-label fw-semibold">Alternatif 1</label>
                <input type="text" name="alternatif[]" class="form-control form-control-lg" placeholder="Contoh: Alternatif A" required>
              </div>
            </div>

            <button type="button" class="btn btn-outline-secondary btn-sm mb-3" id="btnTambahAlt">
              <i class="fa-solid fa-plus me-1"></i>Tambah Alternatif
            </button>

            <div class="d-flex justify-content-between mt-3">
              <a href="mulai.php" class="btn btn-outline-secondary">
                <i class="fa-solid fa-arrow-left me-2"></i>Kembali
              </a>
              <button type="submit" class="btn btn-primary px-4">
                Lanjut <i class="fa-solid fa-arrow-right ms-2"></i>
              </button>
            </div>
          </form>
        </div>
      </div>

      <div class="col-lg-4">
        <div class="cardx">
          <div class="fw-bold mb-2">
            <i class="fa-solid fa-lightbulb me-2" style="color: var(--p1);"></i>Tips cepat
          </div>
          <ul class="text-muted mb-0" style="padding-left: 18px; line-height: 1.7;">
            <li>Masukkan minimal 2 alternatif supaya ranking lebih terasa.</li>
            <li>Gunakan nama yang jelas: “Laptop A”, “Vendor B”, “Kandidat C”.</li>
            <li>Nanti di step berikutnya kamu isi kriteria & bobot.</li>
          </ul>
        </div>
      </div>
    </div>
  </main>

</div>

<script>
let countAlt = 1;

document.getElementById('btnTambahAlt').addEventListener('click', () => {
  countAlt++;
  const wrap = document.createElement('div');
  wrap.className = 'mb-3';
  wrap.innerHTML = `
    <label class="form-label fw-semibold">Alternatif ${countAlt}</label>
    <div class="d-flex gap-2">
      <input type="text" name="alternatif[]" class="form-control form-control-lg" placeholder="Contoh: Alternatif ${countAlt}" required>
      <button type="button" class="btn btn-outline-danger remove-alt" title="Hapus">
        <i class="fa-solid fa-trash"></i>
      </button>
    </div>
  `;
  document.getElementById('containerAlternatif').appendChild(wrap);

  // tombol hapus
  wrap.querySelector('.remove-alt').addEventListener('click', () => {
    wrap.remove();
  });
});
</script>

</body>
</html>
