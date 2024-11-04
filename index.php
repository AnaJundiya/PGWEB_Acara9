<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Website Kabupaten Sleman</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <style>
        /* CSS styling for the page */
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #d0c9f4;
        }

        h2 {
            color: black;
        }

        h1 {
            text-align: center;
            font-size: 2em;
            margin-bottom: 20px;
            background-color: #d0c9f4;
        }

        #map {
            width: 100%;
            height: 600px;
            margin: 20px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table,
        th,
        td {
            border: 1px solid #ccc;
            text-align: center;
            padding: 10px;
        }

        th {
            background-color: #917df6;
        }

        .delete-btn,
        .edit-btn {
            background-color: #ff6347;
            color: white;
            padding: 5px;
            border: none;
            cursor: pointer;
            margin: 2px;
        }

        .edit-btn {
            background-color: #4CAF50;
        }
    </style>
</head>

<body>
    <h1>Website GIS Kabupaten Sleman</h1>
    <div id="map"></div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        // Initialize map with a default view in case PHP data is empty
        var map = L.map("map").setView([0, 0], 5);

        var osm = L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(map);

        var rupabumiindonesia = L.tileLayer('https://geoservices.big.go.id/rbi/rest/services/BASEMAP/Rupabumi_Indonesia/MapServer/tile/{z}/{y}/{x}', {
            attribution: 'Badan Informasi Geospasial'
        });

        rupabumiindonesia.addTo(map);

        <?php
        // Database connection
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "penduduk_db";

        $conn = new mysqli($servername, $username, $password, $dbname);

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $sql = "SELECT * FROM penduduk";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $firstMarker = $result->fetch_assoc();
            echo "map.setView([" . $firstMarker['latitude'] . ", " . $firstMarker['longitude'] . "], 13);";

            $result->data_seek(0);
            while ($row = $result->fetch_assoc()) {
                $lat = $row['latitude'];
                $lng = $row['longitude'];
                $kecamatan = $row['kecamatan'];
                $luas = $row['luas'];
                $jumlah_penduduk = $row['jumlah_penduduk'];

                echo "L.marker([$lat, $lng]).addTo(map)
                      .bindPopup('<b>Kecamatan: $kecamatan</b><br>Luas: $luas km²<br>Jumlah Penduduk: $jumlah_penduduk');";
            }
        } else {
            echo "console.log('No markers found in the database.');";
        }

        $conn->close();
        ?>
    </script>

    <h2>Data Penduduk Kabupaten Sleman</h2>
    <table>
        <thead>
            <tr>
                <th>Kecamatan</th>
                <th>Luas (km²)</th>
                <th>Jumlah Penduduk</th>
                <th>Longitude</th>
                <th>Latitude</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $conn = new mysqli($servername, $username, $password, $dbname);

            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            $sql = "SELECT * FROM penduduk";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<form method='POST' action=''>";
                    echo "<td><input type='text' name='kecamatan' value='" . $row['kecamatan'] . "'></td>";
                    echo "<td><input type='text' name='luas' value='" . $row['luas'] . "'></td>";
                    echo "<td><input type='text' name='jumlah_penduduk' value='" . $row['jumlah_penduduk'] . "'></td>";
                    echo "<td><input type='text' name='longitude' value='" . $row['longitude'] . "'></td>";
                    echo "<td><input type='text' name='latitude' value='" . $row['latitude'] . "'></td>";
                    echo "<td>
                            <input type='hidden' name='edit_id' value='" . $row['id'] . "'>
                            <button type='submit' class='edit-btn'>Edit</button>
                            <button type='submit' name='delete_id' value='" . $row['id'] . "' class='delete-btn'>Hapus</button>
                          </td>";
                    echo "</form>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='6'>No data found</td></tr>";
            }

            // Handle edit request
            if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_id'])) {
                $edit_id = $_POST['edit_id'];
                $kecamatan = $_POST['kecamatan'];
                $luas = $_POST['luas'];
                $jumlah_penduduk = $_POST['jumlah_penduduk'];
                $longitude = $_POST['longitude'];
                $latitude = $_POST['latitude'];

                $sql = "UPDATE penduduk SET kecamatan='$kecamatan', luas='$luas', jumlah_penduduk='$jumlah_penduduk', longitude='$longitude', latitude='$latitude' WHERE id=$edit_id";
                if ($conn->query($sql) === TRUE) {
                    echo "<script>alert('Record updated successfully');</script>";
                    echo "<script>window.location.href = window.location.href;</script>";
                } else {
                    echo "Error updating record: " . $conn->error;
                }
            }

            // Handle delete request
            if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_id'])) {
                $delete_id = $_POST['delete_id'];

                $sql = "DELETE FROM penduduk WHERE id=$delete_id";
                if ($conn->query($sql) === TRUE) {
                    echo "<script>alert('Record deleted successfully');</script>";
                    echo "<script>window.location.href = window.location.href;</script>";
                } else {
                    echo "Error deleting record: " . $conn->error;
                }
            }

            $conn->close();
            ?>
        </tbody>
    </table>
</body>

</html>