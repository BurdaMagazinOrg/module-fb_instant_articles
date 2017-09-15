<?php

/**
 * @file
 * Contains views_plugin_row_fiafields.
 */

/**
 * Plugin which performs a node_view on the resulting object and formats it as
 * an RSS item as per the Facebook Instant Article specification.
 */
class views_plugin_row_fiafields extends views_plugin_row {
  // Basic properties that let the row style follow relationships.
  var $base_table = 'node';
  var $base_field = 'nid';

  // Stores the nodes loaded with pre_render.
  var $nodes = array();

  /**
   * Add to the base class' options for the row.
   */
  function option_definition() {
    $options = parent::option_definition();
    $options['view_mode'] = array('fb_instant_article' => 'fb_instant_article');
    return $options;
  }

  /**
   * Alter the options form for the row to add our options.
   *
   * @param array $form
   * @param array $form_state
   */
  function options_form(&$form, &$form_state) {
    parent::options_form($form, $form_state);

    $form['view_mode'] = array(
      '#type' => 'select',
      '#title' => t('View mode'),
      '#options' => $this->options_form_summary_options(),
      '#default_value' => $this->options['view_mode'],
    );
  }

  /**
   * Helper function to return a list of available view modes for use in the
   * row settings.
   */
  function options_form_summary_options() {
    $entity_info = entity_get_info('node');
    $options = array();
    if (!empty($entity_info['view modes'])) {
      foreach ($entity_info['view modes'] as $mode => $settings) {
        $options[$mode] = $settings['label'];
      }
    }
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  function summary_title() {
    $options = $this->options_form_summary_options();
    return check_plain($options[$this->options['view_mode']]);
  }

  /**
   * {@inheritdoc}
   */
  function pre_render($values) {
    $nids = array();
    foreach ($values as $row) {
      $nids[] = $row->{$this->field_alias};
    }
    if (!empty($nids)) {
     // Get a fresh copy of the node to bypass Entity cache module, preventing
     // getting corrupted Ads/Analytics DOMDocument.
     if(module_exists('entitycache')) {
       $this->nodes = node_load_multiple($nids, array(), TRUE);
     }
     else {
       $this->nodes = node_load_multiple($nids);
     }
    }
  }

  /**
   * Render a row object.
   *
   * @return string
   *   The rendered output of a single row, used by the style plugin.
   */
  function render($row) {
    global $base_url;

    $nid = $row->{$this->field_alias};
    if (!is_numeric($nid)) {
      return;
    }

    // Load the specified node:
    $node = $this->nodes[$nid];
    if (empty($node)) {
      return;
    }

    $uri = entity_uri('node', $node);
    $node->link = url($uri['path'], $uri['options'] + array('absolute' => TRUE));

    // Build output for node.
    $view_mode = $this->options['view_mode'];
    $node_view = node_view($node, $view_mode);

    $item = new stdClass();
    // Required elements
    $item->node = $node;
    $item->title = $node->title;
    $item->link = $node->link;
    $item->content = drupal_render($node_view);
    // Optional elements, modules and themes will have the chance to alter
    // these.
    $item->elements = array(
      'guid' => $node->nid . ' at ' . $base_url,
      'pubDate' => format_date($node->created, 'custom', 'c'),
      'author' => format_username($node),
    );

    // Add necessary namespace
    $this->view->style_plugin->namespaces['xmlns:content'] = 'http://purl.org/rss/1.0/modules/content/';

    return theme($this->theme_functions(),
      array(
        'view' => $this->view,
        'options' => $this->options,
        'row' => $item,
      ));
  }
}

