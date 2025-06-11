# React WooCommerce Products

Plugin para WordPress que permite renderizar productos de WooCommerce con plantillas React personalizables, ofreciendo una experiencia moderna e interactiva para los usuarios.

## 🚀 Características

- ✅ **Plantillas React personalizables** - Crea diseños únicos para tus productos
- ✅ **Sistema de gestión de plantillas** - Importa, exporta y administra plantillas fácilmente
- ✅ **API REST completa** - Acceso a datos de productos y análiticas
- ✅ **Analytics integrado** - Seguimiento de vistas y conversiones por plantilla
- ✅ **Shortcode support** - Usa `[react_woo_product]` en cualquier lugar
- ✅ **Modo vista previa** - Previsualiza plantillas antes de aplicarlas
- ✅ **Responsive design** - Funciona perfectamente en todos los dispositivos
- ✅ **Compatible con temas** - Se integra con cualquier tema de WordPress

## 📋 Requisitos

- WordPress 5.0 o superior
- WooCommerce 3.0 o superior
- PHP 7.4 o superior
- Extensión ZipArchive de PHP (para importar/exportar plantillas)

## 🔧 Instalación

1. Descarga el plugin o clónalo en tu directorio de plugins de WordPress
2. Activa el plugin desde el panel de administración de WordPress
3. Ve a **React Products** en el menú del admin para configurar el plugin

## 📖 Uso Básico

### Habilitar React para un producto

1. Ve a **Productos > Editar producto** en WooCommerce
2. En el metabox "React Template Settings" (barra lateral), marca "Habilitar React para este producto"
3. Selecciona una plantilla del dropdown
4. Guarda el producto

### Configuración global

1. Ve a **React Products > Configuración**
2. Habilita React globalmente para aplicarlo a todos los productos
3. Selecciona la plantilla por defecto
4. Configura opciones de caché

### Usar shortcode

```
[react_woo_product id="123"]
[react_woo_product id="123" template="template-1"]
[react_woo_product id="123" template="template-2" class="mi-clase-personalizada"]
```

## 🎨 Crear Plantillas Personalizadas

### Estructura de una plantilla

Cada plantilla debe estar en su propio directorio dentro de `/wp-content/themes/tu-tema/react-woo-templates/` y contener:

```
mi-plantilla/
├── config.json      # Configuración de la plantilla
├── index.js         # Código React de la plantilla
├── style.css        # Estilos CSS (opcional)
└── thumbnail.jpg    # Imagen de vista previa (opcional)
```

### config.json

```json
{
    "name": "Mi Plantilla",
    "description": "Descripción de mi plantilla personalizada",
    "version": "1.0.0",
    "author": "Tu Nombre",
    "thumbnail": "thumbnail.jpg",
    "category": "modern",
    "tags": ["modern", "clean", "responsive"],
    "settings": {
        "primaryColor": {
            "type": "color",
            "default": "#2c5aa0",
            "label": "Color principal",
            "description": "Color principal para elementos destacados"
        },
        "showRating": {
            "type": "boolean",
            "default": true,
            "label": "Mostrar rating",
            "description": "Mostrar estrellas de valoración"
        }
    }
}
```

### index.js

```javascript
// Definir el template React
window.RWPTemplate = {
    init: function() {
        this.render(
            document.getElementById('rwp-product-container'),
            window.rwpProductData,
            window.rwpTemplateConfig
        );
    },
    
    render: function(container, productData, config) {
        var settings = Object.assign({
            primaryColor: '#2c5aa0',
            showRating: true
        }, config.settings || {});
        
        var element = React.createElement(MiComponente, {
            product: productData,
            settings: settings
        });
        
        ReactDOM.render(element, container);
    }
};

// Componente React
var MiComponente = function(props) {
    var product = props.product;
    var settings = props.settings;
    
    return React.createElement('div', { 
        className: 'mi-plantilla',
        style: { color: settings.primaryColor }
    },
        React.createElement('h2', null, product.name),
        React.createElement('p', null, product.price),
        React.createElement('button', {
            onClick: function() {
                // Agregar al carrito
                window.RWPFrontend.addToCart(product.id);
            }
        }, 'Agregar al carrito')
    );
};
```

## 🛠️ API REST

El plugin expone varios endpoints REST:

### Productos

```
GET /wp-json/react-woo-products/v1/products
GET /wp-json/react-woo-products/v1/products/123
```

Parámetros disponibles:
- `page` - Página de resultados (default: 1)
- `per_page` - Productos por página (default: 12, max: 50)
- `category` - Filtrar por slug de categoría
- `search` - Buscar productos
- `orderby` - Ordenar por: date, title, price, popularity
- `order` - ASC o DESC

### Categorías

```
GET /wp-json/react-woo-products/v1/categories
```

### Plantillas

```
GET /wp-json/react-woo-products/v1/templates
```

### Analytics

```
POST /wp-json/react-woo-products/v1/analytics/123
```

Parámetros:
- `event` - Tipo de evento: 'view' o 'conversion'
- `template_id` - ID de la plantilla utilizada

## 🎯 Hooks y Filtros

### Filtros disponibles

```php
// Filtrar plantillas disponibles
add_filter('rwp_available_templates', function($templates) {
    // Modificar array de plantillas
    return $templates;
});

// Override de plantilla para producto específico
add_filter('rwp_product_template_override', function($template, $product_id) {
    // Devolver plantilla personalizada
    return $template;
}, 10, 2);

// Override estado React habilitado
add_filter('rwp_is_react_enabled_override', function($enabled, $product_id) {
    // Forzar React habilitado/deshabilitado
    return $enabled;
}, 10, 2);
```

### Acciones disponibles

```php
// Antes de renderizar producto React
add_action('rwp_before_render_product', function($product_id, $template_id) {
    // Tu código aquí
}, 10, 2);

// Después de renderizar producto React
add_action('rwp_after_render_product', function($product_id, $template_id) {
    // Tu código aquí
}, 10, 2);
```

## 🚀 Funciones Helper

```php
// Verificar si React está habilitado para un producto
if (rwp_is_react_enabled_for_product(123)) {
    // React está habilitado
}

// Obtener plantilla asignada a un producto
$template = rwp_get_product_template(123);
if ($template) {
    echo $template['name'];
}
```

## 📊 Analytics y Estadísticas

El plugin incluye un sistema de analytics para trackear:

- **Vistas** - Cuántas veces se ha visto cada plantilla
- **Conversiones** - Cuántas veces se ha agregado al carrito desde cada plantilla
- **Tasa de conversión** - Porcentaje de conversiones por vistas

Accede a las estadísticas en **React Products > Analytics**.

## 🔧 Configuración Avanzada

### Caché

El plugin incluye un sistema de caché configurable:

```php
// En wp-config.php o functions.php
define('RWP_CACHE_ENABLED', true);
define('RWP_CACHE_DURATION', 3600); // 1 hora
```

### Directorios personalizados

```php
// Cambiar directorio de plantillas personalizadas
add_filter('rwp_custom_templates_path', function($path) {
    return get_template_directory() . '/mi-directorio-plantillas/';
});
```

## 🛠️ Troubleshooting

### Problemas comunes

**Las plantillas no se cargan:**
- Verifica que React y ReactDOM estén cargándose correctamente
- Comprueba la consola del navegador para errores JavaScript
- Asegúrate de que los archivos de plantilla tienen la estructura correcta

**Errores 404 en API REST:**
- Ve a **Configuración > Enlaces permanentes** y guarda los cambios
- Verifica que WooCommerce esté activo

**Plantillas no aparecen en el admin:**
- Verifica que el directorio de plantillas existe y tiene permisos correctos
- Comprueba que `config.json` tiene formato JSON válido

### Debug

Habilita el modo debug de WordPress para ver errores detallados:

```php
// En wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

## 🤝 Contribuir

1. Fork el repositorio
2. Crea una rama para tu feature (`git checkout -b feature/mi-nueva-caracteristica`)
3. Commit tus cambios (`git commit -am 'Agrega nueva característica'`)
4. Push a la rama (`git push origin feature/mi-nueva-caracteristica`)
5. Crea un Pull Request

## 📄 Licencia

GPL v2 o posterior. Ver el archivo `LICENSE` para más detalles.

## 🆘 Soporte

Para soporte, reportar bugs o solicitar nuevas características:

1. Revisa la documentación y FAQ
2. Busca en los issues existentes
3. Crea un nuevo issue con toda la información relevante

## 📝 Changelog

### 1.0.0
- Lanzamiento inicial
- Sistema de plantillas React
- API REST completa
- Panel de administración
- Analytics básico
- Soporte para shortcodes

---

**¿Te gusta el plugin?** ⭐ ¡Dale una estrella al repositorio! 