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
   * {@inheritdoc}
   */
  function options_form(&$form, &$form_state) {
    return FALSE;
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
     if (module_exists('entitycache')) {
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
    $node_view = node_view($node, 'fb_instant_article');

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

