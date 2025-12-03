<?php
require('library/fpdf.php');
include 'db.php';

$qAlt   = mysqli_query($conn, "SELECT * FROM alternatif ORDER BY id ASC");
$qKri   = mysqli_query($conn, "SELECT * FROM kriteria ORDER BY id ASC");
$qHasil = mysqli_query($conn, "
    SELECT hasil.*, alternatif.nama
    FROM hasil
    JOIN alternatif ON hasil.id_alternatif = alternatif.id
    ORDER BY ranking ASC
");

class PDF extends FPDF {

    function Header() {
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 7, "LAPORAN HASIL PERHITUNGAN TOPSIS", 0, 1, 'C');

        $this->SetFont('Arial', '', 12);
        $this->Cell(0, 6, "Sistem Pendukung Keputusan - Metode TOPSIS", 0, 1, 'C');
        $this->Cell(0, 6, "Tanggal: " . date('d-m-Y'), 0, 1, 'C');

        $this->Ln(4);
        $this->Line(10, 31, 200, 31);
        $this->Ln(6);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 9);
        $this->Cell(0, 10, 'Dicetak otomatis oleh sistem TOPSIS | Halaman '.$this->PageNo(), 0, 0, 'C');
    }

    function SectionTitle($title) {
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 7, $title, 0, 1);
        $this->SetFont('Arial', '', 11);
    }

    function TableHeader() {
        $this->SetFont('Arial', 'B', 11);
        $this->SetFillColor(230, 230, 230);

        $this->Cell(10, 8, "No", 1, 0, 'C', true);
        $this->Cell(75, 8, "Alternatif", 1, 0, 'C', true);
        $this->Cell(30, 8, "Skor", 1, 0, 'C', true);
        $this->Cell(30, 8, "Ranking", 1, 1, 'C', true);

        $this->SetFont('Arial', '', 11);
    }
}

$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 11);

// ALTERNATIF
$pdf->SectionTitle("Daftar Alternatif");
if (mysqli_num_rows($qAlt) > 0) {
    while ($row = mysqli_fetch_assoc($qAlt)) {
        $pdf->Cell(0, 6, "- " . $row['nama'], 0, 1);
    }
} else {
    $pdf->Cell(0, 6, "Data alternatif belum tersedia.", 0, 1);
}
$pdf->Ln(4);

// KRITERIA
$pdf->SectionTitle("Daftar Kriteria");
if (mysqli_num_rows($qKri) > 0) {
    while ($row = mysqli_fetch_assoc($qKri)) {
        $pdf->Cell(0, 6, "- {$row['nama']} | Bobot {$row['bobot']} | " . strtoupper($row['sifat']), 0, 1);
    }
} else {
    $pdf->Cell(0, 6, "Data kriteria belum tersedia.", 0, 1);
}
$pdf->Ln(6);

// HASIL RANKING
$pdf->SectionTitle("Hasil Perankingan");
$pdf->TableHeader();

$no = 1;
while ($row = mysqli_fetch_assoc($qHasil)) {
    $pdf->Cell(10, 8, $no, 1, 0, 'C');
    $pdf->Cell(75, 8, $row['nama'], 1);
    $pdf->Cell(30, 8, number_format($row['skor'], 4), 1, 0, 'C');
    $pdf->Cell(30, 8, $row['ranking'], 1, 1, 'C');
    $no++;
}

$pdf->Output("D", "topsis_laporan.pdf");
?>
