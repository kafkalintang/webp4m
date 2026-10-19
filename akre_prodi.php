<?php
include "koneksi.php"; 

// ==========================================
// 1. AMBIL DATA DARI DATABASE
// ==========================================
$query = "
    SELECT a1.*, ps.nama_program_studi, ps.nama_jurusan, l.nama_lembaga, ja.jenis_akreditasi
    FROM akreditasi a1
    INNER JOIN (
        SELECT id_prodi, MAX(tanggal_berakhir) AS max_tanggal
        FROM akreditasi
        WHERE akreditasi = 'program studi'
        GROUP BY id_prodi
    ) a2 ON a1.id_prodi = a2.id_prodi AND a1.tanggal_berakhir = a2.max_tanggal
    INNER JOIN program_studi ps ON a1.id_prodi = ps.id_prodi
    LEFT JOIN lembaga l ON a1.id_lembaga = l.id_lembaga
    LEFT JOIN jenis_akreditasi ja ON a1.id_jenis = ja.id_jenis
    WHERE a1.akreditasi = 'program studi'
    ORDER BY ps.nama_jurusan ASC
";
$result = mysqli_query($koneksi, $query);

$data_prodi = [];
$total_unggul = 0;
$total_baik_sekali = 0;
$total_baik = 0;
$total_expire = 0;
$total_semua = 0;
$hari_ini = date('Y-m-d');

while ($row = mysqli_fetch_assoc($result)) {
    $data_prodi[] = $row;
    $total_semua++;
    
    // Hitung Statistik Peringkat
    $peringkat = strtolower($row['peringkat']);
    if (strpos($peringkat, 'unggul') !== false) {
        $total_unggul++;
    } elseif (strpos($peringkat, 'baik sekali') !== false) {
        $total_baik_sekali++;
    } else {
        $total_baik++;
    }

    // Hitung Masa Expire (< 1 Tahun)
    $tgl_berakhir = new DateTime($row['tanggal_berakhir']);
    $tgl_sekarang = new DateTime($hari_ini);
    $selisih = $tgl_sekarang->diff($tgl_berakhir)->days;
    
    if ($tgl_berakhir >= $tgl_sekarang && $selisih <= 365) {
        $total_expire++;
    }
}

// Hitung Persentase
$pct_unggul = ($total_semua > 0) ? round(($total_unggul / $total_semua) * 100) : 0;
$pct_baik_sekali = ($total_semua > 0) ? round(($total_baik_sekali / $total_semua) * 100) : 0;
$pct_baik = ($total_semua > 0) ? round(($total_baik / $total_semua) * 100) : 0;
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Akreditasi Program Studi - P4M Polibatam</title>
  <meta name="description" content="">
  <meta name="keywords" content="">

  <link href="assets/img/logo/polibatam.svg" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Inter:wght@100;200;300;400;500;600;700;800;900&family=Nunito:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
  
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/aos/aos.css" rel="stylesheet">
  <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
  <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">
  <link href="assets/css/main.css" rel="stylesheet">
  
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

  <style>
    .nav-tabs-custom { border-bottom: 2px solid #e9ecef; margin-bottom: 30px; }
    .nav-tabs-custom .nav-link { border: none; color: #6c757d; font-weight: 600; padding: 12px 20px; position: relative; background: transparent; }
    .nav-tabs-custom .nav-link:hover { color: #0d83fd; }
    .nav-tabs-custom .nav-link.active { color: #0d83fd; background: transparent; }
    .nav-tabs-custom .nav-link.active::after { content: ''; position: absolute; bottom: -2px; left: 0; width: 100%; height: 3px; background-color: #0d83fd; }
    
    .modern-card { background: #fff; border-radius: 12px; border: 1px solid #e9ecef; box-shadow: 0 4px 15px rgba(0,0,0,0.03); height: 100%; }
    .modern-card .card-header { background: transparent; border-bottom: 1px solid #e9ecef; padding: 20px; }
    
    .stat-card { background: #fff; border-radius: 12px; border: 1px solid #e9ecef; padding: 20px; transition: 0.3s; cursor: pointer; height: 100%; }
    .stat-card:hover { box-shadow: 0 8px 15px rgba(0,0,0,0.08); transform: translateY(-3px); border-color: #0d83fd; }
    .stat-card .stat-label { font-size: 14px; color: #6c757d; display: flex; align-items: center; gap: 8px; margin-bottom: 10px; }
    .stat-card .stat-number { font-size: 28px; font-weight: 700; color: #1e2b4c; line-height: 1; margin-bottom: 5px; }
    .stat-card .stat-desc { font-size: 13px; color: #adb5bd; }
    
    .dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; }
    
    .card-warning { background-color: #fff9e6; border-color: #ffeeba; }
    .card-warning .stat-number, .card-warning .stat-label, .card-warning .stat-desc { color: #856404; }

    .pill-filter { display: inline-block; padding: 8px 16px; border-radius: 50px; background: #fff; border: 1px solid #dee2e6; color: #495057; font-size: 14px; font-weight: 500; cursor: pointer; transition: 0.2s; }
    .pill-filter:hover { border-color: #0d83fd; color: #0d83fd; }
    .pill-filter.active { background: #0d83fd; border-color: #0d83fd; color: #fff; }
    
    .table-custom { vertical-align: middle; margin-bottom: 0; }
    .table-custom thead th { background-color: #f8f9fa; color: #1e2b4c; font-weight: 600; border-bottom: 2px solid #dee2e6; padding: 15px; }
    .table-custom tbody td { padding: 15px; border-bottom: 1px solid #e9ecef; color: #495057; }
    
    .badge-expire { background-color: #fff3cd; color: #856404; font-weight: 600; padding: 6px 12px; border-radius: 6px; }
    .badge-aktif { background-color: #d1e7dd; color: #0f5132; font-weight: 600; padding: 6px 12px; border-radius: 6px; }
  </style>
</head>

<body class="service-details-page">

  <!-- Navbar -->
  <header id="header" class="header d-flex align-items-center fixed-top">
    <div class="header-container container-fluid container-xl position-relative d-flex align-items-center justify-content-between">
      <a href="index.php" class="logo d-flex align-items-center me-auto me-xl-0">
        <img src="assets/img/logo/Polibatam.svg" alt="Logo"><h1 class="sitename">P4M Polibatam</h1>
      </a>
      <nav id="navmenu" class="navmenu">
        <ul>
          <li><a href="index.php">Home</a></li>
          <li><a href="index.php#features">Aplikasi</a></li>
          <li class="dropdown">
            <a href=""><span>Layanan</span><i class="bi bi-chevron-down toggle-dropdown"></i></a>
            <ul>
                <li><a href="hasil_kepuasan.php">Hasil Kepuasan</a></li>
                <li><a href="standar_layanan.php">Standar Layanan Polibatam</a></li>
            </ul>
          </li>
          <li class="dropdown">
            <a href="" class="active"><span>Akreditasi</span><i class="bi bi-chevron-down toggle-dropdown"></i></a>
            <ul>
                <li><a href="akre_perguruan.php">Perguruan Tinggi</a></li>
                <li><a href="akre_unit.php">Unit Kerja</a></li>
                <li><a href="akre_prodi.php" class="active">Program Studi</a></li>
            </ul>
          </li>
          <li class="dropdown">
            <a href=""><span>Program Fasilitasi P4M</span><i class="bi bi-chevron-down toggle-dropdown"></i></a>
            <ul>
                <li><a href="buku_isbn.php">Buku ISBN</a></li>
                <li><a href="video_digital.php">Video Digital</a></li>
            </ul>
          </li>
          <li><a href="berita.php">Berita</a></li>
        </ul>
        <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
       </nav>
      <a class="btn-getstarted" href="login.php">Login</a>
    </div>
  </header>

  <main class="main">

    <div class="page-title light-background" style="margin-top: 80px;">
      <div class="container">
        <div class="container section-title" data-aos="fade-up">
          <h2>Akreditasi Program Studi</h2>
          <p>Halaman ini menyediakan data Akreditasi Program Studi.</p>
        </div>
      </div>
    </div>

    <section id="service-details" class="service-details section">
      <div class="container" data-aos="fade-up">
        
        <ul class="nav nav-tabs nav-tabs-custom" id="myTab" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="nasional-tab" data-bs-toggle="tab" data-bs-target="#nasional" type="button" role="tab">Nasional</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="internasional-tab" data-bs-toggle="tab" data-bs-target="#internasional" type="button" role="tab">Internasional (IABEE / ABET)</button>
          </li>
        </ul>

        <div class="tab-content" id="myTabContent">
          
          <!-- ================= TAB NASIONAL ================= -->
          <div class="tab-pane fade show active" id="nasional" role="tabpanel">
            
            <!-- Baris Diagram & Ringkasan -->
            <div class="row g-4 mb-5 align-items-center">
              <div class="col-lg-3">
                <div class="position-relative mx-auto" style="width: 200px; height: 200px;">
                  <canvas id="donutChartNasional"></canvas>
                  <div class="position-absolute top-50 start-50 translate-middle text-center" style="pointer-events: none;">
                    <h3 class="mb-0 fw-bold" style="color: #1e2b4c;"><?= $total_semua ?></h3>
                    <small class="text-muted">prodi</small>
                  </div>
                </div>
              </div>

              <div class="col-lg-9">
                <div class="row g-3">
                  <div class="col-md-3">
                    <div class="stat-card" onclick="setFilter('kategori', 'unggul')" title="Klik untuk melihat detail">
                      <div class="stat-label"><span class="dot" style="background:#0d83fd;"></span> Unggul</div>
                      <div class="stat-number"><?= $total_unggul ?></div>
                      <div class="stat-desc"><?= $pct_unggul ?>% dari total</div>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="stat-card" onclick="setFilter('kategori', 'baik sekali')" title="Klik untuk melihat detail">
                      <div class="stat-label"><span class="dot" style="background:#7fbcff;"></span> Baik Sekali</div>
                      <div class="stat-number"><?= $total_baik_sekali ?></div>
                      <div class="stat-desc"><?= $pct_baik_sekali ?>% dari total</div>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="stat-card" onclick="setFilter('kategori', 'baik')" title="Klik untuk melihat detail">
                      <div class="stat-label"><span class="dot" style="background:#adb5bd;"></span> Baik</div>
                      <div class="stat-number"><?= $total_baik ?></div>
                      <div class="stat-desc"><?= $pct_baik ?>% dari total</div>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="stat-card card-warning" onclick="setFilter('expire', '1')" title="Klik untuk melihat detail">
                      <div class="stat-label">Masa Expire < 1 Thn</div>
                      <div class="stat-number"><?= $total_expire ?></div>
                      <div class="stat-desc">Perlu Reminder KPS</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Filter Jurusan -->
            <div class="mb-4">
              <h6 class="fw-bold mb-2">Pilih Jurusan (Filter)</h6>
              <p class="text-muted small mb-3">Klik jurusan untuk menyaring tabel</p>
              <div class="d-flex flex-wrap gap-2" id="filter-pills-nas">
                <button class="pill-filter active" onclick="setFilter('jurusan', 'Semua Jurusan')">Semua Jurusan</button>
                <button class="pill-filter" onclick="setFilter('jurusan', 'Teknik Elektro')">Teknik Elektro</button>
                <button class="pill-filter" onclick="setFilter('jurusan', 'Manajemen Bisnis')">Manajemen Bisnis</button>
                <button class="pill-filter" onclick="setFilter('jurusan', 'Informatika')">Informatika</button>
                <button class="pill-filter" onclick="setFilter('jurusan', 'Teknik Mesin')">Teknik Mesin</button>
              </div>
            </div>

            <!-- Tabel Data -->
            <div class="modern-card">
              <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                  <h6 class="fw-bold mb-1" id="judul-tabel">Daftar Prodi (Semua)</h6>
                  <p class="text-muted small mb-0">Menampilkan daftar program studi berdasarkan filter terpilih</p>
                </div>
                <input type="text" id="search-input" class="form-control form-control-sm" placeholder="Cari nama prodi..." style="width: 250px; border-radius: 20px;" onkeyup="searchTable()">
              </div>
              <div class="card-body p-0">
                <div class="table-responsive">
                  <table class="table table-custom table-hover" id="tabelNasional">
                    <thead>
                      <tr>
                        <th width="5%" class="text-center">No</th>
                        <th width="30%">Program Studi</th>
                        <th class="text-center">Peringkat</th>
                        <th class="text-center">Lembaga</th>
                        <th class="text-center">Masa Berlaku</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Aksi</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if(count($data_prodi) > 0): ?>
                        <?php $no = 1; foreach($data_prodi as $prodi): ?>
                          <?php 
                            // Hitung Status Expire
                            $tgl_berakhir = new DateTime($prodi['tanggal_berakhir']);
                            $tgl_sekarang = new DateTime($hari_ini);
                            $selisih = $tgl_sekarang->diff($tgl_berakhir)->days;
                            $is_expire = ($tgl_berakhir >= $tgl_sekarang && $selisih <= 365);
                            
                            // Tentukan kategori untuk filter JS (unggul, baik sekali, baik)
                            $peringkat_lower = strtolower($prodi['peringkat']);
                            $kategori = 'baik';
                            if (strpos($peringkat_lower, 'unggul') !== false) $kategori = 'unggul';
                            elseif (strpos($peringkat_lower, 'baik sekali') !== false) $kategori = 'baik sekali';
                          ?>
                          <tr class="prodi-row" 
                              data-jurusan="<?= htmlspecialchars($prodi['nama_jurusan']) ?>" 
                              data-kategori="<?= $kategori ?>"
                              data-expire="<?= $is_expire ? '1' : '0' ?>">
                            <td class="text-center"><?= $no++ ?></td>
                            <td>
                              <b><?= htmlspecialchars($prodi['nama_program_studi']) ?></b><br>
                              <small class="text-muted"><?= htmlspecialchars($prodi['nama_jurusan']) ?></small>
                            </td>
                            <td class="text-center"><?= htmlspecialchars($prodi['peringkat']) ?></td>
                            <td class="text-center"><?= htmlspecialchars($prodi['nama_lembaga']) ?></td>
                            <td class="text-center"><?= date('d M Y', strtotime($prodi['tanggal_mulai'])) ?> - <?= date('d M Y', strtotime($prodi['tanggal_berakhir'])) ?></td>
                            <td class="text-center">
                              <?php if($is_expire): ?>
                                <span class="badge-expire">Masa Expire < 1 Thn</span>
                              <?php else: ?>
                                <span class="badge-aktif">Aktif</span>
                              <?php endif; ?>
                            </td>
                            <td class="text-center">
                              <?php if(!empty($prodi['sertifikat'])): ?>
                                <a href="admin/<?= htmlspecialchars($prodi['sertifikat']) ?>" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill">
                                  <i class="bi bi-file-earmark-pdf"></i> Lihat
                                </a>
                              <?php else: ?>
                                <span class="text-muted small">Tidak ada</span>
                              <?php endif; ?>
                            </td>
                          </tr>
                        <?php endforeach; ?>
                      <?php else: ?>
                        <tr>
                          <td colspan="7" class="text-center py-5">
                            <div class="text-muted mb-2"><i class="bi bi-folder-x fs-1"></i></div>
                            <h6 class="fw-bold text-dark">Belum Ada Data</h6>
                            <p class="small text-muted mb-0">Data akreditasi program studi belum diisi oleh admin.</p>
                          </td>
                        </tr>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>

          </div>

          <!-- ================= TAB INTERNASIONAL ================= -->
          <div class="tab-pane fade" id="internasional" role="tabpanel">
            <div class="modern-card p-5 text-center">
              <div class="text-muted mb-2"><i class="bi bi-globe fs-1"></i></div>
              <h6 class="fw-bold text-dark">Belum Ada Data Internasional</h6>
              <p class="small text-muted mb-0">Data akreditasi program studi internasional belum diisi oleh admin.</p>
            </div>
          </div>
          
        </div>

      </div>
    </section>

  </main>

  <!-- Footer -->
  <footer id="footer" class="footer">
    <div class="container footer-top">
      <div class="row gy-4">
        <div class="col-lg-4 col-md-6 footer-about">
          <a href="index.php" class="logo d-flex align-items-center">
            <span class="sitename">P4M Polibatam</span>
          </a>
          <div class="footer-contact pt-3">
            <p>Lantai 3 Gedung Utama jalan Ahmad Yani, Tlk. Tering Kec. Batam Kota 29461</p>
            <p class="mt-3"><strong>Nomor:</strong> <span>+62 778 469856 469860 ext. 1036</span></p>
            <p><strong>Email:</strong> <span>p4m@polibatam.ac.id</span></p>
          </div>
          <div class="social-links d-flex mt-4">
            <a href="https://www.youtube.com/@penjaminanmutupoliteknikne6754"><i class="bi bi-youtube"></i></a>
            <a href="https://www.polibatam.ac.id/"><i class="bi bi-globe"></i></a> 
          </div>
        </div>

        <div class="col-lg-2 col-md-3 footer-links">
          <h4>Tautan</h4>
          <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="index.php#features">Aplikasi</a></li>
            <li><a href="hasil_kepuasan.php">Layanan</a></li>
            <li><a href="akre_perguruan.php">Akreditasi</a></li>
            <li><a href="buku_isbn.php">Program Fasilitasi P4M</a></li>
            <li><a href="berita.php">Berita</a></li>
          </ul>
        </div>

        <div class="col-lg-2 col-md-3 footer-links">
          <h4>Layanan</h4>
          <ul>
            <li><a href="hasil_kepuasan.php">Hasil Kepuasan</a></li>
            <li><a href="standar_layanan.php">Standar Layanan Polibatam</a></li>
          </ul>
        </div>

        <div class="col-lg-2 col-md-3 footer-links">
          <h4>Akreditasi</h4>
          <ul>
            <li><a href="akre_perguruan.php">Perguruan Tinggi</a></li>
            <li><a href="akre_unit.php">Unit Kerja</a></li>
            <li><a href="akre_prodi.php">Program Studi</a></li>
          </ul>
        </div>

        <div class="col-lg-2 col-md-3 footer-links">
          <h4>Program Fasilitasi P4M</h4>
          <ul>
            <li><a href="buku_isbn.php">Buku ISBN</a></li>
            <li><a href="video_digital.php">Video Digital</a></li>
          </ul>
        </div>

      </div>
    </div>

    <div class="container copyright text-center mt-4">
      <p><strong class="px-1 sitename">Made By Pusat Penjaminan Mutu dan Pengembangan Pembelajaran</strong></p>
    </div>
  </footer>

  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/aos/aos.js"></script>
  <script src="assets/js/main.js"></script>

  <script>
    document.addEventListener("DOMContentLoaded", function() {
      
      // 1. TAMPILAN DONUT CHART
      const ctx = document.getElementById('donutChartNasional').getContext('2d');
      new Chart(ctx, {
        type: 'doughnut',
        data: {
          labels: ['Unggul', 'Baik Sekali', 'Baik'],
          datasets: [{
            data: [<?= $total_unggul ?>, <?= $total_baik_sekali ?>, <?= $total_baik ?>],
            backgroundColor: ['#0d83fd', '#7fbcff', '#adb5bd'],
            borderWidth: 2,
            borderColor: '#fff'
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          cutout: '75%',
          plugins: { legend: { display: false } }
        }
      });

      // Variabel untuk menyimpan status filter saat ini
      let currentJurusan = "Semua Jurusan";
      let currentKategori = "semua";
      let currentExpire = "0";

      // 2. FUNGSI UTAMA UNTUK MENYARING TABEL (Bisa dipanggil dari kotak atau tombol)
      window.setFilter = function(type, value) {
        if (type === 'jurusan') {
            currentJurusan = value;
            // Ubah warna tombol jurusan
            document.querySelectorAll('#filter-pills-nas .pill-filter').forEach(btn => btn.classList.remove('active'));
            // Cari tombol yang diklik berdasarkan teksnya
            Array.from(document.querySelectorAll('#filter-pills-nas .pill-filter')).find(el => el.innerText.includes(value) || value === 'Semua Jurusan' && el.innerText === 'Semua Jurusan').classList.add('active');
            
            // Reset filter kategori jika yang diklik adalah jurusan
            currentKategori = "semua";
            currentExpire = "0";
        } 
        else if (type === 'kategori') {
            currentKategori = value;
            // Reset filter jurusan jika yang diklik adalah kategori
            currentJurusan = "Semua Jurusan";
            document.querySelectorAll('#filter-pills-nas .pill-filter').forEach(btn => btn.classList.remove('active'));
            document.querySelector('#filter-pills-nas .pill-filter').classList.add('active'); // Set ke "Semua Jurusan"
        }
        else if (type === 'expire') {
            currentExpire = value;
            // Reset filter lainnya
            currentKategori = "semua";
            currentJurusan = "Semua Jurusan";
            document.querySelectorAll('#filter-pills-nas .pill-filter').forEach(btn => btn.classList.remove('active'));
            document.querySelector('#filter-pills-nas .pill-filter').classList.add('active'); // Set ke "Semua Jurusan"
        }

        applyFilter(); // Jalankan penyaringan
      };

      // 3. FUNGSI UNTUK MENERAPKAN FILTER KE TABEL
      function applyFilter() {
        let judul = "Daftar Prodi ";
        if (currentJurusan !== "Semua Jurusan") judul += `(${currentJurusan})`;
        else if (currentKategori !== "semua") judul += `(Peringkat: ${currentKategori.toUpperCase()})`;
        else if (currentExpire === "1") judul += `(Akan Expire < 1 Tahun)`;
        else judul += "(Semua)";
        
        document.getElementById('judul-tabel').innerText = judul;

        const rows = document.querySelectorAll('.prodi-row');
        rows.forEach(row => {
            let matchJurusan = (currentJurusan === "Semua Jurusan" || row.dataset.jurusan.toLowerCase().includes(currentJurusan.toLowerCase()));
            let matchKategori = (currentKategori === "semua" || row.dataset.kategori === currentKategori);
            let matchExpire = (currentExpire === "0" || row.dataset.expire === "1");

            if (matchJurusan && matchKategori && matchExpire) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
      }

      // 4. FITUR PENCARIAN (SEARCH)
      window.searchTable = function() {
        const input = document.getElementById("search-input").value.toLowerCase();
        const rows = document.querySelectorAll('.prodi-row');
        
        rows.forEach(row => {
          const text = row.innerText.toLowerCase();
          if(text.includes(input)) {
            row.style.display = ''; 
          } else {
            row.style.display = 'none'; 
          }
        });
      };
      
    });
  </script>
</body>
</html>