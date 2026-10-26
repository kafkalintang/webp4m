<?php
require __DIR__ . '/../vendor/autoload.php';
include __DIR__ . '/../koneksi.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

date_default_timezone_set('Asia/Jakarta');

// ==========================================
// UBAH BARIS INI UNTUK TESTING ATAU PRODUKSI
// ==========================================
// $today = new DateTime('today'); // <--- PAKAI INI KALAU SUDAH PRODUKSI
$today = new DateTime('2026-10-26'); // <--- PAKAI INI UNTUK TESTING

$jadwal = [365, 2, 1]; 
$list = [];

// 1. AKREDITASI PRODI
$q1 = mysqli_query($koneksi, "
    SELECT a.tanggal_berakhir, ps.nama_program_studi AS nama, ps.email_kps AS email, 'Program Studi' AS jenis
    FROM akreditasi a
    JOIN program_studi ps ON ps.id_prodi = a.id_prodi
    WHERE ps.email_kps IS NOT NULL AND ps.email_kps <> ''
");
while ($r = mysqli_fetch_assoc($q1)) { 
    $list[] = $r; 
}

// 2. AKREDITASI UNIT (Anti-Error)
$q2 = mysqli_query($koneksi, "
    SELECT a.*, u.*
    FROM akreditasi a
    JOIN unit u ON u.id_unit = a.id_unit
");
while ($r = mysqli_fetch_assoc($q2)) {
    $nama_unit = $r['nama_unit'] ?? $r['nama'] ?? $r['nama_kepala_unit'] ?? 'Unit Tidak Diketahui';
    $email_unit = $r['email_kps'] ?? $r['email'] ?? $r['email_unit'] ?? '';
    
    if (!empty($email_unit)) {
        $list[] = [
            'tanggal_berakhir' => $r['tanggal_berakhir'],
            'nama' => $nama_unit,
            'email' => $email_unit,
            'jenis' => 'Unit'
        ];
    }
}

// 3. AKREDITASI PERGURUAN TINGGI
$q3 = mysqli_query($koneksi, "SELECT * FROM akreditasi_perguruan");
while ($r = mysqli_fetch_assoc($q3)) {
    $list[] = [
        'tanggal_berakhir' => $r['tanggal_berakhir'],
        'nama' => 'Politeknik Negeri Batam',
        'email' => 'admin@polibatam.ac.id', // GANTI EMAIL INI JIKA PERLU
        'jenis' => 'Perguruan Tinggi'
    ];
}

// ==========================================
// PROSES PENGIRIMAN EMAIL
// ==========================================
$terkirim = 0;
foreach ($list as $data) {
    $tgl_akhir = new DateTime($data['tanggal_berakhir']);
    
    // Hitung selisih hari (bisa minus kalau sudah lewat)
    $sisa_hari = (int)$today->diff($tgl_akhir)->days;
    
    // Cek apakah tanggal berakhir lebih kecil dari hari ini (sudah lewat)
    if ($tgl_akhir < $today) {
        continue; // Lewati jika sudah expired
    }

    // Cek jadwal
    if (!in_array($sisa_hari, $jadwal)) continue;

    // Label
    if ($sisa_hari == 365)      $label = '1 tahun';
    elseif ($sisa_hari == 2)    $label = '2 hari';
    else                        $label = '1 hari';

    // Pesan
    $pesan = "⚠️ PERINGATAN AKREDITASI ⚠️\n\n"
           . "Yth. Bapak/Ibu Pengelola,\n\n"
           . "Masa berlaku akreditasi {$data['jenis']} {$data['nama']} "
           . "akan berakhir pada " . date('d/m/Y', strtotime($data['tanggal_berakhir']))
           . " (tersisa {$label}).\n\n"
           . "Segera persiapkan proses re-akreditasi sebelum masa berlaku habis. "
           . "Jangan sampai terlewat!\n\n"
           . "Terima kasih. 🙏";

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'kafkaalintang83@gmail.com';
        $mail->Password   = 'njya esao czad ipqx';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('kafkaalintang83@gmail.com', 'Website P4M Polibatam');
        $mail->addAddress($data['email']);
        $mail->Subject = "Peringatan Akreditasi {$data['jenis']} - {$data['nama']} (sisa {$label})";
        $mail->isHTML(true);
        $mail->Body    = nl2br(htmlspecialchars($pesan));
        $mail->AltBody = $pesan;

        $mail->send();
        echo "[OK] {$data['jenis']} - {$data['nama']} (sisa {$label})\n";
        $terkirim++;
    } catch (Exception $e) {
        echo "[GAGAL] {$data['jenis']} - {$data['nama']}: {$mail->ErrorInfo}\n";
    }
}

echo "\nSelesai. Total terkirim: {$terkirim}\n";