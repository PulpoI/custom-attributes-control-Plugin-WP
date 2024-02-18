<?php
/**
 * Plugin Name: Desactivador de Opciones de Producto
 * Description: Plugin para gestionar la visibilidad de atributos y términos en productos de WooCommerce. Facilita la selección de productos personalizados en tu tienda online al desactivar automáticamente los términos de atributos no disponibles en todos los productos.
 * Version: 1.0
 * Author: Pablo Duarte
 */

// Definir la ruta del plugin
define('CAC_PLUGIN_DIR', plugin_dir_path(__FILE__));

// Incluir archivos necesarios
require_once CAC_PLUGIN_DIR . 'includes/admin.php';
