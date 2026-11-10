<?php
include 'koneksi.php';

// Ambil data dari tabel cermin_tikungan
$result = $conn->query("SELECT * FROM cermin_tikungan");
$dataCermin = array();

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $dataCermin[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Web GIS - Cermin Tikungan Sorosutan</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.5.1/dist/leaflet.css">
    
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; display: flex; flex-direction: column; gap: 20px; }
        h2 { margin-top: 0; margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center; }
        table { width: 100%; border-collapse: collapse; box-shadow: 0 0 10px rgba(0,0,0,0.1); font-size: 14px;}
        th, td { border: 1px solid #ddd; padding: 8px; text-align: center; }
        th { background-color: #f4f4f4; }
        #map { height: 500px; width: 100%; border: 1px solid #ccc; z-index: 1; }
        
        .btn-tambah { background-color: #4CAF50; color: white; border: none; padding: 10px 15px; cursor: pointer; border-radius: 4px; }
        .modal { display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); justify-content: center; align-items: center; }
        .modal-content { background-color: #fff; padding: 20px; border-radius: 8px; width: 400px; display: flex; flex-direction: column; gap: 10px; max-height: 90vh; overflow-y: auto;}
        .modal-content input { padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
        .modal-actions { display: flex; justify-content: space-between; margin-top: 10px; }
        .modal-actions button { padding: 8px 15px; border: none; border-radius: 4px; cursor: pointer; }
        
        .btn-simpan { background-color: #008CBA; color: white; }
        .btn-batal { background-color: #f44336; color: white; }
        .btn-update { background-color: #ff9800; color: white; }
    </style>
</head>
<body>

    <h2>Peta Sebaran Cermin Tikungan - Umbulharjo</h2>
    <div id="map"></div>

    <h2>
        Data Cermin Tikungan
        <button class="btn-tambah" onclick="bukaModalTambah()">+ Tambah Data</button>
    </h2>
    
    <table>
        <thead>
            <tr>
                <th>Nama/Kode</th>
                <th>Jalan</th>
                <th>RT/RW</th>
                <th>Kampung</th> 
                <th>Kondisi</th>
                <th>Latitude</th>
                <th>Longitude</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody id="table-body">
            
        </tbody>
    </table>

    <!-- MODAL TAMBAH DATA -->
    <div id="modalTambah" class="modal">
        <form class="modal-content" action="simpan.php" method="POST">
            <h3 style="text-align: center; margin-top: 0;">Tambah Cermin Tikungan</h3>
            <input type="text" name="nama_kode" placeholder="Nama/Kode (Cth: CT 100)" required>
            <input type="text" name="nama_jalan" placeholder="Nama Jalan" required>
            <input type="text" name="rt" placeholder="RT" required>
            <input type="text" name="rw" placeholder="RW" required>
            <input type="text" name="kampung" placeholder="Kampung" required> 
            <input type="text" name="kondisi" placeholder="Kondisi (Cth: Baik / Rusak)" required>
            <input type="number" name="latitude" placeholder="Latitude" step="any" required>
            <input type="number" name="longitude" placeholder="Longitude" step="any" required>
            
            <div class="modal-actions">
                <button type="button" class="btn-batal" onclick="tutupModalTambah()">Batal</button>
                <button type="submit" class="btn-simpan">Simpan</button>
            </div>
        </form>
    </div>

    <!-- MODAL EDIT DATA -->
    <div id="modalEdit" class="modal">
        <form class="modal-content" action="update.php" method="POST">
            <h3 style="text-align: center; margin-top: 0;">Edit Data</h3>
            <input type="hidden" name="id" id="edit-id">
            
            <label>Nama/Kode:</label> <input type="text" name="nama_kode" id="edit-kode" required>
            <label>Nama Jalan:</label> <input type="text" name="nama_jalan" id="edit-jalan" required>
            <label>RT:</label> <input type="text" name="rt" id="edit-rt" required>
            <label>RW:</label> <input type="text" name="rw" id="edit-rw" required>
            <label>Kampung:</label> <input type="text" name="kampung" id="edit-kampung" required> <!-- Kembali jadi Kampung -->
            <label>Kondisi:</label> <input type="text" name="kondisi" id="edit-kondisi" required>
            <label>Latitude:</label> <input type="number" name="latitude" id="edit-lat" step="any" required>
            <label>Longitude:</label> <input type="number" name="longitude" id="edit-lng" step="any" required>
            
            <div class="modal-actions">
                <button type="button" class="btn-batal" onclick="tutupModalEdit()">Batal</button>
                <button type="submit" class="btn-update">Update Data</button>
            </div>
        </form>
    </div>

    <script src="https://unpkg.com/leaflet@1.5.1/dist/leaflet.js"></script>
    <script>
        var dataTitik = <?php echo json_encode($dataCermin) ?: '[]'; ?>;
        
        var map = L.map('map').setView([-7.825, 110.380], 15);
        
        var basemapGoogle = L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
            maxZoom: 20, subdomains: ['mt0', 'mt1', 'mt2', 'mt3'], attribution: 'Map data &copy; Google'
        });

        var basemapEsri = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            attribution: 'Tiles &copy; Esri'
        });

        basemapGoogle.addTo(map);

        var tableBody = document.getElementById('table-body');
        var overlayMarkers = {};
        
        if(dataTitik.length > 0) {
            dataTitik.forEach(function(item) {
                // Tulis ke Tabel
                var row = `<tr>
                    <td>${item.nama_kode}</td>
                    <td>${item.nama_jalan}</td>
                    <td>${item.rt} / ${item.rw}</td>
                    <td>${item.kampung}</td> <!-- Kembali jadi Kampung -->
                    <td>${item.kondisi}</td>
                    <td>${item.latitude}</td>
                    <td>${item.longitude}</td>
                    <td>
                        <a href="javascript:void(0)" onclick="bukaModalEdit('${item.id}', '${item.nama_kode}', '${item.nama_jalan}', '${item.rt}', '${item.rw}', '${item.kampung}', '${item.kondisi}', '${item.latitude}', '${item.longitude}')">edit</a>
                    </td>
                </tr>`;
                tableBody.innerHTML += row;

                // Tulis ke Peta
                var marker = L.marker([item.latitude, item.longitude]).addTo(map);
                var popupContent = `<b>${item.nama_kode}</b><br>
                                    Jalan: ${item.nama_jalan}<br>
                                    RT/RW: ${item.rt}/${item.rw}<br>
                                    Kampung: ${item.kampung}<br> <!-- Kembali jadi Kampung -->
                                    Kondisi: ${item.kondisi}`;
                marker.bindPopup(popupContent);
                
                overlayMarkers[item.nama_kode] = marker;
            });
        }

        var baseMaps = { 
            "Google Street": basemapGoogle,
            "Esri Imagery (Satelit)": basemapEsri
        };

        L.control.layers(baseMaps, overlayMarkers, {collapsed: true}).addTo(map);
        L.control.scale({ maxWidth: 150, position: 'bottomright' }).addTo(map);

        function bukaModalTambah() { document.getElementById('modalTambah').style.display = 'flex'; }
        function tutupModalTambah() { document.getElementById('modalTambah').style.display = 'none'; }

        // FUNGSI MODAL EDIT
        function bukaModalEdit(id, kode, jalan, rt, rw, kampung, kondisi, lat, lng) {
            document.getElementById('modalEdit').style.display = 'flex';
            document.getElementById('edit-id').value = id;
            document.getElementById('edit-kode').value = kode;
            document.getElementById('edit-jalan').value = jalan;
            document.getElementById('edit-rt').value = rt;
            document.getElementById('edit-rw').value = rw;
            document.getElementById('edit-kampung').value = kampung;
            document.getElementById('edit-kondisi').value = kondisi;
            document.getElementById('edit-lat').value = lat;
            document.getElementById('edit-lng').value = lng;
        }
        function tutupModalEdit() { document.getElementById('modalEdit').style.display = 'none'; }
    </script>
</body>
</html>