/**
 * @file
 * Attaches behaviors for accessible modal and offcanvas triggers.
 */
(function (bootstrap, Drupal) {

  /**
   * Attaches the accessible toggle behavior to modal and offcanvas elements.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Initializes AccessibleToggle for modal and offcanvas elements.
   * @prop {Drupal~behaviorDetach} detach
   *   (Optional) Detach logic can go here if needed in the future.
   */
  Drupal.behaviors.accessibleToggle = {
    attach: function (context, settings) {
      // Initialize AccessibleToggle for modal and offcanvas triggers.
      bootstrap.AccessibleToggle.init();
    }
  };

})(bootstrap, Drupal);
