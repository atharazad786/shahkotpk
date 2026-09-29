<?php
require_once __DIR__.'/app/bootstrap.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interactive City Map - ShahkotPK</title>
    <link rel="stylesheet" href="/assets/platform-v4.0.0.css?v=430">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        #map { height: 100vh; width: 100%; }
        .v4-public-head { position: absolute; top: 0; left: 0; right: 0; z-index: 1000; background: rgba(255,255,255,0.9); }
    </style>
</head>
<body class="v4-public" style="margin:0; padding:0;">
    <header class="v4-public-head">
        <a href="/">← Back to ShahkotPK</a>
        <b>Explore Shahkot City Map</b>
    </header>
    <div id="map"></div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // Initialize map centered at Shahkot, Punjab, Pakistan
        var map = L.map('map').setView([31.5700, 73.4833], 14);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        // Dummy data for pins
        var businesses = [
            { name: "THQ Hospital Shahkot", lat: 31.5710, lng: 73.4840, category: "Healthcare" },
            { name: "Fri Chicks Shahkot", lat: 31.5695, lng: 73.4855, category: "Food" },
            { name: "Punjab Group of Colleges", lat: 31.5680, lng: 73.4820, category: "Education" }
        ];

        businesses.forEach(function(b) {
            L.marker([b.lat, b.lng]).addTo(map)
                .bindPopup("<b>" + b.name + "</b><br>" + b.category);
        });
    </script>
</body>
</html>
