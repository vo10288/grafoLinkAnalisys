<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Grafo Geografico</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet.polylineDecorator/dist/leaflet.polylineDecorator.css" />
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
    <script src="https://cdn.jsdelivr.net/npm/leaflet.polylineDecorator/dist/leaflet.polylineDecorator.min.js"></script>
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
            var markers = [];
            var relations = [];

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
                            var markerSocieta = L.marker([latSocieta, lngSocieta], { icon: getMarkerIcon(societa) }).addTo(map);
                            markerSocieta.bindPopup("<b>" + societa + "</b><br>" + indirizzoSocieta);
                            markers.push(markerSocieta);

                            // Geocodifica l'indirizzo dell'amministratore
                            geocodeAddress(geocoder, indirizzoAmministratore, function (errorAmministratore, latAmministratore, lngAmministratore) {
                                if (!errorAmministratore) {
                                    // Crea il marcatore per l'amministratore
                                    var markerAmministratore = L.marker([latAmministratore, lngAmministratore], { icon: getMarkerIcon(amministratore) }).addTo(map);
                                    markerAmministratore.bindPopup("<b>" + amministratore + "</b><br>" + indirizzoAmministratore);
                                    markers.push(markerAmministratore);

                                    // Collega i marcatori con una linea retta
                                    var relation = {
                                        societa: societa,
                                        amministratore: amministratore,
                                        start: { lat: latSocieta, lng: lngSocieta },
                                        end: { lat: latAmministratore, lng: lngAmministratore }
                                    };
                                    relations.push(relation);

                                    // Crea il layer per le linee rette delle relazioni
                                    var relationLayer = L.polylineDecorator([
                                        [latSocieta, lngSocieta],
                                        [latAmministratore, lngAmministratore]
                                    ], {
                                        patterns: [{
                                            offset: '100%',
                                            repeat: 0,
                                            symbol: L.Symbol.arrowHead({
                                                pixelSize: 10,
                                                polygon: false,
                                                pathOptions: { stroke: true, color: 'black', weight: 1 }
                                            })
                                        }]
                                    }).addTo(map);

                                    // Aggiungi il layer delle linee rette al gruppo delle relazioni
                                    relationLayer.addTo(mapRelationGroup);
                                } else {
                                    console.log(errorAmministratore);
                                }
                            });
                        } else {
                            console.log(errorSocieta);
                        }
                    });
                }
            });

            // Crea un gruppo per le relazioni
            var mapRelationGroup = L.layerGroup().addTo(map);

            // Funzione per centrare la mappa sui marcatori
            function fitMapToMarkers() {
                var bounds = L.latLngBounds(markers.map(function (marker) {
                    return marker.getLatLng();
                }));
                map.fitBounds(bounds);
            }

            // Centra la mappa sui marcatori dopo il caricamento dei dati
            fitMapToMarkers();
        }

        // Funzione per ottenere l'icona del marcatore in base all'entità
        function getMarkerIcon(entity) {
            var colors = ['red', 'green', 'blue', 'yellow', 'orange', 'purple', 'gray'];
            var index = Math.abs(hashCode(entity)) % colors.length;
            var color = colors[index];
            return L.icon({
                iconUrl: 'https://cdn.rawgit.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-' + color + '.png',
                shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
                iconSize: [25, 41],
                iconAnchor: [12, 41],
                popupAnchor: [1, -34],
                shadowSize: [41, 41]
            });
        }

        // Funzione per calcolare l'hash code di una stringa
        function hashCode(str) {
            var hash = 0;
            if (str.length === 0) {
                return hash;
            }
            for (var i = 0; i < str.length; i++) {
                var char = str.charCodeAt(i);
                hash = ((hash << 5) - hash) + char;
                hash = hash & hash; // Converti in un intero a 32 bit
            }
            return hash;
        }
    </script>
</body>
</html>
