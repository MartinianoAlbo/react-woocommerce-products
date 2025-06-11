<?php
if (!defined('ABSPATH')) {
    exit;
}

// Obtener estadísticas
$template_manager = RWP_Template_Manager::getInstance();
$stats = $template_manager->get_template_stats();
$templates = $template_manager->get_available_templates();

// Calcular totales
$total_views = array_sum(array_column($stats, 'views'));
$total_conversions = array_sum(array_column($stats, 'conversions'));
$conversion_rate = $total_views > 0 ? ($total_conversions / $total_views) * 100 : 0;
?>

<div class="wrap">
    <h1><?php _e('Analytics de Plantillas React', 'react-woo-products'); ?></h1>
    
    <div class="rwp-analytics-summary">
        <div class="rwp-summary-card">
            <div class="rwp-summary-icon">
                <span class="dashicons dashicons-visibility"></span>
            </div>
            <div class="rwp-summary-content">
                <h3><?php echo number_format($total_views); ?></h3>
                <p><?php _e('Total de Vistas', 'react-woo-products'); ?></p>
            </div>
        </div>
        
        <div class="rwp-summary-card">
            <div class="rwp-summary-icon">
                <span class="dashicons dashicons-cart"></span>
            </div>
            <div class="rwp-summary-content">
                <h3><?php echo number_format($total_conversions); ?></h3>
                <p><?php _e('Total de Conversiones', 'react-woo-products'); ?></p>
            </div>
        </div>
        
        <div class="rwp-summary-card">
            <div class="rwp-summary-icon">
                <span class="dashicons dashicons-chart-line"></span>
            </div>
            <div class="rwp-summary-content">
                <h3><?php echo number_format($conversion_rate, 2); ?>%</h3>
                <p><?php _e('Tasa de Conversión', 'react-woo-products'); ?></p>
            </div>
        </div>
    </div>
    
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php _e('Plantilla', 'react-woo-products'); ?></th>
                <th><?php _e('Vistas', 'react-woo-products'); ?></th>
                <th><?php _e('Conversiones', 'react-woo-products'); ?></th>
                <th><?php _e('Tasa de Conversión', 'react-woo-products'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($stats)): ?>
                <?php foreach ($stats as $template_stat): ?>
                    <?php 
                    $template_info = null;
                    foreach ($templates as $template) {
                        if ($template['id'] === $template_stat['template_id']) {
                            $template_info = $template;
                            break;
                        }
                    }
                    
                    if (!$template_info) continue;
                    
                    $template_conversion_rate = $template_stat['views'] > 0 ? 
                        ($template_stat['conversions'] / $template_stat['views']) * 100 : 0;
                    ?>
                    
                    <tr>
                        <td><strong><?php echo esc_html($template_info['name']); ?></strong></td>
                        <td><?php echo number_format($template_stat['views']); ?></td>
                        <td><?php echo number_format($template_stat['conversions']); ?></td>
                        <td><?php echo number_format($template_conversion_rate, 2); ?>%</td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" style="text-align: center; padding: 40px;">
                        <?php _e('No hay estadísticas disponibles aún.', 'react-woo-products'); ?>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<style>
.rwp-analytics-summary {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.rwp-summary-card {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 20px;
    display: flex;
    align-items: center;
}

.rwp-summary-icon {
    margin-right: 15px;
    font-size: 24px;
    color: #0073aa;
}

.rwp-summary-content h3 {
    margin: 0;
    font-size: 24px;
    font-weight: 600;
}

.rwp-summary-content p {
    margin: 5px 0 0 0;
    color: #666;
    font-size: 14px;
}
</style> 