Drupal.behaviors.progress = {
  attach: function () {
    var element = document.getElementsByClassName("progress-bar");
    var width = 1;
    var identity = setInterval(scene, 500);
    function scene() {
      if (width >= 100) {
        // clearInterval(identity);
        width = 1;
        element[0].setAttribute('aria-valuenow', width);
        element[0].innerHTML = width + '%';
        element[0].style.width = width + '%';
      } else {
        width++;
        element[0].setAttribute('aria-valuenow', width);
        element[0].innerHTML = width + '%';
        element[0].style.width = width + '%';
      }
    }
  }

};
