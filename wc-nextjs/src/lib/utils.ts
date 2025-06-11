import { type ClassValue, clsx } from "clsx"
import { twMerge } from "tailwind-merge"

export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs))
}

export function formatPrice(price: string | number, currency = 'EUR', locale = 'es-ES'): string {
  const numericPrice = typeof price === 'string' ? parseFloat(price) : price
  
  if (isNaN(numericPrice)) {
    return '0,00 €'
  }
  
  return new Intl.NumberFormat(locale, {
    style: 'currency',
    currency: currency,
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(numericPrice)
}

export function stripHtml(html: string): string {
  return html.replace(/<[^>]*>/g, '').trim()
}

export function isProductInStock(stockStatus: string, stockQuantity?: number | null): boolean {
  if (stockStatus === 'outofstock') return false
  if (stockStatus === 'onbackorder') return true
  if (stockStatus === 'instock') {
    if (stockQuantity === null || stockQuantity === undefined) return true
    return stockQuantity > 0
  }
  return false
}

export function getStockStatusText(stockStatus: string, stockQuantity?: number | null): string {
  switch (stockStatus) {
    case 'instock':
      if (stockQuantity === null || stockQuantity === undefined) return 'En stock'
      if (stockQuantity > 10) return 'En stock'
      if (stockQuantity > 0) return `Últimas ${stockQuantity} unidades`
      return 'Sin stock'
    case 'outofstock':
      return 'Agotado'
    case 'onbackorder':
      return 'Disponible bajo pedido'
    default:
      return 'Estado desconocido'
  }
}
 