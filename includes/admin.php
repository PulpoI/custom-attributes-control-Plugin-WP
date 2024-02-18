<?php
// Agregar la página de configuración al menú de administración
add_action('admin_menu', 'cac_add_admin_page');

function cac_add_admin_page()
{
    add_menu_page(
        'Control de Términos Personalizados',
        'Control de Términos',
        'manage_options',
        'custom-attributes-control',
        'cac_render_admin_page',
        'dashicons-admin-generic',
        99
    );
}

// Renderizar la página de administración
function cac_render_admin_page()
{
    ?>
    <div class="wrap">
        <h2>Control de Términos Personalizados</h2>
        <p>Aquí puedes gestionar la visibilidad de los atributos y términos en productos de WooCommerce.</p>
        <form method="post" action="options.php">
            <?php
            settings_fields('cac_settings_group');
            do_settings_sections('custom-attributes-control');
            submit_button('Guardar cambios');
            ?>
        </form>
    </div>
    <?php
}

// Agregar secciones y campos al panel de administración
add_action('admin_init', 'cac_initialize_settings');

function cac_initialize_settings()
{
    add_settings_section(
        'cac_settings_section',
        'Atributos y Términos',
        'cac_render_settings_section',
        'custom-attributes-control'
    );

    add_settings_field(
        'cac_selected_attributes',
        'Atributos',
        'cac_render_selected_attributes_field',
        'custom-attributes-control',
        'cac_settings_section'
    );

    register_setting('cac_settings_group', 'cac_selected_attributes');
}

function cac_render_settings_section()
{
    echo 'Selecciona los térmios que deseas desactivar:';
}

function cac_render_selected_attributes_field()
{
    $selected_attributes = get_option('cac_selected_attributes');
    if (!is_array($selected_attributes)) {
        $selected_attributes = array();
    }
    $attributes = wc_get_attribute_taxonomies();

    if ($attributes) {
        foreach ($attributes as $attribute) {
            $terms = get_terms(
                array(
                    'taxonomy' => 'pa_' . $attribute->attribute_name,
                    'hide_empty' => false,
                )
            );

            if (!empty($terms)) {
                echo "<p><strong>{$attribute->attribute_label}</strong></p>";
                foreach ($terms as $term) {
                    $checked = in_array($term->slug, $selected_attributes) ? 'checked' : '';
                    echo "<input type='checkbox' name='cac_selected_attributes[]' value='$term->slug' $checked /> $term->name <br/>";
                }
            }
        }
    } else {
        echo 'No hay atributos disponibles.';
    }
}

// Desactivar los términos de atributos seleccionados en la página del producto
add_filter('woocommerce_dropdown_variation_attribute_options_html', 'cac_disable_selected_attributes', 10, 2);

function cac_disable_selected_attributes($html, $args)
{
    $selected_attributes = get_option('cac_selected_attributes');

    // Si no hay atributos seleccionados para deshabilitar, retornar el HTML sin cambios
    if (empty($selected_attributes)) {
        return $html;
    }

    // Obtener el nombre del atributo actual
    $current_attribute = str_replace('attribute_', '', $args['attribute']);

    // Parsear el HTML en busca de opciones de atributos
    $dom = new DOMDocument();
    // Especificar la codificación de caracteres
    $dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    $options = $dom->getElementsByTagName('option');

    // Array para almacenar opciones deshabilitadas
    $disabled_options = [];

    foreach ($options as $option) {
        // Verificar si el término actual está desactivado
        if (in_array($option->getAttribute('value'), $selected_attributes)) {
            // Agregar la opción a la lista de opciones deshabilitadas
            $disabled_options[] = $option;
        }
    }

    // Eliminar las opciones deshabilitadas del DOM
    foreach ($disabled_options as $option) {
        $option->parentNode->removeChild($option);
    }

    // Convertir el DOM modificado de nuevo a HTML
    $html = $dom->saveHTML();

    return $html;
}


