<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Grafo Geografico</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/1.4.1/MarkerCluster.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/1.4.1/MarkerCluster.Default.css" />
    <style>
        #map {
            height: 600px;
        }
    </style>
</head>
<body>
    <div>
        <form action="" method="post" enctype="multipart/form-data">
            <input type="file" name="file" accept=".txt">
            <button type="submit">Carica file</button>
        </form>
    </div>

    <div id="map"></div>

    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/1.4.1/leaflet.markercluster.js"></script>
        <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDxK7M6ps41HwY-QRLxPBvdYGLcwukmG-s&libraries=places"></script>

    <script>
        // Funzione per geocodificare un indirizzo utilizzando il servizio di Google Maps
        function geocodeAddress(geocoder, address, callback) {
            geocoder.geocode({ address: address }, function (results, status) {
                if (status === google.maps.GeocoderStatus.OK && results.length > 0) {
                    var location = results[0].geometry.location;
                    callback(null, location.lat(), location.lng());
                } else {
                    callback('Errore nella geocodifica dell\'indirizzo: ' + address, null, null);
                }
            });
        }

        // Creazione della mappa
        var map = L.map('map').setView([51.505, -0.09], 13);

        // Aggiunta di una mappa di base (es. OpenStreetMap)
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // Inizializzazione del cluster dei marker
        var markers = L.markerClusterGroup();

        // Elaborazione del file caricato
        var fileInput = document.querySelector('input[type="file"]');
        fileInput.addEventListener('change', function (e) {
            var file = e.target.files[0];
            var reader = new FileReader();

            reader.onload = function (event) {
                var fileContent = event.target.result;
                processData(fileContent);
            };

            reader.readAsText(file);
        });

        // Funzione per elaborare i dati del file
        function processData(data) {
            var rows = data.split('\n');

            // Rimuovi l'intestazione se presente
            if (rows.length > 0) {
                if (rows[0].indexOf(';') === -1) {
                    rows.shift();
                }
            }

            var geocoder = new google.maps.Geocoder();

            rows.forEach(function (row) {
                var fields = row.split(';');
                if (fields.length >= 4) {
                    var societa = fields[0].trim();
                    var indirizzoSocieta = fields[1].trim();
                    var amministratore = fields[2].trim();
                    var indirizzoAmministratore = fields[3].trim();

                    // Geocodifica l'indirizzo della società
                    geocodeAddress(geocoder, indirizzoSocieta, function (errorSocieta, latSocieta, lngSocieta) {
                        if (!errorSocieta) {
                            // Crea il marcatore per la società
                            var markerSocieta = L.marker([latSocieta, lngSocieta]);
                            markers.addLayer(markerSocieta);
                            markerSocieta.bindPopup("<b>" + societa + "</b><br>" + indirizzoSocieta);
                        } else {
                            console.log(errorSocieta);
                        }
                    });

                    // Geocodifica l'indirizzo dell'amministratore
                    geocodeAddress(geocoder, indirizzoAmministratore, function (errorAmministratore, latAmministratore, lngAmministratore) {
                        if (!errorAmministratore) {
                            // Crea il marcatore per l'amministratore
                            var markerAmministratore = L.marker([latAmministratore, lngAmministratore]);
                            markers.addLayer(markerAmministratore);
                            markerAmministratore.bindPopup("<b>" + amministratore + "</b><br>" + indirizzoAmministratore);
                        } else {
                            console.log(errorAmministratore);
                        }
                    });

                    // Collega i marcatori con una linea retta
                    var line = L.polyline([
                        [latSocieta, lngSocieta],
                        [latAmministratore, lngAmministratore]
                    ], { color: 'red' }).addTo(map);
                }
            });

            // Aggiungi il cluster dei marker alla mappa
            map.addLayer(markers);
        }

        // Funzione per creare l'icona personalizzata del marcatore
        function createMarkerIcon(color) {
            return L.icon({
                iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-' + color + '.png',
                iconSize: [25, 41],
                iconAnchor: [12, 41],
                popupAnchor: [1, -34],
                shadowSize: [41, 41]
            });
        }
    </script>
</body>
</html>
