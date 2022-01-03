(function (bs, Drupal) {
  Drupal.behaviors.inpage_navigation = {
    attach: function attach(context, settings) {
      new bs.ScrollSpy(document.body, {
        // TODO: At ticket OEL-848 this will be fixed to target on class and not on id.
        target: '#bcl-inpage-navigation'
      });
    }
  };
})(this.bootstrap, Drupal);
