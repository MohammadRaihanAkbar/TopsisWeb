<?php
session_start();
include "db.php";

if (!$conn) die("Koneksi DB gagal: " . mysqli_connect_error());

// reset session supaya hasil selalu fresh
unset($_SESSION['steps'], $_SESSION['ranking']);

$steps = [];

/* ambil data stabil */
$qKrit = mysqli_query($conn, "SELECT id, nama, bobot, sifat FROM kriteria ORDER BY id ASC");
$qAlt  = mysqli_query($conn, "SELECT id, nama FROM alternatif ORDER BY id ASC");

$krit = [];
while ($r = mysqli_fetch_assoc($qKrit)) {
  $kid = (int)$r['id'];
  $krit[$kid] = [
    'nama'  => $r['nama'],
    'bobot' => (float)$r['bobot'],
    'sifat' => ($r['sifat'] === 'cost') ? 'cost' : 'benefit',
  ];
}

$alt = [];
while ($r = mysqli_fetch_assoc($qAlt)) {
  $alt[(int)$r['id']] = $r['nama'];
}

if (count($krit) === 0 || count($alt) === 0) {
  header("Location: mulai.php");
  exit;
}

/* ambil nilai */
$qNil  = mysqli_query($conn, "SELECT id_alternatif, id_kriteria, nilai FROM nilai");
$matrix = []; // format: [kriteria][alternatif]
$nilaiCount = 0;

while ($r = mysqli_fetch_assoc($qNil)) {
  $aid = (int)$r['id_alternatif'];
  $kid = (int)$r['id_kriteria'];
  $matrix[$kid][$aid] = (float)$r['nilai'];
  $nilaiCount++;
}

/* validasi kelengkapan nilai */
$expected = count($krit) * count($alt);
if ($nilaiCount < $expected) {
  $_SESSION['error_nilai'] = "Nilai belum lengkap. Harusnya $expected input, tapi baru ada $nilaiCount. Isi semua nilai dulu ya.";
  header("Location: nilai.php");
  exit;
}

/* pastiin semua sel ada */
foreach ($krit as $kid => $_k) {
  if (!isset($matrix[$kid])) $matrix[$kid] = [];
  foreach ($alt as $aid => $_a) {
    if (!isset($matrix[$kid][$aid])) $matrix[$kid][$aid] = 0.0;
  }
}

/******** STEP 1 — Matriks Keputusan ********/
$steps['matrix_keputusan'] = $matrix;

/******** STEP 2 — Normalisasi ********/
$normal = [];
$normInfo = []; // sum_sq & sqrt pembagi per kriteria

foreach ($krit as $kid => $_) {
  $sum = 0.0;
  foreach ($alt as $aid => $_a) $sum += pow($matrix[$kid][$aid], 2);

  $akar = sqrt($sum);
  if ($akar == 0) $akar = 1;

  $normInfo[$kid] = ['sum_sq' => $sum, 'sqrt' => $akar];

  foreach ($alt as $aid => $_a) {
    $normal[$kid][$aid] = $matrix[$kid][$aid] / $akar;
  }
}

$steps['normalisasi'] = $normal;
$steps['norm_info']   = $normInfo;

/******** STEP 3 — Normalisasi Terbobot ********/
$terbobot = [];
foreach ($krit as $kid => $k) {
  foreach ($alt as $aid => $_a) {
    $terbobot[$kid][$aid] = $normal[$kid][$aid] * (float)$k['bobot'];
  }
}
$steps['terbobot'] = $terbobot;

/******** STEP 4 — A+ dan A- ********/
$Aplus = [];
$Amin  = [];
foreach ($krit as $kid => $k) {
  $vals = array_values($terbobot[$kid]);
  if ($k['sifat'] === 'benefit') {
    $Aplus[$kid] = max($vals);
    $Amin[$kid]  = min($vals);
  } else {
    $Aplus[$kid] = min($vals);
    $Amin[$kid]  = max($vals);
  }
}
$steps['ideal_plus']  = $Aplus;
$steps['ideal_minus'] = $Amin;

/******** STEP 5 — D+ dan D- (simpan kuadrat per kriteria buat manual) ********/
$Dplus = [];
$Dmin  = [];
$sqPlus = []; // [alt][krit] = (y - A+)^2
$sqMin  = []; // [alt][krit] = (y - A-)^2

foreach ($alt as $aid => $_a) {
  $sumPlus = 0.0;
  $sumMin  = 0.0;

  foreach ($krit as $kid => $_k) {
    $y  = $terbobot[$kid][$aid];
    $ap = $Aplus[$kid];
    $am = $Amin[$kid];

    $p = pow($y - $ap, 2);
    $m = pow($y - $am, 2);

    $sqPlus[$aid][$kid] = $p;
    $sqMin[$aid][$kid]  = $m;

    $sumPlus += $p;
    $sumMin  += $m;
  }

  $Dplus[$aid] = sqrt($sumPlus);
  $Dmin[$aid]  = sqrt($sumMin);
}

$steps['dplus']   = $Dplus;
$steps['dmin']    = $Dmin;
$steps['sq_plus'] = $sqPlus;
$steps['sq_min']  = $sqMin;

/******** STEP 6 — Skor Preferensi + Ranking ********/
$ranking = [];
foreach ($alt as $aid => $name) {
  $den = $Dplus[$aid] + $Dmin[$aid];
  $score = ($den == 0) ? 0.0 : ($Dmin[$aid] / $den);

  $ranking[] = [
    'id_alternatif' => $aid,
    'alternatif'    => $name,
    'skor'          => round($score, 6),
  ];
}

/* sort skor desc */
usort($ranking, fn($a,$b) => $b['skor'] <=> $a['skor']);

/* set ranking 1..n */
foreach ($ranking as $i => &$r) $r['ranking'] = $i + 1;
unset($r);

$_SESSION['ranking'] = $ranking;
$_SESSION['steps']   = $steps;

/* simpan ke tabel hasil (bind sekali, execute berkali-kali) */
mysqli_query($conn, "DELETE FROM hasil");

$stmt = mysqli_prepare($conn, "INSERT INTO hasil (id_alternatif, skor, ranking) VALUES (?, ?, ?)");
if ($stmt) {
  mysqli_stmt_bind_param($stmt, "idi", $ida, $sk, $rk);
  foreach ($ranking as $r) {
    $ida = (int)$r['id_alternatif'];
    $sk  = (float)$r['skor'];
    $rk  = (int)$r['ranking'];
    mysqli_stmt_execute($stmt);
  }
  mysqli_stmt_close($stmt);
}

header("Location: hasil.php");
exit;
