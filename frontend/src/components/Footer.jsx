const Footer = () => {
  return (
    <footer className="bg-gothic-900 border-t border-gothic-700 py-8">
      <div className="container mx-auto px-4">
        <div className="flex flex-col md:flex-row items-center justify-between">
          <div className="flex items-center space-x-2 mb-4 md:mb-0">
            <div className="w-6 h-6 bg-accent-gradient rounded flex items-center justify-center">
              <span className="text-white font-bold text-sm">E</span>
            </div>
            <span className="text-gothic-300 font-serif">
              E-Biblioteka
            </span>
          </div>
          
          <div className="text-center md:text-right text-gothic-400 text-sm">
            <p>&copy; 2024 E-Biblioteka. Sva prava zadržana.</p>
            <p className="mt-1">Seminarski rad - Internet Tehnologije</p>
          </div>
        </div>
      </div>
    </footer>
  )
}

export default Footer
