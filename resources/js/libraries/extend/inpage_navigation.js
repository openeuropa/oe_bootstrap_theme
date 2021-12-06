(function (bs, Drupal) {
  Drupal.behaviors.inpage_navigation = {
    attach: function attach(context, settings) {
      new bs.ScrollSpy(document.body, {
        target: '#bcl-inpage-navigation'
      });
    }
  };
})(this.bootstrap, Drupal);
