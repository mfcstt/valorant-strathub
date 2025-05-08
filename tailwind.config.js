/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ["./**/*.{html,js,php}"],
  theme: {
    extend: {
      colors: {
        primary: "#FF4657",
        secondary: "#E55566",
        text_primary: "#E5E2E9",
        text_secondary: "#7A7B9F",
        background_primary: "#0F0F1A",
        background_secondary: "#1A1B2D",
        
      },
    },
  },
  plugins: [],
};
