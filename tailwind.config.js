/**
 * پیکربندی Tailwind برای پروژه کلینیک زیبایی.
 * خروجی با Tailwind standalone CLI ساخته می‌شود (بدون نیاز به Node/CDN):
 *   ./vendor/tailwindcss -c tailwind.config.js -i static/css/input.css -o static/css/tailwind.css --minify
 */
module.exports = {
  content: [
    "./templates/**/*.html",
    "./apps/**/templates/**/*.html",
    "./apps/**/*.py", // برای کلاس‌هایی که در ویوها ساخته می‌شوند
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ["Vazirmatn", "system-ui", "sans-serif"],
      },
      colors: {
        blush: {
          50: "#fdf2f5", 100: "#fce7ec", 200: "#f9cfdb", 300: "#f3a9bf",
          400: "#ea7a9c", 500: "#dc547c", 600: "#c63a63", 700: "#a52c50",
          800: "#892746", 900: "#74243f",
        },
        sand: {
          50: "#faf7f2", 100: "#f3ece1", 200: "#e8d9c4", 300: "#d8bf9e",
          400: "#c6a177", 500: "#b98a5e", 600: "#a87450", 700: "#8b5d43",
          800: "#724d3b", 900: "#5f4133",
        },
      },
      boxShadow: {
        soft: "0 10px 40px -12px rgba(198, 58, 99, 0.25)",
      },
      keyframes: {
        floatUp: {
          "0%": { opacity: "0", transform: "translateY(16px)" },
          "100%": { opacity: "1", transform: "none" },
        },
        blob: {
          "0%,100%": { borderRadius: "42% 58% 63% 37% / 42% 45% 55% 58%" },
          "50%": { borderRadius: "58% 42% 37% 63% / 55% 58% 42% 45%" },
        },
      },
      animation: {
        floatUp: "floatUp .7s ease both",
        blob: "blob 9s ease-in-out infinite",
      },
    },
  },
  plugins: [],
};
