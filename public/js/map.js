; (function ($) {
    $(document).ready(function () {
        // Manejar la variable global
        const spsm_stores_data = spsm_data.stores_data ? JSON.parse(spsm_data.stores_data) : [];
        const public_path = spsm_data.path ? spsm_data.path : null
        const mapElement = $("#map")
        console.log(public_path);
        if (!mapElement.length) {
            return
        }

        console.log("map inicializado")
        // Inicializar el mapa de OpenLayers en Buenos Aires, Argentina
        const defaultLat = -34.6073387 // Latitud de Buenos Aires
        const defaultLng = -58.4432852 // Longitud de Buenos Aires

        ////////////////////////    INIT MAPA START   ////////////////////////
        var map = new ol.Map({
            target: "map",
            layers: [
                new ol.layer.Tile({
                    source: new ol.source.OSM()
                })
            ],
            view: new ol.View({
                center: ol.proj.fromLonLat([defaultLng, defaultLat]), // Coordenadas de Buenos Aires
                zoom: 6
            })
        })

        ////////////////////////    CREAR MARCADOR/S START   ////////////////////////
        // Array para almacenar las coordenadas (lat, lng)
        var coordenadas = []

        // Supongamos que 'spsm_stores_data' es el array con los datos de las tiendas
        if (spsm_stores_data) {
            $.each(spsm_stores_data, function (i, data) {
                // Asegúrate de que las propiedades 'latitud' y 'longitud' existen
                if (data.latitud && data.longitud) {
                    coordenadas.push([data.longitud, data.latitud]) // Se usa [lng, lat] para OpenLayers
                }
            })
        }

        // Crear un array vacío para las características de los marcadores
        let features = []

        // Recorrer el array de coordenadas y crear un marcador para cada tienda
        $.each(coordenadas, function (i, coordenada) {
            const marker = new ol.Feature({
                geometry: new ol.geom.Point(ol.proj.fromLonLat(coordenada)) // Convertir a proyección
            })

            features.push(marker) // Añadir el marcador al array de características
        })

        // Crear la fuente de los marcadores con las características
        var markerSource = new ol.source.Vector({
            features: features // Usar todas las características
        })

        // Crear la capa de los marcadores
        var markerLayer = new ol.layer.Vector({
            source: markerSource,
            style: new ol.style.Style({
                image: new ol.style.Icon({
                    anchor: [0.5, 1],
                    src: "https://cdn-icons-png.flaticon.com/512/684/684908.png", // Icono del marcador
                    scale: 0.07 // Tamaño del ícono
                })
            })
        })
        $(document).on("click", ".tienda-btn", function () {
            const tiendaId = String($(this).data("id"))

            // Asegúrate de que los datos de tiendas están correctamente disponibles
            console.log(spsm_stores_data)

            // Busca la tienda por su id
            const tiendaData = spsm_stores_data.find((store) => store.id === tiendaId)

            if (tiendaData) {
                // Extrae las coordenadas de latitud y longitud
                const tiendaCoords = [tiendaData.longitud, tiendaData.latitud]

                // Transformar las coordenadas a la proyección correcta
                const projectedCoords = ol.proj.fromLonLat(tiendaCoords)

                // Mantener el mismo nivel de zoom
                const currentZoom = map.getView().getZoom()
                const newZoom = 14
                // Animar el movimiento del mapa
                map.getView().animate({
                    center: projectedCoords, // Coordenadas de destino
                    zoom: newZoom, // Mantener el zoom actual
                    duration: 1000 // Duración de la animación en milisegundos
                })
            } else {
                console.log("No se encontró la tienda con el ID:", tiendaId)
            }
        })

        // Agregar la capa de marcadores al mapa
        map.addLayer(markerLayer)
    })
})(jQuery)
