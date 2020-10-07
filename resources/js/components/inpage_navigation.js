// Inpage navigation component
// https://v5.getbootstrap.com/docs/5.0/components/scrollspy/#via-javascript
// -----------------------------------------------------------------------------
const navInpage = Array.prototype.slice.apply(
  document.querySelectorAll(".navbar-inpage")
);

if(navInpage.length) {
  navInpage.forEach((nav) => {
    var scrollSpy = new bootstrap.ScrollSpy(document.body, {
      target: '#' + nav.getAttribute("id"),
      offset: 10
    });  
  });
}
