<?php
session_start();
include 'db.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mulai Analisis TOPSIS</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>
<div class="page">

  <!-- Navbar: konsisten dengan style modern -->
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
    <h2 class="fw-bold mb-1">Mulai Analisis</h2>
    <div class="text-muted">Isi informasi dasar untuk membuat sesi perhitungan TOPSIS.</div>
  </div>

  <div class="stepper">
    <div class="stepper-item active">
      <span class="bubble">1</span>
      <span class="label">Info</span>
    </div>
    <div class="stepper-line"></div>

    <div class="stepper-item">
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


    <!-- Layout 2 kolom -->
    <div class="row g-4 align-items-stretch">
      <!-- Form -->
      <div class="col-lg-7">
        <div class="cardx h-100">
          <div class="d-flex align-items-start gap-3 mb-3">
            <div class="form-icon">
              <i class="fa-solid fa-pen-to-square"></i>
            </div>
            <div>
              <h4 class="fw-bold mb-1">Informasi Dasar</h4>
              <div class="text-muted">Masukkan judul analisis untuk memulai proses TOPSIS.</div>
            </div>
          </div>

          <form action="alternatif.php" method="POST" class="mt-3">
            <div class="mb-3">
              <label class="form-label fw-semibold">Judul Analisis</label>
              <input
                type="text"
                name="judul"
                class="form-control form-control-lg"
                placeholder="Contoh: Pemilihan Supplier Terbaik"
                required
              >
              <div class="form-text">Gunakan judul yang spesifik agar mudah diingat.</div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
              <a href="index.php" class="btn btn-outline-secondary">
                <i class="fa-solid fa-arrow-left me-2"></i>Kembali
              </a>
              <button type="submit" class="btn btn-primary px-4">
                Lanjut <i class="fa-solid fa-arrow-right ms-2"></i>
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Side info -->
      <div class="col-lg-5">
        <div class="cardx h-100">
          <div class="fw-bold mb-2"><i class="fa-solid fa-circle-info me-2" style="color: var(--p1);"></i>Petunjuk Singkat</div>
          <ul class="text-muted mb-4" style="padding-left: 18px; line-height: 1.7;">
            <li>Step 1: Isi judul analisis.</li>
            <li>Step 2: Tambahkan alternatif.</li>
            <li>Step 3: Tambahkan kriteria + bobot.</li>
            <li>Step 4: Sistem hitung dan tampilkan ranking.</li>
          </ul>

          <div class="tips-box">
            <div class="fw-bold mb-1">Tips judul yang bagus</div>
            <div class="text-muted">Contoh: “Pemilihan Laptop untuk Kuliah”, “Seleksi Beasiswa”, “Pemilihan Vendor Terbaik”.</div>
          </div>
        </div>
      </div>
    </div>
  </main>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>