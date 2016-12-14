define(['jquery', 'underscore', 'skyLinkVue'], function ($, _, vue) {
  var mageJsComponent = function(config, node) {
    'use strict';

    new vue({
      el: node,
      data: {
        items: [],
        since_id: null
      },
      methods: {
        getItems: function () {
          var vm = this;

          var url = config.log_viewer_url;
          if (vm.since_id) {
            url += '?since_id='+vm.since_id;
          }

          $.ajax({
            async: false,
            method: 'GET',
            url: url,
            dataType: 'json',
            success: function (response) {

              if (response.count === 0) {
                return;
              }
              console.log(response);

              vm.since_id = response[response.length - 1].id;
              vm.items.push.apply(vm.items, response);
            }
          });

          console.log('hi');
        }
      },
      mounted: function () {
        this.getItems();
      }
    });
  };

  return mageJsComponent;
});
