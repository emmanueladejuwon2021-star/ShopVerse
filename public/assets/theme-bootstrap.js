// ThemeContext port: persisted theme w/ system-preference fallback (no-flash).
// External file so the CSP can allow it with a fixed hash instead of 'unsafe-inline'.
(function () {
  var saved = localStorage.getItem('theme');
  var dark = saved ? saved === 'dark' : window.matchMedia('(prefers-color-scheme: dark)').matches;
  if (dark) document.documentElement.classList.add('dark');
})();
