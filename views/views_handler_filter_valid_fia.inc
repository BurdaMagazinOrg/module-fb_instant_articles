<?php

/**
 * @file
 * Definition of views_handler_filter_valid_fia.
 */


/**
 * Simple filter that checks if a node implements the FIA custom view mode
 */
class views_handler_filter_valid_fia extends views_handler_filter {

  var $fia_view_mode = 'facebook_instant_articles_rss';

  /**
   * Display the filter on the administrative summary
   */
  function admin_summary() {
    return t('Filtering for items that have custom facebook view mode settings');
  }

  /**
   * Determine if a filter can be exposed.
   */
  function can_expose() {
    return FALSE;
  }

  /**
   * Determine if a filter can be converted into a group.
   * Only exposed filters with operators available can be converted into groups.
   */
  function can_build_group() {
    return FALSE;
  }

  /**
   * Provide the basic form which calls through to subforms.
   * If overridden, it is best to call through to the parent,
   * or to at least make sure all of the functions in this form
   * are called.
   */
  function options_form(&$form, &$form_state) {
    parent::options_form($form, $form_state);
    $form['info'] = [
      '#type' => 'markup',
      '#markup' => 'Filter for nodes that implement the FIA view mode',
      '#prefix' => '<div class="clearfix">',
      '#suffix' => '</div>',
    ];
  }

  /**
   * Add this filter to the query.
   */
  function query() {
    // Get entity_info for nodes.
    $field = 'node.' . $this->real_field;
    $entity_info = entity_get_info('node');

    // Array of node types that implement our custom view mode.
    $node_types = array();
    foreach ($entity_info['bundles'] as $id => $bundle) {
      $view_mode_settings = field_view_mode_settings('node', $id);
      if (isset($view_mode_settings[$this->fia_view_mode]) && $view_mode_settings[$this->fia_view_mode]['custom_settings'] !== FALSE) {
        $node_types[] = $id;
      }
    }
    if (count($node_types) > 0) {
      /**
       * Only set the value and operator if we have some valid node types, so
       * that we don't break the query.  Leaving them as they are will result
       * in an empty query, which is good
       */
      $this->query->add_where($this->options['group'], $field, $node_types, 'IN');
    }
  }
}
