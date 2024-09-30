<?php






class SPSM_Ajax
{

    public $query_class;
    public function __construct()
    {
        $this->query_class = new SPSM_DB_Queries;
    }


    public function spsm_enqueue_ajax_scripts()
    {
        // Encolar el script principal para manejo de Ajax
        wp_enqueue_script('spsm-store-ajax', plugins_url('/js/spsm-ajax.js', __DIR__), array('jquery'), null, true);

        // Pasar variables de PHP a JavaScript (URL de admin-ajax.php y el nonce)
        wp_localize_script('spsm-store-ajax', 'spsm_ajax_obj', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('spsm_ajax_nonce')
        ));
    }

    public function spsm_handle_ajax_callback()
    {
        // Valida el nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'spsm_ajax_nonce')) {
            wp_send_json_error('Nonce verification failed', 400);
        }

        // Define los campos requeridos, deben ser igual al name enviado por ajax en data
        $required_fields = [
            'local',
            'tienda_nombre',
            'direccion',
            'localidad',
            'tiendaInfo',
            'zona',
            'latitud',
            'longitud',
            'gmaps_url'
        ];

        $sanitized_fields = array();

        // Procesa y sanitiza los campos
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                wp_send_json_error('Faltan campos: ' . $field, 400);
            }

            // Usa wp_kses_post() para los campos que contienen HTML
            if ($field === 'popupInfo' || $field === 'tiendaInfo') {
                $sanitized_fields[$field] = wp_kses_post($_POST[$field]);
            } else {
                // Sanitiza los otros campos como texto plano
                $sanitized_fields[$field] = sanitize_text_field($_POST[$field]);
            }
        }

        // Inserta la tienda en la base de datos usando la clase de consultas
        $store_data = $this->query_class->insert_store(
            $sanitized_fields['tienda_nombre'],
            $sanitized_fields['local'],
            $sanitized_fields['localidad'],
            $sanitized_fields['zona'],
            $sanitized_fields['latitud'],
            $sanitized_fields['longitud'],
            $sanitized_fields['direccion'],
            $sanitized_fields['tiendaInfo'],
            $sanitized_fields['gmaps_url']
        );
        // Preparar variable con todas las tiendas en formato json
        // $json_stores = $this->query_class->get_all_stores();
        $store_data = json_encode($store_data);
        // Verifica si la inserción fue exitosa
        if ($store_data) {
            // if (!empty($json_stores)) {
            // wp_send_json_success(array('stores_data' => $json_stores));
            // }
            wp_send_json_success(array('store_data' => $store_data));
        } else {
            wp_send_json_error('Error al insertar la tienda', 500);
        }



        wp_die();
    }



    public function spsm_get_store_info() {
        if(!isset($_POST["nonce"]) || !wp_verify_nonce($_POST['nonce'], 'spsm_ajax_nonce')){
            wp_send_json_error('Nonce verification failed', 400);
        };

        if(!isset($_POST['sucursal_id']) || empty($_POST['sucursal_id'])){
            wp_send_json_error('Sucursal ID no valida', 400);
        }

        $sucursal_id = sanitize_text_field($_POST['sucursal_id']);
        echo "<pre>";
        var_dump($sucursal_id);
        echo "</pre>";
        $sucursal_data = $this->query_class->get_store_by_id($sucursal_id);

        return $sucursal_data;
        wp_die();
    }

    public function spsm_sucursal_update_form(){
		?>

		<?php
		$localidades = $this->query_class->get_all_localidades();

		?>
		<form class="p-4 border rounded" id="updateSucursalForm">
			<input type="hidden" name="sucursal_id" id="sucursalId" value="">
			<fieldset class="grid grid-cols-2 items-center gap-x-3 gap-y-3">
				<h4 class="col-span-2 text-xl pb-2 mb-3 border-b-4 border-sky-500 text-sky-500 font-bold font-mono">
					Configuración del punto de interes</h4>

				<div class="">
					<label for="local" class="uppercase font-semibold text-gray-500 mb-2">Elegir local</label>
					<select class="w-full" name="local" id="local" required>
						<!--                      store -->
						<option value="">-- Seleccionar local --</option>
						<option value="Cambio Baires">Cambio Baires</option>
						<option value="Dolar Ok">Dolar Ok</option>
						<option value="Voy y Vuelvo">Voy y vuelvo</option>
					</select>
				</div>

				<div class="relative">
					<label for="tiendaNombre" class="uppercase font-semibold text-gray-500 mb-2">Nombre</label>
					<a class="font-normal italic absolute top-1 right-1 text-white text-base lowercase bg-sky-500 px-[10px] rounded-full cursor-pointer"
						tabindex="0" data-bs-container="body" data-bs-toggle="popover" data-bs-trigger="focus"
						data-bs-placement="right"
						data-bs-content="Elige el nombre con el que se mostrara este punto de interes.">i</a>
					<input class="w-full" type="text" name="tienda_nombre" id="tiendaNombre"
						placeholder="Ej. Punto de Gral Pacheco" required><!-- tienda_nombre -->
				</div>

				<div class="col-span-2">
					<label for="direccion" class="uppercase font-semibold block text-gray-500 mb-2">Dirección <a
							class="font-normal italic text-white text-base lowercase bg-sky-500  px-[10px] rounded-full float-end cursor-pointer"
							tabindex="0" data-bs-container="body" data-bs-toggle="popover" data-bs-placement="top"
							data-bs-content="La dirección, a diferencia de la latitud y la longitud, es únicamente informativa para el usuario y puedes cargarla manualmente en este campo o buscando en el mapa.">i</a></label>
					<input class="w-full" type="text" name="direccion" id="direccion"
						placeholder="Ingresar dirección manualmente" required><!-- direccion -->
				</div>

				<div class="col-span-2 md:col-span-1">
					<label for="localidad" class="uppercase font-semibold block text-gray-500 mb-2">Localidad</label>
					<!-- <input class="w-full" type="text" name="localidad" id="localidad" placeholder="Ingresar localidad" required> -->
					<select name="localidad" id="localidad" class="w-full" required>
						<option></option>
						<?php
						foreach ($localidades as $localidad) {
							if (!empty($localidad)) {
								$localidad = $localidad['localidad'];
								echo '<option value="' . $localidad . '">' . $localidad . '</option>';
							}
						}
						?>
					</select>

				</div>

				<div class="col-span-2 md:col-span-1">
					<label for="zona" class="uppercase font-semibold block text-gray-500 mb-2">Zona del punto</label>
					<select class="w-full" name="zona" id="zona" required>
						<!--                      zona -->
						<option>-- Seleccionar zona --</option>
						<option value="caba">CABA</option>
						<option value="zona-norte">ZONA NORTE</option>
						<option value="zona-oeste">ZONA OESTE</option>
						<option value="zona-sur">ZONA SUR</option>
						<option value="interior-norte">Interior Norte</option>
						<option value="interior-sur">Interior Sur</option>
					</select>
				</div>

				<div class="col-span-2">
					<label for="tiendaInfo" class="uppercase font-semibold block text-gray-500 mb-2">Descripción de la
						tienda</label>
					<p class="text-gray-400 text-sm mb-3">Esta descripción será visualizada para los usuario, no pasar
						información sensible </p>
					<?php
					$html = "<strong>Horario</strong>\n\n<strong>Lunes a Viernes de</strong> 9:00 a 18:00 hs. <strong>Sábado de</strong> 10:00 a 13:00 hs. <strong>Feriados</strong> 09:00 a 16:00 hs.";

					// $this->spsm_wp_editor($html, 'tiendaDescripcion', 'descripcion');
					?>
				</div>
			</fieldset>

			<hr class="my-3 px-2">

			<fieldset class="grid grid-cols-2 gap-3">
				<div class="col-span-2 relative">
					<h4 class="col-span-2 pb-2 text-xl mb-3 border-b-4 border-sky-500 text-sky-500 font-bold font-mono">
						Configuración del Marcador</h4>
					<a class="font-normal italic absolute top-1 right-1 text-white text-base lowercase bg-sky-500 px-[10px] rounded-full cursor-pointer"
						tabindex="0" data-bs-container="body" data-bs-toggle="popover" data-bs-trigger="focus"
						data-bs-placement="right"
						data-bs-content="Puedes obtener la longitud y la latitud de un punto desde el mapa, buscando la dirección de la tienda o ingresando manualmente sus coordenadas si las conoces.">i</a>
				</div>

				<div class="">
					<label for="lat" class="uppercase font-semibold mb-2 block text-gray-500">Latitud</label>
					<input class="w-full" type="text" name="lat" id="lat" placeholder="Ej. -3334445" required>
					<!--                      lat -->
				</div>

				<div>
					<label for="lng" class="uppercase font-semibold mb-2 block text-gray-500">Longitud</label>
					<input class="w-full" type="text" name="lng" id="lng" placeholder="Ej. -55554443" required>
					<!--                      lng -->
				</div>

				<div class="col-span-2 relative ">
					<label for="gmaps" class="uppercase font-semibold mb-2 block text-gray-500">GMaps URL</label>
					<a class="font-normal italic absolute top-1 right-1 text-white text-base lowercase bg-sky-500 px-[10px] rounded-full cursor-pointer"
						tabindex="0" data-bs-container="body" data-bs-toggle="popover" data-bs-trigger="focus"
						data-bs-placement="top"
						data-bs-content="Buscar una dirección en el mapa agregará en este campo un enlace de google maps con sus coordenadas.">i</a>
					<input class="w-full" type="text" name="gmaps" id="gmaps"
						placeholder="https://www.google.com.ar/maps/search/..." required><!--                      gmap -->
				</div>
				<!-- <div class="col-span-2">
					<label for="popupInfo" class="uppercase font-semibold mb-2 block text-gray-500">Descripción dentro del popup</label>
					<p class="text-gray-400 text-sm mb-3">Esta descripción será visualizada para los usuario, no pasar información sensible</p>
					<div id="quillPopup" class="h-max min-h-20">
						<p><strong>Horarios: 10:20 a 14:30</strong></p>
						<p><strong>Días: </strong>Lunes a Viernes y Finde cerrado</p>
					</div>
				</div> -->
			</fieldset>
			<!-- FORM SUBMIT -->
			<div class="flex relative my-3 ">
				<span class="absolute left-5 top-[5px] text-white" id="formSpinner"></span>
				<input id="updateSubmit"
					class="w-full px-4 py-2 text-center bg-sky-500 text-white font-bold text-xl rounded cursor-pointer hover:bg-sky-600"
					type="submit" value="ACTUALIZAR SUCURSAL">
			</div>
		</form>
	<?php
	}


    public function spsm_delete_store_handle($store_id)
    {
        // Valida el nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'spsm_ajax_nonce')) {
            wp_send_json_error('Nonce verification failed', 400);
        }
    
        // Sanitize and validate the store ID
        if (isset($_POST['id']) && !empty($_POST['id'])) {
            $store_id = absint($_POST['id']); // Asegúrate de que es un número
        } else {
            wp_send_json_error('Invalid store ID', 400);
        }
    
        // Eliminar la tienda
        if ($this->query_class->delete_store($store_id)) {
            
            // Verifica si hay tiendas restantes después de la eliminación
            $remaining_stores = $this->query_class->get_all_stores();
            
            if (empty($remaining_stores)) {
                // No hay tiendas restantes
                wp_send_json_success(array('message' => 'Punto eliminado', 'spsm_stores_empty' => true));
            } else {
                // Hay tiendas restantes
                wp_send_json_success(array('message' => 'Punto eliminado', 'spsm_stores_empty' => false));
            }
            
        } else {
            wp_send_json_error('Failed to delete store', 500);
        }
    
        wp_die();
    }
    
    
}
