<?php
session_start();

$ranking = $_SESSION['ranking'] ?? null;
if (!$ranking){
    die("Data ranking tidak ditemukan.");
}

header("Content-Type: text/csv");
header("Content-Disposition: attachment; filename=hasil_topsis.csv");

$output = fopen("php://output", "w");

// header kolom
fputcsv($output, ["Peringkat","Alternatif","Skor"]);

$no = 1;
foreach ($ranking as $r){
    fputcsv($output, [$no, $r['alternatif'], $r['skor']]);
    $no++;
}

fclose($output);
exit;
?>
