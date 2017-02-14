define(['jquery'], function ($) {
  var mageJsComponent = function (config, node) {
    var $button = $(config.button);
    var $responseWrapper = $(config.response_wrapper);
    var $responseDate = $(config.response_date);

    // Watches for the "Check ETA" button to be tapped and fetches the ETA
    $button.on('mouseup', function (e) {
      $button
        .attr('disabled', 'disabled')
        .html(config.check_waiting_text);

      checkEta();
    });

    // Checks the ETA date and updates the DOM
    function checkEta() {
      $.get(config.eta_url, function (response) {
        var date = new Date(response.date);

        $responseDate.html(date.toLocaleDateString());
        $button.addClass('checked');
        $responseWrapper.removeClass('not-ready');
      })
    }

  };

  return mageJsComponent;
});
