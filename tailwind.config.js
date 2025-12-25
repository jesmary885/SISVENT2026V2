/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
     extend: {

            gridTemplateColumns: {
                // Simple 16 column grid
                '13': 'repeat(13, minmax(0, 1fr))',
                '14': 'repeat(14, minmax(0, 1fr))',
                '15': 'repeat(15, minmax(0, 1fr))',
                '16': 'repeat(16, minmax(0, 1fr))',
            },

            fontFamily: {
                'Opena': ['Open Sans', 'sans-serif'],
                'Arima': [ 'Arima', 'system-ui'],
                'Edu': [ 'Edu AU VIC WA NT Dots', 'serif'],
                'Allerta' : ['Allerta Stencil', 'serif'],
                'inter': ['Inter', 'sans-serif'],
                'poppins': ['Poppins', 'sans-serif'],

        
            },

            height: {
                '68': '17rem',
                '128': '32rem',
                '142': '42rem',
                '152': '52rem',
                '162': '62rem',
                '172': '72rem',
            },
            width: {
                '128': '32rem',
                '142': '42rem',
                '152': '52rem',
            },

            animation: {
                fall: 'fall 4s linear infinite',
                bounce: 'bounce 2s infinite',
            },
            keyframes: {
                fall: {
                    '0%': { transform: 'translateY(-100%)' },
                    '100%': { transform: 'translateY(100vh)' },
                },
                bounce: {
                    '0%, 100%': {
                      transform: 'translateY(0)',
                      animationTimingFunction: 'cubic-bezier(0.8, 0, 1, 1)',
                    },
                    '50%': {
                      transform: 'translateY(25%)',
                      animationTimingFunction: 'cubic-bezier(0, 0, 0.2, 1)',
                    },
                },
            },
        },
  },
  plugins: [],
}