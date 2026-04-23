window.tailwind = window.tailwind || {};
window.tailwind.config = {
  darkMode: "class",
  corePlugins: {
    preflight: false,
  },
  theme: {
    extend: {
      colors: {
        "surface-bright": "#f8f9ff",
        "on-primary": "#ffffff",
        "primary": "#00685f",
        "primary-container": "#008378",
        "surface-container": "#e5eeff",
        "on-surface": "#0b1c30",
        "error": "#ba1a1a",
      }
    }
  }
};
