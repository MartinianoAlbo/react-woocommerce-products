<?php
/**
 * Clase para manejar las plantillas React del plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class RWP_Template_Manager {
    
    private static $instance = null;
    private $templates = array();
    
    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new RWP_Template_Manager();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->load_templates();
    }
    
    /**
     * Cargar plantillas disponibles
     */
    private function load_templates() {
        // Plantillas del plugin
        $plugin_templates = $this->scan_templates_directory(RWP_TEMPLATES_PATH);
        
        // Plantillas personalizadas del tema
        $custom_templates = array();
        if (file_exists(RWP_CUSTOM_TEMPLATES_PATH)) {
            $custom_templates = $this->scan_templates_directory(RWP_CUSTOM_TEMPLATES_PATH);
        }
        
        // Combinar plantillas
        $this->templates = array_merge($plugin_templates, $custom_templates);
        
        // Permitir filtrar plantillas
        $this->templates = apply_filters('rwp_available_templates', $this->templates);
    }
    
    /**
     * Escanear directorio de plantillas
     */
    private function scan_templates_directory($directory) {
        $templates = array();
        
        if (!is_dir($directory)) {
            return $templates;
        }
        
        $template_dirs = scandir($directory);
        
        foreach ($template_dirs as $template_dir) {
            if ($template_dir === '.' || $template_dir === '..') {
                continue;
            }
            
            $template_path = $directory . $template_dir . '/';
            
            if (is_dir($template_path)) {
                $config_file = $template_path . 'config.json';
                $js_file = $template_path . 'index.js';
                $css_file = $template_path . 'style.css';
                
                // Verificar que existan los archivos necesarios
                if (file_exists($config_file) && file_exists($js_file)) {
                    $config = json_decode(file_get_contents($config_file), true);
                    
                    if (json_last_error() === JSON_ERROR_NONE && $config) {
                        $templates[$template_dir] = array(
                            'id' => $template_dir,
                            'name' => isset($config['name']) ? $config['name'] : $template_dir,
                            'description' => isset($config['description']) ? $config['description'] : '',
                            'version' => isset($config['version']) ? $config['version'] : '1.0.0',
                            'author' => isset($config['author']) ? $config['author'] : '',
                            'thumbnail' => isset($config['thumbnail']) ? $template_path . $config['thumbnail'] : '',
                            'category' => isset($config['category']) ? $config['category'] : 'general',
                            'tags' => isset($config['tags']) ? $config['tags'] : array(),
                            'settings' => isset($config['settings']) ? $config['settings'] : array(),
                            'path' => $template_path,
                            'js_file' => $js_file,
                            'css_file' => file_exists($css_file) ? $css_file : '',
                            'config' => $config,
                            'is_custom' => strpos($directory, RWP_CUSTOM_TEMPLATES_PATH) !== false,
                        );
                    }
                }
            }
        }
        
        return $templates;
    }
    
    /**
     * Obtener todas las plantillas disponibles
     */
    public function get_available_templates() {
        return $this->templates;
    }
    
    /**
     * Obtener una plantilla específica
     */
    public function get_template($template_id) {
        return isset($this->templates[$template_id]) ? $this->templates[$template_id] : null;
    }
    
    /**
     * Obtener plantilla asignada a un producto
     */
    public function get_product_template($product_id) {
        // Verificar si React está habilitado para este producto
        if (!rwp_is_react_enabled_for_product($product_id)) {
            return null;
        }
        
        // Obtener plantilla específica del producto
        $template_id = get_post_meta($product_id, '_rwp_template_id', true);
        
        // Si no hay plantilla específica, usar la por defecto
        if (empty($template_id)) {
            $global_settings = get_option('rwp_global_settings');
            $template_id = $global_settings['default_template'];
        }
        
        return $this->get_template($template_id);
    }
    
    /**
     * Asignar plantilla a un producto
     */
    public function assign_template_to_product($product_id, $template_id) {
        if (!isset($this->templates[$template_id])) {
            return false;
        }
        
        update_post_meta($product_id, '_rwp_template_id', $template_id);
        return true;
    }
    
    /**
     * Habilitar/deshabilitar React para un producto
     */
    public function toggle_react_for_product($product_id, $enabled = true) {
        $value = $enabled ? 'enabled' : 'disabled';
        update_post_meta($product_id, '_rwp_react_enabled', $value);
        return true;
    }
    
    /**
     * Obtener URL de los assets de una plantilla
     */
    public function get_template_asset_url($template_id, $asset_file) {
        $template = $this->get_template($template_id);
        
        if (!$template) {
            return '';
        }
        
        $asset_path = $template['path'] . $asset_file;
        
        if (!file_exists($asset_path)) {
            return '';
        }
        
        // Convertir ruta del servidor a URL
        if ($template['is_custom']) {
            $base_url = get_stylesheet_directory_uri() . '/react-woo-templates/';
            $relative_path = str_replace(RWP_CUSTOM_TEMPLATES_PATH, '', $asset_path);
        } else {
            $base_url = RWP_PLUGIN_URL . 'templates/';
            $relative_path = str_replace(RWP_TEMPLATES_PATH, '', $asset_path);
        }
        
        return $base_url . $relative_path;
    }
    
    /**
     * Obtener configuración de una plantilla
     */
    public function get_template_config($template_id) {
        $template = $this->get_template($template_id);
        return $template ? $template['config'] : null;
    }
    
    /**
     * Validar estructura de plantilla
     */
    public function validate_template($template_path) {
        $required_files = array('config.json', 'index.js');
        $errors = array();
        
        foreach ($required_files as $file) {
            if (!file_exists($template_path . $file)) {
                $errors[] = sprintf('Archivo requerido no encontrado: %s', $file);
            }
        }
        
        // Validar config.json
        $config_file = $template_path . 'config.json';
        if (file_exists($config_file)) {
            $config = json_decode(file_get_contents($config_file), true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                $errors[] = 'El archivo config.json no es válido: ' . json_last_error_msg();
            } else {
                $required_config = array('name', 'version');
                foreach ($required_config as $key) {
                    if (!isset($config[$key])) {
                        $errors[] = sprintf('Campo requerido faltante en config.json: %s', $key);
                    }
                }
            }
        }
        
        return array(
            'valid' => empty($errors),
            'errors' => $errors
        );
    }
    
    /**
     * Importar plantilla desde archivo ZIP
     */
    public function import_template($zip_file_path, $target_directory = null) {
        if (!class_exists('ZipArchive')) {
            return array(
                'success' => false,
                'message' => 'La extensión ZipArchive no está disponible'
            );
        }
        
        $zip = new ZipArchive();
        $result = $zip->open($zip_file_path);
        
        if ($result !== TRUE) {
            return array(
                'success' => false,
                'message' => 'No se pudo abrir el archivo ZIP'
            );
        }
        
        // Usar directorio personalizado por defecto
        if (!$target_directory) {
            $target_directory = RWP_CUSTOM_TEMPLATES_PATH;
        }
        
        // Crear directorio si no existe
        if (!file_exists($target_directory)) {
            wp_mkdir_p($target_directory);
        }
        
        // Extraer archivo
        $extract_result = $zip->extractTo($target_directory);
        $zip->close();
        
        if (!$extract_result) {
            return array(
                'success' => false,
                'message' => 'Error al extraer el archivo ZIP'
            );
        }
        
        // Recargar plantillas
        $this->load_templates();
        
        return array(
            'success' => true,
            'message' => 'Plantilla importada exitosamente'
        );
    }
    
    /**
     * Importar código React generado por IA y convertirlo a plantilla
     */
    public function import_ai_code($ai_code, $template_name = null, $template_description = '') {
        // Validar que el código no esté vacío
        if (empty($ai_code)) {
            return array(
                'success' => false,
                'message' => 'El código proporcionado está vacío'
            );
        }

        // Generar nombre de plantilla único si no se proporciona
        if (empty($template_name)) {
            $template_name = 'AI Template ' . date('Y-m-d H:i:s');
        }
        
        // Crear ID único para la plantilla
        $template_id = 'ai-template-' . sanitize_title($template_name) . '-' . time();
        
        // Directorio de destino
        $target_directory = RWP_CUSTOM_TEMPLATES_PATH;
        $template_path = $target_directory . $template_id . '/';
        
        // Crear directorio si no existe
        if (!file_exists($target_directory)) {
            wp_mkdir_p($target_directory);
        }
        
        // Crear directorio de la plantilla
        if (!wp_mkdir_p($template_path)) {
            return array(
                'success' => false,
                'message' => 'No se pudo crear el directorio de la plantilla'
            );
        }

        // Procesar el código React
        $processed_code = $this->process_ai_react_code($ai_code);
        
        // Crear config.json
        $config = array(
            'name' => $template_name,
            'description' => !empty($template_description) ? $template_description : 'Plantilla generada por IA',
            'version' => '1.0.0',
            'author' => 'IA Generator',
            'category' => 'ai-generated',
            'tags' => array('ai', 'generated', 'react'),
            'settings' => array()
        );
        
        $config_content = json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
        if (file_put_contents($template_path . 'config.json', $config_content) === false) {
            return array(
                'success' => false,
                'message' => 'No se pudo crear el archivo config.json'
            );
        }
        
        // Crear index.js con el código procesado
        if (file_put_contents($template_path . 'index.js', $processed_code['js']) === false) {
            return array(
                'success' => false,
                'message' => 'No se pudo crear el archivo index.js'
            );
        }
        
        // Crear style.css con los estilos extraídos
        if (file_put_contents($template_path . 'style.css', $processed_code['css']) === false) {
            return array(
                'success' => false,
                'message' => 'No se pudo crear el archivo style.css'
            );
        }

        // Recargar plantillas
        $this->load_templates();
        
        return array(
            'success' => true,
            'message' => 'Código IA importado exitosamente como plantilla: ' . $template_name,
            'template_id' => $template_id
        );
    }
    
    /**
     * Procesar código React de IA para adaptarlo al plugin
     */
    private function process_ai_react_code($ai_code) {
        // Extraer CSS si está incluido en el código
        $css_content = '';
        $js_content = $ai_code;
        
        // Buscar estilos inline o bloques de CSS
        if (preg_match('/```css\s*(.*?)\s*```/s', $ai_code, $css_matches)) {
            $css_content = $css_matches[1];
            $js_content = str_replace($css_matches[0], '', $js_content);
        }
        
        // Buscar estilos en <style> tags
        if (preg_match('/<style[^>]*>(.*?)<\/style>/s', $ai_code, $style_matches)) {
            $css_content .= "\n" . $style_matches[1];
            $js_content = str_replace($style_matches[0], '', $js_content);
        }
        
        // Limpiar el código JS
        $js_content = trim($js_content);
        
        // Si el código no parece ser React válido, intentar envolver en función
        if (!preg_match('/export\s+default|function\s+\w+|const\s+\w+\s*=|=>\s*{/', $js_content)) {
            $js_content = "export default function AITemplate(props) {\n    const { product } = props;\n    \n    return (\n        " . $js_content . "\n    );\n}";
        }
        
        // Asegurar que tenemos imports básicos de React
        if (!preg_match('/import.*React/i', $js_content)) {
            $js_content = "import React from 'react';\n\n" . $js_content;
        }
        
        // Agregar comentarios de identificación
        $js_content = "// Plantilla generada automáticamente desde código IA\n" .
                     "// Fecha: " . date('Y-m-d H:i:s') . "\n\n" . $js_content;
        
        return array(
            'js' => $js_content,
            'css' => $css_content
        );
    }
    
    /**
     * Exportar plantilla a archivo ZIP
     */
    public function export_template($template_id, $output_path = null) {
        $template = $this->get_template($template_id);
        
        if (!$template) {
            return array(
                'success' => false,
                'message' => 'Plantilla no encontrada'
            );
        }
        
        if (!class_exists('ZipArchive')) {
            return array(
                'success' => false,
                'message' => 'La extensión ZipArchive no está disponible'
            );
        }
        
        if (!$output_path) {
            $output_path = wp_upload_dir()['path'] . '/' . $template_id . '.zip';
        }
        
        $zip = new ZipArchive();
        $result = $zip->open($output_path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        
        if ($result !== TRUE) {
            return array(
                'success' => false,
                'message' => 'No se pudo crear el archivo ZIP'
            );
        }
        
        // Agregar archivos de la plantilla
        $template_path = $template['path'];
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($template_path),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
        
        foreach ($files as $file) {
            if (!$file->isDir()) {
                $file_path = $file->getRealPath();
                $relative_path = substr($file_path, strlen($template_path));
                $zip->addFile($file_path, $template_id . '/' . $relative_path);
            }
        }
        
        $zip->close();
        
        return array(
            'success' => true,
            'message' => 'Plantilla exportada exitosamente',
            'file_path' => $output_path
        );
    }
    
    /**
     * Obtener estadísticas de uso de plantillas
     */
    public function get_template_stats($template_id = null) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'rwp_template_analytics';
        
        if ($template_id) {
            $stats = $wpdb->get_row($wpdb->prepare(
                "SELECT template_id, SUM(views) as total_views, SUM(conversions) as total_conversions 
                 FROM $table_name WHERE template_id = %s GROUP BY template_id",
                $template_id
            ));
        } else {
            $stats = $wpdb->get_results(
                "SELECT template_id, SUM(views) as total_views, SUM(conversions) as total_conversions 
                 FROM $table_name GROUP BY template_id ORDER BY total_views DESC"
            );
        }
        
        return $stats;
    }
} 