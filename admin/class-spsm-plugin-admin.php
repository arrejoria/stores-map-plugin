<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.linkedin.com/in/arr-dev/
 * @since      1.0.0
 *
 * @package    Spsm_Plugin
 * @subpackage Spsm_Plugin/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Spsm_Plugin
 * @subpackage Spsm_Plugin/admin
 * @author     Arrejoria Lucas <arrejoria.work@gmail.com>
 */
class Spsm_Plugin_Admin
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
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */


    private $query_class;

    public function __construct($plugin_name, $version)
    {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->query_class = new SPSM_DB_Queries;
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {
        // wp_enqueue_style('quill-style', $tinymce_path, array(), null, 'all');
        // wp_enqueue_style('jquery-ui', plugin_dir_url(__FILE__) . 'css/jquery-ui.min.css', array(), null, 'all');
        wp_enqueue_style('select2', plugin_dir_url(__FILE__) . 'css/select2.min.css', array(), null, 'all');
        wp_enqueue_style('openlayers-style', 'https://cdn.jsdelivr.net/npm/ol@v10.1.0/ol.css', array(), null, 'all');
        wp_enqueue_style('bootstrap-style', plugin_dir_url(__FILE__) .  'css/bootstrap.min.css', array(), null, 'all');
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/spsm-plugin-admin.css', array(), $this->version, 'all');
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {

        // Encolar jQuery
        wp_enqueue_script('jquery');
        // Jquery UI
        // wp_enqueue_script('jquery-ui', plugin_dir_url(__FILE__) .  'js/jquery-ui.min.js', array('jquery'), null, true);
        // Encolar Popper.js
        wp_enqueue_script('popper', plugin_dir_url(__FILE__) .  'js/poppers.min.js', array('jquery'), null, true);
        // Encolar Bootstrap
        wp_enqueue_script('bootstrap', plugin_dir_url(__FILE__) . 'js/bootstrap.bundle.min.js', array('jquery', 'popper'), null, true);
        // Encolar Select 2
        wp_enqueue_script('select2', plugin_dir_url(__FILE__) .  'js/select2.min.js', array('jquery'), null, true);
        // Encolar OpenLayers (ol.js)
        wp_enqueue_script('ol-script', plugin_dir_url(__FILE__) . 'js/ol.js', array('jquery'), null, false);
        // Encolar script personalizado para el mapa
        wp_enqueue_script('map-script', plugin_dir_url(__FILE__) . 'js/map.js', array('jquery', 'ol-script'), null, false);
        // Encolar el script principal del plugin con versión y dependencias
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/spsm-plugin-admin.js', array('jquery'), $this->version, true);

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

        $map_marker_url = "assets/images/map-marker.png";
        $map_marker_icon = $this->verificar_map_marker_image_exist($map_marker_url);

        // Localizar los datos para pasarlos a JavaScript
        wp_localize_script($this->plugin_name, 'spsm_data', array(
            'path' => plugin_dir_url(__FILE__),
            'marker-icon' =>  "https://cdn-icons-png.flaticon.com/512/684/684908.png",
            'stores_empty' => $stores_empty,
            'stores_data' => json_encode($stores),
            'localidades_data' => json_encode($localidades),
        ));
    }


    public function spsm_admin_menu()
    {
        add_menu_page(
            "SPSM Dashboard",
            "SPSM Stores",
            "manage_options",
            "spsm-stores",
            array($this, 'spsm_option_page'),
            "dashicons-location",
        );

        add_submenu_page(
            'spsm-stores', // Slug del menú principal
            'Agregar Localidades', // Título de la página
            'Agregar Localidades', // Título del menú
            'manage_options', // Capacidad necesaria
            'spsm-add-localidades', // Slug del submenú
            array($this, 'spsm_localidades_option_page') // Callback para renderizar el formulario
        );
    }


    public function spsm_option_page()
    {
        // Verificar si el usuario tiene permisos para acceder
        if (!current_user_can('manage_options')) {
            return;
        } ?>
<div class="wrap">
    <div class="container">
        <h2 class="text-2xl mb-9 font-mono font-semibold">Cargar las sucursales que se mostrarán en el mapa</h2>
        <div class="container w-full px-4 py-2 bg-gray-100">
            <h3 class="mb-3">Template shortcode para mostrar la sección del mapa en la web:</h3>
            <p class="text-[16px] font-mono text-gray-500 bg-slate-200 p-2 inline-block rounded">
                [spsm_stores_template]
            </p>
        </div>
        <div class="container bg-white flex flex-col md:grid sm:grid-cols-6 my-3 px-2 py-4 border rounded">
            <!-- MAP SECTION -->
            <div class="grid grid-cols-1 col-span-1 md:col-span-6 place-content-start gap-4 md:px-4 mb-5">
                <div class="relative w-fit">
                    <h3 class="text-xl font-bold font-mono">Buscar en el mapa la ubicación de la sucursal</h3>
                    <a class="font-normal italic absolute top-[-20px] right-0 sm:top-1 sm:right-[-40px] text-white text-base lowercase bg-sky-500 px-[10px] rounded-full cursor-pointer"
                        tabindex="0" data-bs-container="body" data-bs-toggle="popover" data-bs-trigger="focus"
                        data-bs-placement="right"
                        data-bs-content="Al buscar la dirección, obtendrás sus coordenadas exactas. Si la ubicación no es precisa, puedes ajustar manualmente el marcador en el mapa para seleccionar un punto específico.">i</a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-7 gap-2">
                    <input class="col-span-1 md:col-span-2 py-1" type="text" id="street" placeholder="Calle" value="" />
                    <input class="col-span-1 py-1 md:col-span-1" type="text" id="streetNumber" placeholder="Atura"
                        value="" />
                    <input class="col-span-1 py-1 md:col-span-1" type="text" id="locality" placeholder="Localidad"
                        value="" />
                    <input class="col-span-1 py-1 md:col-span-1" type="text" id="city" placeholder="Ciudad" value="" />
                    <input class="col-span-1 py-1 md:col-span-1" type="text" id="province" placeholder="Provincia"
                        value="" />
                    <button id="search-button"
                        class="col-span-1 py-2  bg-sky-500 hover:bg-sky-600 text-white rounded font-bold uppercase">Buscar</button>
                    <p id="showStreet" class="mt-3 col-span-1 md:col-span-12"></p>
                </div>

                <!-- MAP SECTION HERE -->
                <div id="map" style="width: 100%; height: 300px; margin-top: 10px;"></div>

                <p class="px-3 py-2 rounded border inline-block w-auto">Al ingresar una ubicación que coincida con el
                    resultado deseado, se obtendrán las coordenadas exactas para ubicar el marcador de la sucursal en el
                    mapa de inicio.</p>

                <div id="popup" class="ol-popup">
                    <a id="popup-closer" class="ol-popup-closer" href="#"></a>
                    <div id="popup-content"></div>
                </div>
                <div class="modal fade" id="mapError" tabindex="-1" aria-labelledby="mapErrorLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title text-xl font-semibold" id="mapErrorLabel">Resultado no encontrado
                                </h5>
                                <button type="button" class="btn-close text-white" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body" id="mapErrorMsg">
                                <p class="text-[15px] font-semibold mb-2">Si OpenLayers no puede encontrar su sucursal
                                    con
                                    la dirección proporcionada, considera los siguientes consejos para mejorar la
                                    búsqueda:</p>
                                <hr>
                                <ul class="mb-3 text-sm list-disc px-3 my-3 space-y-1">
                                    <li><i class="font-semibold">Prueba con una dirección cercana o el nombre del
                                            establecimiento (ej. El nombre de un centro comercial, si esta se encuentra
                                            dentro)</i> si la dirección exacta no está disponible o si no se puede
                                        encontrar en OpenLayers Maps. Puede proporcionar una <strong>ubicación
                                            aproximada</strong> que sea útil para los usuarios.</li>
                                    <li>Verifica que la dirección ingresada esté completa y sin abreviaciones. Evita
                                        usar códigos postales y asegúrate de que cada parte de la dirección esté
                                        correctamente especificado.</li>
                                    <li>Sé consistente en la forma en que ingresas la dirección. Utiliza el mismo
                                        formato y estilo cada vez que realices una búsqueda para obtener resultados más
                                        precisos.</li>
                                    <li>Utiliza herramientas externas como Google Maps para verificar la dirección y
                                        obtener más detalles sobre su exactitud.</li>
                                </ul>

                                <a href="#" class="btn bg-primary px-3 py-2 text-white font-semibold rounded float-end"
                                    target="_blank" id="gmapBtn">Ir a Google Maps</a>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- <div class="grid grid-cols-1 md:grid-cols-5 gap-2">
                    <input class="col-span-1 md:col-span-4 py-1" type="text" id="street"
                        placeholder="Ingresa la dirección o calle" value="Av. Santa Fe 2349, CABA" />
                    <button id="search-button"
                        class="col-span-1 py-2 bg-sky-500 hover:bg-sky-600 text-white rounded font-bold uppercase">Buscar</button>
                    <p id="showStreet" class="mt-3 col-span-1 md:col-span-5"></p>
                </div> -->
            </div>

            <!-- STORES LIST SECTION -->
            <div class="w-full md:px-4 col-span-1 sm:col-span-3 md:col-span-3 mb-5">
                <h3 class="text-xl mb-3 font-bold font-mono">Lista de puntos de interes</h3>

                <div id="storesList" class="max-h-[400px] h-full relative">
                    <span id="storesEmpty"
                        class="hidden text-3xl font-bold uppercase text-gray-300 text-center absolute left-2/4 top-2/4 w-full"
                        style="transform: translate(-50%, -50%);"></span>
                    <ul id="store-items"
                        class="store-items border p-2 box-border min-h-[400px] overflow-scroll max-h-[400px]">
                    </ul>
                </div>
                <?php
                        $this->spsm_store_handle_modal();
                        ?>
            </div>

            <!-- FORM STORES SECTION -->
            <div class=" w-full md:px-4 col-span-1 sm:col-span-3 md:col-span-3">
                <h3 class="text-xl mb-3 font-bold font-mono">Crear nuevo punto de interes</h3>
                <?php
                        $this->spsm_create_stores_form();
                        ?>
            </div>
        </div>

    </div>

</div>
<?php
    }

    public function spsm_store_handle_modal()
    {
    ?>
<div class="modal fade" id="showModal" tabindex="-1" aria-labelledby="storeTitleMd" aria-hidden="true">
    <div class=" modal-dialog modal-fullscreen-sm-down modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="storeTitleMd"></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php
                        $this->spsm_sucursal_update_form();
                        ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary uppercase w-full max-w-xs mx-auto text-center"
                    data-bs-dismiss="modal">Cerrar Ventana Modal</button>
            </div>
        </div>
    </div>
</div>
<?php
    }

    public function spsm_create_stores_form()
    {
    ?>

<?php
        $localidades = $this->query_class->get_all_localidades();

        ?>
<form class="p-4 border rounded" id="crearPuntoForm">

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
                sucursal</label>
            <p class="text-gray-400 text-sm mb-3">Esta descripción será visualizada para los usuario, no pasar
                información sensible </p>
            <?php
                    $html = "<strong>Horario</strong>\n\n<strong>Lunes a Viernes de</strong> 9:00 a 18:00 hs. <strong>Sábado de</strong> 10:00 a 13:00 hs. <strong>Feriados</strong> 09:00 a 16:00 hs.";
                    $this->spsm_wp_editor($html, 'tiendaDescripcion', 'descripcion');
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
                data-bs-content="Puedes obtener la longitud y la latitud de un punto desde el mapa, buscando la dirección de la sucursal o ingresando manualmente sus coordenadas si las conoces.">i</a>
        </div>

        <div class="">
            <label for="lat" class="uppercase font-semibold mb-2 block text-gray-500">Latitud</label>
            <input class="w-full" type="text" name="lat" id="lat" placeholder="Ej. -3334445" required>
        </div>

        <div>
            <label for="lng" class="uppercase font-semibold mb-2 block text-gray-500">Longitud</label>
            <input class="w-full" type="text" name="lng" id="lng" placeholder="Ej. -55554443" required>
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
    <div class="flex relative my-3 ">
        <span class="absolute left-5 top-[5px] text-white" id="formSpinner"></span>
        <input id="createSubmit"
            class="w-full px-4 py-2 text-center bg-sky-500 text-white font-bold text-xl rounded cursor-pointer hover:bg-sky-600"
            type="submit" value="CREAR PUNTO DE INTERES">
    </div>
</form>
<?php
    }

    public function spsm_sucursal_update_form()
    {
    ?>

<?php
        $localidades = $this->query_class->get_all_localidades();

        ?>
<div class="text-center mt-3 space-y-3 hidden" id="searchInfo">

</div>
<form class="p-4 border rounded hidden" id="updateSucursalForm">
    <input type="hidden" name="sucursal_id" id="sucursalId" value="">
    <fieldset class="grid grid-cols-2 items-center gap-x-3 gap-y-3">
        <h4 class="col-span-2 text-xl pb-2 mb-3 border-b-4 border-sky-500 text-sky-500 font-bold font-mono">
            Actualizar datos del punto de interes</h4>

        <div class="col-span-2 md:col-span-1">
            <label for="local" class="uppercase font-semibold text-gray-500 mb-2">Elegir local</label>
            <select class="w-full" name="local" id="updateLocal" required>
                <option value="">-- Seleccionar local --</option>
                <option value="Cambio Baires">Cambio Baires</option>
                <option value="Dolar Ok">Dolar Ok</option>
                <option value="Voy y Vuelvo">Voy y vuelvo</option>
            </select>
        </div>

        <div class="col-span-2 md:col-span-1 relative">
            <label for="tiendaNombre" class="uppercase font-semibold text-gray-500 mb-2">Nombre</label>
            <a class="font-normal italic absolute top-1 right-1 text-white text-base lowercase bg-sky-500 px-[10px] rounded-full cursor-pointer"
                tabindex="0" data-bs-container="body" data-bs-toggle="popover" data-bs-trigger="focus"
                data-bs-placement="right"
                data-bs-content="Elige el nombre con el que se mostrara este punto de interes.">i</a>
            <input class="w-full" type="text" name="tienda_nombre" id="updateName"
                placeholder="Ej. Punto de Gral Pacheco" required><!-- tienda_nombre -->
        </div>

        <div class="col-span-2">
            <label for="direccion" class="uppercase font-semibold block text-gray-500 mb-2">Dirección <a
                    class="font-normal italic text-white text-base lowercase bg-sky-500  px-[10px] rounded-full float-end cursor-pointer"
                    tabindex="0" data-bs-container="body" data-bs-toggle="popover" data-bs-placement="top"
                    data-bs-content="La dirección, a diferencia de la latitud y la longitud, es únicamente informativa para el usuario y puedes cargarla manualmente en este campo o buscando en el mapa.">i</a></label>
            <input class="w-full" type="text" name="direccion" id="updateDireccion"
                placeholder="Ingresar dirección manualmente" required><!-- direccion -->
        </div>

        <div class="col-span-2 md:col-span-1">
            <label for="localidad" class="uppercase font-semibold block text-gray-500 mb-2">Localidad</label>
            <!-- <input class="w-full" type="text" name="localidad" id="localidad" placeholder="Ingresar localidad" required> -->
            <select name="localidad" id="updateLocalidad" class="w-full" required>
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
            <select class="w-full" name="zona" id="updateZona" required>
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
                    $html = "";

                    $this->spsm_wp_editor($html, 'updateTiendaDescripcion', 'descripcion');
                    ?>
        </div>
    </fieldset>

    <hr class="my-3 px-2">

    <fieldset class="grid grid-cols-2 gap-3">
        <div class="col-span-2 relative">
            <h4 class="col-span-2 pb-2 text-xl mb-3 border-b-4 border-sky-500 text-sky-500 font-bold font-mono">
                Actualizar Marcador o enlace a GMAPS</h4>
            <a class="font-normal italic absolute top-1 right-1 text-white text-base lowercase bg-sky-500 px-[10px] rounded-full cursor-pointer"
                tabindex="0" data-bs-container="body" data-bs-toggle="popover" data-bs-trigger="focus"
                data-bs-placement="right"
                data-bs-content="Puedes obtener la longitud y la latitud de un punto desde el mapa, buscando la dirección de la tienda o ingresando manualmente sus coordenadas si las conoces.">i</a>
        </div>

        <div class="">
            <label for="lat" class="uppercase font-semibold mb-2 block text-gray-500">Latitud</label>
            <input class="w-full" type="text" name="lat" id="updateLat" placeholder="Ej. -3334445" required>
            <!--                      lat -->
        </div>

        <div>
            <label for="lng" class="uppercase font-semibold mb-2 block text-gray-500">Longitud</label>
            <input class="w-full" type="text" name="lng" id="updateLng" placeholder="Ej. -55554443" required>
            <!--                      lng -->
        </div>

        <div class="col-span-2 relative ">
            <label for="gmaps" class="uppercase font-semibold mb-2 block text-gray-500">GMaps URL</label>
            <a class="font-normal italic absolute top-1 right-1 text-white text-base lowercase bg-sky-500 px-[10px] rounded-full cursor-pointer"
                tabindex="0" data-bs-container="body" data-bs-toggle="popover" data-bs-trigger="focus"
                data-bs-placement="top"
                data-bs-content="Buscar una dirección en el mapa agregará en este campo un enlace de google maps con sus coordenadas.">i</a>
            <input class="w-full" type="text" name="gmaps" id="updateGmaps"
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
            class="w-full px-4 py-2 text-center bg-sky-500 hover:bg-sky-600 text-white font-bold text-xl rounded cursor-pointer"
            type="submit" value="ACTUALIZAR">
    </div>
</form>
<?php
    }


    public function spsm_wp_editor($content  = '', $editor_id, $textarea_name)
    {
        $settings = array(
            'media_buttons' => false,
            'editor_heigh' => 80,
            'textarea_rows' => 5,
            'textarea_name' => $textarea_name
        );
        return wp_editor($content, $editor_id, $settings);
    }

    // Metodos asignados para la pagina del submenu -> Localidades
    public function spsm_localidades_option_page()
    { ?>

<div class="grid grid-cols-1">
    <h1 class="text-2xl font-bold my-5">Gestionar localidades de Store Maps</h1>

    <div>
        <h2 class="text-xl font-semibold">Agregar nuevas localidades</h2>
        <form id="localidadesForm" method="POST" action="">
            <label class="bg-gray-100 border rounded border-gray-500 my-3 p-2" for="localidades">Ingrese las localidades
                (una por línea):</label><br>
            <textarea class="p-2" name="localidades" id="localidades" rows="15" cols="50" required></textarea><br><br>
            <button type="submit" class="button button-primary">Insertar Localidades</button>
        </form>
    </div>
    <!-- <div>
				<h2>Lista de localidades</h2>
			</div> -->
</div>
<?php $this->spsm_localidades_form(); ?>

<?php
    }

    public function spsm_localidades_form()
    {
        global $wpdb;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            try {
                // Validar si se ingresaron localidades
                if (empty($_POST['localidades'])) {
                    throw new Exception('No se ingresaron localidades para cargar en Stores Map');
                }

                // Dividir las localidades ingresadas por cada línea nueva con \n
                $localidades = explode("\n", sanitize_textarea_field($_POST['localidades']));
                $inserted_count = 0;
                $localidades_ya_existentes = []; // Array para almacenar localidades ya creadas
                $localidades_insertadas = [];
                foreach ($localidades as $localidad) {
                    $localidad = trim($localidad);

                    // Verificar si existe la localidad en la tabla localidades
                    $localidad_existente = $this->query_class->find_localidad($localidad);
                    if ($localidad_existente) {

                        // Almacenar la localidad en el array de existentes sino saltarla
                        $localidades_ya_existentes[] = $localidad;
                        continue;
                    }

                    // Preparar los datos para la inserción
                    $data = array(
                        'localidad' => $localidad
                    );

                    // Insertar la nueva localidad
                    $insertar_localidad = $this->query_class->insert_localidad($data);

                    if ($insertar_localidad) {
                        $localidades_insertadas[] = $insertar_localidad;
                        $inserted_count++;
                    } else {
                        throw new Exception('Hubo un error al insertar la localidad ' . $localidad);
                    }
                }

                // Mostrar mensaje de éxito si se insertaron localidades
                if ($inserted_count > 0) {
                    echo '<div class="alert alert-success absolute right-0 top-9" role="alert">';
                    echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
                    echo '<p>Localidades cargadas con éxito ' . $inserted_count . ':</p>';
                    echo '<ul>';
                    foreach ($localidades_insertadas as $localidad_insertada) {
                        echo '<li>' . esc_html($localidad_insertada) . '</li>';
                    }
                    echo '</ul>';
                    echo '</div>';
                }

                // Si hay localidades existentes, mostrar un mensaje de error con la lista de localidades
                if (!empty($localidades_ya_existentes)) {
                    echo '<div class="alert alert-danger absolute right-0 top-20" role="alert">';
                    echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
                    echo '<p>Las siguientes localidades ya están creadas:</p>';
                    echo '<ul>';
                    foreach ($localidades_ya_existentes as $localidad_existente) {
                        echo '<li>' . esc_html($localidad_existente) . '</li>';
                    }
                    echo '</ul>';
                    echo '</div>';
                }
            } catch (Exception $e) {
                echo '<div class="alert alert-danger absolute right-0 top-5" role="alert">';
                echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
                echo '<p>' . $e->getMessage() . '</p>';
                echo '</div>';
            }
        }
    }

    public function verificar_map_marker_image_exist($imagen_relativa)
    {
        // Obtener la ruta completa al archivo dentro del plugin
        $ruta_imagen = plugin_dir_url(__FILE__) . $imagen_relativa;

        // Verificar si el archivo existe
        if (file_exists($ruta_imagen)) {
            // Si existe, devolver la URL del archivo
            return plugin_dir_url(__FILE__) . $imagen_relativa;
        } else {
            // Si no existe, devolver una imagen predeterminada o un mensaje de error
            return plugin_dir_url(__FILE__) . 'img/default.png';
        }
    }
}