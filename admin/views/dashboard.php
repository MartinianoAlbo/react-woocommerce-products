<?php
if (!defined('ABSPATH')) {
    exit;
}

$template_manager = RWP_Template_Manager::getInstance();
$templates = $template_manager->get_available_templates();
$stats = $template_manager->get_template_stats();
$global_settings = get_option('rwp_global_settings', array());

// Calcular estadísticas rápidas
$total_views = array_sum(array_column($stats, 'views'));
$total_conversions = array_sum(array_column($stats, 'conversions'));
$conversion_rate = $total_views > 0 ? ($total_conversions / $total_views) * 100 : 0;

// Obtener productos con React habilitado
global $wpdb;
$react_products_count = $wpdb->get_var("
    SELECT COUNT(*) 
    FROM {$wpdb->postmeta} 
    WHERE meta_key = '_rwp_react_enabled' 
    AND meta_value = 'enabled'
");
?>

<div class="wrap">
    <h1><?php _e('React WooCommerce Products', 'react-woo-products'); ?></h1>
    
    <div class="rwp-dashboard-welcome">
        <div class="rwp-welcome-panel">
            <div class="rwp-welcome-panel-content">
                <h2><?php _e('¡Bienvenido a React WooCommerce Products!', 'react-woo-products'); ?></h2>
                <p class="about-description">
                    <?php _e('Transforma tus productos de WooCommerce con plantillas React modernas y personalizables.', 'react-woo-products'); ?>
                </p>
                
                <div class="rwp-welcome-panel-column-container">
                    <div class="rwp-welcome-panel-column">
                        <h3><?php _e('Primeros Pasos', 'react-woo-products'); ?></h3>
                        <ul>
                            <li><a href="<?php echo admin_url('admin.php?page=react-woo-products-templates'); ?>" class="welcome-icon dashicons-admin-appearance"><?php _e('Ver Plantillas Disponibles', 'react-woo-products'); ?></a></li>
                            <li><a href="<?php echo admin_url('admin.php?page=react-woo-products-settings'); ?>" class="welcome-icon dashicons-admin-settings"><?php _e('Configurar el Plugin', 'react-woo-products'); ?></a></li>
                            <li><a href="<?php echo admin_url('edit.php?post_type=product'); ?>" class="welcome-icon dashicons-products"><?php _e('Habilitar React en Productos', 'react-woo-products'); ?></a></li>
                        </ul>
                    </div>
                    
                    <div class="rwp-welcome-panel-column">
                        <h3><?php _e('Recursos', 'react-woo-products'); ?></h3>
                        <ul>
                            <li><a href="#" class="welcome-icon dashicons-book-alt"><?php _e('Documentación', 'react-woo-products'); ?></a></li>
                            <li><a href="#" class="welcome-icon dashicons-video-alt3"><?php _e('Tutoriales', 'react-woo-products'); ?></a></li>
                            <li><a href="#" class="welcome-icon dashicons-sos"><?php _e('Soporte', 'react-woo-products'); ?></a></li>
                        </ul>
                    </div>
                    
                    <div class="rwp-welcome-panel-column rwp-welcome-panel-last">
                        <h3><?php _e('Estado del Sistema', 'react-woo-products'); ?></h3>
                        <ul>
                            <li>
                                <span class="rwp-status-item">
                                    <?php if (class_exists('WooCommerce')): ?>
                                        <span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span>
                                        <?php _e('WooCommerce Activo', 'react-woo-products'); ?>
                                    <?php else: ?>
                                        <span class="dashicons dashicons-warning" style="color: #dc3232;"></span>
                                        <?php _e('WooCommerce Requerido', 'react-woo-products'); ?>
                                    <?php endif; ?>
                                </span>
                            </li>
                            <li>
                                <span class="rwp-status-item">
                                    <span class="dashicons dashicons-admin-appearance" style="color: #0073aa;"></span>
                                    <?php echo count($templates); ?> <?php _e('Plantillas Disponibles', 'react-woo-products'); ?>
                                </span>
                            </li>
                            <li>
                                <span class="rwp-status-item">
                                    <span class="dashicons dashicons-products" style="color: #0073aa;"></span>
                                    <?php echo $react_products_count; ?> <?php _e('Productos con React', 'react-woo-products'); ?>
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="rwp-dashboard-stats">
        <h2><?php _e('Estadísticas Rápidas', 'react-woo-products'); ?></h2>
        
        <div class="rwp-stats-grid">
            <div class="rwp-stat-box">
                <div class="rwp-stat-icon">
                    <span class="dashicons dashicons-visibility"></span>
                </div>
                <div class="rwp-stat-content">
                    <h3><?php echo number_format($total_views); ?></h3>
                    <p><?php _e('Vistas Totales', 'react-woo-products'); ?></p>
                </div>
            </div>
            
            <div class="rwp-stat-box">
                <div class="rwp-stat-icon">
                    <span class="dashicons dashicons-cart"></span>
                </div>
                <div class="rwp-stat-content">
                    <h3><?php echo number_format($total_conversions); ?></h3>
                    <p><?php _e('Conversiones', 'react-woo-products'); ?></p>
                </div>
            </div>
            
            <div class="rwp-stat-box">
                <div class="rwp-stat-icon">
                    <span class="dashicons dashicons-chart-line"></span>
                </div>
                <div class="rwp-stat-content">
                    <h3><?php echo number_format($conversion_rate, 1); ?>%</h3>
                    <p><?php _e('Tasa Conversión', 'react-woo-products'); ?></p>
                </div>
            </div>
            
            <div class="rwp-stat-box">
                <div class="rwp-stat-icon">
                    <span class="dashicons dashicons-admin-appearance"></span>
                </div>
                <div class="rwp-stat-content">
                    <h3><?php echo count($templates); ?></h3>
                    <p><?php _e('Plantillas', 'react-woo-products'); ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="rwp-dashboard-content">
        <div class="rwp-dashboard-row">
            <div class="rwp-dashboard-col-8">
                <div class="rwp-recent-activity">
                    <h2><?php _e('Plantillas Más Populares', 'react-woo-products'); ?></h2>
                    
                    <div class="rwp-activity-list">
                        <?php if (!empty($stats)): ?>
                            <?php 
                            // Ordenar por vistas
                            usort($stats, function($a, $b) {
                                return $b['views'] - $a['views'];
                            });
                            
                            $top_templates = array_slice($stats, 0, 5);
                            ?>
                            
                            <?php foreach ($top_templates as $template_stat): ?>
                                <?php 
                                $template_info = null;
                                foreach ($templates as $template) {
                                    if ($template['id'] === $template_stat['template_id']) {
                                        $template_info = $template;
                                        break;
                                    }
                                }
                                
                                if (!$template_info) continue;
                                ?>
                                
                                <div class="rwp-activity-item">
                                    <div class="rwp-activity-icon">
                                        <span class="dashicons dashicons-admin-appearance"></span>
                                    </div>
                                    <div class="rwp-activity-content">
                                        <h4><?php echo esc_html($template_info['name']); ?></h4>
                                        <p><?php echo esc_html($template_info['description']); ?></p>
                                        <div class="rwp-activity-meta">
                                            <span><?php echo number_format($template_stat['views']); ?> vistas</span>
                                            <span><?php echo number_format($template_stat['conversions']); ?> conversiones</span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="rwp-no-activity">
                                <span class="dashicons dashicons-chart-bar"></span>
                                <p><?php _e('No hay actividad reciente. ¡Habilita React en algunos productos para ver estadísticas!', 'react-woo-products'); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="rwp-dashboard-col-4">
                <div class="rwp-quick-actions">
                    <h2><?php _e('Acciones Rápidas', 'react-woo-products'); ?></h2>
                    
                    <div class="rwp-action-buttons">
                        <a href="<?php echo admin_url('admin.php?page=react-woo-products-templates'); ?>" class="button button-primary button-large">
                            <span class="dashicons dashicons-upload"></span>
                            <?php _e('Importar Plantilla', 'react-woo-products'); ?>
                        </a>
                        
                        <a href="<?php echo admin_url('admin.php?page=react-woo-products-settings'); ?>" class="button button-secondary button-large">
                            <span class="dashicons dashicons-admin-settings"></span>
                            <?php _e('Configuración', 'react-woo-products'); ?>
                        </a>
                        
                        <a href="<?php echo admin_url('admin.php?page=react-woo-products-analytics'); ?>" class="button button-secondary button-large">
                            <span class="dashicons dashicons-chart-bar"></span>
                            <?php _e('Ver Analytics', 'react-woo-products'); ?>
                        </a>
                        
                        <a href="<?php echo admin_url('edit.php?post_type=product'); ?>" class="button button-secondary button-large">
                            <span class="dashicons dashicons-products"></span>
                            <?php _e('Gestionar Productos', 'react-woo-products'); ?>
                        </a>
                    </div>
                </div>
                
                <div class="rwp-tips">
                    <h2><?php _e('Consejos', 'react-woo-products'); ?></h2>
                    
                    <div class="rwp-tip-item">
                        <span class="dashicons dashicons-lightbulb"></span>
                        <div>
                            <h4><?php _e('Prueba A/B Testing', 'react-woo-products'); ?></h4>
                            <p><?php _e('Usa diferentes plantillas para productos similares y compara las tasas de conversión.', 'react-woo-products'); ?></p>
                        </div>
                    </div>
                    
                    <div class="rwp-tip-item">
                        <span class="dashicons dashicons-performance"></span>
                        <div>
                            <h4><?php _e('Optimiza el Rendimiento', 'react-woo-products'); ?></h4>
                            <p><?php _e('Habilita el caché en la configuración para mejorar los tiempos de carga.', 'react-woo-products'); ?></p>
                        </div>
                    </div>
                    
                    <div class="rwp-tip-item">
                        <span class="dashicons dashicons-smartphone"></span>
                        <div>
                            <h4><?php _e('Diseño Responsivo', 'react-woo-products'); ?></h4>
                            <p><?php _e('Todas las plantillas están optimizadas para dispositivos móviles automáticamente.', 'react-woo-products'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.rwp-welcome-panel {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 30px;
    margin-bottom: 30px;
}

.rwp-welcome-panel h2 {
    margin-top: 0;
    font-size: 24px;
    color: #23282d;
}

.rwp-welcome-panel-column-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 30px;
    margin-top: 30px;
}

.rwp-welcome-panel-column h3 {
    margin-top: 0;
    color: #0073aa;
}

.rwp-welcome-panel-column ul {
    margin: 0;
    padding: 0;
    list-style: none;
}

.rwp-welcome-panel-column li {
    margin-bottom: 10px;
}

.welcome-icon {
    text-decoration: none;
    display: flex;
    align-items: center;
}

.welcome-icon:before {
    margin-right: 10px;
    font-size: 16px;
    color: #0073aa;
}

.rwp-status-item {
    display: flex;
    align-items: center;
    gap: 8px;
}

.rwp-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin: 20px 0 30px 0;
}

.rwp-stat-box {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 20px;
    display: flex;
    align-items: center;
}

.rwp-stat-icon {
    margin-right: 15px;
    font-size: 24px;
    color: #0073aa;
}

.rwp-stat-content h3 {
    margin: 0;
    font-size: 24px;
    font-weight: 600;
    color: #23282d;
}

.rwp-stat-content p {
    margin: 5px 0 0 0;
    color: #666;
    font-size: 14px;
}

.rwp-dashboard-row {
    display: flex;
    gap: 30px;
}

.rwp-dashboard-col-8 {
    flex: 0 0 65%;
}

.rwp-dashboard-col-4 {
    flex: 0 0 30%;
}

.rwp-recent-activity,
.rwp-quick-actions,
.rwp-tips {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 20px;
    margin-bottom: 20px;
}

.rwp-activity-item {
    display: flex;
    align-items: flex-start;
    padding: 15px 0;
    border-bottom: 1px solid #eee;
}

.rwp-activity-item:last-child {
    border-bottom: none;
}

.rwp-activity-icon {
    margin-right: 15px;
    margin-top: 5px;
    color: #0073aa;
}

.rwp-activity-content h4 {
    margin: 0 0 5px 0;
    font-size: 14px;
}

.rwp-activity-content p {
    margin: 0 0 10px 0;
    color: #666;
    font-size: 13px;
}

.rwp-activity-meta {
    display: flex;
    gap: 15px;
    font-size: 12px;
    color: #999;
}

.rwp-no-activity {
    text-align: center;
    padding: 40px 20px;
    color: #666;
}

.rwp-no-activity .dashicons {
    font-size: 48px;
    margin-bottom: 10px;
    opacity: 0.5;
}

.rwp-action-buttons {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.rwp-action-buttons .button {
    justify-content: flex-start;
    display: flex;
    align-items: center;
    gap: 8px;
}

.rwp-tip-item {
    display: flex;
    align-items: flex-start;
    margin-bottom: 20px;
    padding-bottom: 20px;
    border-bottom: 1px solid #eee;
}

.rwp-tip-item:last-child {
    margin-bottom: 0;
    padding-bottom: 0;
    border-bottom: none;
}

.rwp-tip-item .dashicons {
    margin-right: 15px;
    margin-top: 2px;
    color: #0073aa;
    font-size: 18px;
}

.rwp-tip-item h4 {
    margin: 0 0 8px 0;
    font-size: 14px;
    color: #23282d;
}

.rwp-tip-item p {
    margin: 0;
    color: #666;
    font-size: 13px;
    line-height: 1.4;
}
</style> 