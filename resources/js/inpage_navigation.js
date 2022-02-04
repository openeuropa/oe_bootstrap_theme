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
   * @prop {Drupal~behaviorDetach} detach
   *   Destroys the scrollSpy instances to prevent subscribing multiple times.
   */
  Drupal.behaviors.inpage_navigation = {
    attach: function () {
      Array.prototype.forEach.call(document.querySelectorAll('nav.bcl-inpage-navigation'), function (nav) {
        instances.push(new bootstrap.ScrollSpy(document.body, {
          target: nav
        }));
      });
    },
    detach: function () {
      instances.forEach(function (instance) {
        instance.dispose();
      });
    }
  };

})(bootstrap, Drupal);

