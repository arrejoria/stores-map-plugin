<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://www.linkedin.com/in/arr-dev/
 * @since      1.0.0
 *
 * @package    Spsm_Plugin
 * @subpackage Spsm_Plugin/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Spsm_Plugin
 * @subpackage Spsm_Plugin/public
 * @author     Arrejoria Lucas <arrejoria.work@gmail.com>
 */
class Spsm_Plugin_Public
{

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */

	private $query_class;

	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->query_class = new SPSM_DB_Queries;

		add_shortcode('spsm_stores_template', array($this, 'spsm_home_section_template'));
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{

		wp_enqueue_style('openlayers-style', 'https://cdn.jsdelivr.net/npm/ol@v10.1.0/ol.css', array(), null, 'all');
		wp_enqueue_style('select2-theme',  plugin_dir_url(__FILE__) .  'css/select2-bootstrap-5-theme.min.css', array(), null, 'all');
		wp_enqueue_style('select2-min',  plugin_dir_url(__FILE__) .  'css/select2.min.css', array(), null, 'all');
		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/spsm-plugin-public.css', array(), $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */

	public function enqueue_scripts()
	{
		wp_enqueue_script('jQuery');
		// Cargar OpenLayers desde CDN (versión optimizada sin módulos)
		wp_enqueue_script('select2', plugin_dir_url(__FILE__) .  'js/select2.min.js', array('jquery'), null, true);
		// Encolar OpenLayers (ol.js)
		wp_enqueue_script('ol-script', plugin_dir_url(__FILE__) . 'js/ol.js', array('jquery'), null, false);
		// Encolar script personalizado para el mapa
		wp_enqueue_script('map-script', plugin_dir_url(__FILE__) . 'js/map.js', array('jquery', 'ol-script'), null, false);
		// Cargar los scripts personalizados del plugin
		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/spsm-plugin-public.js', array('jquery'), $this->version, true);


		$this->spsm_stores_localize_scripts();
	}


	public function spsm_stores_localize_scripts()
	{

		// Obtén todas las tiendas y localidades
		$stores = $this->query_class->get_all_stores();
		$localidades = $this->query_class->get_all_localidades();

		// Si no hay tiendas, indicarlo
		if (empty($stores)) {
			$stores = [];
			$localidades = [];
			$stores_empty = true;
		} else {
			$stores_empty = false;
		}

		// Localizar los datos para pasarlos a JavaScript
		wp_localize_script($this->plugin_name, 'spsm_data', array(
			'path' => plugin_dir_url(__FILE__),
			'stores_empty' => $stores_empty,
			'stores_data' => json_encode($stores),
			'localidades_data' => json_encode($localidades),
		));
	}

	/**
	 * Contenido HTML y PHP para compartir en toda la web mediante el uso de un shortcode. 
	 * La parte funcional de esta sección será parte de la variable global inicializada en el head de la web.
	 * 
	 * Use Shortcode:		[spsm_home_section_template]
	 * 
	 * @return void
	 */
	public function spsm_home_section_template()
	{
?>
		<div class="w-full px-2 smsp mb-[100px] space-y-3" id="mapContainer">
			<h1 class="text-2xl font-semibold text-center mb-12 text-primary-color">Podes buscar las sucursales que tenemos por
				tu zona:</h1>
			<div class="stores-filter flex flex-col items-center justify-center gap-2 md:flex-row w-full md:max-w-full border rounded md:rounded-full md:gap-x-3 border-gray-400 py-3 px-1 md:px-5 mx-auto !mb-7 shadow"
				id="storesFilters">
				<div class="w-full md:w-1/5">
					<select class="form-select " aria-label="Default select example" name="locales" id="local">
						<option></option>
						<option value="Cambio Baires">Cambio Baires</option>
						<option value="Dolar Ok">Dolar Ok</option>
						<option value="Voy y Vuelvo">Voy y vuelvo</option>
					</select>
				</div>

				<div class="w-full md:w-1/5">
					<select class="form-select rounded-full" name="zonas" id="zona" required>
						<!--                      zona -->
						<option></option>
						<option value="caba">CABA</option>
						<option value="zona-norte">ZONA NORTE</option>
						<option value="zona-oeste">ZONA OESTE</option>
						<option value="zona-sur">ZONA SUR</option>
						<option value="interior-norte">INTERIOR NORTE</option>
						<option value="interior-sur">INTERIOR SUR</option>
					</select>
				</div>

				<div class="w-full md:w-1/5">
					<select class="form-select " aria-label="Default select example" name="localidades" id="localidad">
						<option></option>
					</select>
				</div>

				<div class="flex w-full md:w-1/4 relative text-sm gap-3">
					<button id="filterBtn"
						class="btn btn-small col-span-1 bg-primary-color text-white rounded w-full py-1 uppercase font-bold">Buscar</button>
					<button id="resetBtn"
						class="btn btn-small col-span-1 bg-gray-300 text-white rounded w-full py-1 uppercase font-bold">Reiniciar</button>
					<span
						class="results-msg py-3 px-2 bg-white bottom-[-55px] border  rounded shadow-sm shadow-white text-center right-0 absolute z-10 text-nowrap text-sm font-semibold text-red-500 hide-noresults"
						id="noResults">No se encontraron resultados</span>
				</div>
			</div>
			<div class="grid grid-cols-1 md:grid-cols-7 xl:grid-cols-12 w-full md:border gap-y-3 shadow-md">
				<div class="stores-content col-span-1 md:col-span-3 xl:col-span-4 relative border">
					<h2 class="text-xl uppercase col-span-6 py-3 px-2 font-semibold bg-primary-color text-white">Sucursales</h2>
					<ul class="lista-tiendas p-2 max-sm:max-h-[300px] max-h-[500px] scrollbar space-y-3" id="sucursales">
					</ul>
					<img class="scrollbar-i" src="<?= esc_attr(plugin_dir_url(__FILE__) . 'assets/images/scroll-down1.gif'); ?>"
						alt="scrolldown lista de tiendas icon" width="100" height="80">
				</div>
				<div class="flex flex-col  md:col-span-4 xl:col-span-8 max-md:min-h-[400px] h-full">
					<div class="punto-descripcion min-h-fit" id="storeInfo"></div>
					<div class="punto-mapa md:h-fit flex-1 h-full min-h-[200px]" id="map"></div>
				</div>
			</div>
		</div>
<?php
	}
}
