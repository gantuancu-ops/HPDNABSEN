<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validasi Lokasi Presensi</title>
    
    <!-- Google Fonts & Font Awesome untuk Ikon -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            color: #333;
        }

        .card {
            background: rgba(255, 255, 255, 0.95);
            padding: 40px 30px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            max-width: 450px;
            width: 100%;
            text-align: center;
            transition: all 0.3s ease;
        }

        .icon-box {
            font-size: 50px;
            color: #667eea;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }

        h2 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 15px;
            color: #4a5568;
        }

        p#status {
            font-size: 16px;
            color: #718096;
            line-height: 1.6;
            margin-bottom: 25px;
        }

        /* Animasi Loading Spinner */
        .spinner {
            width: 50px;
            height: 50px;
            border: 5px solid #edf2f7;
            border-top: 5px solid #667eea;
            border-radius: 50%;
            margin: 0 auto 20px auto;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Style untuk tombol coba lagi jika error/gagal */
        .btn-retry {
            display: inline-block;
            background: #667eea;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.2s;
            margin-top: 15px;
        }

        .btn-retry:hover {
            background: #5a67d8;
        }

        /* Status Khusus */
        .status-success { color: #48bb78 !important; }
        .status-error { color: #f56565 !important; }
    </style>
</head>
<body>

<div class="card">
    <div id="loading-spinner" class="spinner"></div>
    <div id="icon-container" class="icon-box">
        <i class="fas fa-map-marker-alt"></i>
    </div>
    <h2>Validasi Lokasi</h2>
    <p id="status">Memeriksa lokasi GPS Anda. Mohon tunggu sebentar...</p>
    <div id="action-area"></div>
</div>

<script>
const FORM_URL = "https://fill.boloforms.com/signature/103DlDoLGps2P1AxCTPpvN6G7FRbPOYp54kp7zb0UKR0?p=view";
const TARGET_LAT = -5.422639;
const TARGET_LNG = 119.438212;
const RADIUS_METER = 100;

const statusText = document.getElementById("status");
const spinner = document.getElementById("loading-spinner");
const iconContainer = document.getElementById("icon-container");
const actionArea = document.getElementById("action-area");

function getDistance(lat1, lon1, lat2, lon2){
    const R = 6371000; // Radius bumi dalam meter
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
              Math.cos(lat1 * Math.PI/180) * Math.cos(lat2 * Math.PI/180) *
              Math.sin(dLon/2) * Math.sin(dLon/2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    return R * c;
}

function periksaLokasi() {
    // Reset Tampilan ke mode loading
    spinner.style.display = "block";
    iconContainer.innerHTML = '<i class="fas fa-map-marker-alt"></i>';
    iconContainer.style.color = "#667eea";
    statusText.className = "";
    statusText.innerHTML = "Memeriksa lokasi GPS Anda. Mohon tunggu sebentar...";
    actionArea.innerHTML = "";

    if(navigator.geolocation){
        navigator.geolocation.getCurrentPosition(
            function(position){
                spinner.style.display = "none";
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                const distance = getDistance(lat, lng, TARGET_LAT, TARGET_LNG);

                if(distance <= RADIUS_METER){
                    iconContainer.innerHTML = '<i class="fas fa-check-circle"></i>';
                    iconContainer.style.color = "#48bb78";
                    statusText.innerHTML = "<b>Lokasi Valid!</b><br>Mengalihkan Anda ke halaman formulir...";
                    statusText.className = "status-success";

                    setTimeout(function(){
                        window.location.href = FORM_URL;
                    }, 1500);
                } else {
                    iconContainer.innerHTML = '<i class="fas fa-times-circle"></i>';
                    iconContainer.style.color = "#f56565";
                    statusText.innerHTML = "<b>Akses Ditolak.</b><br>Anda berada di luar area yang diizinkan.<br><span style='font-size:14px; display:block; margin-top:10px;'>Jarak Anda saat ini: <b>" + Math.round(distance) + " meter</b> dari target (Maksimal " + RADIUS_METER + "m).</span>";
                    statusText.className = "status-error";
                    actionArea.innerHTML = '<button class="btn-retry" onclick="periksaLokasi()">Coba Lagi</button>';
                }
            },
            function(error){
                spinner.style.display = "none";
                iconContainer.innerHTML = '<i class="fas fa-exclamation-triangle"></i>';
                iconContainer.style.color = "#ecc94b";
                statusText.innerHTML = "<b>Gagal mendapatkan GPS.</b><br>Pastikan GPS Anda aktif dan Anda telah memberikan izin akses lokasi pada browser.";
                actionArea.innerHTML = '<button class="btn-retry" onclick="periksaLokasi()">Aktifkan & Coba Lagi</button>';
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            }
        );
    } else {
        spinner.style.display = "none";
        iconContainer.innerHTML = '<i class="fas fa-ban"></i>';
        iconContainer.style.color = "#f56565";
        statusText.innerHTML = "Browser tidak mendukung Geolocation.";
    }
}

// Jalankan fungsi saat web pertama kali dimuat
window.onload = periksaLokasi;
</script>

</body>
</html>