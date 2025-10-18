const LoadingSpinner = ({ size = 'md', text = 'Učitavanje...' }) => {
  const sizeClasses = {
    sm: 'h-4 w-4',
    md: 'h-8 w-8',
    lg: 'h-12 w-12',
    xl: 'h-16 w-16',
  }

  return (
    <div className="flex flex-col items-center justify-center py-8">
      <div className={`animate-spin rounded-full border-2 border-gothic-600 border-t-accent-500 ${sizeClasses[size]}`}></div>
      {text && (
        <p className="mt-4 text-gothic-400 text-sm">{text}</p>
      )}
    </div>
  )
}

export default LoadingSpinner
