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

        // Creazione della mappa e centratura su coordinate di default
        var map = L.map('map').setView([44.50, 11.09], 9);

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

            // Array per salvare le posizioni dei marcatori
            var markers = [];

            // Oggetto per salvare le relazioni tra i marcatori
            var relations = {};

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
                            // Crea il marcatore per la società con un colore diverso per ogni gruppo di relazione
                            var markerSocieta = L.marker([latSocieta, lngSocieta], { icon: getMarkerIcon(societa) }).addTo(map);
                            markerSocieta.bindPopup("<b>" + societa + "</b><br>" + indirizzoSocieta);

                            // Aggiungi il marcatore all'array
                            markers.push(markerSocieta);

                            // Aggiungi la relazione tra la società e l'amministratore
                            addRelation(societa, amministratore);
                        } else {
                            console.log(errorSocieta);
                        }
                    });

                    // Geocodifica l'indirizzo dell'amministratore
                    geocodeAddress(geocoder, indirizzoAmministratore, function (errorAmministratore, latAmministratore, lngAmministratore) {
                        if (!errorAmministratore) {
                            // Crea il marcatore per l'amministratore con un colore diverso per ogni gruppo di relazione
                            var markerAmministratore = L.marker([latAmministratore, lngAmministratore], { icon: getMarkerIcon(amministratore) }).addTo(map);
                            markerAmministratore.bindPopup("<b>" + amministratore + "</b><br>" + indirizzoAmministratore);

                            // Aggiungi il marcatore all'array
                            markers.push(markerAmministratore);

                            // Aggiungi la relazione tra la società e l'amministratore
                            addRelation(societa, amministratore);
                        } else {
                            console.log(errorAmministratore);
                        }
                    });
                }
            });

            // Collega le relazioni correlate con una linea retta
            connectRelations(relations);

            // Centra la mappa sulla posizione dei marcatori
            if (markers.length > 0) {
                var group = new L.featureGroup(markers);
                map.fitBounds(group.getBounds());
            }
        }

        // Funzione per aggiungere una relazione tra due entità
        function addRelation(entity1, entity2) {
            if (!relations[entity1]) {
                relations[entity1] = [];
            }

            if (!relations[entity2]) {
                relations[entity2] = [];
            }

            relations[entity1].push(entity2);
            relations[entity2].push(entity1);
        }

        // Funzione per collegare le relazioni correlate con una linea retta
        function connectRelations(relations) {
            for (var entity1 in relations) {
                var relatedEntities = relations[entity1];

                for (var i = 0; i < relatedEntities.length; i++) {
                    var entity2 = relatedEntities[i];

                    // Recupera i marcatori delle entità correlate
                    var marker1 = findMarkerByEntity(entity1);
                    var marker2 = findMarkerByEntity(entity2);

                    if (marker1 && marker2) {
                        // Collega i marcatori con una linea retta
                        var line = L.polyline([marker1.getLatLng(), marker2.getLatLng()], { color: 'blue' }).addTo(map);
                    }
                }
            }
        }

        // Funzione per trovare un marcatore dato il nome dell'entità
        function findMarkerByEntity(entity) {
            for (var i = 0; i < markers.length; i++) {
                var marker = markers[i];
                if (marker.options.title === entity) {
                    return marker;
                }
            }
            return null;
        }

        // Funzione per ottenere l'icona del marcatore in base all'entità
        function getMarkerIcon(entity) {
            var colors = ['red', 'green', 'blue', 'yellow', 'orange', 'purple', 'gray'];
            var index = Math.abs(hashCode(entity)) % colors.length;
            var color = colors[index];
            return L.icon({
                iconUrl: 'https://cdn.rawgit.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-' + color + '.png',
                shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
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
