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

        var $latitudeInput = $("#lat");
        var $longitudeInput = $("#lng");
        var $direccionInp = $("#calle"),
            $localidadInp = $("#localidad"),
            $zonaSel = $("#zona");
        var $showStreet = $("#showStreet");

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
            $.getJSON(
                `https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(address)}&format=json&limit=1`,
                function (data) {
                    console.log('from geocodeAddress', data);
                    if (data.length > 0) {
                        var lat = parseFloat(data[0].lat);
                        var lon = parseFloat(data[0].lon);
                        moveMarkerTo([lon, lat]);

                        $latitudeInput.val(lat);
                        $longitudeInput.val(lon);

                        $showStreet.html(`<strong>Ubicación encontrada: </strong><br> ${data[0].display_name}`);
                        $('#search-button').text('Buscar');

                        var gmapUrl = `https://maps.google.com/?q=${lat},${lon}&ll=${lat},${lon}&z=20`
                        $('#gmaps').val(gmapUrl);

                    } else {
                        $('#search-button').text('Buscar');
                        var gmapUrl = `https://www.google.com.ar/maps/search/${encodeURIComponent(address)}`;
                        $("#gmapBtn").attr('href', gmapUrl);
                        $('#mapError').modal('show');


                    }
                }
            ).fail(function (error) {
                console.error("Error al obtener la dirección:", error);
            });
        }

        $("#search-button").on("click", function () {
            var street = $("#street").val();
            if (street) {
                $('#search-button').text('Buscando..');
                geocodeAddress(street);
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

            // Obtener la latitud y longitud
            var lat = lonLat[1];
            var lon = lonLat[0];

            console.log("Nueva latitud: " + lat);
            console.log("Nueva longitud: " + lon);

            // Puedes actualizar los campos de tu formulario o realizar otras acciones
            document.getElementById('lat').value = lat;
            document.getElementById('lng').value = lon;

            $('#lon').value = lon;
            $('#lat').value = lat;
            var gmapUrl = `https://maps.google.com/?q=${lat},${lon}&ll=${lat},${lon}&z=20`
            $('#gmaps').val(gmapUrl);

        });

    });
})(jQuery)