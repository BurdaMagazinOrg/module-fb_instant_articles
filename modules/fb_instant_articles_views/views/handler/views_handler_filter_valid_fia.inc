<?php

/**
 * @file
 * Definition of views_handler_filter_valid_fia.
 */


/**
 * Simple filter that checks if a node implements the FIA custom view mode
 */
class views_handler_filter_valid_fia extends views_handler_filter {

  /**
   * Display the filter on the administrative summary
   */
  function admin_summary() {
    return t('Filtering for valid Facebook Instant Articles content types.');
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
      '#markup' => 'Filter for nodes marked as "Include Content Type in Facebook Instant Articles feed."',
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

    $entity_types = fb_instant_articles_get_article_entity_types();
    $node_types = array_keys($entity_types['node']);

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
