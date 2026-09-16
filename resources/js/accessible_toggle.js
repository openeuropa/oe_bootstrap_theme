/**
 * @file
 * Attaches behaviors for accessible Bootstrap components.
 */
(function (bootstrap, Drupal, once) {

  /**
   * Adds or removes aria-labelledby based on the offcanvas title visibility.
   *
   * @param {HTMLElement} offcanvas
   *   The offcanvas element.
   */
  function updateOffcanvasLabelledByAttribute(offcanvas) {
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
   * Updates aria-labelledby on all responsive offcanvas elements.
   */
  function updateResponsiveOffcanvasLabelledByAttributes() {
    document
      .querySelectorAll(
        '[class*="offcanvas-"][data-offcanvas-aria-labelledby]'
      )
      .forEach(updateOffcanvasLabelledByAttribute);
  }

  window.addEventListener(
    'resize',
    updateResponsiveOffcanvasLabelledByAttributes
  );

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
    }
  };

  /**
   * Dynamically updates attributes on responsive offcanvas elements.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Initializes attribute switching for responsive offcanvas elements.
   */
  Drupal.behaviors.offcanvasAttributeSwitching = {
    attach: function (context, settings) {
      once(
        'offcanvas-attribute-switching',
        '[class*="offcanvas-"][data-offcanvas-static-role]',
        context
      )
        .forEach(function initializeStaticOffcanvasRole(offcanvas) {
          // Bootstrap removes its dialog role when the offcanvas closes.
          offcanvas.addEventListener(
            'hidden.bs.offcanvas',
            function restoreStaticOffcanvasRole() {
              offcanvas.setAttribute(
                'role',
                offcanvas.getAttribute('data-offcanvas-static-role')
              );
            }
          );
          updateOffcanvasLabelledByAttribute(offcanvas);
        });
    }
  };

})(bootstrap, Drupal, once);
