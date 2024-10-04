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

		wp_enqueue_style('normalize', plugin_dir_url(__FILE__) . 'css/normalize.css', array(), null, 'all');
		wp_enqueue_style('openlayers-style', 'https://cdn.jsdelivr.net/npm/ol@v10.1.0/ol.css', array(), null, 'all');
		wp_enqueue_style('bootstrap', plugin_dir_url(__FILE__) . 'css/bootstrap.min.css', array(), null, 'all');
		wp_enqueue_style('select2-min',  plugin_dir_url(__FILE__) .  'css/select2.min.css', array(), null, 'all');
		wp_enqueue_style('select2-theme',  plugin_dir_url(__FILE__) .  'css/select2-bootstrap.min.css', array(), null, 'all');
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

		// Obtén todas las sucursales y localidades
		$stores = $this->query_class->get_all_stores();
		$localidades = $this->query_class->get_all_localidades();

		// Si no hay sucursales, indicarlo
		if (empty($stores)) {
			$stores = [];
			$localidades = [];
			$stores_empty = true;
		} else {
			$stores_empty = false;
		}


		$icon_path = "assets/images/map-marker.png";
		$alt_icon_path = "assets/images/map-marker-alt.png";
		// Ruta a https://starpay.local/wp-content/plugins/spsm-plugin/public/assets/images/map-marker.png
		$map_marker_icon = $this->verificar_map_marker_image_exist($icon_path);
		$map_marker_icon_alt = $this->verificar_map_marker_image_exist($alt_icon_path);

		$map_markers = array($map_marker_icon, $map_marker_icon_alt);
		// Localizar los datos para pasarlos a JavaScript
		wp_localize_script($this->plugin_name, 'spsm_data', array(
			'path' => plugin_dir_url(__FILE__),
			'marker_icon' =>  $map_markers,
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
<div class="w-full px-2 spsm mb-[100px] space-y-3" id="mapContainer">
    <div class="stores-filter flex flex-col items-center justify-center gap-2 md:flex-row w-full border rounded md:rounded-full md:gap-x-3 border-gray-400 py-3 px-1 md:px-5 mx-auto !mb-7 shadow"
        id="storesFilters">
        <div class="w-full md:w-1/5">
            <select class="form-control select2-single" aria-label="Default select example" name="locales" id="local">
                <option></option>
                <option value="Cambio Baires">Cambio Baires</option>
                <option value="Dolar Ok">Dolar Ok</option>
                <option value="Voy y Vuelvo">Voy y vuelvo</option>
            </select>
        </div>

        <div class="w-full md:w-1/5">
            <select class="form-control select2-single rounded-full" name="zonas" id="zona" required>
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

        <div class="w-full md:w-1/4">
            <select class="form-select " aria-label="Default select example" name="localidades" id="localidad">
                <option></option>
            </select>
        </div>

        <div class="flex w-full md:w-1/4 relative gap-3">
            <button id="filterBtn"
                class="col-span-1 bg-secondary-color text-white rounded w-full px-3 py-2 uppercase font-bold hover:brightness-75 active:brightness-100">Buscar</button>
            <button id="resetBtn"
                class="col-span-1 bg-gray-300 text-white rounded w-full px-3 py-2 uppercase font-bold hover:brightness-90 active:brightness-100">Reiniciar</button>
            <span
                class="results-msg py-3 px-2 bg-white bottom-[-55px] border border-solid border-gray-200 rounded shadow-sm shadow-white text-center right-0 absolute z-10 text-nowrap font-semibold text-red-500 hide-noresults"
                id="noResults">No se encontraron resultados</span>
        </div>
    </div>
    <div
        class="grid grid-cols-1 md:grid-cols-7 xl:grid-cols-12 w-full md:border-solid md:border md:border-gray-200 gap-y-3">
        <div
            class="stores-content col-span-1 md:col-span-3 xl:col-span-4 border-t border-solid border-color-secondary relative shadow-md">
            <h2
                class="text-xl uppercase col-span-6 py-3 px-2 font-semibold bg-secondary-color text-white ff-gotham-bold">
                Sucursales
            </h2>
            <ul class="lista-sucursales p-2 max-sm:max-h-[300px] max-h-[500px] scrollbar space-y-3" id="sucursales">
            </ul>
            <img class="scrollbar-i" src="<?= esc_attr(plugin_dir_url(__FILE__) . 'assets/images/scroll-down1.gif'); ?>"
                alt="scrolldown lista de sucursales icon" width="100" height="80">
        </div>
        <div
            class="flex flex-col  md:col-span-4 xl:col-span-8 max-md:min-h-[400px] h-full border-solid border border-color-secondary shadow-md">
            <div class="punto-descripcion relative" id="storeInfo"></div>
            <div class="punto-mapa md:h-fit flex-1 h-full min-h-[200px]" id="map"></div>
        </div>
    </div>
</div>
<?php
	}


	public function verificar_map_marker_image_exist($icon_url)
	{
		// Convierte la URL en una ruta del sistema de archivos
		$icon_path = plugin_dir_path(__FILE__) . $icon_url;

		// Verifica si el archivo existe en el servidor
		if (file_exists($icon_path)) {
			return plugin_dir_url(__FILE__) . $icon_url; // Devuelve la URL del archivo
		} else {
			// Si no existe, devolver una imagen predeterminada o un mensaje de error
			return "https://cdn-icons-png.flaticon.com/512/684/684908.png";
		}
	}
}