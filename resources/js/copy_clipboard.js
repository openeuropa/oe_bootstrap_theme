/**
 * @file
 * Attaches behaviors to copy text to clipboard for elements with data-copy-target attribute.
 */
(function (Drupal) {

  Drupal.behaviors.copyClipboard = {
    attach: function (context) {
      // Use a scoped function to handle click events
      const handleClick = function (event) {
        const element = event.currentTarget;
        const targetSelector = element.getAttribute('data-copy-target');
        const targetElement = document.querySelector(targetSelector);
        
        if (targetElement) {
          const copyText = targetElement.innerText || targetElement.value;
          navigator.clipboard.writeText(copyText);
        }
      };

      // Attach click event listener to elements with data-copy-target
      once('oebt-clipcopy', '[data-copy-target]', context).forEach(function (element) {
        element.addEventListener('click', handleClick);
      });
    },

    detach: function (context, settings, trigger) {
      if (trigger === 'unload') {
        Array.prototype.forEach.call(context.querySelectorAll('[data-copy-target]'), function (element) {
          element.removeEventListener('click', handleClick);
        });
      }
    }
  };

})(Drupal);
