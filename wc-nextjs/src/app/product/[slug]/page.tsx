import { notFound } from 'next/navigation'
import { wooCommerceApi } from '@/lib/woocommerce'
import ProductDetailView from '@/app/components/ProductDetailView'
import { Metadata } from 'next'

interface ProductPageProps {
  params: Promise<{
    slug: string
  }>
}

// Generar metadata dinámicamente
export async function generateMetadata({ params }: ProductPageProps): Promise<Metadata> {
  const { slug } = await params
  const product = await wooCommerceApi.getProductBySlug(slug)
  
  if (!product) {
    return {
      title: 'Producto no encontrado',
      description: 'El producto que buscas no existe o no está disponible.'
    }
  }

  return {
    title: product.name,
    description: product.short_description?.replace(/<[^>]*>/g, '') || product.description?.replace(/<[^>]*>/g, '').substring(0, 160),
    openGraph: {
      title: product.name,
      description: product.short_description?.replace(/<[^>]*>/g, '') || product.description?.replace(/<[^>]*>/g, '').substring(0, 160),
      images: product.images?.map(img => ({
        url: img.src,
        width: 800,
        height: 600,
        alt: img.alt || product.name
      })) || [],
      type: 'website'
    },
    twitter: {
      card: 'summary_large_image',
      title: product.name,
      description: product.short_description?.replace(/<[^>]*>/g, '') || product.description?.replace(/<[^>]*>/g, '').substring(0, 160),
      images: product.images?.[0]?.src ? [product.images[0].src] : []
    }
  }
}

export default async function ProductPage({ params }: ProductPageProps) {
  const { slug } = await params
  const product = await wooCommerceApi.getProductBySlug(slug)
  
  if (!product) {
    notFound()
  }

  // Obtener productos relacionados
  const relatedProducts = await wooCommerceApi.getRelatedProducts(product.id, 4)

  return (
    <div className="min-h-screen bg-gray-50">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <ProductDetailView 
          product={product} 
          relatedProducts={relatedProducts}
        />
      </div>
    </div>
  )
}

// Generar páginas estáticas para productos populares (opcional)
export async function generateStaticParams() {
  try {
    // Obtener los primeros 20 productos para pre-renderizar
    const response = await wooCommerceApi.getProducts({ 
      per_page: 20,
      orderby: 'popularity',
      order: 'desc'
    })
    
    return response.data.map((product) => ({
      slug: product.slug
    }))
  } catch (error) {
    console.error('Error generating static params:', error)
    return []
  }
} 