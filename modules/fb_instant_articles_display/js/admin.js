/**
 * @file
 * Javascript functionality for Facebook Instant Articles Display module.
 */

(function ($) {
  'use strict';

  // Row handlers for the 'Manage display' screen.
  Drupal.fieldUIDisplayOverview = Drupal.fieldUIDisplayOverview || {};

  Drupal.fieldUIDisplayOverview.facebookInstantArticlesDisplay = function (row, data) {

    this.row = row;
    this.name = data.name;
    this.region = data.region;
    this.tableDrag = data.tableDrag;

    // Attach change listener to the 'region' select.
    this.$regionSelect = $('select.ds-field-region', row);
    this.$regionSelect.change(Drupal.fieldUIOverview.onChange);

    // Attach change listener to the 'formatter type' select.
    this.$formatSelect = $('select.field-formatter-type', row);
    this.$formatSelect.change(Drupal.fieldUIOverview.onChange);

    return this;
  };

  Drupal.fieldUIDisplayOverview.facebookInstantArticlesDisplay.prototype = {

    /**
     * Returns the region corresponding to the current form values of the row.
     */
    getRegion: function () {
      return this.$regionSelect.val();
    },

    /**
     * Reacts to a row being changed regions.
     *
     * This function is called when the row is moved to a different region, as a
     * result of either :
     * - a drag-and-drop action
     * - user input in one of the form elements watched by the
     *   Drupal.fieldUIOverview.onChange change listener.
     *
     * @param region
     *   The name of the new region for the row.
     *
     * @return
     *   A hash object indicating which rows should be AJAX-updated as a result
     *   of the change, in the format expected by
     *   Drupal.displayOverview.AJAXRefreshRows().
     */
    regionChange: function (region) {

      // Replace dashes with underscores.
      region = region.replace(/-/g, '_');

      // Set the region of the select list.
      this.$regionSelect.val(region);

      // Prepare rows to be refreshed in the form.
      var refreshRows = {};
      refreshRows[this.name] = this.$regionSelect.get(0);

      // If a row is handled by field_group module, loop through the children.
      if ($(this.row).hasClass('field-group') && $.isFunction(Drupal.fieldUIDisplayOverview.group.prototype.regionChangeFields)) {
        Drupal.fieldUIDisplayOverview.group.prototype.regionChangeFields(region, this, refreshRows);
      }

      return refreshRows;
    }
  };

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
