import React, { useState, useEffect } from 'react'

const Footer = () => {
  const [quote, setQuote] = useState(null)
  const [loading, setLoading] = useState(true)

  const fallbackQuotes = [
    { quote: 'Knjige su prozor u druge svetove.', author: 'Nepoznat autor' },
    { quote: 'Čitanje je putovanje bez kretanja.', author: 'Nepoznat autor' },
    { quote: 'Knjiga je najbolji prijatelj čoveka.', author: 'Nepoznat autor' },
    { quote: 'U knjigama nalazimo sebe.', author: 'Nepoznat autor' },
    { quote: 'Čitanje obogaćuje dušu.', author: 'Nepoznat autor' },
    { quote: 'Knjige su mostovi između generacija.', author: 'Nepoznat autor' },
    { quote: 'Svaka knjiga je nova avantura.', author: 'Nepoznat autor' },
    { quote: 'Čitanje nas čini pametnijima.', author: 'Nepoznat autor' },
    { quote: 'Život je lep kada imaš dobru knjigu.', author: 'Nepoznat autor' },
    { quote: 'Knjige su ključevi koji otvaraju vrata znanja.', author: 'Nepoznat autor' },
    { quote: 'Čitanje je najbolji način da putuješ bez kretanja.', author: 'Nepoznat autor' },
    { quote: 'Knjiga je san koji držite u ruci.', author: 'Neil Gaiman' },
    { quote: 'Čitanje je za um ono što je vežba za telo.', author: 'Joseph Addison' },
    { quote: 'Knjige su ogledala: u njima vidiš samo ono što već imaš u sebi.', author: 'Carlos Ruiz Zafón' },
    { quote: 'Dobra knjiga je događaj u mom životu.', author: 'Stendhal' },
    { quote: 'Čitanje je razgovor sa najpametnijim ljudima.', author: 'René Descartes' },
    { quote: 'Knjige su najbolji prijatelji koji nikad ne izdaju.', author: 'Nepoznat autor' },
    { quote: 'Čitanje je hrana za dušu.', author: 'Nepoznat autor' },
    { quote: 'Knjige su mostovi koji povezuju prošlost sa budućnošću.', author: 'Nepoznat autor' },
    { quote: 'Svaka stranica je nova avantura.', author: 'Nepoznat autor' }
  ]

  const getRandomFallbackQuote = () => {
    return fallbackQuotes[Math.floor(Math.random() * fallbackQuotes.length)]
  }

  const fetchQuoteFromAPI = async () => {
    try {
      const response = await fetch('https://zenquotes.io/api/random', {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
        }
      })

      if (!response.ok) {
        throw new Error('API response not ok')
      }

      const data = await response.json()
      if (data && data.length > 0) {
        return {
          quote: data[0].q,
          author: data[0].a || 'Nepoznat autor'
        }
      } else {
        throw new Error('No quote data received')
      }
    } catch (error) {
      console.error('Error fetching quote from API:', error)
      throw error
    }
  }

  const loadQuote = async () => {
    setLoading(true)
    try {
      const apiQuote = await fetchQuoteFromAPI()
      setQuote(apiQuote)
    } catch (error) {
      // Koristi fallback ako API ne radi
      setQuote(getRandomFallbackQuote())
    } finally {
      setLoading(false)
    }
  }

  const refreshQuote = () => {
    loadQuote()
  }

  useEffect(() => {
    loadQuote()
  }, [])

  return (
    <footer className="bg-gothic-900 border-t border-gothic-700 mt-auto">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div className="text-center">
          {loading ? (
            <div className="animate-pulse">
              <div className="h-4 bg-gothic-700 rounded w-3/4 mx-auto mb-2"></div>
              <div className="h-3 bg-gothic-700 rounded w-1/2 mx-auto"></div>
            </div>
          ) : quote ? (
            <div className="max-w-4xl mx-auto">
              <blockquote className="text-gothic-200 text-lg italic mb-4">
                "{quote.quote}"
              </blockquote>
              <cite className="text-gothic-400 text-sm">
                — {quote.author}
              </cite>
              <div className="mt-4">
                <button
                  onClick={refreshQuote}
                  className="text-gothic-500 hover:text-gothic-300 text-sm transition-colors"
                >
                  Osveži citat
                </button>
              </div>
            </div>
          ) : (
            <p className="text-gothic-400">
              Knjige su prozor u druge svetove.
            </p>
          )}
          
          <div className="mt-8 pt-6 border-t border-gothic-700">
            <p className="text-gothic-500 text-sm">
              © 2025 E-Biblioteka. Sva prava zadržana.
            </p>
          </div>
        </div>
      </div>
    </footer>
  )
}

export default Footer