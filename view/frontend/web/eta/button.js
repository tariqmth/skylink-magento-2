define(['jquery'], function ($) {
  var mageJsComponent = function (config, node) {
    var $button = $(config.button);
    var $responseWrapper = $(config.response_wrapper);
    var $responseDate = $(config.response_date);
    var $failedResponseWrapper = $(config.failed_response_wrapper);

    // Watches for the "Check ETA" button to be tapped and fetches the ETA
    $button.on('mouseup', function (e) {
      $button
        .attr('disabled', 'disabled')
        .html(config.check_waiting_text);

      checkEta();
    });

    // Checks the ETA date and updates the DOM
    function checkEta() {
      $.getJSON(config.eta_url, function (response) {
        if (typeof response.date !== 'undefined') {
          var date = new Date(response.date);

          $responseDate.html(date.toLocaleDateString());
        } else {
          $responseDate.html(config.no_eta_text);
        }

        $button.addClass('checked');
        $responseWrapper.removeClass('not-ready');
      })
        .fail(function () {
          $button.addClass('checked');
          $failedResponseWrapper.removeClass('not-ready');
        });
    }

  };

  return mageJsComponent;
});
