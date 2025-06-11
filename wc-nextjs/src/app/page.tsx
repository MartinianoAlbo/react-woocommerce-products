import ProductList from './components/ProductList'

export default function Home() {
  return (
    <div className="min-h-screen bg-gray-50">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <header className="mb-8">
          <h1 className="text-3xl font-bold text-gray-900 mb-2">
            Tienda WooCommerce
          </h1>
          <p className="text-gray-600">
            Productos renderizados con Next.js y React
          </p>
        </header>
        
        <ProductList />
      </div>
    </div>
  )
}
