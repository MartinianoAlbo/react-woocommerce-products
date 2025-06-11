import { WooProduct, WooProductQuery, WooProductsResponse } from '@/types/woocommerce'

const WC_API_CONFIG = {
  baseURL: process.env.NEXT_PUBLIC_WP_API_URL || 'http://saphirus.local/wp-json',
  consumerKey: process.env.NEXT_PUBLIC_WC_CONSUMER_KEY || '',
  consumerSecret: process.env.NEXT_PUBLIC_WC_CONSUMER_SECRET || '',
  version: 'react-woo-products/v1'
}

// Tipos para el carrito
export interface CartItem {
  key: string
  product_id: number
  variation_id: number
  quantity: number
  line_total: number
  line_subtotal: number
  product: {
    id: number
    name: string
    slug: string
    price: string
    image: string[] | null
  }
}

export interface CartTotals {
  subtotal: number
  total: number
  tax_total: number
  shipping_total: number
}

export interface Cart {
  items: CartItem[]
  totals: CartTotals
  item_count: number
}

export interface AddToCartRequest {
  product_id: number
  quantity: number
  variation_id?: number
  variation?: Record<string, string>
}

export interface AddToCartResponse {
  success: boolean
  cart_item_key: string
  message: string
  cart: Cart
}

class WooCommerceAPI {
  private baseURL: string
  private consumerKey: string
  private consumerSecret: string
  private version: string

  constructor() {
    this.baseURL = WC_API_CONFIG.baseURL
    this.consumerKey = WC_API_CONFIG.consumerKey
    this.consumerSecret = WC_API_CONFIG.consumerSecret
    this.version = WC_API_CONFIG.version
    
    // Debug de configuración
    console.log('WooCommerce API Config:', {
      baseURL: this.baseURL,
      version: this.version,
      hasConsumerKey: !!this.consumerKey,
      hasConsumerSecret: !!this.consumerSecret
    })
  }

  private buildURL(endpoint: string, params?: WooProductQuery): string {
    const url = new URL(`${this.baseURL}/${this.version}/${endpoint}`)
    
    if (params) {
      Object.entries(params).forEach(([key, value]) => {
        if (value !== undefined && value !== null) {
          if (Array.isArray(value)) {
            value.forEach((item) => url.searchParams.append(key, item.toString()))
          } else {
            url.searchParams.append(key, value.toString())
          }
        }
      })
    }
    
    console.log('Built URL:', url.toString())
    return url.toString()
  }

  private async makeRequest(url: string, options: RequestInit = {}): Promise<Response> {
    console.log('Making request to:', url, 'with options:', options)
    
    const defaultHeaders = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    }

    const requestOptions: RequestInit = {
      ...options,
      headers: {
        ...defaultHeaders,
        ...options.headers,
      },
      mode: 'cors' as RequestMode,
      credentials: 'same-origin' as RequestCredentials,
    }

    try {
      const response = await fetch(url, requestOptions)
      console.log('Response status:', response.status, response.statusText)
      console.log('Response headers:', response.headers)
      
      return response
    } catch (error) {
      console.error('Fetch error details:', {
        error,
        url,
        options: requestOptions
      })
      throw error
    }
  }

  async getProducts(query: WooProductQuery = {}): Promise<WooProductsResponse> {
    const url = this.buildURL('products', query)
    
    const response = await this.makeRequest(url, {
      next: { revalidate: 300 }
    })
    
    if (!response.ok) {
      throw new Error(`Failed to fetch products: ${response.statusText}`)
    }
    
    const data = await response.json()
    
    return {
      data: data.products || [],
      total: data.total || 0,
      total_pages: data.pages || 0
    }
  }

  async getProductBySlug(slug: string): Promise<WooProduct | null> {
    try {
      const response = await this.getProducts({ slug, per_page: 1 })
      const product = response.data[0] || null
      return product
    } catch (error) {
      console.error('Error fetching product by slug:', error)
      return null
    }
  }

  async getProductById(id: number): Promise<WooProduct | null> {
    try {
      const url = this.buildURL(`products/${id}`)
      
      const response = await this.makeRequest(url, {
        next: { revalidate: 300 }
      })
      
      if (!response.ok) {
        throw new Error(`Failed to fetch product: ${response.statusText}`)
      }
      
      const product = await response.json()
      return product
    } catch (error) {
      console.error('Error fetching product by ID:', error)
      return null
    }
  }

  async getRelatedProducts(productId: number, limit = 4): Promise<WooProduct[]> {
    try {
      const product = await this.getProductById(productId)
      if (!product || !product.categories.length) {
        return []
      }

      const categorySlug = product.categories[0].slug
      const response = await this.getProducts({
        per_page: limit + 1,
        exclude: [productId],
        category: categorySlug
      })

      return response.data.slice(0, limit)
    } catch (error) {
      console.error('Error fetching related products:', error)
      return []
    }
  }

  // Métodos del carrito
  async addToCart(data: AddToCartRequest): Promise<AddToCartResponse> {
    const url = this.buildURL('cart/add')
    
    console.log('Adding to cart:', data)
    
    const response = await this.makeRequest(url, {
      method: 'POST',
      body: JSON.stringify(data)
    })
    
    if (!response.ok) {
      const responseText = await response.text()
      console.error('Add to cart error response:', responseText)
      
      let errorData
      try {
        errorData = JSON.parse(responseText)
      } catch (e) {
        errorData = { message: `HTTP ${response.status}: ${response.statusText}` }
      }
      
      throw new Error(errorData.message || 'Error al añadir al carrito')
    }
    
    const result = await response.json()
    console.log('Add to cart success:', result)
    return result
  }

  async getCart(): Promise<Cart> {
    const url = this.buildURL('cart')
    
    const response = await this.makeRequest(url, {
      cache: 'no-store'
    })
    
    if (!response.ok) {
      throw new Error(`Failed to fetch cart: ${response.statusText}`)
    }
    
    return await response.json()
  }

  async updateCartItem(cartItemKey: string, quantity: number): Promise<{ success: boolean; message: string; cart: Cart }> {
    const url = this.buildURL('cart/update')
    
    const response = await this.makeRequest(url, {
      method: 'POST',
      body: JSON.stringify({
        cart_item_key: cartItemKey,
        quantity
      })
    })
    
    if (!response.ok) {
      const errorData = await response.json()
      throw new Error(errorData.message || 'Error al actualizar carrito')
    }
    
    return await response.json()
  }

  async removeFromCart(cartItemKey: string): Promise<{ success: boolean; message: string; cart: Cart }> {
    const url = this.buildURL('cart/remove')
    
    const response = await this.makeRequest(url, {
      method: 'POST',
      body: JSON.stringify({
        cart_item_key: cartItemKey
      })
    })
    
    if (!response.ok) {
      const errorData = await response.json()
      throw new Error(errorData.message || 'Error al eliminar del carrito')
    }
    
    return await response.json()
  }
}

export const wooCommerceApi = new WooCommerceAPI()