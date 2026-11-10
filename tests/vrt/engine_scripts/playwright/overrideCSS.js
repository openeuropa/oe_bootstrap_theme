module.exports = async (page) => {
  // Freeze animations to stabilize results.
 await page.addStyleTag({
  content: `
    *, *::before, *::after {
      -webkit-animation: none |important;
      animation-duration: 0s !important;
      animation-delay: 0s !important;
      transition-duration: 0s !important;
      transition-delay: 0s !important;
      caret-color: transparent !important;
      scroll-behavior: auto !important;
    }
  `
 });
};
