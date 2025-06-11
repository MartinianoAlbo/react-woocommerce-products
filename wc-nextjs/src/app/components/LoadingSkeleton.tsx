export default function LoadingSkeleton() {
  return (
    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
      {Array.from({ length: 12 }, (_, i) => (
        <div key={i} className="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden animate-pulse">
          {/* Imagen skeleton */}
          <div className="aspect-square bg-gray-200"></div>
          
          <div className="p-4">
            {/* Título skeleton */}
            <div className="h-4 bg-gray-200 rounded mb-2"></div>
            <div className="h-3 bg-gray-200 rounded w-3/4 mb-3"></div>
            
            {/* Categorías skeleton */}
            <div className="flex gap-1 mb-3">
              <div className="h-5 bg-gray-200 rounded w-16"></div>
              <div className="h-5 bg-gray-200 rounded w-12"></div>
            </div>
            
            {/* Precio skeleton */}
            <div className="flex items-center justify-between mb-2">
              <div className="h-6 bg-gray-200 rounded w-20"></div>
              <div className="flex items-center space-x-1">
                {Array.from({ length: 5 }, (_, j) => (
                  <div key={j} className="w-4 h-4 bg-gray-200 rounded"></div>
                ))}
              </div>
            </div>
            
            {/* Descripción skeleton */}
            <div className="space-y-1">
              <div className="h-3 bg-gray-200 rounded"></div>
              <div className="h-3 bg-gray-200 rounded w-2/3"></div>
            </div>
          </div>
        </div>
      ))}
    </div>
  )
} 