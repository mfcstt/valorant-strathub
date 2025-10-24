tailwind.config = {
  theme: {
    extend: {
      colors: {
        'red-base': '#E55566',
        'red-light': '#F77980',

        'gray-1': '#0F0F1A',
        'gray-2': '#131320',
        'gray-3': '#1A1B2D',
        'gray-4': '#45455F',
        'gray-5': '#7A7B9F',
        'gray-6': '#B5B6C9',
        'gray-7': '#E4E5EC',
        'white': '#FFFFFF',

        'error-base': '#D04048',
        'error-light': '#F77980',
      },
      fontFamily: {
        'rammetto': ["Rammetto One", "sans-serif"],
        'rajdhani': ["Rajdhani", "sans-serif"],
        'nunito': ["Nunito Sans", "sans-serif"],
      },
      backgroundImage: {
        'thumb': "url('/assets/images/thumb.png')",
      },
      boxShadow: {
        'buttonHover': '0px 15px 6px #E5556605, 0px 9px 5px #E5556614, 0px 4px 4px #E5556621, 0px 1px 2px #E5556626',
      },
    }
  }
}