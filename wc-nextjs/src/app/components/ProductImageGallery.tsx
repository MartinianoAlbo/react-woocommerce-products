'use client'

import { useState } from 'react'
import Image from 'next/image'
import { WooImage } from '@/types/woocommerce'

interface ProductImageGalleryProps {
  images: WooImage[]
  productName: string
}

export default function ProductImageGallery({ images, productName }: ProductImageGalleryProps) {
  const [selectedImageIndex, setSelectedImageIndex] = useState(0)
  const [isFullscreenOpen, setIsFullscreenOpen] = useState(false)
  
  if (!images || images.length === 0) {
    return (
      <div className="aspect-square bg-gray-100 rounded-lg flex items-center justify-center">
        <svg className="w-24 h-24 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
      </div>
    )
  }

  const currentImage = images[selectedImageIndex]

  return (
    <div className="space-y-4">
      {/* Imagen principal */}
      <div className="relative aspect-square overflow-hidden rounded-lg bg-gray-100">
        <Image
          src={currentImage.src}
          alt={currentImage.alt || productName}
          fill
          className="object-cover cursor-zoom-in"
          sizes="(max-width: 768px) 100vw, 50vw"
          priority
          unoptimized={true}
          onClick={() => setIsFullscreenOpen(true)}
        />
        
        {/* Botón de zoom */}
        <button
          onClick={() => setIsFullscreenOpen(true)}
          className="absolute top-4 right-4 bg-white/80 hover:bg-white text-gray-700 p-2 rounded-full transition-all duration-200"
          aria-label="Ampliar imagen"
        >
          <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
          </svg>
        </button>
      </div>

      {/* Miniaturas */}
      {images.length > 1 && (
        <div className="grid grid-cols-4 gap-2">
          {images.map((image, index) => (
            <button
              key={image.id}
              onClick={() => setSelectedImageIndex(index)}
              className={`relative aspect-square overflow-hidden rounded-md border-2 transition-all duration-200 ${
                index === selectedImageIndex
                  ? 'border-blue-500 ring-2 ring-blue-200'
                  : 'border-gray-200 hover:border-gray-300'
              }`}
            >
              <Image
                src={image.src}
                alt={image.alt || `${productName} imagen ${index + 1}`}
                fill
                className="object-cover"
                sizes="(max-width: 768px) 25vw, 12vw"
                unoptimized={true}
              />
            </button>
          ))}
        </div>
      )}

      {/* Modal de pantalla completa */}
      {isFullscreenOpen && (
        <div className="fixed inset-0 z-50 bg-black bg-opacity-90 flex items-center justify-center p-4">
          <div className="relative max-w-4xl max-h-full">
            <button
              onClick={() => setIsFullscreenOpen(false)}
              className="absolute -top-12 right-0 text-white hover:text-gray-300 p-2"
              aria-label="Cerrar imagen ampliada"
            >
              <svg className="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
            
            <div className="relative">
              <Image
                src={currentImage.src}
                alt={currentImage.alt || productName}
                width={800}
                height={600}
                className="max-w-full max-h-[80vh] object-contain"
                unoptimized={true}
              />
              
              {/* Navegación en pantalla completa */}
              {images.length > 1 && (
                <>
                  {selectedImageIndex > 0 && (
                    <button
                      onClick={() => setSelectedImageIndex(selectedImageIndex - 1)}
                      className="absolute left-4 top-1/2 transform -translate-y-1/2 bg-black/50 hover:bg-black/70 text-white p-2 rounded-full transition-all duration-200"
                      aria-label="Imagen anterior"
                    >
                      <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
                      </svg>
                    </button>
                  )}
                  
                  {selectedImageIndex < images.length - 1 && (
                    <button
                      onClick={() => setSelectedImageIndex(selectedImageIndex + 1)}
                      className="absolute right-4 top-1/2 transform -translate-y-1/2 bg-black/50 hover:bg-black/70 text-white p-2 rounded-full transition-all duration-200"
                      aria-label="Imagen siguiente"
                    >
                      <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
                      </svg>
                    </button>
                  )}
                </>
              )}
            </div>
            
            {/* Indicador de imagen */}
            {images.length > 1 && (
              <div className="absolute bottom-4 left-1/2 transform -translate-x-1/2 bg-black/50 text-white px-3 py-1 rounded-full text-sm">
                {selectedImageIndex + 1} de {images.length}
              </div>
            )}
          </div>
        </div>
      )}
    </div>
  )
} 