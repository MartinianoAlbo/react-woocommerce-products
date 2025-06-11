'use client'

import { useState } from 'react'
import Link from 'next/link'
import { WooProduct } from '@/types/woocommerce'
import { formatPrice, getStockStatusText, isProductInStock } from '@/lib/utils'
import ProductCard from './ProductCard'
import ProductImageGallery from '@/app/components/ProductImageGallery'
import AddToCartButton from './AddToCartButton'

interface ProductDetailViewProps {
  product: WooProduct
  relatedProducts: WooProduct[]
}

export default function ProductDetailView({ product, relatedProducts }: ProductDetailViewProps) {
  const [quantity, setQuantity] = useState(1)
  
  const isOnSale = product.sale_price && parseFloat(product.sale_price) > 0
  const inStock = isProductInStock(product.stock_status, product.stock_quantity)
  const stockText = getStockStatusText(product.stock_status, product.stock_quantity)

  return (
    <div className="space-y-12">
      {/* Breadcrumbs */}
      <nav className="flex" aria-label="Breadcrumb">
        <ol className="flex items-center space-x-2">
          <li>
            <Link href="/" className="text-gray-500 hover:text-gray-700">
              Inicio
            </Link>
          </li>
          <li className="flex items-center">
            <svg className="h-4 w-4 text-gray-400 mx-2" fill="currentColor" viewBox="0 0 20 20">
              <path fillRule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clipRule="evenodd" />
            </svg>
            {product.categories && product.categories.length > 0 && (
              <>
                <Link 
                  href={`/category/${product.categories[0].slug}`}
                  className="text-gray-500 hover:text-gray-700"
                >
                  {product.categories[0].name}
                </Link>
                <svg className="h-4 w-4 text-gray-400 mx-2" fill="currentColor" viewBox="0 0 20 20">
                  <path fillRule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clipRule="evenodd" />
                </svg>
              </>
            )}
          </li>
          <li className="text-gray-900 font-medium truncate">
            {product.name}
          </li>
        </ol>
      </nav>

      {/* Producto principal */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-12">
        {/* Galería de imágenes */}
        <div className="space-y-4">
          <ProductImageGallery images={product.images} productName={product.name} />
        </div>

        {/* Información del producto */}
        <div className="space-y-6">
          <div>
            <h1 className="text-3xl font-bold text-gray-900 mb-2">
              {product.name}
            </h1>
            
            {/* SKU */}
            {product.sku && (
              <p className="text-sm text-gray-500">
                SKU: {product.sku}
              </p>
            )}
          </div>

          {/* Rating */}
          {product.average_rating && parseFloat(product.average_rating) > 0 && (
            <div className="flex items-center space-x-2">
              <div className="flex text-yellow-400">
                {Array.from({ length: 5 }, (_, i) => (
                  <svg
                    key={i}
                    className={`w-5 h-5 ${
                      i < Math.floor(parseFloat(product.average_rating))
                        ? 'text-yellow-400'
                        : 'text-gray-300'
                    }`}
                    fill="currentColor"
                    viewBox="0 0 20 20"
                  >
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                  </svg>
                ))}
              </div>
              <span className="text-sm text-gray-600">
                {product.average_rating} ({product.rating_count} {product.rating_count === 1 ? 'valoración' : 'valoraciones'})
              </span>
            </div>
          )}

          {/* Precio */}
          <div className="flex items-center space-x-4">
            {isOnSale ? (
              <>
                <span className="text-3xl font-bold text-red-600">
                  {formatPrice(product.sale_price!)}
                </span>
                <span className="text-xl text-gray-500 line-through">
                  {formatPrice(product.regular_price)}
                </span>
                <span className="bg-red-100 text-red-800 text-sm font-medium px-2 py-1 rounded">
                  Ahorra {formatPrice((parseFloat(product.regular_price) - parseFloat(product.sale_price!)).toString())}
                </span>
              </>
            ) : (
              <span className="text-3xl font-bold text-gray-900">
                {formatPrice(product.price)}
              </span>
            )}
          </div>

          {/* Descripción corta */}
          {product.short_description && (
            <div 
              className="text-gray-700 prose prose-sm"
              dangerouslySetInnerHTML={{ __html: product.short_description }}
            />
          )}

          {/* Estado de stock */}
          <div className="flex items-center space-x-2">
            <div className={`w-3 h-3 rounded-full ${
              inStock ? 'bg-green-500' : 'bg-red-500'
            }`} />
            <span className={`font-medium ${
              inStock ? 'text-green-700' : 'text-red-700'
            }`}>
              {stockText}
            </span>
          </div>

          {/* Categorías y etiquetas */}
          <div className="space-y-3">
            {product.categories && product.categories.length > 0 && (
              <div>
                <span className="text-sm font-medium text-gray-900 mr-2">Categorías:</span>
                <div className="inline-flex flex-wrap gap-2">
                  {product.categories.map((category) => (
                    <Link
                      key={category.id}
                      href={`/category/${category.slug}`}
                      className="text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1 rounded-full transition-colors"
                    >
                      {category.name}
                    </Link>
                  ))}
                </div>
              </div>
            )}

            {product.tags && product.tags.length > 0 && (
              <div>
                <span className="text-sm font-medium text-gray-900 mr-2">Etiquetas:</span>
                <div className="inline-flex flex-wrap gap-2">
                  {product.tags.map((tag) => (
                    <span
                      key={tag.id}
                      className="text-sm bg-blue-100 text-blue-700 px-3 py-1 rounded-full"
                    >
                      #{tag.name}
                    </span>
                  ))}
                </div>
              </div>
            )}
          </div>

          {/* Añadir al carrito */}
          {inStock && (
            <div className="space-y-4">
              <div className="flex items-center space-x-4">
                <label className="text-sm font-medium text-gray-900">
                  Cantidad:
                </label>
                <div className="flex items-center border border-gray-300 rounded-md">
                  <button
                    onClick={() => setQuantity(Math.max(1, quantity - 1))}
                    className="px-3 py-2 text-gray-600 hover:text-gray-800"
                    disabled={quantity <= 1}
                  >
                    −
                  </button>
                  <input
                    type="number"
                    min="1"
                    max={product.stock_quantity || 999}
                    value={quantity}
                    onChange={(e) => setQuantity(Math.max(1, parseInt(e.target.value) || 1))}
                    className="w-16 px-3 py-2 text-center border-0 focus:ring-0"
                  />
                  <button
                    onClick={() => setQuantity(quantity + 1)}
                    className="px-3 py-2 text-gray-600 hover:text-gray-800"
                    disabled={product.stock_quantity !== null && quantity >= product.stock_quantity}
                  >
                    +
                  </button>
                </div>
              </div>

              <AddToCartButton 
                product={product}
                quantity={quantity}
              />
            </div>
          )}
        </div>
      </div>

      {/* Descripción completa */}
      {product.description && (
        <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
          <h2 className="text-2xl font-bold text-gray-900 mb-4">
            Descripción del producto
          </h2>
          <div 
            className="prose prose-gray max-w-none"
            dangerouslySetInnerHTML={{ __html: product.description }}
          />
        </div>
      )}

      {/* Productos relacionados */}
      {relatedProducts.length > 0 && (
        <div className="space-y-6">
          <h2 className="text-2xl font-bold text-gray-900">
            Productos relacionados
          </h2>
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            {relatedProducts.map((relatedProduct) => (
              <ProductCard key={relatedProduct.id} product={relatedProduct} />
            ))}
          </div>
        </div>
      )}
    </div>
  )
} 