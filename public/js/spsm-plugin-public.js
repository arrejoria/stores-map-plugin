; (function ($) {
	"use strict"

	/**
	 * All of the code for your public-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

	$(document).ready(function () {
		// Codigo que se ejecuta al cargar la pagina
		if (
			(!spsm_stores_data && spsm_stores_data.length === 0) ||
			(!spsm_localidades_data && spsm_localidades_data.length === 0)
		) {
			return $("#mapContainer").css("display", "none")
		}

		const ALL_SUCURSALES = spsm_stores_data
		const ALL_LOCALIDADES = spsm_localidades_data

		// Inicializar Select2 para los campos en storeFilters
		init_select2_fields(ALL_LOCALIDADES)

		//Cargar todas las localidades en el campo localidades de StoreFilters
		fill_localidades_options(ALL_LOCALIDADES)
		load_sucursales(ALL_SUCURSALES)

		// MOSTRAR INFO DE LA SUCURSAL AL HACER CLICK EN VER INFO
		$(document).on("click", ".tienda-btn", function () {

			let sucursalId = String($(this).data("id")) // Convertir el ID a string
			const store = ALL_SUCURSALES.find((store) => store.id === sucursalId)


			let html = `<div class="relative border-l">
							<h2 class="text-xl uppercase py-3 px-2 font-semibold bg-primary-color text-white">Más información: </h2>
							<div class="info-content flex flex-col gap-3 py-3 px-2 text-sm">
								<div class="grid grid-cols-2 gap-3 border-b pb-2">
									<div class="flex flex-col col-span-1 mb-3">
										<h4 class="font-semibold">Dirección:</h4>
										<p class="info-direccion">${store.direccion}</p>
									</div>
									<div class="flex justify-around gap-3">
										<div>
											<h4 class="font-semibold">Local:</h4>
											<p class="info-direccion">${store.local}</p>
										</div>

										<div>
											<h4 class="font-semibold">Localidad:</h4>
											<p class="info-direccion capitalize">${store.localidad}</p>
										</div>
									</div>
								</div>
								<div class="flex max-md:flex-col gap-3 align-between justify-around">
									<div class="mb-3">
										<div class="flex flex-col gap-y-1">
											${store.tienda_info}
										</div>
									</div>
									<div class="flex md:flex-col items-center gap-2">
										<h4 class="font-semibold mb-3">Ir a la ubicación:</h4>
										<a href="${store.gmaps_url}" target="_blank" class="font-semibold self-end bg-sky-500 px-3 py-1 rounded text-white inline-block w-fit">Ir a Maps</a>
									</div>
								</div>
							</div>
							<a id="closeDescripcion" class="absolute top-2 right-2 text-white p-2 cursor-pointer">✖</a>
						</div>`

			$(".punto-descripcion").html(html)
			$(".punto-descripcion")
				.removeClass("hide-descripcion")
				.addClass(["show-descripcion", "md:row-span-1"])
			$(".punto-mapa").removeClass("md:row-span-2")

			$("html, body").animate({
				scrollTop: $("#storeInfo").offset().top
			}, .8); // Duración de la animación en milisegundos

		})
		$(document).on("click", "#closeDescripcion", function () {
			$(".punto-descripcion")
				.addClass("hide-descripcion")
				.removeClass(["show-descripcion", "md:row-span-1"])
			$(".punto-mapa").addClass("md:row-span-2")
			// $('.punto-descripcion').addClass('hidePuntoDescripcion')
		})



		// Mostrar animacion de scroll down si la cantidad de sucursales es mayor a 5 en la lista
		if (ALL_SUCURSALES.length > 5) {
			active_scroll_img()
		}
		// Ocultar Scroll Gif al realizar scroll en las sucursales
		$(".lista-tiendas").on("scroll", function () {
			hide_scroll_img()
		})
		// Mostrar Scroll Gif
		function active_scroll_img() {
			$("#sucursales").scrollTop(0);

			$(".scrollbar-i").css({
				opacity: ".7",
				display: "block"
			})
		}
		// Ocultar Scroll Gif
		function hide_scroll_img() {
			$(".scrollbar-i").css({
				opacity: "0",
				display: "none"
			})
		}

		function get_store_by_id(data, id) {
			return
		}



		/**
		 * Filtrar sucursales por el valor seleccionado en cada campo y mostrar sus resultados
		 */
		$(document).on("click", "#filterBtn", function () {
			const sucursales = handle_filter_function(ALL_SUCURSALES)

			console.log(sucursales);
			if (sucursales) {
				console.log('Cantidad de sucursales para scroll func:' + sucursales.length);

				sucursales.length > 5 ? active_scroll_img() : hide_scroll_img()
			}
		})

		/**
		 * Resetear campos en stores filter y mostrar todas las sucursales
		 */
		$(document).on("click", "#resetBtn", function () {
			// Resetear el valor de los selects
			$("#local").val(null).trigger("change")
			$("#zona").val(null).trigger("change")
			$("#localidad").val(null).trigger("change")
			$("#noResults").removeClass("show-noresults")

			load_sucursales(ALL_SUCURSALES)
			active_scroll_img();
		})

		/**
		 * Filtra las tiendas con base en los valores de los campos de filtro (local, zona, localidad) y actualiza los resultados.
		 *
		 * @param {Array} storesData - Array de objetos que contienen los datos de las tiendas.
		 */
		function handle_filter_function(storesData) {
			const filterFields = ["local", "zona", "localidad"] // Identificadores de los campos que filtraran las tiendas
			let resultadoTemp = storesData // Almacenar temporalmente todas las tiendas

			// Recorre cada campo de filtro y aplica el filtrado si el campo no está vacío
			$.each(filterFields, function (i, field) {
				// Obtener valor del campo actual
				let value = $(`#${field}`).val()

				// Si el campo tiene un valor, filtra los resultados acumulativamente
				if (value !== "") {
					console.log(`Filtrando por: ${field} con valor: ${value}`)
					resultadoTemp = get_stores_by_filter(resultadoTemp, field, value) // Filtrar basándose en el valor actual del campo
				}
			})

			// Si se encuentran resultados, cargarlos; de lo contrario, mostrar un mensaje de "no encontrado"
			if (resultadoTemp.length > 0) {
				console.log("Resultados encontrados:", resultadoTemp)
				$("#noResults").removeClass("show-noresults")

				return load_sucursales(resultadoTemp) // Cargar los resultados filtrados
			}

			// Si no se encuentran resultados, mostrar el mensaje de "no se encontró nada"
			console.log("No se encontró nada en la búsqueda")
			$("#noResults").addClass("show-noresults")
		}

		/**
		 * Filtra las tiendas cargadas en un objeto en función de una clave y un valor de búsqueda.
		 *
		 * @param {Array} objData - Array de objetos que contienen la información de las tiendas.
		 * @param {string} objKey - Clave del objeto sobre la cual se realizará la búsqueda (por ejemplo, "id", "nombre", etc.).
		 * @param {*} searchValue - Valor a buscar en el campo especificado por objKey.
		 *
		 * @returns {Array} - Un array de tiendas que coinciden con el valor de búsqueda.
		 */
		function get_stores_by_filter(objData, objKey, searchValue) {
			return objData.filter((store) => store[objKey] === searchValue)
		}

		/**
		 *  Función para cargar todas las sucursales en la lista HTML.
		 *  @param {Object[]} dataObj - Array de objetos que representan las sucursales.
		 *  @param {number} dataObj[].id - ID de la sucursal.
		 *  @param {string} dataObj[].tienda_nombre - Nombre de la tienda.
		 *  @param {string} dataObj[].local - Local de la tienda.
		 *  @param {string} dataObj[].localidad - Localidad de la tienda.
		 *  @param {string} dataObj[].zona - Zona de la tienda.
		 *  @param {number} dataObj[].latitud - Latitud de la tienda.
		 *  @param {number} dataObj[].longitud - Longitud de la tienda.
		 *  @param {string} dataObj[].direccion - Dirección de la tienda.
		 *  @param {string} dataObj[].popup_info - Información adicional para el popup.
		 *  @param {string} dataObj[].tienda_info - Información detallada de la tienda.
		 *  @param {string} dataObj[].gmaps_url - URL de Google Maps para la ubicación.
		 *  @param {string} dataObj[].created_at - Fecha de creación de la tienda.
		 *  @returns {void} No retorna ningún valor.
		 */
		function load_sucursales(dataObj) {
			const tiendaListElm = $("#sucursales")
			tiendaListElm.html("")
			$.each(dataObj, function (i, store) {
				const {
					id,
					tienda_nombre,
					local,
					localidad,
					zona,
					latitud,
					longitud,
					direccion,
					popup_info,
					tienda_info,
					gmaps_url,
					created_at
				} = store

				// Crear el HTML para cada sucursal
				let html = `<li class="grid grid-cols-4 items-center gap-1 py-3 px-1 bg-white rounded">
                        <div class="col-span-3 flex gap-2 items-center">
                            <span class="dashicons dashicons-store text-sm"></span>
                            <h3 class="">${tienda_nombre}</h3>
                        </div>
                        <button data-id="${id}" class="tienda-btn col-span-1 border rounded bg-primary-color text-white p-1 text-center">Ver info</button>
                    </li>`

				// Insertar el HTML en la lista de tiendas
				tiendaListElm.append(html)
			})
			const sucursalesCreadas = tiendaListElm[0].children;
			return sucursalesCreadas;
		}

		/**
		 * Rellena el selector de "localidades" con las opciones obtenidas de los datos proporcionados (spsm_localidades_data).
		 *
		 * @param {Object[]} data - Array de objetos que contiene la información de las localidades.
		 * Cada objeto debe tener una propiedad "localidad" para ser usado como valor y texto de las opciones.
		 */
		function fill_localidades_options(data) {
			$.each(data, function (i, obj) {
				const localidad = obj.localidad

				// Verificar si la localidad está vacía
				if (localidad === "") {
					return true // Continúa con la siguiente iteración
				}

				// Crear la opción HTML con el valor y el texto de la localidad
				const html = `<option value="${localidad}">${localidad}</option>`

				// Agregar la opción al select de localidades
				$("#localidad").append(html)
			})
		}

		/**
		 * Inicializa los selectores con Select2 para los campos de local, zona y localidad en el formulario.
		 * Se usa en el template del shortcode.
		 *
		 * @property {jQuery.select2} local - Selector del campo "local", que contiene los locales registrados en el mapa de tiendas.
		 * @property {jQuery.select2} zona - Selector del campo "zona", que contiene las zonas registradas en el mapa de tiendas.
		 * @property {jQuery.select2} localidad - Selector del campo "localidad", que contiene las localidades registradas en el mapa de tiendas.
		 */
		function init_select2_fields() {
			$("#local").select2({
				placeholder: "Seleccionar local",
				width: "100%"
			})

			$("#zona").select2({
				placeholder: "Seleccionar zona",
				width: "100%"
			})

			$("#localidad").select2({
				placeholder: "Seleccionar localidad",
				width: "100%"
			})
		}


	})
})(jQuery)
