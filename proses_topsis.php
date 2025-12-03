<?php
session_start();
include "db.php";

// reset session
unset($_SESSION['steps'], $_SESSION['ranking']);

$steps = [];

// ambil data
$qKrit = mysqli_query($conn, "SELECT * FROM kriteria ORDER BY id ASC");
$qAlt  = mysqli_query($conn, "SELECT * FROM alternatif ORDER BY id ASC");
$qNil  = mysqli_query($conn, "SELECT * FROM nilai ORDER BY id_alternatif, id_kriteria");

$krit = [];
foreach ($qKrit as $r) {
    $krit[$r['id']] = [
        'nama' => $r['nama'],
        'bobot' => floatval($r['bobot']),
        'sifat' => $r['sifat'] // benefit / cost
    ];
}

$alt = [];
foreach ($qAlt as $r) {
    $alt[$r['id']] = $r['nama'];
}

$matrix = [];
foreach ($qNil as $r) {
    $matrix[$r['id_kriteria']][$r['id_alternatif']] = floatval($r['nilai']);
}

/******** STEP 1 — Matriks Keputusan ********/
$steps['matrix_keputusan'] = $matrix;

/******** STEP 2 — Normalisasi ********/
$normal = [];
foreach ($krit as $kid => $_) {
    $sum = 0;
    foreach ($alt as $aid => $_) $sum += pow($matrix[$kid][$aid], 2);
    $akar = sqrt($sum);

    foreach ($alt as $aid => $_) {
        $normal[$kid][$aid] = $akar == 0 ? 0 : $matrix[$kid][$aid] / $akar;
    }
}
$steps['normalisasi'] = $normal;

/******** STEP 3 — Normalisasi terbobot ********/
$terbobot = [];
foreach ($krit as $kid => $k) {
    foreach ($alt as $aid => $_) {
        $terbobot[$kid][$aid] = $normal[$kid][$aid] * $k['bobot'];
    }
}
$steps['terbobot'] = $terbobot;

/******** STEP 4 — A+ dan A- ********/
$Aplus = [];
$Amin  = [];
foreach ($krit as $kid => $k) {
    $vals = array_values($terbobot[$kid]);
    if ($k['sifat'] === "benefit") {
        $Aplus[$kid] = max($vals);
        $Amin[$kid]  = min($vals);
    } else {
        $Aplus[$kid] = min($vals);
        $Amin[$kid]  = max($vals);
    }
}
$steps['ideal_plus']  = $Aplus;
$steps['ideal_minus'] = $Amin;

/******** STEP 5 — D+ dan D- ********/
$Dplus = [];
$Dmin  = [];
foreach ($alt as $aid => $_) {
    $sumPlus = 0;
    $sumMin  = 0;
    foreach ($krit as $kid => $_) {
        $sumPlus += pow($terbobot[$kid][$aid] - $Aplus[$kid], 2);
        $sumMin  += pow($terbobot[$kid][$aid] - $Amin[$kid], 2);
    }
    $Dplus[$aid] = sqrt($sumPlus);
    $Dmin[$aid]  = sqrt($sumMin);
}
$steps['dplus'] = $Dplus;
$steps['dmin']  = $Dmin;

/******** STEP 6 — Skor preferensi ********/
$ranking = [];
foreach ($alt as $aid => $name) {
    $score = ($Dplus[$aid] + $Dmin[$aid] == 0) ? 0 : $Dmin[$aid] / ($Dplus[$aid] + $Dmin[$aid]);
    $ranking[] = [
        'alternatif' => $name,
        'skor' => round($score, 6)
    ];
}

// sort score tertinggi
usort($ranking, fn($a,$b) => $b['skor'] <=> $a['skor']);
$_SESSION['ranking'] = $ranking;
$_SESSION['steps']   = $steps;

// simpan ke database tabel hasil
mysqli_query($conn, "DELETE FROM hasil");
foreach ($ranking as $i => $r) {
    $rank = $i + 1;
    $name = mysqli_real_escape_string($conn, $r['alternatif']);
    $score = $r['skor'];
    mysqli_query($conn, "
        INSERT INTO hasil(id_alternatif, skor, ranking)
        SELECT id, '$score', '$rank' FROM alternatif WHERE nama = '$name'
    ");
}

header("Location: hasil.php");
exit;
?>
