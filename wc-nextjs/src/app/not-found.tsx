import Link from 'next/link'
import { Metadata } from 'next'

export const metadata: Metadata = {
  title: 'Página no encontrada - 404',
  description: 'La página que buscas no existe o ha sido movida.'
}

export default function NotFound() {
  return (
    <div className="min-h-screen bg-gray-50 flex flex-col items-center justify-center px-4">
      <div className="max-w-md w-full text-center">
        {/* Ilustración 404 */}
        <div className="mb-8">
          <svg
            className="mx-auto h-32 w-32 text-gray-400"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              strokeLinecap="round"
              strokeLinejoin="round"
              strokeWidth={1}
              d="M9.172 16.172a4 4 0 015.656 0M9 12h6m-6-4h6m2 5.291A7.962 7.962 0 0112 20a7.962 7.962 0 01-5-1.709M15 3H9a2 2 0 00-2 2v1.009A3.014 3.014 0 016 9v6a3 3 0 003 3h6a3 3 0 003-3V9a3.014 3.014 0 00-1-2.009V5a2 2 0 00-2-2z"
            />
          </svg>
        </div>

        {/* Título y mensaje */}
        <h1 className="text-4xl font-bold text-gray-900 mb-4">
          404
        </h1>
        <h2 className="text-xl font-semibold text-gray-700 mb-4">
          Producto no encontrado
        </h2>
        <p className="text-gray-600 mb-8">
          Lo sentimos, el producto que buscas no existe o ya no está disponible.
          Puede que haya sido descontinuado o que la URL sea incorrecta.
        </p>

        {/* Botones de acción */}
        <div className="space-y-4">
          <Link
            href="/"
            className="w-full inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
          >
            <svg className="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            Volver al inicio
          </Link>
          
          <Link
            href="/search"
            className="w-full inline-flex items-center justify-center px-6 py-3 border border-gray-300 text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
          >
            <svg className="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            Buscar productos
          </Link>
        </div>

        {/* Enlaces de ayuda */}
        <div className="mt-8 pt-6 border-t border-gray-200">
          <p className="text-sm text-gray-500 mb-4">
            ¿Necesitas ayuda? Aquí tienes algunos enlaces útiles:
          </p>
          <div className="flex flex-col sm:flex-row gap-4 text-sm">
            <Link
              href="/help"
              className="text-blue-600 hover:text-blue-500 transition-colors"
            >
              Centro de ayuda
            </Link>
            <Link
              href="/contact"
              className="text-blue-600 hover:text-blue-500 transition-colors"
            >
              Contacto
            </Link>
            <Link
              href="/sitemap"
              className="text-blue-600 hover:text-blue-500 transition-colors"
            >
              Mapa del sitio
            </Link>
          </div>
        </div>
      </div>
    </div>
  )
} 