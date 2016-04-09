/**
 * @file
 * Javascript functionality for Facebook Instant Articles Display module.
 */

(function ($) {
  'use strict';

  // Handle updates to Ad settings displayed based on selected Ad Type
  Drupal.behaviors.adSettings = {
    attach: function (context, settings) {

      // Initialize the state of the Ads settings based on current value
      $(document).ready(function(){
        updateAdSettingsDisplay($('.ad-type').val());
      });

      // Update the state of the Ads settings if the Ad Type changes
      $('.ad-type').change(function(){
        updateAdSettingsDisplay($('.ad-type').val());
      });

      // Updates the state of the Ads settings displayed based on seleted Ad Type
      function updateAdSettingsDisplay(selectedAdType) {
        switch(selectedAdType) {
          case 'None':
            $('.form-item-fb-instant-articles-ads-an-placement-id').css("visibility", "hidden");
            $('.form-item-fb-instant-articles-ads-iframe-url').css("visibility", "hidden");
            $('.form-item-fb-instant-articles-ads-dimension').css("visibility", "hidden");
            $('.form-item-fb-instant-articles-ads-embed-code').css("visibility", "hidden");
            $('.ads-footer').css("position", "relative");
            $('.ads-footer').css("top", "-450px");
            $('.ads').css("height", "200px");
            break;
          case 'Facebook Audience Network':
            $('.form-item-fb-instant-articles-ads-an-placement-id').css("visibility", "visible");
            $('.form-item-fb-instant-articles-ads-an-placement-id').css("position", "relative");
            $('.form-item-fb-instant-articles-ads-an-placement-id').css("top", "-120px");
            $('.form-item-fb-instant-articles-ads-iframe-url').css("visibility", "hidden");
            $('.form-item-fb-instant-articles-ads-dimension').css("visibility", "visible");
            $('.form-item-fb-instant-articles-ads-dimension').css("position", "relative");
            $('.form-item-fb-instant-articles-ads-dimension').css("top", "-130px");
            $('.form-item-fb-instant-articles-ads-embed-code').css("visibility", "hidden");
            $('.ads-footer').css("position", "relative");
            $('.ads-footer').css("top", "-310px");
            $('.ads').css("height", "350px");
            break;
          case 'Source URL':
            $('.form-item-fb-instant-articles-ads-an-placement-id').css("visibility", "hidden");
            $('.form-item-fb-instant-articles-ads-iframe-url').css("visibility", "visible");
            $('.form-item-fb-instant-articles-ads-iframe-url').css("position", "relative");
            $('.form-item-fb-instant-articles-ads-iframe-url').css("top", "-10px");
            $('.form-item-fb-instant-articles-ads-dimension').css("visibility", "visible");
            $('.form-item-fb-instant-articles-ads-dimension').css("position", "relative");
            $('.form-item-fb-instant-articles-ads-dimension').css("top", "-110px");
            $('.form-item-fb-instant-articles-ads-embed-code').css("visibility", "hidden");
            $('.ads-footer').css("position", "relative");
            $('.ads-footer').css("top", "-290px");
            $('.ads').css("height", "360px");
            break;
          case 'Embed Code':
            $('.form-item-fb-instant-articles-ads-an-placement-id').css("visibility", "hidden");
            $('.form-item-fb-instant-articles-ads-iframe-url').css("visibility", "hidden");
            $('.form-item-fb-instant-articles-ads-dimension').css("visibility", "visible");
            $('.form-item-fb-instant-articles-ads-dimension').css("position", "relative");
            $('.form-item-fb-instant-articles-ads-dimension').css("top", "-40px");
            $('.form-item-fb-instant-articles-ads-embed-code').css("visibility", "visible");
            $('.form-item-fb-instant-articles-ads-embed-code').css("position", "relative");
            $('.form-item-fb-instant-articles-ads-embed-code').css("top", "-280px");
            $('.ads-footer').css("position", "relative");
            $('.ads-footer').css("top", "-220px");
            $('.ads').css("height", "440px");
            break;
        }
      }
    }
  };

})(jQuery);
