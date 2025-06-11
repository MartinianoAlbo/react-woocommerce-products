<?php
/**
 * Clase para manejar las REST API endpoints del plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class RWP_REST_API {
    
    private $namespace = 'react-woo-products/v1';
    
    public function __construct() {
        add_action('rest_api_init', array($this, 'register_routes'));
        add_action('rest_api_init', array($this, 'add_cors_support'));
    }
    
    public function add_cors_support() {
        $enable_cors = defined('REST_REQUEST') && REST_REQUEST;
        if ($enable_cors) {
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE');
            header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
            header('Access-Control-Allow-Credentials: true');
        }
        
        // Manejar preflight requests
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            header('HTTP/1.1 200 OK');
            exit();
        }
    }
    
    /**
     * Registrar las rutas de la API REST
     */
    public function register_routes() {
        // Obtener productos
        register_rest_route($this->namespace, '/products', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_products'),
            'permission_callback' => '__return_true',
            'args' => array(
                'page' => array(
                    'default' => 1,
                    'sanitize_callback' => 'absint',
                ),
                'per_page' => array(
                    'default' => 12,
                    'sanitize_callback' => 'absint',
                ),
                'category' => array(
                    'default' => '',
                    'sanitize_callback' => 'sanitize_text_field',
                ),
                'search' => array(
                    'default' => '',
                    'sanitize_callback' => 'sanitize_text_field',
                ),
                'slug' => array(
                    'default' => '',
                    'sanitize_callback' => 'sanitize_text_field',
                ),
                'orderby' => array(
                    'default' => 'date',
                    'sanitize_callback' => 'sanitize_text_field',
                ),
                'order' => array(
                    'default' => 'DESC',
                    'sanitize_callback' => 'sanitize_text_field',
                ),
            ),
        ));
        
        // Obtener un producto específico
        register_rest_route($this->namespace, '/products/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_product'),
            'permission_callback' => '__return_true',
            'args' => array(
                'id' => array(
                    'validate_callback' => function($param, $request, $key) {
                        return is_numeric($param);
                    }
                ),
            ),
        ));
        
        // Obtener categorías
        register_rest_route($this->namespace, '/categories', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_categories'),
            'permission_callback' => '__return_true',
        ));
        
        // Obtener plantillas disponibles
        register_rest_route($this->namespace, '/templates', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_templates'),
            'permission_callback' => '__return_true',
        ));
        
        // Endpoint para analytics (opcional)
        register_rest_route($this->namespace, '/analytics/(?P<product_id>\d+)', array(
            'methods' => 'POST',
            'callback' => array($this, 'track_analytics'),
            'permission_callback' => '__return_true',
            'args' => array(
                'product_id' => array(
                    'validate_callback' => function($param, $request, $key) {
                        return is_numeric($param);
                    }
                ),
                'event' => array(
                    'required' => true,
                    'sanitize_callback' => 'sanitize_text_field',
                ),
                'template_id' => array(
                    'sanitize_callback' => 'sanitize_text_field',
                ),
            ),
        ));

        // Endpoint para añadir al carrito
        register_rest_route('react-woo-products/v1', '/cart/add', array(
            'methods' => array('POST', 'OPTIONS'),
            'callback' => array($this, 'add_to_cart'),
            'permission_callback' => '__return_true',
            'args' => array(
                'product_id' => array(
                    'required' => true,
                    'sanitize_callback' => 'absint',
                    'validate_callback' => function($param) {
                        return is_numeric($param) && $param > 0;
                    }
                ),
                'quantity' => array(
                    'default' => 1,
                    'sanitize_callback' => 'absint',
                ),
                'variation_id' => array(
                    'default' => 0,
                    'sanitize_callback' => 'absint',
                ),
                'variation' => array(
                    'default' => array(),
                    'sanitize_callback' => array($this, 'sanitize_variation_data'),
                ),
            ),
        ));

        // Endpoint para obtener el carrito
        register_rest_route('react-woo-products/v1', '/cart', array(
            'methods' => array('GET', 'OPTIONS'),
            'callback' => array($this, 'get_cart'),
            'permission_callback' => '__return_true',
        ));

        // Endpoint para actualizar cantidad en el carrito
        register_rest_route('react-woo-products/v1', '/cart/update', array(
            'methods' => array('POST', 'OPTIONS'),
            'callback' => array($this, 'update_cart_item'),
            'permission_callback' => '__return_true',
            'args' => array(
                'cart_item_key' => array(
                    'required' => true,
                    'sanitize_callback' => 'sanitize_text_field',
                ),
                'quantity' => array(
                    'required' => true,
                    'sanitize_callback' => 'absint',
                ),
            ),
        ));

        // Endpoint para eliminar del carrito
        register_rest_route('react-woo-products/v1', '/cart/remove', array(
            'methods' => array('POST', 'OPTIONS'),
            'callback' => array($this, 'remove_from_cart'),
            'permission_callback' => '__return_true',
            'args' => array(
                'cart_item_key' => array(
                    'required' => true,
                    'sanitize_callback' => 'sanitize_text_field',
                ),
            ),
        ));
    }
    
    /**
     * Obtener lista de productos
     */
    public function get_products($request) {
        $page = $request->get_param('page');
        $per_page = $request->get_param('per_page');
        $category = $request->get_param('category');
        $search = $request->get_param('search');
        $orderby = $request->get_param('orderby');
        $order = $request->get_param('order');
        $slug = $request->get_param('slug');
        
        $args = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => min($per_page, 50), // Máximo 50 productos por página
            'paged' => $page,
            'orderby' => $orderby,
            'order' => $order,
        );
        
        // Filtro por slug específico
        if (!empty($slug)) {
            $args['name'] = $slug;
            $args['posts_per_page'] = 1; // Solo necesitamos uno cuando buscamos por slug
        }
        
        // Filtro por categoría
        if (!empty($category)) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'product_cat',
                    'field' => 'slug',
                    'terms' => $category,
                ),
            );
        }
        
        // Filtro por búsqueda
        if (!empty($search)) {
            $args['s'] = $search;
        }
        
        $query = new WP_Query($args);
        $products = array();
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $product_id = get_the_ID();
                $product = wc_get_product($product_id);
                
                if ($product) {
                    $products[] = $this->format_product_data($product);
                }
            }
            wp_reset_postdata();
        }
        
        return new WP_REST_Response(array(
            'products' => $products,
            'total' => $query->found_posts,
            'pages' => $query->max_num_pages,
            'current_page' => $page,
        ), 200);
    }
    
    /**
     * Obtener un producto específico
     */
    public function get_product($request) {
        $product_id = $request->get_param('id');
        $product = wc_get_product($product_id);
        
        if (!$product) {
            return new WP_Error('product_not_found', 'Producto no encontrado', array('status' => 404));
        }
        
        return new WP_REST_Response($this->format_product_data($product), 200);
    }
    
    /**
     * Obtener categorías de productos
     */
    public function get_categories($request) {
        $categories = get_terms(array(
            'taxonomy' => 'product_cat',
            'hide_empty' => true,
        ));
        
        $formatted_categories = array();
        foreach ($categories as $category) {
            $formatted_categories[] = array(
                'id' => $category->term_id,
                'name' => $category->name,
                'slug' => $category->slug,
                'count' => $category->count,
            );
        }
        
        return new WP_REST_Response($formatted_categories, 200);
    }
    
    /**
     * Obtener plantillas disponibles
     */
    public function get_templates($request) {
        $template_manager = RWP_Template_Manager::getInstance();
        $templates = $template_manager->get_available_templates();
        
        return new WP_REST_Response($templates, 200);
    }
    
    /**
     * Registrar evento de analytics
     */
    public function track_analytics($request) {
        global $wpdb;
        
        $product_id = $request->get_param('product_id');
        $event = $request->get_param('event');
        $template_id = $request->get_param('template_id');
        
        $table_name = $wpdb->prefix . 'rwp_template_analytics';
        
        if ($event === 'view') {
            // Incrementar vistas
            $wpdb->query($wpdb->prepare(
                "INSERT INTO $table_name (product_id, template_id, views, last_viewed) 
                 VALUES (%d, %s, 1, NOW())
                 ON DUPLICATE KEY UPDATE views = views + 1, last_viewed = NOW()",
                $product_id, $template_id
            ));
        } elseif ($event === 'conversion') {
            // Incrementar conversiones
            $wpdb->query($wpdb->prepare(
                "UPDATE $table_name SET conversions = conversions + 1 
                 WHERE product_id = %d AND template_id = %s",
                $product_id, $template_id
            ));
        }
        
        return new WP_REST_Response(array('success' => true), 200);
    }
    
    /**
     * Formatear datos del producto para la API
     */
    public function format_product_data($product) {
        $data = array(
            'id' => $product->get_id(),
            'name' => $product->get_name(),
            'slug' => $product->get_slug(),
            'type' => $product->get_type(),
            'status' => $product->get_status(),
            'featured' => $product->is_featured(),
            'description' => $product->get_description(),
            'short_description' => $product->get_short_description(),
            'price' => $product->get_price(),
            'regular_price' => $product->get_regular_price(),
            'sale_price' => $product->get_sale_price(),
            'on_sale' => $product->is_on_sale(),
            'purchasable' => $product->is_purchasable(),
            'total_sales' => $product->get_total_sales(),
            'virtual' => $product->is_virtual(),
            'downloadable' => $product->is_downloadable(),
            'permalink' => get_permalink($product->get_id()),
            'sku' => $product->get_sku(),
            'stock_status' => $product->get_stock_status(),
            'in_stock' => $product->is_in_stock(),
            'stock_quantity' => $product->get_stock_quantity(),
            'manage_stock' => $product->get_manage_stock(),
            'categories' => $this->get_product_categories($product->get_id()),
            'tags' => $this->get_product_tags($product->get_id()),
            'images' => $this->get_product_images($product),
            'attributes' => $this->get_product_attributes($product),
            'variations' => $this->get_product_variations($product),
            'meta_data' => array(
                'react_enabled' => get_post_meta($product->get_id(), '_rwp_react_enabled', true),
                'template_id' => get_post_meta($product->get_id(), '_rwp_template_id', true),
            ),
        );
        
        return $data;
    }
    
    /**
     * Obtener imágenes del producto
     */
    private function get_product_images($product) {
        $images = array();
        
        // Imagen principal
        $main_image_id = $product->get_image_id();
        if ($main_image_id) {
            $images[] = array(
                'id' => $main_image_id,
                'src' => wp_get_attachment_url($main_image_id),
                'name' => get_the_title($main_image_id),
                'alt' => get_post_meta($main_image_id, '_wp_attachment_image_alt', true),
                'position' => 0,
            );
        }
        
        // Galería de imágenes
        $gallery_image_ids = $product->get_gallery_image_ids();
        foreach ($gallery_image_ids as $position => $image_id) {
            $images[] = array(
                'id' => $image_id,
                'src' => wp_get_attachment_url($image_id),
                'name' => get_the_title($image_id),
                'alt' => get_post_meta($image_id, '_wp_attachment_image_alt', true),
                'position' => $position + 1,
            );
        }
        
        return $images;
    }
    
    /**
     * Obtener atributos del producto
     */
    private function get_product_attributes($product) {
        $attributes = array();
        $product_attributes = $product->get_attributes();
        
        foreach ($product_attributes as $attribute) {
            $attributes[] = array(
                'id' => $attribute->get_id(),
                'name' => $attribute->get_name(),
                'options' => $attribute->get_options(),
                'position' => $attribute->get_position(),
                'visible' => $attribute->get_visible(),
                'variation' => $attribute->get_variation(),
            );
        }
        
        return $attributes;
    }
    
    /**
     * Obtener variaciones del producto
     */
    private function get_product_variations($product) {
        $variations = array();
        
        if ($product->is_type('variable')) {
            $variation_ids = $product->get_children();
            
            foreach ($variation_ids as $variation_id) {
                $variation = wc_get_product($variation_id);
                if ($variation) {
                    $variations[] = array(
                        'id' => $variation->get_id(),
                        'sku' => $variation->get_sku(),
                        'price' => $variation->get_price(),
                        'regular_price' => $variation->get_regular_price(),
                        'sale_price' => $variation->get_sale_price(),
                        'stock_status' => $variation->get_stock_status(),
                        'stock_quantity' => $variation->get_stock_quantity(),
                        'attributes' => $variation->get_variation_attributes(),
                        'image' => $variation->get_image_id() ? wp_get_attachment_url($variation->get_image_id()) : '',
                    );
                }
            }
        }
        
        return $variations;
    }
    
    /**
     * Obtener categorías del producto con formato correcto
     */
    private function get_product_categories($product_id) {
        $categories = wp_get_post_terms($product_id, 'product_cat');
        $formatted_categories = array();
        
        if (!is_wp_error($categories) && !empty($categories)) {
            foreach ($categories as $category) {
                $formatted_categories[] = array(
                    'id' => $category->term_id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                );
            }
        }
        
        return $formatted_categories;
    }
    
    /**
     * Obtener tags del producto con formato correcto
     */
    private function get_product_tags($product_id) {
        $tags = wp_get_post_terms($product_id, 'product_tag');
        $formatted_tags = array();
        
        if (!is_wp_error($tags) && !empty($tags)) {
            foreach ($tags as $tag) {
                $formatted_tags[] = array(
                    'id' => $tag->term_id,
                    'name' => $tag->name,
                    'slug' => $tag->slug,
                );
            }
        }
        
        return $formatted_tags;
    }

    public function add_to_cart($request) {
        // Manejar OPTIONS request
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            return rest_ensure_response(array('status' => 'ok'));
        }

        if (!function_exists('WC')) {
            return new WP_Error('woocommerce_not_active', 'WooCommerce is not active', array('status' => 500));
        }

        // Inicializar la sesión de WooCommerce si no existe
        if (!WC()->session) {
            WC()->session = new WC_Session_Handler();
            WC()->session->init();
        }

        // Inicializar el carrito si no existe
        if (!WC()->cart) {
            wc_load_cart();
        }

        $product_id = $request->get_param('product_id');
        $quantity = $request->get_param('quantity');
        $variation_id = $request->get_param('variation_id');
        $variation = $request->get_param('variation');

        // Log para debugging
        error_log('Add to cart request: ' . json_encode(array(
            'product_id' => $product_id,
            'quantity' => $quantity,
            'variation_id' => $variation_id,
            'variation' => $variation
        )));

        // Verificar que el producto existe
        $product = wc_get_product($product_id);
        if (!$product) {
            return new WP_Error('product_not_found', 'Producto no encontrado', array('status' => 404));
        }

        // Verificar stock
        if (!$product->is_in_stock()) {
            return new WP_Error('product_out_of_stock', 'Producto agotado', array('status' => 400));
        }

        if ($product->managing_stock() && $product->get_stock_quantity() < $quantity) {
            return new WP_Error('insufficient_stock', 'Stock insuficiente', array('status' => 400));
        }

        // Añadir al carrito
        $cart_item_key = WC()->cart->add_to_cart(
            $product_id,
            $quantity,
            $variation_id,
            $variation
        );

        if (!$cart_item_key) {
            return new WP_Error('add_to_cart_failed', 'Error al añadir al carrito', array('status' => 500));
        }

        // Log para debugging
        error_log('Product added to cart successfully: ' . $cart_item_key);

        // Devolver información del carrito actualizada
        return rest_ensure_response(array(
            'success' => true,
            'cart_item_key' => $cart_item_key,
            'message' => 'Producto añadido al carrito correctamente',
            'cart' => $this->get_cart_data(),
        ));
    }

    public function get_cart($request) {
        // Manejar OPTIONS request
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            return rest_ensure_response(array('status' => 'ok'));
        }

        if (!function_exists('WC')) {
            return new WP_Error('woocommerce_not_active', 'WooCommerce is not active', array('status' => 500));
        }

        // Inicializar la sesión de WooCommerce si no existe
        if (!WC()->session) {
            WC()->session = new WC_Session_Handler();
            WC()->session->init();
        }

        // Inicializar el carrito si no existe
        if (!WC()->cart) {
            wc_load_cart();
        }

        return rest_ensure_response($this->get_cart_data());
    }

    public function update_cart_item($request) {
        // Manejar OPTIONS request
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            return rest_ensure_response(array('status' => 'ok'));
        }

        if (!function_exists('WC')) {
            return new WP_Error('woocommerce_not_active', 'WooCommerce is not active', array('status' => 500));
        }

        $cart_item_key = $request->get_param('cart_item_key');
        $quantity = $request->get_param('quantity');

        if (!WC()->cart->get_cart_item($cart_item_key)) {
            return new WP_Error('cart_item_not_found', 'Producto no encontrado en el carrito', array('status' => 404));
        }

        if ($quantity == 0) {
            WC()->cart->remove_cart_item($cart_item_key);
        } else {
            WC()->cart->set_quantity($cart_item_key, $quantity);
        }

        return rest_ensure_response(array(
            'success' => true,
            'message' => 'Carrito actualizado correctamente',
            'cart' => $this->get_cart_data(),
        ));
    }

    public function remove_from_cart($request) {
        // Manejar OPTIONS request
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            return rest_ensure_response(array('status' => 'ok'));
        }

        if (!function_exists('WC')) {
            return new WP_Error('woocommerce_not_active', 'WooCommerce is not active', array('status' => 500));
        }

        $cart_item_key = $request->get_param('cart_item_key');

        if (!WC()->cart->get_cart_item($cart_item_key)) {
            return new WP_Error('cart_item_not_found', 'Producto no encontrado en el carrito', array('status' => 404));
        }

        WC()->cart->remove_cart_item($cart_item_key);

        return rest_ensure_response(array(
            'success' => true,
            'message' => 'Producto eliminado del carrito',
            'cart' => $this->get_cart_data(),
        ));
    }

    private function get_cart_data() {
        if (!WC()->cart) {
            return array(
                'items' => array(),
                'totals' => array(
                    'subtotal' => 0,
                    'total' => 0,
                    'tax_total' => 0,
                    'shipping_total' => 0,
                ),
                'item_count' => 0,
            );
        }

        $cart_items = array();
        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
            $product = $cart_item['data'];
            $product_image = wp_get_attachment_image_src($product->get_image_id(), 'thumbnail');
            
            $cart_items[] = array(
                'key' => $cart_item_key,
                'product_id' => $cart_item['product_id'],
                'variation_id' => $cart_item['variation_id'],
                'quantity' => $cart_item['quantity'],
                'line_total' => $cart_item['line_total'],
                'line_subtotal' => $cart_item['line_subtotal'],
                'product' => array(
                    'id' => $product->get_id(),
                    'name' => $product->get_name(),
                    'slug' => $product->get_slug(),
                    'price' => $product->get_price(),
                    'image' => $product_image ? $product_image[0] : null,
                ),
            );
        }

        return array(
            'items' => $cart_items,
            'totals' => array(
                'subtotal' => (float) WC()->cart->get_subtotal(),
                'total' => (float) WC()->cart->get_total('raw'),
                'tax_total' => (float) WC()->cart->get_total_tax(),
                'shipping_total' => (float) WC()->cart->get_shipping_total(),
            ),
            'item_count' => WC()->cart->get_cart_contents_count(),
        );
    }

    public function sanitize_variation_data($value) {
        if (!is_array($value)) {
            return array();
        }
        
        $sanitized = array();
        foreach ($value as $key => $val) {
            $sanitized[sanitize_text_field($key)] = sanitize_text_field($val);
        }
        
        return $sanitized;
    }
} 