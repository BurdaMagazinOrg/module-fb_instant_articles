<?php

/**
 * @file
 * Definition of views_handler_filter_enabled_fia.
 */


/**
 * Simple filter to select entities marked as beeing published for fbia.
 */
class views_handler_filter_enabled_fia extends views_handler_filter {


  /**
   * Display the filter on the administrative summary
   */
  function admin_summary() {
    return t('Filtering for selected Facebook Instant Articles content.');
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
    $form['info'] = array(
      '#type' => 'markup',
      '#markup' => 'Filter for selected content shown in Facebook Instant Articles feed. Filter only works if "All nodes enabled by default?" is unchecked.',
      '#prefix' => '<div class="clearfix">',
      '#suffix' => '</div>',
    );
  }

  /**
   * Add this filter to the query.
   */
  function query() {
    // We don't extend the query if the setting for all_enabled_default is set.
    $default_enabled = variable_get('fb_instant_articles_all_enabled_default', TRUE);
    if (!$default_enabled) {
      // Build join to add fb_instant_articles_entity_settings to query.
      $join = new views_join();
      $join->table = 'fb_instant_articles_entity_settings';
      $join->field = 'entity_id';
      $join->left_table = 'node';
      $join->left_field = 'nid';
      $join->type = 'LEFT';
      // Add relationship to views query.
      $this->query->add_relationship('fia_entity_settings', $join, 'node');
      $this->query->add_where($this->options['group'], 'fia_entity_settings.fia_enabled', 1);
    }
  }
}
