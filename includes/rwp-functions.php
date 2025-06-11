<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Obtener el ID de la plantilla para un producto
 */
if (!function_exists('rwp_get_product_template_id')) {
    function rwp_get_product_template_id($product_id) {
        $template = rwp_get_product_template($product_id);
        return $template ? $template['id'] : '';
    }
}

/**
 * Obtener la plantilla completa para un producto
 */
if (!function_exists('rwp_get_product_template')) {
    function rwp_get_product_template($product_id) {
        return RWP_Template_Manager::getInstance()->get_product_template($product_id);
    }
}

/**
 * Verificar si React está habilitado para un producto
 */
if (!function_exists('rwp_is_react_enabled_for_product')) {
    function rwp_is_react_enabled_for_product($product_id) {
        $global_settings = get_option('rwp_global_settings', array('enable_react_globally' => 'no'));
        
        $product_react_enabled = get_post_meta($product_id, '_rwp_react_enabled', true);

        if ($global_settings['enable_react_globally'] === 'yes') {
            return true;
        }
        
        if ($product_react_enabled === 'enabled') {
            return true;
        }
        
        return false;
    }
} 