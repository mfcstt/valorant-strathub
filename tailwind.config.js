/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ["./**/*.{html,js,php}"],
  theme: {
    extend: {
      colors: {
        primary: "#00be00",
        secondary: "#2d2d2d",
        accent: "#ff4d4d",
        light: "#ffffff",
        dark: "#000000",
        success: "#4CAF50",
        warning: "#FFC107",
        error: "#F44336",
        info: "#2196F3",
      },
    },
  },
  plugins: [],
};
