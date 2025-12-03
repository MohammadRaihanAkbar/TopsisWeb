<?php
session_start();
require "db.php";

// ➤ Ambil alternatif, kriteria, nilai
$alternatif = mysqli_query($conn, "SELECT * FROM alternatif");
$kriteria = mysqli_query($conn, "SELECT * FROM kriteria ORDER BY id");

$listAlternatif = [];
while($a = mysqli_fetch_assoc($alternatif)) $listAlternatif[$a['id']] = $a['nama'];

$listKriteria = [];
while($k = mysqli_fetch_assoc($kriteria)) $listKriteria[$k['id']] = $k;

$nilai = mysqli_query($conn, "
    SELECT * FROM nilai
");
$matrix = [];
while($n = mysqli_fetch_assoc($nilai)){
    $matrix[$n['id_alternatif']][$n['id_kriteria']] = $n['nilai'];
}

// ============= 1. MATRiks KEPUTUSAN =============
$decisionMatrix = $matrix;

// ============= 2. NORMALISASI =============
$normal = [];
foreach($listKriteria as $idk => $kr){
    $sum = 0;
    foreach($listAlternatif as $ida => $nm)
        $sum += pow($decisionMatrix[$ida][$idk] ?? 0, 2);
    $sq = sqrt($sum);

    foreach($listAlternatif as $ida => $nm)
        $normal[$ida][$idk] = ($decisionMatrix[$ida][$idk] ?? 0) / ($sq ?: 1);
}

// ============= 3. NORMALISASI TERBOBOT =============
$weighted = [];
foreach($listAlternatif as $ida => $nm)
    foreach($listKriteria as $idk => $kr)
        $weighted[$ida][$idk] = $normal[$ida][$idk] * $kr['bobot'];

// ============= 4. SOLUSI IDEAL =============
$idealPlus = [];  // A+
$idealMin  = [];  // A-
foreach($listKriteria as $idk => $kr){
    $vals = array_column($weighted, $idk);
    $idealPlus[$idk] = ($kr['sifat'] == 'benefit') ? max($vals) : min($vals);
    $idealMin[$idk]  = ($kr['sifat'] == 'benefit') ? min($vals) : max($vals);
}

// ============= 5. JARAK A+ & A- =============
$Dplus = $Dmin = [];
foreach($listAlternatif as $ida => $nm){
    $sum1 = $sum2 = 0;
    foreach($listKriteria as $idk => $kr){
        $sum1 += pow($weighted[$ida][$idk] - $idealPlus[$idk], 2);
        $sum2 += pow($weighted[$ida][$idk] - $idealMin[$idk], 2);
    }
    $Dplus[$ida] = sqrt($sum1);
    $Dmin[$ida]  = sqrt($sum2);
}

// ============= 6. NILAI PREFERENSI / RANKING =============
$preferensi = [];
foreach($listAlternatif as $ida => $nm){
    $preferensi[$ida] = $Dmin[$ida] / ($Dmin[$ida] + $Dplus[$ida]);
}

// urutkan ranking
arsort($preferensi);
$ranked = [];
$i = 1;
foreach($preferensi as $ida => $v){
    $ranked[] = ['id_alternatif'=>$ida,'nama'=>$listAlternatif[$ida],'skor'=>$v,'ranking'=>$i];
    $i++;
}

// simpan ke DB tabel hasil (hapus dulu)
mysqli_query($conn, "DELETE FROM hasil");
foreach($ranked as $r)
    mysqli_query($conn, "INSERT INTO hasil(id_alternatif, skor, ranking) VALUES ('{$r['id_alternatif']}', '{$r['skor']}', '{$r['ranking']}')");

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Hasil TOPSIS</title>
<link rel="stylesheet" href="style.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="site-overlay">

<div class="container container-narrow py-4">

<!-- Alternatif terbaik -->
<div class="box text-center">
  <div class="title">🔝 Alternatif Terbaik</div>
  <div class="best"><?= $ranked[0]['nama'] ?></div>
  <div class="fw-bold text-muted">Skor: <?= round($ranked[0]['skor'],4) ?></div>
</div>

<!-- PERINGKAT -->
<div class="box">
  <h5 class="fw-bold mb-3">Peringkat Alternatif</h5>
  <table class="table table-bordered">
    <thead><tr><th>Peringkat</th><th>Alternatif</th><th>Skor</th></tr></thead>
    <tbody>
    <?php foreach($ranked as $r): ?>
      <tr>
        <td><?= $r['ranking'] ?></td>
        <td><?= $r['nama'] ?></td>
        <td><?= round($r['skor'],4) ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

<!-- 🔍 PROSES PERHITUNGAN -->
<div class="box">
<h5 class="fw-bold mb-3">Langkah-langkah Perhitungan TOPSIS</h5>

<!-- MATRiks KEPUTUSAN -->
<p><b>1️⃣ Matriks Keputusan (X)</b></p>
<div class="mono mb-2">x<sub>ij</sub> = nilai alternatif i terhadap kriteria j</div>
<table class="table table-bordered">
<thead>
<tr><th>Alternatif</th>
<?php foreach($listKriteria as $kr) echo "<th>{$kr['nama']}</th>"; ?>
</tr></thead>
<tbody>
<?php foreach($decisionMatrix as $ida => $row): ?>
<tr>
<td><?= $listAlternatif[$ida] ?></td>
<?php foreach($listKriteria as $idk => $kr) echo "<td>{$row[$idk]}</td>"; ?>
</tr>
<?php endforeach; ?>
</tbody></table>

<!-- NORMALISASI -->
<p class="mt-4"><b>2️⃣ Normalisasi Matriks (R)</b></p>
<div class="mono mb-2">r<sub>ij</sub> = x<sub>ij</sub> / √Σ(x<sub>ij</sub>²)</div>
<table class="table table-bordered">
<thead><tr><th>Alternatif</th>
<?php foreach($listKriteria as $kr) echo "<th>{$kr['nama']}</th>"; ?>
</tr></thead>
<tbody>
<?php foreach($normal as $ida => $row): ?>
<tr>
<td><?= $listAlternatif[$ida] ?></td>
<?php foreach($listKriteria as $idk => $kr) echo "<td>".round($row[$idk],4)."</td>"; ?>
</tr>
<?php endforeach; ?>
</tbody></table>

<!-- NORMALISASI TERBOBOT -->
<p class="mt-4"><b>3️⃣ Normalisasi Terbobot (Y)</b></p>
<div class="mono mb-2">y<sub>ij</sub> = w<sub>j</sub> × r<sub>ij</sub></div>
<table class="table table-bordered">
<thead><tr><th>Alternatif</th>
<?php foreach($listKriteria as $kr) echo "<th>{$kr['nama']}</th>"; ?>
</tr></thead>
<tbody>
<?php foreach($weighted as $ida => $row): ?>
<tr>
<td><?= $listAlternatif[$ida] ?></td>
<?php foreach($listKriteria as $idk => $kr) echo "<td>".round($row[$idk],4)."</td>"; ?>
</tr>
<?php endforeach; ?>
</tbody></table>

<!-- SOLUSI IDEAL -->
<p class="mt-4"><b>4️⃣ Solusi Ideal Positif A⁺ & Negatif A⁻</b></p>
<table class="table table-bordered">
<thead><tr><th></th>
<?php foreach($listKriteria as $kr) echo "<th>{$kr['nama']}</th>"; ?>
</tr></thead>
<tbody>
<tr><td><b>A⁺</b></td>
<?php foreach($idealPlus as $v) echo "<td>".round($v,4)."</td>"; ?>
</tr>
<tr><td><b>A⁻</b></td>
<?php foreach($idealMin as $v) echo "<td>".round($v,4)."</td>"; ?>
</tr>
</tbody></table>

<!-- JARAK -->
<p class="mt-4"><b>5️⃣ Jarak Terhadap A⁺ dan A⁻</b></p>
<div class="mono mb-2">
D<sub>i</sub><sup>+</sup> = √Σ(y<sub>ij</sub> − A<sub>j</sub><sup>+</sup>)²<br>
D<sub>i</sub><sup>−</sup> = √Σ(y<sub>ij</sub> − A<sub>j</sub><sup>−</sup>)²
</div>
<table class="table table-bordered">
<thead><tr><th>Alternatif</th><th>D⁺</th><th>D⁻</th></tr></thead>
<tbody>
<?php foreach($listAlternatif as $ida => $nm): ?>
  <tr><td><?= $nm ?></td>
  <td><?= round($Dplus[$ida],4) ?></td>
  <td><?= round($Dmin[$ida],4) ?></td></tr>
<?php endforeach; ?>
</tbody></table>

<!-- NILAI PREFERENSI -->
<p class="mt-4"><b>6️⃣ Nilai Preferensi</b></p>
<div class="mono mb-2">
V<sub>i</sub> = D<sub>i</sub><sup>−</sup> / (D<sub>i</sub><sup>−</sup> + D<sub>i</sub><sup>+</sup>)
</div>
<table class="table table-bordered">
<thead><tr><th>Alternatif</th><th>V</th></tr></thead>
<tbody>
<?php foreach($preferensi as $ida => $v): ?>
<tr><td><?= $listAlternatif[$ida] ?></td><td><?= round($v,4) ?></td></tr>
<?php endforeach; ?>
</tbody></table>

</div>

<div class="export-buttons">
    <a href="export_pdf.php" class="btn btn-danger">Export PDF</a>
    <a href="export_csv.php" class="btn btn-success">Export CSV</a>
</div>

<a href="index.php" class="btn btn-primary mt-3">Kembali ke Dashboard</a>

</div>
</div>
</body>
</html>
