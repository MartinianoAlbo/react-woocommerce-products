import React from 'react'
import Image from 'next/image'
import Link from 'next/link'
import { WooProduct } from '@/types/woocommerce'
import { formatPrice } from '@/lib/utils'

interface ProductCardProps {
  product: WooProduct
}

export default function ProductCard({ product }: ProductCardProps) {
  const primaryImage = product.images?.[0]
  const isOnSale = product.sale_price && parseFloat(product.sale_price) > 0
  
  const [imageLoaded, setImageLoaded] = React.useState(false)

  React.useEffect(() => {
    if (primaryImage?.src) {
      setImageLoaded(true) // Set to true anyway to show fallback
    }
  }, [primaryImage?.src])
  
  return (
    <div className="group bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow duration-200">
      <Link href={`/product/${product.slug}`}>
        <div className="relative aspect-square overflow-hidden bg-gray-100">
          {imageLoaded && primaryImage ? (
            <Image
              src={primaryImage.src}
              alt={primaryImage.alt || product.name}
              fill
              className="object-cover group-hover:scale-105 transition-transform duration-200"
              sizes="(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 25vw"
              unoptimized={true}
              onError={(e) => {
                console.error('Image failed to load:', primaryImage.src)
                setImageLoaded(false)
              }}
            />
          ) : (
            <div className="flex items-center justify-center h-full text-gray-400">
              <svg className="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
            </div>
          )}
          
          {/* Badge de descuento */}
          {isOnSale && (
            <div className="absolute top-2 left-2 bg-red-500 text-white text-xs font-medium px-2 py-1 rounded">
              ¡Oferta!
            </div>
          )}
          
          {/* Badge de stock */}
          {product.stock_status === 'outofstock' && (
            <div className="absolute top-2 right-2 bg-gray-900 text-white text-xs font-medium px-2 py-1 rounded">
              Agotado
            </div>
          )}
        </div>
        
        <div className="p-4">
          <h3 className="text-sm font-medium text-gray-900 mb-2 line-clamp-2 group-hover:text-blue-600 transition-colors">
            {product.name}
          </h3>
          
          {/* Categorías */}
          {product.categories && product.categories.length > 0 && (
            <div className="flex flex-wrap gap-1 mb-2">
              {product.categories.slice(0, 2).map((category) => (
                <span
                  key={category.id}
                  className="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded"
                >
                  {category.name}
                </span>
              ))}
            </div>
          )}
          
          {/* Precio */}
          <div className="flex items-center justify-between">
            <div className="flex items-center space-x-2">
              {isOnSale ? (
                <>
                  <span className="text-lg font-bold text-red-600">
                    {formatPrice(product.sale_price!)}
                  </span>
                  <span className="text-sm text-gray-500 line-through">
                    {formatPrice(product.regular_price)}
                  </span>
                </>
              ) : (
                <span className="text-lg font-bold text-gray-900">
                  {formatPrice(product.price)}
                </span>
              )}
            </div>
            
            {/* Rating */}
            {product.average_rating && parseFloat(product.average_rating) > 0 && (
              <div className="flex items-center">
                <div className="flex text-yellow-400">
                  {Array.from({ length: 5 }, (_, i) => (
                    <svg
                      key={i}
                      className={`w-4 h-4 ${
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
                <span className="text-xs text-gray-500 ml-1">
                  ({product.rating_count})
                </span>
              </div>
            )}
          </div>
          
          {/* Descripción corta */}
          {product.short_description && (
            <div 
              className="text-sm text-gray-600 mt-2 line-clamp-2"
              dangerouslySetInnerHTML={{ 
                __html: product.short_description.replace(/<[^>]*>/g, '') 
              }}
            />
          )}
        </div>
      </Link>
    </div>
  )
} 