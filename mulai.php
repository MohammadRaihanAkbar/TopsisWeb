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
<div class="site-overlay">
<nav class="navbar navbar-expand-lg">
  <div class="container container-narrow">
    <a class="navbar-brand" href="index.php"><i class="fa-solid fa-chart-simple icon"></i>SPK TOPSIS</a>
  </div>
</nav>

<div class="container container-narrow" style="margin-top:110px">
  <div class="card-clean">
    <div class="text-center mb-3">
      <span class="step active">1</span>
      <span class="ms-3">Info Dasar</span>
    </div>

    <h3 class="fw-bold"><i class="fa-solid fa-pen-to-square icon"></i>Informasi Dasar</h3>
    <p class="text-muted">Masukkan judul analisis. Metode SPK: TOPSIS.</p>

    <form action="alternatif.php" method="POST">
      <div class="mb-3">
        <label class="form-label">Judul Analisis</label>
        <input type="text" name="judul" class="form-control" placeholder="Contoh: Pemilihan Supplier Terbaik" required>
      </div>

      <div class="d-flex justify-content-end">
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-arrow-right me-2"></i>Lanjut</button>
      </div>
    </form>

  </div>
</div>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
