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



	public function spsm_get_store_info()
	{
		// Verificar nonce de seguridad
		if (!isset($_POST["nonce"]) || !wp_verify_nonce($_POST['nonce'], 'spsm_ajax_nonce')) {
			wp_send_json_error('Nonce verification failed', 400);
		}

		// Verificar si el ID de la sucursal es válido
		if (!isset($_POST['sucursal_id']) || empty($_POST['sucursal_id'])) {
			wp_send_json_error('Sucursal ID no válida', 400);
		}

		// Sanitizar el ID de la sucursal
		$sucursal_id = sanitize_text_field($_POST['sucursal_id']);

		// Obtener los datos de la sucursal o un array vacío si no existe
		$sucursal_data = $this->query_class->get_store_by_id($sucursal_id) ?? [];

		// Si se obtuvieron datos de la sucursal, enviar respuesta exitosa
		if (!empty($sucursal_data)) {
			wp_send_json_success(array('sucursal_data' => $sucursal_data), 200);
		}

		// Enviar respuesta de error si no se encontraron datos
		wp_send_json_error('No se encontraron datos de la sucursal', 400);
	}


	public function spsm_update_store_info()
	{
		// Verificar nonce de seguridad
		if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'spsm_ajax_nonce')) {
			wp_send_json_error('Nonce verification failed', 400);
		}

		// Verificar si el ID de la sucursal es válido
		if (!isset($_POST['sucursal_id']) || empty($_POST['sucursal_id'])) {
			wp_send_json_error('Sucursal ID no válida', 400);
		}

		// Sanitizar los datos recibidos
		$sucursal_id = sanitize_text_field($_POST['sucursal_id']);
		$data = array(
			'local' => sanitize_text_field($_POST['local']),
			'tienda_nombre' => sanitize_text_field($_POST['tienda_nombre']),
			'direccion' => sanitize_text_field($_POST['direccion']),
			'localidad' => sanitize_text_field($_POST['localidad']),
			'zona' => sanitize_text_field($_POST['zona']),
			'tienda_info' => wp_kses_post($_POST['tiendaInfo']), // Permitir solo HTML seguro
			'latitud' => sanitize_text_field($_POST['latitud']),
			'longitud' => sanitize_text_field($_POST['longitud']),
			'gmaps_url' => esc_url_raw($_POST['gmaps_url']), // Escapar URL
		);

		// Llamar a la función para actualizar la sucursal
		$updated = $this->query_class->update_store_by_id($sucursal_id, $data);

		// Verificar si la actualización fue exitosa
		if ($updated !== false) {
			wp_send_json_success('Sucursal actualizada correctamente', 200);
		} else {
			wp_send_json_error('No se pudo actualizar la sucursal', 500);
		}
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
