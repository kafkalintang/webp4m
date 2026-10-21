<?php
require __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include "../koneksi.php";
/** @var mysqli $koneksi */ 

$hari_ini = new DateTime();

$pengirim_email = 'kafkaalintang83@gmail.com'; 
$password_email = 'njya esao czad ipqx';    

$query = "SELECT * FROM akreditasi WHERE akreditasi = 'program studi' AND sertifikat LIKE 'email:%'";
$result = mysqli_query($koneksi, $query);

while ($row = mysqli_fetch_assoc($result)) {
    $email_kps = substr($row['sertifikat'], 6);
    if (empty($email_kps)) continue;

    $tgl_berakhir = new DateTime($row['tanggal_berakhir']);
    $selisih = $hari_ini->diff($tgl_berakhir)->days;

    $status_pesan = "";
    if ($tgl_berakhir < $hari_ini) {
        $status_pesan = "TELAH KEDALUWARSA";
    } elseif ($selisih == 365) {
        $status_pesan = "akan berakhir dalam 1 TAHUN lagi";
    } elseif ($selisih == 2) {
        $status_pesan = "akan berakhir dalam 2 HARI lagi";
    } elseif ($selisih == 1) {
        $status_pesan = "akan berakhir BESOK";
    }

    if ($status_pesan != "") {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = $pengirim_email;
            $mail->Password   = $password_email;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // Pengirim email
            $mail->setFrom($pengirim_email, 'P4M Polibatam (Sistem Otomatis)');
            
            // Penerima email
            $mail->addAddress($email_kps);
            
            $mail->isHTML(true);
            $mail->Subject = "Peringatan Akreditasi: " . $row['nama_jurusan'];
            
            $body = "<h3>Peringatan Dini Akreditasi</h3>";
            $body .= "<p>Yth. Ketua Program Studi,</p>";
            $body .= "<p>Diberitahukan bahwa akreditasi program studi <b>" . $row['nama_jurusan'] . "</b> " . $status_pesan . " pada tanggal <b>" . $row['tanggal_berakhir'] . "</b>.</p>";
            $body .= "<p>Mohon segera lakukan persiapan perpanjangan akreditasi.</p>";
            $body .= "<br><p>Terima kasih.</p>";
            
            $mail->Body = $body;
            $mail->send();
            echo "Email terkirim ke: $email_kps <br>";
        } catch (Exception $e) {
            echo "Gagal mengirim email ke $email_kps. Error: {$mail->ErrorInfo} <br>";
        }
    }
}
?>