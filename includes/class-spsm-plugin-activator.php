<?php

/**
 * Fired during plugin activation
 *
 * @link       https://www.linkedin.com/in/arr-dev/
 * @since      1.0.0
 *
 * @package    Spsm_Plugin
 * @subpackage Spsm_Plugin/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Spsm_Plugin
 * @subpackage Spsm_Plugin/includes
 * @author     Arrejoria Lucas <arrejoria.work@gmail.com>
 */
class Spsm_Plugin_Activator
{

    /**
     * Ejecutado durante la activación del plugin
     *
     * @since    1.0.0
     */
    public static function activate()
    {
        self::create_stores_table();
        self::create_stores_localidades_table();
        flush_rewrite_rules();
    }

    /**
     * Verifica si la tabla existe antes de crearla
     *
     * @since    1.0.0
     */
    public static function create_stores_table()
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'spsm_stores';

        // Verificar si la tabla ya existe
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") !== $table_name) {

            $charset_collate = $wpdb->get_charset_collate();

            $sql = "CREATE TABLE $table_name (
                id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                tienda_nombre VARCHAR(255) NOT NULL,
                local VARCHAR(255) NOT NULL,
                localidad VARCHAR(255) NOT NULL,
                zona VARCHAR(255) NOT NULL,
                latitud FLOAT(10, 6) NOT NULL,
                longitud FLOAT(10, 6) NOT NULL,
                direccion VARCHAR(255) NOT NULL,
                popup_info TEXT DEFAULT NULL,
                tienda_info TEXT DEFAULT NULL,
                gmaps_url VARCHAR(255) NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id)
            ) $charset_collate;";


            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }
    }

    public static function create_stores_localidades_table()
    {

        global $wpdb;

        $table_name = $wpdb->prefix . 'spsm_stores_localidades';

        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") !== $table_name) {

            $charset_collate = $wpdb->get_charset_collate();
            $sql = "CREATE TABLE $table_name (
                id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                localidad VARCHAR(255) NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                UNIQUE(localidad),
                PRIMARY KEY (id)
            ) $charset_collate;";


            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }
    }
}
