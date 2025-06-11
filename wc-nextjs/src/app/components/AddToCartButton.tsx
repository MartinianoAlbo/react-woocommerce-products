'use client'

import { useState } from 'react'
import { WooProduct } from '@/types/woocommerce'
import { wooCommerceApi } from '@/lib/woocommerce'

interface AddToCartButtonProps {
  product: WooProduct
  quantity: number
  variation?: Record<string, unknown>
  className?: string
}

export default function AddToCartButton({ 
  product, 
  quantity, 
  variation, 
  className = '' 
}: AddToCartButtonProps) {
  const [isLoading, setIsLoading] = useState(false)
  const [isAdded, setIsAdded] = useState(false)
  const [error, setError] = useState<string | null>(null)

  const handleAddToCart = async () => {
    setIsLoading(true)
    setError(null)
    
    try {
      const cartData = {
        product_id: product.id,
        quantity: quantity,
        variation_id: variation?.id as number || 0,
        variation: variation?.attributes as Record<string, string> || {}
      }
      
      const response = await wooCommerceApi.addToCart(cartData)
      
      if (response.success) {
        // Mostrar estado de éxito
        setIsAdded(true)
        
        // Resetear después de 3 segundos
        setTimeout(() => {
          setIsAdded(false)
        }, 3000)
        
        // Disparar evento personalizado para actualizar componentes del carrito
        window.dispatchEvent(new CustomEvent('cartUpdated', {
          detail: { 
            action: 'add',
            product, 
            quantity, 
            variation,
            cart: response.cart
          }
        }))
      } else {
        throw new Error('Error al añadir al carrito')
      }
      
    } catch (error) {
      console.error('Error añadiendo producto al carrito:', error)
      setError(error instanceof Error ? error.message : 'Error desconocido')
      
      // Limpiar error después de 5 segundos
      setTimeout(() => {
        setError(null)
      }, 5000)
    } finally {
      setIsLoading(false)
    }
  }

  const baseClasses = `
    w-full flex items-center justify-center px-6 py-3 text-base font-medium rounded-md
    transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2
    disabled:opacity-50 disabled:cursor-not-allowed
  `

  // Estado de error
  if (error) {
    return (
      <div className="space-y-2">
        <button
          onClick={handleAddToCart}
          disabled={isLoading || product.stock_status === 'outofstock'}
          className={`${baseClasses} ${
            product.stock_status === 'outofstock'
              ? 'bg-gray-400 text-gray-700 cursor-not-allowed'
              : 'bg-blue-600 hover:bg-blue-700 text-white focus:ring-blue-500'
          } ${className}`}
        >
          <svg className="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.293 2.293a1 1 0 001.414 1.414L8 15m0 0v6a2 2 0 002 2h1M9 3v2m6-2v2m-6 8h6" />
          </svg>
          {product.stock_status === 'outofstock' ? 'Agotado' : 'Reintentar'}
        </button>
        <div className="text-sm text-red-600 text-center">
          {error}
        </div>
      </div>
    )
  }

  // Estado de éxito
  if (isAdded) {
    return (
      <button
        disabled
        className={`${baseClasses} bg-green-600 text-white ${className}`}
      >
        <svg className="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
        </svg>
        ¡Añadido al carrito!
      </button>
    )
  }

  // Estado normal
  return (
    <button
      onClick={handleAddToCart}
      disabled={isLoading || product.stock_status === 'outofstock'}
      className={`${baseClasses} ${
        product.stock_status === 'outofstock'
          ? 'bg-gray-400 text-gray-700 cursor-not-allowed'
          : 'bg-blue-600 hover:bg-blue-700 text-white focus:ring-blue-500'
      } ${className}`}
    >
      {isLoading ? (
        <>
          <svg className="animate-spin -ml-1 mr-2 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
            <circle
              className="opacity-25"
              cx="12"
              cy="12"
              r="10"
              stroke="currentColor"
              strokeWidth="4"
            />
            <path
              className="opacity-75"
              fill="currentColor"
              d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
            />
          </svg>
          Añadiendo...
        </>
      ) : (
        <>
          <svg className="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.293 2.293a1 1 0 001.414 1.414L8 15m0 0v6a2 2 0 002 2h1M9 3v2m6-2v2m-6 8h6" />
          </svg>
          {product.stock_status === 'outofstock' ? 'Agotado' : 'Añadir al carrito'}
        </>
      )}
    </button>
  )
} 