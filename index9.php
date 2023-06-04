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
    <div>
        <form action="" method="post" enctype="multipart/form-data">
            <input type="file" name="file" accept=".txt">
            <button type="submit">Carica file</button>
        </form>
    </div>

    <div id="map"></div>

    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
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

            var markers = {}; // Mappa per tenere traccia dei marcatori creati per ogni entità
            var relations = []; // Array per tenere traccia delle relazioni

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
                            var markerIdSocieta = societa + '|' + indirizzoSocieta;
                            var markerSocieta;

                            if (markers.hasOwnProperty(markerIdSocieta)) {
                                // Incrementa il numero di occorrenze
                                markers[markerIdSocieta].count++;
                                markerSocieta = markers[markerIdSocieta].marker;
                            } else {
                                // Crea un nuovo marcatore
                                var markerColor = getRandomColor();
                                markerSocieta = L.marker([latSocieta, lngSocieta], { icon: createMarkerIcon(markerColor) }).addTo(map);
                                markerSocieta.bindPopup("<b>" + societa + " (" + markers[markerIdSocieta].count + ")" + "</b><br>" + indirizzoSocieta);

                                // Aggiungi il marcatore alla mappa dei marcatori
                                markers[markerIdSocieta] = {
                                    marker: markerSocieta,
                                    count: 1
                                };
                            }

                            // Aggiungi le relazioni
                            relations.push([markerSocieta, amministratore, indirizzoAmministratore]);
                        } else {
                            console.log(errorSocieta);
                        }

                        // Disegna le relazioni
                        drawRelations();
                    });

                    // Geocodifica l'indirizzo dell'amministratore
                    geocodeAddress(geocoder, indirizzoAmministratore, function (errorAmministratore, latAmministratore, lngAmministratore) {
                        if (!errorAmministratore) {
                            var markerIdAmministratore = amministratore + '|' + indirizzoAmministratore;
                            var markerAmministratore;

                            if (markers.hasOwnProperty(markerIdAmministratore)) {
                                // Incrementa il numero di occorrenze
                                markers[markerIdAmministratore].count++;
                                markerAmministratore = markers[markerIdAmministratore].marker;
                            } else {
                                // Crea un nuovo marcatore
                                var markerColor = getRandomColor();
                                markerAmministratore = L.marker([latAmministratore, lngAmministratore], { icon: createMarkerIcon(markerColor) }).addTo(map);
                                markerAmministratore.bindPopup("<b>" + amministratore + " (" + markers[markerIdAmministratore].count + ")" + "</b><br>" + indirizzoAmministratore);

                                // Aggiungi il marcatore alla mappa dei marcatori
                                markers[markerIdAmministratore] = {
                                    marker: markerAmministratore,
                                    count: 1
                                };
                            }

                            // Aggiungi le relazioni
                            relations.push([markerAmministratore, societa, indirizzoSocieta]);
                        } else {
                            console.log(errorAmministratore);
                        }

                        // Disegna le relazioni
                        drawRelations();
                    });
                }
            });

            // Funzione per disegnare le relazioni
            function drawRelations() {
                relations.forEach(function (relation) {
                    var marker1 = relation[0];
                    var marker2Name = relation[1];
                    var marker2Address = relation[2];

                    // Trova il marcatore corrispondente al nome e all'indirizzo
                    var markerId = marker2Name + '|' + marker2Address;
                    var marker2 = markers[markerId].marker;

                    // Crea una linea retta tra i due marcatori
                    var line = L.polyline([marker1.getLatLng(), marker2.getLatLng()], { color: 'blue' }).addTo(map);
                });
            }

            // Funzione per creare un'icona personalizzata per i marcatori
            function createMarkerIcon(color) {
                return L.icon({
                    iconUrl: 'https://cdn.rawgit.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x.png',
                    iconSize: [25, 41],
                    iconAnchor: [12, 41],
                    popupAnchor: [1, -34],
                    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
                    shadowSize: [41, 41],
                    shadowAnchor: [13, 41],
                    className: 'marker-' + color
                });
            }

            // Funzione per generare un colore casuale in formato esadecimale
            function getRandomColor() {
                var letters = '0123456789ABCDEF';
                var color = '#';
                for (var i = 0; i < 6; i++) {
                    color += letters[Math.floor(Math.random() * 16)];
                }
                return color;
            }
        }
    </script>
</body>
</html>
