<?php
include "koneksi.php"; 

// QUERY DIPERBARUI: Mengambil semua data prodi
$query = "
    SELECT a1.*, ps.nama_program_studi, ps.nama_jurusan, l.nama_lembaga, ja.jenis_akreditasi
    FROM akreditasi a1
    INNER JOIN program_studi ps ON a1.id_prodi = ps.id_prodi
    LEFT JOIN lembaga l ON a1.id_lembaga = l.id_lembaga
    LEFT JOIN jenis_akreditasi ja ON a1.id_jenis = ja.id_jenis
    WHERE a1.akreditasi = 'program studi'
    ORDER BY ps.nama_jurusan ASC, a1.tanggal_berakhir DESC
";
$result = mysqli_query($koneksi, $query);

$data_nasional = [];
$data_internasional = [];
$hari_ini = date('Y-m-d');

// Variabel penampung untuk Tab Nasional
$total_semua_nas = 0; $total_unggul_nas = 0; $total_baik_sekali_nas = 0; $total_baik_nas = 0; $total_expire_nas = 0; $total_terakreditasi_nas = 0;

// Variabel penampung untuk Tab Internasional
$total_semua_int = 0; $total_unggul_int = 0; $total_baik_sekali_int = 0; $total_baik_int = 0; $total_expire_int = 0; $total_terakreditasi_int = 0;

while ($row = mysqli_fetch_assoc($result)) {
    // MENGGUNAKAN jenis_akreditasi SEBAGAI PEMISAH
    $tipe = isset($row['jenis_akreditasi']) ? $row['jenis_akreditasi'] : 'Nasional';
    
    if (strtolower($tipe) == 'internasional') {
        $data_internasional[] = $row;
        $total_semua_int++;
        
        $predikat = strtolower($row['peringkat']);
        if (strpos($predikat, 'unggul') !== false) $total_unggul_int++;
        elseif (strpos($predikat, 'baik sekali') !== false) $total_baik_sekali_int++;
        elseif (strpos($predikat, 'baik') !== false) $total_baik_int++;
        else $total_terakreditasi_int++;

        $tgl_berakhir = new DateTime($row['tanggal_berakhir']);
        $tgl_sekarang = new DateTime($hari_ini);
        $selisih = $tgl_sekarang->diff($tgl_berakhir)->days;
        if ($tgl_berakhir >= $tgl_sekarang && $selisih <= 365) $total_expire_int++;
    } else {
        $data_nasional[] = $row;
        $total_semua_nas++;
        
        $predikat = strtolower($row['peringkat']);
        if (strpos($predikat, 'unggul') !== false) $total_unggul_nas++;
        elseif (strpos($predikat, 'baik sekali') !== false) $total_baik_sekali_nas++;
        elseif (strpos($predikat, 'baik') !== false) $total_baik_nas++;
        else $total_terakreditasi_nas++;

        $tgl_berakhir = new DateTime($row['tanggal_berakhir']);
        $tgl_sekarang = new DateTime($hari_ini);
        $selisih = $tgl_sekarang->diff($tgl_berakhir)->days;
        if ($tgl_berakhir >= $tgl_sekarang && $selisih <= 365) $total_expire_nas++;
    }
}

// Persentase Nasional
$pct_unggul_nas = ($total_semua_nas > 0) ? round(($total_unggul_nas / $total_semua_nas) * 100) : 0;
$pct_baik_sekali_nas = ($total_semua_nas > 0) ? round(($total_baik_sekali_nas / $total_semua_nas) * 100) : 0;
$pct_baik_nas = ($total_semua_nas > 0) ? round(($total_baik_nas / $total_semua_nas) * 100) : 0;
$pct_terakreditasi_nas = ($total_semua_nas > 0) ? round(($total_terakreditasi_nas / $total_semua_nas) * 100) : 0;

// Persentase Internasional
$pct_unggul_int = ($total_semua_int > 0) ? round(($total_unggul_int / $total_semua_int) * 100) : 0;
$pct_baik_sekali_int = ($total_semua_int > 0) ? round(($total_baik_sekali_int / $total_semua_int) * 100) : 0;
$pct_baik_int = ($total_semua_int > 0) ? round(($total_baik_int / $total_semua_int) * 100) : 0;
$pct_terakreditasi_int = ($total_semua_int > 0) ? round(($total_terakreditasi_int / $total_semua_int) * 100) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Akreditasi Program Studi - P4M Polibatam</title>
  <link href="assets/img/logo/polibatam.svg" rel="icon">
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Inter:wght@100;200;300;400;500;600;700;800;900&family=Nunito:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/aos/aos.css" rel="stylesheet">
  <link href="assets/css/main.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/exceljs/4.3.0/exceljs.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>

  <style>
    .nav-tabs-custom { border-bottom: 2px solid #e9ecef; margin-bottom: 30px; }
    .nav-tabs-custom .nav-link { border: none; color: #6c757d; font-weight: 600; padding: 12px 20px; position: relative; background: transparent; transition: 0.3s; }
    .nav-tabs-custom .nav-link:hover { color: #0d83fd; }
    .nav-tabs-custom .nav-link.active { color: #0d83fd; background: transparent; }
    .nav-tabs-custom .nav-link.active::after { content: ''; position: absolute; bottom: -2px; left: 0; width: 100%; height: 3px; background-color: #0d83fd; }
    .modern-card { background: #fff; border-radius: 12px; border: 1px solid #e9ecef; box-shadow: 0 4px 15px rgba(0,0,0,0.03); height: 100%; transition: 0.3s; }
    .modern-card .card-header { background: transparent; border-bottom: 1px solid #e9ecef; padding: 20px; }
    .stat-card { background: #fff; border-radius: 12px; border: 1px solid #e9ecef; padding: 15px; transition: 0.3s; cursor: pointer; height: 100%; display: flex; flex-direction: column; justify-content: center; }
    .stat-card:hover { box-shadow: 0 8px 15px rgba(0,0,0,0.08); transform: translateY(-3px); border-color: #0d83fd; }
    .stat-card .stat-label { font-size: 13px; color: #6c757d; display: flex; align-items: center; gap: 5px; margin-bottom: 8px; white-space: nowrap; }
    .stat-card .stat-number { font-size: 24px; font-weight: 700; color: #1e2b4c; line-height: 1; margin-bottom: 4px; }
    .stat-card .stat-desc { font-size: 12px; color: #adb5bd; }
    .dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; }
    .card-warning { background-color: #fff9e6; border-color: #ffeeba; }
    .card-warning .stat-number, .card-warning .stat-label, .card-warning .stat-desc { color: #856404; }
    .pill-filter { display: inline-block; padding: 8px 16px; border-radius: 50px; background: #fff; border: 1px solid #dee2e6; color: #495057; font-size: 14px; font-weight: 500; cursor: pointer; transition: 0.2s; }
    .pill-filter:hover { border-color: #0d83fd; color: #0d83fd; }
    .pill-filter.active { background: #0d83fd; border-color: #0d83fd; color: #fff; }
    .table-custom { vertical-align: middle; margin-bottom: 0; }
    .table-custom thead th { background-color: #f8f9fa; color: #1e2b4c; font-weight: 600; border-bottom: 2px solid #dee2e6; padding: 15px; white-space: nowrap; }
    .table-custom tbody td { padding: 15px; border-bottom: 1px solid #e9ecef; color: #495057; }
    .table-custom tbody tr { transition: 0.2s; }
    .table-custom tbody tr:hover { background-color: #f8fbff; }
    .badge-expire { background-color: #fff3cd; color: #856404; font-weight: 600; padding: 6px 12px; border-radius: 6px; font-size: 13px; }
    .badge-aktif { background-color: #d1e7dd; color: #0f5132; font-weight: 600; padding: 6px 12px; border-radius: 6px; font-size: 13px; }
  </style>
</head>
<body class="service-details-page">

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
            <button class="nav-link" id="internasional-tab" data-bs-toggle="tab" data-bs-target="#internasional" type="button" role="tab">Internasional</button>
          </li>
        </ul>

        <div class="tab-content" id="myTabContent">
          
          <!-- ================= TAB NASIONAL ================= -->
          <div class="tab-pane fade show active" id="nasional" role="tabpanel">
            
            <div class="row g-3 mb-4 align-items-center">
              <div class="col-lg-2">
                <div class="position-relative mx-auto" style="width: 150px; height: 150px;">
                  <canvas id="donutChartNasional"></canvas>
                  <div class="position-absolute top-50 start-50 translate-middle text-center" style="pointer-events: none;">
                    <h3 class="mb-0 fw-bold" style="color: #1e2b4c;"><?= $total_semua_nas ?></h3>
                    <small class="text-muted">prodi</small>
                  </div>
                </div>
              </div>

              <div class="col-lg-10">
                <div class="d-flex justify-content-end mb-2">
                  <button onclick="exportExcel('nasional')" class="btn btn-success btn-sm rounded-pill px-3 shadow-sm">
                    <i class="bi bi-file-earmark-excel-fill me-1"></i> Export Excel
                  </button>
                </div>

                <div class="row g-2">
                  <div class="col">
                    <div class="stat-card" onclick="setFilter('nasional', 'kategori', 'unggul')">
                      <div class="stat-label"><span class="dot" style="background:#0d83fd;"></span> Unggul</div>
                      <div class="stat-number"><?= $total_unggul_nas ?></div>
                      <div class="stat-desc"><?= $pct_unggul_nas ?>%</div>
                    </div>
                  </div>
                  <div class="col">
                    <div class="stat-card" onclick="setFilter('nasional', 'kategori', 'baik sekali')">
                      <div class="stat-label"><span class="dot" style="background:#7fbcff;"></span> Baik Sekali</div>
                      <div class="stat-number"><?= $total_baik_sekali_nas ?></div>
                      <div class="stat-desc"><?= $pct_baik_sekali_nas ?>%</div>
                    </div>
                  </div>
                  <div class="col">
                    <div class="stat-card" onclick="setFilter('nasional', 'kategori', 'baik')">
                      <div class="stat-label"><span class="dot" style="background:#adb5bd;"></span> Baik</div>
                      <div class="stat-number"><?= $total_baik_nas ?></div>
                      <div class="stat-desc"><?= $pct_baik_nas ?>%</div>
                    </div>
                  </div>
                  <div class="col">
                    <div class="stat-card" onclick="setFilter('nasional', 'kategori', 'terakreditasi')">
                      <div class="stat-label"><span class="dot" style="background:#6f42c1;"></span> Terakreditasi</div>
                      <div class="stat-number"><?= $total_terakreditasi_nas ?></div>
                      <div class="stat-desc"><?= $pct_terakreditasi_nas ?>%</div>
                    </div>
                  </div>
                  <div class="col">
                    <div class="stat-card card-warning" onclick="setFilter('nasional', 'expire', '1')">
                      <div class="stat-label">Expire < 1 Thn</div>
                      <div class="stat-number"><?= $total_expire_nas ?></div>
                      <div class="stat-desc">Reminder KPS</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="mb-4">
              <h6 class="fw-bold mb-2">Pilih Jurusan (Filter)</h6>
              <p class="text-muted small mb-3">Klik jurusan untuk menyaring tabel</p>
              <div class="d-flex flex-wrap gap-2" id="filter-pills-nas">
                <button class="pill-filter active" onclick="setFilter('nasional', 'jurusan', 'Semua Jurusan')">Semua Jurusan</button>
                <button class="pill-filter" onclick="setFilter('nasional', 'jurusan', 'Teknik Elektro')">Teknik Elektro</button>
                <button class="pill-filter" onclick="setFilter('nasional', 'jurusan', 'Manajemen Bisnis')">Manajemen Bisnis</button>
                <button class="pill-filter" onclick="setFilter('nasional', 'jurusan', 'Informatika')">Informatika</button>
                <button class="pill-filter" onclick="setFilter('nasional', 'jurusan', 'Teknik Mesin')">Teknik Mesin</button>
              </div>
            </div>

            <div class="modern-card">
              <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                  <h6 class="fw-bold mb-1" id="judul-tabel-nas">Daftar Prodi (Nasional)</h6>
                  <p class="text-muted small mb-0">Menampilkan daftar program studi berdasarkan filter terpilih</p>
                </div>
                <input type="text" id="search-input-nas" class="form-control form-control-sm" placeholder="Cari nama prodi..." style="width: 250px; border-radius: 20px;" onkeyup="searchTable('nasional')">
              </div>
              <div class="card-body p-0">
                <div class="table-responsive">
                  <table class="table table-custom table-hover" id="tabelNasional">
                    <thead>
                      <tr>
                        <th width="5%" class="text-center">No</th>
                        <th width="25%">Program Studi</th>
                        <th class="text-center">Predikat</th>
                        <th class="text-center">Lembaga</th>
                        <th class="text-center">Tanggal Mulai</th>
                        <th class="text-center">Tanggal Berakhir</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Aksi</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if(count($data_nasional) > 0): ?>
                        <?php $no = 1; foreach($data_nasional as $prodi): ?>
                          <?php 
                            $tgl_berakhir = new DateTime($prodi['tanggal_berakhir']);
                            $tgl_sekarang = new DateTime($hari_ini);
                            $selisih = $tgl_sekarang->diff($tgl_berakhir)->days;
                            $is_expire = ($tgl_berakhir >= $tgl_sekarang && $selisih <= 365);
                            
                            $predikat_lower = strtolower($prodi['peringkat']);
                            $kategori = 'terakreditasi'; 
                            if (strpos($predikat_lower, 'unggul') !== false) $kategori = 'unggul';
                            elseif (strpos($predikat_lower, 'baik sekali') !== false) $kategori = 'baik sekali';
                            elseif (strpos($predikat_lower, 'baik') !== false) $kategori = 'baik';
                          ?>
                          <tr class="prodi-row-nas" data-jurusan="<?= htmlspecialchars($prodi['nama_jurusan']) ?>" data-kategori="<?= $kategori ?>" data-expire="<?= $is_expire ? '1' : '0' ?>">
                            <td class="text-center"><?= $no++ ?></td>
                            <td>
                              <b><?= htmlspecialchars($prodi['nama_program_studi']) ?></b><br>
                              <small class="text-muted"><?= htmlspecialchars($prodi['nama_jurusan']) ?></small>
                            </td>
                            <td class="text-center"><?= htmlspecialchars($prodi['peringkat']) ?></td>
                            <td class="text-center"><?= htmlspecialchars($prodi['nama_lembaga']) ?></td>
                            <td class="text-center text-muted"><?= date('d M Y', strtotime($prodi['tanggal_mulai'])) ?></td>
                            <td class="text-center text-muted"><?= date('d M Y', strtotime($prodi['tanggal_berakhir'])) ?></td>
                            <td class="text-center">
                              <?php if($is_expire): ?>
                                <span class="badge-expire">Masa Expire < 1 Thn</span>
                              <?php else: ?>
                                <span class="badge-aktif">Aktif</span>
                              <?php endif; ?>
                            </td>
                            <td class="text-center">
                              <a href="akre_prodi_detail.php?id=<?= $prodi['id_prodi'] ?>" class="btn btn-sm btn-outline-primary rounded-pill">
                                <i class="bi bi-eye"></i> Lihat Detail
                              </a>
                            </td>
                          </tr>
                        <?php endforeach; ?>
                      <?php else: ?>
                        <tr>
                          <td colspan="8" class="text-center py-5">
                            <div class="text-muted mb-2"><i class="bi bi-folder-x fs-1"></i></div>
                            <h6 class="fw-bold text-dark">Belum Ada Data</h6>
                            <p class="small text-muted mb-0">Data akreditasi program studi nasional belum diisi oleh admin.</p>
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
            
            <!-- STATISTIK INTERNASIONAL -->
            <div class="row g-3 mb-4 align-items-center">
              <div class="col-lg-2">
                <div class="position-relative mx-auto" style="width: 150px; height: 150px;">
                  <canvas id="donutChartInternasional"></canvas>
                  <div class="position-absolute top-50 start-50 translate-middle text-center" style="pointer-events: none;">
                    <h3 class="mb-0 fw-bold" style="color: #1e2b4c;"><?= $total_semua_int ?></h3>
                    <small class="text-muted">prodi</small>
                  </div>
                </div>
              </div>

              <div class="col-lg-10">
                <div class="d-flex justify-content-end mb-2">
                  <button onclick="exportExcel('internasional')" class="btn btn-success btn-sm rounded-pill px-3 shadow-sm">
                    <i class="bi bi-file-earmark-excel-fill me-1"></i> Export Excel
                  </button>
                </div>

                <div class="row g-2">
                  <div class="col">
                    <div class="stat-card" onclick="setFilter('internasional', 'kategori', 'unggul')">
                      <div class="stat-label"><span class="dot" style="background:#0d83fd;"></span> Unggul</div>
                      <div class="stat-number"><?= $total_unggul_int ?></div>
                      <div class="stat-desc"><?= $pct_unggul_int ?>%</div>
                    </div>
                  </div>
                  <div class="col">
                    <div class="stat-card" onclick="setFilter('internasional', 'kategori', 'baik sekali')">
                      <div class="stat-label"><span class="dot" style="background:#7fbcff;"></span> Baik Sekali</div>
                      <div class="stat-number"><?= $total_baik_sekali_int ?></div>
                      <div class="stat-desc"><?= $pct_baik_sekali_int ?>%</div>
                    </div>
                  </div>
                  <div class="col">
                    <div class="stat-card" onclick="setFilter('internasional', 'kategori', 'baik')">
                      <div class="stat-label"><span class="dot" style="background:#adb5bd;"></span> Baik</div>
                      <div class="stat-number"><?= $total_baik_int ?></div>
                      <div class="stat-desc"><?= $pct_baik_int ?>%</div>
                    </div>
                  </div>
                  <div class="col">
                    <div class="stat-card" onclick="setFilter('internasional', 'kategori', 'terakreditasi')">
                      <div class="stat-label"><span class="dot" style="background:#6f42c1;"></span> Terakreditasi</div>
                      <div class="stat-number"><?= $total_terakreditasi_int ?></div>
                      <div class="stat-desc"><?= $pct_terakreditasi_int ?>%</div>
                    </div>
                  </div>
                  <div class="col">
                    <div class="stat-card card-warning" onclick="setFilter('internasional', 'expire', '1')">
                      <div class="stat-label">Expire < 1 Thn</div>
                      <div class="stat-number"><?= $total_expire_int ?></div>
                      <div class="stat-desc">Reminder KPS</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- FILTER JURUSAN INTERNASIONAL -->
            <div class="mb-4">
              <h6 class="fw-bold mb-2">Pilih Jurusan (Filter)</h6>
              <p class="text-muted small mb-3">Klik jurusan untuk menyaring tabel</p>
              <div class="d-flex flex-wrap gap-2" id="filter-pills-int">
                <button class="pill-filter active" onclick="setFilter('internasional', 'jurusan', 'Semua Jurusan')">Semua Jurusan</button>
                <button class="pill-filter" onclick="setFilter('internasional', 'jurusan', 'Teknik Elektro')">Teknik Elektro</button>
                <button class="pill-filter" onclick="setFilter('internasional', 'jurusan', 'Manajemen Bisnis')">Manajemen Bisnis</button>
                <button class="pill-filter" onclick="setFilter('internasional', 'jurusan', 'Informatika')">Informatika</button>
                <button class="pill-filter" onclick="setFilter('internasional', 'jurusan', 'Teknik Mesin')">Teknik Mesin</button>
              </div>
            </div>

            <!-- TABEL INTERNASIONAL -->
            <div class="modern-card">
              <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                  <h6 class="fw-bold mb-1" id="judul-tabel-int">Daftar Prodi (Internasional)</h6>
                  <p class="text-muted small mb-0">Menampilkan daftar program studi internasional</p>
                </div>
                <input type="text" id="search-input-int" class="form-control form-control-sm" placeholder="Cari nama prodi..." style="width: 250px; border-radius: 20px;" onkeyup="searchTable('internasional')">
              </div>
              <div class="card-body p-0">
                <div class="table-responsive">
                  <table class="table table-custom table-hover" id="tabelInternasional">
                    <thead>
                      <tr>
                        <th width="5%" class="text-center">No</th>
                        <th width="25%">Program Studi</th>
                        <th class="text-center">Predikat</th>
                        <th class="text-center">Lembaga</th>
                        <th class="text-center">Tanggal Mulai</th>
                        <th class="text-center">Tanggal Berakhir</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Aksi</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if(count($data_internasional) > 0): ?>
                        <?php $no_int = 1; foreach($data_internasional as $prodi_int): ?>
                          <?php 
                            $tgl_berakhir = new DateTime($prodi_int['tanggal_berakhir']);
                            $tgl_sekarang = new DateTime($hari_ini);
                            $selisih = $tgl_sekarang->diff($tgl_berakhir)->days;
                            $is_expire = ($tgl_berakhir >= $tgl_sekarang && $selisih <= 365);
                            
                            $predikat_lower = strtolower($prodi_int['peringkat']);
                            $kategori = 'terakreditasi'; 
                            if (strpos($predikat_lower, 'unggul') !== false) $kategori = 'unggul';
                            elseif (strpos($predikat_lower, 'baik sekali') !== false) $kategori = 'baik sekali';
                            elseif (strpos($predikat_lower, 'baik') !== false) $kategori = 'baik';
                          ?>
                          <tr class="prodi-row-int" data-jurusan="<?= htmlspecialchars($prodi_int['nama_jurusan']) ?>" data-kategori="<?= $kategori ?>" data-expire="<?= $is_expire ? '1' : '0' ?>">
                            <td class="text-center"><?= $no_int++ ?></td>
                            <td>
                              <b><?= htmlspecialchars($prodi_int['nama_program_studi']) ?></b><br>
                              <small class="text-muted"><?= htmlspecialchars($prodi_int['nama_jurusan']) ?></small>
                            </td>
                            <td class="text-center"><?= htmlspecialchars($prodi_int['peringkat']) ?></td>
                            <td class="text-center"><?= htmlspecialchars($prodi_int['nama_lembaga']) ?></td>
                            <td class="text-center text-muted"><?= date('d M Y', strtotime($prodi_int['tanggal_mulai'])) ?></td>
                            <td class="text-center text-muted"><?= date('d M Y', strtotime($prodi_int['tanggal_berakhir'])) ?></td>
                            <td class="text-center">
                              <?php if($is_expire): ?>
                                <span class="badge-expire">Masa Expire < 1 Thn</span>
                              <?php else: ?>
                                <span class="badge-aktif">Aktif</span>
                              <?php endif; ?>
                            </td>
                            <td class="text-center">
                              <a href="akre_prodi_detail.php?id=<?= $prodi_int['id_prodi'] ?>" class="btn btn-sm btn-outline-primary rounded-pill">
                                <i class="bi bi-eye"></i> Lihat Detail
                              </a>
                            </td>
                          </tr>
                        <?php endforeach; ?>
                      <?php else: ?>
                        <tr>
                          <td colspan="8" class="text-center py-5">
                            <div class="text-muted mb-2"><i class="bi bi-globe fs-1"></i></div>
                            <h6 class="fw-bold text-dark">Belum Ada Data Internasional</h6>
                            <p class="small text-muted mb-0">Data akreditasi program studi internasional belum diisi oleh admin.</p>
                          </td>
                        </tr>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
          
        </div>

      </div>
    </section>

  </main>

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
      AOS.init({ duration: 600, once: true, easing: 'ease-out-cubic' });

      // Chart Nasional
      const ctxNas = document.getElementById('donutChartNasional').getContext('2d');
      new Chart(ctxNas, {
        type: 'doughnut',
        data: {
          labels: ['Unggul', 'Baik Sekali', 'Baik', 'Terakreditasi'],
          datasets: [{
            data: [<?= $total_unggul_nas ?>, <?= $total_baik_sekali_nas ?>, <?= $total_baik_nas ?>, <?= $total_terakreditasi_nas ?>],
            backgroundColor: ['#0d83fd', '#7fbcff', '#adb5bd', '#6f42c1'],
            borderWidth: 2, borderColor: '#fff'
          }]
        },
        options: { responsive: true, maintainAspectRatio: false, cutout: '75%', plugins: { legend: { display: false } } }
      });

      // Chart Internasional
      const ctxInt = document.getElementById('donutChartInternasional').getContext('2d');
      new Chart(ctxInt, {
        type: 'doughnut',
        data: {
          labels: ['Unggul', 'Baik Sekali', 'Baik', 'Terakreditasi'],
          datasets: [{
            data: [<?= $total_unggul_int ?>, <?= $total_baik_sekali_int ?>, <?= $total_baik_int ?>, <?= $total_terakreditasi_int ?>],
            backgroundColor: ['#0d83fd', '#7fbcff', '#adb5bd', '#6f42c1'],
            borderWidth: 2, borderColor: '#fff'
          }]
        },
        options: { responsive: true, maintainAspectRatio: false, cutout: '75%', plugins: { legend: { display: false } } }
      });

      // State Filter
      let filterState = {
        nasional: { jurusan: "Semua Jurusan", kategori: "semua", expire: "0" },
        internasional: { jurusan: "Semua Jurusan", kategori: "semua", expire: "0" }
      };

      window.setFilter = function(tab, type, value) {
        filterState[tab][type] = value;
        
        if (type === 'jurusan') {
            filterState[tab].kategori = "semua";
            filterState[tab].expire = "0";
            document.querySelectorAll(`#filter-pills-${tab === 'nasional' ? 'nas' : 'int'} .pill-filter`).forEach(btn => btn.classList.remove('active'));
            Array.from(document.querySelectorAll(`#filter-pills-${tab === 'nasional' ? 'nas' : 'int'} .pill-filter`)).find(el => el.innerText.includes(value) || value === 'Semua Jurusan' && el.innerText === 'Semua Jurusan').classList.add('active');
        } else if (type === 'kategori') {
            filterState[tab].jurusan = "Semua Jurusan";
            filterState[tab].expire = "0";
            document.querySelectorAll(`#filter-pills-${tab === 'nasional' ? 'nas' : 'int'} .pill-filter`).forEach(btn => btn.classList.remove('active'));
            document.querySelector(`#filter-pills-${tab === 'nasional' ? 'nas' : 'int'} .pill-filter`).classList.add('active');
        } else if (type === 'expire') {
            filterState[tab].kategori = "semua";
            filterState[tab].jurusan = "Semua Jurusan";
            document.querySelectorAll(`#filter-pills-${tab === 'nasional' ? 'nas' : 'int'} .pill-filter`).forEach(btn => btn.classList.remove('active'));
            document.querySelector(`#filter-pills-${tab === 'nasional' ? 'nas' : 'int'} .pill-filter`).classList.add('active');
        }
        applyFilter(tab);
      };

      function applyFilter(tab) {
        let judul = "Daftar Prodi ";
        if (filterState[tab].jurusan !== "Semua Jurusan") judul += `(${filterState[tab].jurusan})`;
        else if (filterState[tab].kategori !== "semua") judul += `(Predikat: ${filterState[tab].kategori.toUpperCase()})`;
        else if (filterState[tab].expire === "1") judul += `(Akan Expire < 1 Tahun)`;
        else judul += `(${tab.charAt(0).toUpperCase() + tab.slice(1)})`;
        
        document.getElementById(`judul-tabel-${tab === 'nasional' ? 'nas' : 'int'}`).innerText = judul;

        const rows = document.querySelectorAll(`.prodi-row-${tab === 'nasional' ? 'nas' : 'int'}`);
        rows.forEach(row => {
            let matchJurusan = (filterState[tab].jurusan === "Semua Jurusan" || row.dataset.jurusan.toLowerCase().includes(filterState[tab].jurusan.toLowerCase()));
            let matchKategori = (filterState[tab].kategori === "semua" || row.dataset.kategori === filterState[tab].kategori);
            let matchExpire = (filterState[tab].expire === "0" || row.dataset.expire === "1");
            if (matchJurusan && matchKategori && matchExpire) row.style.display = '';
            else row.style.display = 'none';
        });
      }

      window.searchTable = function(tab) {
        const input = document.getElementById(`search-input-${tab === 'nasional' ? 'nas' : 'int'}`).value.toLowerCase();
        const rows = document.querySelectorAll(`.prodi-row-${tab === 'nasional' ? 'nas' : 'int'}`);
        rows.forEach(row => {
          if(row.innerText.toLowerCase().includes(input)) row.style.display = ''; 
          else row.style.display = 'none'; 
        });
      };
    });

    // FUNGSI EXPORT EXCEL (Mendukung Nasional & Internasional)
    window.exportExcel = function(tab) {
      const tableId = tab === 'nasional' ? 'tabelNasional' : 'tabelInternasional';
      const title = tab === 'nasional' ? 'LAPORAN AKREDITASI PROGRAM STUDI NASIONAL' : 'LAPORAN AKREDITASI PROGRAM STUDI INTERNASIONAL';
      const fileName = tab === 'nasional' ? 'Laporan_Akreditasi_Prodi_Nasional.xlsx' : 'Laporan_Akreditasi_Prodi_Internasional.xlsx';

      const workbook = new ExcelJS.Workbook();
      const worksheet = workbook.addWorksheet('Data Akreditasi');

      worksheet.mergeCells('A1:D1');
      const titleCell = worksheet.getCell('A1');
      titleCell.value = title;
      titleCell.font = { size: 16, bold: true, color: { argb: 'FF0D83FD' } };
      titleCell.alignment = { vertical: 'middle', horizontal: 'center' };
      worksheet.getRow(1).height = 30;

      worksheet.mergeCells('A2:D2');
      const subTitleCell = worksheet.getCell('A2');
      subTitleCell.value = 'Politeknik Negeri Batam - Pusat Penjaminan Mutu dan Pengembangan Pembelajaran';
      subTitleCell.font = { size: 11, italic: true, color: { argb: 'FF6C757D' } };
      subTitleCell.alignment = { vertical: 'middle', horizontal: 'center' };
      worksheet.getRow(2).height = 20;

      const headerRow = worksheet.getRow(4);
      headerRow.values = ['Jurusan', 'Prodi', 'Status Akreditasi', 'Lembaga'];
      
      headerRow.eachCell((cell) => {
        cell.font = { bold: true, color: { argb: 'FFFFFFFF' } };
        cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF0D83FD' } };
        cell.alignment = { vertical: 'middle', horizontal: 'center' };
        cell.border = { top: { style: 'thin' }, left: { style: 'thin' }, bottom: { style: 'thin' }, right: { style: 'thin' } };
      });

      worksheet.columns = [
        { key: 'jurusan', width: 30 },
        { key: 'prodi', width: 45 },
        { key: 'status', width: 25 },
        { key: 'lembaga', width: 20 }
      ];

      var table = document.getElementById(tableId);
      var rows = table.querySelectorAll('tbody tr');
      let currentRowIndex = 5;
      
      rows.forEach(function(row) {
        if(row.style.display !== 'none' && !row.querySelector('td[colspan]')) { // Abaikan baris "Belum Ada Data"
          var cols = row.querySelectorAll('td');
          
          var jurusan = cols[1].querySelector('small').innerText.trim();
          var prodi = cols[1].querySelector('b').innerText.trim();
          var predikat = cols[2].innerText.trim();
          var lembaga = cols[3].innerText.trim();
          
          var is_expire = row.getAttribute('data-expire') === '1';
          var status = is_expire ? 'Masa Expire < 1 Thn' : 'Aktif';

          const dataRow = worksheet.getRow(currentRowIndex);
          dataRow.values = [jurusan, prodi, status, lembaga];
          
          dataRow.eachCell({ includeEmpty: true }, (cell, colNumber) => {
            cell.alignment = { vertical: 'middle', horizontal: 'center' };
            cell.border = { top: { style: 'thin', color: { argb: 'FFDEE2E6' } }, left: { style: 'thin', color: { argb: 'FFDEE2E6' } }, bottom: { style: 'thin', color: { argb: 'FFDEE2E6' } }, right: { style: 'thin', color: { argb: 'FFDEE2E6' } } };
            if (colNumber === 3 && is_expire) cell.font = { color: { argb: 'FF856404' }, bold: true };
          });
          dataRow.height = 25;
          currentRowIndex++;
        }
      });

      workbook.xlsx.writeBuffer().then(function(buffer) {
        saveAs(new Blob([buffer], { type: 'application/octet-stream' }), fileName);
      });
    };
  </script>
</body>
</html>