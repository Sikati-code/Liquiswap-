/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./App.{js,jsx,ts,tsx}",
    "./src/**/*.{js,jsx,ts,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        // LiquiSwap Color System
        'liquiswap-navy': '#0B1E3B',
        'douala-slate': '#334155',
        'yello-gold': '#FFCC00',
        'citrus-orange': '#FF7900',
        'pulse-purple': '#6D28D9',
        'cash-green': '#059669',
        'kribi-white': '#FFFFFF',
        'deep-navy': '#0F172A',
      },
      fontFamily: {
        'inter-regular': ['Inter-Regular'],
        'inter-medium': ['Inter-Medium'],
        'inter-semibold': ['Inter-SemiBold'],
        'inter-bold': ['Inter-Bold'],
      },
    },
  },
  plugins: [],
};
