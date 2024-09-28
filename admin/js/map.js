(function ($) {
    $(document).ready(function () {
        var $mapElement = $('#map');

        if (!$mapElement.length) {
            return;
        }

        // Inicializar el mapa de OpenLayers en Buenos Aires, Argentina
        const defaultLat = -34.6073387; // Latitud de Buenos Aires
        const defaultLng = -58.4432852; // Longitud de Buenos Aires

        ////////////////////////    INIT MAPA START   ////////////////////////
        var map = new ol.Map({
            target: "map",
            layers: [
                new ol.layer.Tile({
                    source: new ol.source.OSM(),
                }),
            ],
            view: new ol.View({
                center: ol.proj.fromLonLat([defaultLng, defaultLat]), // Coordenadas de Buenos Aires
                zoom: 12,
            }),
        });

        ////////////////////////    CREAR MARCADOR/S START   ////////////////////////
        var marker = new ol.Feature({
            geometry: new ol.geom.Point(ol.proj.fromLonLat([defaultLng, defaultLat])), // Coordenadas de Buenos Aires
        });

        var markerSource = new ol.source.Vector({
            features: [marker],
        });

        var markerLayer = new ol.layer.Vector({
            source: markerSource,
            style: new ol.style.Style({
                image: new ol.style.Icon({
                    anchor: [0.5, 1],
                    src: "https://cdn-icons-png.flaticon.com/512/684/684908.png", // Icono del marcador
                    scale: 0.07,
                }),
            }),
        });

        map.addLayer(markerLayer);

        ////////////////////////    CONTENEDOR DEL POPUP  START  ////////////////////////
        var popup = new ol.Overlay({
            element: $('#popup')[0],
            autoPan: true,
            autoPanAnimation: {
                duration: 250,
            },
        });
        map.addOverlay(popup);

        // Al hacer clic en el marcador
        marker.on('click', function (evt) {
            var coordinate = evt.target.getGeometry().getCoordinates();
            $('#popup-content').html('Hola mundo desde el marcador');
            popup.setPosition(coordinate);
            $('#popup').show();
        });

        // Cerrar el popup
        $('#popup-closer').on('click', function () {
            popup.setPosition(undefined);
            this.blur();
            return false;
        });

        ////////////////////////    OL EVENTOS START   ////////////////////////

        var dragInteraction = new ol.interaction.Modify({
            source: markerSource,
        });

        map.addInteraction(dragInteraction);

        const $latitudeInput = $("#lat");
        const $longitudeInput = $("#lng");
        const $showStreet = $("#showStreet");

        dragInteraction.on("modifyend", function (event) {
            var markerCoords = marker.getGeometry().getCoordinates();
            updateMarkerInfo(markerCoords);
        });


        function moveMarkerTo(coords) {
            var lonLat = ol.proj.fromLonLat(coords);
            marker.setGeometry(new ol.geom.Point(lonLat));
            map.getView().setCenter(lonLat);
            const zoom = map.getView().getZoom();
            map.getView().animate({
                zoom: zoom == 12 ? map.getView().getZoom() + 3 : map.getView().getZoom(),
                duration: 350
            })
        }

        function geocodeAddress(address) {
            const url = `https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(address)}&format=json&limit=1`;

            fetch(url)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`Error en la solicitud: ${response.statusText}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.length === 0) {
                        showError(address)
                        throw new Error("No se encontraron resultados para la dirección.");
                    }

                    // Procesa la respuesta si se encuentran datos
                    handleGeocodeResponse(data); // data es el primer resultado, no el obj en si
                })
                .catch(error => {
                    console.error(error.message);
                });
        }


        function handleGeocodeResponse(data) {
            console.log('from handleGeocodeResponse: ', data);
            if (data.length > 0) {
                const lat = parseFloat(data[0].lat);
                const lon = parseFloat(data[0].lon);
                updateMap(lat, lon, data[0].display_name);
            } else {

                showError(data);
            }

        }

        function updateMap(lat, lon, displayName) {
            moveMarkerTo([lon, lat]);

            $latitudeInput.val(lat);
            $longitudeInput.val(lon);
            $showStreet.html(`<strong>Ubicación encontrada: </strong><br> ${displayName}`);
            $('#search-button').text('Buscar');

            const gmapUrl = `https://maps.google.com/?q=${lat},${lon}&ll=${lat},${lon}&z=20`;
            $('#gmaps').val(gmapUrl);
        }

        function showError(address) {
            $('#search-button').text('Buscar');
            const gmapUrl = `https://www.google.com.ar/maps/search/${encodeURIComponent(address)}`;
            $("#gmapBtn").attr('href', gmapUrl);
            $('#mapError').modal('show');
        }


        $("#search-button").on("click", function () {
            console.log('clicked search button');

            var street = $("#street").val().trim();
            var streetNumber = $("#streetNumber").val().trim();
            var locality = $("#locality").val().trim();
            var city = $("#city").val().trim();
            var province = $("#province").val().trim();

            if (street) {
                $('#search-button').text('Buscando..');
                // Construir la dirección con los valores existentes
                var addressParts = [
                    street,
                    streetNumber,
                    locality,
                    city,
                    province,
                    "Argentina"
                ];
                // Filtrar los valores vacíos y unir con comas
                var fullAddress = addressParts.filter(Boolean).join(', ');
                console.log(`Dirección buscada: ${fullAddress}`);
                geocodeAddress(fullAddress);
                $('#direccion').val(street);
            } else {
                alert("Por favor, ingresa una dirección.");
            }
        });


        // Crear la interacción de Translate para mover el marcador
        var translate = new ol.interaction.Translate({
            features: new ol.Collection([marker]), // Pasar el marcador como la característica que queremos mover
        });


        map.addInteraction(translate);

        // Evento que se dispara al terminar de mover el marcador
        translate.on('translateend', function (event) {
            // Obtener las coordenadas actualizadas del marcador
            var newCoords = event.features.item(0).getGeometry().getCoordinates();
            var lonLat = ol.proj.toLonLat(newCoords);

            // Animar el mapa a la nueva ubicación
            map.getView().animate({
                center: ol.proj.fromLonLat(lonLat),  // Convertir lonLat para que sea compatible con el sistema de proyección
                duration: 1000
            });

            // Obtener la latitud y longitud
            var lat = lonLat[1];
            var lon = lonLat[0];

            // Llamar a la función para obtener la dirección basada en las coordenadas
            getAddressFromCoordinate(lon, lat);

            // Actualizar los campos del formulario
            $('#lat').val(lat);  // Usando jQuery para actualizar el valor
            $('#lng').val(lon);

            // Actualizar el campo de Google Maps URL
            var gmapUrl = `https://maps.google.com/?q=${lat},${lon}&ll=${lat},${lon}&z=20`;
            $('#gmaps').val(gmapUrl);
        });


        function getAddressFromCoordinate(lon, lat) {


            // URL de la API de Nominatim
            var url = `https://nominatim.openstreetmap.org/reverse?format=json&lon=${lon}&lat=${lat}`;

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data && data.display_name) {
                        // Aquí puedes mostrar la dirección en el UI
                        $showStreet.html(`<strong>Ubicación encontrada: </strong><br> ${data.display_name}`);
                    } else {
                        console.error('No se encontró la dirección.');
                    }
                })
                .catch(error => {
                    console.error('Error al obtener la dirección:', error);
                });
        }
    });
})(jQuery)