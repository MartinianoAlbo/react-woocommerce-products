<?php
/**
 * Plugin Name: React WooCommerce Products
 * Plugin URI: https://minimalart.co/
 * Description: Plugin para renderizar productos de WooCommerce con plantillas React personalizables
 * Version: 1.0.0
 * Author: Alvaro
 * License: GPL v2 or later
 * Text Domain: react-woo-products
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Definir constantes del plugin
define('RWP_PLUGIN_FILE', __FILE__);
define('RWP_VERSION', '1.0.0');
define('RWP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('RWP_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('RWP_TEMPLATES_PATH', RWP_PLUGIN_PATH . 'templates/');
define('RWP_CUSTOM_TEMPLATES_PATH', get_stylesheet_directory() . '/react-woo-templates/');

require_once RWP_PLUGIN_PATH . 'includes/rwp-functions.php';
require_once RWP_PLUGIN_PATH . 'includes/class-rwp-template-override.php';

// Clase principal del plugin
class ReactWooCommerceProducts {
    
    private static $instance = null;
    
    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new ReactWooCommerceProducts();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // Verificar dependencias
        add_action('admin_init', array($this, 'check_dependencies'));
        
        // Cargar clases necesarias
        $this->load_classes();
        
        // Hooks de activación y desactivación
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // Inicializar componentes
        add_action('init', array($this, 'init'));
    }
    
    /**
     * Verificar que WooCommerce esté activo
     */
    public function check_dependencies() {
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', function() {
                ?>
                <div class="notice notice-error">
                    <p><?php _e('React WooCommerce Products requiere WooCommerce para funcionar.', 'react-woo-products'); ?></p>
                </div>
                <?php
            });
            deactivate_plugins(plugin_basename(__FILE__));
        }
    }
    
    /**
     * Cargar clases del plugin
     */
    private function load_classes() {
        // REST API
        require_once RWP_PLUGIN_PATH . 'includes/class-rwp-rest-api.php';
        
        // Panel de administración
        require_once RWP_PLUGIN_PATH . 'includes/class-rwp-admin.php';
        
        // Sistema de plantillas
        require_once RWP_PLUGIN_PATH . 'includes/class-rwp-template-manager.php';
        
        // Frontend
        require_once RWP_PLUGIN_PATH . 'includes/class-rwp-frontend.php';
    }
    
    /**
     * Inicializar componentes del plugin
     */
    public function init() {
        // Cargar textdomain
        load_plugin_textdomain('react-woo-products', false, dirname(plugin_basename(__FILE__)) . '/languages');
        
        // Inicializar REST API
        new RWP_REST_API();
        
        // Inicializar panel de administración
        if (is_admin()) {
            new RWP_Admin();
        }
        
        // Inicializar frontend
        if (!is_admin()) {
            new RWP_Frontend();
        }
        
        // Inicializar sistema de plantillas
        RWP_Template_Manager::getInstance();
    }
    
    /**
     * Activación del plugin
     */
    public function activate() {
        // Crear tablas si es necesario
        $this->create_tables();
        
        // Crear opciones por defecto
        $default_settings = array(
            'enable_react_globally' => 'no',  // Cambiado de false a 'no'
            'default_template' => 'template-1',
            'cache_enabled' => '1',  // Cambiado de true a '1'
            'cache_duration' => 3600
        );
        
        
        // Si la opción ya existe, actualizarla
        if (get_option('rwp_global_settings') !== false) {
            update_option('rwp_global_settings', $default_settings);
        } else {
            add_option('rwp_global_settings', $default_settings);
        }
        
        // Crear directorio para plantillas personalizadas
        $custom_dir = RWP_CUSTOM_TEMPLATES_PATH;
        if (!file_exists($custom_dir)) {
            wp_mkdir_p($custom_dir);
            
            // Crear archivo README
            file_put_contents($custom_dir . '/README.md', 
                "# Plantillas Personalizadas de React WooCommerce Products\n\n" .
                "Coloca aquí tus plantillas personalizadas siguiendo la estructura:\n" .
                "- template-1/\n" .
                "- template-2/\n" .
                "- template-3/\n\n" .
                "Cada carpeta debe contener:\n" .
                "- index.js\n" .
                "- style.css\n" .
                "- config.json\n"
            );
        }
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Desactivación del plugin
     */
    public function deactivate() {
        flush_rewrite_rules();
    }
    
    /**
     * Crear tablas personalizadas si es necesario
     */
    private function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Tabla para analytics de templates (opcional)
        $table_name = $wpdb->prefix . 'rwp_template_analytics';
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            product_id bigint(20) NOT NULL,
            template_id varchar(50) NOT NULL,
            views int(11) DEFAULT 0,
            conversions int(11) DEFAULT 0,
            last_viewed datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY product_id (product_id),
            KEY template_id (template_id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}


ReactWooCommerceProducts::getInstance();