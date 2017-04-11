define(['jquery', 'underscore', 'skyLinkVue'], function ($, _, vue) {
  var mageJsComponent = function(config, node) {
    'use strict';

    new vue({
      el: node,
      mounted: function () {
        this.updateLogs();
      },
      data: {
        logs: [],
        currentLevelFilter: config.default_level,
        latestLogId: null,
        autoscroll: true
      },
      computed: {

        filteredLogs: function () {
          var me = this;
          return this.logs.filter(function (log) {
            return log.level >= me.currentLevelFilter;
          });
        }

      },
      methods: {

        toggleAutoscroll: function() {
          this.autoscroll = !this.autoscroll;
        },

        autoscrollIfNeeded: function () {
          if (false === this.autoscroll) {
            return;
          }

          setTimeout(function () {
            var $logs = $(config.logs);
            $logs.scrollTop($logs[0].scrollHeight);
          }, 50);
        },

        getCssClass: function (log) {
          return 'log log-level-'+log.level;
        },

        updateLogs: function () {
          var parameters = {};

          if (null !== this.latestLogId) {
            parameters.since_id = this.latestLogId;
          }

          var url = config.log_viewer_url+'?'+$.param(parameters);

          var me = this;
          $.getJSON(url, function (response) {

            if (response.length > 0) {
              me.logs.push.apply(me.logs, response);

              // We'll make sure we're syncing our logs to keep with the server
              me.logs.splice(0, Math.max(me.logs.length - config.logs_to_keep, 0));

              // And now we'll autoscroll if needed
              me.autoscrollIfNeeded();
            }

            if (me.logs.length > 0) {
              me.latestLogId = me.logs[me.logs.length - 1].id;
            }

            setTimeout(function () {
              me.updateLogs();
            }, config.update_timeout);
          });
        },

        clearLogs: function () {
          var me = this;
          $.post(config.log_clearer_url, { form_key: window.FORM_KEY }, function (response) {
            me.logs = [];
          });
        }
      }
    });
  };

  return mageJsComponent;
});
