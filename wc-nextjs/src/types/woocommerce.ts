export interface WooImage {
  id: number
  src: string
  name: string
  alt: string
}

export interface WooCategory {
  id: number
  name: string
  slug: string
}

export interface WooTag {
  id: number
  name: string
  slug: string
}

export interface WooAttribute {
  id: number
  name: string
  position: number
  visible: boolean
  variation: boolean
  options: string[]
}

export interface WooProduct {
  id: number
  name: string
  slug: string
  permalink: string
  date_created: string
  date_modified: string
  type: 'simple' | 'grouped' | 'external' | 'variable'
  status: 'draft' | 'pending' | 'private' | 'publish'
  featured: boolean
  catalog_visibility: 'visible' | 'catalog' | 'search' | 'hidden'
  description: string
  short_description: string
  sku: string
  price: string
  regular_price: string
  sale_price: string
  date_on_sale_from: string | null
  date_on_sale_to: string | null
  price_html: string
  on_sale: boolean
  purchasable: boolean
  total_sales: number
  virtual: boolean
  downloadable: boolean
  downloads: unknown[]
  download_limit: number
  download_expiry: number
  external_url: string
  button_text: string
  tax_status: 'taxable' | 'shipping' | 'none'
  tax_class: string
  manage_stock: boolean
  stock_quantity: number | null
  stock_status: 'instock' | 'outofstock' | 'onbackorder'
  backorders: 'no' | 'notify' | 'yes'
  backorders_allowed: boolean
  backordered: boolean
  low_stock_amount: number | null
  sold_individually: boolean
  weight: string
  dimensions: {
    length: string
    width: string
    height: string
  }
  shipping_required: boolean
  shipping_taxable: boolean
  shipping_class: string
  shipping_class_id: number
  reviews_allowed: boolean
  average_rating: string
  rating_count: number
  upsell_ids: number[]
  cross_sell_ids: number[]
  parent_id: number
  purchase_note: string
  categories: WooCategory[]
  tags: WooTag[]
  images: WooImage[]
  attributes: WooAttribute[]
  default_attributes: unknown[]
  variations: number[]
  grouped_products: number[]
  menu_order: number
  meta_data: unknown[]
}

export interface WooProductsResponse {
  data: WooProduct[]
  total: number
  total_pages: number
}

export interface WooProductQuery {
  page?: number
  per_page?: number
  search?: string
  after?: string
  before?: string
  exclude?: number[]
  include?: number[]
  offset?: number
  order?: 'asc' | 'desc'
  orderby?: 'date' | 'id' | 'include' | 'title' | 'slug' | 'price' | 'popularity' | 'rating' | 'menu_order'
  parent?: number[]
  parent_exclude?: number[]
  slug?: string
  status?: 'any' | 'draft' | 'pending' | 'private' | 'publish'
  type?: 'simple' | 'grouped' | 'external' | 'variable'
  sku?: string
  featured?: boolean
  category?: string
  tag?: string
  shipping_class?: string
  attribute?: string
  attribute_term?: string
  tax_class?: string
  on_sale?: boolean
  min_price?: string
  max_price?: string
  stock_status?: 'instock' | 'outofstock' | 'onbackorder'
} 