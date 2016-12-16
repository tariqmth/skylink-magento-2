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
      watch: {
        currentLevelFilter: function (newFilter) {
          this.currentLevelFilter = newFilter;
          this.updateLogs();
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
        filteredLogs: function () {
          var me = this;
          return this.logs.filter(function (log) {
            return log.level >= me.currentLevelFilter;
          });
        }
      }
    });
  };

  return mageJsComponent;
});
