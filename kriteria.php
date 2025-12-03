<?php
session_start();
include 'db.php';

$error = "";

// Pastikan alternatif sudah ada
if (!isset($_SESSION['alternatif']) || count($_SESSION['alternatif']) == 0) {
  header("Location: alternatif.php");
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['kriteria'])) {
  $kriteria = array_values(array_filter(array_map('trim', (array)$_POST['kriteria']), fn($v)=>$v!==''));
  $bobot_raw = (array)($_POST['bobot'] ?? []);
  $tipe_raw  = (array)($_POST['tipe'] ?? []);

  // bobot float & default
  $bobot = [];
  foreach ($bobot_raw as $x) $bobot[] = (float)$x;

  // tipe: pastiin ada benefit/cost sesuai index
  $tipe = [];
  foreach ($kriteria as $i => $_) {
    $t = $tipe_raw[$i] ?? 'benefit';
    $tipe[$i] = ($t === 'cost') ? 'cost' : 'benefit';
  }

  if (count($kriteria) < 1) {
    $error = "Masukkan minimal 1 kriteria.";
  } else {
    // validasi bobot > 0
    foreach ($kriteria as $i => $nama) {
      $b = $bobot[$i] ?? 0;
      if ($b <= 0) {
        $error = "Bobot untuk Kriteria ".($i+1)." harus lebih dari 0.";
        break;
      }
    }
  }

  if ($error === "") {
    $_SESSION['kriteria'] = $kriteria;
    $_SESSION['bobot'] = $bobot;
    $_SESSION['tipe'] = $tipe;

    mysqli_query($conn, "DELETE FROM kriteria");
    mysqli_query($conn, "ALTER TABLE kriteria AUTO_INCREMENT = 1");

    // prepared insert
    $stmt = mysqli_prepare($conn, "INSERT INTO kriteria (nama, bobot, sifat) VALUES (?, ?, ?)");
    if (!$stmt) {
      $error = "Gagal menyiapkan query: " . mysqli_error($conn);
    } else {
      foreach ($kriteria as $i => $nama) {
        $b = $bobot[$i];
        $t = $tipe[$i];
        mysqli_stmt_bind_param($stmt, "sds", $nama, $b, $t); // s=string, d=double, s=string
        mysqli_stmt_execute($stmt);
      }
      mysqli_stmt_close($stmt);

      header("Location: nilai.php");
      exit;
    }
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
        <h2 class="fw-bold mb-1">Kriteria & Bobot</h2>
        <div class="text-muted">Masukkan kriteria penilaian, bobot, dan tipe (benefit/cost).</div>
      </div>

      <div class="stepper">
        <div class="stepper-item done">
          <span class="bubble"><i class="fa-solid fa-check"></i></span>
          <span class="label">Info</span>
        </div>
        <div class="stepper-line"></div>

        <div class="stepper-item done">
          <span class="bubble"><i class="fa-solid fa-check"></i></span>
          <span class="label">Alt</span>
        </div>
        <div class="stepper-line"></div>

        <div class="stepper-item active">
          <span class="bubble">3</span>
          <span class="label">Kriteria</span>
        </div>
        <div class="stepper-line"></div>

        <div class="stepper-item">
          <span class="bubble">4</span>
          <span class="label">Nilai</span>
        </div>
      </div>
    </div>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger mb-3"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="row g-4">
      <div class="col-lg-8">
        <div class="cardx">
          <form method="POST" id="formKriteria">
            <div id="containerKriteria">
              <!-- item 1 -->
              <div class="kri-item mb-3" data-index="0">
                <div class="d-flex align-items-center justify-content-between mb-2">
                  <div class="fw-bold">Kriteria 1</div>
                  <button type="button" class="btn btn-outline-danger btn-sm kri-remove d-none" title="Hapus">
                    <i class="fa-solid fa-trash"></i>
                  </button>
                </div>

                <div class="mb-2">
                  <label class="form-label fw-semibold">Nama Kriteria</label>
                  <input type="text" name="kriteria[]" class="form-control form-control-lg" placeholder="Contoh: Harga" required>
                </div>

                <div class="mb-2">
                  <label class="form-label fw-semibold">Bobot</label>
                  <input type="number" step="any" min="0.0001" name="bobot[]" class="form-control form-control-lg" value="1" required>
                  <div class="form-text">Boleh desimal. Minimal &gt; 0.</div>
                </div>

                <div>
                  <label class="form-label fw-semibold">Tipe Kriteria</label>
                  <div class="d-flex gap-3 flex-wrap">
                    <label class="form-check d-flex align-items-center gap-2">
                      <input class="form-check-input" type="radio" name="tipe[0]" value="benefit" checked>
                      <span class="form-check-label">Benefit</span>
                    </label>
                    <label class="form-check d-flex align-items-center gap-2">
                      <input class="form-check-input" type="radio" name="tipe[0]" value="cost">
                      <span class="form-check-label">Cost</span>
                    </label>
                  </div>
                </div>
              </div>
            </div>

            <button type="button" class="btn btn-outline-primary btn-sm" id="btnTambahKriteria">
              <i class="fa-solid fa-plus me-1"></i>Tambah Kriteria
            </button>

            <div class="d-flex justify-content-between mt-4">
              <a href="alternatif.php" class="btn btn-outline-secondary">
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
            <i class="fa-solid fa-scale-balanced me-2" style="color: var(--p1);"></i>Tips bobot
          </div>
          <ul class="text-muted mb-0" style="padding-left: 18px; line-height: 1.7;">
            <li>Bobot lebih besar = lebih penting.</li>
            <li>Benefit: semakin besar semakin baik.</li>
            <li>Cost: semakin kecil semakin baik (contoh: harga, jarak).</li>
          </ul>
        </div>
      </div>
    </div>

  </main>
</div>

<script>
let kriCount = 1;

function refreshKriteriaLabels(){
  const items = document.querySelectorAll('#containerKriteria .kri-item');
  items.forEach((item, idx) => {
    item.dataset.index = idx;
    item.querySelector('.fw-bold').textContent = `Kriteria ${idx + 1}`;

    // update name radio supaya tidak bentrok
    const radios = item.querySelectorAll('input[type="radio"]');
    radios.forEach(r => {
      r.name = `tipe[${idx}]`;
    });

    // tombol hapus: tampil kalau item > 1
    const btnRemove = item.querySelector('.kri-remove');
    btnRemove.classList.toggle('d-none', items.length === 1);
  });
}

document.getElementById('btnTambahKriteria').addEventListener('click', () => {
  const idx = kriCount;

  const wrap = document.createElement('div');
  wrap.className = 'kri-item mb-3';
  wrap.dataset.index = idx;

  wrap.innerHTML = `
    <div class="d-flex align-items-center justify-content-between mb-2">
      <div class="fw-bold">Kriteria ${idx + 1}</div>
      <button type="button" class="btn btn-outline-danger btn-sm kri-remove" title="Hapus">
        <i class="fa-solid fa-trash"></i>
      </button>
    </div>

    <div class="mb-2">
      <label class="form-label fw-semibold">Nama Kriteria</label>
      <input type="text" name="kriteria[]" class="form-control form-control-lg" placeholder="Contoh: Kualitas" required>
    </div>

    <div class="mb-2">
      <label class="form-label fw-semibold">Bobot</label>
      <input type="number" step="any" min="0.0001" name="bobot[]" class="form-control form-control-lg" value="1" required>
    </div>

    <div>
      <label class="form-label fw-semibold">Tipe Kriteria</label>
      <div class="d-flex gap-3 flex-wrap">
        <label class="form-check d-flex align-items-center gap-2">
          <input class="form-check-input" type="radio" name="tipe[${idx}]" value="benefit" checked>
          <span class="form-check-label">Benefit</span>
        </label>
        <label class="form-check d-flex align-items-center gap-2">
          <input class="form-check-input" type="radio" name="tipe[${idx}]" value="cost">
          <span class="form-check-label">Cost</span>
        </label>
      </div>
    </div>
  `;

  wrap.querySelector('.kri-remove').addEventListener('click', () => {
    wrap.remove();
    refreshKriteriaLabels();
  });

  document.getElementById('containerKriteria').appendChild(wrap);
  kriCount++;
  refreshKriteriaLabels();
});

// aktifkan tombol hapus kalau > 1
refreshKriteriaLabels();
</script>

</body>
</html>
