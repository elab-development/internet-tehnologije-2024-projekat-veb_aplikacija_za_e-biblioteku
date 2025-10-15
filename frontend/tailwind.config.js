/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      fontFamily: {
        'serif': ['Crimson Text', 'Times New Roman', 'serif'],
        'sans': ['Inter', 'system-ui', 'sans-serif'],
      },
      colors: {
        'gothic': {
          50: '#f8f8f8',
          100: '#e8e8e8',
          200: '#d4d4d4',
          300: '#b8b8b8',
          400: '#999999',
          500: '#7a7a7a',
          600: '#5c5c5c',
          700: '#404040',
          800: '#2a2a2a',
          900: '#1a1a1a',
          950: '#0d0d0d',
        },
        'accent': {
          50: '#fdf2f8',
          100: '#fce7f3',
          200: '#fbcfe8',
          300: '#f9a8d4',
          400: '#f472b6',
          500: '#ec4899',
          600: '#db2777',
          700: '#be185d',
          800: '#9d174d',
          900: '#831843',
        }
      },
      backgroundImage: {
        'gothic-gradient': 'linear-gradient(135deg, #1a1a1a 0%, #2a2a2a 100%)',
        'accent-gradient': 'linear-gradient(135deg, #ec4899 0%, #be185d 100%)',
      }
    },
  },
  plugins: [],
}
