/**
 * @file
 * Attaches behaviors for accessible Bootstrap components.
 */
(function (bootstrap, Drupal) {

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

})(bootstrap, Drupal);
