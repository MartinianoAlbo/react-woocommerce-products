<?php
/**
 * Clase para manejar el panel de administración del plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class RWP_Admin {
    
    public function __construct() {
        // Menús de administración
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Scripts y estilos del admin
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        
        // Metaboxes para productos
        add_action('add_meta_boxes', array($this, 'add_product_metaboxes'));
        add_action('save_post', array($this, 'save_product_meta'));
        
        // AJAX handlers
        add_action('wp_ajax_rwp_preview_template', array($this, 'ajax_preview_template'));
        add_action('wp_ajax_rwp_import_template', array($this, 'ajax_import_template'));
        add_action('wp_ajax_rwp_import_ai_code', array($this, 'ajax_import_ai_code'));
        add_action('wp_ajax_rwp_export_template', array($this, 'ajax_export_template'));
        add_action('wp_ajax_rwp_delete_template', array($this, 'ajax_delete_template'));
        add_action('wp_ajax_rwp_get_template_stats', array($this, 'ajax_get_template_stats'));
        
        // Configuraciones
        add_action('admin_init', array($this, 'register_settings'));
        
        // Ocultar notices en páginas del plugin
        add_action('admin_head', array($this, 'hide_admin_notices'));
    }
    
    /**
     * Agregar menús de administración
     */
    public function add_admin_menu() {
        // Menú principal
        add_menu_page(
            'React WooCommerce Products',
            'React Products',
            'manage_options',
            'react-woo-products',
            array($this, 'admin_page'),
            'dashicons-layout',
            56
        );
        
        // Submenús
        add_submenu_page(
            'react-woo-products',
            'Plantillas',
            'Plantillas',
            'manage_options',
            'react-woo-products-templates',
            array($this, 'templates_page')
        );
        
        add_submenu_page(
            'react-woo-products',
            'Configuración',
            'Configuración',
            'manage_options',
            'react-woo-products-settings',
            array($this, 'settings_page')
        );
        
        add_submenu_page(
            'react-woo-products',
            'Analytics',
            'Analytics',
            'manage_options',
            'react-woo-products-analytics',
            array($this, 'analytics_page')
        );
    }
    
    /**
     * Cargar assets del admin
     */
    public function enqueue_admin_assets($hook) {
        // Solo cargar en páginas del plugin
        if (strpos($hook, 'react-woo-products') === false && $hook !== 'post.php' && $hook !== 'post-new.php') {
            return;
        }
        
        // Scripts
        wp_enqueue_script(
            'rwp-admin-js',
            RWP_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery', 'wp-util'),
            RWP_VERSION,
            true
        );
        
        // Estilos
        wp_enqueue_style(
            'rwp-admin-css',
            RWP_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            RWP_VERSION
        );
        
        // Localizar scripts
        wp_localize_script('rwp-admin-js', 'rwpAdmin', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('rwp_admin_nonce'),
            'strings' => array(
                'confirmDelete' => __('¿Estás seguro de que quieres eliminar esta plantilla?', 'react-woo-products'),
                'importing' => __('Importando...', 'react-woo-products'),
                'exporting' => __('Exportando...', 'react-woo-products'),
                'error' => __('Error', 'react-woo-products'),
                'success' => __('Éxito', 'react-woo-products'),
            )
        ));
    }
    
    /**
     * Página principal del admin
     */
    public function admin_page() {
        $template_manager = RWP_Template_Manager::getInstance();
        $templates = $template_manager->get_available_templates();
        $stats = $template_manager->get_template_stats();
        
        include RWP_PLUGIN_PATH . 'admin/views/dashboard.php';
    }
    
    /**
     * Página de plantillas
     */
    public function templates_page() {
        $template_manager = RWP_Template_Manager::getInstance();
        $templates = $template_manager->get_available_templates();
        
        include RWP_PLUGIN_PATH . 'admin/views/templates.php';
    }
    
    /**
     * Página de configuración
     */
    public function settings_page() {
        include RWP_PLUGIN_PATH . 'admin/views/settings.php';
    }
    
    /**
     * Página de analytics
     */
    public function analytics_page() {
        $template_manager = RWP_Template_Manager::getInstance();
        $stats = $template_manager->get_template_stats();
        
        include RWP_PLUGIN_PATH . 'admin/views/analytics.php';
    }
    
    /**
     * Agregar metaboxes a los productos
     */
    public function add_product_metaboxes() {
        add_meta_box(
            'rwp_product_settings',
            'React Template Settings',
            array($this, 'product_metabox_callback'),
            'product',
            'side',
            'default'
        );
    }
    
    /**
     * Contenido del metabox del producto
     */
    public function product_metabox_callback($post) {
        wp_nonce_field('rwp_product_meta_nonce', 'rwp_product_meta_nonce');
        
        $react_enabled = get_post_meta($post->ID, '_rwp_react_enabled', true);
        $template_id = get_post_meta($post->ID, '_rwp_template_id', true);
        
        $template_manager = RWP_Template_Manager::getInstance();
        $templates = $template_manager->get_available_templates();
        $global_settings = get_option('rwp_global_settings');
        
        ?>
        <div class="rwp-product-settings">
            <p>
                <label>
                    <input type="checkbox" name="rwp_react_enabled" value="enabled" <?php checked($react_enabled, 'enabled'); ?>>
                    <?php _e('Habilitar React para este producto', 'react-woo-products'); ?>
                </label>
            </p>
            
            <p>
                <label for="rwp_template_id"><?php _e('Plantilla:', 'react-woo-products'); ?></label>
                <select name="rwp_template_id" id="rwp_template_id">
                    <option value=""><?php _e('Usar plantilla por defecto', 'react-woo-products'); ?></option>
                    <?php foreach ($templates as $template): ?>
                        <option value="<?php echo esc_attr($template['id']); ?>" <?php selected($template_id, $template['id']); ?>>
                            <?php echo esc_html($template['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </p>
            
            <?php if ($react_enabled === 'enabled' && !empty($template_id)): ?>
                <p>
                    <button type="button" class="button" id="rwp-preview-template" data-product-id="<?php echo $post->ID; ?>" data-template-id="<?php echo esc_attr($template_id); ?>">
                        <?php _e('Vista previa', 'react-woo-products'); ?>
                    </button>
                </p>
            <?php endif; ?>
        </div>
        
        <style>
        .rwp-product-settings p {
            margin-bottom: 15px;
        }
        .rwp-product-settings label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }
        .rwp-product-settings select {
            width: 100%;
        }
        </style>
        <?php
    }
    
    /**
     * Guardar metadatos del producto
     */
    public function save_product_meta($post_id) {
        // Verificar nonce
        if (!isset($_POST['rwp_product_meta_nonce']) || !wp_verify_nonce($_POST['rwp_product_meta_nonce'], 'rwp_product_meta_nonce')) {
            return;
        }
        
        // Verificar permisos
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Verificar que es un producto
        if (get_post_type($post_id) !== 'product') {
            return;
        }
        
        // Guardar configuración de React
        $react_enabled = isset($_POST['rwp_react_enabled']) ? 'enabled' : 'disabled';
        update_post_meta($post_id, '_rwp_react_enabled', $react_enabled);
        
        // Guardar plantilla seleccionada
        $template_id = sanitize_text_field($_POST['rwp_template_id']);
        update_post_meta($post_id, '_rwp_template_id', $template_id);
    }
    
    /**
     * Registrar configuraciones
     */
    public function register_settings() {
        register_setting('rwp_settings', 'rwp_global_settings', array(
            'sanitize_callback' => array($this, 'sanitize_settings')
        ));
        
        add_settings_section(
            'rwp_general_settings',
            __('Configuración General', 'react-woo-products'),
            array($this, 'settings_section_callback'),
            'rwp_settings'
        );
        
        add_settings_field(
            'enable_react_globally',
            __('Habilitar React globalmente', 'react-woo-products'),
            array($this, 'checkbox_field_callback'),
            'rwp_settings',
            'rwp_general_settings',
            array('field' => 'enable_react_globally')
        );
        
        add_settings_field(
            'default_template',
            __('Plantilla por defecto', 'react-woo-products'),
            array($this, 'select_field_callback'),
            'rwp_settings',
            'rwp_general_settings',
            array('field' => 'default_template')
        );
        
        add_settings_field(
            'cache_enabled',
            __('Habilitar caché', 'react-woo-products'),
            array($this, 'checkbox_field_callback'),
            'rwp_settings',
            'rwp_general_settings',
            array('field' => 'cache_enabled')
        );
        
        add_settings_field(
            'cache_duration',
            __('Duración del caché (segundos)', 'react-woo-products'),
            array($this, 'number_field_callback'),
            'rwp_settings',
            'rwp_general_settings',
            array('field' => 'cache_duration')
        );
    }
    
    /**
     * Callback para sección de configuraciones
     */
    public function settings_section_callback() {
        echo '<p>' . __('Configuración general del plugin React WooCommerce Products.', 'react-woo-products') . '</p>';
    }
    
    /**
     * Callback para campos checkbox
     */
    public function checkbox_field_callback($args) {
        $options = get_option('rwp_global_settings');
        $field = $args['field'];
        $value = isset($options[$field]) ? $options[$field] : false;
        
        echo '<input type="checkbox" name="rwp_global_settings[' . $field . ']" value="1" ' . checked(1, $value, false) . '>';
    }
    
    /**
     * Callback para campos select
     */
    public function select_field_callback($args) {
        $options = get_option('rwp_global_settings');
        $field = $args['field'];
        $value = isset($options[$field]) ? $options[$field] : '';
        
        $template_manager = RWP_Template_Manager::getInstance();
        $templates = $template_manager->get_available_templates();
        
        echo '<select name="rwp_global_settings[' . $field . ']">';
        echo '<option value="">' . __('Seleccionar plantilla', 'react-woo-products') . '</option>';
        foreach ($templates as $template) {
            echo '<option value="' . esc_attr($template['id']) . '" ' . selected($value, $template['id'], false) . '>';
            echo esc_html($template['name']);
            echo '</option>';
        }
        echo '</select>';
    }
    
    /**
     * Callback para campos numéricos
     */
    public function number_field_callback($args) {
        $options = get_option('rwp_global_settings');
        $field = $args['field'];
        $value = isset($options[$field]) ? $options[$field] : '';
        
        echo '<input type="number" name="rwp_global_settings[' . $field . ']" value="' . esc_attr($value) . '" min="0">';
    }
    
    /**
     * Sanitizar configuraciones
     */
    public function sanitize_settings($input) {
        $sanitized = array();
        
        $sanitized['enable_react_globally'] = isset($input['enable_react_globally']) ? true : false;
        $sanitized['default_template'] = sanitize_text_field($input['default_template']);
        $sanitized['cache_enabled'] = isset($input['cache_enabled']) ? true : false;
        $sanitized['cache_duration'] = absint($input['cache_duration']);
        
        return $sanitized;
    }
    
    /**
     * AJAX: Vista previa de plantilla
     */
    public function ajax_preview_template() {
        check_ajax_referer('rwp_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('No tienes permisos suficientes.');
        }
        
        $product_id = intval($_POST['product_id']);
        $template_id = sanitize_text_field($_POST['template_id']);
        
        $template_manager = RWP_Template_Manager::getInstance();
        $template = $template_manager->get_template($template_id);
        
        if (!$template) {
            wp_send_json_error('Plantilla no encontrada');
        }
        
        $preview_url = add_query_arg(array(
            'rwp_preview' => '1',
            'template_id' => $template_id,
            'product_id' => $product_id
        ), get_permalink($product_id));
        
        wp_send_json_success(array(
            'preview_url' => $preview_url
        ));
    }
    
    /**
     * AJAX: Importar plantilla
     */
    public function ajax_import_template() {
        check_ajax_referer('rwp_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('No tienes permisos suficientes.');
        }
        
        if (!isset($_FILES['template_file'])) {
            wp_send_json_error('No se seleccionó ningún archivo');
        }
        
        $template_manager = RWP_Template_Manager::getInstance();
        $result = $template_manager->import_template($_FILES['template_file']['tmp_name']);
        
        if ($result['success']) {
            wp_send_json_success($result['message']);
        } else {
            wp_send_json_error($result['message']);
        }
    }
    
    /**
     * AJAX: Importar código React generado por IA
     */
    public function ajax_import_ai_code() {
        check_ajax_referer('rwp_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('No tienes permisos suficientes.');
        }
        
        if (!isset($_POST['ai_code'])) {
            wp_send_json_error('No se proporcionó ningún código');
        }
        
        $ai_code = sanitize_textarea_field($_POST['ai_code']);
        $template_name = isset($_POST['template_name']) ? sanitize_text_field($_POST['template_name']) : '';
        $template_description = isset($_POST['template_description']) ? sanitize_textarea_field($_POST['template_description']) : '';
        
        $template_manager = RWP_Template_Manager::getInstance();
        $result = $template_manager->import_ai_code($ai_code, $template_name, $template_description);
        
        if ($result['success']) {
            wp_send_json_success($result['message']);
        } else {
            wp_send_json_error($result['message']);
        }
    }
    
    /**
     * AJAX: Exportar plantilla
     */
    public function ajax_export_template() {
        check_ajax_referer('rwp_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('No tienes permisos suficientes.');
        }
        
        $template_id = sanitize_text_field($_POST['template_id']);
        
        $template_manager = RWP_Template_Manager::getInstance();
        $result = $template_manager->export_template($template_id);
        
        if ($result['success']) {
            wp_send_json_success(array(
                'message' => $result['message'],
                'download_url' => wp_get_attachment_url($result['file_path'])
            ));
        } else {
            wp_send_json_error($result['message']);
        }
    }
    
    /**
     * AJAX: Eliminar plantilla
     */
    public function ajax_delete_template() {
        check_ajax_referer('rwp_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('No tienes permisos suficientes.');
        }
        
        $template_id = sanitize_text_field($_POST['template_id']);
        
        $template_manager = RWP_Template_Manager::getInstance();
        $template = $template_manager->get_template($template_id);
        
        if (!$template || !$template['is_custom']) {
            wp_send_json_error('Solo se pueden eliminar plantillas personalizadas');
        }
        
        // Eliminar directorio de la plantilla
        $this->delete_directory($template['path']);
        
        wp_send_json_success('Plantilla eliminada exitosamente');
    }
    
    /**
     * AJAX: Obtener estadísticas de plantilla
     */
    public function ajax_get_template_stats() {
        check_ajax_referer('rwp_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('No tienes permisos suficientes.');
        }
        
        $template_id = sanitize_text_field($_POST['template_id']);
        
        $template_manager = RWP_Template_Manager::getInstance();
        $stats = $template_manager->get_template_stats($template_id);
        
        wp_send_json_success($stats);
    }
    
    /**
     * Eliminar directorio recursivamente
     */
    private function delete_directory($dir) {
        if (!is_dir($dir)) {
            return false;
        }
        
        $files = array_diff(scandir($dir), array('.', '..'));
        
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                $this->delete_directory($path);
            } else {
                unlink($path);
            }
        }
        
        return rmdir($dir);
    }
    
    /**
     * Ocultar notices de WordPress en páginas del plugin
     */
    public function hide_admin_notices() {
        $screen = get_current_screen();
        
        // Verificar si estamos en una página del plugin
        if (isset($screen->id) && strpos($screen->id, 'react-woo-products') !== false) {
            // Remover todas las notices de admin
            remove_all_actions('admin_notices');
            remove_all_actions('all_admin_notices');
            
            // También agregar CSS para ocultar cualquier notice que pueda aparecer
            ?>
            <style>
            .notice, .error, .updated, .update-nag {
                display: none !important;
            }
            </style>
            <?php
        }
    }
} 