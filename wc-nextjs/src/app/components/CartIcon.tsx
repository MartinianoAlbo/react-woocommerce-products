'use client'

import { useState, useEffect } from 'react'
import { wooCommerceApi, Cart } from '@/lib/woocommerce'

export default function CartIcon() {
  const [cart, setCart] = useState<Cart | null>(null)
  const [isLoading, setIsLoading] = useState(true)

  const loadCart = async () => {
    try {
      setIsLoading(true)
      const cartData = await wooCommerceApi.getCart()
      setCart(cartData)
    } catch (error) {
      console.error('Error loading cart:', error)
      // Si hay error, inicializar carrito vacío
      setCart({
        items: [],
        totals: { subtotal: 0, total: 0, tax_total: 0, shipping_total: 0 },
        item_count: 0
      })
    } finally {
      setIsLoading(false)
    }
  }

  useEffect(() => {
    loadCart()

    // Escuchar eventos de actualización del carrito
    const handleCartUpdate = (event: CustomEvent) => {
      const { cart: updatedCart } = event.detail
      if (updatedCart) {
        setCart(updatedCart)
      } else {
        // Si no hay información del carrito en el evento, recargar
        loadCart()
      }
    }

    window.addEventListener('cartUpdated', handleCartUpdate as EventListener)

    return () => {
      window.removeEventListener('cartUpdated', handleCartUpdate as EventListener)
    }
  }, [])

  const formatPrice = (price: number) => {
    return new Intl.NumberFormat('es-ES', {
      style: 'currency',
      currency: 'EUR',
      minimumFractionDigits: 2,
    }).format(price)
  }

  if (isLoading) {
    return (
      <div className="flex items-center space-x-2 text-gray-600">
        <div className="animate-pulse">
          <div className="relative">
            <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.293 2.293a1 1 0 001.414 1.414L8 15m0 0v6a2 2 0 002 2h1M9 3v2m6-2v2m-6 8h6" />
            </svg>
          </div>
        </div>
        <span className="text-sm">Cargando...</span>
      </div>
    )
  }

  const itemCount = cart?.item_count || 0
  const total = cart?.totals?.total || 0

  return (
    <div className="flex items-center space-x-2 text-gray-700">
      <div className="relative">
        <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.293 2.293a1 1 0 001.414 1.414L8 15m0 0v6a2 2 0 002 2h1M9 3v2m6-2v2m-6 8h6" />
        </svg>
        
        {itemCount > 0 && (
          <span className="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center font-medium">
            {itemCount > 99 ? '99+' : itemCount}
          </span>
        )}
      </div>
      
      <div className="text-sm">
        {itemCount > 0 ? (
          <div>
            <div className="font-medium">{itemCount} {itemCount === 1 ? 'producto' : 'productos'}</div>
            <div className="text-xs text-gray-500">{formatPrice(total)}</div>
          </div>
        ) : (
          <span>Carrito vacío</span>
        )}
      </div>
    </div>
  )
} 