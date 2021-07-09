/**
 * @file
 * Extends the methods of progress bar in core/misc/progress.js.
 */

(function($, Drupal) {
  /**
   * Theme function for the progress bar.
   *
   * @param {string} id
   *   The id for the progress bar.
   *
   * @return {string}
   *   The HTML for the progress bar.
   */
  Drupal.theme.progressBar = function(id) {
    return (
      `<div id="${id}" aria-live="polite">` +
      '<label class="progress-bar-label"></label>' +
      '<div class="progress"><div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div></div>' +
      '<small class="form-text text-muted progress-bar-message"></small>' +
      '</div>'
    );
  };

  $.extend(
    Drupal.ProgressBar.prototype,
    /** @lends Drupal.ProgressBar# */ {
      /**
       * Set the percentage and status message for the progressbar.
       *
       * @param {number} percentage
       *   The progress percentage.
       * @param {string} message
       *   The message to show the user.
       * @param {string} label
       *   The text for the progressbar label.
       */
      setProgress(percentage, message, label) {
        if (percentage >= 0 && percentage <= 100) {
          $(this.element)
            .find('div.progress-bar')
            .css('width', `${percentage}%`)
            .attr('aria-valuenow', percentage)
            .html(`${percentage}%`);
        }
        $('label.progress-bar-label', this.element).html(label);
        $('small.progress-bar-message', this.element).html(message);
        if (this.updateCallback) {
          this.updateCallback(percentage, message, this);
        }
      },

      /**
       * Display errors on the page.
       *
       * @param {string} string
       *   The error message to show the user.
       */
      displayError(string) {
        const error = $('<div class="alert alert-danger"></div>').html(
          string,
        );
        $(this.element)
          .before(error)
          .hide();

        if (this.errorCallback) {
          this.errorCallback(this);
        }
      },
    },
  );
})(jQuery, Drupal);
