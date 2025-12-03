<?php
session_start();
include "db.php";

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function f6($n){ return number_format((float)$n, 6, '.', ''); }
function fraw($n){
  $s = (string)((float)$n);
  return rtrim(rtrim($s,'0'),'.');
}

$ranking = $_SESSION['ranking'] ?? [];
$steps   = $_SESSION['steps'] ?? [];

/* fallback: kalau session ranking ilang, ambil dari DB hasil */
if (count($ranking) === 0) {
  $q = mysqli_query($conn, "
    SELECT a.id as id_alternatif, a.nama as alternatif, h.skor, h.ranking
    FROM hasil h
    JOIN alternatif a ON a.id = h.id_alternatif
    ORDER BY h.ranking ASC
  ");
  while ($r = mysqli_fetch_assoc($q)) {
    $ranking[] = [
      'id_alternatif' => (int)$r['id_alternatif'],
      'alternatif'    => $r['alternatif'],
      'skor'          => (float)$r['skor'],
      'ranking'       => (int)$r['ranking'],
    ];
  }
}

/* ✅ FIX: kalau ranking key hilang / nol semua, bikin ulang 1..n */
$needFix = false;
foreach ($ranking as $r) {
  if (!isset($r['ranking'])) { $needFix = true; break; }
}
if ($needFix) {
  usort($ranking, fn($a,$b) => ($b['skor'] ?? 0) <=> ($a['skor'] ?? 0));
  foreach ($ranking as $i => &$r) $r['ranking'] = $i + 1;
  unset($r);
}

/* kalau steps kosong, paksa hitung ulang */
if (count($ranking) === 0 || empty($steps)) {
  header("Location: proses.php");
  exit;
}

/* list kriteria & alternatif untuk label tabel */
$qKrit = mysqli_query($conn, "SELECT id, nama, sifat, bobot FROM kriteria ORDER BY id ASC");
$qAlt  = mysqli_query($conn, "SELECT id, nama FROM alternatif ORDER BY id ASC");

$listKrit = [];
while($r = mysqli_fetch_assoc($qKrit)) $listKrit[(int)$r['id']] = $r;

$listAlt = [];
while($r = mysqli_fetch_assoc($qAlt)) $listAlt[(int)$r['id']] = $r['nama'];

$best = $ranking[0];

$X = $steps['matrix_keputusan'] ?? [];
$R = $steps['normalisasi'] ?? [];
$Y = $steps['terbobot'] ?? [];
$Aplus = $steps['ideal_plus'] ?? [];
$Amin  = $steps['ideal_minus'] ?? [];
$normInfo = $steps['norm_info'] ?? [];
$sqPlus = $steps['sq_plus'] ?? [];
$sqMin  = $steps['sq_min'] ?? [];
$DplusAll = $steps['dplus'] ?? [];
$DminAll  = $steps['dmin'] ?? [];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Hasil TOPSIS</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="page">

  <!-- NAVBAR: samain persis sama beranda -->
  <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
    <div class="container container-narrow">
      <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="index.php">
        <span class="logo-pill"><i class="fa-solid fa-chart-simple"></i></span>
        <span>SPK TOPSIS</span>
      </a>

      <div class="ms-auto d-flex gap-2">
        <a href="export_pdf.php" class="btn btn-danger btn-sm px-3">
          <i class="fa-solid fa-file-pdf me-2"></i>Export PDF
        </a>
        <a href="export_csv.php" class="btn btn-success btn-sm px-3">
          <i class="fa-solid fa-file-csv me-2"></i>Export CSV
        </a>
      </div>
    </div>
  </nav>

  <main class="container container-narrow" style="padding: 42px 0 80px;">
    <div class="mb-3">
      <h2 class="fw-bold mb-1"><i class="fa-solid fa-ranking-star me-2 text-primary"></i>Hasil TOPSIS</h2>
      <div class="text-muted">Ranking + detail perhitungan + manual substitusi angka.</div>
    </div>

    <div class="cardx mb-4 text-center">
      <div class="text-muted mb-1">🏆 Alternatif Terbaik</div>
      <div class="best"><?= h($best['alternatif']) ?></div>
      <div class="fw-bold text-muted">Skor: <?= f6($best['skor']) ?></div>
    </div>

    <div class="cardx mb-4">
      <h5 class="fw-bold mb-3">Peringkat Alternatif</h5>
      <div class="table-wrap">
        <table class="table table-result align-middle mb-0">
          <thead>
            <tr>
              <th style="width:120px;">Peringkat</th>
              <th>Alternatif</th>
              <th style="width:180px;">Skor</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($ranking as $r): ?>
              <tr>
                <td class="fw-bold"><?= (int)($r['ranking'] ?? 0) ?></td>
                <td><?= h($r['alternatif'] ?? '') ?></td>
                <td><?= f6($r['skor'] ?? 0) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="cardx">
      <h5 class="fw-bold mb-3">Detail Perhitungan</h5>

      <div class="accordion" id="accTopsis">
        <?= accItem("1) Matriks Keputusan (X)", "c1", true,
          "<div class='text-muted mb-2'>x<sub>ij</sub> = nilai alternatif i terhadap kriteria j</div>" .
          renderMatrixTable($X, $listAlt, $listKrit, false)
        ); ?>

        <?= accItem("2) Normalisasi (R)", "c2", false,
          "<div class='text-muted mb-2'>r<sub>ij</sub> = x<sub>ij</sub> / √Σ(x<sub>ij</sub>²)</div>" .
          renderMatrixTable($R, $listAlt, $listKrit, true)
        ); ?>

        <?= accItem("3) Normalisasi Terbobot (Y)", "c3", false,
          "<div class='text-muted mb-2'>y<sub>ij</sub> = w<sub>j</sub> × r<sub>ij</sub></div>" .
          renderMatrixTable($Y, $listAlt, $listKrit, true)
        ); ?>

        <?= accItem("4) Solusi Ideal (A⁺ & A⁻)", "c4", false,
          renderIdealTable($listKrit, $Aplus, $Amin)
        ); ?>

        <?= accItem("5) Jarak ke A⁺ & A⁻ (D⁺ & D⁻)", "c5", false,
          renderDTable($listAlt, $DplusAll, $DminAll)
        ); ?>

        <?= accItem("6) Nilai Preferensi (V) & Ranking", "c6", false,
          "<div class='text-muted mb-2'>V = D⁻ / (D⁺ + D⁻)</div>" . renderRankMini($ranking)
        ); ?>

        <?= accItem("7) Manual Hitung (Substitusi Angka ke Rumus)", "c7", false,
          renderManualPicker($listAlt) . renderManualBlocks($listAlt, $listKrit, $X, $R, $Y, $Aplus, $Amin, $normInfo, $sqPlus, $sqMin, $DplusAll, $DminAll)
        ); ?>
      </div>

      <div class="d-flex justify-content-between mt-4">
        <a href="nilai.php" class="btn btn-outline-secondary">
          <i class="fa-solid fa-arrow-left me-2"></i>Kembali
        </a>
        <a href="index.php" class="btn btn-primary px-4">
          Dashboard <i class="fa-solid fa-house ms-2"></i>
        </a>
      </div>
    </div>

  </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  function showManual(){
    const v = document.getElementById('pickAltManual').value;
    document.querySelectorAll('.manual-block').forEach(el => el.style.display='none');
    const t = document.getElementById('manual-'+v);
    if(t) t.style.display='block';
  }
  document.addEventListener('DOMContentLoaded', showManual);
</script>
</body>
</html>

<?php
/* ================= helpers ================= */

function accItem($title, $id, $open, $html){
  ob_start(); ?>
  <div class="accordion-item">
    <h2 class="accordion-header" id="h<?= h($id) ?>">
      <button class="accordion-button <?= $open ? '' : 'collapsed' ?>" type="button"
              data-bs-toggle="collapse" data-bs-target="#<?= h($id) ?>">
        <?= $title ?>
      </button>
    </h2>
    <div id="<?= h($id) ?>" class="accordion-collapse collapse <?= $open ? 'show' : '' ?>" data-bs-parent="#accTopsis">
      <div class="accordion-body"><?= $html ?></div>
    </div>
  </div>
  <?php return ob_get_clean();
}

function renderMatrixTable($mat, $listAlt, $listKrit, $round){
  ob_start(); ?>
  <div class="table-wrap">
    <table class="table table-result align-middle mb-0">
      <thead>
        <tr>
          <th>Kriteria \ Alternatif</th>
          <?php foreach($listAlt as $aid => $nm): ?>
            <th><?= h($nm) ?></th>
          <?php endforeach; ?>
        </tr>
      </thead>
      <tbody>
        <?php foreach($listKrit as $kid => $kr): ?>
          <tr>
            <td class="fw-semibold"><?= h($kr['nama']) ?></td>
            <?php foreach($listAlt as $aid => $_): 
              $v = (float)($mat[$kid][$aid] ?? 0);
            ?>
              <td><?= $round ? f6($v) : fraw($v) ?></td>
            <?php endforeach; ?>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php return ob_get_clean();
}

function renderIdealTable($listKrit, $Aplus, $Amin){
  ob_start(); ?>
  <div class="table-wrap">
    <table class="table table-result align-middle mb-0">
      <thead>
        <tr>
          <th></th>
          <?php foreach($listKrit as $kid => $kr): ?>
            <th><?= h($kr['nama']) ?> <span class="text-muted">(<?= h($kr['sifat']) ?>)</span></th>
          <?php endforeach; ?>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td class="fw-bold">A⁺</td>
          <?php foreach($listKrit as $kid => $_): ?>
            <td><?= f6($Aplus[$kid] ?? 0) ?></td>
          <?php endforeach; ?>
        </tr>
        <tr>
          <td class="fw-bold">A⁻</td>
          <?php foreach($listKrit as $kid => $_): ?>
            <td><?= f6($Amin[$kid] ?? 0) ?></td>
          <?php endforeach; ?>
        </tr>
      </tbody>
    </table>
  </div>
  <?php return ob_get_clean();
}

function renderDTable($listAlt, $Dplus, $Dmin){
  ob_start(); ?>
  <div class="table-wrap">
    <table class="table table-result align-middle mb-0">
      <thead><tr><th>Alternatif</th><th>D⁺</th><th>D⁻</th></tr></thead>
      <tbody>
        <?php foreach($listAlt as $aid => $nm): ?>
          <tr>
            <td class="fw-semibold"><?= h($nm) ?></td>
            <td><?= f6($Dplus[$aid] ?? 0) ?></td>
            <td><?= f6($Dmin[$aid] ?? 0) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php return ob_get_clean();
}

function renderRankMini($ranking){
  ob_start(); ?>
  <div class="table-wrap">
    <table class="table table-result align-middle mb-0">
      <thead><tr><th style="width:120px;">Rank</th><th>Alternatif</th><th style="width:180px;">Skor (V)</th></tr></thead>
      <tbody>
        <?php foreach($ranking as $r): ?>
          <tr>
            <td class="fw-bold"><?= (int)($r['ranking'] ?? 0) ?></td>
            <td><?= h($r['alternatif'] ?? '') ?></td>
            <td><?= f6($r['skor'] ?? 0) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php return ob_get_clean();
}

function renderManualPicker($listAlt){
  $firstAid = array_key_first($listAlt);
  ob_start(); ?>
  <div class="row g-3 align-items-end mb-3">
    <div class="col-md-6">
      <label class="form-label fw-semibold">Pilih Alternatif</label>
      <select class="form-select" id="pickAltManual" onchange="showManual()">
        <?php foreach($listAlt as $aid => $nm): ?>
          <option value="<?= (int)$aid ?>" <?= ($aid===$firstAid?'selected':'') ?>><?= h($nm) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-6">
      <div class="small text-muted">Format manual: rumus + substitusi angka.</div>
    </div>
  </div>
  <?php return ob_get_clean();
}

function renderManualBlocks($listAlt, $listKrit, $X, $R, $Y, $Aplus, $Amin, $normInfo, $sqPlus, $sqMin, $DplusAll, $DminAll){
  ob_start();

  foreach($listAlt as $aid => $nm){
    $Dplus = (float)($DplusAll[$aid] ?? 0);
    $Dmin  = (float)($DminAll[$aid] ?? 0);
    $V = ($Dplus + $Dmin)==0 ? 0 : ($Dmin/($Dplus+$Dmin));
    ?>
    <div class="manual-block" id="manual-<?= (int)$aid ?>" style="display:none;">
      <div class="calc-card">
        <div class="d-flex flex-wrap justify-content-between gap-2 align-items-center mb-2">
          <div>
            <div class="text-muted small">Alternatif</div>
            <div class="fw-bold" style="font-size:1.05rem;"><?= h($nm) ?></div>
          </div>
          <div class="d-flex gap-2 flex-wrap">
            <span class="pill">D⁺: <b><?= f6($Dplus) ?></b></span>
            <span class="pill">D⁻: <b><?= f6($Dmin) ?></b></span>
            <span class="pill">V: <b><?= f6($V) ?></b></span>
          </div>
        </div>

        <hr class="my-3">

        <div class="calc">
          <div class="calc-title">A) Normalisasi</div>
          <div class="calc-line">Rumus: <b>r<sub>ij</sub> = x<sub>ij</sub> / √( Σ x<sub>ij</sub>² )</b></div>

          <?php foreach($listKrit as $kid => $kr):
            $xij = (float)($X[$kid][$aid] ?? 0);
            $sumSq = (float)($normInfo[$kid]['sum_sq'] ?? 0);
            $sqrt  = (float)($normInfo[$kid]['sqrt'] ?? 1);
            $rij   = (float)($R[$kid][$aid] ?? 0);

            $parts = [];
            foreach($listAlt as $aid2 => $_nm){
              $val = (float)($X[$kid][$aid2] ?? 0);
              $parts[] = fraw($val) . "²";
            }
            $expr = implode(" + ", $parts);
          ?>
            <div class="calc-block">
              <div class="calc-sub"><?= h($kr['nama']) ?></div>
              <div class="calc-line">
                √(Σx²) = √( <?= h($expr) ?> ) = √(<?= f6($sumSq) ?>) = <b><?= f6($sqrt) ?></b>
              </div>
              <div class="calc-line">
                r = <?= fraw($xij) ?> / <?= f6($sqrt) ?> = <b><?= f6($rij) ?></b>
              </div>
            </div>
          <?php endforeach; ?>

          <div class="calc-title mt-4">B) Normalisasi Terbobot</div>
          <div class="calc-line">Rumus: <b>y<sub>ij</sub> = w<sub>j</sub> × r<sub>ij</sub></b></div>

          <?php foreach($listKrit as $kid => $kr):
            $w   = (float)($kr['bobot'] ?? 0);
            $rij = (float)($R[$kid][$aid] ?? 0);
            $yij = (float)($Y[$kid][$aid] ?? 0);
          ?>
            <div class="calc-block">
              <div class="calc-sub"><?= h($kr['nama']) ?></div>
              <div class="calc-line">
                y = <?= f6($w) ?> × <?= f6($rij) ?> = <b><?= f6($yij) ?></b>
              </div>
            </div>
          <?php endforeach; ?>

          <div class="calc-title mt-4">C) Solusi Ideal</div>
          <div class="calc-line"><b>A⁺</b>: benefit=max, cost=min. <b>A⁻</b>: kebalikannya.</div>

          <?php foreach($listKrit as $kid => $kr): ?>
            <div class="calc-block">
              <div class="calc-sub"><?= h($kr['nama']) ?> (<?= h($kr['sifat']) ?>)</div>
              <div class="calc-line">A⁺ = <b><?= f6($Aplus[$kid] ?? 0) ?></b></div>
              <div class="calc-line">A⁻ = <b><?= f6($Amin[$kid] ?? 0) ?></b></div>
            </div>
          <?php endforeach; ?>

          <div class="calc-title mt-4">D) Jarak (D⁺ & D⁻)</div>
          <div class="calc-line">Rumus: <b>D⁺ = √( Σ (y − A⁺)² )</b></div>
          <div class="calc-line">Rumus: <b>D⁻ = √( Σ (y − A⁻)² )</b></div>

          <?php
            $sumP = 0.0; $exprP = [];
            foreach($listKrit as $kid => $_kr){
              $y  = (float)($Y[$kid][$aid] ?? 0);
              $ap = (float)($Aplus[$kid] ?? 0);
              $sq = (float)($sqPlus[$aid][$kid] ?? pow($y-$ap,2));
              $sumP += $sq;
              $exprP[] = "(" . f6($y) . "−" . f6($ap) . ")²";
            }

            $sumM = 0.0; $exprM = [];
            foreach($listKrit as $kid => $_kr){
              $y  = (float)($Y[$kid][$aid] ?? 0);
              $am = (float)($Amin[$kid] ?? 0);
              $sq = (float)($sqMin[$aid][$kid] ?? pow($y-$am,2));
              $sumM += $sq;
              $exprM[] = "(" . f6($y) . "−" . f6($am) . ")²";
            }
          ?>
          <div class="calc-block">
            <div class="calc-sub">Hitung D⁺</div>
            <div class="calc-line">D⁺ = √( <?= h(implode(" + ", $exprP)) ?> )</div>
            <div class="calc-line">= √(<?= f6($sumP) ?>) = <b><?= f6($Dplus) ?></b></div>
          </div>

          <div class="calc-block">
            <div class="calc-sub">Hitung D⁻</div>
            <div class="calc-line">D⁻ = √( <?= h(implode(" + ", $exprM)) ?> )</div>
            <div class="calc-line">= √(<?= f6($sumM) ?>) = <b><?= f6($Dmin) ?></b></div>
          </div>

          <div class="calc-title mt-4">E) Nilai Preferensi</div>
          <div class="calc-line">Rumus: <b>V = D⁻ / (D⁺ + D⁻)</b></div>
          <div class="calc-block">
            <div class="calc-line">
              V = <?= f6($Dmin) ?> / (<?= f6($Dplus) ?> + <?= f6($Dmin) ?>) = <b><?= f6($V) ?></b>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php
  }

  return ob_get_clean();
}
?>
