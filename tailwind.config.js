/** @type {import('tailwindcss').Config} */
module.exports = {
  content: {
    relative: true,
    files   : ["./templates/**.php","./templates/parts/*.php"]
  },
  theme: {
    extend: {},
  },
  plugins: [],
  prefix: 'dlm-'
}
