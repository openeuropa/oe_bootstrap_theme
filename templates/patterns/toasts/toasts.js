Drupal.behaviors.toasts = {
  attach: function () {
    var toastElList = [].slice.call(document.querySelectorAll(".toast"));
    var options = { autohide: false };
    var toastList = toastElList.map(function (toastEl) {
    var toast = new bootstrap.Toast(toastEl, options);
      toast.show();
    });
  }

};
