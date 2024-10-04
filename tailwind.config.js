/** @type {import('tailwindcss').Config} */
export default {
  content: ['./admin/*.php', './public/*.php', './admin/partials/*.php', './public/js/*.js', './admin/js/*.js'],
  theme: {
    screens: {
      xs: '365px',
      sm: '480px',
      md: '768px',
      lg: '976px',
      xl: '1440px',
    }
  },
  variants: {
    extend: {
      gridTemplateColumns: ['responsive'], // Habilita variantes responsivas para grid-cols
    },
  },
  plugins: [],
  important: true,
}
