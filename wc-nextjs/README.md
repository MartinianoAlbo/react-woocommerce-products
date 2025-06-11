# WooCommerce Next.js Frontend

Este proyecto es un frontend moderno desarrollado con Next.js 15 y React 19 que se integra con la API REST de WooCommerce para mostrar productos de forma dinámica y optimizada.

## 🚀 Características

- **Next.js 15** con App Router y Server Components
- **React 19** con las últimas características
- **TypeScript** para un desarrollo más seguro
- **Tailwind CSS 4** para estilos modernos y responsivos
- **Integración completa con WooCommerce** API REST
- **Optimización de imágenes** con Next.js Image
- **SEO optimizado** con metadata dinámico
- **Páginas de producto dinámicas** con rutas generadas estáticamente
- **Galería de imágenes interactiva** con zoom y navegación
- **Componentes de carga** (skeletons) para mejor UX
- **Responsive design** optimizado para móviles
- **Funcionalidad de carrito** (preparada para implementar)

## 📁 Estructura del Proyecto

```
src/
├── app/
│   ├── components/          # Componentes reutilizables
│   │   ├── ProductList.tsx      # Lista de productos con paginación
│   │   ├── ProductCard.tsx      # Tarjeta individual de producto
│   │   ├── ProductDetailView.tsx # Vista detallada del producto
│   │   ├── ProductImageGallery.tsx # Galería con zoom
│   │   ├── AddToCartButton.tsx  # Botón añadir al carrito
│   │   └── LoadingSkeleton.tsx  # Componente de carga
│   ├── product/[slug]/      # Páginas dinámicas de productos
│   │   └── page.tsx
│   ├── globals.css          # Estilos globales
│   ├── layout.tsx           # Layout principal
│   ├── page.tsx             # Página de inicio
│   └── not-found.tsx        # Página 404 personalizada
├── lib/
│   ├── woocommerce.ts       # Cliente API de WooCommerce
│   └── utils.ts             # Funciones de utilidad
└── types/
    └── woocommerce.ts       # Tipos TypeScript para WC
```

## 🛠️ Instalación y Configuración

### 1. Requisitos Previos

- Node.js 18+ 
- WordPress con WooCommerce instalado
- Claves de API de WooCommerce generadas

### 2. Configuración de WooCommerce

1. Ve a **WooCommerce > Configuración > Avanzado > API REST**
2. Crea una nueva clave API con permisos de **Lectura**
3. Copia el Consumer Key y Consumer Secret

### 3. Variables de Entorno

Crea un archivo `.env.local` en la raíz del proyecto:

```env
NEXT_PUBLIC_WP_API_URL=https://tu-sitio.com/wp-json
NEXT_PUBLIC_WC_CONSUMER_KEY=ck_tu_consumer_key_aqui
NEXT_PUBLIC_WC_CONSUMER_SECRET=cs_tu_consumer_secret_aqui
NEXT_PUBLIC_SITE_NAME=Tu Tienda
NEXT_PUBLIC_DEFAULT_CURRENCY=EUR
NEXT_PUBLIC_DEFAULT_LOCALE=es-ES
```

### 4. Instalación de Dependencias

```bash
npm install
```

### 5. Ejecutar en Desarrollo

```bash
npm run dev
```

La aplicación estará disponible en `http://localhost:3000`

## 🏗️ Construcción para Producción

```bash
# Generar build optimizado
npm run build

# Ejecutar en producción
npm start
```

## 📱 Componentes Principales

### ProductList
Muestra una lista paginada de productos con filtros y búsqueda.

```tsx
<ProductList />
```

### ProductCard
Tarjeta individual que muestra información básica del producto.

```tsx
<ProductCard product={product} />
```

### ProductDetailView
Vista completa del producto con galería, descripción y opciones de compra.

```tsx
<ProductDetailView 
  product={product} 
  relatedProducts={relatedProducts} 
/>
```

### ProductImageGallery
Galería interactiva con zoom y navegación entre imágenes.

```tsx
<ProductImageGallery 
  images={product.images} 
  productName={product.name} 
/>
```

## 🎨 Personalización

### Estilos
Los estilos están basados en Tailwind CSS. Puedes personalizar:

- **Colores**: Modifica la paleta en `tailwind.config.ts`
- **Componentes**: Edita las clases en `globals.css`
- **Layout**: Ajusta los componentes en `src/app/components/`

### Funcionalidad del Carrito
El componente `AddToCartButton` está preparado para integrar con:

- WooCommerce Store API
- Servicios de carrito externos
- Estados globales (Zustand, Redux, etc.)

```tsx
// Ejemplo de integración personalizada
const handleAddToCart = async (product, quantity) => {
  // Tu lógica personalizada aquí
  await addToCart(product.id, quantity)
  updateCartState()
}
```

## 🔧 API de WooCommerce

### Endpoints Utilizados

- `GET /wp-json/wc/v3/products` - Lista de productos
- `GET /wp-json/wc/v3/products/{id}` - Producto específico
- `GET /wp-json/wc/v3/products/categories` - Categorías

### Funciones Disponibles

```tsx
import { wooCommerceApi } from '@/lib/woocommerce'

// Obtener productos
const products = await wooCommerceApi.getProducts({
  per_page: 12,
  page: 1
})

// Buscar productos
const searchResults = await wooCommerceApi.searchProducts('término')

// Obtener producto por slug
const product = await wooCommerceApi.getProductBySlug('producto-ejemplo')

// Productos relacionados
const related = await wooCommerceApi.getRelatedProducts(productId, 4)
```

## 🚀 Optimización y Rendimiento

### Características de Rendimiento

- **ISR (Incremental Static Regeneration)** para productos populares
- **Lazy loading** de imágenes con Next.js Image
- **Caching** de peticiones API
- **Code splitting** automático
- **Optimización de bundles**

### Configuración de Cache

```tsx
// En woocommerce.ts
const response = await fetch(url, {
  next: { revalidate: 300 } // Cache por 5 minutos
})
```

## 🐛 Solución de Problemas

### Error de CORS
Si tienes problemas de CORS, añade estos headers en tu WordPress:

```php
// En functions.php de tu tema
add_action('rest_api_init', function() {
    remove_filter('rest_pre_serve_request', 'rest_send_cors_headers');
    add_filter('rest_pre_serve_request', function($value) {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Credentials: true');
        return $value;
    });
});
```

### Problemas de Autenticación
Verifica que las claves de API tengan los permisos correctos y que estén correctamente configuradas en las variables de entorno.

### Imágenes no Cargan
Configura Next.js para permitir el dominio de tus imágenes:

```js
// next.config.ts
const nextConfig = {
  images: {
    domains: ['tu-sitio.com'],
  },
}
```

## 📄 Licencia

Este proyecto está bajo la licencia MIT. Consulta el archivo `LICENSE` para más detalles.

## 🤝 Contribución

Las contribuciones son bienvenidas. Por favor:

1. Haz fork del proyecto
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

## 📞 Soporte

Si tienes algún problema o pregunta, puedes:

- Abrir un issue en el repositorio
- Contactar al equipo de desarrollo
- Consultar la documentación de [Next.js](https://nextjs.org/docs) y [WooCommerce API](https://woocommerce.github.io/woocommerce-rest-api-docs/)

---

**Nota**: Este proyecto está diseñado para funcionar como parte del plugin WordPress "React WooCommerce Products". Asegúrate de tener el plugin principal instalado y configurado correctamente.
