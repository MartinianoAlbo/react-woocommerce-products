// Template 1: Modern Card
(function() {
    'use strict';

    // Definir el template
    window.RWPTemplate = {
        
        init: function() {
            this.render(
                document.getElementById('rwp-product-container'),
                window.rwpProductData,
                window.rwpTemplateConfig
            );
        },
        
        render: function(container, productData, config) {
            if (!container || !productData) {
                console.error('RWP Template 1: Contenedor o datos del producto no disponibles');
                return;
            }
            
            // Configuración por defecto
            var settings = Object.assign({
                showRating: true,
                showCategories: true,
                showSKU: false,
                primaryColor: '#2c5aa0',
                buttonStyle: 'filled',
                animationDuration: 300
            }, config.settings || {});
            
            // Crear elemento React
            var element = React.createElement(ModernCardTemplate, {
                product: productData,
                settings: settings
            });
            
            // Renderizar
            ReactDOM.render(element, container);
            
            // Agregar clase para animación
            setTimeout(function() {
                container.classList.add('rwp-template-1-loaded');
            }, 50);
            
            // Tracking de vista
            if (window.RWPFrontend && typeof window.RWPFrontend.trackEvent === 'function') {
                window.RWPFrontend.trackEvent('view', {
                    product_id: productData.id,
                    template_id: 'template-1'
                });
            }
        },
        
        initShortcode: function(containerId, productId) {
            var container = document.getElementById(containerId);
            if (!container) return;
            
            // Usar datos del shortcode
            var productData = window.rwpShortcodeData[productId];
            if (productData) {
                this.render(container, productData, window.rwpTemplateConfig || {});
            }
        }
    };
    
    // Componente React para Modern Card
    var ModernCardTemplate = function(props) {
        var product = props.product;
        var settings = props.settings;
        
        // Estado para el carrito
        var [isAddingToCart, setIsAddingToCart] = React.useState(false);
        var [cartMessage, setCartMessage] = React.useState('');
        
        // Handler para agregar al carrito
        var handleAddToCart = function() {
            setIsAddingToCart(true);
            setCartMessage('');
            
            if (window.RWPFrontend && typeof window.RWPFrontend.addToCart === 'function') {
                window.RWPFrontend.addToCart(product.id, 1)
                    .then(function(response) {
                        if (response.error) {
                            setCartMessage('Error: ' + response.error);
                        } else {
                            setCartMessage('¡Producto agregado al carrito!');
                            // Trigger evento
                            jQuery(document).trigger('added_to_cart', [response.fragments, response.cart_hash]);
                        }
                    })
                    .catch(function(error) {
                        setCartMessage('Error al agregar al carrito');
                    })
                    .finally(function() {
                        setIsAddingToCart(false);
                        setTimeout(function() {
                            setCartMessage('');
                        }, 3000);
                    });
            } else {
                setIsAddingToCart(false);
                setCartMessage('Error: Funcionalidad no disponible');
            }
        };
        
        // Renderizar rating
        var renderRating = function() {
            if (!settings.showRating || !product.rating) return null;
            
            var stars = [];
            var rating = parseFloat(product.rating) || 0;
            
            for (var i = 1; i <= 5; i++) {
                var filled = i <= rating;
                stars.push(
                    React.createElement('span', {
                        key: i,
                        className: 'star ' + (filled ? 'filled' : 'empty'),
                        style: { color: filled ? '#ffc107' : '#e4e5e9' }
                    }, '★')
                );
            }
            
            return React.createElement('div', { className: 'product-rating' },
                stars,
                React.createElement('span', { className: 'rating-text' }, 
                    '(' + rating + ')'
                )
            );
        };
        
        // Estilos dinámicos basados en configuración
        var buttonStyles = {
            filled: {
                background: settings.primaryColor,
                color: 'white',
                border: 'none'
            },
            outline: {
                background: 'transparent',
                color: settings.primaryColor,
                border: '2px solid ' + settings.primaryColor
            },
            minimal: {
                background: 'transparent',
                color: settings.primaryColor,
                border: 'none',
                textDecoration: 'underline'
            }
        };
        
        var buttonStyle = Object.assign({}, buttonStyles[settings.buttonStyle] || buttonStyles.filled);
        
        return React.createElement('div', { 
            className: 'rwp-template-1 rwp-modern-card',
            style: { 
                transition: 'all ' + settings.animationDuration + 'ms ease',
                '--primary-color': settings.primaryColor
            }
        },
            // Imagen del producto
            product.images && product.images.length > 0 &&
            React.createElement('div', { className: 'product-image-container' },
                React.createElement('img', {
                    src: product.images[0].src,
                    alt: product.images[0].alt || product.name,
                    className: 'product-image',
                    loading: 'lazy'
                }),
                product.on_sale &&
                React.createElement('div', { className: 'sale-badge' }, 'OFERTA')
            ),
            
            // Contenido del producto
            React.createElement('div', { className: 'product-content' },
                
                // Categorías
                settings.showCategories && product.categories && product.categories.length > 0 &&
                React.createElement('div', { className: 'product-categories' },
                    product.categories.map(function(category, index) {
                        return React.createElement('span', { 
                            key: index, 
                            className: 'category-tag' 
                        }, category);
                    })
                ),
                
                // Título
                React.createElement('h2', { className: 'product-title' }, product.name),
                
                // Rating
                renderRating(),
                
                // Precio
                React.createElement('div', { className: 'product-price' },
                    product.on_sale &&
                    React.createElement('span', { className: 'original-price' },
                        window.RWPFrontend ? 
                        window.RWPFrontend.formatPrice(product.regular_price) : 
                        '$' + product.regular_price
                    ),
                    React.createElement('span', { 
                        className: product.on_sale ? 'sale-price' : 'current-price' 
                    },
                        window.RWPFrontend ? 
                        window.RWPFrontend.formatPrice(product.price) : 
                        '$' + product.price
                    )
                ),
                
                // Descripción corta
                product.short_description &&
                React.createElement('div', { 
                    className: 'product-description',
                    dangerouslySetInnerHTML: { __html: product.short_description }
                }),
                
                // SKU
                settings.showSKU && product.sku &&
                React.createElement('div', { className: 'product-sku' },
                    React.createElement('strong', null, 'SKU: '),
                    product.sku
                ),
                
                // Botón agregar al carrito o mensaje de agotado
                React.createElement('div', { className: 'product-actions' },
                    product.purchasable && product.in_stock ?
                    React.createElement('button', {
                        className: 'add-to-cart-btn ' + settings.buttonStyle,
                        onClick: handleAddToCart,
                        disabled: isAddingToCart,
                        style: buttonStyle
                    }, isAddingToCart ? 'Agregando...' : 'Agregar al carrito') :
                    
                    React.createElement('div', { className: 'out-of-stock-message' },
                        'Producto agotado'
                    )
                ),
                
                // Mensaje del carrito
                cartMessage &&
                React.createElement('div', { 
                    className: 'cart-message ' + (cartMessage.includes('Error') ? 'error' : 'success')
                }, cartMessage)
            )
        );
    };
    
    // Auto-inicializar si los datos están disponibles
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            if (window.rwpProductData && window.RWPTemplate) {
                window.RWPTemplate.init();
            }
        });
    } else {
        if (window.rwpProductData && window.RWPTemplate) {
            window.RWPTemplate.init();
        }
    }
})(); 