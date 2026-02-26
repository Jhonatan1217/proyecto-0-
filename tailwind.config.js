/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./index.html",
    "./index.php",
    "./src/**/*.{html,js,php}",
    "./public/**/*.{html,js,php}",
    "./src/views/**/*.{html,php}",
    "./*.php",
    "./src/**/*.php",
    "./src/**/*.js"
  ],
  theme: {
    extend: {},
  },
  plugins: [],
}