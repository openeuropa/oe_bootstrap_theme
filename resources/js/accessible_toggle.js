/**
 * @file
 * Attaches behaviors for accessible Bootstrap components.
 */
(function (bootstrap, Drupal) {

  const staticOffcanvasRoles = new WeakMap();

  /**
   * Attaches the accessible toggle behavior to Bootstrap components.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Initializes AccessibleToggle for specified Bootstrap components.
   */
  Drupal.behaviors.accessibleToggle = {
    attach: function (context, settings) {
      bootstrap.AccessibleToggle.init([
        { selector: '[data-bs-toggle="modal"]', type: 'modal' },
        { selector: '[data-bs-toggle="offcanvas"]', type: 'offcanvas' }
        // Additional components like collapse can be added here in the future.
      ]);

      document
        .querySelectorAll('[class*="offcanvas-"][data-offcanvas-static-role]')
        .forEach(function initializeStaticOffcanvasRole(offcanvas) {
          if (staticOffcanvasRoles.has(offcanvas)) {
            return;
          }

          staticOffcanvasRoles.set(
            offcanvas,
            offcanvas.getAttribute('data-offcanvas-static-role')
          );
          // Bootstrap removes its dialog role when the offcanvas closes.
          offcanvas.addEventListener(
            'hidden.bs.offcanvas',
            function restoreStaticOffcanvasRole() {
              offcanvas.setAttribute('role', staticOffcanvasRoles.get(offcanvas));
            }
          );
        });
    }
  };

})(bootstrap, Drupal);
