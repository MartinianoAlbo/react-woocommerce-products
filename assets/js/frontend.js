// Frontend JavaScript para React WooCommerce Products
(function() {
    'use strict';

    // Frontend object
    var RWPFrontend = {
        
        init: function() {
            this.bindEvents();
            this.initializeProductContainer();
        },
        
        bindEvents: function() {
            // Evento cuando se agrega al carrito
            jQuery(document).on('added_to_cart', this.onAddedToCart);
            
            // Evento para tracking de analytics
            jQuery(document).on('click', '.rwp-track-event', this.trackEvent);
        },
        
        initializeProductContainer: function() {
            var container = document.getElementById('rwp-product-container');
            if (!container) {
                return;
            }
            
            var productId = container.getAttribute('data-product-id');
            var templateId = container.getAttribute('data-template-id');
            
            if (!productId || !templateId) {
                console.error('RWP: Faltan datos del producto o plantilla');
                return;
            }
            
            // Esperar a que React esté disponible
            this.waitForReact(function() {
                RWPFrontend.renderProduct(container, productId, templateId);
            });
        },
        
        waitForReact: function(callback) {
            if (window.React && window.ReactDOM) {
                callback();
            } else {
                setTimeout(function() {
                    RWPFrontend.waitForReact(callback);
                }, 100);
            }
        },
        
        renderProduct: function(container, productId, templateId) {
            // Verificar que los datos del producto estén disponibles
            if (!window.rwpProductData) {
                console.error('RWP: Datos del producto no disponibles');
                return;
            }
            
            // Crear componente React básico si no hay plantilla personalizada
            if (!window.RWPTemplate) {
                this.createDefaultTemplate();
            }
            
            // Renderizar el componente
            try {
                if (window.RWPTemplate && typeof window.RWPTemplate.render === 'function') {
                    window.RWPTemplate.render(container, window.rwpProductData, window.rwpTemplateConfig);
                } else {
                    this.renderDefaultProduct(container, window.rwpProductData);
                }
            } catch (error) {
                console.error('RWP: Error al renderizar producto', error);
                this.renderErrorMessage(container, 'Error al cargar la plantilla del producto');
            }
        },
        
        createDefaultTemplate: function() {
            window.RWPTemplate = {
                render: function(container, productData, config) {
                    RWPFrontend.renderDefaultProduct(container, productData);
                },
                
                init: function() {
                    // Inicialización por defecto
                }
            };
        },
        
        renderDefaultProduct: function(container, productData) {
            var element = React.createElement(RWPDefaultProduct, {
                product: productData,
                config: window.rwpTemplateConfig || {}
            });
            
            ReactDOM.render(element, container);
        },
        
        renderErrorMessage: function(container, message) {
            container.innerHTML = '<div class="rwp-error">Error: ' + message + '</div>';
        },
        
        onAddedToCart: function(event, fragments, cart_hash, button) {
            // Tracking cuando se agrega al carrito
            if (window.rwpProductData) {
                RWPFrontend.trackEvent('conversion', {
                    product_id: window.rwpProductData.id,
                    template_id: window.rwpTemplateConfig ? window.rwpTemplateConfig.id : 'default'
                });
            }
        },
        
        trackEvent: function(eventType, data) {
            if (!window.rwpData) {
                return;
            }
            
            var trackingData = data || {};
            trackingData.event = eventType;
            
            jQuery.ajax({
                url: window.rwpData.restUrl + 'analytics/' + (trackingData.product_id || '0'),
                method: 'POST',
                data: trackingData,
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', window.rwpData.nonce);
                }
            });
        },
        
        // Utilidades
        formatPrice: function(price) {
            if (!price || !window.rwpData) {
                return '';
            }
            
            var symbol = window.rwpData.currencySymbol || '$';
            var position = window.rwpData.currencyPosition || 'left';
            
            switch (position) {
                case 'left':
                    return symbol + price;
                case 'right':
                    return price + symbol;
                case 'left_space':
                    return symbol + ' ' + price;
                case 'right_space':
                    return price + ' ' + symbol;
                default:
                    return symbol + price;
            }
        },
        
        addToCart: function(productId, quantity, variation) {
            var data = {
                product_id: productId,
                quantity: quantity || 1
            };
            
            if (variation) {
                data.variation_id = variation.id;
                data.variation = variation.attributes;
            }
            
            return jQuery.post(window.rwpData.ajaxUrl, jQuery.extend(data, {
                action: 'woocommerce_add_to_cart'
            }));
        }
    };
    
    // Componente React por defecto
    var RWPDefaultProduct = function(props) {
        var product = props.product;
        var config = props.config;
        
        var handleAddToCart = function() {
            RWPFrontend.addToCart(product.id).then(function(response) {
                if (response.error) {
                    alert('Error al agregar al carrito: ' + response.error);
                } else {
                    alert('Producto agregado al carrito');
                    // Trigger evento personalizado
                    jQuery(document).trigger('added_to_cart', [response.fragments, response.cart_hash]);
                }
            });
        };
        
        return React.createElement('div', { className: 'rwp-default-product' },
            React.createElement('h1', { className: 'product-title' }, product.name),
            
            product.images && product.images.length > 0 && 
            React.createElement('div', { className: 'product-images' },
                React.createElement('img', {
                    src: product.images[0].src,
                    alt: product.images[0].alt || product.name,
                    className: 'product-main-image'
                })
            ),
            
            React.createElement('div', { className: 'product-price' },
                product.on_sale && 
                React.createElement('span', { className: 'original-price' }, 
                    RWPFrontend.formatPrice(product.regular_price)
                ),
                React.createElement('span', { 
                    className: product.on_sale ? 'sale-price' : 'price' 
                }, RWPFrontend.formatPrice(product.price))
            ),
            
            product.short_description &&
            React.createElement('div', { 
                className: 'product-description',
                dangerouslySetInnerHTML: { __html: product.short_description }
            }),
            
            product.purchasable && product.in_stock &&
            React.createElement('button', {
                className: 'add-to-cart-button button alt',
                onClick: handleAddToCart
            }, 'Agregar al carrito'),
            
            !product.in_stock &&
            React.createElement('div', { className: 'out-of-stock' }, 'Agotado'),
            
            product.categories && product.categories.length > 0 &&
            React.createElement('div', { className: 'product-categories' },
                React.createElement('strong', null, 'Categorías: '),
                product.categories.join(', ')
            ),
            
            product.sku &&
            React.createElement('div', { className: 'product-sku' },
                React.createElement('strong', null, 'SKU: '),
                product.sku
            )
        );
    };
    
    // Inicializar cuando el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            RWPFrontend.init();
        });
    } else {
        RWPFrontend.init();
    }
    
    // Hacer disponible globalmente
    window.RWPFrontend = RWPFrontend;
    window.RWPDefaultProduct = RWPDefaultProduct;
})(); 