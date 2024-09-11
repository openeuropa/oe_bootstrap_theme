/**
 * @file
 * Attaches behaviors to copy text to clipboard for elements with data-copy-target attribute.
 */
(function (Drupal) {

  /**
   * Attaches the copy-to-clipboard behavior to elements with the 'data-copy-target' attribute.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Initializes copy-to-clipboard functionality for each element.
   * @prop {Drupal~behaviorDetach} detach
   *   Cleans up any event listeners or instances to avoid memory leaks.
   */
  Drupal.behaviors.copy_clipboard = {
    attach: function (context) {
      Array.prototype.forEach.call(document.querySelectorAll('[data-copy-target]'), function (element) {
        var targetClass = element.getAttribute('data-copy-target');
        var targetElement = document.querySelector('.' + targetClass);
        if (targetElement) {
          element.addEventListener('click', function () {
            var copyText = targetElement.innerText || targetElement.value;

            // Copy the text to the clipboard
            navigator.clipboard.writeText(copyText);
          });
        }
      });
    },

    detach: function (context, settings, trigger) {
      if (trigger === 'unload') {
        Array.prototype.forEach.call(document.querySelectorAll('[data-copy-target]'), function (element) {
          element.removeEventListener('click');
        });
      }
    }
  };

})(Drupal);
