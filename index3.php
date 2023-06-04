<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Grafo Geografico</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <style>
        #map {
            height: 600px;
        }
    </style>
</head>
<body>
    <div id="map"></div>

    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDxK7M6ps41HwY-QRLxPBvdYGLcwukmG-s&libraries=places"></script>
    <script>
        // Dati di esempio
        var dati = [
            ['Società A', 'Via Ferrari,Bologna, Italy', 'Amministratore 1', 'Via Edoardo Weber 1, Bologna, Italy'],
            ['Società B', 'Via Colonna 1, Roma, Italy', 'Amministratore 2', 'Via Bologna 1, Roma, Italy'],
            ['Società C', 'Via Bologna 1, Firenze, Italy', 'Amministratore 1', 'Via Edoardo Weber 1, Bologna, Italy'],
            // Aggiungi altri dati se necessario
        ];

        // Creazione della mappa
        var map = L.map('map').setView([51.505, -0.09], 13);

        // Aggiunta di una mappa di base (es. OpenStreetMap)
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // Geocodifica l'indirizzo e aggiunge i marcatori alla mappa
        var geocoder = new google.maps.Geocoder();

        dati.forEach(function (riga) {
            var nome = riga[0];
            var indirizzo = riga[1];

            geocoder.geocode({ address: indirizzo }, function (results, status) {
                if (status === google.maps.GeocoderStatus.OK && results.length > 0) {
                    var latitudine = results[0].geometry.location.lat();
                    var longitudine = results[0].geometry.location.lng();

                    var marker = L.marker([latitudine, longitudine]).addTo(map);
                    marker.bindPopup("<b>" + nome + "</b><br>" + indirizzo);
                }
            });
        });
    </script>
</body>
</html>
