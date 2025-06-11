<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php _e('Plantillas React', 'react-woo-products'); ?></h1>
    
    <div class="rwp-templates-header">
        <p><?php _e('Gestiona las plantillas React para tus productos de WooCommerce.', 'react-woo-products'); ?></p>
        
        <div class="rwp-templates-actions">
            <button type="button" class="button button-primary" id="rwp-import-template">
                <span class="dashicons dashicons-upload"></span>
                <?php _e('Importar Plantilla', 'react-woo-products'); ?>
            </button>
            
            <button type="button" class="button button-secondary" id="rwp-import-ai-code">
                <span class="dashicons dashicons-admin-generic"></span>
                <?php _e('Importar Código IA', 'react-woo-products'); ?>
            </button>
            
            <button type="button" class="button" id="rwp-refresh-templates">
                <span class="dashicons dashicons-update"></span>
                <?php _e('Actualizar Lista', 'react-woo-products'); ?>
            </button>
        </div>
    </div>

    <div class="rwp-templates-grid">
        <?php if (!empty($templates)): ?>
            <?php foreach ($templates as $template): ?>
                <div class="rwp-template-card" data-template-id="<?php echo esc_attr($template['id']); ?>">
                    <div class="rwp-template-preview">
                        <?php if (!empty($template['preview'])): ?>
                            <img src="<?php echo esc_url($template['preview']); ?>" alt="<?php echo esc_attr($template['name']); ?>">
                        <?php else: ?>
                            <div class="rwp-template-placeholder">
                                <span class="dashicons dashicons-format-image"></span>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="rwp-template-info">
                        <h3><?php echo esc_html($template['name']); ?></h3>
                        <p class="rwp-template-description"><?php echo esc_html($template['description']); ?></p>
                        
                        <div class="rwp-template-meta">
                            <span class="rwp-template-version">v<?php echo esc_html($template['version']); ?></span>
                            <span class="rwp-template-author"><?php echo esc_html($template['author']); ?></span>
                        </div>
                        
                        <div class="rwp-template-stats">
                            <span class="rwp-stat">
                                <span class="dashicons dashicons-visibility"></span>
                                <?php echo number_format($template['stats']['views'] ?? 0); ?> vistas
                            </span>
                            <span class="rwp-stat">
                                <span class="dashicons dashicons-cart"></span>
                                <?php echo number_format($template['stats']['conversions'] ?? 0); ?> conversiones
                            </span>
                        </div>
                    </div>
                    
                    <div class="rwp-template-actions">
                        <button type="button" class="button button-primary rwp-template-preview-btn" data-template-id="<?php echo esc_attr($template['id']); ?>">
                            <span class="dashicons dashicons-visibility"></span>
                            <?php _e('Vista Previa', 'react-woo-products'); ?>
                        </button>
                        
                        <button type="button" class="button rwp-template-export-btn" data-template-id="<?php echo esc_attr($template['id']); ?>">
                            <span class="dashicons dashicons-download"></span>
                            <?php _e('Exportar', 'react-woo-products'); ?>
                        </button>
                        
                        <?php if ($template['is_custom']): ?>
                            <button type="button" class="button button-link-delete rwp-template-delete-btn" data-template-id="<?php echo esc_attr($template['id']); ?>">
                                <span class="dashicons dashicons-trash"></span>
                                <?php _e('Eliminar', 'react-woo-products'); ?>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="rwp-no-templates">
                <span class="dashicons dashicons-format-image"></span>
                <h3><?php _e('No hay plantillas disponibles', 'react-woo-products'); ?></h3>
                <p><?php _e('Importa tu primera plantilla para comenzar.', 'react-woo-products'); ?></p>
                <button type="button" class="button button-primary" id="rwp-import-first-template">
                    <?php _e('Importar Plantilla', 'react-woo-products'); ?>
                </button>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal para importar plantillas -->
<div id="rwp-import-modal" class="rwp-modal" style="display: none;">
    <div class="rwp-modal-content">
        <div class="rwp-modal-header">
            <h2><?php _e('Importar Plantilla React', 'react-woo-products'); ?></h2>
            <button type="button" class="rwp-modal-close">&times;</button>
        </div>
        
        <div class="rwp-modal-body">
            <div class="rwp-import-zone" id="rwp-import-zone">
                <span class="dashicons dashicons-upload"></span>
                <p><?php _e('Arrastra un archivo ZIP aquí o haz clic para seleccionar', 'react-woo-products'); ?></p>
                <input type="file" id="rwp-template-file" accept=".zip" style="display: none;">
                <button type="button" class="button" id="rwp-select-file"><?php _e('Seleccionar Archivo', 'react-woo-products'); ?></button>
            </div>
            
            <div class="rwp-import-progress" id="rwp-import-progress" style="display: none;">
                <div class="rwp-progress-bar">
                    <div class="rwp-progress-fill"></div>
                </div>
                <p class="rwp-progress-text"><?php _e('Importando plantilla...', 'react-woo-products'); ?></p>
            </div>
        </div>
        
        <div class="rwp-modal-footer">
            <button type="button" class="button" id="rwp-cancel-import"><?php _e('Cancelar', 'react-woo-products'); ?></button>
            <button type="button" class="button button-primary" id="rwp-confirm-import" disabled><?php _e('Importar', 'react-woo-products'); ?></button>
        </div>
    </div>
</div>

<!-- Modal para vista previa -->
<div id="rwp-preview-modal" class="rwp-modal" style="display: none;">
    <div class="rwp-modal-content rwp-preview-modal-content">
        <div class="rwp-modal-header">
            <h2><?php _e('Vista Previa de Plantilla', 'react-woo-products'); ?></h2>
            <button type="button" class="rwp-modal-close">&times;</button>
        </div>
        
        <div class="rwp-modal-body">
            <div class="rwp-preview-container">
                <div class="rwp-preview-toolbar">
                    <button type="button" class="button rwp-preview-device active" data-device="desktop">
                        <span class="dashicons dashicons-desktop"></span> Desktop
                    </button>
                    <button type="button" class="button rwp-preview-device" data-device="tablet">
                        <span class="dashicons dashicons-tablet"></span> Tablet
                    </button>
                    <button type="button" class="button rwp-preview-device" data-device="mobile">
                        <span class="dashicons dashicons-smartphone"></span> Mobile
                    </button>
                </div>
                
                <div class="rwp-preview-frame" id="rwp-preview-frame">
                    <div class="rwp-preview-loading">
                        <span class="spinner is-active"></span>
                        <p><?php _e('Cargando vista previa...', 'react-woo-products'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para importar código IA -->
<div id="rwp-ai-import-modal" class="rwp-modal" style="display: none;">
    <div class="rwp-modal-content">
        <div class="rwp-modal-header">
            <h2><?php _e('Importar Código React de IA', 'react-woo-products'); ?></h2>
            <button type="button" class="rwp-modal-close">&times;</button>
        </div>
        
        <div class="rwp-modal-body">
            <div class="rwp-ai-import-form">
                <div class="rwp-form-group">
                    <label for="rwp-template-name"><?php _e('Nombre de la Plantilla:', 'react-woo-products'); ?></label>
                    <input type="text" id="rwp-template-name" class="regular-text" placeholder="<?php _e('Ej: Mi Plantilla IA', 'react-woo-products'); ?>">
                </div>
                
                <div class="rwp-form-group">
                    <label for="rwp-template-description"><?php _e('Descripción (opcional):', 'react-woo-products'); ?></label>
                    <textarea id="rwp-template-description" class="regular-text" rows="2" placeholder="<?php _e('Describe qué hace esta plantilla...', 'react-woo-products'); ?>"></textarea>
                </div>
                
                <div class="rwp-form-group">
                    <label for="rwp-ai-code"><?php _e('Código React generado por IA:', 'react-woo-products'); ?></label>
                    <textarea id="rwp-ai-code" rows="20" placeholder="<?php _e('Pega aquí el código React completo generado por v0.dev, ChatGPT, o cualquier otra IA...', 'react-woo-products'); ?>"></textarea>
                </div>
                
                <div class="rwp-ai-import-tips">
                    <h4><?php _e('💡 Consejos para mejores resultados:', 'react-woo-products'); ?></h4>
                    <ul>
                        <li><?php _e('Incluye tanto el código JSX como los estilos CSS', 'react-woo-products'); ?></li>
                        <li><?php _e('El código puede incluir bloques ```css o <style> tags', 'react-woo-products'); ?></li>
                        <li><?php _e('Se adaptará automáticamente para usar los datos del producto de WooCommerce', 'react-woo-products'); ?></li>
                        <li><?php _e('Puedes pegar código completo de herramientas como v0.dev', 'react-woo-products'); ?></li>
                    </ul>
                </div>
            </div>
            
            <div class="rwp-ai-import-progress" id="rwp-ai-import-progress" style="display: none;">
                <div class="rwp-progress-bar">
                    <div class="rwp-progress-fill"></div>
                </div>
                <p class="rwp-progress-text"><?php _e('Procesando código IA...', 'react-woo-products'); ?></p>
            </div>
        </div>
        
        <div class="rwp-modal-footer">
            <button type="button" class="button" id="rwp-cancel-ai-import"><?php _e('Cancelar', 'react-woo-products'); ?></button>
            <button type="button" class="button button-primary" id="rwp-confirm-ai-import"><?php _e('Crear Plantilla', 'react-woo-products'); ?></button>
        </div>
    </div>
</div> 