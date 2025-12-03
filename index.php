<?php
include 'db.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>SPK TOPSIS</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="site-overlay">
<nav class="navbar navbar-expand-lg">
  <div class="container container-narrow">
    <a class="navbar-brand" href="index.php"><i class="fa-solid fa-chart-simple icon"></i>SPK TOPSIS</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav"><span class="navbar-toggler-icon"></span></button>
    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link active" href="index.php">Beranda</a></li>
        <li class="nav-item"><a class="nav-link" href="#fitur">Fitur</a></li>
        <li class="nav-item"><a class="nav-link" href="#tentang">Tentang</a></li>
      </ul>
    </div>
  </div>
</nav>

<section class="hero">
  <div class="container container-narrow">
    <h1 class="hero-title">TOPSIS Solver untuk Mahasiswa</h1>
    <p class="hero-subtitle">Analisis multikriteria jadi lebih mudah dan cepat</p>
    <div>
      <a href="mulai.php" class="btn btn-primary btn-lg me-2"><i class="fa-solid fa-rocket me-2"></i>Mulai Analisis</a>
      <a href="#tentang" class="btn btn-outline-light btn-lg"><i class="fa-solid fa-book-open me-2"></i>Pelajari</a>
    </div>
  </div>
</section>

<section id="fitur" class="py-5 bg-light">
  <div class="container container-narrow">
    <h2 class="text-center mb-4 fw-bold">Fitur Utama</h2>
    <div class="row g-4">
      <div class="col-md-4">
        <div class="fitur-card p-4 rounded shadow-sm text-center">
          <i class="fa-solid fa-file-signature fa-2x mb-2" style="color:var(--purple-500)"></i>
          <h5 class="fw-bold">Input Data Mudah</h5>
          <p>Kamu bisa memasukkan kriteria dan alternatif secara langsung tanpa ribet.</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="fitur-card p-4 rounded shadow-sm text-center">
          <i class="fa-solid fa-calculator fa-2x mb-2" style="color:var(--purple-500)"></i>
          <h5 class="fw-bold">Perhitungan Otomatis</h5>
          <p>Normalisasi, bobot, dan peringkat dihitung otomatis.</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="fitur-card p-4 rounded shadow-sm text-center">
          <i class="fa-solid fa-award fa-2x mb-2" style="color:var(--purple-500)"></i>
          <h5 class="fw-bold">Hasil Akurat</h5>
          <p>Metode TOPSIS menghasilkan output yang terukur dan bisa diuji.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<section id="tentang" class="py-5">
  <div class="container container-narrow">
    <div class="about-card p-5 rounded shadow-sm text-center">
      <h2 class="fw-bold mb-3">Apa itu TOPSIS?</h2>
      <p>Metode pemilihan multikriteria berdasarkan jarak tiap alternatif ke solusi ideal positif dan negatif.</p>
    </div>
  </div>
</section>

<footer class="py-3">
  <div class="container container-narrow text-white">
    <small>SPK TOPSIS — dibuat untuk tugas & pembelajaran</small>
  </div>
</footer>
</div><!-- /overlay -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
