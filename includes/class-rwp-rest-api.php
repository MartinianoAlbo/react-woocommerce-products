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
        
        $args = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => min($per_page, 50), // Máximo 50 productos por página
            'paged' => $page,
            'orderby' => $orderby,
            'order' => $order,
        );
        
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
            'categories' => wp_get_post_terms($product->get_id(), 'product_cat', array('fields' => 'names')),
            'tags' => wp_get_post_terms($product->get_id(), 'product_tag', array('fields' => 'names')),
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
} 