<?php
include 'koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id         = $_POST['id'];
    $nama_kode  = $_POST['nama_kode'];
    $nama_jalan = $_POST['nama_jalan'];
    $rt         = $_POST['rt'];
    $rw         = $_POST['rw'];
    $kampung    = $_POST['kampung'];
    $kondisi    = $_POST['kondisi'];
    $latitude   = $_POST['latitude'];
    $longitude  = $_POST['longitude'];

    $sql = "UPDATE cermin_tikungan SET 
            nama_kode='$nama_kode', 
            nama_jalan='$nama_jalan', 
            rt='$rt', 
            rw='$rw', 
            kampung='$kampung', 
            kondisi='$kondisi',
            latitude='$latitude', 
            longitude='$longitude' 
            WHERE id='$id'";

    if ($conn->query($sql) === TRUE) {
        header("Location: index.php");
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
$conn->close();
?>