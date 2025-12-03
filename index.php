<?php include 'db.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>SPK TOPSIS</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <!-- CSS kamu harus paling bawah -->
  <link rel="stylesheet" href="style.css">
</head>

<body>
<div class="page">

  <!-- NAVBAR -->
  <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
    <div class="container container-narrow">
      <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="index.php">
        <span class="logo-pill"><i class="fa-solid fa-chart-simple"></i></span>
        <span>SPK TOPSIS</span>
      </a>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="nav">
        <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
          <li class="nav-item"><a class="nav-link" href="index.php">Beranda</a></li>
          <li class="nav-item"><a class="nav-link" href="#fitur">Fitur</a></li>
          <li class="nav-item"><a class="nav-link" href="#tentang">Tentang</a></li>
          <li class="nav-item ms-lg-2">
            <a class="btn btn-primary btn-sm px-3" href="mulai.php">
              <i class="fa-solid fa-bolt me-2"></i>Mulai
            </a>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- HERO -->
  <header class="hero hero-bg">
    <div class="container container-narrow">
      <div class="row align-items-center g-4">
        <div class="col-lg-6 reveal reveal-up">
          <div class="badge-soft mb-3 reveal reveal-up delay-1">
            <i class="fa-solid fa-wand-magic-sparkles me-2"></i>
            Kelompok 4
          </div>

          <h1 class="display-5 fw-bold hero-title reveal reveal-up delay-2">
            TOPSIS Solver <span class="text-gradient">untuk Mahasiswa</span>
          </h1>

          <p class="hero-desc reveal reveal-up delay-3">
            Input kriteria & alternatif, sistem hitung otomatis dan tampilkan ranking yang mudah dipahami.
          </p>

          <div class="d-flex gap-2 flex-wrap mt-3 reveal reveal-up delay-4">
            <a href="mulai.php" class="btn btn-primary btn-lg px-4">
              <i class="fa-solid fa-rocket me-2"></i>Mulai Analisis
            </a>
            <a href="#tentang" class="btn btn-outline-secondary btn-lg px-4">
              <i class="fa-solid fa-book-open me-2"></i>Pelajari
            </a>
          </div>

          <div class="mini-stats mt-4 reveal reveal-up delay-5">
            <div class="mini">
              <i class="fa-solid fa-layer-group"></i>
              <div>
                <div class="mini-title">Multi-kriteria</div>
                <div class="mini-sub">Kelola bobot & nilai</div>
              </div>
            </div>
            <div class="mini">
              <i class="fa-solid fa-gears"></i>
              <div>
                <div class="mini-title">Otomatis</div>
                <div class="mini-sub">Normalisasi → Ranking</div>
              </div>
            </div>
            <div class="mini">
              <i class="fa-solid fa-award"></i>
              <div>
                <div class="mini-title">Hasil Jelas</div>
                <div class="mini-sub">Transparan & bisa diuji</div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-6 reveal reveal-right delay-2">
          <div class="hero-card">
            <div class="hero-card-top">
              <div class="dot red"></div>
              <div class="dot yellow"></div>
              <div class="dot green"></div>
            </div>
            <div class="hero-card-body">
              <div class="row g-3">
                <div class="col-6">
                  <div class="kpi reveal reveal-up delay-1">
                    <div class="kpi-label">Langkah</div>
                    <div class="kpi-value">4</div>
                    <div class="kpi-sub">Input → Hasil</div>
                  </div>
                </div>
                <div class="col-6">
                  <div class="kpi reveal reveal-up delay-2">
                    <div class="kpi-label">Output</div>
                    <div class="kpi-value">Ranking</div>
                    <div class="kpi-sub">Nilai preferensi</div>
                  </div>
                </div>

                <div class="col-12">
                  <div class="preview reveal" id="previewBox">
                    <div class="preview-title">Preview Hasil</div>

                    <div class="preview-row">
                      <span>A1</span><span class="bar w82"></span><span>0.82</span>
                    </div>
                    <div class="preview-row">
                      <span>A2</span><span class="bar w73"></span><span>0.73</span>
                    </div>
                    <div class="preview-row">
                      <span>A3</span><span class="bar w61"></span><span>0.61</span>
                    </div>
                  </div>
                </div>
              </div>
              <div class="small text-muted mt-3">*Tampilan preview.</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </header>

  <!-- FITUR -->
  <section id="fitur" class="section">
    <div class="container container-narrow">
      <div class="text-center mb-4 reveal reveal-up">
        <h2 class="fw-bold">Fitur Utama</h2>
        <p class="text-muted mb-0">Semua yang kamu butuhkan untuk perhitungan TOPSIS yang nyaman.</p>
      </div>

      <div class="row g-4">
        <div class="col-md-4 reveal reveal-up delay-1">
          <div class="feature h-100">
            <div class="feature-icon"><i class="fa-solid fa-file-signature"></i></div>
            <h5 class="fw-bold mt-3">Input Data Mudah</h5>
            <p class="text-muted mb-0">Masukkan kriteria & alternatif tanpa ribet. UI ringkas dan rapi.</p>
          </div>
        </div>
        <div class="col-md-4 reveal reveal-up delay-2">
          <div class="feature h-100">
            <div class="feature-icon"><i class="fa-solid fa-calculator"></i></div>
            <h5 class="fw-bold mt-3">Perhitungan Otomatis</h5>
            <p class="text-muted mb-0">Normalisasi, pembobotan, sampai ranking dihitung otomatis.</p>
          </div>
        </div>
        <div class="col-md-4 reveal reveal-up delay-3">
          <div class="feature h-100">
            <div class="feature-icon"><i class="fa-solid fa-award"></i></div>
            <h5 class="fw-bold mt-3">Hasil Akurat</h5>
            <p class="text-muted mb-0">Output transparan dan mudah dicek ulang untuk laporan/presentasi.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- TENTANG -->
  <section id="tentang" class="section section-gray">
    <div class="container container-narrow">
      <div class="row g-4 align-items-stretch">
        <div class="col-lg-7 reveal reveal-left delay-1">
          <div class="cardx h-100">
            <h2 class="fw-bold mb-2">Apa itu TOPSIS?</h2>
            <p class="text-muted mb-3">
              TOPSIS memilih alternatif terbaik berdasarkan jarak ke solusi ideal positif dan negatif.
              Cocok untuk pemilihan laptop, beasiswa, kandidat, lokasi, vendor, dll.
            </p>

            <div class="checklist">
              <div class="check"><i class="fa-solid fa-circle-check"></i> Transparan (langkah jelas)</div>
              <div class="check"><i class="fa-solid fa-circle-check"></i> Fleksibel (banyak kriteria)</div>
              <div class="check"><i class="fa-solid fa-circle-check"></i> Cepat (hasil ranking otomatis)</div>
            </div>

            <div class="mt-4">
              <a href="mulai.php" class="btn btn-primary px-4">
                <i class="fa-solid fa-play me-2"></i>Coba Sekarang
              </a>
            </div>
          </div>
        </div>

        <div class="col-lg-5 reveal reveal-right delay-2">
          <div class="cardx h-100">
            <h5 class="fw-bold mb-3">Workflow Singkat</h5>
            <ol class="text-muted mb-0">
              <li>Tambah kriteria + bobot</li>
              <li>Tambah alternatif</li>
              <li>Isi nilai tiap kriteria</li>
              <li>Lihat hasil & ranking</li>
            </ol>
          </div>
        </div>
      </div>
    </div>
  </section>

  <footer class="py-4">
    <div class="container container-narrow text-center">
      <small class="text-muted">SPK TOPSIS — dibuat untuk tugas & pembelajaran</small>
    </div>
  </footer>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- ANIMASI SCROLL REVEAL -->
<script>
(function(){
  const els = document.querySelectorAll('.reveal');

  if (!('IntersectionObserver' in window)){
    els.forEach(el => el.classList.add('is-visible'));
    // preview bar
    document.querySelectorAll('.preview').forEach(p => p.classList.add('is-visible'));
    return;
  }

  const io = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if(entry.isIntersecting){
        entry.target.classList.add('is-visible');

        // kalau preview muncul, animasikan bar
        if(entry.target.classList.contains('preview')){
          entry.target.classList.add('is-visible');
        }

        io.unobserve(entry.target);
      }
    });
  }, { threshold: 0.12 });

  els.forEach(el => io.observe(el));
})();
</script>

</body>
</html>
