<?php
require '../vendor/autoload.php'; 
include '../koneksi.php'; 
/** @var mysqli $koneksi */ //
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Tangkap data form dan bersihkan
    $name    = strip_tags(trim($_POST["name"] ?? ''));
    $email   = filter_var(trim($_POST["email"] ?? ''), FILTER_SANITIZE_EMAIL);
    $subject = strip_tags(trim($_POST["subject"] ?? ''));
    $message = trim($_POST["message"] ?? '');
    $id_prodi = isset($_POST["id_prodi"]) ? (int)$_POST["id_prodi"] : 0;

    // 2. Validasi Data
    if (empty($name) || empty($subject) || empty($message)) {
        http_response_code(400);
        echo "Nama, Subjek, dan Pesan wajib diisi.";
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo "Format email tidak valid.";
        exit;
    }

    if ($id_prodi === 0) {
        http_response_code(400);
        echo "Anda belum memilih Program Studi tujuan.";
        exit;
    }

    // 3. Cari email KPS dari database berdasarkan ID Prodi
    $id_prodi_escaped = mysqli_real_escape_string($koneksi, $id_prodi);
    $query = "SELECT email_kps, nama_program_studi FROM program_studi WHERE id_prodi = '$id_prodi_escaped'";
    $result = mysqli_query($koneksi, $query);
    $data_prodi = mysqli_fetch_assoc($result);

    // Jika data prodi tidak ditemukan di database
    if (!$data_prodi) {
        http_response_code(400);
        echo "Program studi tidak ditemukan di sistem.";
        exit;
    }

    // Jika email KPS kosong
    if (empty($data_prodi['email_kps'])) {
        http_response_code(400);
        echo "Email KPS untuk Program Studi " . htmlspecialchars($data_prodi['nama_program_studi']) . " belum diatur oleh Admin.";
        exit;
    }

    $email_tujuan = $data_prodi['email_kps'];
    $nama_prodi = $data_prodi['nama_program_studi'];

    // 4. KONFIGURASI EMAIL (PHPMailer)
    $mail = new PHPMailer(true);
    try {
        // Konfigurasi SMTP
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'kafkaalintang83@gmail.com'; // Ganti dengan email kamu
        $mail->Password   = 'njya esao czad ipqx';       // GANTI DENGAN 16 KARAKTER APP PASSWORD KAMU!
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Pengirim & Penerima
        $mail->setFrom('kafkaalintang83@gmail.com', 'Website P4M Polibatam');
        $mail->addAddress($email_tujuan); 
        $mail->addReplyTo($email, $name); 

        // Konten Email
        $mail->isHTML(true);
        $mail->Subject = "Pesan Baru ($nama_prodi): " . $subject;
        
        $body = "<h3>Pesan Baru untuk Prodi $nama_prodi</h3>";
        $body .= "<p><b>Nama Pengirim:</b> " . htmlspecialchars($name) . "</p>";
        $body .= "<p><b>Email Pengirim:</b> " . htmlspecialchars($email) . "</p>";
        $body .= "<p><b>Subjek:</b> " . htmlspecialchars($subject) . "</p>";
        $body .= "<p><b>Pesan:</b><br>" . nl2br(htmlspecialchars($message)) . "</p>";
        
        $mail->Body = $body;
        $mail->send();
        
        // Respon sukses (dibaca oleh Javascript form)
        echo "OK"; 
    } catch (Exception $e) {
        http_response_code(500);
        echo "Pesan gagal dikirim. Error: {$mail->ErrorInfo}";
    }
} else {
    http_response_code(403);
    echo "Akses ditolak.";
}
?>