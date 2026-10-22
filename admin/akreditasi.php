<?php
include "../koneksi.php";
/** @var mysqli $koneksi */

// Proses Tambah Data
if (isset($_POST["submit"])) {
    $akreditasi_tipe = $_POST["akreditasi"];
    $id_unit = (isset($_POST["id_unit"]) && $_POST["id_unit"] !== "0") ? (int)$_POST["id_unit"] : 0;
    $nama_jurusan = (isset($_POST["nama_jurusan"]) && $_POST["nama_jurusan"] !== "0") ? $_POST["nama_jurusan"] : "";
    $id_prodi = (isset($_POST["id_prodi"]) && $_POST["id_prodi"] !== "0") ? (int)$_POST["id_prodi"] : 0;
    $peringkat = $_POST["peringkat"];
    $id_lembaga = (isset($_POST["id_lembaga"]) && $_POST["id_lembaga"] !== "0") ? (int)$_POST["id_lembaga"] : 0;
    $id_jenis = (isset($_POST["id_jenis"]) && $_POST["id_jenis"] !== "0") ? (int)$_POST["id_jenis"] : 0;
    $tanggal_mulai = $_POST["tanggal_mulai"];
    $tanggal_berakhir = $_POST["tanggal_berakhir"];

    if ($akreditasi_tipe == 'Unit Kerja') {
        $id_prodi = 0;
        $nama_jurusan = "";
    }

    $sertifikat = '';

    // Upload Sertifikat (PDF)
    if (!empty($_FILES['sertifikat']['name'])) {
        $uploadDir = 'uploads/sertifikat/';
        $fileName = time() . '-' . basename($_FILES['sertifikat']['name']);
        $filePath = $uploadDir . $fileName;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $fileType = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        if ($fileType == 'pdf') {
            if (move_uploaded_file($_FILES['sertifikat']['tmp_name'], $filePath)) {
                $sertifikat = $filePath;
            }
        }
    }

    $sql = "INSERT INTO akreditasi (akreditasi, id_unit, nama_jurusan, id_prodi, peringkat, id_lembaga, id_jenis, tanggal_mulai, tanggal_berakhir, sertifikat) 
            VALUES ('$akreditasi_tipe', '$id_unit', '$nama_jurusan', '$id_prodi', '$peringkat', '$id_lembaga', '$id_jenis', '$tanggal_mulai', '$tanggal_berakhir', '$sertifikat')";
    $result = mysqli_query($koneksi, $sql);
    if ($result) {
        echo "<script>alert('Data telah ditambahkan'); window.location='akreditasi.php'</script>";
    } else {
        echo "<script>alert('Data gagal ditambahkan!'); window.location='akreditasi.php'</script>";
    }
}

// Proses Edit Data
if (isset($_POST["update"])) {
    $id_akreditasi = $_POST["id_akreditasi"];
    $akreditasi_tipe = $_POST["akreditasi"];
    $id_unit = (isset($_POST["id_unit"]) && $_POST["id_unit"] !== "0") ? (int)$_POST["id_unit"] : 0;
    $nama_jurusan = (isset($_POST["nama_jurusan"]) && $_POST["nama_jurusan"] !== "0") ? $_POST["nama_jurusan"] : "";
    $id_prodi = (isset($_POST["id_prodi"]) && $_POST["id_prodi"] !== "0") ? (int)$_POST["id_prodi"] : 0;
    $peringkat = $_POST["peringkat"];
    $id_lembaga = (isset($_POST["id_lembaga"]) && $_POST["id_lembaga"] !== "0") ? (int)$_POST["id_lembaga"] : 0;
    $id_jenis = (isset($_POST["id_jenis"]) && $_POST["id_jenis"] !== "0") ? (int)$_POST["id_jenis"] : 0;
    $tanggal_mulai = $_POST["tanggal_mulai"];
    $tanggal_berakhir = $_POST["tanggal_berakhir"];

    if ($akreditasi_tipe == 'Unit Kerja') {
        $id_prodi = 0;
        $nama_jurusan = "";
    }

    $sertifikat_sql = "";

    // Upload Sertifikat Baru (PDF)
    if (!empty($_FILES['sertifikat']['name'])) {
        $uploadDir = 'uploads/sertifikat/';
        $fileName = time() . '-' . basename($_FILES['sertifikat']['name']);
        $filePath = $uploadDir . $fileName;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $fileType = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        if ($fileType == 'pdf') {
            if (move_uploaded_file($_FILES['sertifikat']['tmp_name'], $filePath)) {
                $sertifikat_sql = ", sertifikat='" . $filePath . "'";
            }
        }
    }

    $sql = "UPDATE akreditasi SET 
            akreditasi='$akreditasi_tipe', id_unit='$id_unit', nama_jurusan='$nama_jurusan', id_prodi='$id_prodi', 
            peringkat='$peringkat', id_lembaga='$id_lembaga', id_jenis='$id_jenis', 
            tanggal_mulai='$tanggal_mulai', tanggal_berakhir='$tanggal_berakhir' 
            $sertifikat_sql 
            WHERE id_akreditasi='$id_akreditasi'";

    $result = mysqli_query($koneksi, $sql);
    if ($result) {
        echo "<script>alert('Data telah diupdate'); window.location='akreditasi.php'</script>";
    } else {
        echo "<script>alert('Data gagal diupdate!'); window.location='akreditasi.php'</script>";
    }
}

// Proses Delete Data
if (isset($_POST["delete"])) {
    $id_akreditasi = mysqli_real_escape_string($koneksi, $_POST["id_akreditasi"]);
    $delete = mysqli_query($koneksi, "DELETE FROM akreditasi WHERE id_akreditasi='$id_akreditasi'");
    if ($delete) {
        echo "<script>alert('Data Berhasil Dihapus'); window.location='akreditasi.php';</script>";
    } else {
        echo "<script>alert('Data Gagal Dihapus: " . mysqli_error($koneksi) . "'); window.location='akreditasi.php';</script>";
    }
}

// Ambil data setelah proses CRUD
$akreditasi = query("SELECT
    akreditasi.id_akreditasi,
    akreditasi.akreditasi,
    akreditasi.id_unit,                
    akreditasi.id_prodi,               
    unit.nama_unit_kerja,
    akreditasi.id_lembaga,
    akreditasi.id_jenis,
    akreditasi.nama_jurusan,
    ps_prodi.nama_program_studi,
    akreditasi.peringkat,
    ps_lembaga.nama_lembaga,
    ps_jenis_akreditasi.jenis_akreditasi,
    akreditasi.tanggal_mulai,
    akreditasi.tanggal_berakhir,
    akreditasi.sertifikat
FROM akreditasi
LEFT JOIN unit ON unit.id_unit = akreditasi.id_unit
LEFT JOIN program_studi AS ps_prodi ON ps_prodi.id_prodi = akreditasi.id_prodi
LEFT JOIN lembaga AS ps_lembaga ON ps_lembaga.id_lembaga = akreditasi.id_lembaga
LEFT JOIN jenis_akreditasi AS ps_jenis_akreditasi ON ps_jenis_akreditasi.id_jenis = akreditasi.id_jenis");

$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page > 1) ? ($page * $limit) - $limit : 0;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

if ($search !== '') {
    $akreditasi = array_filter($akreditasi, function ($item) use ($search) {
        return stripos((string)$item['nama_unit_kerja'], $search) !== false
            || stripos((string)$item['nama_jurusan'], $search) !== false
            || stripos((string)$item['nama_program_studi'], $search) !== false
            || stripos((string)$item['jenis_akreditasi'], $search) !== false;
    });
}

$totalData = count($akreditasi);
$totalPages = ceil($totalData / $limit);
$akreditasi = array_slice($akreditasi, $start, $limit);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>P4M Polibatam</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">

    <link href="img/favicon.ico" rel="icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="lib/tempusdominus/css/tempusdominus-bootstrap-4.min.css" rel="stylesheet" />
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">

    <style>
    .logo-image { height: 40px; width: auto; }
    .table-custom { background-color: white; color: #333; width: 100%; }
    .table-custom thead th { background-color: #0d83fd; color: white; text-align: center; }
    .table-custom td, .table-custom th { text-align: center; vertical-align: middle; }
    .table-custom .btn-icon { padding: 0; margin: 0; border: none; background: transparent; font-size: 16px; }
    .table-custom .btn-icon i { font-size: 18px; margin: 0; }
    .table-custom .btn-warning i { color: #ffc107; }
    .table-custom .btn-danger i { color: #dc3545; }
    .table-custom .btn-icon:hover { color: #0d83fd; }

    .pagination-custom { display: flex; justify-content: center; align-items: center; list-style: none; padding: 0; margin: 15px 0; gap: 5px; }
    .pagination-custom .page-item { display: flex; align-items: center; justify-content: center; transition: all 0.2s ease-in-out; }
    .pagination-custom .page-link { display: flex; align-items: center; justify-content: center; padding: 6px 10px; font-size: 12px; font-weight: 500; color: #0d83fd; background-color: white; border: 1.5px solid #0d83fd; border-radius: 8px; box-shadow: 1px 1px 5px rgba(0, 0, 0, 0.08); transition: all 0.2s ease-in-out; }
    .pagination-custom .page-item.active .page-link { background-color: #0d83fd; color: white; border-color: #0d83fd; box-shadow: 1px 1px 10px rgba(13, 131, 253, 0.2); }
    .pagination-custom .page-link:hover { background-color: rgba(13, 131, 253, 0.1); color: #0d83fd; transform: scale(1.05); }
    .pagination-custom .page-item.disabled .page-link { color: #aaa; border-color: #ddd; background-color: #f8f9fa; cursor: not-allowed; }
    .pagination-custom .arrow { font-size: 14px; padding: 6px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }

    .search-form-wrapper { display: flex; align-items: center; gap: 10px; margin-bottom: 20px; }
    .search-form-wrapper .input-group { width: 300px; }
    .search-form-wrapper .form-control { border-radius: 20px 0 0 20px; }
    .search-form-wrapper .btn-cari { border-radius: 0 20px 20px 0; background-color: #0d83fd; color: white; }
    .showing-info { font-size: 14px; color: #0d83fd; text-align: center; margin-bottom: 10px; }
    .btn-tambah { background-color: #0d83fd; color: white; border-radius: 20px; padding: 5px 15px; border: none; }
    .hidden { display: none; }
    </style>

<script>
    function showQuestions(value, prefix = '') {
        document.getElementById(prefix + 'id_unit').classList.add('hidden');
        document.getElementById(prefix + 'nama_jurusan').classList.add('hidden');
        document.getElementById(prefix + 'id_prodi').classList.add('hidden');
        if (value == 'Unit Kerja') {
            document.getElementById(prefix + 'id_unit').classList.remove('hidden');
        }
        if (value == 'Program Studi') {
            document.getElementById(prefix + 'nama_jurusan').classList.remove('hidden');
            document.getElementById(prefix + 'id_prodi').classList.remove('hidden');
        }
    }

    function filterProdi(jurusan, prefix = '') {
        var select = document.getElementById(prefix + 'select_prodi');
        for (var i = 0; i < select.options.length; i++) {
            var opt = select.options[i];
            if (opt.value === '0') continue;
            var cocok = opt.getAttribute('data-jurusan') === jurusan;
            opt.hidden = !cocok;
            opt.disabled = !cocok;
        }
        select.value = '0';
    }

    function editData(data) {
        document.getElementById('edit_id_akreditasi').value = data.id_akreditasi;
        document.getElementById('edit_tanggal_mulai').value = data.tanggal_mulai;
        document.getElementById('edit_tanggal_berakhir').value = data.tanggal_berakhir;
        document.getElementById('edit_peringkat').value = data.peringkat;
        document.getElementById('edit_id_lembaga').value = data.id_lembaga;
        document.getElementById('edit_id_jenis').value = data.id_jenis;

        if(data.akreditasi === 'Unit Kerja') {
            document.getElementById('edit_radio_unit').checked = true;
            showQuestions('Unit Kerja', 'edit_');
            document.getElementById('edit_id_unit').value = data.id_unit;
        } else {
            document.getElementById('edit_radio_prodi').checked = true;
            showQuestions('Program Studi', 'edit_');
            var selJurusan = document.getElementById('edit_nama_jurusan');
            for(var i=0; i<selJurusan.options.length; i++){
                if(selJurusan.options[i].value === data.nama_jurusan) selJurusan.selectedIndex = i;
            }
            filterProdi(data.nama_jurusan, 'edit_');
            var selProdi = document.getElementById('edit_select_prodi');
            for(var i=0; i<selProdi.options.length; i++){
                if(selProdi.options[i].value === data.id_prodi.toString()) selProdi.selectedIndex = i;
            }
        }
    }
</script>

</head>

<body>
    <div class="container-xxl position-relative bg-white d-flex p-0">

        <!-- Sidebar -->
        <div class="sidebar pe-4 pb-3 bg-light">
            <nav class="navbar bg-light navbar-light">
            <a href="#" class="navbar-brand mx-5 mb-3">
                <span style="font-size: 24px; font-weight: bold; color: #4AC4F3;">
                    <img src="../assets/img/logo/polibatam.svg" alt="" class="logo-image" /> Polibatam
                </span>
            </a>
                <div class="navbar-nav w-100">
                    <a href="dashboard.php" class="nav-item nav-link"><i class="fa fa-home me-2"></i>Dashboard</a>
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown"><i class="fa fa-cogs me-2"></i>Kelola Layanan</a>
                        <div class="dropdown-menu bg-transparent border-0">
                            <a href="hasil_kepuasan_jurusan.php" class="dropdown-item">Hasil Kepuasan Jurusan</a>
                            <a href="hasil_kepuasan_unit.php" class="dropdown-item">Hasil Kepuasan Unit Kerja</a>
                            <a href="layanan.php" class="dropdown-item">Menu Layanan</a>
                            <a href="isi_layanan.php" class="dropdown-item">Isi Layanan</a>
                        </div>
                    </div>
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown"><i class="fa fa-user me-2"></i>Kelola Data</a>
                        <div class="dropdown-menu bg-transparent border-0">
                            <a href="unit.php" class="dropdown-item">Unit Kerja</a>
                            <a href="prodi.php" class="dropdown-item">Program Studi</a>
                            <a href="lembaga.php" class="dropdown-item">Lembaga</a>
                            <a href="jenis_akre.php" class="dropdown-item">Jenis Akreditasi</a>
                        </div>
                    </div>
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle active" data-bs-toggle="dropdown"><i class="fa fa-graduation-cap me-2"></i>Kelola Akreditasi</a>
                        <div class="dropdown-menu bg-transparent border-0">
                            <a href="akreditasi.php" class="dropdown-item active">Akreditasi</a>
                            <a href="akre_perguruan.php" class="dropdown-item">Perguruan Tinggi</a>
                            <a href="akre_unit.php" class="dropdown-item">Unit Kerja</a>
                            <a href="akre_prodi.php" class="dropdown-item">Program Studi</a>
                        </div>
                    </div>
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown"><i class="fa fa-concierge-bell me-2"></i>Kelola Program</a>
                        <div class="dropdown-menu bg-transparent border-0">
                            <a href="panduan.php" class="dropdown-item">Panduan Program</a>
                            <a href="buku_isbn.php" class="dropdown-item">Buku ISBN</a>
                            <a href="video_digital.php" class="dropdown-item">Video Digital</a>
                        </div>
                    </div>
                    <a href="berita.php" class="nav-item nav-link"><i class="fa fa-clipboard-list me-2"></i>Kelola Berita</a>
                </div>
            </nav>
        </div>
        <!-- Sidebar -->

        <!-- Content -->
        <div class="content">
            <!-- Navbar -->
            <nav class="navbar navbar-expand bg-light navbar-light sticky-top px-4 py-2 shadow-sm">
                <a href="#" class="sidebar-toggler flex-shrink-0"><i class="fa fa-bars"></i></a>
                <div class="navbar-nav align-items-center ms-auto">
                <a href="../index.php" class="btn btn-link text-dark" style="position: absolute; right: 20px;"><i class="fa fa-sign-out-alt"></i></a>
                </div>
            </nav>
            <!-- Navbar -->

            <!-- Main Content -->
            <div class="container-fluid pt-4 px-4">
                <h5 class="mb-4 text-primary">Kelola Akreditasi Dan Sertifikasi</h5>
                <div class="row g-4">
                    <div class="col-12">
                        <div class="bg-light rounded h-100 p-4">
                            <!-- search bar -->
                            <div class="search-form-wrapper">
                                <a class="button btn-tambah" href="#" data-bs-toggle="modal" data-bs-target="#modalTambah">Tambah Data</a>
                                <form action="akreditasi.php" method="get" class="m-0">
                                    <div class="input-group">
                                        <input type="text" name="search" class="form-control" placeholder="Cari unit, jurusan, atau prodi..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                                        <button type="submit" class="btn btn-cari"><i class="fas fa-search"></i></button>
                                    </div>
                                </form>
                            </div>

                            <!-- Table -->
                            <div class="table-responsive">
                                <table class="table table-hover table-custom">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Unit Kerja</th>
                                            <th>Jurusan</th>
                                            <th>Program Studi</th>
                                            <th>Predikat</th>
                                            <th>Lembaga</th>
                                            <th>Jenis Akreditasi</th>
                                            <th>Tanggal Mulai</th>
                                            <th>Tanggal Berakhir</th>
                                            <th>Sertifikat</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($akreditasi)): ?>
                                            <?php $id = 1; foreach ($akreditasi as $row): ?>
                                                <tr>
                                                    <td><?= $id++ ?></td>
                                                    <td><?= !empty($row['nama_unit_kerja']) ? $row['nama_unit_kerja'] : "-" ?></td>
                                                    <td><?= !empty($row['nama_jurusan']) ? $row['nama_jurusan'] : "-" ?></td>
                                                    <td><?= !empty($row['nama_program_studi']) ? $row['nama_program_studi'] : "-" ?></td>
                                                    <td><?= $row["peringkat"] ?></td>
                                                    <td><?= strtoupper($row["nama_lembaga"]) ?></td>
                                                    <td><?= $row["jenis_akreditasi"] ?></td>
                                                    <td><?= $row["tanggal_mulai"] ?></td>
                                                    <td><?= $row["tanggal_berakhir"] ?></td>
                                                    <td>
                                                        <?php if (!empty($row['sertifikat']) && strpos($row['sertifikat'], 'email:') !== 0): ?>
                                                            <a href="<?= $row['sertifikat'] ?>" target="_blank" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i> Lihat PDF</a>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex justify-content-center gap-1">
                                                            <button class="btn btn-icon btn-warning" data-bs-toggle="modal" data-bs-target="#modalEdit" onclick='editData(<?= json_encode($row) ?>)' title="Edit"><i class="fas fa-edit"></i></button>
                                                            <button class="btn btn-icon btn-danger" data-bs-toggle="modal" data-bs-target="#modalDelete<?= $row['id_akreditasi'] ?>" title="Hapus"><i class="fas fa-trash-alt"></i></button>                                    
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr><td colspan="11">Tidak ada data yang tersedia.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Pagination -->
                            <div class="showing-info text-center mb-3">
                                Menampilkan <?php echo ($totalData > 0) ? $start + 1 : 0; ?> sampai <?php echo min($start + $limit, $totalData); ?> dari <?php echo $totalData; ?> data
                            </div>
                            <nav aria-label="Page navigation">
                                <ul class="pagination pagination-custom">
                                    <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                        <a class="page-link arrow" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>" tabindex="-1">←</a>
                                    </li>
                                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                        <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                                        <a class="page-link arrow" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>">→</a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Main Content -->

            <!-- Modal Tambah Data -->
            <div class="modal fade" id="modalTambah" tabindex="-1" aria-labelledby="modalTambahLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalTambahLabel">Tambah Data</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form method="post" enctype="multipart/form-data">
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Akreditasi</label>
                                    <div class="flex flex-col">
                                        <div class="flex items-center px-4">
                                            <input id="akreditasi_unit" type="radio" value="Unit Kerja" name="akreditasi" onclick="showQuestions(this.value)" class="form-check-input" required />
                                            <label class="form-label px-2">Unit Kerja</label>
                                            <input id="akreditasi_program_studi" type="radio" value="Program Studi" name="akreditasi" onclick="showQuestions(this.value)" class="form-check-input" required />
                                            <label class="form-label px-2">Program Studi</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3 hidden" id="id_unit">
                                    <label for="id_unit" class="form-label">Nama Unit Kerja</label>
                                    <select class="form-select" name="id_unit">
                                        <option value="0" selected disabled>-- Pilih Unit Kerja --</option>
                                        <?php $ambil = mysqli_query($koneksi, "SELECT * FROM unit"); while ($data = mysqli_fetch_assoc($ambil)) { echo '<option value="' . $data['id_unit'] . '">' . $data['nama_unit_kerja'] . '</option>'; } ?>
                                    </select>
                                </div>
                                <div class="mb-3 hidden" id="nama_jurusan">
                                    <label for="nama_jurusan" class="form-label">Nama Jurusan</label>
                                    <select class="form-select" name="nama_jurusan" onchange="filterProdi(this.value)">
                                        <option value="0" selected disabled>-- Pilih Jurusan --</option>
                                        <?php $ambil = mysqli_query($koneksi, "SELECT DISTINCT nama_jurusan FROM program_studi WHERE nama_jurusan <> '' ORDER BY nama_jurusan"); while ($data = mysqli_fetch_assoc($ambil)) { $jur = htmlspecialchars($data['nama_jurusan'], ENT_QUOTES); echo '<option value="' . $jur . '">' . $jur . '</option>'; } ?>
                                    </select>
                                </div>
                                <div class="mb-3 hidden" id="id_prodi">
                                    <label for="id_prodi" class="form-label">Nama Program Studi</label>
                                    <select class="form-select" name="id_prodi" id="select_prodi">
                                        <option value="0" selected disabled>-- Pilih Program Studi --</option>
                                        <?php $ambil = mysqli_query($koneksi, "SELECT * FROM program_studi ORDER BY nama_program_studi"); while ($data = mysqli_fetch_assoc($ambil)) { $jur = htmlspecialchars($data['nama_jurusan'], ENT_QUOTES); echo '<option value="' . $data['id_prodi'] . '" data-jurusan="' . $jur . '" hidden disabled>' . $data['nama_program_studi'] . '</option>'; } ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="peringkat" class="form-label">Predikat</label>
                                    <select class="form-select" name="peringkat" id="peringkat" required>
                                        <option value="" selected disabled>-- Pilih Predikat --</option>
                                        <option value="Unggul">Unggul</option>
                                        <option value="Baik Sekali">Baik Sekali</option>
                                        <option value="Baik">Baik</option>
                                        <option value="Cukup Sekali">Cukup Sekali</option>
                                        <option value="Cukup">Cukup</option>
                                    </select>
                                </div>
                                <div class="mb-3" id="id_lembaga">
                                    <label for="id_lembaga" class="form-label">Lembaga</label>
                                    <select class="form-select" name="id_lembaga">
                                        <option value="0" selected disabled>-- Pilih Lembaga --</option>
                                        <?php $ambil = mysqli_query($koneksi, "SELECT * FROM lembaga"); while ($data = mysqli_fetch_assoc($ambil)) { echo '<option value="' . $data['id_lembaga'] . '">' . strtoupper($data['nama_lembaga']) . '</option>'; } ?>
                                    </select>
                                </div>
                                <div class="mb-3" id="id_jenis">
                                    <label for="id_jenis" class="form-label">Jenis Akreditasi</label>
                                    <select class="form-select" name="id_jenis">
                                        <option value="0" selected disabled>-- Pilih Jenis Akreditasi --</option>
                                        <?php 
                                            $ambil = mysqli_query($koneksi, "SELECT * FROM jenis_akreditasi WHERE jenis_akreditasi IN ('Nasional', 'Internasional')"); 
                                            while ($data = mysqli_fetch_assoc($ambil)) { 
                                                echo '<option value="' . $data['id_jenis'] . '">' . $data['jenis_akreditasi'] . '</option>'; 
                                            } 
                                        ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="tanggal_mulai" class="form-label">Tanggal Mulai</label>
                                    <input type="date" class="form-control" name="tanggal_mulai" required>
                                </div>
                                <div class="mb-3">
                                    <label for="tanggal_berakhir" class="form-label">Tanggal Berakhir</label>
                                    <input type="date" class="form-control" name="tanggal_berakhir" required>
                                </div>
                                <div class="mb-3">
                                    <label for="sertifikat" class="form-label">Upload Sertifikat (PDF)</label>
                                    <input type="file" class="form-control" id="sertifikat" name="sertifikat" accept=".pdf">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" name="submit" class="btn btn-primary">Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Modal Edit Data -->
            <div class="modal fade" id="modalEdit" tabindex="-1" aria-labelledby="modalEditLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalEditLabel">Edit Data Akreditasi</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form method="post" enctype="multipart/form-data">
                            <input type="hidden" name="id_akreditasi" id="edit_id_akreditasi">
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Akreditasi</label>
                                    <div class="flex flex-col">
                                        <div class="flex items-center px-4">
                                            <input id="edit_radio_unit" type="radio" value="Unit Kerja" name="akreditasi" onclick="showQuestions(this.value, 'edit_')" class="form-check-input" required />
                                            <label class="form-label px-2">Unit Kerja</label>
                                            <input id="edit_radio_prodi" type="radio" value="Program Studi" name="akreditasi" onclick="showQuestions(this.value, 'edit_')" class="form-check-input" required />
                                            <label class="form-label px-2">Program Studi</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3 hidden" id="edit_id_unit">
                                    <label for="edit_id_unit" class="form-label">Nama Unit Kerja</label>
                                    <select class="form-select" name="id_unit" id="edit_id_unit">
                                        <option value="0" selected disabled>-- Pilih Unit Kerja --</option>
                                        <?php $ambil = mysqli_query($koneksi, "SELECT * FROM unit"); while ($data = mysqli_fetch_assoc($ambil)) { echo '<option value="' . $data['id_unit'] . '">' . $data['nama_unit_kerja'] . '</option>'; } ?>
                                    </select>
                                </div>
                                <div class="mb-3 hidden" id="edit_nama_jurusan">
                                    <label for="edit_nama_jurusan" class="form-label">Nama Jurusan</label>
                                    <select class="form-select" name="nama_jurusan" id="edit_nama_jurusan" onchange="filterProdi(this.value, 'edit_')">
                                        <option value="0" selected disabled>-- Pilih Jurusan --</option>
                                        <?php $ambil = mysqli_query($koneksi, "SELECT DISTINCT nama_jurusan FROM program_studi WHERE nama_jurusan <> '' ORDER BY nama_jurusan"); while ($data = mysqli_fetch_assoc($ambil)) { $jur = htmlspecialchars($data['nama_jurusan'], ENT_QUOTES); echo '<option value="' . $jur . '">' . $jur . '</option>'; } ?>
                                    </select>
                                </div>
                                <div class="mb-3 hidden" id="edit_id_prodi">
                                    <label for="edit_id_prodi" class="form-label">Nama Program Studi</label>
                                    <select class="form-select" name="id_prodi" id="edit_select_prodi">
                                        <option value="0" selected disabled>-- Pilih Program Studi --</option>
                                        <?php $ambil = mysqli_query($koneksi, "SELECT * FROM program_studi ORDER BY nama_program_studi"); while ($data = mysqli_fetch_assoc($ambil)) { $jur = htmlspecialchars($data['nama_jurusan'], ENT_QUOTES); echo '<option value="' . $data['id_prodi'] . '" data-jurusan="' . $jur . '" hidden disabled>' . $data['nama_program_studi'] . '</option>'; } ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_peringkat" class="form-label">Predikat</label>
                                    <select class="form-select" name="peringkat" id="edit_peringkat" required>
                                        <option value="Unggul">Unggul</option>
                                        <option value="Baik Sekali">Baik Sekali</option>
                                        <option value="Baik">Baik</option>
                                        <option value="Cukup Sekali">Cukup Sekali</option>
                                        <option value="Cukup">Cukup</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_id_lembaga" class="form-label">Lembaga</label>
                                    <select class="form-select" name="id_lembaga" id="edit_id_lembaga">
                                        <?php $ambil = mysqli_query($koneksi, "SELECT * FROM lembaga"); while ($data = mysqli_fetch_assoc($ambil)) { echo '<option value="' . $data['id_lembaga'] . '">' . strtoupper($data['nama_lembaga']) . '</option>'; } ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_id_jenis" class="form-label">Jenis Akreditasi</label>
                                    <select class="form-select" name="id_jenis" id="edit_id_jenis">
                                        <?php 
                                            $ambil = mysqli_query($koneksi, "SELECT * FROM jenis_akreditasi WHERE jenis_akreditasi IN ('Nasional', 'Internasional')"); 
                                            while ($data = mysqli_fetch_assoc($ambil)) { 
                                                echo '<option value="' . $data['id_jenis'] . '">' . $data['jenis_akreditasi'] . '</option>'; 
                                            } 
                                        ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_tanggal_mulai" class="form-label">Tanggal Mulai</label>
                                    <input type="date" class="form-control" name="tanggal_mulai" id="edit_tanggal_mulai" required>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_tanggal_berakhir" class="form-label">Tanggal Berakhir</label>
                                    <input type="date" class="form-control" name="tanggal_berakhir" id="edit_tanggal_berakhir" required>
                                </div>
                                <div class="mb-3">
                                    <label for="sertifikat" class="form-label">Upload Sertifikat Baru (PDF)</label>
                                    <input type="file" class="form-control" id="sertifikat" name="sertifikat" accept=".pdf">
                                    <small class="text-muted">*Kosongkan jika tidak ingin mengubah Sertifikat.</small>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" name="update" class="btn btn-primary">Update Data</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <?php foreach ($akreditasi as $row): ?>
                <div class="modal fade" id="modalDelete<?php echo $row['id_akreditasi']; ?>" tabindex="-1" aria-labelledby="modalDeleteLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalDeleteLabel">Konfirmasi Hapus Data</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form method="post" action="akreditasi.php">
                                <input type="hidden" name="id_akreditasi" value="<?php echo $row['id_akreditasi']; ?>">
                                <div class="modal-body">
                                    <p>Apakah Anda yakin ingin menghapus data ini?</p>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" name="delete" class="btn btn-danger">Hapus</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <!-- Footer -->
            <div class="container-fluid pt-4 px-4">
                <div class="bg-light rounded-top p-4">
                    <div class="row">
                        <div class="col-12 text-center">
                            Made By Pusat Pengembangan dan Penjaminan Mutu (P4M).
                        </div>
                    </div>
                </div>
            </div>
            <!-- Footer -->

        </div>
        <!-- Content -->

        <!-- Back to Top -->
        <a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="lib/chart/chart.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/waypoints/waypoints.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="lib/tempusdominus/js/moment.min.js"></script>
    <script src="lib/tempusdominus/js/moment-timezone.min.js"></script>
    <script src="lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js"></script>

    <!-- Template Javascript -->
    <script src="js/main.js"></script>
</body>

</html>