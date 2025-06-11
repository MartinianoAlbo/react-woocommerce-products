'use client'

import { useEffect, useState, useCallback } from 'react'
import { WooProduct } from '@/types/woocommerce'
import { wooCommerceApi } from '@/lib/woocommerce'
import ProductCard from './ProductCard'
import LoadingSkeleton from '@/app/components/LoadingSkeleton'

export default function ProductList() {
  const [products, setProducts] = useState<WooProduct[]>([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)
  const [currentPage, setCurrentPage] = useState(1)
  const [totalPages, setTotalPages] = useState(1)
  
  const PRODUCTS_PER_PAGE = 12

  const loadProducts = useCallback(async () => {
    try {
      setLoading(true)
      const response = await wooCommerceApi.getProducts({
        page: currentPage,
        per_page: PRODUCTS_PER_PAGE,
        status: 'publish'
      })
      
      setProducts(response.data)
      setTotalPages(Math.ceil(response.total / PRODUCTS_PER_PAGE))
      setError(null)
    } catch (err) {
      setError('Error al cargar los productos')
      console.error('Error loading products:', err)
    } finally {
      setLoading(false)
    }
  }, [currentPage])

  useEffect(() => {
    loadProducts()
  }, [loadProducts])

  if (loading) {
    return <LoadingSkeleton />
  }

  if (error) {
    return (
      <div className="text-center py-12">
        <div className="bg-red-50 border border-red-200 rounded-lg p-6 max-w-md mx-auto">
          <h3 className="text-red-800 font-medium mb-2">Error</h3>
          <p className="text-red-600">{error}</p>
          <button 
            onClick={loadProducts}
            className="mt-4 bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition-colors"
          >
            Reintentar
          </button>
        </div>
      </div>
    )
  }

  if (products.length === 0) {
    return (
      <div className="text-center py-12">
        <h3 className="text-lg font-medium text-gray-900 mb-2">
          No hay productos disponibles
        </h3>
        <p className="text-gray-600">
          No se encontraron productos en la tienda.
        </p>
      </div>
    )
  }

  return (
    <div>
      {/* Grid de productos */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-8">
        {products.map((product) => (
          <ProductCard key={product.id} product={product} />
        ))}
      </div>

      {/* Paginación */}
      {totalPages > 1 && (
        <div className="flex justify-center items-center space-x-2">
          <button
            onClick={() => setCurrentPage(prev => Math.max(prev - 1, 1))}
            disabled={currentPage === 1}
            className="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Anterior
          </button>
          
          <div className="flex space-x-1">
            {Array.from({ length: Math.min(5, totalPages) }, (_, i) => {
              const pageNum = i + 1
              const isActive = pageNum === currentPage
              
              return (
                <button
                  key={pageNum}
                  onClick={() => setCurrentPage(pageNum)}
                  className={`px-3 py-2 text-sm font-medium rounded-md transition-colors ${
                    isActive
                      ? 'bg-blue-600 text-white'
                      : 'text-gray-700 hover:bg-gray-100'
                  }`}
                >
                  {pageNum}
                </button>
              )
            })}
          </div>
          
          <button
            onClick={() => setCurrentPage(prev => Math.min(prev + 1, totalPages))}
            disabled={currentPage === totalPages}
            className="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Siguiente
          </button>
        </div>
      )}
    </div>
  )
} 