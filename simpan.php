<?php
include 'koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_kode  = $_POST['nama_kode'];
    $nama_jalan = $_POST['nama_jalan'];
    $rt         = $_POST['rt'];
    $rw         = $_POST['rw'];
    $kampung    = $_POST['kampung'];
    $kondisi    = $_POST['kondisi'];
    $latitude   = $_POST['latitude'];
    $longitude  = $_POST['longitude'];

    $sql = "INSERT INTO cermin_tikungan (nama_kode, nama_jalan, rt, rw, kampung, kondisi, latitude, longitude) 
            VALUES ('$nama_kode', '$nama_jalan', '$rt', '$rw', '$kampung', '$kondisi', '$latitude', '$longitude')";

    if ($conn->query($sql) === TRUE) {
        header("Location: index.php");
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
$conn->close();
?>