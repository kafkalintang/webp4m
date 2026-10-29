<?php
include "../koneksi.php";
/** @var mysqli $koneksi */

// =========================================================================
// FUNGSI HELPER: Auto-insert master data jika user memilih "Lainnya"
// =========================================================================

/**
 * Mendapatkan ID dari nama, jika tidak ada maka insert baru.
 * 
 * @param mysqli $koneksi Koneksi database
 * @param string $table Nama tabel
 * @param string $idField Nama kolom Primary Key
 * @param string $nameField Nama kolom yang dicek/di-insert
 * @param string $newName Nilai baru yang diketik user
 * @param array $extraFields Kolom tambahan (opsional)
 * @return int ID dari data yang di-insert/ditemukan
 */
function getOrCreateId($koneksi, $table, $idField, $nameField, $newName, $extraFields = [])
{
    $newName = trim($newName);
    if ($newName === '') return 0;

    $escaped = mysqli_real_escape_string($koneksi, $newName);
    $cek = mysqli_query($koneksi, "SELECT $idField FROM $table WHERE LOWER($nameField) = LOWER('$escaped') LIMIT 1");
    if ($cek && mysqli_num_rows($cek) > 0) {
        $row = mysqli_fetch_assoc($cek);
        return (int)$row[$idField];
    }

    $cols = [$nameField];
    $vals = ["'$escaped'"];
    foreach ($extraFields as $col => $val) {
        $cols[] = $col;
        $vals[] = "'" . mysqli_real_escape_string($koneksi, $val) . "'";
    }
    $sql = "INSERT INTO $table (" . implode(',', $cols) . ") VALUES (" . implode(',', $vals) . ")";
    if (mysqli_query($koneksi, $sql)) {
        return (int)mysqli_insert_id($koneksi);
    }
    return 0;
}

// =========================================================================
// PROSES TAMBAH DATA
// =========================================================================
if (isset($_POST["submit"])) {
    $peringkat = $_POST["peringkat"];
    $id_lembaga = $_POST["id_lembaga"];
    
    // ==== HANDLE "LAINNYA" ====
    if (!empty($_POST['peringkat_baru'])) {
        $peringkat = trim($_POST['peringkat_baru']);
    }
    if (!empty($_POST['id_lembaga_baru'])) {
        $id_lembaga = getOrCreateId($koneksi, 'lembaga', 'id_lembaga', 'nama_lembaga', $_POST['id_lembaga_baru']);
    }

    $tanggal_mulai = $_POST["tanggal_mulai"];
    $tanggal_berakhir = $_POST["tanggal_berakhir"];                                
    $sertifikat = '';

    // Upload Sertifikat (satu file)
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

    // Simpan data ke database
    $sql = "INSERT INTO akreditasi_perguruan (peringkat, id_lembaga, tanggal_mulai, tanggal_berakhir, sertifikat) 
            VALUES ('$peringkat', '$id_lembaga', '$tanggal_mulai', '$tanggal_berakhir', '$sertifikat')";

    $result = mysqli_query($koneksi, $sql);
    if ($result) {
        echo "<script>alert('Data telah ditambahkan');
            window.location='akre_perguruan.php';</script>";
    } else {
        echo "<script>alert('Data gagal ditambahkan!');
        window.location='akre_perguruan.php';</script>";
    }
}

// =========================================================================
// PROSES EDIT / UPDATE DATA (DENGAN RIWAYAT SERTIFIKAT)
// =========================================================================
if (isset($_POST["update"])) {
    $id_perguruan = mysqli_real_escape_string($koneksi, $_POST["id_perguruan"]);
    $peringkat = $_POST["peringkat"];
    $id_lembaga = $_POST["id_lembaga"];

    // ==== HANDLE "LAINNYA" ====
    if (!empty($_POST['peringkat_baru'])) {
        $peringkat = trim($_POST['peringkat_baru']);
    }
    if (!empty($_POST['id_lembaga_baru'])) {
        $id_lembaga = getOrCreateId($koneksi, 'lembaga', 'id_lembaga', 'nama_lembaga', $_POST['id_lembaga_baru']);
    }

    $tanggal_mulai = $_POST["tanggal_mulai"];
    $tanggal_berakhir = $_POST["tanggal_berakhir"];
    $sertifikat_sql = "";

    // Ambil data sertifikat lama dari database
    $query_lama = mysqli_query($koneksi, "SELECT sertifikat FROM akreditasi_perguruan WHERE id_perguruan='$id_perguruan'");
    $data_lama = mysqli_fetch_assoc($query_lama);
    $sertifikat_lama = $data_lama['sertifikat'] ?? '';

    // Upload Sertifikat Baru (jika ada)
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
                // Jika sudah ada sertifikat lama, gabungkan dengan koma (append riwayat)
                if (!empty($sertifikat_lama)) {
                    $sertifikat_baru_full = $sertifikat_lama . ',' . $filePath;
                } else {
                    $sertifikat_baru_full = $filePath;
                }
                $sertifikat_sql = ", sertifikat='$sertifikat_baru_full'";
            }
        }
    }

    $sql = "UPDATE akreditasi_perguruan SET 
            peringkat='$peringkat', 
            id_lembaga='$id_lembaga', 
            tanggal_mulai='$tanggal_mulai', 
            tanggal_berakhir='$tanggal_berakhir' 
            $sertifikat_sql 
            WHERE id_perguruan='$id_perguruan'";

    $result = mysqli_query($koneksi, $sql);
    if ($result) {
        echo "<script>alert('Data telah diupdate');
            window.location='akre_perguruan.php';</script>";
    } else {
        echo "<script>alert('Data gagal diupdate!');
        window.location='akre_perguruan.php';</script>";
    }
}

// =========================================================================
// PROSES DELETE DATA
// =========================================================================
if (isset($_POST["delete"])) {
    $id_perguruan = mysqli_real_escape_string($koneksi, $_POST["id_perguruan"]);
    $delete = mysqli_query($koneksi, "DELETE FROM akreditasi_perguruan WHERE id_perguruan='$id_perguruan'");

    if ($delete) {
        echo "<script>alert('Data Berhasil Dihapus'); window.location='akre_perguruan.php';</script>";
    } else {
        echo "<script>alert('Data Gagal Dihapus: " . mysqli_error($koneksi) . "'); window.location='akre_perguruan.php';</script>";
    }
}

// =========================================================================
// AMBIL DATA UNTUK DITAMPILKAN
// =========================================================================
$query_akreditasi = query("
    SELECT 
        ap.id_perguruan,
        ap.peringkat,
        ap.id_lembaga,
        l.nama_lembaga,
        ap.tanggal_mulai,
        ap.tanggal_berakhir,
        ap.sertifikat
    FROM akreditasi_perguruan AS ap
    LEFT JOIN lembaga AS l 
        ON l.id_lembaga = ap.id_lembaga
    ORDER BY ap.id_perguruan DESC
");

// Pagination setup
$limit = 10; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page > 1) ? ($page * $limit) - $limit : 0;
// Pencarian
$search = isset($_GET['nama_lembaga']) ? trim($_GET['nama_lembaga']) : '';
if ($search !== '') {
    $query_akreditasi = array_filter($query_akreditasi, function ($item) use ($search) {
        return stripos((string)$item['nama_lembaga'], $search) !== false;
    });
}
// Query untuk mendapatkan data
$totalData = count($query_akreditasi);
$totalPages = ceil($totalData / $limit);
$akreditasi_perguruan = array_slice($query_akreditasi, $start, $limit);

// =========================================================================
// LOGIKA GROUPING (MENGGABUNGKAN PERINGKAT & LEMBAGA YANG SAMA)
// =========================================================================
$grouped_data = [];
foreach ($akreditasi_perguruan as $row) {
    // Kunci unik berdasarkan peringkat dan id_lembaga
    $key = $row['peringkat'] . '|' . $row['id_lembaga'];
    
    if (!isset($grouped_data[$key])) {
        $grouped_data[$key] = [
            'peringkat' => $row['peringkat'],
            'id_lembaga' => $row['id_lembaga'],
            'nama_lembaga' => $row['nama_lembaga'],
            'details' => [] // Menyimpan list tanggal & sertifikat
        ];
    }
    
    // Pecah sertifikat berdasarkan koma
    $sertifikats = !empty($row['sertifikat']) ? explode(',', $row['sertifikat']) : [];
    
    // Gabungkan tanggal dan sertifikat ke dalam array details
    $grouped_data[$key]['details'][] = [
        'id_perguruan' => $row['id_perguruan'],
        'tanggal_mulai' => $row['tanggal_mulai'],
        'tanggal_berakhir' => $row['tanggal_berakhir'],
        'sertifikats' => $sertifikats
    ];
}
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

    /* Styling untuk Tabel Grouping */
    .table-custom tbody tr.group-row td { border-bottom: 1px solid #dee2e6; }
    .table-custom tbody tr.detail-row td { border-bottom: none; padding: 8px 10px; font-size: 14px; }
    .table-custom tbody tr.detail-row:last-child td { border-bottom: 1px solid #dee2e6; }
    .table-custom tbody tr.detail-row:hover { background-color: #f8f9fa; }
    
    .file-link { display: block; text-align: left; margin-bottom: 3px; color: #0d83fd; text-decoration: none; font-size: 13px; }
    .file-link:hover { text-decoration: underline; }
    .file-link i { color: #dc3545; margin-right: 5px; }

    .pagination-custom { display: flex; justify-content: center; align-items: center; list-style: none; padding: 0; margin: 15px 0; gap: 5px; }
    .pagination-custom .page-item { display: flex; align-items: center; justify-content: center; transition: all 0.2s ease-in-out; }
    .pagination-custom .page-link { display: flex; align-items: center; justify-content: center; padding: 6px 10px; font-size: 12px; font-weight: 500; color: #0d83fd; background-color: white; border: 1.5px solid #0d83fd; border-radius: 8px; box-shadow: 1px 1px 5px rgba(0, 0, 0, 0.08); transition: all 0.2s ease-in-out; }
    .pagination-custom .page-item.active .page-link { background-color: #0d83fd; color: white; border-color: #0d83fd; box-shadow: 1px 1px 10px rgba(13, 131, 253, 0.2); }
    .pagination-custom .page-link:hover { background-color: rgba(13, 131, 253, 0.1); color: #0d83fd; transform: scale(1.05); }
    .pagination-custom .page-item.disabled .page-link { color: #aaa; border-color: #ddd; background-color: #f8f9fa; cursor: not-allowed; }
    .pagination-custom .arrow { font-size: 14px; padding: 6px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }

    .search-bar-wrapper { display: flex; justify-content: flex-start; margin-bottom: 20px; }
    .search-bar-wrapper form { display: flex; align-items: center; }
    .search-bar { border: 1px solid #0d83fd; border-radius: 20px; padding: 5px 15px; width: 250px; margin-right: -40px; }
    .btn-cari { background-color: #0d83fd; color: white; border-radius: 20px; padding: 5px 15px; border: none; margin-left: -8px; }
    .btn-cari i { font-size: 18px; }
    .showing-info { font-size: 14px; color: #0d83fd; text-align: center; margin-bottom: 10px; }
    .btn-tambah { background-color: #0d83fd; color: white; border-radius: 20px; padding: 5px 15px; border: none; }
    .hidden { display: none !important; }
    </style>

<script>
    function toggleLainnya(select, inputId) {
        var input = document.getElementById(inputId);
        if (!input) return;
        if (select.value === 'Lainnya') {
            input.classList.remove('hidden');
            input.required = true;
            input.focus();
        } else {
            input.classList.add('hidden');
            input.required = false;
            input.value = '';
        }
    }

    function editData(data) {
        ['edit_peringkat_baru','edit_lembaga_baru'].forEach(function(id){
            var el = document.getElementById(id);
            if (el) { el.value = ''; el.classList.add('hidden'); el.required = false; }
        });

        document.getElementById('edit_id_perguruan').value = data.id_perguruan;
        document.getElementById('edit_tanggal_mulai').value = data.tanggal_mulai;
        document.getElementById('edit_tanggal_berakhir').value = data.tanggal_berakhir;

        var selPeringkat = document.getElementById('edit_peringkat');
        var foundPeringkat = false;
        for (var i = 0; i < selPeringkat.options.length; i++) {
            if (selPeringkat.options[i].value === data.peringkat) {
                selPeringkat.selectedIndex = i;
                foundPeringkat = true;
                break;
            }
        }
        if (!foundPeringkat) {
            selPeringkat.value = 'Lainnya';
            document.getElementById('edit_peringkat_baru').classList.remove('hidden');
            document.getElementById('edit_peringkat_baru').required = true;
            document.getElementById('edit_peringkat_baru').value = data.peringkat;
        }

        document.getElementById('edit_id_lembaga').value = data.id_lembaga;
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
                            <a href="akreditasi.php" class="dropdown-item">Akreditasi</a>
                            <a href="akre_perguruan.php" class="dropdown-item active">Perguruan Tinggi</a>
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
                    <a href="berita.php" class="nav-item nav-link">
                        <i class="fa fa-clipboard-list me-2"></i>Kelola Berita
                    </a>
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
                <a href="../index.php" class="btn btn-link text-dark" style="position: absolute; right: 20px;">
                    <i class="fa fa-sign-out-alt"></i>
                </a>
                </div>
            </nav>
            <!-- Navbar -->

            <!-- Main Content -->
            <div class="container-fluid pt-4 px-4">
                <h5 class="mb-4 text-primary">Kelola Akreditasi Perguruan Tinggi</h5>
                <div class="row g-4">
                    <div class="col-12">
                        <div class="bg-light rounded h-100 p-4">
                        <!-- search bar -->
                        <div class="search-bar-wrapper">
                            <div style="display: flex; justify-content: flex-start; align-items: center;">
                            <a class="button btn-tambah" href="#" style="margin-right: 5px;" data-bs-toggle="modal" data-bs-target="#modalTambah">Tambah Data</a>
                            <form action="akre_perguruan.php" method="get">
                            <input type="text" name="nama_lembaga" class="form-control search-bar" placeholder="nama lembaga" value="<?php echo htmlspecialchars($search); ?>">
                            <button type="submit" class="btn btn-cari"><i class="fas fa-search"></i></button>
                            </form>
                            </div>
                        </div>      
                        <!-- Table Grouped -->
                        <div class="table-responsive">
                            <table class="table table-hover table-custom">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Peringkat</th>
                                    <th>Lembaga</th>
                                    <th>Tanggal Mulai</th>
                                    <th>Tanggal Berakhir</th>
                                    <th>Sertifikat</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($grouped_data)): ?>
                                <?php $no = 1; ?>
                                <?php foreach ($grouped_data as $group): ?>
                                    
                                    <?php 
                                        // Hitung total baris detail untuk rowspan
                                        $rowspan = count($group['details']); 
                                    ?>
                                    
                                    <?php foreach ($group['details'] as $index => $detail): ?>
                                    <tr class="<?php echo ($index == 0) ? 'group-row' : 'detail-row'; ?>">
                                        
                                        <?php if ($index == 0): ?>
                                            <!-- Kolom No, Peringkat, Lembaga hanya dirender di baris pertama -->
                                            <td rowspan="<?php echo $rowspan; ?>"><?php echo $no++; ?></td>
                                            <td rowspan="<?php echo $rowspan; ?>"><?php echo $group["peringkat"]; ?></td>
                                            <td rowspan="<?php echo $rowspan; ?>"><?php echo $group["nama_lembaga"]; ?></td>
                                        <?php endif; ?>
                                        
                                        <!-- Kolom Tanggal & Sertifikat selalu dirender -->
                                        <td><?php echo $detail["tanggal_mulai"]; ?></td>
                                        <td><?php echo $detail["tanggal_berakhir"]; ?></td>
                                        <td>
                                            <?php if (!empty($detail['sertifikats'])): ?>
                                                <?php foreach ($detail['sertifikats'] as $pdf): ?>
                                                    <a href="<?php echo trim($pdf); ?>" target="_blank" class="file-link">
                                                        <i class="fas fa-file-pdf"></i> <?php echo basename(trim($pdf)); ?>
                                                    </a>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <span class="text-muted">No File</span>
                                            <?php endif; ?>
                                        </td>
                                        
                                        <!-- Kolom Aksi (Edit & Hapus) hanya untuk baris tanggal paling baru -->
                                        <td>
                                            <div class="d-flex justify-content-center gap-1">
                                                <button class="btn btn-icon btn-warning" data-bs-toggle="modal" data-bs-target="#modalEdit" onclick='editData(<?php echo json_encode($detail) ?>)' title="Edit / Perpanjang"><i class="fas fa-edit"></i></button>
                                                <button class="btn btn-icon btn-danger" data-bs-toggle="modal" data-bs-target="#modalDelete<?php echo $detail['id_perguruan']; ?>" title="Hapus"><i class="fas fa-trash-alt"></i></button>                                    
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endforeach; ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center">Tidak ada data yang tersedia.</td>
                                </tr>
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
                                    <a class="page-link arrow" href="?page=<?php echo $page - 1; ?>&nama_lembaga=<?php echo urlencode($search); ?>" tabindex="-1">←</a>
                                </li>
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&nama_lembaga=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                                </li>
                                <?php endfor; ?>
                                <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                                    <a class="page-link arrow" href="?page=<?php echo $page + 1; ?>&nama_lembaga=<?php echo urlencode($search); ?>">→</a>
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
                                    <label for="peringkat" class="form-label">Peringkat</label>
                                    <select class="form-select" name="peringkat" id="peringkat" required onchange="toggleLainnya(this, 'peringkat_baru')">
                                        <option value="" selected disabled>-- Pilih Peringkat --</option>
                                        <option value="Unggul">Unggul</option>
                                        <option value="Baik Sekali">Baik Sekali</option>
                                        <option value="Baik">Baik</option>
                                        <option value="Cukup Sekali">Cukup Sekali</option>
                                        <option value="Cukup">Cukup</option>
                                        <option value="Belum Ada Peringkat">Belum Ada Peringkat</option>
                                        <option value="Lainnya">+ Lainnya (Ketik Manual)</option>
                                    </select>
                                    <input type="text" class="form-control mt-2 hidden" name="peringkat_baru" id="peringkat_baru" placeholder="Ketik peringkat baru...">
                                </div>
                                <div class="mb-3" id="id_lembaga">
                                    <label for="id_lembaga" class="form-label">Lembaga</label>
                                    <select class="form-select" name="id_lembaga" id="id_lembaga" onchange="toggleLainnya(this, 'lembaga_baru')">
                                        <option value="0" selected disabled>-- Pilih Lembaga --</option>
                                        <?php
                                            $ambil = mysqli_query($koneksi,"SELECT * FROM lembaga");
                                            while ($data = mysqli_fetch_assoc($ambil)){
                                                echo '<option value="'.$data['id_lembaga'].'">'.strtoupper($data['nama_lembaga']).'</option>';
                                            }
                                        ?>
                                        <option value="Lainnya">+ Lainnya (Ketik Manual)</option>
                                    </select>
                                    <input type="text" class="form-control mt-2 hidden" name="id_lembaga_baru" id="lembaga_baru" placeholder="Ketik nama lembaga baru...">
                                </div>
                                <div class="mb-3">
                                    <label for="tanggal_mulai" class="form-label">Tanggal Mulai</label>
                                    <input type="date" class="form-control" name="tanggal_mulai" required>
                                </div>
                                <div class="mb-3">
                                    <label for="tanggal" class="form-label">Tanggal Berakhir</label>
                                    <input type="date" class="form-control" name="tanggal_berakhir" required>
                                </div>
                                <div class="mb-3">
                                    <label for="sertifikat" class="form-label">Sertifikat</label>
                                    <input type="file" class="form-control" id="sertifikat" name="sertifikat" accept=".pdf" required>
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
                            <h5 class="modal-title" id="modalEditLabel">Edit / Perpanjang Data</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form method="post" enctype="multipart/form-data">
                            <input type="hidden" name="id_perguruan" id="edit_id_perguruan">
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="edit_peringkat" class="form-label">Peringkat</label>
                                    <select class="form-select" name="peringkat" id="edit_peringkat" required onchange="toggleLainnya(this, 'edit_peringkat_baru')">
                                        <option value="" selected disabled>-- Pilih Peringkat --</option>
                                        <option value="Unggul">Unggul</option>
                                        <option value="Baik Sekali">Baik Sekali</option>
                                        <option value="Baik">Baik</option>
                                        <option value="Cukup Sekali">Cukup Sekali</option>
                                        <option value="Cukup">Cukup</option>
                                        <option value="Belum Ada Peringkat">Belum Ada Peringkat</option>
                                        <option value="Lainnya">+ Lainnya (Ketik Manual)</option>
                                    </select>
                                    <input type="text" class="form-control mt-2 hidden" name="peringkat_baru" id="edit_peringkat_baru" placeholder="Ketik peringkat baru...">
                                </div>
                                <div class="mb-3">
                                    <label for="edit_id_lembaga" class="form-label">Lembaga</label>
                                    <select class="form-select" name="id_lembaga" id="edit_id_lembaga" onchange="toggleLainnya(this, 'edit_lembaga_baru')">
                                        <option value="0" selected disabled>-- Pilih Lembaga --</option>
                                        <?php
                                            $ambil = mysqli_query($koneksi,"SELECT * FROM lembaga");
                                            while ($data = mysqli_fetch_assoc($ambil)){
                                                echo '<option value="'.$data['id_lembaga'].'">'.strtoupper($data['nama_lembaga']).'</option>';
                                            }
                                        ?>
                                        <option value="Lainnya">+ Lainnya (Ketik Manual)</option>
                                    </select>
                                    <input type="text" class="form-control mt-2 hidden" name="id_lembaga_baru" id="edit_lembaga_baru" placeholder="Ketik nama lembaga baru...">
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
                                    <label for="edit_sertifikat" class="form-label">Sertifikat Baru (PDF)</label>
                                    <input type="file" class="form-control" id="edit_sertifikat" name="sertifikat" accept=".pdf">
                                    <small class="text-muted text-danger">*Unggah file baru untuk menambahkan riwayat. Kosongkan jika tidak ada.</small>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" name="update" class="btn btn-primary">Update Data</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <?php foreach ($akreditasi_perguruan as $row): ?>
            <!-- Modal Delete -->
            <div class="modal fade" id="modalDelete<?php echo $row['id_perguruan']; ?>" tabindex="-1" aria-labelledby="modalDeleteLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalDeleteLabel">Konfirmasi Hapus Data</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form method="post">
                            <input type="hidden" name="id_perguruan" value="<?php echo $row['id_perguruan']; ?>">
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