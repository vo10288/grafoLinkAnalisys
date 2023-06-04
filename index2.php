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
    <script>
        // Dati di esempio
        var dati = [
            ['Società A', 'Via Ferrari,Bologna', 'Amministratore 1', 'Via Edoardo Weber 1, Bologna'],
            ['Società B', 'Via Colonna 1, Roma', 'Amministratore 2', 'Via Bologna 1, Roma'],
            ['Società C', 'Via Bologna 1, Firenze', 'Amministratore 1', 'Via Edoardo Weber 1, Bologna'],
            // Aggiungi altri dati se necessario
        ];

        // Creazione della mappa
        var map = L.map('map').setView([51.505, -0.09], 13);

        // Aggiunta di una mappa di base (es. OpenStreetMap)
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // Aggiunta dei marcatori per i nodi del grafo
        dati.forEach(function (riga) {
            var nome = riga[0];
            var indirizzo = riga[1];
            var latitudine = 51.5; // Sostituisci con la latitudine reale dell'indirizzo
            var longitudine = -0.09; // Sostituisci con la longitudine reale dell'indirizzo

            var marker = L.marker([latitudine, longitudine]).addTo(map);
            marker.bindPopup("<b>" + nome + "</b><br>" + indirizzo);
        });
    </script>
</body>
</html>
