/**
 * @file
 * Attaches behaviors for accessible Bootstrap components.
 */
(function (bootstrap, Drupal) {

  const staticOffcanvasRoles = new WeakMap();
  let offcanvasResizeListenerAttached = false;

  /**
   * Synchronizes the accessible name with the responsive title visibility.
   *
   * @param {HTMLElement} offcanvas
   *   The offcanvas element.
   */
  function synchronizeOffcanvasLabel(offcanvas) {
    const labelledBy = offcanvas.getAttribute(
      'data-offcanvas-aria-labelledby'
    );
    const title = labelledBy
      ? offcanvas.querySelector('.offcanvas-title')
      : null;

    // A closed mobile offcanvas uses visibility: hidden, so check only whether
    // the title or one of its ancestors uses display: none.
    if (title && title.id === labelledBy && title.offsetParent !== null) {
      offcanvas.setAttribute('aria-labelledby', labelledBy);
    }
    else {
      offcanvas.removeAttribute('aria-labelledby');
    }
  }

  /**
   * Synchronizes labels for all responsive offcanvas elements.
   */
  function synchronizeResponsiveOffcanvasLabels() {
    document
      .querySelectorAll(
        '[class*="offcanvas-"][data-offcanvas-aria-labelledby]'
      )
      .forEach(synchronizeOffcanvasLabel);
  }

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

      synchronizeResponsiveOffcanvasLabels();
      if (!offcanvasResizeListenerAttached) {
        window.addEventListener(
          'resize',
          synchronizeResponsiveOffcanvasLabels
        );
        offcanvasResizeListenerAttached = true;
      }
    }
  };

})(bootstrap, Drupal);
