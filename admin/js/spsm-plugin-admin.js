(function ($) {
  "use strict";

  $(document).ready(function () {
    /**
     * !IMPORTANTE INICIALIZAR VARIABLE GLOBAL
     */
    const spsm_stores_data = spsm_data.stores_data ? JSON.parse(spsm_data.stores_data) : [];
    const spsm_stores_empty = spsm_data.stores_empty ? spsm_data.stores_empty : false;
    const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');

    $(popoverTriggerList).ready()
    if (popoverTriggerList.length) {
      const popoverList = [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl));
    }

    // Mostrar mensaje "No hay puntos registrados" si no los hay
    if (spsm_stores_empty) {
      $('#storesEmpty').removeClass('hidden')
      $('#storesEmpty').text('No hay puntos registrados')
    }

    // Cargar todas las tiendas en la vista del administrador
    // const store_data = $.parseJSON(spsm_stores_data)
    const store_data = spsm_stores_data;
    $.each(store_data, function (i, store) {
      $('#store-items').append(store_item_html(store.id, store.tienda_nombre))
    })


    function store_item_html(id, nombre) {

      let html = `
							<li class="store-item grid grid-cols-5 sm:grid-cols-8 items-center border text-center w-full mb-1">
									<span class="border-r col-span-1 py-2 font-bold">${id}</span>
									<span class="store-name border-r col-span-2 sm:col-span-5 py-2 text-start ms-2">${nombre}</span>
                  <div class="grid grid-cols-2 gap-2 col-span-2 p-1">
                    <button id="showItem" data-id="${id}" data-bs-toggle="modal" data-bs-target="#showModal" class="show-store cursor-pointer uppercase col-span-1 bg-green-500 text-white font-semibold py-2 rounded" ><span class="dashicons dashicons-visibility"></span></button>
									  <button data-storeid="${id}" id="deleteItem" class="delete-btn cursor-pointer uppercase col-span-1 bg-red-500 text-white font-semibold py-2 rounded"><span class="dashicons dashicons-trash" ></span></button>
                  </div>
								</li>
      `
      return html;
    }

  })


  // $(document).on('click', '.show-store', function (e) {
  //   console.log('click en show store');


  //   $.ajax({
  //     url: spsm_ajax_obj.ajax_url,
  //     data: data,
  //     success: function(result){
  //       console.log(result);
  //     }
  //   })

  // })



  // jQuery UI Functionality

  function matchCustom(params, data) {

    // If there are no search terms, return all of the data
    if ($.trim(params.term) === '') {
      return data;
    }

    // Do not display the item if there is no 'text' property
    if (typeof data.text === 'undefined') {
      return null;
    }

    // `params.term` should be the term that is used for searching
    // `data.text` is the text that is displayed for the data object

    // Convertir ambos, el término de búsqueda y el texto, a minúsculas
    var searchTerm = params.term.toLowerCase();
    var textData = data.text.toLowerCase();

    if (textData.indexOf(searchTerm) > -1) {
      var modifiedData = $.extend({}, data, true);
      modifiedData.text += '  ✓';

      // You can return modified objects from here
      // This includes matching the `children` how you want in nested data sets
      console.log(modifiedData);;
      return modifiedData;
    }

    // Return `null` if the term should not be displayed
    return null;
  }

  $("#localidad").select2({
    matcher: matchCustom,
    placeholder: '-- Seleccionar Localidad -- ',
    allowClear: true
  });

  console.log($('#updateLocalidad'));

})(jQuery);
