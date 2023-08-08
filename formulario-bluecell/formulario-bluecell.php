<?php
/*
Plugin Name: Formulario Bluecell
Description: Plugin para agregar un formulario Bluecell y almacenar los datos enviados en una tabla de la base de datos.
Version: 1.0
Author: Adrián Soria García

*/

// Función para crear la tabla de base de datos en la activación del plugin
function formulario_bluecell_install() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'bluecell_data';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id INT NOT NULL AUTO_INCREMENT,
        nombre VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        telefono VARCHAR(20) NOT NULL,
        mensaje TEXT NOT NULL,
        asunto VARCHAR(100) NOT NULL,
        aceptacion_politicas TINYINT NOT NULL,
        fecha DATETIME NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    
    //Crea o actualiza las tablas existentes
    dbDelta($sql);
}

// Función para eliminar la tabla de base de datos en la desinstalación del plugin
function formulario_bluecell_uninstall() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'bluecell_data';
    $wpdb->query("DROP TABLE IF EXISTS $table_name");
}

register_activation_hook(__FILE__, 'formulario_bluecell_install');
register_uninstall_hook(__FILE__, 'formulario_bluecell_uninstall');


// Función para mostrar el formulario
function bluecell_display_form() {
    // HTML del formulario
    $form_html = '
    <form id="bluecell-form">
        <label for="nombre">Nombre:</label>
        <input type="text" name="nombre" required>
        <label for="email">Email:</label>
        <input type="email" name="email" required>
        <label for="telefono">Teléfono:</label>
        <input type="text" name="telefono" required>
        <label for="mensaje">Mensaje:</label>
        <textarea name="mensaje" required></textarea>
        <label for="asunto">Asunto:</label>
        <input type="text" name="asunto" required>
        <label for="aceptacion_politicas">Aceptación Políticas:</label>
        <input type="checkbox" name="aceptacion_politicas" required>
        <input type="submit" value="Enviar">
    </form>';

    echo $form_html;
}

// Función para procesar el formulario mediante AJAX
function bluecell_process_form() {
    if (isset($_POST['nombre']) && isset($_POST['email']) && isset($_POST['telefono']) && isset($_POST['mensaje']) && isset($_POST['asunto']) && isset($_POST['aceptacion_politicas'])) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'bluecell_data';

        $nombre = filter_var($_POST['nombre'] ??= '', FILTER_SANITIZE_SPECIAL_CHARS);
        $email = filter_var($_POST['email'] ??= '', FILTER_SANITIZE_EMAIL);
        $telefono = filter_var($_POST['telefono'] ??= '', FILTER_SANITIZE_SPECIAL_CHARS);
        $mensaje = filter_var($_POST['mensaje'] ??= '', FILTER_SANITIZE_SPECIAL_CHARS);
        $asunto = filter_var($_POST['asunto'] ??= '', FILTER_SANITIZE_SPECIAL_CHARS);
        $aceptacion_politicas = (int) $_POST['aceptacion_politicas'];

        $fecha = current_time('mysql');

        $data = array(
            'nombre' => $nombre,
            'email' => $email,
            'telefono' => $telefono,
            'mensaje' => $mensaje,
            'asunto' => $asunto,
            'aceptacion_politicas' => $aceptacion_politicas,
            'fecha' => $fecha,
        );
        print_r($data);
        $wpdb->insert($table_name, $data);

        wp_die(); // Detener la ejecución de WordPress para la solicitud AJAX
    }
}

add_action('wp_ajax_bluecell_process_form', 'bluecell_process_form');
add_action('wp_ajax_nopriv_bluecell_process_form', 'bluecell_process_form');



// Función para crear la página de administración de datos del formulario
function bluecell_data_page() {
    ?>
    <div class="wrap">
        <h1>Datos del formulario Bluecell</h1>
        <table id="bluecell-data-table" class="display" style="width:100%">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Teléfono</th>
                    <th>Mensaje</th>
                    <th>Asunto</th>
                    <th>Aceptación Políticas</th>
                    <th>Fecha</th>
                </tr>
            </thead>
            <tbody>
                <?php
                global $wpdb;
                $table_name = $wpdb->prefix . 'bluecell_data';
                $data = $wpdb->get_results("SELECT * FROM $table_name");
                foreach ($data as $row) {
                    echo "<tr>";
                    echo "<td>{$row->id}</td>";
                    echo "<td>{$row->nombre}</td>";
                    echo "<td>{$row->email}</td>";
                    echo "<td>{$row->telefono}</td>";
                    echo "<td>{$row->mensaje}</td>";
                    echo "<td>{$row->asunto}</td>";
                    echo "<td>{$row->aceptacion_politicas}</td>";
                    echo "<td>{$row->fecha}</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
    <?php
}

// Función para registrar la página en el menú del CMS
function bluecell_add_data_page() {
    add_menu_page('Formulario Bluecell Data', 'Bluecell Data', 'manage_options', 'bluecell-data', 'bluecell_data_page');
}

add_action('admin_menu', 'bluecell_add_data_page');

// Función para cargar DataTables en la página de administración de datos
function bluecell_enqueue_datatables() {
    wp_enqueue_style('bluecell-datatables-css', plugins_url('/css/dataTables.min.css', __FILE__));
    wp_enqueue_script('bluecell-datatables-js', plugins_url('/js/dataTables.min.js', __FILE__), array('jquery'), '1.10.25', true);
}

add_action('admin_enqueue_scripts', 'bluecell_enqueue_datatables');

// Función para inicializar DataTables en la tabla de datos
function bluecell_initialize_datatables() {
    ?>
    <script>
        jQuery(document).ready(function($) {
            $('#bluecell-data-table').DataTable();
        });
    </script>
    <?php
}

add_action('admin_footer', 'bluecell_initialize_datatables');