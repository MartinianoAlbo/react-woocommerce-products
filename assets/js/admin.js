jQuery(document).ready(function($) {
    'use strict';

    // Admin object
    var RWPAdmin = {
        
        init: function() {
            this.bindEvents();
            this.initTemplatePreview();
            this.initTemplateManager();
        },
        
        bindEvents: function() {
            // Vista previa de plantilla
            $(document).on('click', '#rwp-preview-template', this.previewTemplate);
            
            // Importar plantilla
            $(document).on('click', '#rwp-import-template', this.importTemplate);
            
            // Importar código IA
            $(document).on('click', '#rwp-import-ai-code', this.showAIImportModal);
            $(document).on('click', '#rwp-confirm-ai-import', this.importAICode);
            $(document).on('click', '#rwp-cancel-ai-import', this.hideAIImportModal);
            $(document).on('click', '.rwp-modal-close', this.closeModals);
            
            // Exportar plantilla
            $(document).on('click', '.rwp-export-template', this.exportTemplate);
            
            // Eliminar plantilla
            $(document).on('click', '.rwp-delete-template', this.deleteTemplate);
            
            // Obtener estadísticas
            $(document).on('click', '.rwp-template-stats', this.getTemplateStats);
            
            // Cambio en el selector de plantilla del producto
            $(document).on('change', '#rwp_template_id', this.onTemplateChange);
        },
        
        previewTemplate: function(e) {
            e.preventDefault();
            
            var $button = $(this);
            var productId = $button.data('product-id');
            var templateId = $button.data('template-id');
            
            if (!productId || !templateId) {
                alert(rwpAdmin.strings.error + ': Datos faltantes');
                return;
            }
            
            $button.prop('disabled', true).text('Cargando...');
            
            $.ajax({
                url: rwpAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'rwp_preview_template',
                    nonce: rwpAdmin.nonce,
                    product_id: productId,
                    template_id: templateId
                },
                success: function(response) {
                    if (response.success) {
                        // Abrir vista previa en nueva pestaña
                        window.open(response.data.preview_url, '_blank');
                    } else {
                        alert(rwpAdmin.strings.error + ': ' + response.data);
                    }
                },
                error: function() {
                    alert(rwpAdmin.strings.error + ': Error de conexión');
                },
                complete: function() {
                    $button.prop('disabled', false).text('Vista previa');
                }
            });
        },
        
        importTemplate: function(e) {
            e.preventDefault();
            
            var fileInput = document.getElementById('template-file-input');
            if (!fileInput || !fileInput.files.length) {
                alert('Por favor selecciona un archivo ZIP');
                return;
            }
            
            var formData = new FormData();
            formData.append('action', 'rwp_import_template');
            formData.append('nonce', rwpAdmin.nonce);
            formData.append('template_file', fileInput.files[0]);
            
            var $button = $(this);
            $button.prop('disabled', true).text(rwpAdmin.strings.importing);
            
            $.ajax({
                url: rwpAdmin.ajaxUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        alert(rwpAdmin.strings.success + ': ' + response.data);
                        location.reload();
                    } else {
                        alert(rwpAdmin.strings.error + ': ' + response.data);
                    }
                },
                error: function() {
                    alert(rwpAdmin.strings.error + ': Error de conexión');
                },
                complete: function() {
                    $button.prop('disabled', false).text('Importar');
                }
            });
        },
        
        exportTemplate: function(e) {
            e.preventDefault();
            
            var templateId = $(this).data('template-id');
            if (!templateId) {
                alert('ID de plantilla faltante');
                return;
            }
            
            var $button = $(this);
            $button.prop('disabled', true).text(rwpAdmin.strings.exporting);
            
            $.ajax({
                url: rwpAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'rwp_export_template',
                    nonce: rwpAdmin.nonce,
                    template_id: templateId
                },
                success: function(response) {
                    if (response.success) {
                        // Crear enlace de descarga
                        var link = document.createElement('a');
                        link.href = response.data.download_url;
                        link.download = templateId + '.zip';
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                        
                        alert(rwpAdmin.strings.success + ': ' + response.data.message);
                    } else {
                        alert(rwpAdmin.strings.error + ': ' + response.data);
                    }
                },
                error: function() {
                    alert(rwpAdmin.strings.error + ': Error de conexión');
                },
                complete: function() {
                    $button.prop('disabled', false).text('Exportar');
                }
            });
        },
        
        deleteTemplate: function(e) {
            e.preventDefault();
            
            if (!confirm(rwpAdmin.strings.confirmDelete)) {
                return;
            }
            
            var templateId = $(this).data('template-id');
            if (!templateId) {
                alert('ID de plantilla faltante');
                return;
            }
            
            var $button = $(this);
            $button.prop('disabled', true);
            
            $.ajax({
                url: rwpAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'rwp_delete_template',
                    nonce: rwpAdmin.nonce,
                    template_id: templateId
                },
                success: function(response) {
                    if (response.success) {
                        alert(rwpAdmin.strings.success + ': ' + response.data);
                        location.reload();
                    } else {
                        alert(rwpAdmin.strings.error + ': ' + response.data);
                    }
                },
                error: function() {
                    alert(rwpAdmin.strings.error + ': Error de conexión');
                },
                complete: function() {
                    $button.prop('disabled', false);
                }
            });
        },
        
        getTemplateStats: function(e) {
            e.preventDefault();
            
            var templateId = $(this).data('template-id');
            if (!templateId) {
                alert('ID de plantilla faltante');
                return;
            }
            
            $.ajax({
                url: rwpAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'rwp_get_template_stats',
                    nonce: rwpAdmin.nonce,
                    template_id: templateId
                },
                success: function(response) {
                    if (response.success) {
                        RWPAdmin.showStatsModal(templateId, response.data);
                    } else {
                        alert(rwpAdmin.strings.error + ': ' + response.data);
                    }
                },
                error: function() {
                    alert(rwpAdmin.strings.error + ': Error de conexión');
                }
            });
        },
        
        showStatsModal: function(templateId, stats) {
            var modalHtml = '<div id="rwp-stats-modal" style="display:none;">' +
                '<div class="rwp-modal-content">' +
                '<h3>Estadísticas de ' + templateId + '</h3>' +
                '<p><strong>Total de vistas:</strong> ' + (stats.total_views || 0) + '</p>' +
                '<p><strong>Total de conversiones:</strong> ' + (stats.total_conversions || 0) + '</p>' +
                '<p><strong>Tasa de conversión:</strong> ' + 
                (stats.total_views > 0 ? Math.round((stats.total_conversions / stats.total_views) * 100) : 0) + '%</p>' +
                '<button type="button" class="button" onclick="jQuery(this).closest(\'.rwp-modal-content\').parent().remove();">Cerrar</button>' +
                '</div>' +
                '</div>';
                
            $('body').append(modalHtml);
            $('#rwp-stats-modal').show();
        },
        
        onTemplateChange: function() {
            var templateId = $(this).val();
            var productId = $('#post_ID').val();
            
            if (templateId && productId) {
                // Actualizar el botón de vista previa
                var $previewButton = $('#rwp-preview-template');
                if ($previewButton.length) {
                    $previewButton.data('template-id', templateId).show();
                }
            }
        },
        
        initTemplatePreview: function() {
            // Inicializar vista previa de plantillas
            $('.rwp-template-preview').each(function() {
                var $container = $(this);
                var templateId = $container.data('template-id');
                
                // Aquí se podría cargar una vista previa en miniatura
                // Por ahora solo mostramos información básica
            });
        },
        
        initTemplateManager: function() {
            // Drag and drop para importar plantillas
            var $dropZone = $('#rwp-template-drop-zone');
            
            if ($dropZone.length) {
                $dropZone.on('dragover', function(e) {
                    e.preventDefault();
                    $(this).addClass('dragover');
                });
                
                $dropZone.on('dragleave', function(e) {
                    e.preventDefault();
                    $(this).removeClass('dragover');
                });
                
                $dropZone.on('drop', function(e) {
                    e.preventDefault();
                    $(this).removeClass('dragover');
                    
                    var files = e.originalEvent.dataTransfer.files;
                    if (files.length > 0) {
                        var fileInput = document.getElementById('template-file-input');
                        if (fileInput) {
                            fileInput.files = files;
                            $('#rwp-import-template').trigger('click');
                        }
                    }
                });
            }
        },

        showAIImportModal: function(e) {
            e.preventDefault();
            $('#rwp-ai-import-modal').show();
            $('#rwp-ai-code').focus();
        },

        hideAIImportModal: function(e) {
            e.preventDefault();
            $('#rwp-ai-import-modal').hide();
            // Limpiar formulario
            $('#rwp-template-name').val('');
            $('#rwp-template-description').val('');
            $('#rwp-ai-code').val('');
            $('#rwp-ai-import-progress').hide();
            $('.rwp-ai-import-form').show();
        },

        closeModals: function(e) {
            e.preventDefault();
            $('.rwp-modal').hide();
        },

        importAICode: function(e) {
            e.preventDefault();
            
            var templateName = $('#rwp-template-name').val().trim();
            var templateDescription = $('#rwp-template-description').val().trim();
            var aiCode = $('#rwp-ai-code').val().trim();
            
            // Validaciones
            if (!aiCode) {
                alert('Por favor ingresa el código React generado por IA');
                $('#rwp-ai-code').focus();
                return;
            }
            
            if (!templateName) {
                templateName = 'AI Template ' + new Date().toLocaleString();
            }
            
            // Mostrar progress
            $('.rwp-ai-import-form').hide();
            $('#rwp-ai-import-progress').show();
            
            var $button = $(this);
            $button.prop('disabled', true);
            
            $.ajax({
                url: rwpAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'rwp_import_ai_code',
                    nonce: rwpAdmin.nonce,
                    ai_code: aiCode,
                    template_name: templateName,
                    template_description: templateDescription
                },
                success: function(response) {
                    if (response.success) {
                        alert(rwpAdmin.strings.success + ': ' + response.data);
                        RWPAdmin.hideAIImportModal();
                        location.reload();
                    } else {
                        alert(rwpAdmin.strings.error + ': ' + response.data);
                        $('.rwp-ai-import-form').show();
                        $('#rwp-ai-import-progress').hide();
                    }
                },
                error: function() {
                    alert(rwpAdmin.strings.error + ': Error de conexión');
                    $('.rwp-ai-import-form').show();
                    $('#rwp-ai-import-progress').hide();
                },
                complete: function() {
                    $button.prop('disabled', false);
                }
            });
        }
    };
    
    // Inicializar cuando el DOM esté listo
    RWPAdmin.init();
    
    // Hacer disponible globalmente
    window.RWPAdmin = RWPAdmin;
}); 