/**
 * @file
 * Attaches behaviors to copy text to clipboard for elements with data-copy-target attribute.
 */
(function (Drupal) {

  const handleClick = function (event) {
    const element = event.currentTarget;
    const targetSelector = element.getAttribute('data-copy-target');
    const targetElement = document.querySelector(targetSelector);

    if (targetElement) {
      const copyText = targetElement.innerText || targetElement.value;
      navigator.clipboard.writeText(copyText);
    }
  };

  Drupal.behaviors.copyClipboard = {
    attach: function (context) {
      once('oebt-clipcopy', '[data-copy-target]', context).forEach(function (element) {
        element.addEventListener('click', handleClick);
      });
    },

    detach: function (context, settings, trigger) {
      Array.prototype.forEach.call(context.querySelectorAll('[data-copy-target]'), function (element) {
        element.removeEventListener('click', handleClick);
      });
    }
  };

})(Drupal);
