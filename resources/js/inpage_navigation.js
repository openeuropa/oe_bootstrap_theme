/**
 * @file
 * Attaches behaviors for inpage navigation pattern.
 */
(function (bootstrap, Drupal) {

  var instances = [];

  /**
   * Attaches the scrollspy behaviours to inpage navigation elements.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Initialises a scrollSpy instance for each navigation.
   */
  Drupal.behaviors.inpage_navigation = {
    attach: function () {
      Array.prototype.forEach.call(document.querySelectorAll('nav.bcl-inpage-navigation'), function (nav) {
        instances.push(new bootstrap.ScrollSpy(document.body, {
          target: nav
        }));
      });
    }
  };

})(bootstrap, Drupal);

