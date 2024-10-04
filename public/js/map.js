; (function ($) {
    $(document).ready(function () {
        const spsm_stores_data = spsm_data.stores_data ? JSON.parse(spsm_data.stores_data) : [];
        const public_path = spsm_data.path ? spsm_data.path : null
        const map_marker_icon = spsm_data.marker_icon[0]
        const map_marker_icon_alt = spsm_data.marker_icon[1]
        const mapElement = $("#map")
        if (!mapElement.length) {
            return
        }

        // Inicializar el mapa
        const defaultLat = -34.6073387
        const defaultLng = -58.4432852

        var map = new ol.Map({
            target: "map",
            layers: [
                new ol.layer.Tile({
                    source: new ol.source.OSM(),
                    style: {
                        'fill-color': 'darkgray',
                    }
                })
            ],
            view: new ol.View({
                center: ol.proj.fromLonLat([defaultLng, defaultLat]),
                zoom: 6
            })
        })

        //////////////////////// CREAR MARCADORES ////////////////////////
        let features = [];

        if (spsm_stores_data) {
            $.each(spsm_stores_data, function (i, data) {
                if (data.latitud && data.longitud) {
                    const marker = new ol.Feature({
                        geometry: new ol.geom.Point(ol.proj.fromLonLat([data.longitud, data.latitud]))
                    });
                    features.push(marker);
                }
            })
        }

        var markerSource = new ol.source.Vector({
            features: features
        })

        var normalMarkerStyle = new ol.style.Style({
            image: new ol.style.Icon({
                anchor: [0.5, 1],
                src: map_marker_icon,
                scale: 0.07
            })
        });

        var hoverStyle = new ol.style.Style({
            image: new ol.style.Icon({
                anchor: [0.5, 1],
                src: map_marker_icon_alt,
                scale: 0.04
            })
        });

        var vectorLayer = new ol.layer.Vector({
            source: markerSource,
            style: normalMarkerStyle
        });

        let selectedMarker = null;

        // Listener de click en una sucursal
        $(document).on("click", ".sucursal", function () {
            const tiendaId = String($(this).data("id"));
            const tiendaData = spsm_stores_data.find((store) => store.id === tiendaId);

            if (tiendaData) {
                const tiendaCoords = [tiendaData.longitud, tiendaData.latitud];
                const projectedCoords = ol.proj.fromLonLat(tiendaCoords);
                const newZoom = 14;

                map.getView().animate({
                    center: projectedCoords,
                    zoom: newZoom,
                    duration: 1000
                });

                if (selectedMarker) {
                    selectedMarker.setStyle(normalMarkerStyle);
                }

                // Encontrar el marcador actual
                const currentMarker = vectorLayer.getSource().getFeatures().find(feature => {
                    const coords = feature.getGeometry().getCoordinates();
                    return coords[0] === projectedCoords[0] && coords[1] === projectedCoords[1];
                });

                if (currentMarker) {
                    currentMarker.setStyle(hoverStyle); // Aplicar estilo de selección
                    selectedMarker = currentMarker; // Guardar referencia al marcador seleccionado
                }
            }
        });

        $(document).on('click', '#closeDescripcion', function () {
            // Verificar si hay un marcador seleccionado y restaurar su estilo normal
            if (selectedMarker) {
                selectedMarker.setStyle(normalMarkerStyle); // Restablecer el estilo del marcador seleccionado
                selectedMarker = null; // Limpiar la referencia del marcador seleccionado
            }
        })

        map.addLayer(vectorLayer);
    })
})(jQuery);
