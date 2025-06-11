<?php
if (!defined('ABSPATH')) {
    exit;
}

$global_settings = get_option('rwp_global_settings', array());
$template_manager = RWP_Template_Manager::getInstance();
$templates = $template_manager->get_available_templates();
?>

<div class="wrap">
    <h1><?php _e('Configuración React WooCommerce', 'react-woo-products'); ?></h1>
    
    <form method="post" action="options.php">
        <?php
        settings_fields('rwp_settings');
        do_settings_sections('rwp_settings');
        ?>
        
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row">
                        <label for="enable_react_globally"><?php _e('Habilitar React Globalmente', 'react-woo-products'); ?></label>
                    </th>
                    <td>
                        <input type="checkbox" id="enable_react_globally" name="rwp_global_settings[enable_react_globally]" value="1" <?php checked(1, $global_settings['enable_react_globally'] ?? false); ?>>
                        <p class="description"><?php _e('Si está habilitado, todos los productos usarán React por defecto (se puede desactivar individualmente por producto).', 'react-woo-products'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="default_template"><?php _e('Plantilla por Defecto', 'react-woo-products'); ?></label>
                    </th>
                    <td>
                        <select id="default_template" name="rwp_global_settings[default_template]">
                            <?php foreach ($templates as $template): ?>
                                <option value="<?php echo esc_attr($template['id']); ?>" <?php selected($global_settings['default_template'] ?? 'template-1', $template['id']); ?>>
                                    <?php echo esc_html($template['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description"><?php _e('Plantilla que se usará por defecto cuando React esté habilitado.', 'react-woo-products'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="cache_enabled"><?php _e('Habilitar Caché', 'react-woo-products'); ?></label>
                    </th>
                    <td>
                        <input type="checkbox" id="cache_enabled" name="rwp_global_settings[cache_enabled]" value="1" <?php checked(1, $global_settings['cache_enabled'] ?? true); ?>>
                        <p class="description"><?php _e('Cachear el contenido renderizado de React para mejorar el rendimiento.', 'react-woo-products'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="cache_duration"><?php _e('Duración del Caché', 'react-woo-products'); ?></label>
                    </th>
                    <td>
                        <input type="number" id="cache_duration" name="rwp_global_settings[cache_duration]" value="<?php echo esc_attr($global_settings['cache_duration'] ?? 3600); ?>" min="60" max="86400">
                        <p class="description"><?php _e('Duración del caché en segundos (60 - 86400).', 'react-woo-products'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="debug_mode"><?php _e('Modo Debug', 'react-woo-products'); ?></label>
                    </th>
                    <td>
                        <input type="checkbox" id="debug_mode" name="rwp_global_settings[debug_mode]" value="1" <?php checked(1, $global_settings['debug_mode'] ?? false); ?>>
                        <p class="description"><?php _e('Habilitar información de debug en la consola del navegador.', 'react-woo-products'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="preload_react"><?php _e('Precargar React', 'react-woo-products'); ?></label>
                    </th>
                    <td>
                        <input type="checkbox" id="preload_react" name="rwp_global_settings[preload_react]" value="1" <?php checked(1, $global_settings['preload_react'] ?? true); ?>>
                        <p class="description"><?php _e('Precargar las librerías de React en todas las páginas para mejor rendimiento.', 'react-woo-products'); ?></p>
                    </td>
                </tr>
            </tbody>
        </table>
        
        <?php submit_button(); ?>
    </form>
    
    <hr>
    
    <div class="rwp-settings-section">
        <h2><?php _e('Información del Sistema', 'react-woo-products'); ?></h2>
        
        <table class="widefat">
            <thead>
                <tr>
                    <th><?php _e('Componente', 'react-woo-products'); ?></th>
                    <th><?php _e('Estado', 'react-woo-products'); ?></th>
                    <th><?php _e('Información', 'react-woo-products'); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>WordPress</strong></td>
                    <td>
                        <span class="rwp-status-badge rwp-status-success">
                            <?php _e('Activo', 'react-woo-products'); ?>
                        </span>
                    </td>
                    <td><?php echo get_bloginfo('version'); ?></td>
                </tr>
                <tr>
                    <td><strong>WooCommerce</strong></td>
                    <td>
                        <?php if (class_exists('WooCommerce')): ?>
                            <span class="rwp-status-badge rwp-status-success">
                                <?php _e('Activo', 'react-woo-products'); ?>
                            </span>
                        <?php else: ?>
                            <span class="rwp-status-badge rwp-status-error">
                                <?php _e('Inactivo', 'react-woo-products'); ?>
                            </span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php 
                        if (defined('WC_VERSION')) {
                            echo WC_VERSION;
                        } else {
                            _e('No disponible', 'react-woo-products');
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <td><strong><?php _e('Plantillas Disponibles', 'react-woo-products'); ?></strong></td>
                    <td>
                        <span class="rwp-status-badge rwp-status-info">
                            <?php echo count($templates); ?> <?php _e('plantillas', 'react-woo-products'); ?>
                        </span>
                    </td>
                    <td>
                        <?php
                        $template_names = array_column($templates, 'name');
                        echo implode(', ', $template_names);
                        ?>
                    </td>
                </tr>
                <tr>
                    <td><strong><?php _e('Directorio de Plantillas', 'react-woo-products'); ?></strong></td>
                    <td>
                        <?php if (is_writable(RWP_CUSTOM_TEMPLATES_PATH)): ?>
                            <span class="rwp-status-badge rwp-status-success">
                                <?php _e('Escribible', 'react-woo-products'); ?>
                            </span>
                        <?php else: ?>
                            <span class="rwp-status-badge rwp-status-warning">
                                <?php _e('Solo lectura', 'react-woo-products'); ?>
                            </span>
                        <?php endif; ?>
                    </td>
                    <td><code><?php echo esc_html(RWP_CUSTOM_TEMPLATES_PATH); ?></code></td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <div class="rwp-settings-section">
        <h2><?php _e('Herramientas', 'react-woo-products'); ?></h2>
        
        <div class="rwp-tools-grid">
            <div class="rwp-tool-card">
                <h3><?php _e('Limpiar Caché', 'react-woo-products'); ?></h3>
                <p><?php _e('Eliminar todos los archivos de caché generados por el plugin.', 'react-woo-products'); ?></p>
                <button type="button" class="button" id="rwp-clear-cache">
                    <span class="dashicons dashicons-trash"></span>
                    <?php _e('Limpiar Caché', 'react-woo-products'); ?>
                </button>
            </div>
            
            <div class="rwp-tool-card">
                <h3><?php _e('Exportar Configuración', 'react-woo-products'); ?></h3>
                <p><?php _e('Descargar la configuración actual como archivo JSON.', 'react-woo-products'); ?></p>
                <button type="button" class="button" id="rwp-export-settings">
                    <span class="dashicons dashicons-download"></span>
                    <?php _e('Exportar', 'react-woo-products'); ?>
                </button>
            </div>
            
            <div class="rwp-tool-card">
                <h3><?php _e('Importar Configuración', 'react-woo-products'); ?></h3>
                <p><?php _e('Cargar configuración desde un archivo JSON.', 'react-woo-products'); ?></p>
                <input type="file" id="rwp-import-settings-file" accept=".json" style="display: none;">
                <button type="button" class="button" id="rwp-import-settings">
                    <span class="dashicons dashicons-upload"></span>
                    <?php _e('Importar', 'react-woo-products'); ?>
                </button>
            </div>
            
            <div class="rwp-tool-card">
                <h3><?php _e('Restablecer Plugin', 'react-woo-products'); ?></h3>
                <p><?php _e('Restaurar todas las configuraciones a sus valores por defecto.', 'react-woo-products'); ?></p>
                <button type="button" class="button button-link-delete" id="rwp-reset-plugin">
                    <span class="dashicons dashicons-undo"></span>
                    <?php _e('Restablecer', 'react-woo-products'); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.rwp-settings-section {
    margin-top: 30px;
}

.rwp-status-badge {
    padding: 4px 8px;
    border-radius: 3px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.rwp-status-success {
    background: #d4edda;
    color: #155724;
}

.rwp-status-error {
    background: #f8d7da;
    color: #721c24;
}

.rwp-status-warning {
    background: #fff3cd;
    color: #856404;
}

.rwp-status-info {
    background: #cce7ff;
    color: #004085;
}

.rwp-tools-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.rwp-tool-card {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 20px;
}

.rwp-tool-card h3 {
    margin-top: 0;
    margin-bottom: 10px;
}

.rwp-tool-card p {
    color: #666;
    margin-bottom: 15px;
}

.rwp-tool-card .button {
    width: 100%;
}
</style> 