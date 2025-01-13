/**
 * @file
 * Attaches behaviors for managing accordion expand/collapse actions.
 */
(function (bootstrap, Drupal) {

  /**
   * Attaches the accordion toggle behavior to Drupal.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Initializes AccordionToggle for managing accordion actions.
   */
  Drupal.behaviors.accordionToggle = {
    attach: function (context, settings) {
      bootstrap.AccordionToggle.init();
    }
  };
})(bootstrap, Drupal);
