<?php

class SPSM_DB_Queries
{

    private $wpdb;
    private $table_name_stores;
    private $table_name_localidades;

    public function __construct()
    {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_name_stores = $this->wpdb->prefix . 'spsm_stores'; // Nombre de la tabla con prefijo de WordPress
        $this->table_name_localidades = $this->wpdb->prefix . 'spsm_stores_localidades'; // Nombre de la tabla con prefijo de WordPress
    }


    public function insert_store($tienda_nombre, $local, $localidad, $zona, $latitud, $longitud, $direccion, $tiendaInfo, $gmapsUrl)
    {
        global $wpdb;

        $data = [
            'tienda_nombre' => $tienda_nombre,
            'local' => $local,
            'localidad' => $localidad,
            'zona' => $zona,
            'latitud' => $latitud,
            'longitud' => $longitud,
            'direccion' => $direccion,
            'tienda_info' => $tiendaInfo,
            'gmaps_url' => $gmapsUrl
        ];

        $format = ['%s', '%s', '%s', '%s', '%f', '%f', '%s', '%s', '%s'];

        $result = $wpdb->insert($this->table_name_stores, $data, $format);

        if ($result) {
            // Obtén el ID de la tienda insertada
            $insert_id = $wpdb->insert_id;

            // Retorna los datos completos de la tienda insertada
            $store = $wpdb->get_row($wpdb->prepare("SELECT * FROM $this->table_name_stores WHERE id = %d", $insert_id), ARRAY_A);

            return $store; // Retorna toda la fila como un formato json
        }

        return false;
    }

    public function get_all_stores()
    {
        global $wpdb; // Acceder al objeto global $wpdb
        $table_name_stores = $this->table_name_stores;
        // Obtener todos los registros
        $results = $wpdb->get_results("SELECT * FROM $table_name_stores ORDER BY created_at DESC", ARRAY_A);

        if ($results) {
            return $results; // Devuelve los resultados en lugar de usar wp_send_json_success
        } else {
            return []; // Devuelve un array vacío si no hay resultados
        }
    }
    
    /**
     * get_store_by_id
     *
     * @param  string $id Identificador de la sucursal
     * @return void
     */
    public function get_store_by_id($id){
        $table_name = $this->table_name_stores;

        if(empty($id)){
            return null;
        }

        $prepared_query = $this->wpdb->prepare("SELECT * FROM $table_name WHERE id = %s", $id);
        $result = $this->wpdb->get_row($prepared_query, ARRAY_A);

    
        if($result){
            return $result;
        } else {
            return [];
        }
    }

    // Método para eliminar un registro
    public function delete_store($id)
    {
        return $this->wpdb->delete($this->table_name_stores, array('id' => $id));
    }

    // Metodo para obtener la localidad buscada
    public function find_localidad($localidad)
    {
        // Sanitización adicional
        $localidad = strtolower(trim($localidad));
        // Verifica que la localidad no esté vacía
        if (empty($localidad)) {
            return null; // O devuelve un error personalizado si es necesario
        }

        $table_name = $this->table_name_localidades;

        // Corrección de tipografía en 'prepare'
        $query = $this->wpdb->prepare("SELECT * FROM $table_name WHERE localidad = %s", $localidad);
        $result = $this->wpdb->get_row($query);

        if ($result) {
            return $result;
        }

        // Retorna el resultado
        return null;
    }

    public function get_all_localidades(){

        $table = $this->table_name_localidades;
        $prepare = $this->wpdb->prepare("SELECT localidad FROM $table ORDER BY localidad ASC");
        $result = $this->wpdb->get_results($prepare, ARRAY_A);
        
        if($result){
            return $result;
        }else{
            return [];
        }
    }

    public function insert_localidad($data)
    {
        // Asegurarse de que $data no esté vacío
        if (empty($data)) {
            return false; // o manejar el error de alguna manera
        }
    
        // Nombre de la tabla a usar
        $table = $this->table_name_localidades;
    
        // Insertar los datos en la tabla y asegurar los tipos de datos
        $insert = $this->wpdb->insert($table, $data, array('%s')); // %s es para strings, ajustar según los tipos de datos
    
        // Retornar la localidad insertada si se insertó con éxito
        if ($insert) {
            return $data['localidad']; // O ajusta según la clave que contiene la localidad
        } else {
            return false; // O manejar el error si la inserción falla
        }
    }

    // Método para insertar datos
    public function insert_data($table_name, $data)
    {
        $this->wpdb->insert($table_name, $data);
        return $this->wpdb->insert_id;
    }

    // // Método para obtener todos los registros
    // public function get_all_records()
    // {
    //     $query = "SELECT * FROM $this->table_name ORDER BY created_at DESC";
    //     return $this->wpdb->get_results($query);
    // }

    // // Método para obtener un solo registro por ID
    // public function get_record_by_id($id)
    // {
    //     $query = $this->wpdb->prepare("SELECT * FROM $this->table_name WHERE id = %d", $id);
    //     return $this->wpdb->get_row($query);
    // }

    // // Método para actualizar un registro
    // public function update_record($data, $where)
    // {
    //     return $this->wpdb->update($this->table_name, $data, $where);
    // }

    // Método personalizado para ejecutar consultas manuales
    public function custom_query($sql)
    {
        return $this->wpdb->get_results($sql);
    }
}
