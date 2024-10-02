
(function ($) {
  'use strict'
  $(window).load(function () {
    console.log('ajax file loaded');

    var spsm_stores_data = spsm_data.stores_data > 0 ? JSON.parse(spsm_data.stores_data) : []
    // Enviar datos del punto de interes mediante AJAX 
    $('#crearPuntoForm').on('submit', function (e) {
      e.preventDefault();

      var tienda_descripcion = tinyMCE.get('tiendaDescripcion').getContent(); // Obtener el HTML del editor

      const data = {
        action: 'spsm_handle_ajax', // El nombre de la acción que vincula con el callback PHP
        local: $('#local').val().trim(),
        tienda_nombre: $('#tiendaNombre').val().trim(),
        direccion: $('#direccion').val().trim(),
        localidad: $('#localidad').val().trim(),
        zona: $('#zona').val().trim(),
        tiendaInfo: tienda_descripcion, // Contenido HTML de Quill para la descripción
        latitud: $('#lat').val(),
        longitud: $('#lng').val(),
        gmaps_url: $('#gmaps').val(),
        nonce: spsm_ajax_obj.nonce // Verificación de seguridad
      };

      // Peticion AJAX para insertar nueva punto de interes y obtener la lista entera
      $.ajax({
        url: spsm_ajax_obj.ajax_url, // admin-ajax.php URL
        type: 'POST',
        data: data,
        beforeSend: function (e) {
          console.log('beforeSend handleAjax: ' + e);
          $('#formSpinner').addClass('spinner-border')
        },
        success: function (response) {
          $('#formSpinner').removeClass('spinner-border')
          console.log('spsm_handle_ajax / response: ');
          console.log(response);

          if (response.success) {
            $('#storesEmpty').addClass('hidden');
            const store_data = JSON.parse(response.data.store_data)
            // Iterar todos los puntos en un bucle
            // data.forEach(store => {
            const html = store_item_html(store_data.id, store_data.tienda_nombre);
            $('#store-items').prepend(html);
            // });

            $('#crearPuntoForm')[0].reset()


            spsm_stores_data = [...spsm_stores_data, store_data]
            console.log('spsm_stores length' + spsm_stores_data.length);
          } else {
            console.error('Error al crear la tienda:', response.store_data.message);
          }
          // Haz algo con la respuesta del servidor
        },
        error: function (xhr, status, error) {
          alert('Ocurrio un error al crear el punto de interes. Verifica que todos los campos sean correctos o estén completados.')
        }
      });
    });

    // Funcion AJAX para mostrar los datos de la sucursal seleccionada
    $(document).on('click', '.show-store', function (e) {
      if ($('.modal-backdrop').is(':visible')) {
        $('body').removeClass('modal-open');
        $('.modal-backdrop').remove();
      };

      console.log('click en show store');
      var sucursalId = $(this).data('id');
      $('#sucursalId').val(sucursalId);
      if (!$('#updateSucursalForm').hasClass('hidden')) {
        $('#updateSucursalForm').addClass('hidden')
      }

      // Obtener el contenido de descripcion desde wp editor utilizando tinymce
      // var tienda_descripcion = tinyMCE.get('updateTiendaDescripcion').getContent(); // Obtener el HTML del editor

      const data = {
        action: 'spsm_get_store', // El nombre de la acción que vincula con el callback PHP
        sucursal_id: sucursalId,
        nonce: spsm_ajax_obj.nonce // Verificación de seguridad
      };

      $.ajax({
        url: spsm_ajax_obj.ajax_url,
        type: 'POST',
        data: data,
        beforeSend: function (e) {
          $('#searchInfo').removeClass('hidden').html(`<span class="spinner-border"></span><p>Extrayendo datos de la sucursal</p>`)
        },
        success: function (response) {
          $('#searchInfo').addClass('hidden')
          $('#updateSucursalForm').removeClass('hidden')
          if (response.success) {
            // Aquí se reciben los datos de la tienda (sucursal_data)
            const sucursal = response.data.sucursal_data;

            // Actualizar los campos del formulario con los datos de la sucursal
            $('#updateLocal').val(sucursal.local);
            $('#updateName').val(sucursal.tienda_nombre);
            $('#updateDireccion').val(sucursal.direccion);
            $('#updateLocalidad').val(sucursal.localidad);
            $('#updateZona').val(sucursal.zona);
            $('#updateLat').val(sucursal.latitud);
            $('#updateLng').val(sucursal.longitud);
            $('#updateGmaps').val(sucursal.gmaps_url);

            // Actualizar el contenido del editor de TinyMCE con la descripción de la tienda
            tinymce.get('updateTiendaDescripcion').setContent(sucursal.tienda_info);
          } else {
            console.error('No se encontraron datos de la sucursal.');
          }
        },
        error: function (xhr, status, error) {
          console.error('Error al mostrar la información: ' + error);
        }
      });
    });


    // Funcion AJAX para actualizar los datos de la sucursal seleccionada
    $(document).on('submit', '#updateSucursalForm', function (e) {
      console.log('click en update submit');
      e.preventDefault();

      const sucursalId = $('#sucursalId').val();
      var tienda_descripcion = tinyMCE.get('updateTiendaDescripcion').getContent(); // Obtener el HTML del editor

      const data = {
        action: 'spsm_update_store', // El nombre de la acción que vincula con el callback PHP
        sucursal_id: sucursalId,
        local: $('#updateLocal').val().trim(),
        tienda_nombre: $('#updateName').val().trim(),
        direccion: $('#updateDireccion').val().trim(),
        localidad: $('#updateLocalidad').val().trim(),
        zona: $('#updateZona').val().trim(),
        tiendaInfo: tienda_descripcion, // Contenido HTML del editor TinyMCE
        latitud: $('#updateLat').val(),
        longitud: $('#updateLng').val(),
        gmaps_url: $('#updateGmaps').val(),
        nonce: spsm_ajax_obj.nonce // Verificación de seguridad
      };

      $.ajax({
        url: spsm_ajax_obj.ajax_url,
        type: 'POST',
        data: data,
        beforeSend: function () {
          $('#updateSucursalForm').addClass('hidden');
          $('#searchInfo').removeClass('hidden').html(`<span class="spinner-border"></span><p>Cargando nuevos datos...</p>`);
        },
        success: function (result) {
          console.log(result);

          if (result.success) {
            // Mostrar el mensaje de éxito
            $('#searchInfo').html("<p>Los nuevos datos fueron cargados con éxito. Ya podés cerrar esta ventana</p>");

            // Selecciona el elemento con el atributo data-storeid que coincida con sucursalId
            var storeItem = $(`[data-storeid="${sucursalId}"]`);

            // Actualiza el nombre de la tienda
            storeItem.closest('.store-item').find('.store-name').text(data.tienda_nombre);
          } else {
            // Mostrar mensaje de error si result.success es falso
            $('#searchInfo').html(`<p>Error: ${result.data}</p>`);
            console.error('Error:', result.data);
          }
        },
        error: function (xhr, status, error) {
          // Mostrar mensaje de error en caso de fallo en la solicitud AJAX
          $('#searchInfo').html(`<p>Ocurrió un error: ${error}</p>`);
          console.error('AJAX Error:', error);
        }
      });
    });

    // Funcion AJAX para eliminar puntos de interes del cliente y el servidor
    $(document).on('click', '.delete-btn', function (e) {
      console.log('del btn clicked');
      var storeId = $(this).data('storeid');

      var data = {
        action: 'spsm_delete_store',
        nonce: spsm_ajax_obj.nonce,
        id: storeId
      };
      $.ajax({
        url: spsm_ajax_obj.ajax_url,
        type: 'POST',
        data: data,
        beforeSend: function (e) {
          console.log('eliminando punto ...');
          $(this).html('<span class="spinner-border"></span>'); // Cambia btn a this
        }.bind(this), // Asegura que `this` se refiera al botón
        success: function (response) {
          if (response.success) {
            console.log('punto eliminado');
            console.log(response);
            $(this).closest('li').remove(); // Cambia btn a this

            // Verificar si todos los puntos fueron eliminados
            if (response.data.spsm_stores_empty) {
              $('#storesEmpty').text('No hay puntos registrados')
              $('#storesEmpty').removeClass('hidden');
            }

          } else {
            console.error('Error al eliminar la tienda: ' + response);
          }
        }.bind(this), // Asegura que `this` se refiera al botón
        error: function (xhr, status, error) {
          console.error('Error al procesar la solicitud ajax: ' + error);
          alert('Error al eliminar la tienda, si el error persiste ponte en contacto con el desarrollador.');
        },
      });
    });

  })

  /**
   *  Template HTML para agregar a la lista de sucursales
   *  @param {number} id - id de la sucursal
   *  @param {string} nombre - nombre de la sucursal
   *
   *  @return {string} Devuelve el cuerpo html como string para insertar en el DOM                
   */
  function store_item_html(id, nombre) {
    let html = `
							<li class="store-item grid grid-cols-5 sm:grid-cols-8 items-center border text-center w-full mb-1">
									<span class="border-r col-span-1 py-2 font-bold">${id}</span>
									<span class="border-r col-span-2 sm:col-span-5 py-2 text-start ms-2">${nombre}</span>
                  <div class="grid grid-cols-2 gap-2 col-span-2 p-1">
                    <button id="showItem" data-bs-toggle="modal" data-bs-target="#showModal" class="cursor-pointer uppercase col-span-1 bg-green-500 text-white font-semibold py-2 rounded" ><span class="dashicons dashicons-visibility"></span></button>
									  <button data-storeid="${id}" id="deleteItem" class="delete-btn cursor-pointer uppercase col-span-1 bg-red-500 text-white font-semibold py-2 rounded"><span class="dashicons dashicons-trash" ></span></button>
                  </div>
								</li>
      `
    return html;
  }

  function handle_update_sucursal(id, e) {
    e.preventDefault()
    const data = {
      action: 'spsm_get_store', // El nombre de la acción que vincula con el callback PHP
      sucursal_id: id,
      nonce: spsm_ajax_obj.nonce // Verificación de seguridad
    }

    $.ajax({
      url: spsm_ajax_obj.ajax_url,
      type: 'POST',
      data: data,
      success: function (result) {
        console.log(result);
      }
    })

  }

})(jQuery);